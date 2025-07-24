document.addEventListener('DOMContentLoaded', function() {
    const exportBtn = document.getElementById('otino-export-btn');
    const loader = document.getElementById('otino-loader');
    const resultBox = document.getElementById('otino-export-result');
    const form = document.getElementById('otino-export-form');

    exportBtn.addEventListener('click', function() {
        const formData = new FormData(form);
        loader.style.display = 'block';
        resultBox.innerHTML = '';

        const params = new URLSearchParams();
        for (const [key, value] of formData.entries()) {
            params.append(key, value);
        }

        fetch(otinoAjax.url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: 'action=otino_generate_export&data=' + encodeURIComponent(params.toString())
        })
        .then(response => response.json())
        .then(response => {
            loader.style.display = 'none';
            if (response.success) {
                resultBox.innerHTML = `
                    <div class="notice notice-success">
                        <p>✅ خروجی آماده شد. تعداد کاربران یافت شده: ${response.data.rows}
                        <a href="${response.data.file}" class="button button-primary" download>دانلود فایل</a></p>
                    </div>`;
            } else {
                resultBox.innerHTML = `
                    <div class="notice notice-error">
                        <p>${response.data}</p>
                    </div>`;
            }
        })
        .catch(error => {
            loader.style.display = 'none';
            resultBox.innerHTML = `
                <div class="notice notice-error">
                    <p>خطا در ارتباط با سرور!</p>
                </div>`;
            console.error('Error:', error);
        });
    });
});
