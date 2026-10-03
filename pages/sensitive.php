<?php
$page_title = "كشف البيانات الحساسة وقاعدة البيانات | مختبر الجوكر الأمني";
require_once __DIR__ . '/../includes/header.php';

$sec = get_security_level();

if (isset($_GET['download']) && $_GET['download'] === 'db') {
    if ($sec === 'low') {
        award_flag('sensitive');
        $file = DB_FILE;
        if (file_exists($file)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/x-sqlite3');
            header('Content-Disposition: attachment; filename="database.sqlite"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file));
            readfile($file);
            exit;
        }
    } else {
        header("HTTP/1.1 403 Forbidden");
        die("<div style='color:red;padding:30px;font-family:sans-serif;'><h2>403 Forbidden - Access Denied</h2>تم حظر الوصول المباشر لقاعدة البيانات عبر قواعد .htaccess ومستوى الحماية الآمن!</div>");
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom border-secondary">
    <div>
        <h2 class="fw-bold text-white mb-1"><i class="fas fa-user-secret text-secondary me-2"></i> 8. كشف البيانات الحساسة وقاعدة البيانات (Sensitive Data Exposure)</h2>
        <p class="text-muted mb-0">من الأخطاء الكارثية في مشاريع SQLite هو وضع ملف قاعدة البيانات داخل المجلد العام للموقع (Web Root) مما يتيح تحميلها بالكامل مباشرة عبر المتصفح.</p>
    </div>
    <span class="badge bg-danger fs-6 px-3 py-2"><i class="fas fa-database me-1"></i> تسريب كامل لقاعدة البيانات</span>
</div>

<div class="card card-cyber mb-4">
    <div class="card-cyber-header d-flex justify-content-between align-items-center">
        <h4 class="mb-0 text-info"><i class="fas fa-download me-2"></i> تحميل ملف قاعدة بيانات SQLite المسرب</h4>
        <span class="badge bg-secondary">تحدي كشف البيانات</span>
    </div>
    <div class="card-body p-4">
        <p class="text-light">
            <strong>السيناريو:</strong> قام المبرمج بتسمية قاعدة البيانات <code>database.sqlite</code> في المجلد الرئيسي، ولم يقم بحمايتها في إعدادات السيرفر (Apache / Nginx).
        </p>

        <?= render_flag_box('sensitive'); ?>

        <div class="p-3 bg-dark rounded border border-secondary mb-4" style="max-width: 650px;">
            <h5 class="text-white mb-2"><i class="fas fa-link text-warning me-2"></i> الرابط المكشوف لقاعدة البيانات:</h5>
            <div class="input-group mb-3">
                <input type="text" class="form-control font-monospace" value="../database.sqlite" readonly>
                <a href="sensitive.php?download=db" class="btn btn-danger fw-bold"><i class="fas fa-file-download me-1"></i> تحميل قاعدة البيانات</a>
            </div>
            <small class="text-muted">
                بمجرد تحميل الملف، يمكن فتحه بأي برنامج مثل <strong>DB Browser for SQLite</strong> لرؤية كل كلمات المرور المشفرة بـ MD5 وبيانات المستخدمين والرسائل السرية!
            </small>
        </div>

        <?= render_hints([
            "اضغط على زر (تحميل قاعدة البيانات) لمحاكاة قيام مهاجم بطلب ملف <code>database.sqlite</code> مباشرة.",
            "ستلاحظ أن السيرفر يسمح بتحميل الملف فوراً، وبداخله جدول كامل للأعلام والمستخدمين!",
            "عند التحميل في المستوى الضعيف (Low)، ستتحصل على العلم."
        ]); ?>

        <?= render_code_comparison(
            '# إعداد خاطئ: ترك ملفات SQLite في المجلد العام public_html\n# http://example.com/database.sqlite -> متاح للتحميل للجميع!',
            '# حماية صحيحة عبر ملف .htaccess في سيرفر Apache:\n<FilesMatch "\.(sqlite|db|env|git|bak|log)$">\n    Require all denied\n</FilesMatch>\n\n// أو الأفضل برمجياً: وضع قاعدة البيانات خارج مجلد الويب:\n// define("DB_FILE", "/var/www/private_data/database.sqlite");',
            'القاعدة الذهبية: لا تضع أي ملفات قواعد بيانات أو إعدادات (.env, .sqlite, .git, backups) في المسار العام للموقع، واحمِ الامتدادات الحساسة عبر إعدادات الويب سيرفر.'
        ); ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
