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
<div class="digikala-main-box flex-center">
    <?php
    if ( isset( $_GET['pagelock'] ) && $_GET['pagelock'] == 1) {
        $lock_status = true;
    } else {
        $lock_status = false;
    }
    $back_url_login = site_url();
    ?>
    <a href="<?php echo $back_url_login; ?>" class="go-back-btn-login-page-otino">
        <img src="<?php echo otino_option('site_logo'); ?>" alt="" class="logo-image-login-page-otino">
    </a>
    <span class="title-under-logo-login-lobgu">
        <?php
        if ($lock_status) {
            echo 'برای ادامه لطفا وارد حساب کاربری شوید';
        } else {
            echo 'ورود | ثبت نام';
        } ?>
    </span>
    <?php
    if (!$lock_status) { ?>
        <p class="desc-above-form-login-lobgu">
            سلام!
            <br>
            لطفا شماره موبایل خود را وارد کنید
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