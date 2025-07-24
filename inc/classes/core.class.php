<?php
defined('ABSPATH') || exit;
class OTINO_Core {
    public $active;
    public $template_load;
    public $font_load;
    public $otp_length;
    public $sms_time;
    public $ban_limit;
    public $ban_time;
    public $mobile_metaname;
    public function __construct() {
        if (class_exists('OTINO_LCNSN_SMS_Manager')) {
            $this->active = true;//to_boolean_optionino(otino_option('otp_activate'));
            $this->template_load = to_boolean_optionino(otino_option('template_load'));
            $this->font_load = to_boolean_optionino(otino_option('font_load'));
            $this->otp_length = otino_option('otp_length');
            $this->sms_time = otino_option('time_sms');
            $this->ban_limit = otino_option('ban_limit');
            $this->ban_time = otino_option('ban_time');
            $this->mobile_metaname = otino_option('meta_mobile');
            add_action('admin_init', [$this, 'merge_mobile_meta_digits']);
            add_filter('template_include', [$this, 'custom_template'], 99);
            add_action('init',[$this,'init']);
        } else {
            $this->active = false;
        }
    }
    public function init() {
            if ($this->font_load) {
                add_action('otino_enqueue_styles', [$this, 'font_load'], 99);
            }
            if ($this->template_load) {
                add_filter('wp_enqueue_scripts', [$this, 'load_styles'], 99);
                add_filter('otino_template_override', function ($default_template, $current_template) {
                    $template_name_otino = !empty(otino_option('template')) ? otino_option('template') : 'minimal';
                    return OTINO_TMPL . "/login-page-$template_name_otino.php";
                }, 10, 2);
                if (!is_user_logged_in()) {
                    add_action('otino_enqueue_styles', function () {
                        $template_name_otino = !empty(otino_option('template')) ? otino_option('template') : 'minimal';
                        wp_enqueue_style("otino-login-$template_name_otino-style", OTINO_ASSETS . "/css/login-page-$template_name_otino.css", array(), OTINO_VERSION, 'all');
                    }, 10 );
                }
            }
            if ($this->active) {
                add_action('otino_login_form', [$this, 'login_register_form']);
                add_action('otino_end_otp_form', [$this, 'otp_change_phone_end_form']);
                add_filter('woocommerce_locate_template', function ($template, $template_name, $template_path) {
                    if ($template_name === 'myaccount/form-login.php') {
                        return OTINO_INC . '/templates/form-login-myaccount.php';
                    } elseif ($template_name === 'global/form-login.php') {
                        return OTINO_INC . '/templates/form-login-global.php';
                    } elseif ($template_name === 'auth/form-login.php') {
                        return OTINO_INC . '/templates/form-login-auth.php';
                    } elseif ($template_name === 'checkout/form-login.php') {
                        return OTINO_INC . '/templates/form-login-checkout.php';
                    }
                    return $template;
                }, 10, 3);
            }
    }
    public function font_load() {
        if (!is_user_logged_in()) {
            wp_enqueue_style('otino-font-style', OTINO_ASSETS . '/css/font-otino.css', array(), OTINO_VERSION, 'all');
        }
    }
    public function login_register_form() {
        return OTINO_INC . '/templates/form-login-myaccount.php';
    }
    public function otp_change_phone_end_form() {
        ?>
        <div class="otp-change-phone-otino">
            <span class="otp-message-end-otino">
                کد به شماره
                <span></span>
                 ارسال شد.
            </span>
            <span class="change-phone-now-otino">
                تغییر شماره
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12.5 4.16663L7.5 9.99996L12.5 15.8333" stroke="#00A2C3" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </span>
        </div>
        <div class="otp-time-reset-otino">
            <span class="otp-time-otino"></span>
            ثانیه مانده تا ارسال مجدد کد تایید
        </div>
        <?php
    }
    public function load_styles() {
        if (is_otino()) {
            if (!is_user_logged_in()) {
                do_action('otino_logged_out_enqueue_styles');
            }
            do_action('otino_enqueue_styles');
        }
    }
    public function load_form_scripts() {
        wp_enqueue_style( 'form-notheme-style-otino', OTINO_ASSETS . '/css/form-notheme-otino.css', array(), OTINO_VERSION, 'all', false );
        wp_enqueue_script( 'form-script-otino', OTINO_ASSETS . '/js/form-otino.js', array(), OTINO_VERSION, true );
        wp_localize_script( 'form-script-otino', 'otino_ajax', array(
            'smstime' => $this->sms_time,
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'otino-ajax-nonce')
        ));
        wp_enqueue_script( 'ajax-script-otino', OTINO_ASSETS . '/js/ajax-notheme-otino.js', array(), OTINO_VERSION, true );
    }
    public function custom_template( $template ) {
        $default_template = $template;
        $current_template = '';
        if ( function_exists('is_otino') && is_otino() ) {
            if ($this->template_load) {
                if (!is_user_logged_in()) {
                    return apply_filters( 'otino_template_override', $default_template, $current_template );
                }
            }
        }
        return $template;
    }
    public function merge_mobile_meta_digits() {
        $users = get_users();
        foreach ($users as $user) {
            $digits_phone_no = (new OTINO_Ajax())->normalize_mobile(get_user_meta($user->ID, 'digits_phone_no', true));
            $digits_phone = (new OTINO_Ajax())->normalize_mobile(get_user_meta($user->ID, 'digits_phone', true));
            if ($digits_phone_no) {
                update_user_meta($user->ID, $this->mobile_metaname, $digits_phone_no);
            } elseif ($digits_phone) {
                update_user_meta($user->ID, $this->mobile_metaname, $digits_phone);
            } else {
                return false;
            }
        }
        return true;
    }
}
new OTINO_Core;