<?php
/**
 * Login form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/global/form-login-myaccount.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see         https://woocommerce.com/document/template-structure/
 * @package     WooCommerce\Templates
 * @version     9.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( is_user_logged_in() ) {
	return;
}
$otp_mode = (new OTINO_Core())->active;
$template_load = (new OTINO_Core())->template_load;
$otp_length = (new OTINO_Core())->otp_length;
$sms_time = (new OTINO_Core())->sms_time;
?>
        <span id="otino-response-box" class="response-otino" style="display: none;"></span>
<?php if (!$otp_mode) { ?>
<form class="woocommerce-form woocommerce-form-login login" method="post" <?php echo ( $hidden ) ? 'style="display:none;"' : ''; ?>>

	<?php do_action( 'woocommerce_login_form_start' ); ?>

	<?php echo ( $message ) ? wpautop( wptexturize( $message ) ) : ''; // @codingStandardsIgnoreLine ?>

	<p class="form-row form-row-first">
		<label for="username"><?php esc_html_e( 'Username or email', 'woocommerce' ); ?>&nbsp;<span class="required" aria-hidden="true">*</span><span class="screen-reader-text"><?php esc_html_e( 'Required', 'woocommerce' ); ?></span></label>
		<input type="text" class="input-text" name="username" id="username" autocomplete="username" required aria-required="true" />
	</p>
	<p class="form-row form-row-last">
		<label for="password"><?php esc_html_e( 'Password', 'woocommerce' ); ?>&nbsp;<span class="required" aria-hidden="true">*</span><span class="screen-reader-text"><?php esc_html_e( 'Required', 'woocommerce' ); ?></span></label>
		<input class="input-text woocommerce-Input" type="password" name="password" id="password" autocomplete="current-password" required aria-required="true" />
	</p>
	<div class="clear"></div>

	<?php do_action( 'woocommerce_login_form' ); ?>

	<p class="form-row">
		<label class="woocommerce-form__label woocommerce-form__label-for-checkbox woocommerce-form-login__rememberme">
			<input class="woocommerce-form__input woocommerce-form__input-checkbox" name="rememberme" type="checkbox" id="rememberme" value="forever" /> <span><?php esc_html_e( 'Remember me', 'woocommerce' ); ?></span>
		</label>
		<?php wp_nonce_field( 'woocommerce-login', 'woocommerce-login-nonce' ); ?>
		<input type="hidden" name="redirect" value="<?php echo esc_url( $redirect ); ?>" />
		<button type="submit" class="woocommerce-button button woocommerce-form-login__submit<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>" name="login" value="<?php esc_attr_e( 'Login', 'woocommerce' ); ?>"><?php esc_html_e( 'Login', 'woocommerce' ); ?></button>
	</p>
	<p class="lost_password">
		<a href="<?php echo esc_url( wp_lostpassword_url() ); ?>"><?php esc_html_e( 'Lost your password?', 'woocommerce' ); ?></a>
	</p>

	<div class="clear"></div>

	<?php do_action( 'woocommerce_login_form_end' ); ?>

</form>
<?php } else { ?>

        <form id="otino-login-register-form-mobile" class="woocommerce-form woocommerce-form-login login otino-login-form no-template-otino" method="POST" <?php echo ( $hidden ) ? 'style="display:none;"' : ''; ?>>

            <?php echo ( $message ) ? wpautop( wptexturize( $message ) ) : ''; // @codingStandardsIgnoreLine ?>

            <p class="woocommerce-form-row otino-mobile-label-form woocommerce-form-row--wide form-row form-row-wide">
                <label for="otino_mobile"><?php _e('شماره موبایل','otino'); ?>&nbsp;<span class="required">*</span></label>
                <input type="tel" inputmode="numeric" pattern="[0-9]*"
       class="woocommerce-Input woocommerce-Input--text input-text"
       name="otino_mobile" id="otino_mobile" required placeholder=""
       autocomplete="off"
       value="<?php echo ( ! empty( $_POST['otino_mobile'] ) ) ? esc_attr( wp_unslash( $_POST['otino_mobile'] ) ) : ''; ?>" />
<?php // @codingStandardsIgnoreLine ?>
            </p>
            <p class="form-row">
                <input type="hidden" name="otino_mobile_nonce" value="<?php echo esc_attr(wp_create_nonce('otino_mobile_nonce')); ?>">
                <?php wp_nonce_field( 'woocommerce-login', 'woocommerce-login-nonce' ); ?>
                <button type="submit" class="woocommerce-button button woocommerce-form-login__submit<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>" name="otino_mobile_submit" value="<?php _e('ارسال کد','otino'); ?>"><?php _e('ارسال کد','otino'); ?></button>
            </p>
            <span class="otino-loading-box" style="display:none;">
                    <svg width="35" height="35" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <style>.spinner_nOfF{animation:2s cubic-bezier(.36,.6,.31,1) infinite spinner_qtyZ;fill:#fff}.spinner_fVhf{animation-delay:-.5s}.spinner_piVe{animation-delay:-1s}.spinner_MSNs{animation-delay:-1.5s}@keyframes spinner_qtyZ{0%{r:0}25%{r:3px;cx:4px}50%{r:3px;cx:12px}75%{r:3px;cx:20px}100%{r:0;cx:20px}}</style>
                        <circle class="spinner_nOfF" cx="4" cy="12" r="3"></circle>
                        <circle class="spinner_nOfF spinner_fVhf" cx="4" cy="12" r="3"></circle>
                        <circle class="spinner_nOfF spinner_piVe" cx="4" cy="12" r="3"></circle>
                        <circle class="spinner_nOfF spinner_MSNs" cx="4" cy="12" r="3"></circle>
                    </svg>
                </span>
        </form>
        <form id="otino-form-otp" class="otino-login-register-form woocommerce-form woocommerce-form-login login otino-otp-form no-template-otino" method="POST" action="">
            <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                <label for="otino_otp"><?php _e('کد تایید','otino'); ?>&nbsp;<span class="required">*</span></label>
                <div class="otino_otp_container">
                    <?php
                    for ($i = 0; $i < $otp_length; $i++) {
                        echo '<input 
    id="otp-input-' . $i . '" 
    type="tel" 
    inputmode="numeric" 
    pattern="\d*" 
    maxlength="1" 
    class="otino_otp_item woocommerce-Input woocommerce-Input--text input-text" 
    required 
    autocomplete="one-time-code">';

                    }
                    ?>
                </div>
                <?php do_action('otino_end_otp_form'); ?>
            </p>
            <p class="form-row">
                <input type="hidden" name="otino_otp_nonce" value="<?php echo esc_attr(wp_create_nonce('otino_otp_nonce')); ?>">
                <?php if (!empty(esc_url( $redirect ))) { ?>
                    <input type="hidden" name="redirect" value="<?php echo esc_url( $redirect ); ?>" />
                <?php } ?>
                <?php wp_nonce_field( 'woocommerce-login', 'woocommerce-login-nonce' ); ?>
                <input type="hidden" name="otino_otp" id="otino_otp" required value="">
                <input type="hidden" name="otino_mobile_hidden" id="otino_mobile_hidden" required value="">
                <input type="hidden" name="otino_referer" id="otino_referer" value="<?php echo is_checkout() ? 'checkout' : 'notcheckout'; ?>">
                <button type="submit" class="woocommerce-button button woocommerce-form-login__submit<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>" name="otino_otp_submit" value="<?php echo _e('تایید','otino'); ?>"><?php echo _e('تایید','otino'); ?></button>
                <span><?php echo ' <span id="buttonCountdownOtino"></span>'; ?></span>
            </p>
            <span class="otino-loading-box" style="display:none;">
                <svg width="35" height="35" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <style>.spinner_nOfF{animation:2s cubic-bezier(.36,.6,.31,1) infinite spinner_qtyZ;fill:#fff}.spinner_fVhf{animation-delay:-.5s}.spinner_piVe{animation-delay:-1s}.spinner_MSNs{animation-delay:-1.5s}@keyframes spinner_qtyZ{0%{r:0}25%{r:3px;cx:4px}50%{r:3px;cx:12px}75%{r:3px;cx:20px}100%{r:0;cx:20px}}</style>
                    <circle class="spinner_nOfF" cx="4" cy="12" r="3"></circle>
                    <circle class="spinner_nOfF spinner_fVhf" cx="4" cy="12" r="3"></circle>
                    <circle class="spinner_nOfF spinner_piVe" cx="4" cy="12" r="3"></circle>
                    <circle class="spinner_nOfF spinner_MSNs" cx="4" cy="12" r="3"></circle>
                </svg>
            </span>
        </form>
    <?php

    add_action('wp_footer', function() {
        (new OTINO_Core())->load_form_scripts();
    });

} ?>