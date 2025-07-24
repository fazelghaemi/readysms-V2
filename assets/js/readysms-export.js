jQuery(document).ready(function($) {
    $('#readysms-export-form').on('submit', function(e) {
        e.preventDefault();

        var form = $(this);
        var exportBtn = $('#readysms-export-btn');
        var loader = $('#readysms-loader');
        var resultDiv = $('#readysms-export-result');
        var formData = form.serialize();

        exportBtn.prop('disabled', true);
        loader.slideDown();
        resultDiv.slideUp().empty();

        $.ajax({
            url: readysmsAjax.url, // Localized variable from PHP
            type: 'POST',
            data: {
                action: readysmsAjax.action, // 'readysms_generate_export'
                nonce: readysmsAjax.nonce,   // Security nonce
                filters: formData
            },
            success: function(response) {
                if (response.success) {
                    var successMessage = '<div style="background-color: #e7f7e7; border-left: 4px solid #4CAF50; padding: 12px;">';
                    successMessage += '<p>فایل خروجی با موفقیت ساخته شد. (' + response.data.row_count + ' کاربر)</p>';
                    successMessage += '<a href="' + response.data.file_url + '" class="button button-primary" download>دانلود فایل CSV</a>';
                    successMessage += '</div>';
                    resultDiv.html(successMessage).slideDown();
                } else {
                    var errorMessage = '<div style="background-color: #fff1f1; border-left: 4px solid #d9534f; padding: 12px;">';
                    errorMessage += '<p><strong>خطا:</strong> ' + response.data.message + '</p>';
                    errorMessage += '</div>';
                    resultDiv.html(errorMessage).slideDown();
                }
            },
            error: function() {
                var errorMessage = '<div style="background-color: #fff1f1; border-left: 4px solid #d9534f; padding: 12px;">';
                errorMessage += '<p><strong>خطا:</strong> یک خطای ناشناخته در ارتباط با سرور رخ داد.</p>';
                errorMessage += '</div>';
                resultDiv.html(errorMessage).slideDown();
            },
            complete: function() {
                exportBtn.prop('disabled', false);
                loader.slideUp();
            }
        });
    });
});