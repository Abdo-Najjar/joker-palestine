<?php
// ==============================================================================
// لوحة التحكم الرئيسية | مختبر الجوكر الفلسطيني (Joker Security Lab)
// إعداد: المهندس احمد سليم
// ==============================================================================

require_once __DIR__ . '/config.php';
$score = get_score();
$sec_level = get_security_level();
$flash = $_SESSION['flash_msg'] ?? null;
unset($_SESSION['flash_msg']);
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الرئيسية | مختبر الجوكر الفلسطيني لاختبار اختراق تطبيقات الويب</title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="assets/images/joker_logo.png">
    <!-- Bootstrap 5 RTL -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <!-- Cairo Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        :root {
            --bg-main: #0b0f19;
            --bg-card: #151c2c;
            --bg-sidebar: #0e1424;
            --accent-green: #10b981;
            --accent-cyan: #06b6d4;
            --accent-red: #ef4444;
            --accent-amber: #f59e0b;
            --border-color: #243149;
            --text-main: #e2e8f0;
            --text-muted: #94a3b8;
        }
        body {
            font-family: 'Cairo', sans-serif;
            background-color: var(--bg-main);
            color: var(--text-main);
            min-height: 100vh;
        }
        .navbar-cyber {
            background-color: var(--bg-sidebar);
            border-bottom: 2px solid var(--border-color);
        }
        .card-cyber {
            background-color: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
            transition: transform 0.2s ease, border-color 0.2s ease;
        }
        .card-cyber:hover {
            border-color: var(--accent-cyan);
            transform: translateY(-2px);
        }
        .hero-banner {
            background: linear-gradient(135deg, rgba(14, 20, 36, 0.95), rgba(21, 28, 44, 0.95)), url('assets/hero_bg.jpg');
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 40px 30px;
            position: relative;
            overflow: hidden;
        }
        .hero-banner::before {
            content: '';
            position: absolute;
            top: 0; right: 0; width: 6px; height: 100%;
            background: linear-gradient(to bottom, #06b6d4, #10b981);
        }
        .badge-solved {
            background-color: var(--accent-green) !important;
            color: #000 !important;
            font-weight: bold;
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark navbar-cyber sticky-top px-3 py-2">
    <div class="container-fluid">
        <a class="navbar-brand d-flex align-items-center text-white me-3" href="index.php">
            <img src="assets/images/joker_logo.png" alt="الجوكر الفلسطيني" class="rounded-circle border border-info shadow-sm me-2" style="width: 44px; height: 44px; object-fit: cover; box-shadow: 0 0 10px rgba(6, 182, 212, 0.5);">
            <div>
                <div class="d-flex align-items-center">
                    <span class="text-info fw-bold fs-5">مختبر الجوكر الفلسطيني</span>
                    <span class="badge bg-secondary ms-2" style="font-size: 0.72rem;">Joker Security Lab v2.0</span>
                </div>
                <div class="fw-bold text-warning" style="font-size: 0.78rem;">
                    <i class="fas fa-shield-alt text-info me-1"></i> إعداد وتطوير: <strong class="text-white">المهندس احمد سليم</strong> 🇵🇸
                </div>
            </div>
        </a>
        
        <div class="d-flex align-items-center gap-2 ms-auto">
            <!-- CTF Solved Counter -->
            <span class="badge bg-dark border border-warning text-warning p-2 fs-6 shadow-sm me-2" style="cursor: pointer;" onclick="if(window.fullCelebrationBlast) window.fullCelebrationBlast();" title="اضغط للاحتفال بإنجازك بالأعلام! 🎊">
                <i class="fas fa-flag me-1 text-warning"></i> الأعلام المكتشفة: 
                <strong class="text-white"><?= $score['solved'] ?></strong> / <?= $score['total'] ?> 
                (<?= $score['percentage'] ?>%)
                <?php if ($score['solved'] > 0): ?>
                    <span class="ms-1">🎉</span>
                <?php endif; ?>
            </span>

            <!-- Security Level Switcher -->
            <div class="dropdown me-2">
                <button class="btn btn-sm <?= $sec_level === 'low' ? 'btn-danger' : 'btn-success' ?> dropdown-toggle fw-bold" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-shield-alt me-1"></i>
                    <?= $sec_level === 'low' ? 'المستوى: ضعيف [Low]' : 'المستوى: محمي [Secure]' ?>
                </button>
                <ul class="dropdown-menu dropdown-menu-dark">
                    <li><a class="dropdown-item <?= $sec_level === 'low' ? 'active' : '' ?>" href="?set_sec=low"><span class="badge bg-danger me-2">Low</span> ضعيف (للتطبيق والشرح)</a></li>
                    <li><a class="dropdown-item <?= $sec_level === 'secure' ? 'active' : '' ?>" href="?set_sec=secure"><span class="badge bg-success me-2">Secure</span> محمي (الكود الآمن بعد الترقيع)</a></li>
                </ul>
            </div>

            <a href="pages/tools.php" class="btn btn-outline-info btn-sm fw-bold text-nowrap">
                <i class="fas fa-terminal me-1"></i> الأدوات 🛠️
            </a>
            <a href="pages/bounty.php" class="btn btn-outline-success btn-sm fw-bold text-nowrap">
                <i class="fas fa-dollar-sign me-1"></i> HackerOne 💰
            </a>
            <a href="pages/quiz.php" class="btn btn-outline-warning btn-sm fw-bold text-nowrap">
                <i class="fas fa-graduation-cap me-1"></i> الاختبار والشهادة 🎓
            </a>
            <a href="pages/settings.php" class="btn btn-outline-secondary btn-sm fw-bold text-nowrap" title="إعدادات المختبر">
                <i class="fas fa-cog me-1"></i> إعدادات
            </a>
            <a href="docs/joker_security_lab_manual.pdf" target="_blank" class="btn btn-outline-primary btn-sm fw-bold text-nowrap">
                <i class="fas fa-file-pdf me-1 text-danger"></i> دليل PDF
            </a>
            <a href="reset.php" class="btn btn-outline-danger btn-sm fw-bold text-nowrap">
                <i class="fas fa-redo-alt me-1"></i> ضبط
            </a>
        </div>
    </div>
</nav>

<div class="container py-4">

    <?php if ($flash): ?>
        <div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show shadow-sm mb-4" role="alert">
            <?= $flash['text'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Hero Banner -->
    <div class="hero-banner mb-5 shadow-lg">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <div class="d-inline-flex align-items-center bg-dark bg-opacity-75 border border-info rounded-pill px-3 py-1 mb-3">
                    <img src="assets/images/joker_logo.png" alt="الجوكر الفلسطيني" class="rounded-circle border border-info me-2" style="width: 28px; height: 28px; object-fit: cover;">
                    <span class="text-white small fw-bold">إعداد وتطوير: <strong class="text-warning">المهندس احمد سليم</strong></span>
                    <span class="text-danger ms-2">🇵🇸</span>
                </div>
                <h1 class="display-6 fw-bold text-white mb-2">
                    مختبر الجوكر الفلسطيني لتطبيقات الويب
                </h1>
                <p class="lead text-light mb-3" style="font-size: 1.15rem;">
                    بيئة تفاعلية مصممة خصيصاً لتدريب الطلاب وشرح أخطر وأشهر ثغرات الويب عملياً (OWASP Top 10)، مبنية بالكامل بلغة PHP وتعمل عبر قاعدة بيانات SQLite بدون أي إعدادات معقدة.
                </p>
                <div class="d-flex flex-wrap gap-2">
                    <a href="pages/sqli.php" class="btn btn-primary fw-bold px-4 py-2">
                        <i class="fas fa-play me-1"></i> ابدأ التحديات العملية
                    </a>
                    <a href="docs/joker_security_lab_manual.pdf" target="_blank" class="btn btn-outline-light fw-bold px-4 py-2">
                        <i class="fas fa-book-reader me-1 text-info"></i> تحميل الشرح والكتيب التوثيقي
                    </a>
                </div>
            </div>
            <div class="col-lg-4 text-center mt-4 mt-lg-0">
                <div class="card card-cyber p-3 text-center border-info">
                    <h5 class="text-info fw-bold mb-2"><i class="fas fa-chart-pie me-1"></i> تقدمك في حل الأعلام</h5>
                    <div class="display-4 fw-bold text-white mb-2"><?= $score['percentage'] ?>%</div>
                    <div class="progress mb-2" style="height: 10px; background-color: #0b0f19;">
                        <div class="progress-bar bg-success progress-bar-striped progress-bar-animated" style="width: <?= $score['percentage'] ?>%"></div>
                    </div>
                    <small class="text-muted"><?= $score['solved'] ?> من أصل <?= $score['total'] ?> علماً تم اكتشافها</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="row g-3 mb-5">
        <div class="col-md-3">
            <div class="card card-cyber p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small">إجمالي التحديات</div>
                        <h3 class="fw-bold text-white mb-0">22 تحدياً</h3>
                    </div>
                    <i class="fas fa-shield-virus fa-2x text-warning"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-cyber p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small">قاعدة البيانات</div>
                        <h3 class="fw-bold text-white mb-0">SQLite 3</h3>
                    </div>
                    <i class="fas fa-database fa-2x text-info"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-cyber p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small">مستوى الحماية الحالي</div>
                        <h3 class="fw-bold <?= $sec_level === 'low' ? 'text-danger' : 'text-success' ?> mb-0">
                            <?= $sec_level === 'low' ? 'ضعيف (Low)' : 'محمي (Secure)' ?>
                        </h3>
                    </div>
                    <i class="fas fa-sliders-h fa-2x text-primary"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-cyber p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small">كتيب الشرح والحلول</div>
                        <h3 class="fw-bold text-white mb-0">PDF متكامل</h3>
                    </div>
                    <i class="fas fa-file-pdf fa-2x text-danger"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Exam & Certificate Callout Banner -->
    <div class="card card-cyber p-4 mb-5 border-warning shadow-lg" style="background: linear-gradient(135deg, rgba(245, 158, 11, 0.08) 0%, rgba(14, 20, 36, 0.95) 100%);">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <div class="d-flex align-items-center mb-2">
                    <span class="badge bg-warning text-dark fw-bold px-3 py-1 me-2">🎓 الاعتماد والتقييم</span>
                    <span class="text-muted small">تقييم الكفاءة في أمن الويب (OWASP Top 10)</span>
                </div>
                <h4 class="text-white fw-bold mb-2">
                    اختبار الكفاءة النهائي والشهادة الرقمية المعتمدة
                </h4>
                <p class="text-light text-opacity-75 mb-3">
                    هل أتممت التحديات وتريد اختبار معلوماتك؟ خض الاختبار الأمني التفاعلي المكون من 7 أسئلة احترافية مع شروحات الحلول، واحصل فوراً على شهادة إتمام رقمية فاخرة باسمك قابلة للطباعة والحفظ كـ PDF بإعداد وتوقيع <strong class="text-warning">المهندس احمد سليم 🇵🇸</strong>!
                </p>
                <div class="d-flex flex-wrap gap-2">
                    <a href="pages/quiz.php" class="btn btn-warning fw-bold px-4 py-2 shadow">
                        <i class="fas fa-pen-nib me-1"></i> خوض الاختبار الأمني الآن 🚀
                    </a>
                    <a href="pages/certificate.php" class="btn btn-outline-light fw-bold px-3 py-2">
                        <i class="fas fa-award me-1 text-warning"></i> معاينة واستلام الشهادة 📜
                    </a>
                </div>
            </div>
            <div class="col-lg-4 text-center mt-3 mt-lg-0">
                <div class="p-3 rounded-circle bg-black bg-opacity-50 d-inline-flex align-items-center justify-content-center border border-warning shadow" style="width: 120px; height: 120px;">
                    <i class="fas fa-certificate fa-3x text-warning"></i>
                </div>
                <div class="text-info small mt-2 fw-bold">معتمدة من مختبر الجوكر الفلسطيني</div>
            </div>
        </div>
    </div>

    <!-- Hacker Tools & Bug Bounty Dual Showcase Cards -->
    <div class="row g-4 mb-5">
        <div class="col-md-6">
            <div class="card card-cyber p-4 h-100 border-info shadow-lg" style="background: linear-gradient(135deg, rgba(6, 182, 212, 0.08) 0%, rgba(14, 20, 36, 0.95) 100%);">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="badge bg-info text-dark fw-bold px-3 py-1"><i class="fas fa-terminal me-1"></i> أدوات الهاكرز</span>
                    <span class="badge bg-black border border-info text-info">Live Playbook</span>
                </div>
                <h4 class="text-white fw-bold mb-2">ترسانة أدوات الهاكر الأخلاقي (Hacking Tools)</h4>
                <p class="text-muted small mb-3">
                    أوامر حقيقية مجهزة ومختبرة لأشهر أدوات الاختراق: <strong>Burp Suite, sqlmap, ffuf, Hydra, cURL</strong> مع أمثلة مباشرة على صفحات وتحديات المختبر.
                </p>
                <div class="mt-auto">
                    <a href="pages/tools.php" class="btn btn-outline-info w-100 fw-bold py-2 shadow-sm">
                        <i class="fas fa-tools me-1"></i> فتح ترسانة الأدوات والأوامر &larr;
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card card-cyber p-4 h-100 border-success shadow-lg" style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.08) 0%, rgba(14, 20, 36, 0.95) 100%);">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="badge bg-success text-dark fw-bold px-3 py-1"><i class="fas fa-dollar-sign me-1"></i> HackerOne Style</span>
                    <span class="badge bg-black border border-success text-success">Bug Bounty</span>
                </div>
                <h4 class="text-white fw-bold mb-2">برنامج مكافآت الثغرات وتقارير الهاكرز</h4>
                <p class="text-muted small mb-3">
                    محاكاة حقيقية لمنصة <strong>HackerOne</strong>: قدّم تقارير احترافية عن الثغرات التي تكتشفها، واجمع المكافآت المالية الافتراضية، واطلع على أرشيف تقارير الهاكرز الواقعية.
                </p>
                <div class="mt-auto">
                    <a href="pages/bounty.php" class="btn btn-outline-success w-100 fw-bold py-2 shadow-sm">
                        <i class="fas fa-bug me-1"></i> دخول برنامج مكافآت الثغرات &larr;
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Cyberpunk Analytics & Progress Visualizer -->
    <div class="row g-4 mb-5">
        <div class="col-lg-7">
            <div class="card card-cyber p-3 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold text-white mb-0">
                        <i class="fas fa-satellite-dish text-info me-2"></i> رادار تحليل المهارات السيبرانية
                    </h5>
                    <span class="badge bg-dark border border-info text-info">Radar Matrix</span>
                </div>
                <div style="height: 320px; position: relative;">
                    <canvas id="skillsRadarChart"></canvas>
                </div>
                <small class="text-muted text-center d-block mt-2">
                    توزيع الكفاءة في المجالات الستة: الحقن، أمن العميل، الصلاحيات، تزوير الخادم، الملفات، المنطق البرمجي
                </small>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card card-cyber p-3 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold text-white mb-0">
                        <i class="fas fa-bullseye text-warning me-2"></i> إحصائية صيد الأعلام
                    </h5>
                    <span class="badge bg-dark border border-warning text-warning">Flags Tracker</span>
                </div>
                <div style="height: 280px; position: relative;">
                    <canvas id="flagsDoughnutChart"></canvas>
                </div>
                <div class="text-center mt-2">
                    <span class="badge bg-success fs-6 px-3 py-2">
                        <?= $score['solved'] ?> أعلام مكتشفة
                    </span>
                    <span class="badge bg-secondary fs-6 px-3 py-2 ms-2">
                        <?= max(0, $score['total'] - $score['solved']) ?> أعلام متبقية
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Challenges Grid -->
    <h3 class="fw-bold text-white mb-3 d-flex align-items-center">
        <i class="fas fa-crosshairs text-danger me-2"></i>
        فهرس الثغرات والتحديات العملية
    </h3>

    <div class="row g-4 mb-5">
        
        <!-- 1. SQLi -->
        <div class="col-md-6 col-lg-4">
            <div class="card card-cyber h-100 p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="badge bg-warning text-dark"><i class="fas fa-database me-1"></i> قاعدة البيانات</span>
                    <?php if (is_flag_solved('sqli_auth') && is_flag_solved('sqli_union')): ?>
                        <span class="badge badge-solved"><i class="fas fa-check"></i> مكتمل</span>
                    <?php endif; ?>
                </div>
                <h5 class="fw-bold text-info">1. حقن قواعد البيانات (SQLi)</h5>
                <p class="text-muted small mb-3">تجاوز شاشات تسجيل الدخول واستخراج البيانات الحساسة عبر UNION في SQLite.</p>
                <div class="mt-auto">
                    <a href="pages/sqli.php" class="btn btn-outline-info btn-sm w-100 fw-bold">دخول التحدي &larr;</a>
                </div>
            </div>
        </div>

        <!-- 2. XSS -->
        <div class="col-md-6 col-lg-4">
            <div class="card card-cyber h-100 p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="badge bg-info text-dark"><i class="fas fa-code me-1"></i> جافاسكربت والعميل</span>
                    <?php if (is_flag_solved('xss_reflected') && is_flag_solved('xss_stored') && is_flag_solved('xss_dom')): ?>
                        <span class="badge badge-solved"><i class="fas fa-check"></i> مكتمل</span>
                    <?php endif; ?>
                </div>
                <h5 class="fw-bold text-info">2. ثغرات السكربتات (XSS)</h5>
                <p class="text-muted small mb-3">ثلاثة أقسام تفاعلية: XSS المنعكس (Reflected)، المخزن (Stored)، والمعتمد على الـ DOM.</p>
                <div class="mt-auto">
                    <a href="pages/xss.php" class="btn btn-outline-info btn-sm w-100 fw-bold">دخول التحدي &larr;</a>
                </div>
            </div>
        </div>

        <!-- 3. LFI -->
        <div class="col-md-6 col-lg-4">
            <div class="card card-cyber h-100 p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="badge bg-danger"><i class="fas fa-folder-open me-1"></i> خادم الملفات</span>
                    <?php if (is_flag_solved('lfi')): ?>
                        <span class="badge badge-solved"><i class="fas fa-check"></i> مكتمل</span>
                    <?php endif; ?>
                </div>
                <h5 class="fw-bold text-info">3. تضمين الملفات واجتياز المسارات (LFI)</h5>
                <p class="text-muted small mb-3">قراءة ملفات النظام الحساسة واستخدام مشغلات PHP Wrappers لقراءة الأكواد المصدرية.</p>
                <div class="mt-auto">
                    <a href="pages/lfi.php" class="btn btn-outline-info btn-sm w-100 fw-bold">دخول التحدي &larr;</a>
                </div>
            </div>
        </div>

        <!-- 4. File Upload -->
        <div class="col-md-6 col-lg-4">
            <div class="card card-cyber h-100 p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="badge bg-success"><i class="fas fa-upload me-1"></i> RCE</span>
                    <?php if (is_flag_solved('upload')): ?>
                        <span class="badge badge-solved"><i class="fas fa-check"></i> مكتمل</span>
                    <?php endif; ?>
                </div>
                <h5 class="fw-bold text-info">4. رفع الملفات غير الآمن (File Upload)</h5>
                <p class="text-muted small mb-3">رفع سكربتات PHP (Web Shells) وتنفيذ أوامر مباشرة على خادم الويب.</p>
                <div class="mt-auto">
                    <a href="pages/upload.php" class="btn btn-outline-info btn-sm w-100 fw-bold">دخول التحدي &larr;</a>
                </div>
            </div>
        </div>

        <!-- 5. Command Injection -->
        <div class="col-md-6 col-lg-4">
            <div class="card card-cyber h-100 p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="badge bg-danger"><i class="fas fa-terminal me-1"></i> أوامر النظام</span>
                    <?php if (is_flag_solved('cmdi')): ?>
                        <span class="badge badge-solved"><i class="fas fa-check"></i> مكتمل</span>
                    <?php endif; ?>
                </div>
                <h5 class="fw-bold text-info">5. حقن أوامر النظام (Cmd Injection)</h5>
                <p class="text-muted small mb-3">استغلال أداة فحص الشبكة (Ping) لحقن وتنفيذ أوامر نظام التشغيل.</p>
                <div class="mt-auto">
                    <a href="pages/cmdi.php" class="btn btn-outline-info btn-sm w-100 fw-bold">دخول التحدي &larr;</a>
                </div>
            </div>
        </div>

        <!-- 6. IDOR -->
        <div class="col-md-6 col-lg-4">
            <div class="card card-cyber h-100 p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="badge bg-warning text-dark"><i class="fas fa-id-card me-1"></i> الصلاحيات</span>
                    <?php if (is_flag_solved('idor')): ?>
                        <span class="badge badge-solved"><i class="fas fa-check"></i> مكتمل</span>
                    <?php endif; ?>
                </div>
                <h5 class="fw-bold text-info">6. التحكم غير المباشر (IDOR)</h5>
                <p class="text-muted small mb-3">التلاعب بأرقام المعرفات (IDs) في الروابط لقراءة رسائل وبيانات الإدارة السرية.</p>
                <div class="mt-auto">
                    <a href="pages/idor.php" class="btn btn-outline-info btn-sm w-100 fw-bold">دخول التحدي &larr;</a>
                </div>
            </div>
        </div>

        <!-- 7. CSRF -->
        <div class="col-md-6 col-lg-4">
            <div class="card card-cyber h-100 p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="badge bg-info text-dark"><i class="fas fa-random me-1"></i> تزوير الطلبات</span>
                    <?php if (is_flag_solved('csrf')): ?>
                        <span class="badge badge-solved"><i class="fas fa-check"></i> مكتمل</span>
                    <?php endif; ?>
                </div>
                <h5 class="fw-bold text-info">7. تزوير الطلبات عبر المواقع (CSRF)</h5>
                <p class="text-muted small mb-3">محاكاة إرسال طلب خفي من موقع خارجي لتعديل بريد الضحية في غياب توكن الحماية.</p>
                <div class="mt-auto">
                    <a href="pages/csrf.php" class="btn btn-outline-info btn-sm w-100 fw-bold">دخول التحدي &larr;</a>
                </div>
            </div>
        </div>

        <!-- 8. Sensitive Data -->
        <div class="col-md-6 col-lg-4">
            <div class="card card-cyber h-100 p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="badge bg-secondary"><i class="fas fa-user-secret me-1"></i> كشف الملفات</span>
                    <?php if (is_flag_solved('sensitive')): ?>
                        <span class="badge badge-solved"><i class="fas fa-check"></i> مكتمل</span>
                    <?php endif; ?>
                </div>
                <h5 class="fw-bold text-info">8. كشف البيانات وقاعدة البيانات</h5>
                <p class="text-muted small mb-3">خطر ترك ملف SQLite في المجلد العام وتحميله مباشرة من المتصفح وطريقة الحماية.</p>
                <div class="mt-auto">
                    <a href="pages/sensitive.php" class="btn btn-outline-info btn-sm w-100 fw-bold">دخول التحدي &larr;</a>
                </div>
            </div>
        </div>

        <!-- 9. Open Redirect -->
        <div class="col-md-6 col-lg-4">
            <div class="card card-cyber h-100 p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="badge bg-primary"><i class="fas fa-external-link-alt me-1"></i> التصيد</span>
                    <?php if (is_flag_solved('redirect')): ?>
                        <span class="badge badge-solved"><i class="fas fa-check"></i> مكتمل</span>
                    <?php endif; ?>
                </div>
                <h5 class="fw-bold text-info">9. التوجيه المفتوح (Open Redirect)</h5>
                <p class="text-muted small mb-3">كيف يستغل المهاجم الروابط التوجيهية في الموقع لخداع الضحايا بروابط تصيد احتيالي.</p>
                <div class="mt-auto">
                    <a href="pages/redirect.php" class="btn btn-outline-info btn-sm w-100 fw-bold">دخول التحدي &larr;</a>
                </div>
            </div>
        </div>

        <!-- 10. SSRF -->
        <div class="col-md-6 col-lg-4">
            <div class="card card-cyber h-100 p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="badge bg-warning text-dark"><i class="fas fa-network-wired me-1"></i> تزوير السيرفر</span>
                    <?php if (is_flag_solved('ssrf')): ?>
                        <span class="badge badge-solved"><i class="fas fa-check"></i> مكتمل</span>
                    <?php endif; ?>
                </div>
                <h5 class="fw-bold text-info">10. تزوير الطلب من جانب الخادم (SSRF)</h5>
                <p class="text-muted small mb-3">استغلال ميزة جلب الروابط لاستهداف الخدمات الداخلية وشبكة localhost.</p>
                <div class="mt-auto">
                    <a href="pages/ssrf.php" class="btn btn-outline-info btn-sm w-100 fw-bold">دخول التحدي &larr;</a>
                </div>
            </div>
        </div>

        <!-- 11. Brute Force -->
        <div class="col-md-6 col-lg-4">
            <div class="card card-cyber h-100 p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="badge bg-danger"><i class="fas fa-key me-1"></i> كلمات المرور</span>
                    <?php if (is_flag_solved('bruteforce')): ?>
                        <span class="badge badge-solved"><i class="fas fa-check"></i> مكتمل</span>
                    <?php endif; ?>
                </div>
                <h5 class="fw-bold text-info">11. التخمين (Brute Force)</h5>
                <p class="text-muted small mb-3">غياب Rate Limiting والتخمين على كلمات المرور الضعيفة مع قاموس تجريبي.</p>
                <div class="mt-auto">
                    <a href="pages/bruteforce.php" class="btn btn-outline-info btn-sm w-100 fw-bold">دخول التحدي &larr;</a>
                </div>
            </div>
        </div>

        <!-- 12. Type Juggling -->
        <div class="col-md-6 col-lg-4">
            <div class="card card-cyber h-100 p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="badge bg-info text-dark"><i class="fas fa-balance-scale me-1"></i> منطق PHP</span>
                    <?php if (is_flag_solved('type_juggling')): ?>
                        <span class="badge badge-solved"><i class="fas fa-check"></i> مكتمل</span>
                    <?php endif; ?>
                </div>
                <h5 class="fw-bold text-info">12. مقارنات PHP الضعيفة (Type Juggling)</h5>
                <p class="text-muted small mb-3">تجاوز دوال مثل strcmp() عبر إرسال مصفوفات في المقارنات الضعيفة.</p>
                <div class="mt-auto">
                    <a href="pages/type_juggling.php" class="btn btn-outline-info btn-sm w-100 fw-bold">دخول التحدي &larr;</a>
                </div>
            </div>
        </div>

        <!-- 13. Broken Access Control -->
        <div class="col-md-6 col-lg-4">
            <div class="card card-cyber h-100 p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="badge bg-warning text-dark"><i class="fas fa-cookie-bite me-1"></i> الكوكي والرتب</span>
                    <?php if (is_flag_solved('access_control')): ?>
                        <span class="badge badge-solved"><i class="fas fa-check"></i> مكتمل</span>
                    <?php endif; ?>
                </div>
                <h5 class="fw-bold text-info">13. التلاعب بالصلاحيات والكوكي</h5>
                <p class="text-muted small mb-3">تعديل ملف تعريف الارتباط user_role للدخول على لوحة المشرف وتصعيد الصلاحيات.</p>
                <div class="mt-auto">
                    <a href="pages/access_control.php" class="btn btn-outline-info btn-sm w-100 fw-bold">دخول التحدي &larr;</a>
                </div>
            </div>
        </div>

        <!-- 14. XXE Injection -->
        <div class="col-md-6 col-lg-4">
            <div class="card card-cyber h-100 p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="badge bg-danger"><i class="fas fa-file-code me-1"></i> XML External Entity</span>
                    <?php if (is_flag_solved('xxe')): ?>
                        <span class="badge badge-solved"><i class="fas fa-check"></i> مكتمل</span>
                    <?php endif; ?>
                </div>
                <h5 class="fw-bold text-info">14. حقن الكيانات الخارجية (XXE)</h5>
                <p class="text-muted small mb-3">استغلال محلل XML غير المحمي عبر DTD و SYSTEM لقراءة الملفات الحساسة وسرقة البيانات.</p>
                <div class="mt-auto">
                    <a href="pages/xxe.php" class="btn btn-outline-info btn-sm w-100 fw-bold">دخول التحدي &larr;</a>
                </div>
            </div>
        </div>

        <!-- 15. JWT Attacks -->
        <div class="col-md-6 col-lg-4">
            <div class="card card-cyber h-100 p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="badge bg-warning text-dark"><i class="fas fa-id-badge me-1"></i> تزوير التوكنات</span>
                    <?php if (is_flag_solved('jwt')): ?>
                        <span class="badge badge-solved"><i class="fas fa-check"></i> مكتمل</span>
                    <?php endif; ?>
                </div>
                <h5 class="fw-bold text-info">15. تزوير توكنات المصادقة (JWT)</h5>
                <p class="text-muted small mb-3">تجاوز توقيع التوكن واستغلال خوارزمية "alg": "none" وتصعيد الصلاحيات إلى مدير النظام.</p>
                <div class="mt-auto">
                    <a href="pages/jwt.php" class="btn btn-outline-info btn-sm w-100 fw-bold">دخول التحدي &larr;</a>
                </div>
            </div>
        </div>

        <!-- 16. SSTI -->
        <div class="col-md-6 col-lg-4">
            <div class="card card-cyber h-100 p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="badge bg-info text-dark"><i class="fas fa-cubes me-1"></i> RCE للقوالب</span>
                    <?php if (is_flag_solved('ssti')): ?>
                        <span class="badge badge-solved"><i class="fas fa-check"></i> مكتمل</span>
                    <?php endif; ?>
                </div>
                <h5 class="fw-bold text-info">16. حقن محركات القوالب (SSTI)</h5>
                <p class="text-muted small mb-3">حقن تعبيرات برمجية مثل {{7*7}} داخل محرك القوالب والوصول لتنفيذ الأوامر عن بُعد.</p>
                <div class="mt-auto">
                    <a href="pages/ssti.php" class="btn btn-outline-info btn-sm w-100 fw-bold">دخول التحدي &larr;</a>
                </div>
            </div>
        </div>

        <!-- 17. Insecure Deserialization -->
        <div class="col-md-6 col-lg-4">
            <div class="card card-cyber h-100 p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="badge bg-danger"><i class="fas fa-boxes-stacked me-1"></i> فك التسلسل</span>
                    <?php if (is_flag_solved('deserialization')): ?>
                        <span class="badge badge-solved"><i class="fas fa-check"></i> مكتمل</span>
                    <?php endif; ?>
                </div>
                <h5 class="fw-bold text-info">17. فك التسلسل غير الآمن (Deserialization)</h5>
                <p class="text-muted small mb-3">استغلال دالة unserialize() وتعديل خصائص الكائنات البرمجية وتفعيل الدوال السحرية.</p>
                <div class="mt-auto">
                    <a href="pages/deserialization.php" class="btn btn-outline-info btn-sm w-100 fw-bold">دخول التحدي &larr;</a>
                </div>
            </div>
        </div>

        <!-- 18. Race Condition -->
        <div class="col-md-6 col-lg-4">
            <div class="card card-cyber h-100 p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="badge bg-warning text-dark"><i class="fas fa-stopwatch me-1"></i> التزامن المالي</span>
                    <?php if (is_flag_solved('race_condition')): ?>
                        <span class="badge badge-solved"><i class="fas fa-check"></i> مكتمل</span>
                    <?php endif; ?>
                </div>
                <h5 class="fw-bold text-info">18. سباق العمليات (Race Condition)</h5>
                <p class="text-muted small mb-3">استغلال ثغرة TOCTOU عبر إرسال طلبات متزامنة بالتوازي لمضاعفة رصيد المحفظة والكوبونات.</p>
                <div class="mt-auto">
                    <a href="pages/race_condition.php" class="btn btn-outline-info btn-sm w-100 fw-bold">دخول التحدي &larr;</a>
                </div>
            </div>
        </div>

        <!-- 19. CORS Misconfiguration -->
        <div class="col-md-6 col-lg-4">
            <div class="card card-cyber h-100 p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="badge bg-danger"><i class="fas fa-network-wired me-1"></i> سياسة الأصول</span>
                    <?php if (is_flag_solved('cors')): ?>
                        <span class="badge badge-solved"><i class="fas fa-check"></i> مكتمل</span>
                    <?php endif; ?>
                </div>
                <h5 class="fw-bold text-info">19. سوء تهيئة مشاركة الموارد (CORS)</h5>
                <p class="text-muted small mb-3">استغلال عكس ترويسة Origin مع الاعتمادات لسحب البيانات المصرفية السرية من موقع المهاجم.</p>
                <div class="mt-auto">
                    <a href="pages/cors.php" class="btn btn-outline-info btn-sm w-100 fw-bold">دخول التحدي &larr;</a>
                </div>
            </div>
        </div>

    </div>

</div>

<footer class="mt-5 py-4 border-top border-secondary text-center rounded-3 shadow" style="background-color: #0e1424;">
    <div class="container">
        <p class="mb-1 text-light fw-bold fs-6">
            ⚔️ مختبر الجوكر الفلسطيني لاختبار اختراق تطبيقات الويب (Joker Security Lab v2.0)
        </p>
        <p class="mb-2 text-info small">
            منصة تعليمية وتدريبية متكاملة لشرح وتطبيق أشهر ثغرات الويب وكيفية ترقيعها برمجياً
        </p>
        <div class="mt-2 py-2 px-4 d-inline-flex align-items-center rounded-pill bg-black border border-warning shadow">
            <img src="assets/images/joker_logo.png" alt="الجوكر الفلسطيني" class="rounded-circle border border-warning me-2" style="width: 32px; height: 32px; object-fit: cover;">
            <span class="text-white-50 me-1">إعداد وتطوير: </span>
            <strong class="text-warning fs-5">المهندس احمد سليم</strong>
            <span class="text-danger ms-1">🇵🇸</span>
        </div>
        <div class="mt-2 small text-light text-opacity-75">
            جميع الحقوق محفوظة للأغراض التعليمية والأمن الأخلاقي &copy; <?= date('Y') ?>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="assets/js/main.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Radar Chart
    const radarCtx = document.getElementById('skillsRadarChart');
    if (radarCtx && typeof Chart !== 'undefined') {
        new Chart(radarCtx, {
            type: 'radar',
            data: {
                labels: [
                    'حقن البيانات (SQLi / CMDi)',
                    'أمن العميل (XSS / CSRF)',
                    'التحكم بالصلاحيات (IDOR)',
                    'تزوير الخادم (SSRF)',
                    'أمن الملفات (LFI / Upload)',
                    'المنطق البرمجي (Type Juggling)'
                ],
                datasets: [{
                    label: 'مستوى التغطية والإتقان (%)',
                    data: [85, 75, 90, 65, 80, 70],
                    backgroundColor: 'rgba(6, 182, 212, 0.25)',
                    borderColor: '#06b6d4',
                    pointBackgroundColor: '#f59e0b',
                    pointBorderColor: '#fff',
                    pointHoverBackgroundColor: '#fff',
                    pointHoverBorderColor: '#06b6d4',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    r: {
                        angleLines: { color: 'rgba(255, 255, 255, 0.1)' },
                        grid: { color: 'rgba(255, 255, 255, 0.08)' },
                        pointLabels: {
                            color: '#cbd5e1',
                            font: { size: 12, family: "'Cairo', sans-serif" }
                        },
                        ticks: {
                            color: '#64748b',
                            backdropColor: 'transparent',
                            stepSize: 20
                        },
                        suggestedMin: 0,
                        suggestedMax: 100
                    }
                },
                plugins: {
                    legend: {
                        labels: {
                            color: '#e2e8f0',
                            font: { family: "'Cairo', sans-serif" }
                        }
                    }
                }
            }
        });
    }

    // 2. Doughnut Chart
    const doughnutCtx = document.getElementById('flagsDoughnutChart');
    if (doughnutCtx && typeof Chart !== 'undefined') {
        const solved = <?= (int)$score['solved'] ?>;
        const total = <?= (int)$score['total'] ?>;
        const remaining = Math.max(0, total - solved);
        new Chart(doughnutCtx, {
            type: 'doughnut',
            data: {
                labels: ['الأعلام المكتشفة', 'التحديات المتبقية'],
                datasets: [{
                    data: [solved, remaining],
                    backgroundColor: ['#10b981', '#1e293b'],
                    borderColor: ['#059669', '#334155'],
                    borderWidth: 2,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '72%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: '#e2e8f0',
                            font: { family: "'Cairo', sans-serif" }
                        }
                    }
                }
            }
        });
    }
});
</script>
<?php render_celebration(true); ?>
</body>
</html>
