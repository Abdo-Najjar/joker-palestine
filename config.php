<?php
// ==============================================================================
// مشروع: مختبر الجوكر الأمني لاختبار اختراق تطبيقات الويب (Joker Security Lab)
// إعداد: الجوكر الفلسطيني احمد سليم
// ==============================================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// مسارات المشروع
define('ROOT_PATH', __DIR__);
define('DB_FILE', ROOT_PATH . '/database.sqlite');
define('UPLOADS_DIR', ROOT_PATH . '/uploads');

// ضبط مستوى الحماية (الافتراضي: ضعيف low)
if (!isset($_SESSION['security_level'])) {
    $_SESSION['security_level'] = 'low'; // 'low' or 'secure'
}

if (isset($_GET['set_sec']) && in_array($_GET['set_sec'], ['low', 'secure'])) {
    $_SESSION['security_level'] = $_GET['set_sec'];
    $redirect_url = strtok($_SERVER["REQUEST_URI"], '?');
    header("Location: $redirect_url");
    exit;
}

// قائمة الأعلام (Flags) للتحديات
$CHALLENGE_FLAGS = [
    'sqli_auth'      => ['title' => 'SQLi: تجاوز تسجيل الدخول', 'flag' => 'FLAG{SQLi_Auth_Bypass_Success_9281}'],
    'sqli_union'     => ['title' => 'SQLi: استخراج البيانات عبر UNION', 'flag' => 'FLAG{SQLi_Union_Extract_Secret_4812}'],
    'xss_reflected'  => ['title' => 'XSS: المنعكس (Reflected)', 'flag' => 'FLAG{XSS_Reflected_Payload_Found_7719}'],
    'xss_stored'     => ['title' => 'XSS: المخزن (Stored)', 'flag' => 'FLAG{XSS_Stored_Script_Triggered_3381}'],
    'xss_dom'        => ['title' => 'XSS: المعتمد على DOM', 'flag' => 'FLAG{XSS_DOM_Execution_Caught_1928}'],
    'lfi'            => ['title' => 'LFI: قراءة الملفات المحلية', 'flag' => 'FLAG{LFI_Local_File_Read_Exposed_8821}'],
    'upload'         => ['title' => 'File Upload: رفع ملفات غير آمن', 'flag' => 'FLAG{File_Upload_WebShell_RCE_5521}'],
    'cmdi'           => ['title' => 'Command Injection: حقن أوامر النظام', 'flag' => 'FLAG{Command_Injection_Pwned_9912}'],
    'idor'           => ['title' => 'IDOR: التحكم غير المباشر بالبيانات', 'flag' => 'FLAG{IDOR_Unauthorized_Access_6619}'],
    'csrf'           => ['title' => 'CSRF: تزوير الطلبات عبر المواقع', 'flag' => 'FLAG{CSRF_Request_Forged_Successfully_4421}'],
    'sensitive'      => ['title' => 'Data Exposure: كشف الملفات وقاعدة البيانات', 'flag' => 'FLAG{Sensitive_Data_Exposed_Download_1192}'],
    'redirect'       => ['title' => 'Open Redirect: التوجيه المفتوح', 'flag' => 'FLAG{Open_Redirect_Exploited_Safe_3301}'],
    'ssrf'           => ['title' => 'SSRF: تزوير الطلب من جهة السيرفر', 'flag' => 'FLAG{SSRF_Internal_Server_Request_7710}'],
    'bruteforce'     => ['title' => 'Brute Force: التخمين على كلمات المرور', 'flag' => 'FLAG{Brute_Force_Password_Cracked_2281}'],
    'type_juggling'  => ['title' => 'Type Juggling: مقارنات PHP الضعيفة', 'flag' => 'FLAG{PHP_Type_Juggling_Bypassed_6672}'],
    'access_control' => ['title' => 'Access Control: التلاعب بالصلاحيات والكوكي', 'flag' => 'FLAG{Privilege_Escalation_Admin_Role_5502}']
];

if (!isset($_SESSION['solved_flags'])) {
    $_SESSION['solved_flags'] = [];
}

// دالة الاتصال بقاعدة البيانات SQLite
function get_db() {
    static $db = null;
    if ($db === null) {
        if (!file_exists(DB_FILE)) {
            require_once ROOT_PATH . '/reset.php';
            reset_database();
        }
        try {
            $db = new PDO('sqlite:' . DB_FILE);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("<div style='color:red;padding:20px;'>خطأ في الاتصال بقاعدة بيانات SQLite: " . htmlspecialchars($e->getMessage()) . "</div>");
        }
    }
    return $db;
}

// تسجيل حل التحدي وإرجاع العلم
function award_flag($flag_key) {
    global $CHALLENGE_FLAGS;
    if (isset($CHALLENGE_FLAGS[$flag_key])) {
        if (!in_array($flag_key, $_SESSION['solved_flags'])) {
            $_SESSION['solved_flags'][] = $flag_key;
        }
        return $CHALLENGE_FLAGS[$flag_key]['flag'];
    }
    return false;
}

function is_flag_solved($flag_key) {
    return in_array($flag_key, $_SESSION['solved_flags']);
}

function get_score() {
    global $CHALLENGE_FLAGS;
    $total = count($CHALLENGE_FLAGS);
    $solved = count($_SESSION['solved_flags']);
    $percentage = round(($solved / $total) * 100);
    return ['solved' => $solved, 'total' => $total, 'percentage' => $percentage];
}

function get_security_level() {
    return $_SESSION['security_level'] ?? 'low';
}

// مكون إرشادي لعرض الكود المصاب مقابل الكود الآمن
function render_code_comparison($vuln_code, $secure_code, $explanation) {
    static $compare_count = 0;
    $compare_count++;
    $collapse_id = "codeCompareCollapse_" . $compare_count;

    // تحويل \n الحرفية إلى أسطر حقيقية (إذا تم تمريرها بسلاسل نصية فردية)
    $vuln_code = str_replace('\n', "\n", $vuln_code);
    $secure_code = str_replace('\n', "\n", $secure_code);
    ?>
    <div class="card my-4 border-info shadow-sm">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
            <span class="fw-bold text-info"><i class="fas fa-code me-2"></i> التحليل الأمني: الكود المصاب vs الكود الآمن</span>
            <button class="btn btn-sm btn-outline-info fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#<?= $collapse_id ?>">
                عرض / إخفاء الكود <i class="fas fa-chevron-down ms-1"></i>
            </button>
        </div>
        <div class="collapse" id="<?= $collapse_id ?>">
            <div class="card-body">
                <div class="alert alert-secondary py-2 mb-3">
                    <strong class="text-white"><i class="fas fa-info-circle me-1 text-info"></i> شرح آلية الخلل والترقيع:</strong>
                    <p class="mb-0 mt-1 text-light"><?= $explanation ?></p>
                </div>
                <div class="row g-3">
                    <div class="col-lg-6 mb-2">
                        <div class="p-3 bg-danger bg-opacity-10 border border-danger rounded h-100 d-flex flex-column shadow-sm">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="text-danger fw-bold mb-0">
                                    <i class="fas fa-bug me-1"></i> الكود المصاب (Vulnerable - Low)
                                </h6>
                                <span class="badge bg-danger">كود مصاب</span>
                            </div>
                            <pre class="bg-black text-danger-subtle p-3 rounded small mb-0 flex-grow-1 border border-danger border-opacity-25" style="direction:ltr; text-align:left; max-height:350px; overflow:auto; width:100%; box-sizing:border-box;"><code class="font-monospace" style="direction:ltr; text-align:left; display:block; white-space:pre;"><?= htmlspecialchars(trim($vuln_code)) ?></code></pre>
                        </div>
                    </div>
                    <div class="col-lg-6 mb-2">
                        <div class="p-3 bg-success bg-opacity-10 border border-success rounded h-100 d-flex flex-column shadow-sm">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="text-success fw-bold mb-0">
                                    <i class="fas fa-shield-alt me-1"></i> الكود الآمن (Secure - High)
                                </h6>
                                <span class="badge bg-success text-dark fw-bold">كود آمن ومرقّع</span>
                            </div>
                            <pre class="bg-black text-success-subtle p-3 rounded small mb-0 flex-grow-1 border border-success border-opacity-25" style="direction:ltr; text-align:left; max-height:350px; overflow:auto; width:100%; box-sizing:border-box;"><code class="font-monospace" style="direction:ltr; text-align:left; display:block; white-space:pre;"><?= htmlspecialchars(trim($secure_code)) ?></code></pre>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
}

// مكون التلميحات المتدرجة
function render_hints($hints) {
    ?>
    <div class="accordion my-3 shadow-sm" id="hintsAccordion">
        <div class="accordion-item border-warning">
            <h2 class="accordion-header" id="headingHints">
                <button class="accordion-button collapsed bg-warning bg-opacity-10 text-dark fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseHints">
                    <i class="fas fa-lightbulb me-2 text-warning"></i> تلميحات ومساعدة للمدرب والطالب (اضغط للإظهار)
                </button>
            </h2>
            <div id="collapseHints" class="accordion-collapse collapse" data-bs-parent="#hintsAccordion">
                <div class="accordion-body">
                    <ol class="mb-0">
                        <?php foreach ($hints as $hint): ?>
                            <li class="mb-2"><?= $hint ?></li>
                        <?php endforeach; ?>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <?php
}

// عرض رسالة النجاح وظهور العلم
function render_flag_box($flag_key) {
    global $CHALLENGE_FLAGS;
    if (is_flag_solved($flag_key)) {
        $flag = $CHALLENGE_FLAGS[$flag_key]['flag'];
        ?>
        <div class="alert alert-success border-2 shadow-sm d-flex align-items-center justify-content-between p-3 my-3">
            <div>
                <h5 class="alert-heading mb-1 text-success fw-bold"><i class="fas fa-trophy text-warning me-2"></i> مبروك! تم حل التحدي بنجاح!</h5>
                <span class="text-muted">العلم الخاص بك هو: </span>
                <code class="fs-5 fw-bold text-dark bg-white px-2 py-1 rounded border border-success"><?= htmlspecialchars($flag) ?></code>
            </div>
            <span class="badge bg-success fs-6"><i class="fas fa-check-circle me-1"></i> مكتمل</span>
        </div>
        <?php
    }
}
