<?php
defined( 'ABSPATH' ) || exit;
/*
Plugin Name: Otino - Optimized OTP signup for WP
Plugin URI: https://shokrino.com/otino
Description: The most optimized login & signup plugin
Author: Shokrino Team
Version: 1.0.2
Author URI: https://shokrino.com
Textdomain: otino
*/
$plugin_data = get_file_data(__FILE__, array('Version' => 'Version'), false);
$plugin_data_name = get_file_data(__FILE__, array('Plugin Name' => 'Plugin Name'), false);
$current_theme = wp_get_theme()->get( 'Name' );
$plugin_version = $plugin_data['Version'];
$plugin_name = $plugin_data_name['Plugin Name'];
$plugin_textdomain = $plugin_data_name['Plugin Name'];

define('OTINO_NAME', $plugin_name);
define('OTINO_VERSION', $plugin_version);
define('OTINO_TEXTDOMAIN', $plugin_version);
define('OTINO_PATH' , WP_CONTENT_DIR.'/plugins'.'/otino');
define('OTINO_MAIN_FILE' , __FILE__ );
define('OTINO_URL' , plugin_dir_url( __DIR__ ).'otino');
define('OTINO_INC' , OTINO_PATH.'/inc');
define('OTINO_TMPL' , OTINO_PATH.'/inc/templates');
define('OTINO_ASSETS' , OTINO_URL.'/assets');

if (!function_exists('is_plugin_active')) {
    include_once(ABSPATH . 'wp-admin/includes/plugin.php');
}

require_once OTINO_INC . '/lib/optionino-framework/optionino-framework.php';
require_once OTINO_INC . '/lib/config-optionino.php';
include_once OTINO_INC . '/classes/auto.class.php';
include_once OTINO_INC . '/classes/core.class.php';
include_once OTINO_INC . '/classes/ajax.class.php';
include_once OTINO_INC . '/classes/sms.class.php';
include_once OTINO_INC . '/elementor/elementor.php';
include_once OTINO_INC . '/classes/lock.class.php';
include_once OTINO_INC . '/classes/export.class.php';

function is_wc_otino() {
    if (class_exists('WooCommerce') && is_plugin_active('woocommerce/woocommerce.php')) {
        return true;
    } else {
        return false;
    }
}
function is_otino() {
    if ( is_wc_otino()) {
        return is_page() && is_account_page();
    }
    return false;
}

function before_setting_otino() {
    ?>
    <div class="help-box-to-shokrino-site">
        <h3 class="title-help-shokrino">
            آموزش کامل افزونه ورود اوتینو
        </h3>
        <p class="desc-help-shokrino">
            تمام بخش های افزونه اوتینو بصورت ویدیویی و با جزئیات در وبسایت شکرینو آموزش داده شده است
        </p>
        <a href="https://shokrino.com/otino" class="go-to-shokrino">
            مشاهده آموزش
        </a>
    </div>
    <style>
        .help-box-to-shokrino-site {
            display: flex;
            flex-direction: column;
            margin: 15px auto 20px;
            background: #fff;
            border-radius: 20px;
            border: 2px solid var(--optionino-main-color);
            padding: 5px 22px 24px;
        }
        .title-help-shokrino {
            margin: 0;
            padding: 15px 0;
        }
        .desc-help-shokrino {
            margin: 0;
        }
        .go-to-shokrino {
            display: flex;
            background: var(--optionino-main-color);
            border: 2px solid transparent;
            border-radius: 9px;
            padding: 6px 12px;
            color: white;
            cursor: pointer;
            width: fit-content;
        }
        .go-to-shokrino {
            display: flex;
            background: var(--optionino-main-color);
            border: 2px solid transparent;
            border-radius: 9px;
            padding: 6px 12px;
            color: white !important;
            cursor: pointer;
            text-decoration: none;
            width: fit-content;
            margin-top: 15px;
            font-size: 14px;
            font-weight: bold;
        }
    </style>
    <?php
}
add_action('optionino_before_setting_otino_option','before_setting_otino');