jQuery(document).ready(function ($) {
    // Mobile submission form handler
    $('body').on('submit', '#readysms_login_form', function (e) {
        e.preventDefault();
        var form = $(this);
        var submitButton = form.find('button[type="submit"]');
        var mobileInput = form.find('input[name="readysms_mobile"]');
        var messagesDiv = form.find('.readysms-messages');

        submitButton.prop('disabled', true).addClass('loading');
        messagesDiv.slideUp().empty();

        $.ajax({
            type: 'POST',
            url: readysms_ajax.ajax_url, // Localized variable
            data: {
                action: 'readysms_mobile', // Refactored AJAX action
                'woocommerce-login-nonce': form.find('#woocommerce-login-nonce').val(),
                readysms_mobile_nonce: form.find('#readysms_mobile_nonce').val(),
                security_js: readysms_ajax.nonce, // Localized variable
                readysms_mobile: mobileInput.val(),
                _wp_http_referer: form.find('input[name="_wp_http_referer"]').val()
            },
            success: function (response) {
                if (response.success) {
                    messagesDiv.removeClass('error').addClass('success').html(response.message).slideDown();
                    // Update hidden mobile field in OTP form and show it
                    $('#readysms_otp_form').find('input[name="readysms_mobile_hidden"]').val(response.data.mobile);
                    $('.mobile-show-readysms').fadeOut(200, function() {
                        $('.otp-show-readysms').fadeIn(200);
                    });
                    otp_time_start(); // A function from form-readysms.js
                } else {
                    messagesDiv.removeClass('success').addClass('error').html(response.message).slideDown();
                    if (response.data && response.data.action === 'reload') {
                        setTimeout(function() { location.reload(); }, 2000);
                    }
                }
            },
            error: function () {
                messagesDiv.removeClass('success').addClass('error').html('یک خطای پیش‌بینی نشده رخ داد.').slideDown();
            },
            complete: function () {
                submitButton.prop('disabled', false).removeClass('loading');
            }
        });
    });

    // OTP submission form handler
    $('body').on('submit', '#readysms_otp_form', function (e) {
        e.preventDefault();
        var form = $(this);
        var submitButton = form.find('button[type="submit"]');
        var messagesDiv = form.find('.readysms-messages');

        submitButton.prop('disabled', true).addClass('loading');
        messagesDiv.slideUp().empty();

        $.ajax({
            type: 'POST',
            url: readysms_ajax.ajax_url, // Localized variable
            data: {
                action: 'readysms_otp', // Refactored AJAX action
                'woocommerce-login-nonce': form.find('#woocommerce-login-nonce').val(),
                readysms_otp_nonce: form.find('#readysms_otp_nonce').val(),
                security_js: readysms_ajax.nonce, // Localized variable
                readysms_otp: form.find('input[name="readysms_otp"]').val(),
                readysms_mobile_hidden: form.find('input[name="readysms_mobile_hidden"]').val(),
                redirect: form.find('input[name="redirect"]').val(),
                _wp_http_referer: form.find('input[name="_wp_http_referer"]').val()
            },
            success: function (response) {
                if (response.success) {
                    messagesDiv.removeClass('error').addClass('success').html(response.message).slideDown();
                    // Redirect or reload page on success
                    var redirectUrl = response.data.redirect || window.location.href;
                    setTimeout(function() { window.location.href = redirectUrl; }, 1000);
                } else {
                    messagesDiv.removeClass('success').addClass('error').html(response.message).slideDown();
                    if (response.data && response.data.action === 'clearOtp') {
                        form.find('input[name="readysms_otp"]').val('');
                    } else if (response.data && response.data.action === 'mobileActive') {
                         $('.otp-show-readysms').fadeOut(200, function() {
                            $('.mobile-show-readysms').fadeIn(200);
                        });
                    }
                }
            },
            error: function () {
                 messagesDiv.removeClass('success').addClass('error').html('یک خطای پیش‌بینی نشده رخ داد.').slideDown();
            },
            complete: function () {
                submitButton.prop('disabled', false).removeClass('loading');
            }
        });
    });
});