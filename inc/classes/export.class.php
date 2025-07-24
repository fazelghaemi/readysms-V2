<?php
defined('ABSPATH') || exit;

if (!class_exists('READYSMS_User_Export')) {
    /**
     * Class READYSMS_User_Export
     * Handles exporting user data to a CSV file.
     */
    class READYSMS_User_Export {

        public function __construct() {
            add_action('admin_menu', [$this, 'add_admin_page']);
            add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
            add_action('wp_ajax_readysms_generate_export', [$this, 'generate_export']);
        }

        /**
         * Adds the export page under the main plugin menu.
         */
        public function add_admin_page() {
            add_submenu_page(
                'readysms_option_settings', // Parent slug (should be defined in config-optionino.php)
                'خروجی کاربران',
                'خروجی کاربران',
                'manage_options',
                'readysms-user-export', // Page slug
                [$this, 'render_export_page_content']
            );
        }

        /**
         * Enqueues necessary scripts for the export page.
         * @param string $hook The current admin page hook.
         */
        public function enqueue_assets($hook) {
            if (isset($_GET['page']) && $_GET['page'] == 'readysms-user-export') {
                wp_enqueue_script('readysms-admin-export', READYSMS_ASSETS_URL . 'js/readysms-export.js', ['jquery'], READYSMS_VERSION, true);
                wp_localize_script('readysms-admin-export', 'readysmsAjax', [
                    'url'      => admin_url('admin-ajax.php'),
                    'action'   => 'readysms_generate_export',
                    'nonce'    => wp_create_nonce('readysms_export_nonce')
                ]);
            }
        }

        /**
         * Renders the HTML content for the export page.
         */
        public function render_export_page_content() {
            // Dynamically get the mobile meta key from settings for consistency
            $mobile_meta_key = readysms_option('meta_mobile') ?: 'mobile_readysms';
            $products = function_exists('wc_get_products') ? wc_get_products(['limit' => -1]) : [];
            ?>
            <div class="wrap">
                <h1>خروجی اطلاعات کاربران</h1>
                <p class="description" style="font-size:15px; background:#f1f1f1; padding:10px; border-radius:5px;">
                    از این ابزار برای دریافت خروجی CSV از کاربران استفاده کنید.<br>
                    <strong>برای خروجی گرفتن از خریداران یک یا چند محصول خاص، آن‌ها را انتخاب کنید. اگر محصولی انتخاب نشود، همه کاربران بررسی می‌شوند.</strong>
                </p>

                <form id="readysms-export-form">
                    
                    <h2>انتخاب محصول (اختیاری)</h2>
                    <p>محصولات مورد نظر را انتخاب کنید (برای انتخاب چندتایی، کلید Ctrl یا Cmd را نگه دارید):</p>
                    <select name="products[]" multiple style="width:400px; min-height:200px;">
                        <?php foreach ($products as $product): ?>
                            <option value="<?php echo esc_attr($product->get_id()); ?>">
                                <?php echo esc_html($product->get_name()); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <br><br>
                    <label>
                        <input type="checkbox" name="include_no_orders" value="1" checked>
                        کاربران بدون سابقه خرید نیز در خروجی باشند
                    </label>

                    <hr style="margin:25px 0;">

                    <h2>انتخاب ستون‌های خروجی</h2>
                    <p>اطلاعاتی که می‌خواهید در فایل خروجی نمایش داده شود را انتخاب کنید:</p>
                    
                    <div style="display: flex; gap: 40px;">
                        <div>
                            <h4>اطلاعات پایه</h4>
                            <label><input type="checkbox" name="columns[]" value="id" checked> شناسه کاربر</label><br>
                            <label><input type="checkbox" name="columns[]" value="display_name" checked> نام نمایشی</label><br>
                            <label><input type="checkbox" name="columns[]" value="user_login" checked> نام کاربری</label><br>
                            <label><input type="checkbox" name="columns[]" value="user_email" checked> ایمیل</label><br>
                            <label><input type="checkbox" name="columns[]" value="<?php echo esc_attr($mobile_meta_key); ?>" checked> موبایل (ReadySMS)</label><br>
                            <label><input type="checkbox" name="columns[]" value="user_registered" checked> تاریخ ثبت‌نام</label><br>
                        </div>
                        <div>
                            <h4>اطلاعات ووکامرس</h4>
                            <label><input type="checkbox" name="columns[]" value="billing_address"> آدرس کامل</label><br>
                            <label><input type="checkbox" name="columns[]" value="billing_city"> شهر</label><br>
                            <label><input type="checkbox" name="columns[]" value="billing_state"> استان</label><br>
                            <label><input type="checkbox" name="columns[]" value="billing_postcode"> کدپستی</label><br>
                            <label><input type="checkbox" name="columns[]" value="billing_phone"> تلفن صورتحساب</label><br>
                        </div>
                        <div>
                            <h4>اطلاعات خرید</h4>
                            <label><input type="checkbox" name="columns[]" value="order_count"> تعداد کل سفارش‌ها</label><br>
                            <label><input type="checkbox" name="columns[]" value="total_spent"> مجموع مبلغ خرید</label><br>
                            <label><input type="checkbox" name="columns[]" value="last_order_date"> تاریخ آخرین خرید</label><br>
                            <label><input type="checkbox" name="columns[]" value="purchased_products"> لیست محصولات خریداری شده</label><br>
                        </div>
                    </div>

                    <hr style="margin:25px 0;">
                    
                    <button type="submit" class="button button-primary" id="readysms-export-btn" style="padding:10px 20px;font-size:16px;">
                        ایجاد خروجی CSV
                    </button>
                    
                    <div id="readysms-loader" style="display:none; margin-top:15px; font-weight: bold;">
                        <p>در حال پردازش، لطفاً چند لحظه صبر کنید...</p>
                    </div>
                    <div id="readysms-export-result" style="margin-top:20px;"></div>
                </form>
            </div>
            <?php
        }

        /**
         * Handles the AJAX request to generate the CSV file.
         */
        public function generate_export() {
            check_ajax_referer('readysms_export_nonce', 'nonce');

            if (!current_user_can('manage_options')) {
                wp_send_json_error(['message' => 'شما دسترسی لازم برای این کار را ندارید.']);
            }

            $filters = isset($_POST['filters']) ? $_POST['filters'] : [];
            $selected_products = isset($filters['products']) ? array_map('intval', $filters['products']) : [];
            $selected_columns = isset($filters['columns']) ? array_map('sanitize_text_field', $filters['columns']) : [];
            $include_no_orders = isset($filters['include_no_orders']) && $filters['include_no_orders'] == '1';

            if (empty($selected_columns)) {
                wp_send_json_error(['message' => 'لطفاً حداقل یک ستون برای خروجی انتخاب کنید.']);
            }
            
            // Filter users based on product purchases
            $user_ids = [];
            if (!empty($selected_products)) {
                $orders = wc_get_orders(['product' => $selected_products, 'limit' => -1, 'return' => 'ids']);
                foreach ($orders as $order_id) {
                    $order = wc_get_order($order_id);
                    if ($order && $order->get_customer_id()) {
                        $user_ids[] = $order->get_customer_id();
                    }
                }
            }
            
            $args = ['number' => -1];
            if (!empty($user_ids)) {
                $args['include'] = array_unique($user_ids);
            }
            $all_users = get_users($args);
            
            // If we should exclude users with no orders at all
            if (!$include_no_orders) {
                $all_users = array_filter($all_users, function($user) {
                    return wc_get_customer_order_count($user->ID) > 0;
                });
            }

            if (empty($all_users)) {
                wp_send_json_error(['message' => 'هیچ کاربری با فیلترهای انتخابی یافت نشد.']);
            }

            $file_info = $this->create_csv_file($all_users, $selected_columns);

            wp_send_json_success([
                'file_url' => $file_info['url'],
                'row_count' => $file_info['count']
            ]);
        }

        /**
         * Creates the CSV file and returns its URL and row count.
         * @param array $users The user objects to export.
         * @param array $columns The columns to include in the CSV.
         * @return array
         */
        private function create_csv_file($users, $columns) {
            $mobile_meta_key = readysms_option('meta_mobile') ?: 'mobile_readysms';
            
            $upload_dir = wp_upload_dir();
            $export_dir = $upload_dir['basedir'] . '/readysms-exports/';
            if (!file_exists($export_dir)) {
                wp_mkdir_p($export_dir);
            }

            $filename = 'readysms-users-export-' . time() . '.csv';
            $filepath = $export_dir . $filename;

            $file = fopen($filepath, 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF)); // Add BOM for UTF-8 Excel compatibility

            // Define all possible column labels
            $labels = [
                'id'                 => 'شناسه کاربر',
                'display_name'       => 'نام نمایشی',
                'user_login'         => 'نام کاربری',
                'user_email'         => 'ایمیل',
                $mobile_meta_key     => 'موبایل (ReadySMS)',
                'user_registered'    => 'تاریخ ثبت‌نام',
                'billing_address'    => 'آدرس کامل',
                'order_count'        => 'تعداد کل سفارش‌ها',
                'total_spent'        => 'مجموع مبلغ خرید',
                'last_order_date'    => 'تاریخ آخرین خرید',
                'purchased_products' => 'محصولات خریداری‌شده',
                'billing_city'       => 'شهر',
                'billing_state'      => 'استان',
                'billing_postcode'   => 'کدپستی',
                'billing_phone'      => 'تلفن صورتحساب',
            ];

            // Create header row based on selected columns
            $header_row = array_map(function($col) use ($labels) {
                return isset($labels[$col]) ? $labels[$col] : $col;
            }, $columns);
            fputcsv($file, $header_row);

            // Add user data rows
            foreach ($users as $user) {
                $row = [];
                foreach ($columns as $col) {
                    switch ($col) {
                        case 'id': $row[] = $user->ID; break;
                        case 'display_name': $row[] = $user->display_name; break;
                        case 'user_login': $row[] = $user->user_login; break;
                        case 'user_email': $row[] = $user->user_email; break;
                        case 'user_registered': $row[] = date('Y-m-d H:i', strtotime($user->user_registered)); break;
                        case 'billing_address': $row[] = get_user_meta($user->ID, 'billing_address_1', true) . ' ' . get_user_meta($user->ID, 'billing_address_2', true); break;
                        case 'billing_city': $row[] = get_user_meta($user->ID, 'billing_city', true); break;
                        case 'billing_state': $row[] = get_user_meta($user->ID, 'billing_state', true); break;
                        case 'billing_postcode': $row[] = get_user_meta($user->ID, 'billing_postcode', true); break;
                        case 'billing_phone': $row[] = get_user_meta($user->ID, 'billing_phone', true); break;
                        case 'order_count': $row[] = wc_get_customer_order_count($user->ID); break;
                        case 'total_spent': $row[] = wc_get_customer_total_spent($user->ID); break;
                        case 'last_order_date': 
                            $last_order = wc_get_customer_last_order($user->ID);
                            $row[] = $last_order ? $last_order->get_date_created()->date('Y-m-d H:i') : '';
                            break;
                        case 'purchased_products':
                            $orders = wc_get_orders(['customer_id' => $user->ID, 'limit' => -1]);
                            $product_names = [];
                            foreach ($orders as $order) {
                                foreach ($order->get_items() as $item) {
                                    $product_names[] = $item->get_name();
                                }
                            }
                            $row[] = implode(' | ', array_unique($product_names));
                            break;
                        default:
                            // This handles our dynamic mobile meta key
                            if ($col === $mobile_meta_key) {
                                $row[] = get_user_meta($user->ID, $mobile_meta_key, true);
                            } else {
                                $row[] = ''; // Placeholder for unhandled columns
                            }
                            break;
                    }
                }
                fputcsv($file, $row);
            }

            fclose($file);

            return ['url' => $upload_dir['baseurl'] . '/readysms-exports/' . $filename, 'count' => count($users)];
        }
    }
    new READYSMS_User_Export();
}