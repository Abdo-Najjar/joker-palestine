<?php
$page_title = "مقارنات PHP الضعيفة (PHP Type Juggling) | مختبر الجوكر الأمني";
require_once __DIR__ . '/../includes/header.php';

$sec = get_security_level();
$msg = "";
$debug_info = "";

// المفتاح السري المخصص للنظام
$secret_api_key = "J0K3R_S3CR3T_K3Y_999";
// الهاش السري السحري (Magic Hash يبدأ بـ 0e ويليه أرقام فقط)
$secret_magic_hash = "0e830400451993494058024219903391";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submitted_key = $_POST['api_key'] ?? null;
    $magic_input = $_POST['magic_input'] ?? null;

    if ($sec === 'low') {
        $strcmp_match = false;
        // سيناريو 1: فحص المفتاح عبر strcmp مع المقارنة الضعيفة == 0
        if (isset($_POST['api_key'])) {
            if (is_array($submitted_key)) {
                // في PHP 7 تعيد strcmp قيمة NULL عند تمرير مصفوفة، وبما أن NULL == 0 في المقارنة الضعيفة فإنها تنجح!
                $strcmp_match = true;
                $debug_info = "تم إرسال مصفوفة Array بنجاح! دالة strcmp تعيد NULL وعند مقارنتها بـ (NULL == 0) تصبح النتيجة TRUE!";
            } else {
                $res = @strcmp($secret_api_key, $submitted_key);
                if ($res == 0) {
                    $strcmp_match = true;
                } else {
                    $debug_info = "المفتاح المدخل: '" . htmlspecialchars($submitted_key) . "' لا يطابق المفتاح السري المخزن.";
                }
            }
        }

        // سيناريو 2: فحص الهاش السحري بالمعامل الضعيف ==
        $magic_match = false;
        if (isset($_POST['magic_input']) && $_POST['magic_input'] !== '') {
            $hashed = md5($magic_input);
            // المقارنة الضعيفة == تعامل أي نصين يبدآن بـ 0e ومتبوعين بأرقام فقط كأرقام علمية (0e... == 0)
            if ($hashed == $secret_magic_hash) {
                $magic_match = true;
                $debug_info = "الهاش المحسوب: <code>$hashed</code><br>الهاش المخزن: <code>$secret_magic_hash</code><br>كلاهما يبدأ بـ 0e ومتبوعين بأرقام، لذا يعتبرهما PHP: (0 == 0) أي TRUE!";
            } else {
                $debug_info = "قيمة MD5 لمدخلك هي: <code>$hashed</code> وهي لا تبدأ بـ 0e متبوعة بأرقام، لذا فشلت المقارنة.";
            }
        }

        if ($strcmp_match || $magic_match) {
            $msg = "<div class='alert alert-success shadow-sm'>
                <h5 class='fw-bold mb-1'><i class='fas fa-trophy me-2 text-warning'></i> أحسنت! تم تجاوز التحقق بنجاح عبر استغلال ثغرة PHP Type Juggling!</h5>
                <p class='mb-0 small'>$debug_info</p>
            </div>";
            award_flag('type_juggling');
        } else {
            $msg = "<div class='alert alert-danger shadow-sm'>
                <div class='fw-bold mb-1'><i class='fas fa-times-circle me-1'></i> فشل التحقق: القيمة المدخلة غير صحيحة!</div>
                <div class='small'>$debug_info</div>
            </div>";
        }
    } else {
        // كود آمن: التحقق من نوع البيانات والمقارنة الصارمة
        $is_valid = false;
        if (isset($_POST['api_key'])) {
            if (is_string($submitted_key) && hash_equals($secret_api_key, $submitted_key)) {
                $is_valid = true;
            }
        }
        if (isset($_POST['magic_input'])) {
            if (is_string($magic_input) && hash_equals($secret_magic_hash, md5($magic_input))) {
                $is_valid = true;
            }
        }

        if ($is_valid) {
            $msg = "<div class='alert alert-success'><i class='fas fa-shield-alt me-1'></i> تم التحقق بنجاح وبشكل آمن تماماً عبر دالة hash_equals().</div>";
        } else {
            $msg = "<div class='alert alert-danger'><i class='fas fa-shield-alt me-1'></i> تم حظر الطلب! فشل التطابق الصارم وتم رفض أي مصفوفات أو مقارنات غير آمنة.</div>";
        }
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom border-secondary">
    <div>
        <h2 class="fw-bold text-white mb-1"><i class="fas fa-balance-scale text-info me-2"></i> 12. مقارنات PHP الضعيفة وتغيير الأنواع (Type Juggling)</h2>
        <p class="text-muted mb-0">سلوك خاص بلغة PHP ينشأ عند استخدام المقارنة الضعيفة (<code>==</code>) بدلاً من الصارمة (<code>===</code>)، أو سوء استخدام دوال المقارنة مثل <code>strcmp()</code> والهاشات السحرية (Magic Hashes).</p>
    </div>
    <span class="badge bg-info text-dark fs-6 px-3 py-2"><i class="fas fa-code-branch me-1"></i> ثغرة في منطق المقارنة (Logic Flaw)</span>
</div>

<!-- صندوق الشرح المفصل لآلية الثغرة -->
<div class="card card-cyber mb-4">
    <div class="card-header bg-dark text-info fw-bold py-2">
        <i class="fas fa-book-open me-2"></i> شرح تفصيلي معمق: ما هي ثغرة Type Juggling وكيف تحدث؟
    </div>
    <div class="card-body p-3">
        <div class="row">
            <div class="col-md-6 mb-3">
                <h6 class="text-warning fw-bold"><i class="fas fa-question-circle me-1"></i> الفرق بين المقارنة الضعيفة والصارمة:</h6>
                <p class="text-light small mb-2">
                    في لغة PHP، المعامل <code>==</code> يقوم بتحويل أنواع البيانات تلقائياً (Type Casting) لمحاولة جعلهما متطابقين، بينما المعامل <code>===</code> يتحقق من تطابق القيمة ونوع البيانات معاً:
                </p>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered table-dark-custom small" style="direction:ltr; text-align:left;">
                        <thead class="table-dark">
                            <tr><th>المقارنة</th><th>النوع</th><th>النتيجة</th><th>التفسير البرمجي</th></tr>
                        </thead>
                        <tbody>
                            <tr><td><code>"0" == 0</code></td><td>Weak (==)</td><td class="text-success fw-bold">TRUE</td><td>تم تحويل النص إلى رقم 0</td></tr>
                            <tr><td><code>"0" === 0</code></td><td>Strict (===)</td><td class="text-danger fw-bold">FALSE</td><td>الأنواع مختلفة (String vs Integer)</td></tr>
                            <tr><td><code>"0e123" == "0e999"</code></td><td>Weak (==)</td><td class="text-success fw-bold">TRUE</td><td>كلاهما 0 في الترميز العلمي 0^123 = 0</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="col-md-6 mb-3">
                <h6 class="text-warning fw-bold"><i class="fas fa-bug me-1"></i> ثغرة دالة strcmp() الشهيرة:</h6>
                <p class="text-light small">
                    دالة <code>strcmp($str1, $str2)</code> تتوقع نصين وترجع <code>0</code> إذا تطابقا. إذا تم تمرير مصفوفة (Array) بدلاً من نص، تعيد الدالة <code>NULL</code> وتُصدر تحذيراً فقط.
                </p>
                <div class="p-2 bg-dark rounded border border-secondary small text-light" style="direction:ltr; text-align:left;">
                    <div class="text-info">// الكود المصاب في السيرفر:</div>
                    <code>if (strcmp($secret, $_POST['key']) == 0)</code><br>
                    <div class="text-muted">// إذا أرسل المهاجم: key[]='bypass'</div>
                    <code>strcmp($secret, Array) -> NULL</code><br>
                    <code>(NULL == 0) -> TRUE! (تجاوز فوري!)</code>
                </div>
            </div>
        </div>
    </div>
</div>

<?= render_flag_box('type_juggling'); ?>
<?= $msg; ?>

<div class="row g-4 mb-4">

    <!-- ==================== التحدي الأول: تجاوز strcmp ==================== -->
    <div class="col-lg-6">
        <div class="card card-cyber h-100">
            <div class="card-cyber-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-info"><i class="fas fa-fingerprint me-2"></i> 1. تجاوز دالة strcmp عبر المصفوفات</h5>
                <span class="badge bg-secondary">تحدي #1</span>
            </div>
            <div class="card-body p-4">
                <p class="text-light small">
                    <strong>السيناريو:</strong> السيرفر يفحص المفتاح السري باستخدام دالة <code>strcmp()</code> مع المقارنة الضعيفة <code>== 0</code>.
                </p>

                <!-- نموذج الإدخال العادي لتجربة القيم الخاطئة -->
                <form method="POST" action="type_juggling.php" class="p-3 bg-dark rounded border border-secondary mb-3">
                    <label class="form-label text-light small fw-bold">أدخل المفتاح السري لتجربة الفحص العادي:</label>
                    <div class="input-group mb-2">
                        <input type="text" id="manual-key" name="api_key" dir="ltr" class="form-control font-monospace" style="direction:ltr; text-align:left;" placeholder="اكتب أي نص لتجربة الفشل..." value="test_123">
                        <button type="submit" class="btn btn-outline-info fw-bold">فحص عادي</button>
                    </div>
                    <small class="text-muted d-block">جرب إرسال نص عادي وسترى أنه يرفض لعدم مطابقة المفتاح.</small>
                </form>

                <!-- نموذج إرسال بايلود المصفوفة بنقرة زر -->
                <div class="p-3 bg-danger bg-opacity-10 rounded border border-danger">
                    <h6 class="text-danger fw-bold mb-2"><i class="fas fa-bolt me-1"></i> تنفيذ هجوم بايلود المصفوفة (Array Payload):</h6>
                    <p class="text-light small mb-2">
                        يتم إرسال اسم المعامل كمصفوفة <span dir="ltr" class="badge bg-black text-warning font-monospace px-2 py-1">api_key[]=bypass</span> بدلاً من نص، مما يجعل الدالة تُرجع <code>NULL</code> ويكون <code>NULL == 0</code> صحيحاً:
                    </p>
                    <form method="POST" action="type_juggling.php">
                        <input type="hidden" name="api_key[]" value="bypass">
                        <button type="submit" class="btn btn-warning fw-bold w-100 shadow">
                            <i class="fas fa-magic me-1"></i> إرسال بايلود المصفوفة وتجاوز الفحص (Bypass via Array)
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- ==================== التحدي الثاني: الهاش السحري ==================== -->
    <div class="col-lg-6">
        <div class="card card-cyber h-100">
            <div class="card-cyber-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-warning"><i class="fas fa-hashtag me-2"></i> 2. تجاوز الهاش السحري (Magic Hashes)</h5>
                <span class="badge bg-secondary">تحدي #2</span>
            </div>
            <div class="card-body p-4">
                <p class="text-light small">
                    <strong>السيناريو:</strong> السيرفر يخزن الهاش السري:
                    <span dir="ltr" class="badge bg-black text-info font-monospace d-block my-1 text-center py-1">0e830400451993494058024219903391</span>
                    ويقوم بمطابقته مع هاش مدخلك باستخدام المقارنة الضعيفة <code>$hash == $stored_hash</code>.
                </p>

                <!-- نموذج إدخال نص عادي لحسابه بـ md5 -->
                <form method="POST" action="type_juggling.php" class="p-3 bg-dark rounded border border-secondary mb-3">
                    <label class="form-label text-light small fw-bold">أدخل أي نص لتجربة الفحص العادي:</label>
                    <div class="input-group mb-2">
                        <input type="text" id="manual-magic" name="magic_input" dir="ltr" class="form-control font-monospace" style="direction:ltr; text-align:left;" placeholder="اكتب كلمة سر عادية..." value="password123">
                        <button type="submit" class="btn btn-outline-warning fw-bold">فحص الهاش</button>
                    </div>
                    <small class="text-muted d-block">كلمة <code>password123</code> لن ينتج عنها هاش يبدأ بـ 0e لذا ستفشل.</small>
                </form>

                <!-- نموذج إرسال الهاش السحري -->
                <div class="p-3 bg-info bg-opacity-10 rounded border border-info">
                    <h6 class="text-info fw-bold mb-2"><i class="fas fa-key me-1"></i> إرسال قيمة الهاش السحري المعروفة عالمياً:</h6>
                    <p class="text-light small mb-2">
                        القيمة <span dir="ltr" class="badge bg-black text-warning font-monospace px-2 py-1">240610708</span> عند تشفيرها بـ MD5 تنتج:
                        <span dir="ltr" class="badge bg-black text-success font-monospace d-block my-1 text-center py-1">0e462097431906509019562988736854</span>
                        وكلاهما يساوي <code>0</code> في الترميز العلمي! (0 == 0 -> TRUE):
                    </p>
                    <form method="POST" action="type_juggling.php">
                        <input type="hidden" name="magic_input" value="240610708">
                        <button type="submit" class="btn btn-info text-dark fw-bold w-100 shadow">
                            <i class="fas fa-check-circle me-1"></i> إرسال القيمة السحرية 240610708 وتجاوز المقارنة
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>

<?= render_hints([
    "في تحدي strcmp: جرب إدخال نص عادي أولاً واضغط (فحص عادي) لترى رسالة الفشل، ثم اضغط على زر (إرسال بايلود المصفوفة) لترى كيف ينجح التجاوز فوراً!",
    "في تحدي الهاش السحري: أي نصين مشفرين بـ MD5 ويبدآن بـ <code>0e</code> ويليهما أرقام فقط، يعاملهما PHP كـ <code>0</code> في المقارنة الضعيفة <code>==</code>.",
    "جرب تغيير مستوى الحماية بالأعلى إلى <strong>Secure</strong> واضغط على أزرار التجاوز مرة أخرى، ولاحظ كيف يتم صدهما فوراً بفضل دالة <code>hash_equals()</code> والمقارنة الصارمة <code>===</code>."
]); ?>

<?= render_code_comparison(
    '// كود مصاب في PHP (المقارنة الضعيفة)\nif (strcmp($secret, $_POST["api_key"]) == 0) {\n    grant_access(); // ينجح إذا تم إرسال مصفوفة\n}\n\nif (md5($_POST["password"]) == $stored_hash) {\n    grant_access(); // ينجح إذا كان الهاشان 0e...\n}',
    '// كود آمن 100% (المقارنة الصارمة وفحص النوع)\nif (is_string($_POST["api_key"]) && hash_equals($secret, $_POST["api_key"])) {\n    grant_access(); // آمن ضد هجوم المصفوفات\n}\n\nif (is_string($_POST["password"]) && hash_equals($stored_hash, md5($_POST["password"]))) {\n    grant_access(); // آمن ضد Magic Hashes\n}',
    'القاعدة الذهبية في PHP: استخدم دائماً دالة hash_equals() لمقارنة كلمات المرور والتوكنات والمفاتيح السرية، وافحص النوع مسبقاً عبر is_string()، وتجنب تماماً استخدام == مع السلاسل النصية الحساسة.'
); ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
