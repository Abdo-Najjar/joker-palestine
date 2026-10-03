<?php
$page_title = "تزوير الطلب من جانب الخادم (SSRF) | مختبر الجوكر الأمني";
require_once __DIR__ . '/../includes/header.php';

$sec = get_security_level();
$fetched_data = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['url'])) {
    $url = trim($_POST['url']);

    if (!empty($url)) {
        if ($sec === 'low') {
            // كود مصاب: جلب الرابط مباشرة دون التحقق من الـ IP الداخلي
            $opts = [
                "http" => [
                    "method" => "GET",
                    "timeout" => 3,
                    "header" => "User-Agent: Joker-Lab-Bot/1.0\r\n"
                ]
            ];
            $context = stream_context_create($opts);
            $content = @file_get_contents($url, false, $context);

            if ($content !== false) {
                $fetched_data = $content;
            } else {
                $error = "تعذر جلب الرابط أو انتهت مهلة الاتصال.";
            }

            // فحص استهداف السيرفر الداخلي
            if (preg_match('/(localhost|127\.0\.0\.1|0\.0\.0\.0|::1)/i', $url)) {
                award_flag('ssrf');
            }
        } else {
            // كود آمن: فحص البروتوكول وعناوين الـ IP الخاصة
            $parsed = parse_url($url);
            if (!isset($parsed['scheme']) || !in_array($parsed['scheme'], ['http', 'https'])) {
                $error = "بروتوكول غير مسموح به! يُقبل فقط http أو https.";
            } else {
                $host = $parsed['host'] ?? '';
                $ip = gethostbyname($host);

                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
                    $error = "تم حظر الطلب! العنوان المستهدف ينتمي لشبكة داخلية خاصة (SSRF Blocked: Private IP $ip).";
                } else {
                    $fetched_data = @file_get_contents($url, false, stream_context_create(["http" => ["timeout" => 3]]));
                }
            }
        }
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom border-secondary">
    <div>
        <h2 class="fw-bold text-white mb-1"><i class="fas fa-network-wired text-warning me-2"></i> 10. تزوير الطلب من جانب الخادم (Server-Side Request Forgery - SSRF)</h2>
        <p class="text-muted mb-0">تتيح ثغرة SSRF للمهاجم استغلال الخادم نفسه لإرسال طلبات شبكة HTTP/TCP إلى أجهزة أو خدمات داخلية (Localhost) لا يمكن للمهاجم الوصول إليها من الخارج مباشرة.</p>
    </div>
    <span class="badge bg-danger fs-6 px-3 py-2"><i class="fas fa-server me-1"></i> فحص الشبكات الداخلية والـ Cloud Metadata</span>
</div>

<div class="card card-cyber mb-4">
    <div class="card-cyber-header d-flex justify-content-between align-items-center">
        <h4 class="mb-0 text-info"><i class="fas fa-globe me-2"></i> أداة معاينة الروابط وجلب المحتوى (URL Content Previewer)</h4>
        <span class="badge bg-secondary">تحدي SSRF</span>
    </div>
    <div class="card-body p-4">
        <p class="text-light">
            <strong>الهدف:</strong> اجعل السيرفر يطلب عنوانه الداخلي <code>http://127.0.0.1</code> أو <code>http://localhost</code> لاستكشاف الخدمات الداخلية واقتناص العلم.
        </p>

        <?= render_flag_box('ssrf'); ?>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger py-2"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST" class="row g-2 mb-3" style="max-width: 650px;">
            <div class="col-8">
                <input type="text" name="url" class="form-control font-monospace" placeholder="http://example.com أو http://127.0.0.1..." value="<?= htmlspecialchars($_POST['url'] ?? 'http://127.0.0.1') ?>" required>
            </div>
            <div class="col-4">
                <button type="submit" class="btn btn-warning text-dark fw-bold w-100"><i class="fas fa-download me-1"></i> جلب من السيرفر</button>
            </div>
        </form>

        <?php if (!empty($fetched_data)): ?>
            <div class="card bg-dark border-secondary mt-3">
                <div class="card-header bg-black text-white d-flex justify-content-between align-items-center py-2">
                    <span class="small"><i class="fas fa-file-code text-info me-1"></i> استجابة السيرفر للمحتوى المجلوب:</span>
                    <span class="badge bg-success">تم الاستلام بنجاح</span>
                </div>
                <div class="card-body p-3">
                    <pre class="mb-0 text-light small" style="direction:ltr; text-align:left; max-height:260px; overflow-y:auto;"><code><?= htmlspecialchars(substr($fetched_data, 0, 1500)) ?></code></pre>
                </div>
            </div>
        <?php endif; ?>

        <?= render_hints([
            "في الوضع العادي، هذه الميزة مخصصة لجلب روابط مثل <code>http://example.com</code>.",
            "لكن المهاجم يستغلها لطلب عناوين مثل: <code>http://127.0.0.1:80</code> أو فحص المنافذ الداخلية (Port Scanning) عبر السيرفر نفسه!",
            "جرب إرسال <code>http://127.0.0.1</code> أو <code>http://localhost</code> واضغط على الزر لتفعيل العلم."
        ]); ?>

        <?= render_code_comparison(
            '// كود مصاب\n$url = $_POST["url"];\n$html = file_get_contents($url);',
            '// كود آمن: حظر عناوين الـ Private / Loopback IPs\n$ip = gethostbyname(parse_url($url, PHP_URL_HOST));\nif (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {\n    die("Access Denied: Private IP Detected!");\n}\n$html = file_get_contents($url);',
            'لمنع SSRF، يجب حل اسم النطاق والتأكد من أن عنوان الـ IP ليس من النطاقات المحجوزة أو الخاصة (127.0.0.0/8, 10.0.0.0/8, 192.168.0.0/16, 169.254.169.254).'
        ); ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
