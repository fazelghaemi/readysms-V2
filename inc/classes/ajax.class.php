<?php
defined('ABSPATH') || exit;

class READYSMS_Ajax {

    private $secretKey;

    public function __construct() {
        $this->secretKey = $this->generateSecretKey();
        
        // Register AJAX hooks for handling mobile number submission
        add_action('wp_ajax_nopriv_readysms_mobile', [$this, 'handle_mobile_submission']);
        add_action('wp_ajax_readysms_mobile', [$this, 'handle_mobile_submission']);
        
        // Register AJAX hooks for handling OTP submission
        add_action('wp_ajax_nopriv_readysms_otp', [$this, 'handle_otp_verification']);
        add_action('wp_ajax_readysms_otp', [$this, 'handle_otp_verification']);
    }

    /**
     * Handles the initial mobile number submission.
     * Verifies nonces, sends the OTP, and sets session data.
     */
    public function handle_mobile_submission() {
        if (!session_id()) {
            session_start();
        }

        $response = ['success' => false, 'message' => '', 'data' => []];

        if (
            !isset($_POST['woocommerce-login-nonce']) || !wp_verify_nonce($_POST['woocommerce-login-nonce'], 'woocommerce-login') ||
            !isset($_POST['readysms_mobile_nonce']) || !wp_verify_nonce($_POST['readysms_mobile_nonce'], 'readysms_mobile_nonce') ||
            !isset($_POST['security_js']) || !wp_verify_nonce($_POST['security_js'], 'readysms-ajax-nonce')
        ) {
            $response['message'] = 'اطلاعات فرم صحیح نیست. درحال بارگزاری مجدد...';
            $response['data']['action'] = 'reload';
            wp_send_json($response);
        }

        // Check if the user is temporarily banned
        if (isset($_SESSION['readysms_ban']) && $this->is_user_banned()) {
            $response['message'] = $this->get_ban_message();
            wp_send_json($response);
        }

        $mobile = !empty($_POST['readysms_mobile']) ? self::normalize_mobile($_POST['readysms_mobile']) : '';

        if (!empty($mobile)) {
            $generated_code = (new READYSMS_Sms())->send_pattern_sms($mobile);

            if ($generated_code !== false) {
                $_SESSION['readysms_session'] = [
                    'code'   => $this->encrypt($generated_code),
                    'mobile' => $mobile,
                    'time'   => time(),
                ];
                $response['success'] = true;
                $response['message'] = 'کد تایید پیامک شد.';
                $response['data']['mobile'] = $mobile;
                $response['data']['action'] = 'otpActive';
            } else {
                $response['message'] = 'امکان ارسال پیامک وجود نداشت. لطفاً با مدیریت تماس بگیرید.';
            }
        } else {
            $response['message'] = 'شماره موبایل وارد شده معتبر نیست.';
        }

        wp_send_json($response);
    }

    /**
     * Handles the OTP code verification.
     * Logs in or signs up the user upon successful verification.
     */
    public function handle_otp_verification() {
        if (!session_id()) {
            session_start();
        }

        $response = ['success' => false, 'message' => '', 'data' => []];
        $core = new READYSMS_Core();

        if (
            !isset($_POST['woocommerce-login-nonce']) || !wp_verify_nonce($_POST['woocommerce-login-nonce'], 'woocommerce-login') ||
            !isset($_POST['readysms_otp_nonce']) || !wp_verify_nonce($_POST['readysms_otp_nonce'], 'readysms_otp_nonce') ||
            !isset($_POST['security_js']) || !wp_verify_nonce($_POST['security_js'], 'readysms-ajax-nonce')
        ) {
            $response['message'] = 'اطلاعات فرم صحیح نیست. درحال بارگزاری مجدد...';
            $response['data']['action'] = 'reload';
            wp_send_json($response);
        }

        // Check ban status again
        if (isset($_SESSION['readysms_ban']) && $this->is_user_banned()) {
            $response['message'] = $this->get_ban_message();
            wp_send_json($response);
        }

        $submitted_otp = !empty($_POST['readysms_otp']) ? sanitize_text_field($_POST['readysms_otp']) : '';
        $session_data = isset($_SESSION['readysms_session']) ? $_SESSION['readysms_session'] : null;

        if (empty($submitted_otp)) {
            $response['message'] = 'کد تاییدی وارد نکرده‌اید.';
            wp_send_json($response);
        }

        if (!$session_data) {
            $response['message'] = 'نشست شما منقضی شده است، لطفاً دوباره تلاش کنید.';
            $response['data']['action'] = 'reload';
            wp_send_json($response);
        }

        $is_otp_correct = hash_equals($session_data['code'], $this->encrypt($submitted_otp));
        $is_time_valid = (time() - $session_data['time']) < $core->sms_time;
        
        if ($is_otp_correct && $is_time_valid) {
            unset($_SESSION['readysms_ban']); // Clear any previous failed attempts
            
            $mobile = $session_data['mobile'];
            $user_id = $this->get_userid_by_mobile($mobile);

            if ($user_id) { // User exists, so log them in
                if (user_can($user_id, 'manage_options')) {
                    $response['message'] = 'ورود مدیران از این طریق امکان‌پذیر نیست. لطفاً از صفحه ورود وردپرس اقدام کنید.';
                } else {
                    $this->login_user($user_id);
                    $response['success'] = true;
                    $response['message'] = 'با موفقیت وارد شدید! در حال انتقال...';
                    $response['data']['redirect'] = !empty($_POST['redirect']) ? $_POST['redirect'] : '';
                    $response['data']['action'] = empty($_POST['redirect']) ? 'reload' : '';
                }
            } else { // User does not exist, so register and log them in
                $user_id = $this->register_user($mobile);
                if (is_wp_error($user_id)) {
                    $response['message'] = 'خطا در هنگام ثبت نام. لطفاً به مدیر سایت اطلاع دهید.';
                } else {
                    $this->login_user($user_id);
                    $response['success'] = true;
                    $response['message'] = 'ثبت‌نام شما با موفقیت انجام شد! در حال انتقال...';
                    $response['data']['redirect'] = !empty($_POST['redirect']) ? $_POST['redirect'] : '';
                    $response['data']['action'] = empty($_POST['redirect']) ? 'reload' : '';
                }
            }
            unset($_SESSION['readysms_session']); // Clear session on success
        } elseif (!$is_time_valid) {
            $response['message'] = 'زمان اعتبار کد به پایان رسیده است. لطفاً دوباره تلاش کنید.';
            $response['data']['action'] = 'mobileActive'; // Go back to mobile step
        } else {
            $this->handle_failed_attempt();
            $response['message'] = 'کد وارد شده صحیح نیست.';
            $response['data']['action'] = 'clearOtp';
        }

        wp_send_json($response);
    }
    
    /**
     * Normalizes a mobile number to a standard format.
     * @param string $mobile
     * @return string|null
     */
    public static function normalize_mobile($mobile) {
        $mobile = str_replace(['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'], range(0, 9), $mobile);
        $mobile = preg_replace('/\D/', '', $mobile);
        if (substr($mobile, 0, 2) === '98') {
            $mobile = substr($mobile, 2);
        }
        if (substr($mobile, 0, 1) === '0') {
            $mobile = substr($mobile, 1);
        }
        return (strlen($mobile) === 10 && is_numeric($mobile)) ? $mobile : null;
    }

    /**
     * Checks if a mobile number is already registered.
     * @param string $mobile
     * @return bool
     */
    public function is_unique_mobile($mobile) {
        $meta_key = (new READYSMS_Core())->mobile_metaname;
        $users_with_mobile = get_users(['meta_key' => $meta_key, 'meta_value' => $mobile, 'fields' => 'ID']);
        return empty($users_with_mobile) && !username_exists($mobile);
    }

    /**
     * Gets user ID by their mobile number.
     * @param string $mobile
     * @return int|null
     */
    public function get_userid_by_mobile($mobile) {
        $meta_key = (new READYSMS_Core())->mobile_metaname;
        $users = get_users(['meta_key' => $meta_key, 'meta_value' => $mobile, 'fields' => 'ID']);
        if (!empty($users)) {
            return $users[0];
        }
        return username_exists($mobile) ?: null;
    }

    /**
     * Generates a unique display name for a new user.
     * @param int $user_id
     * @return string
     */
    private function generate_displayname($user_id) {
        return 'user-' . ($user_id + wp_rand(100, 999));
    }

    /**
     * Generates a random numeric pattern for OTP.
     * @return int
     */
    public function generatePattern() {
        $len = (new READYSMS_Core())->otp_length ?: 5;
        $len = max(4, min(8, (int)$len));
        return mt_rand(pow(10, $len - 1), pow(10, $len) - 1);
    }
    
    /**
     * Logs in a user by setting the auth cookie and updating meta.
     * @param int $user_id
     */
    private function login_user($user_id) {
        update_user_meta($user_id, 'last_login', current_time('mysql'));
        wp_set_current_user($user_id);
        wp_set_auth_cookie($user_id, true);
    }

    /**
     * Creates a new user with the given mobile number.
     * @param string $mobile
     * @return int|WP_Error The new user's ID or a WP_Error object.
     */
    private function register_user($mobile) {
        $core = new READYSMS_Core();
        $user_id = wp_create_user($mobile, wp_generate_password(), ''); // Use mobile as username
        if (!is_wp_error($user_id)) {
            $display_name = $this->generate_displayname($user_id);
            wp_update_user(['ID' => $user_id, 'display_name' => $display_name, 'nickname' => $display_name]);
            update_user_meta($user_id, $core->mobile_metaname, $mobile);
            update_user_meta($user_id, 'billing_phone', '0' . $mobile);
        }
        return $user_id;
    }

    /**
     * Handles the logic for a failed OTP attempt (ban counter).
     */
    private function handle_failed_attempt() {
        $core = new READYSMS_Core();
        if (!isset($_SESSION['readysms_ban'])) {
            $_SESSION['readysms_ban'] = ['attempts' => $core->ban_limit - 1, 'time' => time()];
        } else {
            $_SESSION['readysms_ban']['attempts']--;
        }
    }

    /**
     * Checks if the user is currently banned from making attempts.
     * @return bool
     */
    private function is_user_banned() {
        if (!isset($_SESSION['readysms_ban'])) return false;

        $core = new READYSMS_Core();
        $ban_info = $_SESSION['readysms_ban'];
        
        if ($ban_info['attempts'] > 0) return false; // Still has attempts left

        $time_spent = time() - $ban_info['time'];
        if ($time_spent < $core->ban_time) {
            return true; // Is currently banned
        } else {
            unset($_SESSION['readysms_ban']); // Ban time is over
            return false;
        }
    }

    /**
     * Gets the ban message with remaining time.
     * @return string
     */
    private function get_ban_message() {
        if (!isset($_SESSION['readysms_ban'])) return '';
        
        $core = new READYSMS_Core();
        $time_spent = time() - $_SESSION['readysms_ban']['time'];
        $time_remaining = $core->ban_time - $time_spent;
        $time_remaining_minutes = ceil($time_remaining / 60);

        return "به دلیل تلاش‌های ناموفق متعدد، شما تا " . $time_remaining_minutes . " دقیقه دیگر نمی‌توانید تلاش کنید.";
    }

    /**
     * Generates a secret key for hashing.
     * @return string
     */
    private function generateSecretKey() {
        // This should be a unique, long, and random string.
        // It's best to define this in wp-config.php if possible.
        return 'a_very_secret_key_for_readysms_project_shokrino_refactor';
    }

    /**
     * Encrypts the code for session storage.
     * @param string $code
     * @return string
     */
    private function encrypt($code) {
        return hash_hmac('sha256', $code, $this->secretKey);
    }
}

new READYSMS_Ajax();