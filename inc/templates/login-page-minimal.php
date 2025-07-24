<?php
defined('ABSPATH') || exit;
?>
<!doctype html>
<html <?php language_attributes(); ?> >
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <?php wp_head(); ?>
    <style>
        :root {
            --var-otino-main-color: <?php echo otino_option('site_color'); ?> !important;
        }
    </style>
</head>
<body>
<div class="box-main-login-page-otino container-header-footer flex-center">
    <main class="login-form-box-otino flex-center">
        <?php
        if ( isset( $_GET['pagelock'] ) && $_GET['pagelock'] == 1) {
            $lock_status = true;
        } else {
            $lock_status = false;
        } ?>
        <div class="login-form-wrapper-otino flex-center">
            <div class="box-logo-title-above-form-login-otino">
                <img src="<?php echo otino_option('site_logo'); ?>" alt="" class="logo-image-login-page-otino">
                <span class="title-under-logo-login-lobgu">
                    <?php
                    if ($lock_status) {
                        echo 'برای ادامه لطفا وارد حساب کاربری شوید';
                    } else {
                        $title_welcome = otino_option('welcome_title');
                        echo !empty($title_welcome) ? $title_welcome : 'خوش آمدید';
                    }
                    ?>
                </span>
                <?php
                $referer = wp_get_referer();
                $current_url = home_url($_SERVER['REQUEST_URI']);
                if (!empty($referer) && $referer != $current_url) {
                    $back_url_login = $referer;
                } else {
                    $back_url_login = site_url();
                }
                ?>
                <a href="<?php echo $back_url_login; ?>" class="go-back-btn-login-page-otino">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M4 12H20M20 12L14 6M20 12L14 18" stroke="#282F36" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </a>
            </div>
            <p class="desc-above-form-login-lobgu otp-activated">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M18.3346 8.75008V12.9167C18.3346 15.8334 16.668 17.0834 14.168 17.0834H5.83464C3.33464 17.0834 1.66797 15.8334 1.66797 12.9167V7.08341C1.66797 4.16675 3.33464 2.91675 5.83464 2.91675H11.668" stroke="#282F36" stroke-width="1.25" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M5.83203 7.5L8.44037 9.58333C9.2987 10.2667 10.707 10.2667 11.5654 9.58333L12.5487 8.8" stroke="#282F36" stroke-width="1.25" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M16.2513 6.66667C17.4019 6.66667 18.3346 5.73393 18.3346 4.58333C18.3346 3.43274 17.4019 2.5 16.2513 2.5C15.1007 2.5 14.168 3.43274 14.168 4.58333C14.168 5.73393 15.1007 6.66667 16.2513 6.66667Z" stroke="#282F36" stroke-width="1.25" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                کد تایید ارسال شده را وارد کنید
            </p>
            <?php
            if (!$lock_status) { ?>
                <p class="desc-above-form-login-lobgu">
                    برای ورود / ثبت نام شماره موبایل خود را وارد کنید
                </p>
            <?php } ?>
            <?php woocommerce_login_form(
                array()
            ); ?>
            <?php
            $roles_decs = otino_option('roles_decs');
            if (!empty($roles_decs)) {
            ?>
            <p class="roles-text-under-form-login-lobgu">
                <?php echo $roles_decs; ?>
            </p>
            <?php } ?>
        </div>
    </main>
    <side class="image-side-form-otino flex-center">
        <img src="<?php echo otino_option('side_image_login'); ?>" alt="">
    </side>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        function handleOtpActive(el) {
            const previousSibling = el.previousElementSibling;
            if (previousSibling && previousSibling.tagName === 'P') {
                previousSibling.style.display = 'none';
                document.querySelector('.login-form-wrapper-otino').classList.add('otp-activated');
            }
        }
        function handleUndoOtpActive(el) {
            const previousSibling = el.previousElementSibling;
            if (previousSibling && previousSibling.tagName === 'P') {
                previousSibling.style.display = 'block';
                document.querySelector('.login-form-wrapper-otino').classList.remove('otp-activated');
            }
        }
        const observer = new MutationObserver((mutationsList) => {
            mutationsList.forEach((mutation) => {
                if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                    const target = mutation.target;
                    if (target.classList.contains('otp-active')) {
                        handleOtpActive(target);
                    } else {
                        handleUndoOtpActive(target);
                    }
                }
            });
        });
        document.querySelectorAll('.otino-login-form').forEach((el) => {
            observer.observe(el, { attributes: true });
        });
    });

</script>
</body>
<?php
wp_footer();