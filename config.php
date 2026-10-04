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
    'access_control' => ['title' => 'Access Control: التلاعب بالصلاحيات والكوكي', 'flag' => 'FLAG{Privilege_Escalation_Admin_Role_5502}'],
    'xxe'            => ['title' => 'XXE: حقن الكيانات الخارجية XML', 'flag' => 'FLAG{XXE_Entity_Local_File_Leaked_8192}'],
    'jwt'            => ['title' => 'JWT: تزوير توكن المصادقة (Alg None)', 'flag' => 'FLAG{JWT_Algorithm_None_Priv_Escalated_4319}'],
    'ssti'           => ['title' => 'SSTI: حقن محركات القوالب (RCE)', 'flag' => 'FLAG{SSTI_Template_Injection_Code_Exec_9934}']
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

// دالات إعدادات المختبر والخيارات التفضيلية (Lab Settings System)
function get_lab_setting($key, $default = null) {
    if (!isset($_SESSION['lab_settings'])) {
        $_SESSION['lab_settings'] = [
            'show_hints'           => false, // default hidden
            'show_code_comparison' => true,
            'show_diagrams'        => true,
            'enable_sounds'        => true,
            'enable_confetti'      => true,
            'enable_waf'           => false,
            'student_name'         => $_SESSION['student_name'] ?? 'الباحث الأمني المتميز'
        ];
    }
    if ($default !== null && !isset($_SESSION['lab_settings'][$key])) {
        return $default;
    }
    return $_SESSION['lab_settings'][$key] ?? $default;
}

function update_lab_setting($key, $value) {
    get_lab_setting('init');
    $_SESSION['lab_settings'][$key] = $value;
}

// مكون إرشادي لعرض الكود المصاب مقابل الكود الآمن
function render_code_comparison($vuln_code, $secure_code, $explanation) {
    // التحقق من تفعيل إظهار مقارنة الأكواد في الإعدادات
    if (!get_lab_setting('show_code_comparison', true)) {
        return;
    }

    static $compare_count = 0;
    $compare_count++;
    $collapse_id = "codeCompareCollapse_" . $compare_count;

    // تحويل \n الحرفية إلى أسطر حقيقية (إذا تم تمريرها بسلاسل نصية فردية)
    $vuln_code = str_replace('\n', "\n", $vuln_code);
    $secure_code = str_replace('\n', "\n", $secure_code);
    ?>
    <div class="card card-cyber my-4 border-info shadow-sm" style="background-color: #121829 !important; border: 1.5px solid #06b6d4 !important;">
        <div class="card-header d-flex justify-content-between align-items-center" style="background-color: #0c1222 !important; border-bottom: 1px solid #1e293b; padding: 12px 18px;">
            <span class="fw-bold text-info fs-6"><i class="fas fa-code me-2"></i> التحليل الأمني: الكود المصاب vs الكود الآمن</span>
            <button class="btn btn-sm btn-outline-info fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#<?= $collapse_id ?>">
                عرض / إخفاء الكود <i class="fas fa-chevron-down ms-1"></i>
            </button>
        </div>
        <div class="collapse" id="<?= $collapse_id ?>">
            <div class="card-body p-4" style="background-color: #0e1424 !important;">
                <div class="alert alert-info py-2 px-3 mb-3 d-flex align-items-start gap-2" style="background-color: rgba(6, 182, 212, 0.08) !important; border: 1px solid rgba(6, 182, 212, 0.35) !important;">
                    <i class="fas fa-info-circle text-info fs-5 mt-1"></i>
                    <div>
                        <strong class="text-info d-block">شرح آلية الخلل والترقيع البرمجي:</strong>
                        <div class="mt-1 text-light" style="color: #cbd5e1 !important; line-height: 1.6;"><?= $explanation ?></div>
                    </div>
                </div>
                <div class="row g-3">
                    <div class="col-lg-6 mb-2">
                        <div class="p-3 rounded h-100 d-flex flex-column shadow-sm" style="background-color: #150d14 !important; border: 1.5px solid #ef4444 !important;">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="fw-bold mb-0" style="color: #f87171 !important;">
                                    <i class="fas fa-bug me-1"></i> الكود المصاب (Vulnerable - Low)
                                </h6>
                                <span class="badge bg-danger">كود مصاب</span>
                            </div>
                            <pre class="p-3 rounded small mb-0 flex-grow-1" style="background-color: #07090e !important; border: 1px solid rgba(239, 68, 68, 0.35) !important; direction:ltr; text-align:left; max-height:350px; overflow:auto; width:100%; box-sizing:border-box;"><code class="font-monospace" style="color: #fca5a5 !important; direction:ltr; text-align:left; display:block; white-space:pre;"><?= htmlspecialchars(trim($vuln_code)) ?></code></pre>
                        </div>
                    </div>
                    <div class="col-lg-6 mb-2">
                        <div class="p-3 rounded h-100 d-flex flex-column shadow-sm" style="background-color: #091714 !important; border: 1.5px solid #10b981 !important;">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="fw-bold mb-0" style="color: #34d399 !important;">
                                    <i class="fas fa-shield-alt me-1"></i> الكود الآمن (Secure - High)
                                </h6>
                                <span class="badge bg-success text-dark fw-bold">كود آمن ومرقّع</span>
                            </div>
                            <pre class="p-3 rounded small mb-0 flex-grow-1" style="background-color: #07090e !important; border: 1px solid rgba(16, 185, 129, 0.35) !important; direction:ltr; text-align:left; max-height:350px; overflow:auto; width:100%; box-sizing:border-box;"><code class="font-monospace" style="color: #86efac !important; direction:ltr; text-align:left; display:block; white-space:pre;"><?= htmlspecialchars(trim($secure_code)) ?></code></pre>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
}

// مكون التلميحات المتدرجة (مخفي افتراضياً بالكامل لتحدي الطالب أو يمكن فتحه من الإعدادات)
function render_hints($hints) {
    static $hint_idx = 0;
    $hint_idx++;
    $accordion_id = "hintsAccordion_" . $hint_idx;
    $collapse_id = "collapseHints_" . $hint_idx;
    $heading_id = "headingHints_" . $hint_idx;
    $is_expanded = (bool)get_lab_setting('show_hints', false);
    ?>
    <div class="my-4" id="<?= $accordion_id ?>">
        <div class="card card-cyber rounded-3 shadow-sm" style="background-color: #0d1424 !important; border: 1.5px solid #334155 !important;">
            <div class="card-header p-2 bg-transparent border-0" id="<?= $heading_id ?>">
                <button class="btn btn-sm btn-outline-warning w-100 text-start d-flex justify-content-between align-items-center fw-bold py-2 px-3 <?= $is_expanded ? '' : 'collapsed' ?>" 
                        type="button" 
                        data-bs-toggle="collapse" 
                        data-bs-target="#<?= $collapse_id ?>" 
                        aria-expanded="<?= $is_expanded ? 'true' : 'false' ?>" 
                        aria-controls="<?= $collapse_id ?>"
                        style="border-color: #f59e0b !important; color: #fbbf24 !important;">
                    <span>
                        <i class="fas fa-lightbulb text-warning me-2"></i>
                        💡 تلميحات ومساعدة للحل <?= $is_expanded ? '(مفتوحة)' : '(مخفية افتراضياً)' ?>
                    </span>
                    <span class="badge bg-secondary font-monospace" style="font-size: 0.75rem;">
                        <?= $is_expanded ? 'اضغط للإخفاء' : 'اضغط للإظهار' ?> <i class="fas fa-chevron-down ms-1"></i>
                    </span>
                </button>
            </div>
            <div id="<?= $collapse_id ?>" class="collapse <?= $is_expanded ? 'show' : '' ?>" aria-labelledby="<?= $heading_id ?>">
                <div class="card-body pt-0 pb-3 px-3 border-top border-secondary border-opacity-25 mt-2" style="background-color: #0d1424 !important;">
                    <div class="alert alert-secondary py-2 px-3 mb-3 small text-warning rounded" style="background-color: #070a12 !important; border: 1px solid #334155 !important; color: #fbbf24 !important;">
                        <i class="fas fa-eye-slash me-1"></i> تم إخفاء هذه التلميحات افتراضياً لتتمكن من التفكير وتجربة الحل بنفسك أولاً (يمكن تعديل ذلك من صفحة الإعدادات):
                    </div>
                    <ol class="mb-0 text-light ps-3" style="color: #e2e8f0 !important;">
                        <?php foreach ($hints as $hint): ?>
                            <li class="mb-2" style="font-size: 0.95rem; color: #e2e8f0 !important; line-height: 1.7;"><?= $hint ?></li>
                        <?php endforeach; ?>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <?php
}

// مكون عرض المخططات المعمارية والتوضيحية مع ميزة التكبير عالي الدقة (Lightbox Modal)
function render_diagram($image_name, $title, $description = '', $is_root = false) {
    // التحقق من تفعيل إظهار المخططات في الإعدادات
    if (!get_lab_setting('show_diagrams', true)) {
        return;
    }

    static $diag_count = 0;
    $diag_count++;
    $modal_id = "diagramModal_" . $diag_count;
    $img_dir = $is_root ? 'assets/images/' : '../assets/images/';
    $img_src = $img_dir . $image_name;
    ?>
    <div class="card card-cyber mb-4 overflow-hidden shadow-lg" style="border: 1.5px solid #06b6d4 !important; background: #0b0f19 !important;">
        <div class="card-header py-3 px-3 d-flex flex-wrap justify-content-between align-items-center gap-2" style="background: linear-gradient(135deg, #0f172a 0%, #080d1a 100%); border-bottom: 1px solid #1e293b;">
            <div class="d-flex align-items-center">
                <div class="p-2 rounded-circle bg-info bg-opacity-10 text-info border border-info me-2 d-flex align-items-center justify-content-center shadow-sm" style="width: 38px; height: 38px; flex-shrink: 0;">
                    <i class="fas fa-project-diagram"></i>
                </div>
                <div>
                    <h6 class="mb-0 text-white fw-bold d-flex align-items-center">
                        <span><?= htmlspecialchars($title) ?></span>
                    </h6>
                    <?php if (!empty($description)): ?>
                        <small class="text-muted d-block mt-1"><?= htmlspecialchars($description) ?></small>
                    <?php endif; ?>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-black border border-info text-info px-2 py-1 small">
                    <i class="fas fa-layer-group me-1"></i> مسار تدفق الهجوم
                </span>
                <button type="button" class="btn btn-sm btn-outline-info fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#<?= $modal_id ?>">
                    <i class="fas fa-search-plus me-1"></i> تكبير المخطط بدقة فائقة 🔍
                </button>
            </div>
        </div>
        <div class="card-body p-0 text-center position-relative" style="background: #060911;">
            <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#<?= $modal_id ?>" title="اضغط لتكبير المخطط وفحصه بدقة عالية" style="display:block; cursor: zoom-in;">
                <img src="<?= $img_src ?>" alt="<?= htmlspecialchars($title) ?>" class="img-fluid" style="max-height: 420px; width: 100%; object-fit: contain; padding: 12px; transition: transform 0.3s ease;">
            </a>
            <div class="p-2 text-center border-top border-secondary border-opacity-25 bg-black bg-opacity-40">
                <small class="text-info fw-semibold">
                    <i class="fas fa-mouse-pointer me-1"></i> اضغط على الصورة في أي مكان لفتحها بحجم كامل وشاشة عريضة فائقة الوضوح (Full HD)
                </small>
            </div>
        </div>
    </div>

    <!-- نافذة تكبير المخطط بحجم الشاشة الكاملة (Lightbox High-Res Modal) -->
    <div class="modal fade" id="<?= $modal_id ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered" style="max-width: 92vw;">
            <div class="modal-content shadow-lg border-info" style="background: #080d1a; border: 2px solid #06b6d4 !important; box-shadow: 0 0 35px rgba(6, 182, 212, 0.4) !important;">
                <div class="modal-header border-bottom border-secondary border-opacity-50 py-2 px-3" style="background: #0f172a;">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-shield-alt text-info me-2 fs-5"></i>
                        <h5 class="modal-title text-white fw-bold mb-0"><?= htmlspecialchars($title) ?></h5>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-2 text-center bg-black">
                    <img src="<?= $img_src ?>" alt="<?= htmlspecialchars($title) ?>" class="img-fluid rounded" style="max-height: 85vh; width: 100%; object-fit: contain;">
                </div>
                <div class="modal-footer border-top border-secondary border-opacity-50 py-2 justify-content-between" style="background: #0f172a;">
                    <span class="small text-muted">مختبر الجوكر الفلسطيني | إعداد وتطوير: <strong class="text-info">المهندس احمد سليم 🇵🇸</strong></span>
                    <button type="button" class="btn btn-secondary btn-sm px-4 fw-bold" data-bs-dismiss="modal">إغلاق النافذة</button>
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
        <div class="p-3 my-3 rounded-3 shadow-lg d-flex flex-wrap align-items-center justify-content-between gap-3" 
             style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.12) 0%, rgba(14, 20, 36, 0.95) 100%); border: 1.5px solid #10b981; box-shadow: 0 0 20px rgba(16, 185, 129, 0.25) !important;">
            <div class="d-flex align-items-center gap-3">
                <div class="p-2 rounded-circle bg-success bg-opacity-20 border border-success text-success d-flex align-items-center justify-content-center shadow" style="width: 48px; height: 48px; flex-shrink: 0;">
                    <i class="fas fa-trophy fs-4 text-warning"></i>
                </div>
                <div>
                    <h5 class="mb-1 text-success fw-bold d-flex align-items-center">
                        <span>مبروك! تم حل التحدي واكتشاف العلم بنجاح</span>
                        <span class="badge bg-success text-dark fw-bold ms-2 py-1 px-2" style="font-size: 0.72rem;">مكتمل 100%</span>
                    </h5>
                    <div class="d-flex align-items-center gap-2 mt-1">
                        <span class="text-white-50 small">راية العلم:</span>
                        <code class="fw-bold font-monospace user-select-all" style="color: #fbbf24 !important; background: #07090e !important; border: 1px solid #f59e0b !important; padding: 3px 10px !important; border-radius: 6px !important; font-size: 1rem;"><?= htmlspecialchars($flag) ?></code>
                    </div>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn btn-sm btn-outline-warning copy-flag-btn fw-bold px-3" data-flag="<?= htmlspecialchars($flag) ?>">
                    <i class="fas fa-copy me-1"></i> نسخ العلم
                </button>
                <button type="button" class="btn btn-sm btn-warning text-dark fw-bold px-3 shadow" onclick="triggerConfettiCelebration(<?= htmlspecialchars(json_encode($title)) ?>, <?= htmlspecialchars(json_encode($flag)) ?>)">
                    <i class="fas fa-magic me-1"></i> احتفل بالفوز 🎊
                </button>
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
        <div class="modal-dialog modal-dialog-centered modal-lg" style="max-width: 640px;">
            <div class="modal-content border-warning shadow-lg" style="background: radial-gradient(circle at top, #161f33 0%, #090e1a 100%); color: #fff; border-width: 2px; border-color: #f59e0b !important; box-shadow: 0 0 40px rgba(245, 158, 11, 0.45) !important; border-radius: 16px;">
                <div class="modal-header border-bottom border-warning border-opacity-25 pb-3 pt-3 px-4" style="background: rgba(14, 20, 36, 0.95); border-radius: 14px 14px 0 0;">
                    <div class="d-flex align-items-center w-100">
                        <div class="p-2 rounded-circle bg-warning bg-opacity-10 border border-warning text-center me-3 d-flex align-items-center justify-content-center shadow" style="width: 52px; height: 52px; flex-shrink: 0;">
                            <span class="fs-2">🏆</span>
                        </div>
                        <div class="flex-grow-1">
                            <h4 class="modal-title fw-bold text-warning mb-1" id="celebrationModalLabel" style="text-shadow: 0 0 15px rgba(245, 158, 11, 0.4);">
                                🎉 ألـــف مـبـروك يا بـطـل! تم حل التحدي بنجاح!
                            </h4>
                            <div class="text-light text-opacity-75 small">إنجاز جديد ومتميز يُضاف إلى مسيرتك في اختبار اختراق تطبيقات الويب</div>
                        </div>
                        <button type="button" class="btn-close btn-close-white ms-2" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                </div>
                <div class="modal-body text-center py-4 px-4">
                    <div class="mb-3">
                        <span class="badge bg-dark border border-secondary text-info mb-1 px-3 py-1" style="font-size: 0.8rem;">التحدي الأمني المكتشف</span>
                        <h4 class="text-white fw-bold mb-0 mt-1" id="celebrationChallengeTitle"><?= htmlspecialchars($title) ?></h4>
                    </div>

                    <div class="p-3 rounded-3 my-3 text-center shadow-lg" style="background: #070a12; border: 1.5px solid #f59e0b; box-shadow: 0 0 20px rgba(245, 158, 11, 0.2) !important;">
                        <div class="d-flex justify-content-between align-items-center mb-2 px-2">
                            <span class="text-warning small fw-bold">
                                <i class="fas fa-flag me-1"></i> راية العلم السرية (Secret Flag):
                            </span>
                            <span class="badge bg-warning text-dark font-monospace fw-bold">CAPTURED</span>
                        </div>
                        <div class="p-2 bg-black rounded border border-secondary border-opacity-50 my-2 position-relative">
                            <code class="text-warning fw-bold font-monospace d-block user-select-all" id="celebrationFlagText" style="font-size: 1.15rem; letter-spacing: 0.5px; word-break: break-all; color: #fbbf24 !important; background: transparent !important; border: none !important;"><?= htmlspecialchars($flag) ?></code>
                        </div>
                        <button type="button" class="btn btn-outline-warning btn-sm copy-flag-btn px-4 fw-bold shadow-sm" id="celebrationCopyBtn" data-flag="<?= htmlspecialchars($flag) ?>">
                            <i class="fas fa-copy me-1"></i> نسخ العلم للحافظة
                        </button>
                    </div>

                    <div class="p-3 mb-3 rounded-3 text-start d-flex align-items-center shadow-sm" style="background-color: #071712; border: 1px solid #10b981;">
                        <div class="me-3 p-2 rounded-circle bg-success bg-opacity-20 text-success d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; flex-shrink: 0;">
                            <i class="fas fa-check-circle fs-4"></i>
                        </div>
                        <div>
                            <strong class="text-white d-block mb-1 fs-6">تم تسجيل واحتساب هذا الإنجاز تلقائياً بنجاح! 🎯</strong>
                            <div class="small" style="color: #a7f3d0; line-height: 1.5;">تم تحديث عداد الأعلام وسجل نقاطك في لوحة التحكم الرئيسية والمخططات البيانية.</div>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap justify-content-center gap-3 mt-3">
                        <button type="button" class="btn btn-warning fw-bold text-dark px-4 py-2 shadow" style="box-shadow: 0 4px 15px rgba(245, 158, 11, 0.4) !important;" onclick="fullCelebrationBlast()">
                            <i class="fas fa-magic me-1"></i> إطلاق الألعاب النارية مجدداً 🎊
                        </button>
                        <button type="button" class="btn btn-outline-info fw-bold px-4 py-2" onclick="playVictoryChime()">
                            <i class="fas fa-volume-up me-1"></i> نغمة الفوز 🔊
                        </button>
                    </div>
                </div>
                <div class="modal-footer border-top border-secondary border-opacity-25 justify-content-between py-2 px-4" style="background: rgba(14, 20, 36, 0.95); border-radius: 0 0 14px 14px;">
                    <div class="small d-flex align-items-center text-light">
                        <img src="<?= $img_path ?>" alt="الجوكر" class="rounded-circle border border-warning me-2" style="width: 30px; height: 30px; object-fit: cover;">
                        <span>إعداد وتطوير: <strong class="text-warning">المهندس احمد سليم</strong> 🇵🇸</span>
                    </div>
                    <button type="button" class="btn btn-secondary btn-sm px-4 fw-bold" data-bs-dismiss="modal">متابعة التحديات &larr;</button>
                </div>
            </div>
        </div>
    </div>

    <!-- محرك الكونفيتي والتأثيرات الصوتية -->
    <script src="<?= $js_path ?>"></script>
    <?php
}
