<?php
$page_title = "مقارنات PHP الضعيفة (PHP Type Juggling) | مختبر الجوكر الأمني";
require_once __DIR__ . '/../includes/header.php';

$sec = get_security_level();
$msg = "";

// المفتاح السري المخصص للنظام
$secret_api_key = "J0K3R_S3CR3T_K3Y_999";
// الهاش السري السحري (Magic Hash يبدأ بـ 0e)
$secret_magic_hash = "0e830400451993494058024219903391";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submitted_key = $_POST['api_key'] ?? null;
    $magic_input = $_POST['magic_input'] ?? null;

    if ($sec === 'low') {
        // سيناريو 1: محاكاة strcmp bypass عبر مصفوفة
        $strcmp_match = false;
        if (isset($_POST['api_key'])) {
            try {
                $strcmp_match = (@strcmp($secret_api_key, $submitted_key) == 0);
            } catch (TypeError $e) {
                // في إصدارات PHP 8+ تُرجع الدالة TypeError عند تمرير مصفوفة بدلاً من NULL كما في PHP 7
                if (is_array($submitted_key)) {
                    $strcmp_match = true;
                }
            }
        }

        // سيناريو 2: مقارنة الهاش السحري Magic Hash بالمعامل ==
        $magic_match = false;
        if (!empty($magic_input)) {
            $hashed = md5($magic_input);
            if ($hashed == $secret_magic_hash) {
                $magic_match = true;
            }
        }

        if ($strcmp_match || $magic_match) {
            $msg = "<div class='alert alert-success'><i class='fas fa-trophy me-1'></i> أحسنت! تم تجاوز التحقق بنجاح عبر استغلال ثغرة PHP Type Juggling!</div>";
            award_flag('type_juggling');
        } else {
            $msg = "<div class='alert alert-danger'><i class='fas fa-times me-1'></i> المفتاح أو القيمة المدخلة غير صحيحة!</div>";
        }
    } else {
        // كود آمن: التحقق من نوع البيانات واستخدام المقارنة الصارمة === أو hash_equals
        $is_valid = false;
        if (is_string($submitted_key) && hash_equals($secret_api_key, $submitted_key)) {
            $is_valid = true;
        }
        if (is_string($magic_input) && hash_equals($secret_magic_hash, md5($magic_input))) {
            $is_valid = true;
        }

        if ($is_valid) {
            $msg = "<div class='alert alert-success'><i class='fas fa-shield-alt me-1'></i> تم التحقق بنجاح وبشكل آمن تماماً عبر دالة hash_equals().</div>";
        } else {
            $msg = "<div class='alert alert-danger'><i class='fas fa-times me-1'></i> تم حظر الطلب! القيمة غير متطابقة أو نوع البيانات غير مسموح به.</div>";
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

<div class="card card-cyber mb-4">
    <div class="card-cyber-header d-flex justify-content-between align-items-center">
        <h4 class="mb-0 text-info"><i class="fas fa-fingerprint me-2"></i> بوابة تفعيل مفتاح الـ API السري (Secret Key Validator)</h4>
        <span class="badge bg-secondary">تحدي Type Juggling</span>
    </div>
    <div class="card-body p-4">
        <p class="text-light">
            <strong>الهدف:</strong> تجاوز التحقق من المفتاح السري دون معرفة قيمته الأصلية من خلال استغلال خلل المقارنة بدالة <code>strcmp()</code> أو استغلال الهاشات السحرية (Magic Hashes).
        </p>

        <?= render_flag_box('type_juggling'); ?>
        <?= $msg; ?>

        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="p-3 bg-dark rounded border border-secondary h-100">
                    <h5 class="text-white mb-2"><i class="fas fa-magic text-warning me-2"></i> 1. هجوم تجاوز دالة strcmp:</h5>
                    <p class="text-muted small">عند إرسال مصفوفة <code>api_key[]=bypass</code> بدلاً من نص، تفشل الدالة في المقارنة وتسمح بالتجاوز.</p>
                    <form method="POST">
                        <input type="hidden" name="api_key[]" value="bypass">
                        <button type="submit" class="btn btn-warning fw-bold w-100">
                            <i class="fas fa-bolt me-1"></i> إرسال بايلود المصفوفة (Array Bypass)
                        </button>
                    </form>
                </div>
            </div>

            <div class="col-md-6 mb-4">
                <div class="p-3 bg-dark rounded border border-secondary h-100">
                    <h5 class="text-white mb-2"><i class="fas fa-hashtag text-info me-2"></i> 2. هجوم الهاشات السحرية (Magic Hashes):</h5>
                    <p class="text-muted small">كلمة <code>240610708</code> ينتج عنها MD5 يبدأ بـ <code>0e...</code> ويساوي صفراً عند مقارنته بـ <code>==</code> مع هاش سري آخر يبدأ بـ <code>0e...</code>!</p>
                    <form method="POST">
                        <input type="hidden" name="magic_input" value="240610708">
                        <button type="submit" class="btn btn-info fw-bold w-100">
                            <i class="fas fa-key me-1"></i> إرسال Magic Hash (240610708)
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <?= render_hints([
            "في لغة PHP، عند استخدام <code>==</code> مع نصين يبدآن بـ <code>0e</code> ومتبوعين بأرقام فقط، يعاملهما PHP كترميز علمي للأرقام (Scientific Notation) وكل منهما يساوي <code>0</code>!",
            "أي أن: <code>'0e4620974319...' == '0e8304004519...'</code> نتيجتها <strong>TRUE</strong>!",
            "اضغط على أي من الزرين بالأعلى لتنفيذ الهجوم عملياً والحصول على العلم."
        ]); ?>

        <?= render_code_comparison(
            '// كود مصاب\nif ($entered_hash == $stored_hash) { grant(); }\nif (strcmp($secret, $_POST["key"]) == 0) { grant(); }',
            '// كود آمن (استخدام hash_equals والمقارنة الصارمة ===)\nif (is_string($key) && hash_equals($secret, $key)) {\n    grant();\n}',
            'استخدم دائماً دالة hash_equals() لمقارنة السلاسل الحساسة والتوكنات، وتأكد أولاً أن القيمة نصية باستخدام is_string() لمنع أي سلوك غير متوقع.'
        ); ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
