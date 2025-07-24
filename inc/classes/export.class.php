<?php
defined('ABSPATH') || exit;

if (!class_exists('OTINO_User_Export')) {
    class OTINO_User_Export {

        public function __construct() {
            add_action('admin_menu', [$this, 'add_admin_page']);
            add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
            add_action('wp_ajax_otino_generate_export', [$this, 'generate_export']);
        }

        public function add_admin_page() {
            add_submenu_page(
                'otino_option_settings',
                'خروجی کاربران',
                'خروجی کاربران',
                'manage_options',
                'otino-user-export',
                [$this, 'render_export_page']
            );
        }

        public function enqueue_assets($hook) {
            if (isset($_GET['page']) && $_GET['page'] == "otino-user-export") {
                wp_enqueue_script('otino-admin-export', OTINO_ASSETS . '/js/otino-export.js', ['jquery'], false, true);
                wp_localize_script('otino-admin-export', 'otinoAjax', ['url' => admin_url('admin-ajax.php')]);
            }
        }

        public function render_export_page() {
            $products = wc_get_products(['limit' => -1]);
            ?>
            <div class="wrap">
                <h1>خروجی کاربران</h1>
                <p class="description" style="font-size:15px; background:#f1f1f1; padding:10px; border-radius:5px;">
                    از این ابزار برای دریافت خروجی CSV از کاربران استفاده کنید.<br>
                    <strong>برای خروجی گرفتن خریداران محصولات خاص، محصول را انتخاب کنید. اگر هیچ محصولی انتخاب نکنید، همه کاربران نمایش داده می‌شوند.</strong>
                </p>

                <form id="otino-export-form">
                    
                    <h2>انتخاب محصول</h2>
                    <p>محصولات مورد نظر را انتخاب کنید (می‌توانید چند محصول انتخاب کنید یا هیچ محصولی انتخاب نکنید):</p>
                    <select name="products[]" multiple style="width:300px;height:200px;">
                        <?php foreach ($products as $product): ?>
                            <option value="<?php echo esc_attr($product->get_id()); ?>">
                                <?php echo esc_html($product->get_name()); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <br><br>
                    <label style="margin-top:10px; display:inline-block;">
                        <input type="checkbox" name="include_no_orders" value="1" checked>
                        کاربران بدون سفارش هم در خروجی باشند
                    </label>

                    <hr style="margin:25px 0;">

                    <h2>ستون‌های خروجی</h2>
                    <p>مشخص کنید چه اطلاعاتی در ستون های فایل CSV قرار بگیرد:</p>
                    <label><input type="checkbox" name="columns[]" value="id" checked> شناسه کاربر</label><br>
                    <label><input type="checkbox" name="columns[]" value="display_name" checked> نام کاربر</label><br>
                    <label><input type="checkbox" name="columns[]" value="user_login" checked> یوزرنیم</label><br>
                    <label><input type="checkbox" name="columns[]" value="user_email" checked> ایمیل</label><br>
                    <label><input type="checkbox" name="columns[]" value="mobile_otino" checked> موبایل</label><br>
                    <label><input type="checkbox" name="columns[]" value="user_registered" checked> تاریخ ثبت‌نام</label><br>
                    <label><input type="checkbox" name="columns[]" value="billing_address" checked> آدرس ووکامرس</label>

                    <hr style="margin:25px 0;">
                    
                    <h2>ستون‌های خروجی (اطلاعات پیشرفته)</h2>
                    <p>هر اطلاعاتی پیشرفته تری که نیاز دارید را تیک بزنین تا توی خروجی براتون قرار بگیره. ترجیحا فقط مواردی که ضروری هستن رو انتخاب کنید تا خروجی با کیفیت تری دریافت کنین.</p>
                    <label><input type="checkbox" name="columns[]" value="order_count"> تعداد سفارش‌های کاربر</label><br>
                    <label><input type="checkbox" name="columns[]" value="total_spent"> مجموع مبلغ خرید کاربر</label><br>
                    <label><input type="checkbox" name="columns[]" value="avg_order_value"> میانگین مبلغ هر سفارش کاربر</label><br>
                    <label><input type="checkbox" name="columns[]" value="last_order_date"> آخرین تاریخ خرید کاربر</label><br>
                    <label><input type="checkbox" name="columns[]" value="first_order_date"> تاریخ اولین خرید کاربر</label><br>
                    <label><input type="checkbox" name="columns[]" value="purchased_products"> لیست محصولات خریداری شده توسط کاربر</label><br>
                    <label><input type="checkbox" name="columns[]" value="billing_city"> شهر</label><br>
                    <label><input type="checkbox" name="columns[]" value="billing_state"> استان</label><br>
                    <label><input type="checkbox" name="columns[]" value="billing_postcode"> کدپستی</label><br>
                    <label><input type="checkbox" name="columns[]" value="billing_phone"> تلفن ووکامرس</label><br>
                    <label><input type="checkbox" name="columns[]" value="last_payment_method"> روش پرداخت آخرین خرید</label><br>

                    <br><br>

                    <div id="otino-loader" style="display:none;margin-top:15px;">
                        <p><strong>در حال پردازش، لطفا صبر کنید...</strong></p>
                    </div>
                    <div id="otino-export-result" style="margin-top:20px;"></div>

                    <button type="button" class="button button-primary" id="otino-export-btn" style="padding:10px 20px;font-size:16px;">
                        ساخت خروجی CSV
                    </button>
                </form>

                
            </div>
            <?php
        }

        public function generate_export() {
            if (!current_user_can('manage_options')) {
                wp_send_json_error('دسترسی ندارید.');
            }

            parse_str($_POST['data'], $filters);

            $selected_columns = !empty($filters['columns']) ? array_map('sanitize_text_field', $filters['columns']) : [];
            if (empty($selected_columns)) {
                wp_send_json_error('لطفاً حداقل یک ستون برای خروجی انتخاب کنید.');
            }

            $args = [
                'number' => -1
            ];
            $users = get_users($args);

            $include_no_orders = isset($filters['include_no_orders']) && $filters['include_no_orders'] == '1';

            $users = array_filter($users, function($user) use ($filters, $include_no_orders) {

                $orders = wc_get_orders(['customer_id' => $user->ID, 'limit' => 1]);

                if (!$include_no_orders && empty($orders)) {
                    return false;
                }

                if (!empty($filters['products'])) {
                    $product_ids = array_map('intval', $filters['products']);
                    $all_orders = wc_get_orders(['customer_id' => $user->ID, 'limit' => -1]);
                    foreach ($all_orders as $order) {
                        foreach ($order->get_items() as $item) {
                            if (in_array($item->get_product_id(), $product_ids)) {
                                return true;
                            }
                        }
                    }
                    return false;
                }

                return true;
            });

            if (empty($users)) {
                wp_send_json_error('هیچ کاربری یافت نشد.');
            }

            $file = $this->create_csv($users, $selected_columns);

            wp_send_json_success([
                'file' => $file['url'],
                'rows' => $file['count']
            ]);
        }

        private function create_csv($users, $columns) {
            $upload_dir = wp_upload_dir();
            $export_dir = $upload_dir['basedir'] . '/otino-exports/';
            if (!file_exists($export_dir)) {
                wp_mkdir_p($export_dir);
            }

            $domain = parse_url(site_url(), PHP_URL_HOST);
            $domain_clean = str_replace('.', '-', $domain);
            $filename = 'users-' . $domain_clean . '-export-' . time() . '.csv';
            $filepath = $export_dir . $filename;

            $output = fopen($filepath, 'w');
            fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

            $labels = [
                'id'                 => 'شناسه کاربر',
                'display_name'       => 'نام نمایشی',
                'user_login'         => 'نام کاربری',
                'user_email'         => 'ایمیل',
                'mobile_otino'       => 'شماره موبایل تایید شده',
                'user_registered'    => 'تاریخ ثبت‌نام',
                'billing_address'    => 'آدرس کامل',
                'order_count'        => 'تعداد سفارش‌ها',
                'total_spent'        => 'مجموع مبلغ خرید',
                'avg_order_value'    => 'میانگین مبلغ خرید',
                'last_order_date'    => 'آخرین تاریخ خرید',
                'first_order_date'   => 'تاریخ اولین خرید',
                'purchased_products' => 'محصولات خریداری‌شده',
                'billing_city'       => 'شهر',
                'billing_state'      => 'استان',
                'billing_postcode'   => 'کدپستی',
                'billing_phone'      => 'شماره تلفن',
                'last_payment_method'=> 'روش پرداخت آخرین خرید'
            ];

            $header_row = [];
            foreach ($columns as $col) {
                $header_row[] = isset($labels[$col]) ? $labels[$col] : $col;
            }
            fputcsv($output, $header_row);

            foreach ($users as $user) {
                $row = [];
                foreach ($columns as $col) {
                    switch ($col) {
                        case 'id':
                            $row[] = $user->ID;
                            break;
                        case 'display_name':
                            $row[] = $user->display_name;
                            break;
                        case 'user_login':
                            $row[] = $user->user_login;
                            break;
                        case 'user_email':
                            $row[] = $user->user_email;
                            break;
                        case 'mobile_otino':
                            $row[] = get_user_meta($user->ID, 'mobile_otino', true);
                            break;
                        case 'user_registered':
                            $date = $user->user_registered;
                            $row[] = ($date && $date !== '0000-00-00 00:00:00') ? date('Y-m-d H:i', strtotime($date)) : 'نامشخص';
                            break;
                        case 'billing_address':
                            $address = trim(get_user_meta($user->ID, 'billing_address_1', true) . ' ' . get_user_meta($user->ID, 'billing_city', true));
                            $row[] = $address ?: 'ندارد';
                            break;
                        case 'order_count':
                            $row[] = wc_get_customer_order_count($user->ID);
                            break;
                        case 'total_spent':
                            $row[] = wc_get_customer_total_spent($user->ID);
                            break;
                        case 'avg_order_value':
                            $order_count = wc_get_customer_order_count($user->ID);
                            $total_spent = wc_get_customer_total_spent($user->ID);
                            $row[] = $order_count > 0 ? round($total_spent / $order_count, 2) : 0;
                            break;
                        case 'last_order_date':
                            $last_order = wc_get_customer_last_order($user->ID);
                            $row[] = $last_order ? $last_order->get_date_created()->date('Y-m-d H:i') : 'ندارد';
                            break;
                        case 'first_order_date':
                            $orders = wc_get_orders(['customer_id' => $user->ID, 'orderby' => 'date', 'order' => 'ASC', 'limit' => 1]);
                            $row[] = !empty($orders) ? $orders[0]->get_date_created()->date('Y-m-d H:i') : 'ندارد';
                            break;
                        case 'purchased_products':
                            $orders = wc_get_orders(['customer_id' => $user->ID, 'limit' => -1]);
                            $products = [];
                            foreach ($orders as $order) {
                                foreach ($order->get_items() as $item) {
                                    $products[] = $item->get_name();
                                }
                            }
                            $row[] = !empty($products) ? implode(' | ', array_unique($products)) : 'ندارد';
                            break;
                        case 'billing_city':
                            $row[] = get_user_meta($user->ID, 'billing_city', true);
                            break;
                        case 'billing_state':
                            $row[] = get_user_meta($user->ID, 'billing_state', true);
                            break;
                        case 'billing_postcode':
                            $row[] = get_user_meta($user->ID, 'billing_postcode', true);
                            break;
                        case 'billing_phone':
                            $row[] = get_user_meta($user->ID, 'billing_phone', true);
                            break;
                        case 'last_payment_method':
                            $last_order = wc_get_customer_last_order($user->ID);
                            $row[] = $last_order ? $last_order->get_payment_method_title() : 'ندارد';
                            break;
                    }
                }
                fputcsv($output, $row);
            }

            fclose($output);

            return ['url' => $upload_dir['baseurl'] . '/otino-exports/' . $filename, 'count' => count($users)];
        }


    }
    new OTINO_User_Export();
}
