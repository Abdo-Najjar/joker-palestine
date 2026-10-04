<?php
// ==============================================================================
// مشروع: مختبر الجوكر الفلسطيني لاختبار اختراق تطبيقات الويب (Joker Security Lab)
// التحدي 17: فك التسلسل غير الآمن للبيانات (Insecure Deserialization / PHP Object Injection)
// إعداد وتطوير: المهندس احمد سليم 🇵🇸
// ==============================================================================

$page_title = "فك التسلسل غير الآمن (Deserialization) | مختبر الجوكر الفلسطيني";
require_once __DIR__ . '/../includes/header.php';

$sec = get_security_level();
$deserialized_output = null;
$error_msg = "";
$success_msg = "";
$exploit_triggered = false;

// فئة برمجية توضح مفهوم الدوال السحرية (Magic Methods) في حقن كائنات PHP
class JokerLabLogger {
    public $logfile = "activity.log";
    public $action = "read";
    public $is_admin = false;
    public $status_note = "جلسة تدريبية اعتيادية";

    public function __construct($file = "activity.log", $action = "read", $admin = false) {
        $this->logfile = $file;
        $this->action = $action;
        $this->is_admin = $admin;
    }

    // الدالة السحرية __destruct تُنفذ تلقائياً عند تدمير الكائن في نهاية السكربت أو فك التسلسل
    public function get_execution_summary() {
        if ($this->is_admin) {
            return "🔥 [صلاحيات مدير النظام مكتسبة عبر حقن الكائن] - تم الوصول لسجل السيرفر السري: " . htmlspecialchars($this->logfile);
        }
        return "ℹ️ [جلسة مستخدم عادي] - تم استعراض الملف: " . htmlspecialchars($this->logfile);
    }
}

// حمولات جاهزة للتجربة التفاعلية
$normal_object = new JokerLabLogger("guest_session.log", "view", false);
$normal_serialized = serialize($normal_object);

$exploit_object = new JokerLabLogger("secret_note.txt", "pwn_admin", true);
$exploit_serialized = serialize($exploit_object);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['serialized_payload'])) {
    $raw_input = trim($_POST['serialized_payload']);

    if (empty($raw_input)) {
        $error_msg = "يرجى إدخال نص تسلسل البيانات (Serialized String).";
    } else {
        if ($sec === 'low') {
            // ❌ كود مصاب: فك تسلسل مدخلات المستخدم مباشرة باستخدام unserialize() دون فحص
            try {
                // تفعيل الدالة الخطيرة التي تؤدي إلى PHP Object Injection
                $unserialized = @unserialize($raw_input);

                if ($unserialized === false && $raw_input !== serialize(false)) {
                    $error_msg = "خطأ: نص التسلسل البرمجي غير صالح (Corrupted Serialization).";
                } else {
                    $deserialized_output = $unserialized;
                    $success_msg = "تم فك تسلسل الكائن بنجاح في ذاكرة السيرفر!";

                    // التحقق مما إذا كان الكائن المحقون يحمل صلاحيات مدير النظام
                    if (is_object($unserialized) && ($unserialized instanceof JokerLabLogger || property_exists($unserialized, 'is_admin'))) {
                        if (!empty($unserialized->is_admin) && $unserialized->is_admin === true) {
                            $exploit_triggered = true;
                            award_flag('deserialization');
                        }
                    }
                }
            } catch (Throwable $e) {
                $error_msg = "استثناء أثناء فك التسلسل: " . htmlspecialchars($e->getMessage());
            }
        } else {
            // ✅ كود محمي: حظر فك تسلسل كائنات PHP واستخدام JSON الآمن أو منع الفئات
            if (preg_match('/^O:\d+:/', $raw_input) || stripos($raw_input, 'JokerLabLogger') !== false) {
                $error_msg = "⛔ [WAF & Secure Object Guard] تم حظر الطلب! تم اكتشاف محاولة حقن كائن PHP (PHP Object Injection Blocked).";
            } else {
                // في الوضع المحمي نستخدم خيار منع الفئات تماماً allowed_classes => false أو JSON
                $unserialized = @unserialize($raw_input, ['allowed_classes' => false]);
                if ($unserialized === false) {
                    $error_msg = "تم رفض البيانات: التنسيق المقبول في الوضع المحمي هو البيانات القياسية فقط دون كائنات برمجية.";
                } else {
                    $deserialized_output = $unserialized;
                    $success_msg = "تم فك التسلسل بأمان تام مع حظر استدعاء أي فئات أو دوال سحرية (allowed_classes: false).";
                }
            }
        }
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom border-secondary">
    <div>
        <h2 class="fw-bold text-white mb-1">
            <i class="fas fa-boxes-stacked text-warning me-2"></i> 17. فك التسلسل غير الآمن للبيانات (Insecure Deserialization / Object Injection)
        </h2>
        <p class="text-muted mb-0">
            تحدث ثغرة Insecure Deserialization (CWE-502) عندما يقوم التطبيق بفك تسلسل كائنات برمجية غير موثوقة باستخدام <code>unserialize()</code>، مما يسمح للمهاجم بالتلاعب بخصائص الكائنات وتفعيل الدوال السحرية لتصعيد الصلاحيات أو تنفيذ الأوامر.
        </p>
    </div>
    <span class="badge bg-danger fs-6 px-3 py-2">
        <i class="fas fa-magic me-1"></i> OWASP Top 10 - Software & Data Integrity Failures
    </span>
</div>

<!-- Architecture Flow Diagram Card -->
<div class="card card-cyber mb-4">
    <div class="card-cyber-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0 text-info"><i class="fas fa-project-diagram me-2"></i> مسار تدفق هجوم حقن كائنات PHP (PHP Object Injection Flow)</h5>
        <span class="badge bg-dark border border-info text-info">Serialization Architecture</span>
    </div>
    <div class="card-body p-4 text-center">
        <div class="row align-items-center g-3">
            <div class="col-md-3">
                <div class="p-3 rounded bg-black border border-warning text-center h-100">
                    <span class="badge bg-warning text-dark mb-2">1. تحريف السلسلة</span>
                    <pre class="text-warning small mb-0 font-monospace text-start" style="direction:ltr;">O:14:"JokerLabLogger":3:{... s:8:"is_admin";b:1;}</pre>
                    <small class="text-light text-opacity-75 d-block mt-2">المهاجم يعدل الخاصية <code>is_admin</code> من <code>b:0</code> إلى <code>b:1</code></small>
                </div>
            </div>
            <div class="col-md-1 d-flex justify-content-center">
                <i class="fas fa-arrow-left text-info fs-3 d-none d-md-block"></i>
                <i class="fas fa-arrow-down text-info fs-3 d-md-none"></i>
            </div>
            <div class="col-md-4">
                <div class="p-3 rounded bg-black border border-danger text-center h-100">
                    <span class="badge bg-danger mb-2">2. استدعاء unserialize()</span>
                    <pre class="text-danger small mb-0 font-monospace text-start" style="direction:ltr;">$obj = unserialize($input);</pre>
                    <small class="text-light text-opacity-75 d-block mt-2">السيرفر ينشئ كائناً جديداً في الذاكرة بالقيم الخبيثة دون التحقق من مصدرها!</small>
                </div>
            </div>
            <div class="col-md-1 d-flex justify-content-center">
                <i class="fas fa-arrow-left text-success fs-3 d-none d-md-block"></i>
                <i class="fas fa-arrow-down text-success fs-3 d-md-none"></i>
            </div>
            <div class="col-md-3">
                <div class="p-3 rounded bg-black border border-success text-center h-100">
                    <span class="badge bg-success mb-2">3. الدوال السحرية والتصعيد</span>
                    <pre class="text-success small mb-0 font-monospace text-start" style="direction:ltr;">__destruct() / RCE</pre>
                    <small class="text-light text-opacity-75 d-block mt-2">تنفيذ الأكواد الحساسة بصلاحيات المدير المزور واقتناص العلم!</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Interactive Challenge Card -->
<div class="card card-cyber mb-4">
    <div class="card-cyber-header d-flex justify-content-between align-items-center">
        <h4 class="mb-0 text-info"><i class="fas fa-database me-2"></i> بوابة استعادة كائنات الجلسة وسجلات النظام (Session & Object State Restorer)</h4>
        <span class="badge bg-secondary">تحدي Deserialization</span>
    </div>
    <div class="card-body p-4">
        <p class="text-light">
            <strong>الهدف:</strong> قم باستغلال دالة <code>unserialize()</code> في المستوى الضعيف وحقن كائن <code>JokerLabLogger</code> بخاصية <code>is_admin = true</code> لاقتناص علم التحدي.
        </p>

        <?= render_flag_box('deserialization'); ?>

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
                            <i class="fas fa-stream me-1"></i> نص تسلسل كائن PHP (Serialized String):
                        </label>
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-outline-info" onclick="loadPayload('normal')">كائن مستخدم عادي</button>
                            <button type="button" class="btn btn-outline-danger" onclick="loadPayload('exploit')">حمولة تصعيد الصلاحيات (Admin Exploit)</button>
                        </div>
                    </div>

                    <textarea id="serialized_payload" name="serialized_payload" rows="6" class="form-control font-monospace bg-black text-light border-secondary mb-3" style="direction:ltr; text-align:left; font-size:0.88rem;" required><?= htmlspecialchars($_POST['serialized_payload'] ?? $normal_serialized) ?></textarea>

                    <button type="submit" class="btn btn-warning text-dark fw-bold px-4">
                        <i class="fas fa-bolt me-1"></i> إرسال وفك التسلسل في الذاكرة (Unserialize)
                    </button>
                </form>
            </div>

            <div class="col-lg-5 mt-4 mt-lg-0">
                <div class="card bg-dark border-secondary h-100">
                    <div class="card-header bg-black text-white d-flex justify-content-between align-items-center py-2">
                        <span class="small fw-bold"><i class="fas fa-microchip text-info me-1"></i> معاينة حالة الكائن في الذاكرة:</span>
                        <span class="badge bg-secondary"><?= $sec === 'low' ? 'وضع Low (مكشوف)' : 'وضع Secure (محمي)' ?></span>
                    </div>
                    <div class="card-body p-3">
                        <?php if ($deserialized_output !== null): ?>
                            <div class="mb-3">
                                <span class="badge <?= $exploit_triggered ? 'bg-danger' : 'bg-info text-dark' ?> mb-2">
                                    <?= is_object($deserialized_output) ? 'PHP Object: ' . get_class($deserialized_output) : 'Raw Type: ' . gettype($deserialized_output) ?>
                                </span>
                                <pre class="bg-black text-light p-2 rounded small mb-0 font-monospace" style="direction:ltr; text-align:left; max-height: 200px; overflow-y: auto;"><?= htmlspecialchars(print_r($deserialized_output, true)) ?></pre>
                            </div>
                            <?php if (is_object($deserialized_output) && method_exists($deserialized_output, 'get_execution_summary')): ?>
                                <div class="p-2 rounded <?= $exploit_triggered ? 'bg-success bg-opacity-25 border border-success text-success' : 'bg-black border border-secondary text-light' ?> small">
                                    <?= $deserialized_output->get_execution_summary() ?>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="text-center text-muted py-5">
                                <i class="fas fa-boxes-stacked text-secondary fs-1 mb-2"></i>
                                <p class="mb-0">أرسل نص التسلسل لمعاينته بعد فك التسلسل هنا.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= render_hints([
    "في لغة PHP، تمثل الرموز: <code>O:14:\"JokerLabLogger\"</code> كائناً من الفئة JokerLabLogger بطول اسم 14 حرفاً.",
    "الرمز <code>s:8:\"is_admin\";b:0;</code> يمثل متغيراً نصياً اسمه is_admin وقيمته المنطقية <code>false</code> (b:0).",
    "إذا قام المهاجم بتعديل القيمة إلى <code>b:1</code> (true)، فإن السيرفر عند فك التسلسل يعامل الكائن وكأنه يملك صلاحيات المدير!",
    "اضغط على زر <strong>(حمولة تصعيد الصلاحيات)</strong> لمشاهدة كيف يتم تصعيد الصلاحيات واقتناص علم التحدي فوراً."
]); ?>

<?= render_code_comparison(
    '// ❌ كود مصاب: فك تسلسل كائنات PHP دون قيود\n$data = $_POST["serialized_payload"];\n$object = unserialize($data); // ثغرة كارثية: يستدعي الدوال السحرية وينشئ أي كائن!',
    '// ✅ كود محمي: استخدام JSON الآمن أو حظر الكائنات البرمجية\n$data = $_POST["serialized_payload"];\n// الخيار 1: استخدام تنسيق JSON القياسي\n$safe_data = json_decode($data, true);\n// الخيار 2: حظر الفئات تماماً إذا استلزم استخدام unserialize\n$safe_obj = unserialize($data, ["allowed_classes" => false]);',
    'لتفادي ثغرات Insecure Deserialization، يُمنع منعاً باتاً تمرير مدخلات المستخدم لدالة unserialize() في لغة PHP، ويجب استبدالها بصيغ تبادل البيانات الآمنة مثل JSON أو تشفير وتوقيع البيانات باستخدام HMAC.'
); ?>

<script>
function loadPayload(type) {
    const field = document.getElementById('serialized_payload');
    if (type === 'normal') {
        field.value = <?= json_encode($normal_serialized) ?>;
    } else if (type === 'exploit') {
        field.value = <?= json_encode($exploit_serialized) ?>;
    }
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
