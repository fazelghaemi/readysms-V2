document.addEventListener('DOMContentLoaded', function () {
    const otpInputs = document.querySelectorAll('.otino_otp_item');
    const hiddenOtpInput = document.getElementById('otino_otp');
    const formOtp = document.getElementById('landsell_otp_container');
    const otpForm = document.querySelector('#otino-form-otp');
    const otpFormSubmitBtn = document.querySelector('button[name="otino_otp_submit"]');
    const phoneInput = document.getElementById('otino_mobile');
    const maxPhoneLength = 10;
    const mobileForm = document.querySelector('#otino-login-register-form-mobile');
    const mobileFormSubmitBtn = document.querySelector('button[name="otino_mobile_submit"]');
    const responseDiv = document.getElementById('otino-response-box');
    const loadingIndicator = document.querySelector('.otino-loading-box');
    const firstOtpInput = otpForm.querySelector('#otp-input-0');

    function sanitizePhoneNumber(phone) {
        if (phone.startsWith('98')) {
            phone = phone.substring(2);
        }
        if (phone.startsWith('+98')) {
            phone = phone.substring(3);
        }
        if (phone.startsWith('0098')) {
            phone = phone.substring(4);
        }
        if (phone.startsWith('0')) {
            phone = phone.substring(1);
        }
        return phone;
    }

    function isPhoneNumberValidOtino(phone) {
        return phone.length === maxPhoneLength && phone.startsWith('9') && /^9\d{9}$/.test(phone);
    }

    function updateHiddenOtpValue() {
        let otpValue = '';
        otpInputs.forEach(input => {
            otpValue += input.value;
        });
        hiddenOtpInput.value = otpValue;

        if (otpValue.length === otpInputs.length) {
            otpFormSubmitBtn.click();
        }
    }

    otpInputs.forEach((input, index) => {
        input.addEventListener('input', () => {
            input.value = input.value
                .replace(/[^0-9۰-۹]/g, '')
                .replace(/[۰]/g, '0')
                .replace(/[۱]/g, '1')
                .replace(/[۲]/g, '2')
                .replace(/[۳]/g, '3')
                .replace(/[۴]/g, '4')
                .replace(/[۵]/g, '5')
                .replace(/[۶]/g, '6')
                .replace(/[۷]/g, '7')
                .replace(/[۸]/g, '8')
                .replace(/[۹]/g, '9');

            if (input.value.length === 1 && index < otpInputs.length - 1) {
                otpInputs[index + 1].focus();
            }

            updateHiddenOtpValue();
        });

        input.addEventListener('keydown', (e) => {
            if (e.key === 'Backspace') {
                if (input.value === '' && index > 0) {
                    otpInputs[index - 1].focus();
                }
            }
        });

        input.addEventListener('keypress', (e) => {
            if (!/[0-9۰-۹]/.test(e.key)) {
                e.preventDefault();
            }
        });
    });

    phoneInput.addEventListener('input', function () {
        let phone = phoneInput.value
            .replace(/[^0-9۰-۹]/g, '')
            .replace(/[۰]/g, '0')
            .replace(/[۱]/g, '1')
            .replace(/[۲]/g, '2')
            .replace(/[۳]/g, '3')
            .replace(/[۴]/g, '4')
            .replace(/[۵]/g, '5')
            .replace(/[۶]/g, '6')
            .replace(/[۷]/g, '7')
            .replace(/[۸]/g, '8')
            .replace(/[۹]/g, '9');

        if (phone.length > 11) {
            phone = phone.substring(0, 11);
        }

        if (phone.length === 0 || /^0?9\d{9}$/.test(phone)) {
            phoneInput.style.borderColor = '';
        } else {
            phoneInput.style.borderColor = 'red';
        }

        phoneInput.value = phone;
    });

    if (mobileForm) {
        mobileForm.addEventListener('submit', function (e) {
            let phone = phoneInput.value
                .replace(/[^0-9]/g, '');
            phone = sanitizePhoneNumber(phone);
            phoneInput.value = phone;
        });
    }

});
