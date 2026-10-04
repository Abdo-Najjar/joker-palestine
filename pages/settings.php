<?php
// ==============================================================================
// صفحة إعدادات وتفضيلات المختبر | مختبر الجوكر الفلسطيني
// إعداد وتطوير: المهندس احمد سليم 🇵🇸
// ==============================================================================

$page_title = "إعدادات وتفضيلات المختبر | مختبر الجوكر الفلسطيني";
require_once __DIR__ . '/../includes/header.php';

$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'save_settings') {
        update_lab_setting('show_hints', isset($_POST['show_hints']));
        update_lab_setting('show_code_comparison', isset($_POST['show_code_comparison']));
        update_lab_setting('show_diagrams', isset($_POST['show_diagrams']));
        update_lab_setting('enable_sounds', isset($_POST['enable_sounds']));
        update_lab_setting('enable_confetti', isset($_POST['enable_confetti']));
        update_lab_setting('enable_waf', isset($_POST['enable_waf']));

        $new_name = trim($_POST['student_name'] ?? '');
        if (!empty($new_name)) {
            update_lab_setting('student_name', $new_name);
            $_SESSION['student_name'] = $new_name;
        }

        $msg = "<div class='alert alert-success border-success py-3 shadow d-flex align-items-center mb-4'>
            <i class='fas fa-check-circle fs-3 text-success me-3'></i>
            <div>
                <strong class='d-block fs-6'>تم حفظ وتحديث إعدادات المختبر بنجاح! 🎯</strong>
                <span class='small text-white-50'>تم تطبيق التغييرات فوراً على كافة التحديات ومحاكي الأمان.</span>
            </div>
        </div>";
    } elseif ($_POST['action'] === 'reset_defaults') {
        unset($_SESSION['lab_settings']);
        get_lab_setting('init');
        $msg = "<div class='alert alert-info border-info py-3 shadow d-flex align-items-center mb-4'>
            <i class='fas fa-undo fs-3 text-info me-3'></i>
            <div>
                <strong>تمت استعادة الإعدادات الافتراضية بنجاح!</strong>
            </div>
        </div>";
    }
}

$show_hints = get_lab_setting('show_hints', false);
$show_code_comparison = get_lab_setting('show_code_comparison', true);
$show_diagrams = get_lab_setting('show_diagrams', true);
$enable_sounds = get_lab_setting('enable_sounds', true);
$enable_confetti = get_lab_setting('enable_confetti', true);
$enable_waf = get_lab_setting('enable_waf', false);
$student_name = get_lab_setting('student_name', $_SESSION['student_name'] ?? 'الباحث الأمني المتميز');
?>

<div class="row">
    <div class="col-lg-9 mx-auto">
        <!-- Header Banner -->
        <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom border-secondary">
            <div>
                <h2 class="fw-bold text-white mb-1">
                    <i class="fas fa-sliders-h text-warning me-2"></i> لوحة التحكم في إعدادات وتفضيلات المختبر
                </h2>
                <p class="text-muted mb-0">تخصيص تجربة التحديات، إظهار أو إخفاء التلميحات والأكواد، وتفعيل المحاكيات الأمنية المتقدمة.</p>
            </div>
            <div class="badge bg-black border border-warning text-warning p-2 px-3 fs-6 rounded-pill">
                إعداد: <strong class="text-white">المهندس احمد سليم 🇵🇸</strong>
            </div>
        </div>

        <?= $msg ?>

        <form method="POST" action="settings.php">
            <input type="hidden" name="action" value="save_settings">

            <!-- Card 1: Display & Learning Preferences -->
            <div class="card card-cyber mb-4 shadow">
                <div class="card-header py-3 d-flex align-items-center">
                    <i class="fas fa-desktop text-info me-2 fs-5"></i>
                    <h5 class="mb-0 text-white fw-bold">خيارات العرض والتعليم (Display & Explanations)</h5>
                </div>
                <div class="card-body p-4">
                    <!-- Toggle 1: Hints -->
                    <div class="d-flex justify-content-between align-items-center py-3 border-bottom border-secondary border-opacity-25">
                        <div class="me-3">
                            <strong class="text-white fs-6 d-block mb-1">
                                <i class="fas fa-lightbulb text-warning me-2"></i> إظهار التلميحات افتراضياً (Auto-Expand Hints)
                            </strong>
                            <p class="text-muted small mb-0">
                                الوضع الافتراضي مغلق لتحدي الطالب وتشجيعه على التفكير. عند التفعيل، ستفتح جميع قوائم التلميحات تلقائياً دون الحاجة للنقر.
                            </p>
                        </div>
                        <div class="form-check form-switch fs-4">
                            <input class="form-check-input" type="checkbox" name="show_hints" id="show_hints" <?= $show_hints ? 'checked' : '' ?>>
                        </div>
                    </div>

                    <!-- Toggle 2: Code Comparison -->
                    <div class="d-flex justify-content-between align-items-center py-3 border-bottom border-secondary border-opacity-25">
                        <div class="me-3">
                            <strong class="text-white fs-6 d-block mb-1">
                                <i class="fas fa-code text-info me-2"></i> تفعيل صناديق مقارنة الأكواد (الكود المصاب vs الكود الآمن)
                            </strong>
                            <p class="text-muted small mb-0">
                                إظهار التحليل البرمجي لكيفية كتابة الثغرة وطريقة ترقيعها هندسياً أسفل كل تحدٍّ. يمكنك إخفاؤه للتركيز التام على شاشات الهجوم فقط.
                            </p>
                        </div>
                        <div class="form-check form-switch fs-4">
                            <input class="form-check-input" type="checkbox" name="show_code_comparison" id="show_code_comparison" <?= $show_code_comparison ? 'checked' : '' ?>>
                        </div>
                    </div>

                    <!-- Toggle 3: Architecture Diagrams -->
                    <div class="d-flex justify-content-between align-items-center py-3">
                        <div class="me-3">
                            <strong class="text-white fs-6 d-block mb-1">
                                <i class="fas fa-project-diagram text-success me-2"></i> عرض المخططات الهندسية التوضيحية (Attack Flow Diagrams)
                            </strong>
                            <p class="text-muted small mb-0">
                                تفعيل الرسوم البيانية والمعمارية عالية الدقة التي توضح مسار تدفق كل ثغرة قبل بدء التحدي مع ميزة التكبير (Lightbox).
                            </p>
                        </div>
                        <div class="form-check form-switch fs-4">
                            <input class="form-check-input" type="checkbox" name="show_diagrams" id="show_diagrams" <?= $show_diagrams ? 'checked' : '' ?>>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 2: Interactive Effects & WAF -->
            <div class="card card-cyber mb-4 shadow">
                <div class="card-header py-3 d-flex align-items-center">
                    <i class="fas fa-shield-virus text-danger me-2 fs-5"></i>
                    <h5 class="mb-0 text-white fw-bold">الأمان المتقدم والتأثيرات التفاعلية (Advanced & Effects)</h5>
                </div>
                <div class="card-body p-4">
                    <!-- Toggle 4: WAF Simulation -->
                    <div class="d-flex justify-content-between align-items-center py-3 border-bottom border-secondary border-opacity-25">
                        <div class="me-3">
                            <strong class="text-danger fs-6 d-block mb-1">
                                <i class="fas fa-fire me-2"></i> تفعيل محاكي جدار الحماية (WAF Filter Simulation - ModSecurity Mode)
                            </strong>
                            <p class="text-muted small mb-0">
                                تفعيل فلاتر حماية متقدمة تعترض البايلودات الكلاسيكية الشائعة، مما يجبر الباحث الأمني على استخدام تقنيات التشفير وتجاوز الفلاتر (WAF Bypass).
                            </p>
                        </div>
                        <div class="form-check form-switch fs-4">
                            <input class="form-check-input" type="checkbox" name="enable_waf" id="enable_waf" <?= $enable_waf ? 'checked' : '' ?>>
                        </div>
                    </div>

                    <!-- Toggle 5: Confetti & Fireworks -->
                    <div class="d-flex justify-content-between align-items-center py-3 border-bottom border-secondary border-opacity-25">
                        <div class="me-3">
                            <strong class="text-white fs-6 d-block mb-1">
                                <i class="fas fa-magic text-warning me-2"></i> تشغيل احتفال الألعاب النارية والكونفيتي التلقائي
                            </strong>
                            <p class="text-muted small mb-0">
                                إطلاق احتفال بصري بالكونفيتي والألعاب النارية عند اقتناص أي علم جديد وحل التحدي.
                            </p>
                        </div>
                        <div class="form-check form-switch fs-4">
                            <input class="form-check-input" type="checkbox" name="enable_confetti" id="enable_confetti" <?= $enable_confetti ? 'checked' : '' ?>>
                        </div>
                    </div>

                    <!-- Toggle 6: Victory Sound Effects -->
                    <div class="d-flex justify-content-between align-items-center py-3">
                        <div class="me-3">
                            <strong class="text-white fs-6 d-block mb-1">
                                <i class="fas fa-volume-up text-info me-2"></i> تفعيل نغمة الانتصار الصوتية (Victory Chime)
                            </strong>
                            <p class="text-muted small mb-0">
                                عزف مؤثر صوتي ترحيبي عند الفوز وحل التحدي بنجاح (مع إمكانية كتم الصوت لمن يفضل الهدوء).
                            </p>
                        </div>
                        <div class="form-check form-switch fs-4">
                            <input class="form-check-input" type="checkbox" name="enable_sounds" id="enable_sounds" <?= $enable_sounds ? 'checked' : '' ?>>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 3: Student Identity Profile -->
            <div class="card card-cyber mb-4 shadow">
                <div class="card-header py-3 d-flex align-items-center">
                    <i class="fas fa-user-shield text-warning me-2 fs-5"></i>
                    <h5 class="mb-0 text-white fw-bold">هوية الباحث الأمني (Researcher Profile)</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row align-items-center">
                        <div class="col-md-7 mb-3 mb-md-0">
                            <label class="form-label text-light fw-bold" for="student_name">
                                الاسم الظاهر في الشهادة وتقارير الثغرات:
                            </label>
                            <input type="text" class="form-control font-monospace" id="student_name" name="student_name" value="<?= htmlspecialchars($student_name) ?>" placeholder="أدخل اسمك الكريم..." required>
                            <small class="text-muted">هذا الاسم سيتم اعتماده وتوقيعه رقمياً في الشهادة الرسمية وتوثيق الإنجازات.</small>
                        </div>
                        <div class="col-md-5 text-center">
                            <div class="p-3 bg-dark rounded border border-secondary">
                                <div class="small text-muted mb-1">الشهادة ستصدر باسم:</div>
                                <div class="text-warning fw-bold fs-5"><?= htmlspecialchars($student_name) ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="d-flex justify-content-between align-items-center mt-4">
                <button type="submit" class="btn btn-warning btn-lg fw-bold px-5 shadow">
                    <i class="fas fa-save me-2"></i> حفظ وتطبيق الإعدادات
                </button>
                <button type="submit" name="action" value="reset_defaults" class="btn btn-outline-secondary" onclick="return confirm('هل أنت متأكد من استعادة الإعدادات الافتراضية؟');">
                    <i class="fas fa-undo me-1"></i> استعادة الإعدادات الافتراضية
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
