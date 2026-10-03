<?php
$page_title = "التوجيه المفتوح (Open Redirect) | مختبر الجوكر الأمني";
require_once __DIR__ . '/../includes/header.php';

$sec = get_security_level();
$error = "";

if (isset($_GET['target'])) {
    $target = $_GET['target'];

    if ($sec === 'low') {
        // كود مصاب: توجيه مباشر لأي رابط
        award_flag('redirect');
        if (isset($_GET['simulate'])) {
            $msg = "<div class='alert alert-warning'><strong>[محاكاة التوجيه الناجح]:</strong> كان سيتم توجيهك الآن إلى الرابط الخارجي: <code>" . htmlspecialchars($target) . "</code></div>";
        } else {
            header("Location: " . $target);
            exit;
        }
    } else {
        // كود آمن: السماح بالمسارات الداخلية فقط
        if (strpos($target, '/') === 0 && strpos($target, '//') !== 0 && strpos($target, '/\\') !== 0) {
            header("Location: " . $target);
            exit;
        } else {
            $error = "تم حظر التوجيه! لا يُسمح بالتوجيه إلى روابط أو نطاقات خارجية (External Domains Blocked).";
        }
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom border-secondary">
    <div>
        <h2 class="fw-bold text-white mb-1"><i class="fas fa-external-link-alt text-primary me-2"></i> 9. ثغرة التوجيه المفتوح (Open Redirect)</h2>
        <p class="text-muted mb-0">تحدث عندما يأخذ التطبيق رابطاً خارجياً من المستخدم ويقوم بتوجيهه إليه عبر <code>header('Location: ...')</code> دون التحقق من النطاق، مما يسهل هجمات التصيد الاحتيالي (Phishing).</p>
    </div>
    <span class="badge bg-primary fs-6 px-3 py-2"><i class="fas fa-fish me-1"></i> أداة مساعدة لهجمات التصيد</span>
</div>

<div class="card card-cyber mb-4">
    <div class="card-cyber-header d-flex justify-content-between align-items-center">
        <h4 class="mb-0 text-info"><i class="fas fa-compass me-2"></i> أداة التوجيه بعد تسجيل الخروج / الروابط الخارجية</h4>
        <span class="badge bg-secondary">تحدي التوجيه المفتوح</span>
    </div>
    <div class="card-body p-4">
        <p class="text-light">
            <strong>الهدف:</strong> استغلال رابط التوجيه <code>redirect.php?target=...</code> لجعل الموقع الموثوق يوجه الزائر إلى موقع خارجي ضار أو خبيث.
        </p>

        <?= render_flag_box('redirect'); ?>
        <?= $msg ?? ''; ?>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger py-2"><?= $error ?></div>
        <?php endif; ?>

        <div class="p-3 bg-dark rounded border border-secondary mb-4" style="max-width: 650px;">
            <label class="form-label text-light">الرابط المستهدف للتوجيه (Target URL):</label>
            <form method="GET" class="row g-2">
                <input type="hidden" name="simulate" value="1">
                <div class="col-8">
                    <input type="text" name="target" class="form-control" value="https://google.com" placeholder="https://evil-site.com...">
                </div>
                <div class="col-4">
                    <button type="submit" class="btn btn-primary fw-bold w-100"><i class="fas fa-arrow-right me-1"></i> اختبار التوجيه</button>
                </div>
            </form>
            <small class="text-muted d-block mt-2">
                (تم وضع وضع المحاكاة هنا لترى كيف يقبل السيرفر التوجيه دون مغادرة منصة المختبر).
            </small>
        </div>

        <?= render_hints([
            "لاحظ كيف يقبل السيرفر أي نطاق خارجي مثل <code>https://google.com</code> أو <code>https://attacker.com</code>.",
            "المهاجمون يرسلون رابط مثل: <code>https://your-trusted-bank.com/redirect.php?target=https://fake-login-bank.com</code> لخداع الضحايا بأن الرابط آمن لأنه يبدأ بنطاق البنك الموثوق!",
            "اضغط على زر (اختبار التوجيه) للحصول على العلم."
        ]); ?>

        <?= render_code_comparison(
            '// كود مصاب\n$target = $_GET["target"];\nheader("Location: " . $target);',
            '// كود آمن: حصر التوجيه بالمسارات النسبية فقط أو قائمة بيضاء\n$target = $_GET["target"];\nif (strpos($target, "/") === 0 && strpos($target, "//") !== 0) {\n    header("Location: " . $target);\n} else {\n    die("Invalid Redirect Target!");\n}',
            'لمنع ثغرات Open Redirect، تحقق من أن الرابط يبدأ بشرطة مائلة واحدة فقط / ولا يحتوي على بروتوكول خارجي، أو استخدم قائمة بيضاء بالنطاقات الموثوقة.'
        ); ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
