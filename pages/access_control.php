<?php
$page_title = "التحكم في الصلاحيات والكوكي (Broken Access Control) | مختبر الجوكر الأمني";
require_once __DIR__ . '/../includes/header.php';

$sec = get_security_level();

// في الوضع الضعيف، يتم الاعتماد على Cookie قادم من المتصفح
if (!isset($_COOKIE['user_role'])) {
    setcookie('user_role', 'student', time() + 3600, '/');
    $_COOKIE['user_role'] = 'student';
}

if (isset($_GET['set_role'])) {
    $r = $_GET['set_role'];
    setcookie('user_role', $r, time() + 3600, '/');
    $_COOKIE['user_role'] = $r;
    header("Location: access_control.php");
    exit;
}

$role = $_COOKIE['user_role'] ?? 'student';
$is_admin = false;

if ($sec === 'low') {
    // كود مصاب: الثقة الكاملة في الكوكي المرسل من متصفح العميل
    if ($role === 'admin') {
        $is_admin = true;
        award_flag('access_control');
    }
} else {
    // كود آمن: تخزين الصلاحيات في جلسة السيرفر المحمية فقط
    // وتجاهل أي قيم قادمة من الكوكي
    $is_admin = false; // لا يمكن رفع الصلاحية بالكوكي
}
?>

<div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom border-secondary">
    <div>
        <h2 class="fw-bold text-white mb-1"><i class="fas fa-cookie-bite text-warning me-2"></i> 13. التلاعب بالصلاحيات وملفات الكوكي (Cookie Manipulation)</h2>
        <p class="text-muted mb-0">تحدث ثغرة Broken Access Control عند تخزين رتبة المستخدم أو صلاحياته في جانب العميل (Client-Side Cookie) دون توقيع رقمي أو تشفير، مما يتيح التلاعب بها وتصعيد الصلاحيات (Privilege Escalation).</p>
    </div>
    <span class="badge bg-warning text-dark fs-6 px-3 py-2"><i class="fas fa-user-shield me-1"></i> Privilege Escalation</span>
</div>

<div class="card card-cyber mb-4">
    <div class="card-cyber-header d-flex justify-content-between align-items-center">
        <h4 class="mb-0 text-info"><i class="fas fa-tools me-2"></i> لوحة الإدارة المحمية بالصلاحيات (Admin Control Area)</h4>
        <span class="badge bg-secondary">تحدي الصلاحيات</span>
    </div>
    <div class="card-body p-4">
        <p class="text-light">
            <strong>الهدف:</strong> التلاعب بقيمة ملف تعريف الارتباط (Cookie) المسمى <code>user_role</code> وتغييره من <code>student</code> إلى <code>admin</code> لدخول لوحة التحكم والحصول على العلم.
        </p>

        <?= render_flag_box('access_control'); ?>

        <div class="alert alert-dark border-secondary p-3 mb-4" style="max-width: 650px;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <i class="fas fa-id-badge text-info me-2"></i> رتبتك الحالية المسجلة في المتصفح: 
                    <strong class="fs-5 <?= $role === 'admin' ? 'text-success' : 'text-warning' ?>"><?= htmlspecialchars($role) ?></strong>
                </div>
                <span class="badge <?= $role === 'admin' ? 'bg-success' : 'bg-secondary' ?>"><?= $role === 'admin' ? 'مدير عام (Admin)' : 'طالب (Student)' ?></span>
            </div>
        </div>

        <?php if ($is_admin): ?>
            <div class="card bg-success bg-opacity-10 border border-success p-4 mb-4 text-center">
                <h3 class="text-success fw-bold"><i class="fas fa-crown text-warning me-2"></i> مرحباً بك في لوحة تحكم المدير العام!</h3>
                <p class="text-light mb-0">لقد نجحت في تصعيد صلاحياتك عبر التلاعب بالكوكي (Privilege Escalation)! تم منحك صلاحيات المدير الكاملة.</p>
            </div>
        <?php else: ?>
            <div class="card bg-danger bg-opacity-10 border border-danger p-4 mb-4 text-center">
                <h4 class="text-danger fw-bold"><i class="fas fa-lock me-2"></i> منطقة مقيدة للمدراء فقط!</h4>
                <p class="text-light mb-0">عذراً، رتبتك الحالية (<?= htmlspecialchars($role) ?>) لا تملك صلاحية الوصول إلى هذه اللوحة. يجب أن تكون رتبتك <code>admin</code>.</p>
            </div>
        <?php endif; ?>

        <div class="p-3 bg-dark rounded border border-secondary mb-4" style="max-width: 650px;">
            <h6 class="text-info fw-bold mb-2"><i class="fas fa-magic me-1"></i> أزرار محاكاة التلاعب بالكوكي (أو عدّلها من F12 DevTools):</h6>
            <div class="d-flex gap-2">
                <a href="access_control.php?set_role=admin" class="btn btn-warning fw-bold btn-sm"><i class="fas fa-user-shield me-1"></i> تعديل الكوكي إلى: admin</a>
                <a href="access_control.php?set_role=student" class="btn btn-outline-secondary btn-sm"><i class="fas fa-user-graduate me-1"></i> إعادة الكوكي إلى: student</a>
            </div>
        </div>

        <?= render_hints([
            "افتح أدوات المطورين في المتصفح بالضغط على <code>F12</code>.",
            "توجه إلى تبويب <strong>Application</strong> (أو <strong>Storage</strong> في فايرفوكس) ثم اختر <strong>Cookies</strong>.",
            "ابحث عن الكوكي باسم <code>user_role</code> وعدّل قيمته من <code>student</code> إلى <code>admin</code> ثم أعد تحديث الصفحة!"
        ]); ?>

        <?= render_code_comparison(
            '// كود مصاب: الاعتماد على قيمة الكوكي المباشرة\n$role = $_COOKIE["user_role"];\nif ($role == "admin") {\n    show_admin_panel();\n}',
            '// كود آمن: تخزين الصلاحيات في Session السيرفر مع HttpOnly\nif (!isset($_SESSION["logged_user"]) || $_SESSION["logged_user"]["role"] !== "admin") {\n    header("HTTP/1.1 403 Forbidden");\n    die("Access Denied");\n}',
            'لا تعتمد أبداً على بيانات يرسلها العميل للتحقق من هويته أو صلاحياته. الرتب والصلاحيات يجب أن تُحفظ في الـ Server Session ويُمنح المتصفح فقط معرف جلسة عشوائي (Session ID) محمي بخاصية HttpOnly لمنع سرقته عبر XSS.'
        ); ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
