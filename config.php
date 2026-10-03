<?php
// ==============================================================================
// مشروع: مختبر الجوكر الفلسطيني لاختبار اختراق تطبيقات الويب (Joker Security Lab)
// إعداد: المهندس احمد سليم
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

// تسجيل حل التحدي وإرجاع العلم مع تفعيل احتفالية الفوز
function award_flag($flag_key, $force_celebrate = false) {
    global $CHALLENGE_FLAGS;
    if (isset($CHALLENGE_FLAGS[$flag_key])) {
        $is_new = !in_array($flag_key, $_SESSION['solved_flags']);
        if ($is_new) {
            $_SESSION['solved_flags'][] = $flag_key;
        }

        // تفعيل بيانات الاحتفال في الجلسة (الكونفيتي وصوت الانتصار والنافذة)
        if ($is_new || $force_celebrate || empty($_SESSION['celebrated_keys'][$flag_key])) {
            $_SESSION['celebration'] = [
                'key' => $flag_key,
                'title' => $CHALLENGE_FLAGS[$flag_key]['title'],
                'flag' => $CHALLENGE_FLAGS[$flag_key]['flag'],
                'is_new' => $is_new
            ];
            $_SESSION['celebrated_keys'][$flag_key] = true;
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

// مكون التلميحات المتدرجة (مخفي افتراضياً بالكامل لتحدي الطالب)
function render_hints($hints) {
    static $hint_idx = 0;
    $hint_idx++;
    $accordion_id = "hintsAccordion_" . $hint_idx;
    $collapse_id = "collapseHints_" . $hint_idx;
    $heading_id = "headingHints_" . $hint_idx;
    ?>
    <div class="my-4" id="<?= $accordion_id ?>">
        <div class="card bg-dark bg-opacity-75 border border-secondary border-opacity-50 rounded-3 shadow-sm">
            <div class="card-header p-2 bg-transparent border-0" id="<?= $heading_id ?>">
                <button class="btn btn-sm btn-outline-warning w-100 text-start d-flex justify-content-between align-items-center fw-bold py-2 px-3 collapsed" 
                        type="button" 
                        data-bs-toggle="collapse" 
                        data-bs-target="#<?= $collapse_id ?>" 
                        aria-expanded="false" 
                        aria-controls="<?= $collapse_id ?>">
                    <span>
                        <i class="fas fa-lightbulb text-warning me-2"></i>
                        💡 تلميحات ومساعدة للحل (مخفية افتراضياً)
                    </span>
                    <span class="badge bg-secondary font-monospace" style="font-size: 0.75rem;">
                        اضغط للإظهار <i class="fas fa-chevron-down ms-1"></i>
                    </span>
                </button>
            </div>
            <div id="<?= $collapse_id ?>" class="collapse" aria-labelledby="<?= $heading_id ?>">
                <div class="card-body pt-0 pb-3 px-3 border-top border-secondary border-opacity-25 mt-2">
                    <div class="alert alert-secondary py-1 px-3 mb-2 small text-warning bg-black bg-opacity-50 border-0 rounded">
                        <i class="fas fa-eye-slash me-1"></i> تم إخفاء هذه التلميحات افتراضياً لتتمكن من التفكير وتجربة الحل بنفسك أولاً:
                    </div>
                    <ol class="mb-0 text-light ps-3">
                        <?php foreach ($hints as $hint): ?>
                            <li class="mb-2" style="font-size: 0.93rem;"><?= $hint ?></li>
                        <?php endforeach; ?>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <?php
}

// عرض رسالة النجاح وظهور العلم مع زر الاحتفال المباشر
function render_flag_box($flag_key) {
    global $CHALLENGE_FLAGS;
    if (is_flag_solved($flag_key)) {
        $flag = $CHALLENGE_FLAGS[$flag_key]['flag'];
        $title = $CHALLENGE_FLAGS[$flag_key]['title'] ?? 'التحدي الأمني';
        ?>
        <div class="alert alert-success border-2 shadow-sm d-flex flex-wrap align-items-center justify-content-between p-3 my-3">
            <div class="mb-2 mb-md-0">
                <h5 class="alert-heading mb-1 text-success fw-bold">
                    <i class="fas fa-trophy text-warning me-2"></i> مبروك! تم حل التحدي بنجاح!
                </h5>
                <span class="text-white-50">العلم الخاص بك هو: </span>
                <code class="fs-5 fw-bold text-warning bg-black px-2 py-1 rounded border border-warning font-monospace user-select-all"><?= htmlspecialchars($flag) ?></code>
            </div>
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn btn-sm btn-outline-warning copy-flag-btn" data-flag="<?= htmlspecialchars($flag) ?>">
                    <i class="fas fa-copy me-1"></i> نسخ العلم
                </button>
                <button type="button" class="btn btn-sm btn-warning text-dark fw-bold" onclick="triggerConfettiCelebration(<?= htmlspecialchars(json_encode($title)) ?>, <?= htmlspecialchars(json_encode($flag)) ?>)">
                    <i class="fas fa-magic me-1"></i> احتفل بالفوز 🎊
                </button>
                <span class="badge bg-success fs-6"><i class="fas fa-check-circle me-1"></i> مكتمل</span>
            </div>
        </div>
        <?php
    }
}

// عرض نافذة ومحرك الاحتفال والكونفيتي (Celebration Engine & Modal)
function render_celebration($is_root = false) {
    global $CHALLENGE_FLAGS;
    $celebration = $_SESSION['celebration'] ?? null;
    $has_celebration = ($celebration !== null);
    $title = $celebration['title'] ?? '';
    $flag = $celebration['flag'] ?? '';
    if ($has_celebration) {
        unset($_SESSION['celebration']);
    }
    $js_path = $is_root ? 'assets/js/confetti.js' : '../assets/js/confetti.js';
    $img_path = $is_root ? 'assets/images/joker_logo.png' : '../assets/images/joker_logo.png';
    ?>
    <!-- عنصر تمرير بيانات الاحتفال التلقائي بالجافاسكربت -->
    <?php if ($has_celebration): ?>
        <span id="autoCelebrateData" 
              data-title="<?= htmlspecialchars($title) ?>" 
              data-flag="<?= htmlspecialchars($flag) ?>" 
              style="display:none;"></span>
    <?php endif; ?>

    <!-- نافذة التهنئة المنبثقة للاحتفال بحل التحدي (Celebration Modal) -->
    <div class="modal fade" id="celebrationModal" tabindex="-1" aria-labelledby="celebrationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-warning shadow-lg" style="background: radial-gradient(circle at top, #1a2236 0%, #0d1322 100%); color: #fff; border-width: 2px; box-shadow: 0 0 35px rgba(245, 158, 11, 0.45) !important;">
                <div class="modal-header border-bottom border-warning border-opacity-25 pb-2">
                    <div class="d-flex align-items-center">
                        <span class="fs-1 me-2">🏆</span>
                        <div>
                            <h5 class="modal-title fw-bold text-warning mb-0" id="celebrationModalLabel">🎉 ألـــف مـبـروك يا بـطـل! تم حل التحدي بنجاح!</h5>
                            <small class="text-white-50">إنجاز جديد ومتميز يُضاف إلى مسيرتك في اختبار اختراق تطبيقات الويب</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <div class="mb-3">
                        <span class="badge bg-secondary mb-1" style="font-size: 0.78rem;">التحدي الأمني المكتشف</span>
                        <h4 class="text-info fw-bold mb-0" id="celebrationChallengeTitle"><?= htmlspecialchars($title) ?></h4>
                    </div>

                    <div class="bg-black bg-opacity-75 p-3 rounded-3 border border-warning my-3 text-center shadow-sm">
                        <div class="text-white-50 small mb-1">
                            <i class="fas fa-flag text-warning me-1"></i> راية العلم السرية (Flag):
                        </div>
                        <code class="fs-4 text-warning fw-bold font-monospace d-block my-2 user-select-all" id="celebrationFlagText"><?= htmlspecialchars($flag) ?></code>
                        <button type="button" class="btn btn-outline-warning btn-sm copy-flag-btn" id="celebrationCopyBtn" data-flag="<?= htmlspecialchars($flag) ?>">
                            <i class="fas fa-copy me-1"></i> نسخ العلم للحافظة
                        </button>
                    </div>

                    <div class="alert alert-success bg-opacity-10 border-success py-2 mb-3 small text-start d-flex align-items-center">
                        <i class="fas fa-chart-line text-success fs-3 me-2"></i>
                        <div>
                            <strong>تم تسجيل هذا الإنجاز تلقائياً بنجاح!</strong>
                            <div class="text-white-50">تم احتساب النتيجة وتحديث عداد الأعلام في لوحة التحكم الرئيسية.</div>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap justify-content-center gap-2 mt-3">
                        <button type="button" class="btn btn-warning fw-bold text-dark px-3 shadow" onclick="fullCelebrationBlast()">
                            <i class="fas fa-magic me-1"></i> إطلاق الألعاب النارية مجدداً 🎊
                        </button>
                        <button type="button" class="btn btn-outline-info fw-bold px-3" onclick="playVictoryChime()">
                            <i class="fas fa-volume-up me-1"></i> نغمة الفوز 🔊
                        </button>
                    </div>
                </div>
                <div class="modal-footer border-top border-secondary border-opacity-25 justify-content-between py-2">
                    <div class="small text-white-50 d-flex align-items-center">
                        <img src="<?= $img_path ?>" alt="الجوكر" class="rounded-circle border border-info me-2" style="width: 26px; height: 26px; object-fit: cover;">
                        <span>إعداد وتطوير: <strong class="text-info">المهندس احمد سليم 🇵🇸</strong></span>
                    </div>
                    <button type="button" class="btn btn-secondary btn-sm px-3 fw-bold" data-bs-dismiss="modal">متابعة التحديات</button>
                </div>
            </div>
        </div>
    </div>

    <!-- محرك الكونفيتي والتأثيرات الصوتية -->
    <script src="<?= $js_path ?>"></script>
    <?php
}
