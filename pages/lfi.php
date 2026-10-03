<?php
$page_title = "تضمين الملفات واجتياز المسارات (LFI & Path Traversal) | مختبر الجوكر الأمني";
require_once __DIR__ . '/../includes/header.php';

$sec = get_security_level();
$file = $_GET['file'] ?? 'about.txt';
$included_content = "";
$error_msg = "";

if (isset($_GET['file'])) {
    if ($sec === 'low') {
        // كود مصاب: تمرير المسار مباشرة دون فحص
        if (file_exists($file) || strpos($file, 'php://') === 0 || file_exists(__DIR__ . '/' . $file)) {
            $target = (file_exists($file) || strpos($file, 'php://') === 0) ? $file : (__DIR__ . '/' . $file);
            ob_start();
            @include($target);
            $included_content = ob_get_clean();

            if (strpos($included_content, 'FLAG{LFI_Local_File_Read_Exposed_8821}') !== false) {
                award_flag('lfi');
            }
        } else {
            $error_msg = "الملف غير موجود في المسار المحدد: " . htmlspecialchars($file);
        }
    } else {
        // كود آمن: القائمة البيضاء واستخدام basename
        $allowed = ['about.txt', 'contact.txt'];
        $clean_file = basename($file);
        if (in_array($clean_file, $allowed)) {
            $included_content = file_get_contents(__DIR__ . '/' . $clean_file);
        } else {
            $error_msg = "تم رفض الوصول: الملف غير مصرح به في القائمة البيضاء (Access Denied - Whitelist Protection).";
        }
    }
} else {
    $included_content = file_get_contents(__DIR__ . '/about.txt');
}
?>

<div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom border-secondary">
    <div>
        <h2 class="fw-bold text-white mb-1"><i class="fas fa-folder-open text-danger me-2"></i> 3. تضمين الملفات المحلية واجتياز المسارات (LFI)</h2>
        <p class="text-muted mb-0">تحدث ثغرة Local File Inclusion عندما يستخدم التطبيق دوال مثل <code>include</code> أو <code>require</code> مع مدخلات خارجية دون التحقق من المسار، مما يسمح بقراءة ملفات السيرفر أو تنفيذ أكواد.</p>
    </div>
    <span class="badge bg-danger fs-6 px-3 py-2"><i class="fas fa-file-code me-1"></i> ثغرة عالية الخطورة</span>
</div>

<div class="card card-cyber mb-4">
    <div class="card-cyber-header d-flex justify-content-between align-items-center">
        <h4 class="mb-0 text-info"><i class="fas fa-file-alt me-2"></i> مستعرض الصفحات (Page Viewer)</h4>
        <span class="badge bg-secondary">تحدي LFI</span>
    </div>
    <div class="card-body p-4">
        <p class="text-light">
            <strong>الهدف:</strong> استغل معامل <code>?file=</code> لقراءة الملف السري المخفي <code>secret_note.txt</code> أو ملفات النظام للحصول على العلم.
        </p>

        <?= render_flag_box('lfi'); ?>

        <div class="mb-3 d-flex gap-2">
            <a href="lfi.php?file=about.txt" class="btn btn-outline-info btn-sm fw-bold">صفحة من نحن (about.txt)</a>
            <a href="lfi.php?file=contact.txt" class="btn btn-outline-info btn-sm fw-bold">صفحة الاتصال (contact.txt)</a>
        </div>

        <form method="GET" class="row g-2 mb-3" style="max-width: 650px;">
            <div class="col-8">
                <input type="text" id="lfi-input" name="file" dir="ltr" class="form-control font-monospace" style="direction:ltr; text-align:left;" value="<?= htmlspecialchars($file) ?>" placeholder="secret_note.txt">
                <div class="mt-2 d-flex flex-wrap align-items-center gap-1">
                    <small class="text-muted me-1">تجارب سريعة:</small>
                    <button type="button" class="btn btn-outline-info btn-sm py-0 px-2 font-monospace" onclick="document.getElementById('lfi-input').value='secret_note.txt';">secret_note.txt</button>
                    <button type="button" class="btn btn-outline-info btn-sm py-0 px-2 font-monospace" onclick="document.getElementById('lfi-input').value='../../../../Windows/win.ini';">win.ini</button>
                    <button type="button" class="btn btn-outline-info btn-sm py-0 px-2 font-monospace" onclick="document.getElementById('lfi-input').value='php://filter/convert.base64-encode/resource=about.txt';">php://filter</button>
                </div>
            </div>
            <div class="col-4">
                <button type="submit" class="btn btn-danger fw-bold w-100"><i class="fas fa-folder-open me-1"></i> تحميل وتضمين</button>
            </div>
        </form>

        <?php if (!empty($error_msg)): ?>
            <div class="alert alert-danger py-2"><?= $error_msg ?></div>
        <?php endif; ?>

        <div class="card bg-dark border-secondary mt-3">
            <div class="card-header bg-black text-white d-flex justify-content-between align-items-center py-2">
                <span><i class="fas fa-file-code text-warning me-2"></i> محتوى الملف المضمّن:</span>
                <span class="badge bg-secondary font-monospace"><?= htmlspecialchars($file) ?></span>
            </div>
            <div class="card-body p-3">
                <pre class="mb-0 text-light" style="direction:ltr; text-align:left; white-space:pre-wrap;"><code><?= htmlspecialchars($included_content) ?></code></pre>
            </div>
        </div>
    </div>
</div>

<?= render_hints([
    "يوجد ملف سري في نفس المجلد اسمه <code>secret_note.txt</code>، جرب طلبه مباشرة في المعامل: <code>?file=secret_note.txt</code>.",
    "جرب استخدام Path Traversal للخروج من المجلد: <code>?file=../../../../Windows/win.ini</code> (على ويندوز) أو <code>/etc/passwd</code> (على لينكس).",
    "يمكنك أيضاً تجربة مشغلات PHP المتقدمة لقراءة الأكواد المصدرية مثل: <code class='text-warning'>php://filter/convert.base64-encode/resource=sqli.php</code>."
]); ?>

<?= render_code_comparison(
    '// كود مصاب\n$file = $_GET["file"];\ninclude($file);',
    '// كود آمن (القائمة البيضاء + دالة basename)\n$allowed = ["about.txt", "contact.txt"];\n$clean = basename($_GET["file"]);\nif (in_array($clean, $allowed)) {\n    include($clean);\n} else {\n    die("Access Denied");\n}',
    'أفضل حل لمنع LFI و Path Traversal هو التحقق الصارم عبر القائمة البيضاء (Whitelist) واستخدام دالة basename() لتجريد كل المسارات مثل ../'
); ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
