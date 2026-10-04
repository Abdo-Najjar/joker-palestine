<?php
// ==============================================================================
// شهادة الإتمام والاعتماد الرقمية | مختبر الجوكر الفلسطيني
// إعداد وتطوير: المهندس احمد سليم 🇵🇸
// ==============================================================================

require_once __DIR__ . '/../config.php';

$score = get_score();
$quiz_passed = $_SESSION['quiz_passed'] ?? false;
$quiz_score = $_SESSION['quiz_score'] ?? ($score['percentage'] > 0 ? $score['percentage'] : 85);

// Student name handling
$student_name = trim($_GET['name'] ?? ($_POST['student_name'] ?? ($_SESSION['student_name'] ?? 'الباحث الأمني المتميز')));
if (empty($student_name)) {
    $student_name = 'الباحث الأمني المتميز';
}
$_SESSION['student_name'] = $student_name;

// Generate deterministic or random Verification ID
$cert_hash = strtoupper(substr(md5($student_name . 'JOKER_PAL_SEC' . date('Y-m')), 0, 8));
$cert_id = "JOKER-PAL-{$cert_hash}";
$issue_date = date('Y/m/d');
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>شهادة إتمام مختبر الجوكر الفلسطيني | <?= htmlspecialchars($student_name) ?></title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="../assets/images/joker_logo.png">
    <!-- Bootstrap 5 RTL -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <!-- Cairo Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&family=Amiri:wght@700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --bg-main: #0b0f19;
            --bg-card: #151c2c;
            --gold-primary: #f59e0b;
            --gold-light: #fbbf24;
            --cyan-glow: #06b6d4;
            --border-color: #243149;
        }

        body {
            font-family: 'Cairo', sans-serif;
            background-color: var(--bg-main);
            color: #e2e8f0;
            min-height: 100vh;
            padding: 20px 0 50px 0;
        }

        .no-print-bar {
            background-color: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 15px 25px;
            margin-bottom: 30px;
        }

        /* Certificate Container & Border Design */
        .cert-outer-wrapper {
            max-width: 1050px;
            margin: 0 auto;
            position: relative;
        }

        .certificate-card {
            background: radial-gradient(circle at center, #131b2e 0%, #0a0e18 100%);
            border: 4px solid #b45309;
            border-radius: 20px;
            padding: 45px 55px;
            position: relative;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.8), 0 0 30px rgba(245, 158, 11, 0.2);
            overflow: hidden;
        }

        /* Luxury Double Ornamental Border */
        .certificate-card::before {
            content: '';
            position: absolute;
            top: 14px;
            bottom: 14px;
            left: 14px;
            right: 14px;
            border: 2px solid rgba(245, 158, 11, 0.45);
            border-radius: 12px;
            pointer-events: none;
        }

        .certificate-card::after {
            content: '';
            position: absolute;
            top: 22px;
            bottom: 22px;
            left: 22px;
            right: 22px;
            border: 1px dashed rgba(6, 182, 212, 0.35);
            border-radius: 8px;
            pointer-events: none;
        }

        /* Watermark Background */
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            opacity: 0.04;
            width: 520px;
            height: 520px;
            pointer-events: none;
            z-index: 1;
        }

        .cert-content {
            position: relative;
            z-index: 2;
        }

        .cert-header-title {
            font-size: 2.2rem;
            font-weight: 900;
            background: linear-gradient(135deg, #fef08a 0%, #f59e0b 50%, #d97706 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            letter-spacing: -0.5px;
        }

        .cert-subtitle {
            color: #06b6d4;
            font-size: 1.05rem;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .student-name-display {
            font-family: 'Amiri', 'Cairo', serif;
            font-size: 2.6rem;
            font-weight: 700;
            color: #ffffff;
            text-shadow: 0 0 15px rgba(245, 158, 11, 0.4);
            border-bottom: 2px solid #f59e0b;
            display: inline-block;
            padding: 0 40px 8px 40px;
            margin: 20px 0;
        }

        .cert-body-text {
            font-size: 1.15rem;
            line-height: 1.9;
            color: #cbd5e1;
            max-width: 820px;
            margin: 0 auto;
        }

        /* Gold Medal Badge */
        .cert-seal {
            width: 110px;
            height: 110px;
            border-radius: 50%;
            background: radial-gradient(circle, #fde047 0%, #ca8a04 70%, #854d0e 100%);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            box-shadow: 0 0 25px rgba(234, 179, 8, 0.6);
            border: 3px solid #fef08a;
            color: #000;
            margin: 0 auto;
            text-align: center;
        }

        .cert-seal span.seal-star {
            font-size: 1.4rem;
            line-height: 1;
        }
        .cert-seal span.seal-text {
            font-size: 0.68rem;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            line-height: 1.2;
        }

        /* Signatures block */
        .signature-block {
            margin-top: 35px;
            padding-top: 20px;
        }

        .sign-line {
            width: 200px;
            height: 2px;
            background: linear-gradient(90deg, transparent, #f59e0b, transparent);
            margin: 8px auto;
        }

        /* Print Media Styles */
        @media print {
            body {
                background: #000 !important;
                padding: 0 !important;
                margin: 0 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .no-print, .no-print * {
                display: none !important;
            }
            .cert-outer-wrapper {
                max-width: 100% !important;
                width: 100% !important;
                margin: 0 !important;
            }
            .certificate-card {
                border-radius: 0 !important;
                box-shadow: none !important;
                border: 4px solid #b45309 !important;
                page-break-inside: avoid !important;
                padding: 30px 40px !important;
            }
            @page {
                size: A4 landscape;
                margin: 10mm;
            }
        }
    </style>
</head>
<body>

<div class="container cert-outer-wrapper">

    <!-- Action Toolbar (Hidden during printing) -->
    <div class="no-print no-print-bar d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div class="d-flex align-items-center gap-3">
            <a href="quiz.php" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-right me-1"></i> العودة للاختبار
            </a>
            <a href="../index.php" class="btn btn-outline-info btn-sm">
                <i class="fas fa-home me-1"></i> الرئيسية
            </a>
            <?php if (!$quiz_passed): ?>
                <span class="badge bg-warning text-dark px-3 py-2">
                    <i class="fas fa-info-circle me-1"></i> وضع المعاينة: يمكنك إدخال اسمك وتجربة الطباعة
                </span>
            <?php else: ?>
                <span class="badge bg-success px-3 py-2">
                    <i class="fas fa-check-circle me-1"></i> معتمد رسمياً باجتياز الاختبار بنسبة <?= $quiz_score ?>%
                </span>
            <?php endif; ?>
        </div>

        <form method="GET" action="certificate.php" class="d-flex align-items-center gap-2">
            <label for="nameInput" class="small text-muted text-nowrap">اسم المتدرب:</label>
            <input type="text" id="nameInput" name="name" class="form-control form-control-sm bg-dark text-white border-secondary" value="<?= htmlspecialchars($student_name) ?>" required style="min-width: 220px;">
            <button type="submit" class="btn btn-primary btn-sm fw-bold text-nowrap">
                <i class="fas fa-user-edit me-1"></i> تحديث الاسم
            </button>
        </form>

        <button onclick="window.print()" class="btn btn-warning btn-sm fw-bold px-4 shadow">
            <i class="fas fa-print me-1"></i> طباعة / حفظ كـ PDF
        </button>
    </div>

    <!-- Official Certificate Card -->
    <div class="certificate-card text-center" id="printableCertificate">
        <!-- Watermark -->
        <img src="../assets/images/joker_logo.png" alt="Watermark" class="watermark">

        <div class="cert-content">
            <!-- Top Branding -->
            <div class="d-flex justify-content-between align-items-center mb-3 px-3">
                <div class="text-start">
                    <span class="badge bg-black border border-info text-info px-3 py-2 fs-6">
                        <i class="fas fa-shield-alt text-warning me-1"></i> مختبر الجوكر الفلسطيني v2.0
                    </span>
                    <div class="small text-muted mt-1 font-monospace">VERIFICATION: <?= $cert_id ?></div>
                </div>

                <img src="../assets/images/joker_logo.png" alt="الجوكر الفلسطيني" class="rounded-circle border border-warning shadow" style="width: 75px; height: 75px; object-fit: cover;">

                <div class="text-end">
                    <span class="badge bg-black border border-warning text-warning px-3 py-2 fs-6">
                        OWASP Top 10 Certified
                    </span>
                    <div class="small text-muted mt-1 font-monospace">ISSUE DATE: <?= $issue_date ?></div>
                </div>
            </div>

            <div class="my-2">
                <h1 class="cert-header-title mb-1">شهادة كفاءة وتفوق في أمن الويب</h1>
                <div class="cert-subtitle">CERTIFICATE OF WEB APPLICATION PENETRATION TESTING PROFICIENCY</div>
            </div>

            <p class="text-muted small mt-2 mb-3">
                تُمنح هذه الشهادة المعتمدة من مختبر الجوكر الفلسطيني تقديراً للإنجاز والالتزام بمعايير الأمن السيبراني
            </p>

            <div class="my-3">
                <span class="text-warning-emphasis small fw-bold">تَشْهَدُ إدارة المختبر بأن الباحث الأمني / المتدرب:</span>
                <br>
                <div class="student-name-display">
                    <?= htmlspecialchars($student_name) ?>
                </div>
            </div>

            <p class="cert-body-text">
                قد أتم بنجاح متطلبات التدريب العملي المكثف في <strong class="text-info">مختبر الجوكر الفلسطيني لاختبار اختراق تطبيقات الويب (OWASP Top 10)</strong>، وأظهر كفاءة عالية في تحليل وفحص واكتشاف أشهر الثغرات الأمنية الحرجة بما فيها (SQLi, XSS, CSRF, LFI, Command Injection, IDOR, SSRF)، مع التطبيق العملي للحلول البرمجية والترقيع الدفاعي المعتمد وفق أفضل الممارسات القياسية.
            </p>

            <div class="row align-items-center signature-block">
                <!-- Solved stats -->
                <div class="col-4 text-center">
                    <div class="border border-secondary rounded-3 p-2 bg-black bg-opacity-50 d-inline-block px-4">
                        <div class="text-muted small">نسبة الكفاءة والتقييم</div>
                        <div class="fw-bold text-success fs-5"><?= $quiz_score ?>% (امتياز)</div>
                        <small class="text-info font-monospace"><?= $score['solved'] ?>/<?= $score['total'] ?> تحدياً مكتشفاً</small>
                    </div>
                </div>

                <!-- Golden Official Seal -->
                <div class="col-4 text-center">
                    <div class="cert-seal">
                        <span class="seal-star">🛡️</span>
                        <span class="seal-text">معتمد رسمياً<br>JOKER LAB<br>OFFICIAL</span>
                    </div>
                </div>

                <!-- Signature & Developer info -->
                <div class="col-4 text-center">
                    <div class="d-inline-block text-center">
                        <div class="text-warning fw-bold fs-5 mb-1" style="font-family: 'Amiri', serif;">
                            م. أحمد سليم 🇵🇸
                        </div>
                        <div class="sign-line"></div>
                        <div class="text-white small fw-bold">المهندس احمد سليم</div>
                        <div class="text-muted small" style="font-size: 0.72rem;">
                            مؤسس ومطور مختبر الجوكر الفلسطيني
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-4 pt-2 border-top border-secondary text-muted small d-flex justify-content-between px-3" style="font-size: 0.75rem;">
                <span>رمز التوثيق الرقمي: <code class="text-warning"><?= $cert_id ?></code></span>
                <span>فلسطين - غزة | منصة تعليمية وتطبيقية للأمن الأخلاقي 🇵🇸</span>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
