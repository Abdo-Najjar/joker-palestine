<?php
// ==============================================================================
// مشروع: مختبر الجوكر الفلسطيني لاختبار اختراق تطبيقات الويب (Joker Security Lab)
// التحدي 15: التلاعب بتوكنات المصادقة (JSON Web Token - JWT Attacks)
// إعداد وتطوير: المهندس احمد سليم 🇵🇸
// ==============================================================================

$page_title = "تزوير توكنات المصادقة (JWT) | مختبر الجوكر الفلسطيني";
require_once __DIR__ . '/../includes/header.php';

$sec = get_security_level();
$jwt_secret = "joker_secret_key_2026";

// دوال مساعدة لترميز وفك ترميز Base64Url
function base64url_encode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function base64url_decode($data) {
    return base64_decode(strtr($data, '-_', '+/') . str_repeat('=', 3 - (3 + strlen($data)) % 4));
}

function create_jwt($header_data, $payload_data, $secret = null) {
    $header_encoded = base64url_encode(json_encode($header_data, JSON_UNESCAPED_UNICODE));
    $payload_encoded = base64url_encode(json_encode($payload_data, JSON_UNESCAPED_UNICODE));
    
    if (strtolower($header_data['alg'] ?? '') === 'none' || empty($secret)) {
        return $header_encoded . '.' . $payload_encoded . '.';
    }
    
    $signature = hash_hmac('sha256', "$header_encoded.$payload_encoded", $secret, true);
    $signature_encoded = base64url_encode($signature);
    return "$header_encoded.$payload_encoded.$signature_encoded";
}

// التوكن الافتراضي للمستخدم العادي (Guest)
$default_guest_jwt = create_jwt(
    ['alg' => 'HS256', 'typ' => 'JWT'],
    [
        'user_id' => 1042,
        'username' => 'guest_user',
        'role' => 'guest',
        'iat' => 1775200000,
        'lab' => 'Joker Lab v2.0'
    ],
    $jwt_secret
);

// حمولة هجوم None Algorithm
$exploit_none_jwt = create_jwt(
    ['alg' => 'none', 'typ' => 'JWT'],
    [
        'user_id' => 1,
        'username' => 'admin',
        'role' => 'admin',
        'iat' => 1775200000,
        'lab' => 'Joker Lab v2.0'
    ],
    null
);

$current_token = $_POST['token_input'] ?? ($_COOKIE['joker_jwt_auth'] ?? $default_guest_jwt);

$jwt_header = [];
$jwt_payload = [];
$jwt_sig_raw = "";
$auth_user = null;
$error_msg = "";
$success_msg = "";

if (!empty($current_token)) {
    $parts = explode('.', $current_token);
    if (count($parts) >= 2) {
        $jwt_header = json_decode(base64url_decode($parts[0]), true) ?: [];
        $jwt_payload = json_decode(base64url_decode($parts[1]), true) ?: [];
        $jwt_sig_raw = $parts[2] ?? '';

        $alg = $jwt_header['alg'] ?? 'HS256';

        if ($sec === 'low') {
            // ثغرة: قبول خوارزمية none وتجاوز التحقق من التوقيع!
            if (strtolower($alg) === 'none' || empty($jwt_sig_raw)) {
                $auth_user = $jwt_payload;
                $success_msg = "تم قبول التوكن بنجاح باستخدام خوارزمية [alg: none] دون فحص التوقيع!";
            } else {
                // في حال وجود توقيع، نقوم بفحصه لكن نتقبل التوكن
                $expected_sig = base64url_encode(hash_hmac('sha256', "{$parts[0]}.{$parts[1]}", $jwt_secret, true));
                if (hash_equals($expected_sig, $jwt_sig_raw)) {
                    $auth_user = $jwt_payload;
                    $success_msg = "تم التحقق من صحة توقيع التوكن بنجاح.";
                } else {
                    $error_msg = "توقيع التوكن غير صالح! جرب استغلال ثغرة 'alg': 'none'.";
                }
            }

            // فحص تصعيد الصلاحيات والحصول على العلم
            if ($auth_user && (($auth_user['role'] ?? '') === 'admin' || ($auth_user['username'] ?? '') === 'admin')) {
                award_flag('jwt');
            }
        } else {
            // كود محمي: حظر خوارزمية none وإلزام التحقق الصارم من توقيع HS256
            if (strtolower($alg) === 'none') {
                $error_msg = "⛔ [WAF & JWT Guard] تم رفض التوكن! الخوارزمية 'none' محظورة كلياً لمنع التزوير.";
            } elseif ($alg !== 'HS256') {
                $error_msg = "⛔ خوارزمية غير مدعومة ($alg). يُسمح فقط بـ HS256.";
            } else {
                $expected_sig = base64url_encode(hash_hmac('sha256', "{$parts[0]}.{$parts[1]}", $jwt_secret, true));
                if (hash_equals($expected_sig, $jwt_sig_raw)) {
                    $auth_user = $jwt_payload;
                    $success_msg = "توكن آمن وموثق: تم التحقق من سلامة التوقيع عبر HMAC-SHA256.";
                } else {
                    $error_msg = "⛔ تم رفض التوكن! توقيع JWT لا يتطابق مع المفتاح السري المعتمد.";
                }
            }
        }
    } else {
        $error_msg = "صيغة التوكن غير صحيحة! يجب أن تتكون من 3 أجزاء مفصولة بنقاط (header.payload.signature).";
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom border-secondary">
    <div>
        <h2 class="fw-bold text-white mb-1">
            <i class="fas fa-id-badge text-warning me-2"></i> 15. تزوير وتحريف توكنات المصادقة (JSON Web Token - JWT)
        </h2>
        <p class="text-muted mb-0">
            تحدث ثغرات JWT عند ثقة الخادم بحمولة التوكن دون التحقق الصارم من التوقيع الرقمي، أو عند قبول الخوارزمية غير المشفرة <code>"alg": "none"</code>.
        </p>
    </div>
    <span class="badge bg-danger fs-6 px-3 py-2">
        <i class="fas fa-user-shield me-1"></i> تجاوز المصادقة وتصعيد الصلاحيات (Privilege Escalation)
    </span>
</div>

<!-- Architecture Flow Diagram Card -->
<div class="card card-cyber mb-4">
    <div class="card-cyber-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0 text-info"><i class="fas fa-project-diagram me-2"></i> تشريح بنية توكن JWT وهجوم الخوارزمية None</h5>
        <span class="badge bg-dark border border-info text-info">JWT None Algorithm Flow</span>
    </div>
    <div class="card-body p-4 text-center">
        <div class="row align-items-center g-3">
            <div class="col-md-4">
                <div class="p-3 rounded bg-black border border-danger text-center h-100">
                    <span class="badge bg-danger mb-2">1. Header (الترويسة)</span>
                    <pre class="text-danger small mb-0 font-monospace text-start" style="direction:ltr;">{
  "alg": "none",
  "typ": "JWT"
}</pre>
                    <small class="text-light text-opacity-75 d-block mt-2">المهاجم يغير الخوارزمية إلى <code>none</code> لإخبار السيرفر بعدم الحاجة لتوقيع!</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3 rounded bg-black border border-primary text-center h-100">
                    <span class="badge bg-primary mb-2">2. Payload (الحمولة)</span>
                    <pre class="text-info small mb-0 font-monospace text-start" style="direction:ltr;">{
  "username": "admin",
  "role": "admin"
}</pre>
                    <small class="text-light text-opacity-75 d-block mt-2">تعديل رتبة المستخدم إلى مدير (Admin) للوصول للوحة التحكم السرية.</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3 rounded bg-black border border-success text-center h-100">
                    <span class="badge bg-success mb-2">3. Signature (التوقيع)</span>
                    <pre class="text-success small mb-0 font-monospace text-start" style="direction:ltr;">(فارغ / لا يوجد توقيع)</pre>
                    <small class="text-light text-opacity-75 d-block mt-2">يحذف المهاجم نص التوقيع ويترك نقطة النهاية فارغة <code>header.payload.</code></small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Interactive Challenge Card -->
<div class="card card-cyber mb-4">
    <div class="card-cyber-header d-flex justify-content-between align-items-center">
        <h4 class="mb-0 text-info"><i class="fas fa-key me-2"></i> بوابة مصادقة الـ API والمستخدمين (JWT Auth Portal)</h4>
        <span class="badge bg-secondary">تحدي JWT</span>
    </div>
    <div class="card-body p-4">
        <p class="text-light">
            <strong>الهدف:</strong> أنت مسجل حالياً كـ <code>guest_user</code>. قم بتعديل التوكن وتزويره لتصبح <code>admin</code> برتبة <code>role: "admin"</code> مستغلاً ثغرة <code>"alg": "none"</code> لاقتناص راية العلم.
        </p>

        <?= render_flag_box('jwt'); ?>

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

        <!-- Current Authentication Status Banner -->
        <div class="p-3 rounded mb-4 <?= ($auth_user && ($auth_user['role'] ?? '') === 'admin') ? 'bg-success bg-opacity-25 border border-success' : 'bg-dark border border-secondary' ?>">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-1 fw-bold <?= ($auth_user && ($auth_user['role'] ?? '') === 'admin') ? 'text-success' : 'text-light' ?>">
                        <i class="fas <?= ($auth_user && ($auth_user['role'] ?? '') === 'admin') ? 'fa-crown text-warning' : 'fa-user text-info' ?> me-2"></i>
                        حالة جلسة المستخدم الموثقة: 
                        <span class="badge <?= ($auth_user && ($auth_user['role'] ?? '') === 'admin') ? 'bg-danger fs-6' : 'bg-secondary' ?>">
                            <?= htmlspecialchars($auth_user['role'] ?? 'غير موثق') ?>
                        </span>
                    </h5>
                    <small class="text-light text-opacity-75">
                        اسم المستخدم: <strong><?= htmlspecialchars($auth_user['username'] ?? 'مجهول') ?></strong> | معرف المستخدم: <code><?= htmlspecialchars($auth_user['user_id'] ?? '-') ?></code>
                    </small>
                </div>
                <div>
                    <?php if ($auth_user && ($auth_user['role'] ?? '') === 'admin'): ?>
                        <span class="badge bg-warning text-dark fs-6 px-3 py-2 fw-bold shadow-sm">
                            <i class="fas fa-shield-alt me-1"></i> تم تسجيل الدخول كمدير للنظام!
                        </span>
                    <?php else: ?>
                        <span class="badge bg-dark border border-secondary text-muted px-3 py-2">
                            صلاحيات مستخدم عادي ضيف
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-7">
                <form method="POST" class="p-3 rounded bg-dark border border-secondary shadow-sm">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="form-label text-warning fw-bold mb-0">
                            <i class="fas fa-fingerprint me-1"></i> توكن JWT المُرسل في الطلب (Bearer Token):
                        </label>
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-outline-info" onclick="loadJwt('guest')">توكن الضيف (Guest)</button>
                            <button type="button" class="btn btn-outline-danger" onclick="loadJwt('exploit')">حمولة استغلال (None Alg)</button>
                        </div>
                    </div>

                    <textarea id="token_input" name="token_input" rows="4" class="form-control font-monospace bg-black text-warning border-secondary mb-3" style="direction:ltr; text-align:left; word-break:break-all; font-size:0.86rem;" required><?= htmlspecialchars($current_token) ?></textarea>

                    <button type="submit" class="btn btn-warning text-dark fw-bold px-4">
                        <i class="fas fa-paper-plane me-1"></i> إرسال التوكن للمصادقة وتحديث الجلسة
                    </button>
                </form>
            </div>

            <div class="col-lg-5 mt-4 mt-lg-0">
                <div class="card bg-dark border-secondary h-100">
                    <div class="card-header bg-black text-white d-flex justify-content-between align-items-center py-2">
                        <span class="small fw-bold"><i class="fas fa-search-dollar text-info me-1"></i> المفتش المباشر للتوكن (JWT Inspector):</span>
                        <span class="badge bg-info text-dark">RFC 7519</span>
                    </div>
                    <div class="card-body p-3">
                        <div class="mb-3">
                            <span class="badge bg-danger mb-1">Header (الترويسة المفسرة):</span>
                            <pre class="bg-black text-danger p-2 rounded small mb-0 font-monospace" style="direction:ltr; text-align:left;"><?= htmlspecialchars(json_encode($jwt_header, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
                        </div>
                        <div class="mb-3">
                            <span class="badge bg-primary mb-1">Payload (بيانات الحمولة):</span>
                            <pre class="bg-black text-info p-2 rounded small mb-0 font-monospace" style="direction:ltr; text-align:left;"><?= htmlspecialchars(json_encode($jwt_payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
                        </div>
                        <div class="mb-0">
                            <span class="badge bg-success mb-1">Signature (التوقيع الرقمي):</span>
                            <div class="bg-black text-success p-2 rounded small font-monospace" style="direction:ltr; text-align:left; word-break:break-all;">
                                <?= !empty($jwt_sig_raw) ? htmlspecialchars($jwt_sig_raw) : '<span class="text-muted">[لا يوجد توقيع - خوارزمية None]</span>' ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= render_hints([
    "يتكون توكن JWT من ثلاثة أجزاء تفصل بينها نقطة: <code>Header.Payload.Signature</code> وكل جزء مشفر بـ Base64Url.",
    "في ترويسة التوكن، يحدد حقل <code>alg</code> الخوارزمية المستخدمة للتحقق من التوقيع مثل <code>HS256</code>.",
    "إذا كان السيرفر يقبل القيمة <code>none</code> للخوارزمية، فإنه يتجاهل فحص التوقيع تماماً ويثق بحمولة التوكن!",
    "اضغط على زر <strong>(حمولة استغلال None Alg)</strong> لإرسال توكن تم التلاعب برتبته ليصبح <code>role: admin</code> مع خوارزمية <code>none</code> ونقطة ختامية بدون توقيع."
]); ?>

<?= render_code_comparison(
    '// ❌ كود مصاب: الثقة بحقل الخوارزمية وفحص غير مشدد\n$header = json_decode(base64_decode($parts[0]), true);\nif (strtolower($header["alg"]) === "none") {\n    // ثغرة كارثية: تجاوز فحص التوقيع وقبول التوكن فوراً!\n    $user = json_decode(base64_decode($parts[1]), true);\n}',
    '// ✅ كود محمي: حظر خوارزمية none وإلزام التوقيع عبر السر المشترك\n$header = json_decode(base64_decode($parts[0]), true);\nif ($header["alg"] !== "HS256") {\n    die("Invalid or unsupported algorithm!");\n}\n$valid_sig = hash_hmac("sha256", "$parts[0].$parts[1]", $SECRET_KEY, true);\nif (!hash_equals($valid_sig, base64_decode($parts[2]))) {\n    die("Invalid JWT Signature!");\n}',
    'لمنع هجمات JWT، يجب فرض قائمة بيضاء صارمة للخوارزميات المقبولة وحظر خوارزمية none تماماً، مع استخدام مفاتيح سرية قوية ومعقدة لا يمكن تخمينها عبر هجمات Brute Force.'
); ?>

<script>
function loadJwt(type) {
    const field = document.getElementById('token_input');
    if (type === 'guest') {
        field.value = <?= json_encode($default_guest_jwt) ?>;
    } else if (type === 'exploit') {
        field.value = <?= json_encode($exploit_none_jwt) ?>;
    }
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
