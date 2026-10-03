// مختبر الجوكر الأمني - ملف الجافاسكربت العام
document.addEventListener('DOMContentLoaded', function () {
    // تفعيل الـ Tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // نسخ الأعلام إلى الحافظة بنقرة واحدة
    document.querySelectorAll('.copy-flag-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var text = this.getAttribute('data-flag');
            navigator.clipboard.writeText(text).then(function() {
                var oldHtml = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-check text-success"></i> تم النسخ!';
                setTimeout(function() {
                    btn.innerHTML = oldHtml;
                }, 2000);
            });
        });
    });
});
