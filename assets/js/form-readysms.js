// This function should be globally available for ajax-notheme-readysms.js to call
function otp_time_start() {
    var timerDisplay = jQuery('.readysms-otp-time');
    var timer = readysms_ajax.smstime; // Use the localized variable
    timerDisplay.text(timer);

    var interval = setInterval(function () {
        timer--;
        timerDisplay.text(timer);
        if (timer <= 0) {
            clearInterval(interval);
            // Optionally, re-enable the "resend" button here if you have one.
            jQuery('.readysms-otp-timer').html('زمان به پایان رسید، مجدداً تلاش کنید.');
        }
    }, 1000);
}

jQuery(document).ready(function ($) {
    // Handler for the "Change Number" link/button
    $('body').on('click', '.readysms-change-phone-trigger', function (e) {
        e.preventDefault();
        $('.otp-show-readysms').fadeOut(200, function () {
            $('.mobile-show-readysms').fadeIn(200);
            // Optionally, clear the OTP input field
            $('#readysms_otp_form input[name="readysms_otp"]').val('');
        });
    });
});