<?php
$page_title = "حقن أوامر النظام (Command Injection) | مختبر الجوكر الأمني";
require_once __DIR__ . '/../includes/header.php';

$sec = get_security_level();
$output = "";
$cmd_executed = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ip'])) {
    $ip = trim($_POST['ip']);

    if (!empty($ip)) {
        $is_windows = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');

        if ($sec === 'low') {
            // كود مصاب: دمج المدخل مباشرة في سطر الأوامر
            if ($is_windows) {
                $cmd_executed = "ping -n 1 " . $ip;
            } else {
                $cmd_executed = "ping -c 1 " . $ip;
            }
            $output = @shell_exec($cmd_executed);

            // التحقق من نجاح حقن أمر إضافي
            if (strpbrk($ip, ';|&`$') !== false) {
                award_flag('cmdi');
            }
        } else {
            // كود آمن: التحقق من أن المدخل هو عنوان IP حقيقي فقط أو استخدام escapeshellarg
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                $safe_ip = escapeshellarg($ip);
                $cmd_executed = ($is_windows ? "ping -n 1 " : "ping -c 1 ") . $safe_ip;
                $output = @shell_exec($cmd_executed);
            } else {
                $error = "تم حظر الطلب! القيمة المدخلة ليست عنوان IP صالح (Invalid IP Address).";
            }
        }
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom border-secondary">
    <div>
        <h2 class="fw-bold text-white mb-1"><i class="fas fa-terminal text-danger me-2"></i> 5. حقن أوامر نظام التشغيل (OS Command Injection)</h2>
        <p class="text-muted mb-0">تحدث هذه الثغرة عند استدعاء أوامر النظام (عبر <code>shell_exec</code>, <code>exec</code>, <code>system</code>) وتمرير مدخلات المستخدم دون تعقيم، مما يتيح تشغيل أوامر خادم عشوائية.</p>
    </div>
    <span class="badge bg-danger fs-6 px-3 py-2"><i class="fas fa-biohazard me-1"></i> ثغرة خطيرة جداً (RCE)</span>
</div>

<div class="card card-cyber mb-4">
    <div class="card-cyber-header d-flex justify-content-between align-items-center">
        <h4 class="mb-0 text-info"><i class="fas fa-network-wired me-2"></i> أداة فحص الشبكة والاتصال (Ping Utility)</h4>
        <span class="badge bg-secondary">تحدي أوامر النظام</span>
    </div>
    <div class="card-body p-4">
        <p class="text-light">
            <strong>الهدف:</strong> قم باستغلال أداة الـ Ping لتنفيذ أمر نظام إضافي (مثل <code>whoami</code> أو <code>dir</code> أو <code>id</code>) للحصول على العلم.
        </p>

        <?= render_flag_box('cmdi'); ?>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger py-2"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST" class="row g-2 mb-3" style="max-width: 650px;">
            <div class="col-8">
                <input type="text" id="cmdi-input" name="ip" dir="ltr" class="form-control font-monospace" style="direction:ltr; text-align:left;" placeholder="127.0.0.1 & whoami" value="<?= htmlspecialchars($_POST['ip'] ?? '127.0.0.1') ?>" required>
                <div class="mt-2 d-flex flex-wrap align-items-center gap-1">
                    <small class="text-muted me-1">بايلودات جاهزة للتجربة والتعلم:</small>
                    <button type="button" class="btn btn-outline-info btn-sm py-0 px-2 font-monospace" onclick="document.getElementById('cmdi-input').value='127.0.0.1 & whoami';">127.0.0.1 & whoami</button>
                    <button type="button" class="btn btn-outline-info btn-sm py-0 px-2 font-monospace" onclick="document.getElementById('cmdi-input').value='127.0.0.1 && whoami';">127.0.0.1 && whoami</button>
                    <button type="button" class="btn btn-outline-info btn-sm py-0 px-2 font-monospace" onclick="document.getElementById('cmdi-input').value='127.0.0.1 & dir';">127.0.0.1 & dir</button>
                </div>
            </div>
            <div class="col-4">
                <button type="submit" class="btn btn-danger fw-bold w-100"><i class="fas fa-play me-1"></i> تشغيل الفحص</button>
            </div>
        </form>

        <?php if (!empty($cmd_executed)): ?>
            <div class="alert alert-dark border-secondary small py-2 mb-2">
                <i class="fas fa-terminal text-info me-1"></i> الأمر المنفذ على السيرفر: <code dir="ltr"><?= htmlspecialchars($cmd_executed) ?></code>
            </div>
        <?php endif; ?>

        <?php if (!empty($output)): ?>
            <?php if (stripos($output, 'could not find host') !== false): ?>
                <div class="alert alert-warning py-2 small mb-2">
                    <i class="fas fa-info-circle me-1"></i> <strong>ملاحظة هامة:</strong> يبدو أنك وضعت أمر <code>whoami</code> قبل الـ IP! أمر <code>ping</code> يتوقع عنوان IP أولاً، ثم علامة الربط <code>&</code> أو <code>&&</code> متبوعة بأمرك الإضافي. استخدم الترتيب التالي: <code dir="ltr">127.0.0.1 & whoami</code>
                </div>
            <?php endif; ?>
            <div class="card bg-black border-secondary">
                <div class="card-header bg-dark text-white py-1 small">
                    <i class="fas fa-desktop me-1 text-success"></i> مخرجات الأوامر (Terminal Output):
                </div>
                <div class="card-body p-3">
                    <pre class="mb-0 text-success small" style="direction:ltr; text-align:left; max-height:280px; overflow-y:auto;"><code><?= htmlspecialchars($output) ?></code></pre>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?= render_hints([
    "في أنظمة ويندوز ولينكس، يمكن ربط الأوامر ببعضها باستخدام الرموز: <code>&</code> أو <code>&&</code> أو <code>|</code> أو <code>;</code>.",
    "الترتيب الصحيح (من اليسار لليمين): نضع عنوان الـ IP أولاً، ثم الرمز <code>&</code>، ثم الأمر المطلوب: <span dir='ltr' style='direction:ltr; display:inline-block;'><code class='text-warning bg-black px-2 py-1'>127.0.0.1 & whoami</code></span>",
    "ستلاحظ أن السيرفر نفذ أمر Ping أولاً لعنوان 127.0.0.1، ثم نفذ أمر whoami بعده مباشرة وطبع اسم المستخدم الخاص بجهازك!"
]); ?>

<?= render_code_comparison(
    '// كود مصاب\n$ip = $_POST["ip"];\n$output = shell_exec("ping -c 1 " . $ip);',
    '// كود آمن\n$ip = $_POST["ip"];\nif (filter_var($ip, FILTER_VALIDATE_IP)) {\n    $safe_ip = escapeshellarg($ip);\n    $output = shell_exec("ping -c 1 " . $safe_ip);\n} else {\n    die("Invalid IP");\n}',
    'أفضل ممارسة لتفادي Command Injection هي تجنب استدعاء أوامر النظام المباشرة قدر الإمكان، وإذا كان ضرورياً يجب التحقق الصارم من صحة المدخلات وتغليفها بدالة escapeshellarg() أو escapeshellcmd().'
); ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
