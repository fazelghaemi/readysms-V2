<?php
defined('ABSPATH') || exit;

/**
 * Configures the Optionino framework for ReadySMS.
 */

// Helper function to get ReadySMS options
function readysms_option($field) {
    return optionino_get('readysms_option', $field);
}

// Function to add custom admin styles for the plugin pages
function add_custom_styles_readysms() {
    if (isset($_GET['page']) && in_array($_GET['page'], ['readysms_option_settings', 'readysms-user-export'])) {
        ?>
        <style>
            :root {
                --optionino-main-color: #D63638 !important; /* Ready Studio Red */
            }
        </style>
        <?php
        wp_enqueue_style('readysms-admin-style', READYSMS_ASSETS_URL . 'css/admin-readysms.css', [], READYSMS_VERSION, 'all');
    }
}
add_action('admin_enqueue_scripts', 'add_custom_styles_readysms');


// Main plugin configuration
OPTNNO::set_config('readysms_option', [
    'dev_title'      => 'تنظیمات ReadySMS',
    'dev_version'    => READYSMS_VERSION,
    'logo_url'       => READYSMS_ASSETS_URL . 'img/logo.png', // You may need to create this logo
    'dev_textdomain' => READYSMS_TEXTDOMAIN,
    'menu_type'      => 'menu',
    'menu_title'     => 'ReadySMS',
    'page_title'     => 'تنظیمات ReadySMS',
    'page_capability'=> 'manage_options',
    'page_slug'      => 'readysms_option_settings',
    'icon_url'       => 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0iI2EwYTVhYSI+PHBhdGggZD0iTTEyIDJDMTAuOTEgMiAxMCAyLjkxIDEwIDR2MmgxMVY0YzAtMS4wOS0uOTEtMi0yLTJoLTd6bS0yIDhWNmgtMVY0YzAtMS4wOS0uOTEtMi0yLTJoLTJWNGgtMVY2SDJ2NGgxMnptNi41IDEySDIxVjE0aC0yLjV2NmptLTMuNSA2aC0zdi02SDguNXY2aC0zdi02SDJ2NmMwIDEuMS45IDIgMiAyaDEyLjV2LTJ6bTQtMmgtMlYxNGg2djZoLTJWMTZ6bS04LTEwaC0zdjZINHYtNkgxdi0yaDEwbDJ6Ii8+PC9wYXRoPjwvc3ZnPg==', // A generic settings icon
    'menu_priority'  => 58,
]);

// Styling Settings Tab
OPTNNO::set_tab('readysms_option', [
    'id'     => 'styling_settings',
    'title'  => 'تنظیمات ظاهری',
    'desc'   => 'شخصی‌سازی ظاهر فرم ورود و ثبت‌نام.',
    'fields' => [
        [
            'id'    => 'template_load',
            'type'  => 'switcher',
            'title' => 'فعال‌سازی استایل ReadySMS',
            'desc'  => 'با فعال‌سازی این گزینه، استایل صفحه ورود پیش‌فرض قالب شما با استایل اختصاصی افزونه جایگزین می‌شود.',
            'default' => true,
        ],
        [
            'id'      => 'template',
            'type'    => 'select',
            'title'   => 'انتخاب قالب صفحه ورود',
            'options' => [
                'minimal' => 'قالب مینیمال',
                'modern'  => 'قالب مدرن',
            ],
            'default' => 'minimal',
            'require' => [['template_load', '=', true]],
        ],
        [
            'id'    => 'site_logo',
            'type'  => 'image',
            'title' => 'لوگوی وب‌سایت',
            'desc'  => 'این لوگو در بالای فرم ورود نمایش داده می‌شود.',
            'require' => [['template_load', '=', true]],
        ],
        [
            'id'      => 'roles_decs',
            'type'    => 'textarea',
            'title'   => 'متن قوانین و مقررات',
            'desc'    => 'این متن زیر دکمه ارسال کد در فرم ورود نمایش داده می‌شود.',
            'default' => 'ورود و ثبت نام شما به منزله پذیرش <a href="/terms">شرایط و قوانین</a> وب‌سایت است.'
        ],
    ],
]);

// SMS Gateway Settings Tab (Rebuilt for MessageWay)
OPTNNO::set_tab('readysms_option', [
    'id'     => 'sms_settings',
    'title'  => 'تنظیمات پیامک',
    'desc'   => 'این افزونه به صورت اختصاصی با درگاه <a href="https://msgway.com" target="_blank">MessageWay</a> کار می‌کند. اطلاعات زیر را برای اتصال وارد کنید.',
    'fields' => [
        [
            'id'       => 'api_key_sms',
            'type'     => 'text',
            'title'    => 'کلید API (API Key)',
            'desc'     => 'کلید API خود را از پنل کاربری MessageWay دریافت کرده و در این قسمت وارد کنید.',
            'validate' => 'required',
        ],
        [
            'id'       => 'template_id_sms',
            'type'     => 'text',
            'title'    => 'شناسه الگو (Template ID)',
            'desc'     => 'شناسه الگوی پیامکی که برای ارسال کد تایید ساخته‌اید را وارد کنید. الگو باید حاوی متغیر `code` باشد.',
            'validate' => 'required',
        ],
    ],
]);

// General Settings Tab
OPTNNO::set_tab('readysms_option', [
    'id'     => 'general_settings',
    'title'  => 'تنظیمات عملکرد',
    'desc'   => 'تنظیمات مربوط به نحوه عملکرد سیستم ورود با کد تایید.',
    'fields' => [
        [
            'id'      => 'time_sms',
            'type'    => 'number',
            'title'   => 'زمان انقضای کد (به ثانیه)',
            'desc'    => 'پس از این مدت، کد تایید ارسال شده منقضی می‌شود.',
            'default' => 120,
        ],
        [
            'id'      => 'otp_length',
            'type'    => 'select',
            'title'   => 'تعداد ارقام کد تایید',
            'desc'    => 'تعداد ارقام کدی که برای کاربر پیامک می‌شود را مشخص کنید.',
            'options' => [
                '4' => 'چهار رقمی',
                '5' => 'پنج رقمی',
                '6' => 'شش رقمی',
            ],
            'default' => '5',
        ],
        [
            'id'      => 'ban_limit',
            'type'    => 'number',
            'title'   => 'تعداد تلاش‌های ناموفق مجاز',
            'desc'    => 'پس از این تعداد تلاش ناموفق برای ورود کد، کاربر موقتاً مسدود می‌شود.',
            'default' => 5,
        ],
        [
            'id'      => 'ban_time',
            'type'    => 'number',
            'title'   => 'مدت زمان مسدود بودن (به ثانیه)',
            'desc'    => 'کاربر پس از اتمام این زمان می‌تواند مجدداً برای ورود تلاش کند.',
            'default' => 600, // 10 minutes
        ],
        [
            'id'      => 'meta_mobile',
            'type'    => 'text',
            'title'   => 'کلید متای شماره موبایل',
            'desc'    => 'شماره موبایل تایید شده کاربران تحت این کلید در دیتابیس ذخیره می‌شود.',
            'default' => 'mobile_readysms',
        ],
    ],
]);