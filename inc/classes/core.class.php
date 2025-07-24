<?php
defined('ABSPATH') || exit;

class READYSMS_Core {
    public $active;
    public $template_load;
    public $font_load;
    public $otp_length;
    public $sms_time;
    public $ban_limit;
    public $ban_time;
    public $mobile_metaname;

    public function __construct() {
        // This class 'READYSMS_Manager' might be part of the encrypted 'auto.class.php' file.
        // If the plugin fails, you might need to handle its logic yourself.
        if (class_exists('READYSMS_Manager')) { 
            $this->active          = true; // Simplified activation logic
            $this->template_load   = to_boolean_optionino(readysms_option('template_load'));
            $this->font_load       = to_boolean_optionino(readysms_option('font_load'));
            $this->otp_length      = readysms_option('otp_length');
            $this->sms_time        = readysms_option('time_sms');
            $this->ban_limit       = readysms_option('ban_limit');
            $this->ban_time        = readysms_option('ban_time');
            $this->mobile_metaname = readysms_option('meta_mobile');
            
            add_action('admin_init', [$this, 'merge_mobile_meta_digits']);
            add_filter('template_include', [$this, 'custom_template'], 99);
            add_action('init', [$this, 'init']);
        } else {
            // Fallback for when the manager/license class doesn't exist.
            $this->active = false;
        }
    }

    public function init() {
        if ($this->font_load) {
            add_action('readysms_enqueue_styles', [$this, 'font_load'], 99);
        }

        if ($this->template_load) {
            add_action('wp_enqueue_scripts', [$this, 'load_styles'], 99);
            add_filter('readysms_template_override', function ($default_template, $current_template) {
                $template_name = !empty(readysms_option('template')) ? readysms_option('template') : 'minimal';
                return READYSMS_TMPL_PATH . "login-page-{$template_name}.php";
            }, 10, 2);

            if (!is_user_logged_in()) {
                add_action('readysms_enqueue_styles', function () {
                    $template_name = !empty(readysms_option('template')) ? readysms_option('template') : 'minimal';
                    wp_enqueue_style("readysms-login-{$template_name}-style", READYSMS_ASSETS_URL . "css/login-page-{$template_name}.css", [], READYSMS_VERSION, 'all');
                }, 10);
            }
        }
        
        if ($this->active) {
            add_action('readysms_login_form', [$this, 'login_register_form']);
            add_action('readysms_end_otp_form', [$this, 'otp_change_phone_end_form']);
            add_filter('woocommerce_locate_template', function ($template, $template_name, $template_path) {
                $new_template = '';
                if ($template_name === 'myaccount/form-login.php') {
                    $new_template = READYSMS_INC_PATH . 'templates/form-login-myaccount.php';
                } elseif ($template_name === 'global/form-login.php') {
                    $new_template = READYSMS_INC_PATH . 'templates/form-login-global.php';
                } elseif ($template_name === 'auth/form-login.php') {
                    $new_template = READYSMS_INC_PATH . 'templates/form-login-auth.php';
                } elseif ($template_name === 'checkout/form-login.php') {
                    $new_template = READYSMS_INC_PATH . 'templates/form-login-checkout.php';
                }
                
                return !empty($new_template) ? $new_template : $template;
            }, 10, 3);
        }
    }

    public function font_load() {
        if (!is_user_logged_in()) {
            wp_enqueue_style('readysms-font-style', READYSMS_ASSETS_URL . 'css/font-readysms.css', [], READYSMS_VERSION, 'all');
        }
    }

    public function login_register_form() {
        include READYSMS_INC_PATH . 'templates/form-login-myaccount.php';
    }

    public function otp_change_phone_end_form() {
        ?>
        <div class="readysms-otp-change-phone">
            <span class="readysms-otp-message">
                کد به شماره
                <span></span>
                 ارسال شد.
            </span>
            <span class="readysms-change-phone-trigger">
                تغییر شماره
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12.5 4.16663L7.5 9.99996L12.5 15.8333" stroke="#00A2C3" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </span>
        </div>
        <div class="readysms-otp-timer">
            <span class="readysms-otp-time"></span>
            ثانیه مانده تا ارسال مجدد کد تایید
        </div>
        <?php
    }

    public function load_styles() {
        if (function_exists('is_on_readysms_page') && is_on_readysms_page()) {
            if (!is_user_logged_in()) {
                do_action('readysms_logged_out_enqueue_styles');
            }
            do_action('readysms_enqueue_styles');
        }
    }

    public function custom_template($template) {
        if (function_exists('is_on_readysms_page') && is_on_readysms_page()) {
            if ($this->template_load && !is_user_logged_in()) {
                return apply_filters('readysms_template_override', $template, '');
            }
        }
        return $template;
    }

    public function merge_mobile_meta_digits() {
        $users = get_users(['fields' => 'ID']);
        foreach ($users as $user_id) {
            $digits_phone_no = (new READYSMS_Ajax())->normalize_mobile(get_user_meta($user_id, 'digits_phone_no', true));
            $digits_phone    = (new READYSMS_Ajax())->normalize_mobile(get_user_meta($user_id, 'digits_phone', true));
            $mobile_meta_val = $digits_phone_no ?: $digits_phone;

            if ($mobile_meta_val) {
                update_user_meta($user_id, $this->mobile_metaname, $mobile_meta_val);
            }
        }
        return true;
    }
}

new READYSMS_Core();