<?php
// ==============================================================================
// مشروع: مختبر الجوكر الفلسطيني لاختبار اختراق تطبيقات الويب (Joker Security Lab)
// التحدي 19: سوء تهيئة مشاركة الموارد عبر الأصول (CORS Misconfiguration)
// إعداد وتطوير: المهندس احمد سليم 🇵🇸
// ==============================================================================

// إذا كان الطلب موجهاً لواجهة الـ API البرمجية (API Endpoint Mode)
if (isset($_GET['api']) && $_GET['api'] === 'user_data') {
    require_once __DIR__ . '/../config.php';
    $sec = get_security_level();

    $origin = $_SERVER['HTTP_ORIGIN'] ?? ($_SERVER['HTTP_REFERER'] ?? 'https://evil-attacker.ps');

    if ($sec === 'low') {
        // ❌ كود مصاب: عكس الأصل القادم من الترويسة مباشرة مع السماح بالاعتمادات (Credentials)
        header("Access-Control-Allow-Origin: $origin");
        header("Access-Control-Allow-Credentials: true");
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
        header("Content-Type: application/json; charset=utf-8");

        // إذا كان الأصل ينتمي لنطاق خارجي غير مصرح به أو موقع المهاجم
        if (stripos($origin, 'evil') !== false || stripos($origin, 'attacker') !== false || stripos($origin, 'localhost:3000') !== false || isset($_GET['simulate_exploit'])) {
            award_flag('cors');
        }

        echo json_encode([
            'status' => 'success',
            'user' => 'أحمد سليم',
            'email' => 'ahmed.salim@joker-lab.ps',
            'account_number' => 'PS-9921-884-01',
            'balance' => '$24,850.00 USD',
            'api_secret_key' => 'JOKER_PROD_API_KEY_88992211',
            'secret_flag' => 'FLAG{CORS_Arbitrary_Origin_Data_Theft_3810}',
            'note' => 'بيانات مصرفية وحسابية مشفرة وسرية للغاية'
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    } else {
        // ✅ كود محمي: قائمة بيضاء صارمة للأصول المعتمدة فقط دون عكس عشوائي
        $trusted_origins = [
            'https://joker-lab.ps',
            'https://palestine-sec.ps'
        ];

        if (in_array($origin, $trusted_origins)) {
            header("Access-Control-Allow-Origin: $origin");
            header("Access-Control-Allow-Credentials: true");
        } else {
            // حظر الترويسة تماماً للمواقع الغريبة والمشبوهة
            http_response_code(403);
            header("Content-Type: application/json; charset=utf-8");
            echo json_encode([
                'status' => 'error',
                'message' => '⛔ [CORS Security Policy] تم حظر الطلب! النطاق المستدعي غير مصرح به في القائمة البيضاء (Cross-Origin Request Blocked).'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }
}

$page_title = "سوء تهيئة CORS | مختبر الجوكر الفلسطيني";
require_once __DIR__ . '/../includes/header.php';
$sec = get_security_level();
?>

<div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom border-secondary">
    <div>
        <h2 class="fw-bold text-white mb-1">
            <i class="fas fa-network-wired text-warning me-2"></i> 19. سوء تهيئة مشاركة الموارد عبر الأصول (CORS Misconfiguration)
        </h2>
        <p class="text-muted mb-0">
            تحدث ثغرة CORS (CWE-942) عندما يقوم السيرفر بمرآة وعكس ترويسة <code>Origin</code> العشوائية من طلب المهاجم في ترويسة <code>Access-Control-Allow-Origin</code> مع تفعيل <code>Allow-Credentials: true</code>، مما يسمح لأي موقع خبيث بقراءة بيانات الضحية الحساسة وجلساته المصرفية.
        </p>
    </div>
    <span class="badge bg-danger fs-6 px-3 py-2">
        <i class="fas fa-unlock-alt me-1"></i> تجاوز سياسة الأصل نفسه (SOP Bypass)
    </span>
</div>

<!-- Architecture Flow Diagram Card -->
<div class="card card-cyber mb-4">
    <div class="card-cyber-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0 text-info"><i class="fas fa-project-diagram me-2"></i> مسار استغلال CORS وسرقة البيانات الحساسة عبر موقع المهاجم</h5>
        <span class="badge bg-dark border border-info text-info">CORS Exploitation Flow</span>
    </div>
    <div class="card-body p-4 text-center">
        <div class="row align-items-center g-3">
            <div class="col-md-3">
                <div class="p-3 rounded bg-black border border-danger text-center h-100">
                    <span class="badge bg-danger mb-2">1. موقع المهاجم الخبيث</span>
                    <pre class="text-danger small mb-0 font-monospace text-start" style="direction:ltr;">evil-attacker.ps</pre>
                    <small class="text-light text-opacity-75 d-block mt-2">يقوم بزيارته الضحية المسجل دخوله في المنصة المصرفية</small>
                </div>
            </div>
            <div class="col-md-1 d-flex justify-content-center">
                <i class="fas fa-arrow-left text-info fs-3 d-none d-md-block"></i>
                <i class="fas fa-arrow-down text-info fs-3 d-md-none"></i>
            </div>
            <div class="col-md-4">
                <div class="p-3 rounded bg-black border border-warning text-center h-100">
                    <span class="badge bg-warning text-dark mb-2">2. استدعاء API الضحية مع الكوكي</span>
                    <pre class="text-warning small mb-0 font-monospace text-start" style="direction:ltr;">fetch(url, {credentials: 'include'})</pre>
                    <small class="text-light text-opacity-75 d-block mt-2">السيرفر الضعيف يعكس <code>Allow-Origin: evil-attacker.ps</code> و <code>Allow-Credentials: true</code>!</small>
                </div>
            </div>
            <div class="col-md-1 d-flex justify-content-center">
                <i class="fas fa-arrow-left text-success fs-3 d-none d-md-block"></i>
                <i class="fas fa-arrow-down text-success fs-3 d-md-none"></i>
            </div>
            <div class="col-md-3">
                <div class="p-3 rounded bg-black border border-success text-center h-100">
                    <span class="badge bg-success mb-2">3. سرقة وتسريب البيانات</span>
                    <pre class="text-success small mb-0 font-monospace text-start" style="direction:ltr;">API Key & Balance Exfiltrated</pre>
                    <small class="text-light text-opacity-75 d-block mt-2">المتصفح يسمح للسكربت الخبيث بقراءة الرد المصرفي الحساس واستخراج العلم!</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Interactive Challenge Card -->
<div class="card card-cyber mb-4">
    <div class="card-cyber-header d-flex justify-content-between align-items-center">
        <h4 class="mb-0 text-info"><i class="fas fa-shield-virus me-2"></i> مختبر محاكاة هجوم CORS وسرقة بيانات الحساب البنكي</h4>
        <span class="badge bg-secondary">تحدي CORS</span>
    </div>
    <div class="card-body p-4">
        <p class="text-light">
            <strong>الهدف:</strong> توجد واجهة API داخلية بالمسار <code>cors.php?api=user_data</code> تحتوي على بيانات بنكية ومفتاح سري. قم بمحاكاة هجوم استغلال CORS من أصل خارجي غير موثوق <code>https://evil-attacker.ps</code> لاقتناص العلم الحساس.
        </p>

        <?= render_flag_box('cors'); ?>

        <div class="row g-4">
            <!-- Attacker Site Console -->
            <div class="col-lg-6">
                <div class="p-3 rounded bg-black border border-danger shadow-sm h-100">
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom border-danger">
                        <span class="badge bg-danger fw-bold"><i class="fas fa-skull-crossbones me-1"></i> محاكي موقع المهاجم (Attacker Sandbox)</span>
                        <code class="text-danger small">https://evil-attacker.ps</code>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-warning small fw-bold">ترويسة الأصل المزور (Origin Header):</label>
                        <input type="text" id="origin-input" class="form-control font-monospace bg-dark text-warning border-secondary" value="https://evil-attacker.ps">
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-info small fw-bold">كود الجافاسكربت المنفذ في موقع المهاجم:</label>
                        <pre class="bg-dark text-light p-2 rounded small font-monospace mb-0" style="direction:ltr; text-align:left;">fetch('cors.php?api=user_data', {
    method: 'GET',
    credentials: 'include' // إرسال كوكي الجلسة
}).then(r => r.json()).then(exfiltrateData);</pre>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-danger fw-bold py-2 shadow" onclick="executeCorsExploit()">
                            <i class="fas fa-crosshairs me-1"></i> تنفيذ هجوم سرقة البيانات عبر CORS الآن
                        </button>
                    </div>
                </div>
            </div>

            <!-- Intercepted Data Console -->
            <div class="col-lg-6">
                <div class="card bg-dark border-secondary h-100">
                    <div class="card-header bg-black text-white d-flex justify-content-between align-items-center py-2">
                        <span class="small fw-bold"><i class="fas fa-satellite text-info me-1"></i> البيانات المسروقة من الاستجابة (Exfiltrated JSON Payload):</span>
                        <span class="badge <?= $sec === 'low' ? 'bg-danger' : 'bg-success' ?>">
                            <?= $sec === 'low' ? 'وضع Low (مكشوف)' : 'وضع Secure (محمي)' ?>
                        </span>
                    </div>
                    <div class="card-body p-3">
                        <pre id="cors-output" class="p-3 rounded bg-black border border-secondary font-monospace small mb-0" style="min-height: 250px; max-height: 280px; overflow-y: auto; direction:ltr; text-align:left; color: #10b981;">// اضغط على زر تنفيذ الهجوم لاستعراض نتيجة سحب البيانات...</pre>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= render_hints([
    "سياسة الأصل نفسه (Same-Origin Policy - SOP) تمنع المواقع الخارجية من قراءة محتوى الصفحات الخاصة بالنطاقات الأخرى.",
    "لكن إذا أرسل السيرفر ترويسة <code>Access-Control-Allow-Origin</code> تطابق نطاق المهاجم، بالإضافة لترويسة <code>Access-Control-Allow-Credentials: true</code>، فإن المتصفح يسمح للمهاجم بقراءة الرد بالكامل!",
    "سوء التهيئة الأكثر خطورة هو قراءة السيرفر لترويسة <code>\$_SERVER['HTTP_ORIGIN']</code> وعكسها مباشرة دون التحقق من كونها نطاقاً موثوقاً.",
    "اضغط على زر <strong>(تنفيذ هجوم سرقة البيانات عبر CORS)</strong> لمشاهدة كيف يتم تسريب الحساب المصرفي والعلم الحساس بنقرة واحدة."
]); ?>

<?= render_code_comparison(
    '// ❌ كود مصاب: عكس أصل الطلب مباشرة وتفعيل الاعتمادات\n$origin = $_SERVER["HTTP_ORIGIN"];\nheader("Access-Control-Allow-Origin: " . $origin); // خطأ فادح: يثق بأي موقع خارجي!\nheader("Access-Control-Allow-Credentials: true");',
    '// ✅ كود محمي: قائمة بيضاء صارمة للأصول المصرح بها فقط\n$whitelist = ["https://joker-lab.ps", "https://palestine-sec.ps"];\n$origin = $_SERVER["HTTP_ORIGIN"] ?? "";\nif (in_array($origin, $whitelist, true)) {\n    header("Access-Control-Allow-Origin: " . $origin);\n    header("Access-Control-Allow-Credentials: true");\n} else {\n    // حظر مشاركة الموارد للمواقع غير المعتمدة\n    http_response_code(403);\n}',
    'لمنع ثغرات CORS، يجب تجنب عكس قيمة ترويسة Origin في الاستجابة بشكل ديناميكي، وعدم استخدام الأصل الافتراضي null أو الرموز البديلة (*) مع تفعيل الاعتمادات (Credentials)، وحصر الصلاحيات في قائمة بيضاء صارمة للأصول الموثوقة فقط.'
); ?>

<script>
function executeCorsExploit() {
    const origin = document.getElementById('origin-input').value;
    const output = document.getElementById('cors-output');
    output.innerText = "// جاري إرسال الطلب عبر الأصل المستهدف: " + origin + "...\n";

    fetch('cors.php?api=user_data&simulate_exploit=1', {
        headers: {
            'Origin': origin,
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(res => {
        return res.json().then(data => ({ status: res.status, ok: res.ok, data: data }));
    })
    .then(result => {
        if (result.ok && result.data.status === 'success') {
            output.style.color = '#10b981';
            output.innerText = "🔥 [نجاح هجوم CORS - تم تسريب البيانات المصرفية السرية بنجاح]:\n\n" + JSON.stringify(result.data, null, 4);
            setTimeout(() => location.reload(), 1500);
        } else {
            output.style.color = '#ef4444';
            output.innerText = "⛔ [تم صد الهجوم بنجاح في الوضع المحمي]:\n\nHTTP Status: " + result.status + "\n" + JSON.stringify(result.data, null, 4);
        }
    })
    .catch(err => {
        output.style.color = '#ef4444';
        output.innerText = "Network Error: " + err;
    });
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
