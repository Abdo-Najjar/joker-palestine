<?php
$page_title = "التخمين على كلمات المرور (Brute Force) | مختبر الجوكر الفلسطيني";
require_once __DIR__ . '/../includes/header.php';

$sec = get_security_level();
$msg = "";

if (!isset($_SESSION['failed_logins'])) {
    $_SESSION['failed_logins'] = 0;
    $_SESSION['lockout_time'] = 0;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username'])) {
    $user = trim($_POST['username']);
    $pass = trim($_POST['password']);

    // فحص الإغلاق المؤقت في الوضع الآمن
    if ($sec === 'secure') {
        if ($_SESSION['lockout_time'] > time()) {
            $remaining = $_SESSION['lockout_time'] - time();
            $msg = "<div class='alert alert-danger'><i class='fas fa-lock me-1'></i> تم قفل الحساب مؤقتاً بسبب تكرار المحاولات الخاطئة! الرجاء الانتظار {$remaining} ثانية.</div>";
        }
    }

    if (empty($msg)) {
        if ($user === 'admin' && $pass === 'admin123') {
            $_SESSION['failed_logins'] = 0;
            $msg = "<div class='alert alert-success'><i class='fas fa-check-circle me-1'></i> تم تسجيل الدخول بنجاح! تم اختراق الحساب عبر التخمين.</div>";
            award_flag('bruteforce');
        } else {
            $_SESSION['failed_logins']++;
            if ($sec === 'secure' && $_SESSION['failed_logins'] >= 3) {
                $_SESSION['lockout_time'] = time() + 30; // قفل لمدة 30 ثانية
                $msg = "<div class='alert alert-danger'><i class='fas fa-shield-alt me-1'></i> تم تجاوز الحد المسموح من المحاولات (3 محاولات). تم تفعيل Rate Limiting وقفل الحساب.</div>";
            } else {
                $msg = "<div class='alert alert-danger'><i class='fas fa-times-circle me-1'></i> كلمة المرور غير صحيحة! (المحاولة رقم: {$_SESSION['failed_logins']})</div>";
            }
        }
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom border-secondary">
    <div>
        <h2 class="fw-bold text-white mb-1"><i class="fas fa-key text-danger me-2"></i> 11. هجمات القوة الغاشمة والتخمين (Brute Force Attacks)</h2>
        <p class="text-muted mb-0">تحدث عند غياب آليات كبح المحاولات (Rate Limiting) واختبار الكابتشا (CAPTCHA)، مما يتيح للمهاجمين تجربة آلاف كلمات المرور عبر قواميس مثل RockYou.</p>
    </div>
    <span class="badge bg-warning text-dark fs-6 px-3 py-2"><i class="fas fa-stopwatch me-1"></i> Missing Rate Limiting</span>
</div>

<div class="card card-cyber mb-4">
    <div class="card-cyber-header d-flex justify-content-between align-items-center">
        <h4 class="mb-0 text-info"><i class="fas fa-unlock-alt me-2"></i> شاشة تسجيل الدخول المستهدفة بالتخمين</h4>
        <span class="badge bg-secondary">تحدي Brute Force</span>
    </div>
    <div class="card-body p-4">
        <p class="text-light">
            <strong>الهدف:</strong> التخمين على كلمة مرور حساب المدير (admin) باستخدام قائمة كلمات مرور شائعة (Wordlist) للحصول على العلم.
        </p>

        <?= render_flag_box('bruteforce'); ?>
        <?= $msg; ?>

        <div class="row">
            <div class="col-md-6 mb-4">
                <form method="POST" class="p-3 bg-dark rounded border border-secondary">
                    <div class="mb-3">
                        <label class="form-label text-light">اسم المستخدم:</label>
                        <input type="text" name="username" class="form-control" value="admin" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-light">كلمة المرور:</label>
                        <input type="password" id="pass-field" name="password" class="form-control" placeholder="جرب التخمين..." required>
                    </div>
                    <button type="submit" class="btn btn-danger fw-bold"><i class="fas fa-sign-in-alt me-1"></i> تسجيل الدخول</button>
                </form>
            </div>

            <div class="col-md-6">
                <div class="p-3 bg-dark rounded border border-secondary">
                    <h5 class="text-info mb-2"><i class="fas fa-book me-1"></i> قاموس تجريبي مصغر (Mini Wordlist):</h5>
                    <p class="text-muted small">اضغط على أي كلمة لتجربتها مباشرة أو استخدامها مع Burp Suite / Hydra:</p>
                    <div class="d-flex flex-wrap gap-2">
                        <?php
                        $sample_words = ['123456', 'password', 'qwerty', 'admin', 'admin2026', 'letmein', 'admin123', 'root'];
                        foreach ($sample_words as $w): ?>
                            <button type="button" class="btn btn-outline-secondary btn-sm font-monospace text-light" onclick="document.getElementById('pass-field').value='<?= $w ?>';">
                                <?= $w ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= render_hints([
    "اسم المستخدم هو <code>admin</code>.",
    "كلمة المرور من ضمن الكلمات الشائعة الموجودة في القاموس المصغر بالأعلى.",
    "جرب كلمة <code>admin123</code> وسيتم فك القفل فوراً والحصول على العلم."
]); ?>

<?= render_code_comparison(
    '// كود مصاب: لا يوجد حد للمحاولات الخاطئة\nif ($user == "admin" && $pass == $db_pass) {\n    login();\n}',
    '// كود آمن: تتبع المحاولات وتطبيق Rate Limiting / Lockout\nif ($_SESSION["attempts"] >= 5) {\n    die("تم حظر الحساب مؤقتاً بسبب تكرار المحاولات الخاطئة!");\n}\n// استخدام Google reCAPTCHA بعد محاولتين فاشلتين',
    'الحماية الفعالة ضد التخمين تشمل: تطبيق حظر مؤقت (Account Lockout) بعد عدد محدد من المحاولات، إضافة اختبار CAPTCHA، وتفعيل المصادقة الثنائية (2FA).'
); ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
