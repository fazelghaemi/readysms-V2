document.addEventListener('DOMContentLoaded', function () {
    const hiddenOtpInput = document.getElementById('otino_otp');
    const otpForm = document.querySelector('#otino-form-otp');
    const otpFormSubmitBtn = document.querySelector('button[name="otino_otp_submit"]');
    const phoneInput = document.getElementById('otino_mobile');
    const maxPhoneLength = 10;
    const mobileForm = document.querySelector('#otino-login-register-form-mobile');
    const changeMobileOtp = document.querySelector('.change-phone-now-otino');
    const mobileFormSubmitBtn = document.querySelector('button[name="otino_mobile_submit"]');
    const responseDiv = document.getElementById('otino-response-box');
    const loadingIndicator = document.querySelector('.otino-loading-box');
    const firstOtpInput = otpForm.querySelector('#otp-input-0');
    const mobileInput = mobileForm.querySelector('#otino_mobile');
    timerOtino = new TimerOtino(otino_ajax.smstime * 1000);
    mobileInput.value = '';
    function isPhoneNumberValidOtino(phone) {
        return phone.length === maxPhoneLength && phone.startsWith('9') && /^9\d{9}$/.test(phone);
    }
    changeMobileOtp.addEventListener('click', function (event) {
        mobileForm.classList.remove('otp-active');
        responseDiv.style.display = 'none';
        timerOtino.stop();
    });
    mobileForm.addEventListener('submit', function (event) {
        event.preventDefault();
        const mobileNumber = phoneInput.value.trim();
        responseDiv.style.display = 'none';

        if (!isPhoneNumberValidOtino(mobileNumber)) {
            phoneInput.style.borderColor = 'red';
            responseDiv.textContent = 'شماره موبایل وارد شده معتبر نیست';
            responseDiv.style.display = 'flex';
            responseDiv.style.backgroundColor = '#ffe2e2';
            responseDiv.style.color = '#750404';
        } else {
            phoneInput.style.borderColor = '';
            mobileForm.classList.add('loading');
            responseDiv.style.display = 'none';
            const formMobileData = mobileForm;
            if (!(formMobileData instanceof HTMLFormElement)) {
                console.error('Form element not found or invalid');
                return;
            }
            var formData = new FormData(formMobileData);
            formData.append('action', 'otino_mobile');
            formData.append('otino_mobile_js', mobileNumber);
            formData.append('security_js', otino_ajax.nonce);
            const xhr = new XMLHttpRequest();
            xhr.open('POST', otino_ajax.ajax_url, true);
            xhr.onload = function () {
                mobileForm.classList.remove('loading');
                responseDiv.style.display = 'flex';
                if (xhr.status >= 200 && xhr.status < 400) {
                    const data = JSON.parse(xhr.responseText);
                    if (data.success && data.success !== "failure") {
                        responseDiv.innerHTML = data.message || 'Request was successful!';
                        phoneInput.value = "";
                        responseDiv.style.backgroundColor = '#d4edda';
                        responseDiv.style.color = '#155724';
                        if (data.data.action == 'otpActive') {
                            if (data.data.mobile !== '') {
                                otpForm.querySelector('#otino_mobile_hidden').value = data.data.mobile;
                                otpForm.querySelector('span.otp-message-end-otino > span').innerHTML = data.data.mobile;
                            }
                            mobileForm.classList.add('otp-active');
                            firstOtpInput.focus();
                            timerOtino.start();
                        }
                    } else {
                        responseDiv.innerHTML = data.error || 'An unexpected error occurred.';
                        responseDiv.style.backgroundColor = '#ffe2e2';
                        responseDiv.style.color = '#750404';
                        if (data.data.action == 'reload') {
                            setTimeout(function() {
                                location.reload();
                            }, 1500);
                        }
                    }
                } else {
                    responseDiv.innerHTML = 'An unexpected server error occurred.';
                    responseDiv.style.backgroundColor = '#ffe2e2';
                    responseDiv.style.color = '#750404';
                }
            };
            xhr.onerror = function () {
                mobileForm.classList.add('loading');
                responseDiv.innerHTML = 'An error occurred while processing your request.';
                responseDiv.style.display = 'flex';
                responseDiv.style.backgroundColor = '#750404';
                responseDiv.style.color = '#ffe2e2';
            };
            xhr.send(formData);
        }
    });

    otpForm.addEventListener('submit', function (event) {
        event.preventDefault();
        const otpCode = hiddenOtpInput.value.trim();
        responseDiv.style.display = 'none';
        otpForm.classList.add('loading');
        const formOtpData = otpForm;
        if (!(formOtpData instanceof HTMLFormElement)) {
            console.error('Form element not found or invalid');
            return;
        }
        var otpFormData = new FormData(formOtpData);
        otpFormData.append('action', 'otino_otp');
        otpFormData.append('security_js', otino_ajax.nonce);
        const xhrOtp = new XMLHttpRequest();
        xhrOtp.open('POST', otino_ajax.ajax_url, true);

        xhrOtp.onload = function () {
            otpForm.classList.remove('loading');
            responseDiv.style.display = 'flex';

            if (xhrOtp.status >= 200 && xhrOtp.status < 400) {
                const data = JSON.parse(xhrOtp.responseText);
                if (data.data.action == 'reload') {
                    location.reload();
                }
                if (data.data.action == "clearOtp") {
                    const otpInputs = otpForm.querySelectorAll('input[id^="otp-input-"]');
                    otpInputs.forEach(input => {
                        input.value = '';
                    });
                    firstOtpInput.focus();
                }
                if (data.data.action == "mobileActive") {
                    mobileInput.value = '';
                    mobileForm.classList.remove('otp-active');
                    otpForm.classList.remove('loading');
                    mobileInput.focus();
                    const otpInputs = otpForm.querySelectorAll('input[id^="otp-input-"]');
                    otpInputs.forEach(input => {
                        input.value = '';
                    });
                }
                if (data.success && data.success !== "failure") {
                    otpForm.classList.add('loading');
                    responseDiv.innerHTML = data.message || 'Request was successful!';
                    hiddenOtpInput.value = "";
                    responseDiv.style.backgroundColor = '#d4edda';
                    responseDiv.style.color = '#155724';
                    if (data.data.redirect) {
                        window.location.href = data.data.redirect;
                    } else {
                        location.reload();
                    }
                } else {
                    responseDiv.innerHTML = data.error || 'An unexpected error occurred.';
                    responseDiv.style.backgroundColor = '#ffe2e2';
                    responseDiv.style.color = '#750404';
                }
            } else {
                responseDiv.innerHTML = 'An unexpected server error occurred.';
                responseDiv.style.backgroundColor = '#ffe2e2';
                responseDiv.style.color = '#750404';
            }
        };
        xhrOtp.onerror = function () {
            mobileForm.classList.add('loading');
            responseDiv.innerHTML = 'An error occurred while processing your request.';
            responseDiv.style.display = 'flex';
            responseDiv.style.backgroundColor = '#750404';
            responseDiv.style.color = '#ffe2e2';
        };
        xhrOtp.send(otpFormData);
    });

    function TimerOtino(duration) {
        this.duration = duration;
        this.isRunning = false;
        this.els = {
            buttonCountdown: otpForm.querySelector('.otp-time-otino')
        };

        this.start = () => {
            if (this.isRunning) return;
            this.isRunning = true;

            let start = null;
            let remainingSeconds = this.els.buttonCountdown.textContent = this.duration / 1000;

            const draw = (now) => {
                if (!this.isRunning) return;
                if (!start) start = now;
                const diff = now - start;
                const newSeconds = Math.ceil((this.duration - diff) / 1000);

                if (diff <= this.duration) {
                    if (newSeconds !== remainingSeconds) {
                        remainingSeconds = newSeconds;
                        this.els.buttonCountdown.textContent = newSeconds;
                    }
                    window.requestAnimationFrame(draw);
                } else {
                    this.els.buttonCountdown.textContent = 0;
                    mobileForm.classList.remove('otp-active');
                    responseDiv.style.display = 'none';
                    mobileInput.value = document.getElementById('otino_mobile_hidden').value;
                    this.isRunning = false;
                }
            };

            window.requestAnimationFrame(draw);
        };

        this.stop = () => {
            this.isRunning = false;
        };
    }

});