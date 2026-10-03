<?php
$page_title = "تزوير الطلبات عبر المواقع (CSRF) | مختبر الجوكر الأمني";
require_once __DIR__ . '/../includes/header.php';

$db = get_db();
$sec = get_security_level();

// نعتبر المستخدم الحالي هو user1
$user_id = 2;
$status_msg = "";

// إنشاء توكن الحماية في الوضع الآمن
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_email') {
    $new_email = trim($_POST['email'] ?? '');

    if ($sec === 'low') {
        // كود مصاب: لا يوجد أي فحص للـ CSRF Token
        $stmt = $db->prepare("UPDATE users SET email = ? WHERE id = ?");
        $stmt->execute([$new_email, $user_id]);
        $status_msg = "<div class='alert alert-success'><i class='fas fa-check-circle me-1'></i> تم تحديث البريد الإلكتروني إلى: <strong>" . htmlspecialchars($new_email) . "</strong></div>";

        if (strpos($new_email, 'hacker') !== false || isset($_POST['csrf_attack'])) {
            award_flag('csrf');
        }
    } else {
        // كود آمن: التحقق من صحة الـ CSRF Token
        $token = $_POST['csrf_token'] ?? '';
        if (hash_equals($_SESSION['csrf_token'], $token)) {
            $stmt = $db->prepare("UPDATE users SET email = ? WHERE id = ?");
            $stmt->execute([$new_email, $user_id]);
            $status_msg = "<div class='alert alert-success'><i class='fas fa-shield-alt me-1'></i> تم التحقق من توكن الحماية وتحديث البريد بنجاح.</div>";
        } else {
            $status_msg = "<div class='alert alert-danger'><i class='fas fa-ban me-1'></i> فشل التحقق الأمني! رمز CSRF Token مفقود أو غير صالح (403 Forbidden).</div>";
        }
    }
}

// جلب البريد الحالي
$stmt = $db->prepare("SELECT email FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$current_email = $stmt->fetchColumn();
?>

<div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom border-secondary">
    <div>
        <h2 class="fw-bold text-white mb-1"><i class="fas fa-random text-info me-2"></i> 7. ثغرة تزوير الطلبات عبر المواقع (CSRF)</h2>
        <p class="text-muted mb-0">تتيح ثغرة Cross-Site Request Forgery للمهاجم إجبار متصفح الضحية على تنفيذ إجراءات غير مرغوب فيها داخل موقع موثوق وهو مسجل الدخول فيه.</p>
    </div>
    <span class="badge bg-warning text-dark fs-6 px-3 py-2"><i class="fas fa-user-secret me-1"></i> Client-Side Attack</span>
</div>

<div class="card card-cyber mb-4">
    <div class="card-cyber-header d-flex justify-content-between align-items-center">
        <h4 class="mb-0 text-info"><i class="fas fa-envelope me-2"></i> تعديل البريد الإلكتروني للمستخدم</h4>
        <span class="badge bg-secondary">تحدي CSRF</span>
    </div>
    <div class="card-body p-4">
        <p class="text-light">
            <strong>الهدف:</strong> فهم كيف يمكن تغيير بريد الضحية دون علمه عبر طلب خارجي غير محمي برمز Anti-CSRF Token.
        </p>

        <?= render_flag_box('csrf'); ?>
        <?= $status_msg; ?>

        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="p-3 bg-dark rounded border border-secondary">
                    <h5 class="text-white mb-3"><i class="fas fa-cog me-1 text-warning"></i> النموذج الأصلي لتحديث البريد:</h5>
                    <form method="POST">
                        <input type="hidden" name="action" value="update_email">
                        
                        <?php if ($sec === 'secure'): ?>
                            <!-- حقل الحماية المخفي في الوضع الآمن -->
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                        <?php endif; ?>

                        <div class="mb-3">
                            <label class="form-label text-light">البريد الإلكتروني الحالي:</label>
                            <input type="email" class="form-control" value="<?= htmlspecialchars($current_email) ?>" readonly disabled>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-light">البريد الإلكتروني الجديد:</label>
                            <input type="email" name="email" class="form-control" placeholder="new-email@domain.com" required>
                        </div>
                        <button type="submit" class="btn btn-primary fw-bold"><i class="fas fa-save me-1"></i> حفظ التعديل</button>
                    </form>
                </div>
            </div>

            <div class="col-md-6">
                <div class="p-3 bg-danger bg-opacity-10 rounded border border-danger">
                    <h5 class="text-danger fw-bold"><i class="fas fa-skull me-1"></i> محاكاة هجوم CSRF من صفحة المهاجم الخارجية:</h5>
                    <p class="text-light small">
                        تخيل أن الضحية فتحت صفحة ويب مشبوهة أو منتدى خارجي، وكان بداخل تلك الصفحة نموذج خفي يرسل الطلب تلقائياً عبر متصفح الضحية:
                    </p>
                    <form method="POST" action="csrf.php" target="_self">
                        <input type="hidden" name="action" value="update_email">
                        <input type="hidden" name="email" value="hacker@evil.com">
                        <input type="hidden" name="csrf_attack" value="1">
                        <button type="submit" class="btn btn-danger w-100 fw-bold">
                            <i class="fas fa-radiation me-1"></i> محاكاة نقرة الضحية على رابط المهاجم (Trigger CSRF Attack)
                        </button>
                    </form>
                    <small class="text-muted d-block mt-2">
                        في الوضع الضعيف، سينجح الطلب ويتم تغيير البريد إلى <code>hacker@evil.com</code> وتحصل على العلم!
                    </small>
                </div>
            </div>
        </div>

        <?= render_hints([
            "اضغط على زر محاكاة هجوم CSRF لتجربة كيف يقبل الخادم الطلب بدون توكن حماية.",
            "أو قم بتغيير البريد الإلكتروني واكتب فيه كلمة <code>hacker</code> ليتم اعتبار الهجوم ناجحاً.",
            "جرب تحويل مستوى الحماية إلى Secure ولاحظ كيف سيفشل الهجوم الخارجي فوراً لأن المهاجم يجهل قيمة الـ CSRF Token الفريد."
        ]); ?>

        <?= render_code_comparison(
            '// كود مصاب: استقبال التعديل مباشرة\n$new_email = $_POST["email"];\n$db->query("UPDATE users SET email = \'$new_email\' WHERE id = $uid");',
            '// كود آمن: التحقق من توكن مشفر عشوائي\nif (!hash_equals($_SESSION["csrf_token"], $_POST["csrf_token"])) {\n    die("CSRF Token Invalid!");\n}\n// استخدام SameSite Cookies\nsetcookie("session_id", $sid, ["samesite" => "Strict", "httponly" => true]);',
            'للحماية من CSRF: أضف Anti-CSRF Token عشوائي في كل نموذج حساس، واضبط إعداد SameSite=Strict في كوكيز الجلسة لمنع إرسالها من المواقع الخارجية.'
        ); ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
