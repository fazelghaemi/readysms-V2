<?php
defined('ABSPATH') || exit;
class OTINO_Ajax {
    private $secretKey;
    public function __construct() {
        $this->secretKey = $this->generateSecretKey();
        add_action('wp_ajax_nopriv_otino_mobile', [$this, 'otino_mobile']);
        add_action('wp_ajax_otino_mobile', [$this, 'otino_mobile']);
        add_action('wp_ajax_nopriv_otino_otp', [$this, 'otino_otp']);
        add_action('wp_ajax_otino_otp', [$this, 'otino_otp']);
    }
    public function otino_mobile() {

        if (!session_id()) {
            session_start();
        }
        $data = [];
        $message = "";
        $error = "";
        $status = "";
        if (isset($_POST['woocommerce-login-nonce']) && wp_verify_nonce($_POST['woocommerce-login-nonce'], 'woocommerce-login') && isset($_POST['otino_mobile_nonce']) && wp_verify_nonce($_POST['otino_mobile_nonce'], 'otino_mobile_nonce')&& isset($_POST['security_js']) && wp_verify_nonce($_POST['security_js'] , 'otino-ajax-nonce') ) {
            $mobile = !empty($_POST['otino_mobile']) ? sanitize_text_field($this->normalize_mobile($_POST['otino_mobile'])) : '';
            $mobile_js = !empty($_POST['otino_mobile_js']) ? sanitize_text_field($this->normalize_mobile($_POST['otino_mobile_js'])) : '';
            $ban_time = (new OTINO_Core())->ban_time;
            $ban_limit = (new OTINO_Core())->ban_limit;
            $ban_status = 'pass';
            if (isset($_SESSION['otino_ban'])) {
                if ($_SESSION['otino_ban']['status'] == 0) {
                    $ban_status = 'banned';
                }
            }
            $referer = $_POST['_wp_http_referer'];
            if ($ban_status == 'pass') {
                if (!empty($mobile) && !empty($mobile_js) && $mobile === $mobile_js) {
                    $generated_pattern = (new OTINO_Sms())->sendSmsPattern($mobile);
                    if ($generated_pattern !== false) {
                        $code = $this->encrypt($generated_pattern);
                        $otino_session = array(
                            'code' => $code,
                            'mobile' => $mobile,
                            'time' => time(),
                        );
                        $_SESSION['otino_session'] = $otino_session;
                        $status = 'success';
                        $message = "کد تایید پیامک شد.";
                        $data['mobile'] = $mobile;
                        $data['action'] = 'otpActive';
                    } else {
                        $error = "امکان ارسال پیامک وجود نداشت! لطفا چند ساعت دیگر مجددا تلاش نمایید.";
                    }
                } else {
                    $error = "شماره موبایل معتبر نیست!";
                }
            } else {
                $time_banned = $_SESSION['otino_ban']['time'];
                $time_spent = time() - $_SESSION['otino_ban']['time'];
                $one_hour = $ban_time;
                $time_remaining = $one_hour - $time_spent;
                $time_remaining = max(0, $time_remaining);
                $time_remaining_minutes = floor($time_remaining / 60);
                if ($time_remaining !== 0) {
                    $error = "بدلیل تلاش اشتباه زیاد تا ". $time_remaining_minutes ." دقیقه دیگر نمیتوانید وارد شوید.";
                } else {
                    unset($_SESSION['otino_ban']);
                    $error = "محدودیت ورود شما رفع شد! میتوانید مجددا تلاش نمایید.";
                }
            }
        } else {
            $error = "اطلاعات فرم صحیح نیست. درحال بارگزاری مجدد...";
            $data['action'] = "reload";
        }
        $response = [
            'success' => $status,
            'message' => $message,
            'error' => $error,
            'data' => $data
        ];
        wp_send_json($response);
        die();
    }
    public function otino_otp() {
        if (!session_id()) {
            session_start();
        }
        $data = [];
        $message = "";
        $error = "";
        $status = "";
        if (isset($_POST['woocommerce-login-nonce']) && wp_verify_nonce($_POST['woocommerce-login-nonce'], 'woocommerce-login') && isset($_POST['otino_otp_nonce']) && wp_verify_nonce($_POST['otino_otp_nonce'], 'otino_otp_nonce') && isset($_POST['security_js']) && wp_verify_nonce($_POST['security_js'] , 'otino-ajax-nonce') ) {
            $mobile = !empty($_POST['otino_mobile_hidden']) ? sanitize_text_field($this->normalize_mobile($_POST['otino_mobile_hidden'])) : '';
            $otp = !empty($_POST['otino_otp']) ? sanitize_text_field($_POST['otino_otp']) : '';
            $time = time();
            $sms_time = (new OTINO_Core())->sms_time;
            $ban_time = (new OTINO_Core())->ban_time;
            $ban_limit = (new OTINO_Core())->ban_limit;
            $ban_status = 'pass';
            if (isset($_SESSION['otino_ban'])) {
                if ($_SESSION['otino_ban']['status'] == 0) {
                    $ban_status = 'banned';
                }
            }
            $otino_referer = !empty($_POST['otino_referer']) ? sanitize_text_field($_POST['otino_referer']) : '';
            $otino_session = $_SESSION['otino_session'];
            if (is_array($otino_session)) {
                $currect_otp = $otino_session['code'];
                $currect_mobile = $otino_session['mobile'];
                $sent_time = $otino_session['time'];
                if (!empty($otp)) {
                    if ($ban_status == 'pass') {
                        if (hash_equals($currect_otp, $this->encrypt($otp))) {
                            unset($_SESSION['otino_ban']);
                            if ($time - $sent_time < $sms_time) {
                                if ($mobile == $currect_mobile) {
                                    if ($this->is_unique_mobile($mobile)) {
                                        //signup
                                        $default_username = $mobile;
                                        $default_password = wp_generate_password();
                                        $default_email = "";
                                        $user_id = wp_create_user($default_username, $default_password, $default_email);
                                        if (is_wp_error($user_id)) {
                                            $error = "خطا در هنگام ثبت نام کاربر! لطفا به مدیر سایت اطلاع دهید.";
                                            unset($_SESSION['otino_session']);
                                        } else {
                                            $last_login = current_time('Y-m-d H:i:s');
                                            $display_name = $this->generate_displayname($user_id);
                                            wp_update_user(array(
                                                'ID' => $user_id,
                                                'display_name' => $display_name,
                                            ));
                                            wp_update_user(array(
                                                'ID' => $user_id,
                                                'nickname' => $display_name,
                                            ));
                                            update_user_meta($user_id, (new OTINO_Core())->mobile_metaname , $mobile);
                                            update_user_meta($user_id, 'billing_phone', "0" . $mobile);
                                            update_user_meta($user_id, 'shipping_phone', "0" . $mobile);
                                            update_user_meta($user_id, 'last_login', $last_login);
                                            $_SESSION['last_login'] = $last_login;
                                            wp_set_auth_cookie($user_id, true);
                                            $_SESSION['user_id'] = $user_id;
                                            unset($_SESSION['otino_session']);
                                            $status = 'success';
                                            $message = "با موفقیت ثبت نام شدید! درحال انتقال...";
                                            if (!empty($_POST['redirect'])) {
                                                $data['redirect'] = $_POST['redirect'];
                                            } else {
                                                $data['action'] = "reload";
                                            }
                                        }
                                    } else {
                                        //login
                                        $user_id = $this->get_userid_by_mobile($mobile);
                                        if (user_can($user_id, 'manage_options')) {
                                            $error = "برای امنیت بیشتر ادمین ها از این بخش نباید وارد شوند! (لطفا به صفحه ورود ادمین ها مراجعه کنید).";
                                        } else {
                                            $last_login = current_time('Y-m-d H:i:s');
                                            update_user_meta($user_id, 'last_login', $last_login);
                                            $_SESSION['last_login'] = $last_login;
                                            wp_set_auth_cookie($user_id, true);
                                            $_SESSION['user_id'] = $user_id;
                                            unset($_SESSION['otino_session']);
                                            $status = 'success';
                                            if (!empty($_POST['redirect'])) {
                                                $data['redirect'] = $_POST['redirect'];
                                                $message = "با موفقیت وارد شدید! صفحه مجدد بارگزاری میشود...";
                                            } else {
                                                $data['action'] = "reload";
                                                $message = "با موفقیت وارد شدید! درحال انتقال...";
                                            }
                                        }
                                    }
                                } else {
                                    $error = "اطلاعات وارد شده صحیح نیست.";
                                }
                            } else {
                                $error = "زمان ثبت کد به پایان رسید! لطفا مجددا تلاش کنید.";
                                $data['action'] = ['mobileActive'];
                            }
                        } else {
                            $error = "کد وارد شده صحیح نیست.";
                            $data['action'] = 'clearOtp';
                            if (!isset($_SESSION['otino_ban'])) {
                                $otino_ban = array(
                                    'status' => $ban_limit,
                                    'time' => time(),
                                );
                                $_SESSION['otino_ban'] = $otino_ban;
                            } else {
                                if ($_SESSION['otino_ban']['status'] > 0) {
                                    $otino_ban = array(
                                        'status' => $_SESSION['otino_ban']['status'] - 1,
                                        'time' => $_SESSION['otino_ban']['time'],
                                    );
                                    $_SESSION['otino_ban'] = $otino_ban;
                                }
                            }
                        }
                    } else {
                        $time_banned = $_SESSION['otino_ban']['time'];
                        $time_spent = time() - $_SESSION['otino_ban']['time'];
                        $one_hour = $ban_time;
                        $time_remaining = $one_hour - $time_spent;
                        $time_remaining = max(0, $time_remaining);
                        $time_remaining_minutes = floor($time_remaining / 60);
                        if ($time_remaining !== 0) {
                            $error = "بدلیل تلاش اشتباه زیاد تا ". $time_remaining_minutes ." دقیقه دیگر نمیتوانید وارد شوید.";
                        } else {
                            unset($_SESSION['otino_ban']);
                            $error = "محدودیت ورود شما رفع شد! میتوانید مجددا تلاش نمایید.";
                        }
                    }
                } else {
                    $error = "کد تاییدی وارد نکرده اید!";
                }
            } else {
                $error = "در پردازش مشکلی پیش آمد! لطفا چند ساعت دیگر مجددا تلاش نمایید.";
            }

        } else {
            $error = "اطلاعات فرم صحیح نیست. درحال بارگزاری مجدد...";
            $data['action'] = "reload";
        }
        $response = [
            'success' => $status,
            'message' => $message,
            'error' => $error,
            'data' => $data
        ];
        wp_send_json($response);
        die();
    }
    public static function normalize_mobile($mobile) {
        $persianNumerals = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $latinNumerals = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        $mobile = str_replace($persianNumerals, $latinNumerals, $mobile);
        $mobile = preg_replace('/\D/', '', $mobile);
        if (strpos($mobile, '98') === 0) {
            $mobile = substr($mobile, 2);
        }
        if (strpos($mobile, '0') === 0) {
            $mobile = substr($mobile, 1);
        }
        if (strlen($mobile) === 10) {
            return $mobile;
        } else {
            return NULL;
        }
    }
    public function is_unique_mobile($mobile) {
        $users_with_mobile = get_users(array(
            'meta_key' => (new OTINO_Core())->mobile_metaname,
            'meta_value' => $mobile,
            'fields' => 'ID',
        ));
        if (!empty($users_with_mobile) || username_exists($mobile)) {
            $response = false;
        } else {
            $response = true;
        }
        return $response;
    }
    public function get_userid_by_mobile($mobile) {
        $users_with_mobile = get_users(array(
            'meta_key' => (new OTINO_Core())->mobile_metaname,
            'meta_value' => $mobile,
            'fields' => 'ID',
        ));
        if (!empty($users_with_mobile)) {
            return $users_with_mobile[0];
        }
        $user_id_by_username = username_exists($mobile);
        if ($user_id_by_username) {
            return $user_id_by_username;
        }
        return NULL;
    }
    public function generate_displayname($user_id) {
        $index = $user_id + 2345;
        $unique_username = 'user-'.$index;
        while (username_exists($unique_username)) {
            $index++;
            $unique_username = 'user-'.$index;
        }
        $value = $unique_username;
        return $value;
    }
    public function generatePattern() {
        $len = (new OTINO_Core())->otp_length;
        $len =  empty($len) ? 5 : $len;
        $len = max(4, min(8, $len));
        $minValue = pow(10, $len - 1);
        $maxValue = pow(10, $len) - 1;
        return mt_rand($minValue, $maxValue);
    }
    private function generateSecretKey() {
        return "df21500c//56f5@a2-c8e1+df6be#+#ecef7abacd19722f5*99d09462-508f973d7*2e59db7d3794";
    }
    private function encrypt($code) {
        return hash_hmac('sha256', $code, $this->secretKey);
    }
}
new OTINO_Ajax;