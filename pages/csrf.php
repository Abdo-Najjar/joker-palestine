<?php
$page_title = "تزوير الطلبات عبر المواقع (CSRF) | مختبر الجوكر الفلسطيني";
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

<!-- مخطط معماري توضيحي لمسار هجوم CSRF -->
<?= render_diagram(
    'csrf_diagram.jpg',
    'مخطط توضيحي: مسار هجوم تزوير الطلبات عبر المواقع (CSRF Flow)',
    'شرح تفاعلي لكيفية استدراج الضحية لموقع خارجي مزور يقوم بإرسال طلبات POST لتغيير البريد أو كلمة المرور مستغلاً ملفات الكوكي التلقائية للمتصفح.'
); ?>

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
                <div class="p-3 bg-danger bg-opacity-10 rounded border border-danger h-100">
                    <h5 class="text-danger fw-bold mb-2"><i class="fas fa-skull me-1"></i> تطبيق عملي واقعي: إرسال رابط خارجي ملغوم للضحية:</h5>
                    <p class="text-light small mb-3">
                        في الحياة الواقعية، المهاجم لا يخترق الموقع مباشرة، بل ينشئ صفحة خارجية خادعة (مثل مسابقة أو موقع إخباري) ويرسل رابطها للضحية عبر واتساب أو البريد.
                    </p>
                    
                    <div class="d-grid gap-2 mb-3">
                        <a href="fake_prize_site.html" target="_blank" class="btn btn-danger fw-bold py-2 shadow">
                            <i class="fas fa-external-link-alt me-2"></i> فتح صفحة المهاجم الخارجية (fake_prize_site.html) في تبويب جديد
                        </a>
                        <form method="POST" action="csrf.php" target="_self" class="d-grid">
                            <input type="hidden" name="action" value="update_email">
                            <input type="hidden" name="email" value="hacker@evil.com">
                            <input type="hidden" name="csrf_attack" value="1">
                            <button type="submit" class="btn btn-outline-danger btn-sm">
                                <i class="fas fa-bolt me-1"></i> أو تنفيذ الهجوم فوراً بنقرة زر سريعة هنا
                            </button>
                        </form>
                    </div>

                    <div class="p-2 bg-dark rounded border border-secondary small text-muted">
                        <strong class="text-warning"><i class="fas fa-eye me-1"></i> تجربة عملية مع الطلاب:</strong>
                        <ol class="mb-0 ps-3 mt-1 text-light">
                            <li>تأكد أن بريدك هنا هو: <code><?= htmlspecialchars($current_email) ?></code>.</li>
                            <li>افتح الرابط الخارجي بالأعلى في تبويب جديد واضغط على "استلام الجائزة".</li>
                            <li>ارجع إلى هذا التبويب وحدّث الصفحة (F5)، ستجد أن بريدك تغيّر تلقائياً إلى <code>hacker_hijacked@evil.com</code> دون أن تدخل على هذا الموقع!</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <!-- قسم الشرح باستخدام Burp Suite الاحترافي -->
        <div class="card bg-dark border-info mt-4">
            <div class="card-header bg-black text-info fw-bold d-flex justify-content-between align-items-center py-2">
                <span><i class="fas fa-shield-virus me-2"></i> كيف ينفذ مختبرو الاختراق هذا الهجوم عملياً باستخدام أداة Burp Suite؟</span>
                <span class="badge bg-info text-dark">Burp Suite Professional</span>
            </div>
            <div class="card-body p-3">
                <div class="row align-items-center">
                    <div class="col-lg-7">
                        <h6 class="text-white fw-bold mb-2">خطوات توليد كود الاستغلال (Generate CSRF PoC) في Burp Suite:</h6>
                        <ol class="text-light small mb-0 pe-3">
                            <li class="mb-1">اضبط المتصفح ليمر عبر بروكسي <strong>Burp Suite</strong>.</li>
                            <li class="mb-1">قم بتغيير البريد الإلكتروني في النموذج الطبيعي ليلتقط Burp طلب الـ <code>POST /pages/csrf.php</code> في تبويب <strong>HTTP history</strong>.</li>
                            <li class="mb-1">اضغط بالزر الأيمن (Right-Click) على الطلب، واختر: <br><code class="text-warning bg-black px-2 py-0.5 rounded">Engagement tools -> Generate CSRF PoC</code>.</li>
                            <li class="mb-1">من خيارات <strong>Options</strong>، فعّل خيار <strong class="text-info">Include auto-submit script</strong> لتوليد كود جافاسكربت يرسل الطلب تلقائياً بمجرد فتح الصفحة.</li>
                            <li class="mb-1">اضغط على <strong>Copy HTML</strong> واحفظه كملف <code>attack.html</code> على سيرفر خارجي، ثم أرسل رابطه للضحية!</li>
                        </ol>
                    </div>
                    <div class="col-lg-5 mt-3 mt-lg-0">
                        <div class="p-2 bg-black rounded border border-secondary">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <small class="text-info font-monospace">attack.html (PoC Code)</small>
                                <span class="badge bg-secondary">HTML + JS</span>
                            </div>
                            <pre class="mb-0 text-success small" style="direction:ltr; text-align:left; max-height:160px; overflow-y:auto;"><code>&lt;!-- كود هجوم CSRF الناتج من Burp Suite --&gt;
&lt;html&gt;
  &lt;body&gt;
    &lt;form action="http://localhost:8000/pages/csrf.php" method="POST"&gt;
      &lt;input type="hidden" name="action" value="update_email" /&gt;
      &lt;input type="hidden" name="email" value="hacker@evil.com" /&gt;
    &lt;/form&gt;
    &lt;script&gt;
      document.forms[0].submit();
    &lt;/script&gt;
  &lt;/body&gt;
&lt;/html&gt;</code></pre>
                        </div>
                    </div>
                </div>
            </div>
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

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
