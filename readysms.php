<?php
defined( 'ABSPATH' ) || exit;
/*
 * Plugin Name:       ReadySMS - OTP Login/Signup
 * Plugin URI:        https://readystudio.ir/plugins/readysms
 * Description:       Login and signup with OTP, powered by Rah-e Payam gateway. Customized for Ready Studio.
 * Author:            Ready Studio
 * Version:           1.1.0
 * Author URI:        https://readystudio.ir
 * Text Domain:       readysms
 */

// Define core plugin constants
define('READYSMS_VERSION', '1.1.0');
define('READYSMS_NAME', 'ReadySMS');
define('READYSMS_TEXTDOMAIN', 'readysms');
define('READYSMS_MAIN_FILE', __FILE__);
define('READYSMS_PATH', plugin_dir_path(__FILE__));
define('READYSMS_URL', plugin_dir_url(__FILE__));
define('READYSMS_INC_PATH', READYSMS_PATH . 'inc/');
define('READYSMS_TMPL_PATH', READYSMS_INC_PATH . 'templates/');
define('READYSMS_ASSETS_URL', READYSMS_URL . 'assets/');

// Ensure is_plugin_active() is available
if (!function_exists('is_plugin_active')) {
    include_once(ABSPATH . 'wp-admin/includes/plugin.php');
}

// Include required files
require_once READYSMS_INC_PATH . 'lib/optionino-framework/optionino-framework.php';
require_once READYSMS_INC_PATH . 'lib/readysms-settings.php'; // Needs refactoring
require_once READYSMS_INC_PATH . 'classes/auto.class.php';    // Needs refactoring
require_once READYSMS_INC_PATH . 'classes/core.class.php';    // Needs refactoring
require_once READYSMS_INC_PATH . 'classes/ajax.class.php';    // Needs refactoring
require_once READYSMS_INC_PATH . 'classes/sms.class.php';     // Needs refactoring (Gateway logic is here)
require_once READYSMS_INC_PATH . 'elementor/elementor.php'; // Needs refactoring
require_once READYSMS_INC_PATH . 'classes/lock.class.php';    // Needs refactoring
require_once READYSMS_INC_PATH . 'classes/export.class.php';  // Needs refactoring

/**
 * Checks if WooCommerce is active.
 *
 * @return bool
 */
function is_wc_readysms_active() {
    return class_exists('WooCommerce') && is_plugin_active('woocommerce/woocommerce.php');
}

/**
 * Checks if the current page is a WooCommerce account page.
 *
 * @return bool
 */
function is_on_readysms_page() {
    if (is_wc_readysms_active()) {
        return is_page() && is_account_page();
    }
    return false;
}

/**
 * Adds a help box before the settings panel.
 */
function readysms_settings_help_box() {
    ?>
    <div class="readysms-help-box">
        <h3 class="readysms-help-title">
            راهنمای افزونه پیامک ReadySMS
        </h3>
        <p class="readysms-help-desc">
            آموزش‌های ویدیویی و متنی کامل افزونه در وب‌سایت ردی استودیو قرار گرفته است.
        </p>
        <a href="https://readystudio.ir/docs/readysms" target="_blank" class="readysms-help-button">
            مشاهده آموزش‌ها
        </a>
    </div>
    <style>
        .readysms-help-box {
            display: flex;
            flex-direction: column;
            margin: 15px auto 20px;
            background: #fff;
            border-radius: 15px;
            border: 2px solid var(--optionino-main-color);
            padding: 10px 22px 24px;
        }
        .readysms-help-title {
            margin: 0;
            padding: 15px 0;
            font-size: 18px;
        }
        .readysms-help-desc {
            margin: 0;
            font-size: 14px;
            line-height: 1.7;
        }
        .readysms-help-button {
            display: inline-block;
            background: var(--optionino-main-color);
            border: none;
            border-radius: 9px;
            padding: 8px 16px;
            color: white !important;
            cursor: pointer;
            text-decoration: none;
            width: fit-content;
            margin-top: 15px;
            font-size: 14px;
            font-weight: bold;
            transition: opacity 0.2s;
        }
        .readysms-help-button:hover {
            opacity: 0.8;
        }
    </style>
    <?php
}
// The hook name should also be refactored in the Optionino config file.
add_action('optionino_before_setting_readysms_option', 'readysms_settings_help_box');