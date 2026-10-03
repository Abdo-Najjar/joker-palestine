<?php
// ==============================================================================
// إعادة ضبط قاعدة البيانات وملفات المشروع (Reset Database & Environment)
// إعداد: الجوكر الفلسطيني احمد سليم
// ==============================================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$db_file = __DIR__ . '/database.sqlite';
$uploads_dir = __DIR__ . '/uploads';

function reset_database() {
    global $db_file, $uploads_dir;

    if (file_exists($db_file)) {
        @unlink($db_file);
    }

    try {
        $db = new PDO('sqlite:' . $db_file);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // 1. جدول المستخدمين (Users)
        $db->exec("CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT UNIQUE NOT NULL,
            password TEXT NOT NULL,
            email TEXT NOT NULL,
            role TEXT DEFAULT 'user',
            bio TEXT,
            avatar TEXT DEFAULT 'default.png',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");

        // إدراج مستخدمين تجريبيين
        // admin (كلمة سر ضعيفة للتخمين وللاختبار: admin123)
        // user1 (pass123)
        // john (secret)
        $users = [
            ['admin', md5('admin123'), 'admin@joker-lab.local', 'admin', 'المدير العام للنظام ومسؤول الحماية والبيانات الحساسة.', 'avatar1.png'],
            ['user1', md5('pass123'), 'user1@joker-lab.local', 'user', 'طالب مجتهد في دورة أمن المعلومات.', 'avatar2.png'],
            ['john', md5('secret'), 'john@test.org', 'user', 'مستخدم عادي في المنصة.', 'default.png'],
            ['supervisor', md5('sup2026'), 'supervisor@lab.edu', 'manager', 'مشرف المختبر التعليمي.', 'default.png']
        ];

        $stmt = $db->prepare("INSERT INTO users (username, password, email, role, bio, avatar) VALUES (?, ?, ?, ?, ?, ?)");
        foreach ($users as $u) {
            $stmt->execute($u);
        }

        // 2. جدول المنتجات والبيانات (Products / Secrets) - لاختبار SQLi UNION
        $db->exec("CREATE TABLE IF NOT EXISTS products (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            category TEXT NOT NULL,
            price REAL NOT NULL,
            description TEXT,
            secret_code TEXT
        )");

        $products = [
            ['لابتوب اختراق أخلاقي Dell XPS', 'أجهزة', 1250.00, 'جهاز عالي المواصفات مجهز بأدوات كالي لينكس لاختبار الاختراق.', 'PROD-001'],
            ['بطاقة شبكة Alfa AWUS036ACH', 'شبكات', 75.00, 'بطاقة وايفاي تدعم وضع المراقبة وحقن الحزم Packet Injection.', 'PROD-002'],
            ['راسبيري باي 4 (Raspberry Pi 4)', 'أجهزة مصغرة', 90.00, 'كمبيوتر صغير مناسب لإنشاء أجهزة الاختراق الميدانية Dropboxes.', 'PROD-003'],
            ['كتاب دليل فحص الويب الشامل', 'كتب', 35.00, 'مرجع باللغة العربية يشرح أهم ثغرات OWASP Top 10.', 'FLAG{SQLi_Union_Extract_Secret_4812}']
        ];

        $stmt = $db->prepare("INSERT INTO products (name, category, price, description, secret_code) VALUES (?, ?, ?, ?, ?)");
        foreach ($products as $p) {
            $stmt->execute($p);
        }

        // 3. جدول التعليقات (Comments) - لاختبار Stored XSS
        $db->exec("CREATE TABLE IF NOT EXISTS comments (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            author TEXT NOT NULL,
            comment TEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");

        $comments = [
            ['سالم الكعبي', 'مشروع ممتاز جداً ومفيد لكل المهتمين بالأمن السيبراني!'],
            ['احمد سليم', 'أهلاً بكم في مختبر الجوكر الأمني، نتمنى لكم تجربة تعليمية ممتعة.']
        ];
        $stmt = $db->prepare("INSERT INTO comments (author, comment) VALUES (?, ?)");
        foreach ($comments as $c) {
            $stmt->execute($c);
        }

        // 4. جدول الرسائل الخاصة (Messages) - لاختبار IDOR
        $db->exec("CREATE TABLE IF NOT EXISTS messages (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            sender_id INTEGER,
            receiver_id INTEGER,
            subject TEXT NOT NULL,
            message TEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");

        $messages = [
            [1, 2, 'مرحباً بك في المختبر', 'أهلاً بك user1، نتمنى لك التوفيق في الدورة.'],
            [1, 1, 'بيانات سرية للإدارة فقط [هام جداً]', 'عزيزي المشرف، هذا التقرير الأمني يحتوي على العلم السري: FLAG{IDOR_Unauthorized_Access_6619}'],
            [2, 1, 'استفسار عن التحدي', 'السلام عليكم استاذ احمد، هل يمكن شرح ثغرة LFI بالتفصيل؟']
        ];
        $stmt = $db->prepare("INSERT INTO messages (sender_id, receiver_id, subject, message) VALUES (?, ?, ?, ?)");
        foreach ($messages as $m) {
            $stmt->execute($m);
        }

        // تنظيف مجلد الرفع (Uploads)
        if (is_dir($uploads_dir)) {
            $files = glob($uploads_dir . '/*');
            foreach ($files as $file) {
                if (is_file($file) && basename($file) !== '.gitkeep' && basename($file) !== 'default.png') {
                    @unlink($file);
                }
            }
        } else {
            @mkdir($uploads_dir, 0777, true);
        }

        // إنشاء ملف افتراضي وملاحظة سرية لـ LFI
        $notes_file = __DIR__ . '/pages/secret_note.txt';
        file_put_contents($notes_file, "ملف الملاحظات السرية للخادم:\n========================\nهذا الملف يحتوي على بيانات حساسة تابعة للنظام الداخلي.\nالعلم السري: FLAG{LFI_Local_File_Read_Exposed_8821}\nتم الحفظ بواسطة: الجوكر الفلسطيني احمد سليم.\n");

        return true;
    } catch (PDOException $e) {
        return $e->getMessage();
    }
}

// تنفيذ الطلب إذا كان نداءً مباشراً
$msg = "";
if (isset($_GET['action']) && $_GET['action'] === 'reset') {
    $res = reset_database();
    if (isset($_GET['clear_flags']) && $_GET['clear_flags'] == '1') {
        $_SESSION['solved_flags'] = [];
    }
    if ($res === true) {
        $msg = "تمت إعادة ضبط قاعدة البيانات وملفات المشروع بنجاح 100%!";
    } else {
        $msg = "حدث خطأ أثناء إعادة الضبط: " . htmlspecialchars($res);
    }
}

// إذا استدعي كدالة فقط من ملف آخر
if (!isset($_SERVER['SCRIPT_FILENAME']) || realpath($_SERVER['SCRIPT_FILENAME']) !== realpath(__FILE__)) {
    return;
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إعادة ضبط المختبر | مختبر الجوكر الأمني</title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="assets/images/joker_logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Cairo', sans-serif; background-color: #0f172a; color: #f8fafc; }
        .card { background-color: #1e293b; border-color: #334155; }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center min-vh-100">
    <div class="container" style="max-width: 580px;">
        <div class="card shadow-lg text-center p-4">
            <div class="mb-3">
                <img src="assets/images/joker_logo.png" alt="الجوكر الفلسطيني" class="rounded-circle border border-info mb-3" style="width: 72px; height: 72px; object-fit: cover; box-shadow: 0 0 15px rgba(6, 182, 212, 0.4);">
                <h3 class="fw-bold text-white">إعادة ضبط مختبر الجوكر الأمني</h3>
                <p class="text-secondary small">إعادة بناء قاعدة البيانات الافتراضية وحذف الملفات المرفوعة</p>
            </div>

            <?php if (!empty($msg)): ?>
                <div class="alert alert-success d-flex align-items-center mb-4">
                    <i class="fas fa-check-circle fs-4 me-2"></i>
                    <div><?= $msg ?></div>
                </div>
            <?php endif; ?>

            <div class="d-grid gap-2">
                <a href="reset.php?action=reset" class="btn btn-warning btn-lg fw-bold">
                    <i class="fas fa-database me-2"></i> إعادة ضبط قاعدة البيانات فقط
                </a>
                <a href="reset.php?action=reset&clear_flags=1" class="btn btn-danger btn-lg fw-bold">
                    <i class="fas fa-trash-alt me-2"></i> إعادة ضبط كاملة (مع تصفير الأعلام المحلولة)
                </a>
                <a href="index.php" class="btn btn-outline-light mt-2">
                    <i class="fas fa-arrow-right me-2"></i> العودة إلى اللوحة الرئيسية
                </a>
            </div>

            <div class="mt-4 pt-3 border-top border-secondary text-secondary small">
                إعداد: <strong class="text-info">الجوكر الفلسطيني احمد سليم</strong>
            </div>
        </div>
    </div>
</body>
</html>
