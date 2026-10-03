<?php
$page_title = "رفع الملفات غير الآمن (Unrestricted File Upload) | مختبر الجوكر الأمني";
require_once __DIR__ . '/../includes/header.php';

$sec = get_security_level();
$upload_msg = "";
$uploaded_file_url = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['avatar'])) {
    $file = $_FILES['avatar'];
    $filename = $file['name'];
    $tmp_name = $file['tmp_name'];
    $error = $file['error'];

    if ($error === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if ($sec === 'low') {
            // كود مصاب: لا يوجد فحص للامتداد أو نوع الملف
            $target_dest = UPLOADS_DIR . '/' . $filename;
            if (move_uploaded_file($tmp_name, $target_dest)) {
                $uploaded_file_url = "../uploads/" . htmlspecialchars($filename);
                $upload_msg = "<div class='alert alert-success'><i class='fas fa-check-circle me-1'></i> تم رفع الملف بنجاح إلى: <a href='$uploaded_file_url' target='_blank' class='fw-bold text-dark text-decoration-underline'>$uploaded_file_url</a></div>";

                if (in_array($ext, ['php', 'phtml', 'php5', 'php7', 'phar'])) {
                    award_flag('upload');
                }
            } else {
                $upload_msg = "<div class='alert alert-danger'>فشل نقل الملف المرفوع! تأكد من صلاحيات المجلد.</div>";
            }
        } else {
            // كود آمن: فحص القائمة البيضاء للامتدادات، فحص MIME Type، وإعادة تسمية الملف
            $allowed_exts = ['jpg', 'jpeg', 'png', 'gif'];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $tmp_name);
            finfo_close($finfo);

            $allowed_mimes = ['image/jpeg', 'image/png', 'image/gif'];

            if (in_array($ext, $allowed_exts) && in_array($mime, $allowed_mimes)) {
                $new_filename = bin2hex(random_bytes(10)) . '.' . $ext;
                $target_dest = UPLOADS_DIR . '/' . $new_filename;
                move_uploaded_file($tmp_name, $target_dest);
                $uploaded_file_url = "../uploads/" . $new_filename;
                $upload_msg = "<div class='alert alert-success'><i class='fas fa-shield-alt me-1'></i> تم فحص الصورة والتأكد من أمانها وتغيير اسمها بنجاح.</div>";
            } else {
                $upload_msg = "<div class='alert alert-danger'><i class='fas fa-ban me-1'></i> تم حظر الملف! يُسمح فقط بالصور بصيغ (JPG, PNG, GIF) ولا تُقبل أي سكربتات برمجية.</div>";
            }
        }
    } else {
        $upload_msg = "<div class='alert alert-warning'>حدث خطأ أثناء رفع الملف. كود الخطأ: $error</div>";
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom border-secondary">
    <div>
        <h2 class="fw-bold text-white mb-1"><i class="fas fa-upload text-success me-2"></i> 4. رفع الملفات غير الآمن (File Upload Vulnerability)</h2>
        <p class="text-muted mb-0">تعتبر ثغرات رفع الملفات من أخطر الثغرات (Critical)، حيث تتيح للمهاجم رفع سكربت بلغة PHP (Web Shell) وتشغيله للسيطرة الكاملة على السيرفر (RCE).</p>
    </div>
    <span class="badge bg-danger fs-6 px-3 py-2"><i class="fas fa-skull-crossbones me-1"></i> تؤدي إلى Remote Code Execution</span>
</div>

<div class="card card-cyber mb-4">
    <div class="card-cyber-header d-flex justify-content-between align-items-center">
        <h4 class="mb-0 text-info"><i class="fas fa-image me-2"></i> نموذج رفع الصورة الشخصية (Avatar Upload)</h4>
        <span class="badge bg-secondary">تحدي Web Shell</span>
    </div>
    <div class="card-body p-4">
        <p class="text-light">
            <strong>الهدف:</strong> قم برفع ملف بصيغة <code>.php</code> لتنفيذ أوامر أو فحص إعدادات الخادم والحصول على العلم.
        </p>

        <?= render_flag_box('upload'); ?>
        <?= $upload_msg; ?>

        <form method="POST" enctype="multipart/form-data" class="p-3 bg-dark rounded border border-secondary mb-4" style="max-width: 600px;">
            <div class="mb-3">
                <label class="form-label text-light">اختر ملفاً لرفعه:</label>
                <input type="file" name="avatar" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-success fw-bold"><i class="fas fa-cloud-upload-alt me-1"></i> رفع الملف الآن</button>
        </form>

        <?= render_hints([
            "أنشئ ملفاً على جهازك باسم <code>shell.php</code> وضع بداخله: <code>&lt;?php phpinfo(); ?&gt;</code> أو <code>&lt;?php echo 'Hello RCE'; ?&gt;</code>",
            "قم برفع الملف ثم اضغط على الرابط الذي سيظهر في رسالة النجاح لتشغيله في المتصفح.",
            "عند رفع أي ملف بامتداد php في المستوى الضعيف (Low)، ستتحصل على العلم فوراً."
        ]); ?>

        <?= render_code_comparison(
            '// كود مصاب: رفع مباشر دون تحقق\n$target = "uploads/" . $_FILES["avatar"]["name"];\nmove_uploaded_file($_FILES["avatar"]["tmp_name"], $target);',
            '// كود آمن: فحص الامتداد والنوع وتغيير الاسم\n$ext = strtolower(pathinfo($_FILES["avatar"]["name"], PATHINFO_EXTENSION));\n$allowed = ["jpg", "jpeg", "png", "gif"];\n$finfo = finfo_open(FILEINFO_MIME_TYPE);\n$mime = finfo_file($finfo, $_FILES["avatar"]["tmp_name"]);\nif (in_array($ext, $allowed) && in_array($mime, ["image/jpeg", "image/png"])) {\n    $new_name = bin2hex(random_bytes(10)) . "." . $ext;\n    move_uploaded_file($_FILES["avatar"]["tmp_name"], "uploads/" . $new_name);\n}',
            'الحماية الحقيقية تتطلب: فحص الامتداد بقائمة بيضاء صارمة، فحص نوع المحتوى الحقيقي (MIME Type)، توليد اسم عشوائي للملف لمنع استدعاءه بسهولة، ومنع تنفيذ الـ PHP في مجلد الرفع عبر ملف .htaccess.'
        ); ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
