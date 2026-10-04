<?php
// ==============================================================================
// برنامج مكافآت الثغرات وتقارير الهاكرز | مختبر الجوكر الفلسطيني (HackerOne Style)
// إعداد وتطوير: المهندس احمد سليم 🇵🇸
// ==============================================================================

$page_title = "برنامج مكافآت الثغرات (Bug Bounty) | مختبر الجوكر الفلسطيني";
require_once __DIR__ . '/../includes/header.php';

// إدارة تقارير ومكافآت الجلسة
if (!isset($_SESSION['bounty_reports'])) {
    $_SESSION['bounty_reports'] = [];
}
if (!isset($_SESSION['total_bounty_earned'])) {
    $_SESSION['total_bounty_earned'] = 0;
}

$submission_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit_report') {
    $title = trim($_POST['title'] ?? '');
    $vuln_type = trim($_POST['vuln_type'] ?? 'sqli');
    $severity = trim($_POST['severity'] ?? 'high');
    $poc = trim($_POST['poc'] ?? '');
    $impact = trim($_POST['impact'] ?? '');

    // احتساب المكافأة الافتراضية بحسب الخطورة
    $bounty_map = [
        'critical' => 2500,
        'high'     => 1200,
        'medium'   => 600,
        'low'      => 250
    ];
    $award_amount = $bounty_map[$severity] ?? 500;

    $report_id = '#JOKER-' . rand(1000, 9999);
    $report_entry = [
        'id'       => $report_id,
        'title'    => $title,
        'type'     => $vuln_type,
        'severity' => $severity,
        'bounty'   => $award_amount,
        'poc'      => $poc,
        'impact'   => $impact,
        'date'     => date('Y/m/d H:i')
    ];

    $_SESSION['bounty_reports'][] = $report_entry;
    $_SESSION['total_bounty_earned'] += $award_amount;

    $submission_msg = "<div class='alert alert-success border-success py-3 shadow mb-4 d-flex align-items-center justify-content-between'>
        <div class='d-flex align-items-center'>
            <span class='fs-1 text-warning me-3'>💰</span>
            <div>
                <h5 class='text-success fw-bold mb-1'>تم قبول تقريرك الأمني وصرف المكافأة بنجاح!</h5>
                <div class='text-light'>تقرير الثغرة <strong>$report_id: " . htmlspecialchars($title) . "</strong> اعتُمد كتقرير صالح واستحققت مكافأة قدرها <span class='badge bg-warning text-dark fs-6 font-monospace'>$$award_amount</span>!</div>
            </div>
        </div>
        <button type='button' class='btn btn-warning text-dark fw-bold' onclick='if(window.fullCelebrationBlast) fullCelebrationBlast();'>
            <i class='fas fa-magic me-1'></i> احتفل 🎊
        </button>
    </div>";
}
?>

<div class="row">
    <div class="col-12">
        <!-- Header Banner HackerOne Style -->
        <div class="card border-0 shadow-lg mb-4" style="background: radial-gradient(circle at top right, #162035 0%, #080d18 100%); border-bottom: 3px solid #f59e0b !important;">
            <div class="card-body p-4">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <img src="../assets/images/joker_logo.png" alt="الجوكر" class="rounded-circle border border-warning shadow" style="width: 65px; height: 65px; object-fit: cover;">
                        <div>
                            <div class="d-flex align-items-center gap-2">
                                <h2 class="text-white fw-bold mb-0">برنامج مكافآت الثغرات الأمنية (Bug Bounty Program)</h2>
                                <span class="badge bg-success"><i class="fas fa-shield-alt me-1"></i> مُفعّل ونشط</span>
                            </div>
                            <p class="text-info mb-0 small">
                                منصة محاكاة واقعية مستوحاة من <strong>HackerOne & Bugcrowd</strong> لتدريب الطلاب على كتابة تقارير الثغرات القياسية وحصاد المكافآت.
                            </p>
                        </div>
                    </div>
                    <div class="badge bg-black border border-info text-info p-2 px-3 fs-6 rounded-pill">
                        إشراف وتطوير: <strong class="text-warning">المهندس احمد سليم 🇵🇸</strong>
                    </div>
                </div>

                <!-- Metrics Counter Bar -->
                <div class="row g-3 mt-3 pt-3 border-top border-secondary border-opacity-50">
                    <div class="col-6 col-md-3">
                        <div class="p-3 bg-black bg-opacity-50 rounded border border-secondary text-center">
                            <div class="text-muted small">إجمالي المكافآت التي جمعتها</div>
                            <div class="fs-4 fw-bold text-warning font-monospace">$<?= number_format($_SESSION['total_bounty_earned']) ?></div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-3 bg-black bg-opacity-50 rounded border border-secondary text-center">
                            <div class="text-muted small">تقارير الثغرات المقبولة</div>
                            <div class="fs-4 fw-bold text-info font-monospace"><?= count($_SESSION['bounty_reports']) ?> تقارير</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-3 bg-black bg-opacity-50 rounded border border-secondary text-center">
                            <div class="text-muted small">متوسط زمن الاستجابة (SLA)</div>
                            <div class="fs-4 fw-bold text-success font-monospace">ساعتان (فوري)</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-3 bg-black bg-opacity-50 rounded border border-secondary text-center">
                            <div class="text-muted small">نطاق الأصول المصرح به</div>
                            <div class="fs-4 fw-bold text-primary font-monospace">In-Scope (v2.0)</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $submission_msg ?>

<!-- Main Row: Submission Form + Real Disclosed Reports -->
<div class="row g-4">

    <!-- Column 1: Bug Bounty Submission Form -->
    <div class="col-lg-6">
        <div class="card card-cyber h-100 shadow">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <span class="text-white fw-bold fs-6">
                    <i class="fas fa-file-signature text-warning me-2"></i> تقديم تقرير ثغرة أمنية جديد (Submit Report)
                </span>
                <span class="badge bg-warning text-dark font-monospace">HackerOne Standard</span>
            </div>
            <div class="card-body p-4">
                <p class="text-muted small mb-3">
                    اكتب تقريراً احترافياً عن إحدى الثغرات التي قمت بحلها في المختبر لتحصل على المكافأة والتقييم:
                </p>

                <form method="POST" action="bounty.php">
                    <input type="hidden" name="action" value="submit_report">

                    <div class="mb-3">
                        <label class="form-label text-light fw-bold">عنوان التقرير (Vulnerability Title):</label>
                        <input type="text" name="title" class="form-control" placeholder="مثال: SQL Injection in search query leading to database dump" required>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-light fw-bold">تصنيف الثغرة (CWE Category):</label>
                            <select name="vuln_type" class="form-select">
                                <option value="SQLi">CWE-89: SQL Injection</option>
                                <option value="XSS">CWE-79: Cross-Site Scripting</option>
                                <option value="SSRF">CWE-918: Server-Side Request Forgery</option>
                                <option value="LFI">CWE-22: Path Traversal & LFI</option>
                                <option value="CMDi">CWE-78: OS Command Injection</option>
                                <option value="IDOR">CWE-639: Insecure Direct Object Reference</option>
                                <option value="CSRF">CWE-352: Cross-Site Request Forgery</option>
                                <option value="AuthBypass">CWE-287: Broken Authentication / Juggling</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-light fw-bold">مستوى الخطورة (CVSS Severity):</label>
                            <select name="severity" class="form-select">
                                <option value="critical">حرجة - Critical ($2,500)</option>
                                <option value="high" selected>عالية - High ($1,200)</option>
                                <option value="medium">متوسطة - Medium ($600)</option>
                                <option value="low">منخفضة - Low ($250)</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-light fw-bold">خطوات إعادة إنتاج الثغرة (Steps to Reproduce - PoC):</label>
                        <textarea name="poc" class="form-control font-monospace" rows="3" placeholder="1. الانتقال إلى صفحة...&#10;2. وضع البايلود التالي في المدخل: ' UNION SELECT...&#10;3. ملاحظة ظهور العلم السري..." required></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-light fw-bold">الأثر الأمني والتأثير (Security Impact):</label>
                        <textarea name="impact" class="form-control" rows="2" placeholder="يتيح للمهاجم قراءة قاعدة البيانات بالكامل أو تصعيد صلاحياته إلى مدير عام..." required></textarea>
                    </div>

                    <button type="submit" class="btn btn-warning fw-bold w-100 py-2 shadow">
                        <i class="fas fa-paper-plane me-2"></i> إرسال التقرير للمراجعة وصرف المكافأة 💸
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Column 2: HackerOne Disclosed Hacktivity Feed -->
    <div class="col-lg-6">
        <div class="card card-cyber h-100 shadow">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <span class="text-white fw-bold fs-6">
                    <i class="fas fa-history text-info me-2"></i> أرشيف تقارير الهاكرز الواقعية (Disclosed Hacktivity)
                </span>
                <span class="badge bg-secondary font-monospace">Real World Reports</span>
            </div>
            <div class="card-body p-4" style="max-height: 560px; overflow-y: auto;">
                <p class="text-muted small mb-3">
                    نماذج لتقارير ثغرات حقيقية تم اكتشافها في شركات عالمية عبر HackerOne بنفس الثغرات الموجودة في مختبرنا:
                </p>

                <!-- Report 1: SSRF -->
                <div class="p-3 bg-dark rounded border border-secondary mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="badge bg-danger">Critical - 9.8</span>
                        <span class="badge bg-success font-monospace fw-bold fs-6">$25,000 Bounty</span>
                    </div>
                    <h6 class="text-info fw-bold mb-1">Capital One / AWS - Server-Side Request Forgery (SSRF)</h6>
                    <p class="text-muted small mb-2">
                        استغلال ثغرة SSRF في جلب الروابط لسحب بيانات الـ Cloud Metadata <code>http://169.254.169.254</code> وتسريب أكثر من 100 مليون سجل بنكي.
                    </p>
                    <div class="small text-white-50"><i class="fas fa-user-ninja me-1 text-warning"></i> الباحث: Security Researcher | تم الإغلاق: حل وترقيع كامل</div>
                </div>

                <!-- Report 2: SQLi -->
                <div class="p-3 bg-dark rounded border border-secondary mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="badge bg-danger">Critical - 9.5</span>
                        <span class="badge bg-success font-monospace fw-bold fs-6">$10,000 Bounty</span>
                    </div>
                    <h6 class="text-info fw-bold mb-1">Uber - Blind SQL Injection on Partner Portal</h6>
                    <p class="text-muted small mb-2">
                        حقن استعلامات SQLi في صندوق بحث الشركاء مكن الباحث من سحب جداول المستخدمين والجلسات الإدارية.
                    </p>
                    <div class="small text-white-50"><i class="fas fa-user-ninja me-1 text-warning"></i> الباحث: Ethical Hacker | تم الترقيع: Prepared Statements</div>
                </div>

                <!-- Report 3: XSS -->
                <div class="p-3 bg-dark rounded border border-secondary mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="badge bg-warning text-dark">High - 8.2</span>
                        <span class="badge bg-success font-monospace fw-bold fs-6">$7,500 Bounty</span>
                    </div>
                    <h6 class="text-info fw-bold mb-1">Shopify - Stored XSS leading to Account Takeover</h6>
                    <p class="text-muted small mb-2">
                        حقن وسم SVG خبيث داخل تعليقات المتجر، يتم تشغيله عند فتح التاجر لصفحة الطلبات وسرقة ملفات الكوكي.
                    </p>
                    <div class="small text-white-50"><i class="fas fa-user-ninja me-1 text-warning"></i> الباحث: Bug Hunter | تم الترقيع: Output Encoding & CSP</div>
                </div>

                <!-- Report 4: IDOR -->
                <div class="p-3 bg-dark rounded border border-secondary">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="badge bg-warning text-dark">High - 8.0</span>
                        <span class="badge bg-success font-monospace fw-bold fs-6">$5,000 Bounty</span>
                    </div>
                    <h6 class="text-info fw-bold mb-1">Twitter / X - IDOR in Direct Message Attachments</h6>
                    <p class="text-muted small mb-2">
                        تعديل رقم المعرف في الرابط <code>?msg_id=...</code> أتاح قراءة الرسائل والمرفقات الخاصة بأي مستخدم دون صلاحيات.
                    </p>
                    <div class="small text-white-50"><i class="fas fa-user-ninja me-1 text-warning"></i> الباحث: Whitehat | تم الترقيع: Server-Side Ownership Check</div>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- User's Submitted Reports Table -->
<?php if (!empty($_SESSION['bounty_reports'])): ?>
    <div class="card card-cyber mt-4 shadow">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <span class="text-white fw-bold fs-6">
                <i class="fas fa-clipboard-check text-success me-2"></i> سجل تقاريرك المقبولة ومكافآتك الصادرة
            </span>
            <span class="badge bg-success"><?= count($_SESSION['bounty_reports']) ?> تقارير مسجلة</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-dark-custom align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>رقم التقرير</th>
                            <th>عنوان الثغرة</th>
                            <th>التصنيف</th>
                            <th>الخطورة</th>
                            <th>المكافأة</th>
                            <th>التاريخ</th>
                            <th>الحالة</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (array_reverse($_SESSION['bounty_reports']) as $rep): ?>
                            <tr>
                                <td class="font-monospace text-warning fw-bold"><?= $rep['id'] ?></td>
                                <td class="text-white fw-semibold"><?= htmlspecialchars($rep['title']) ?></td>
                                <td><span class="badge bg-dark border border-secondary"><?= htmlspecialchars($rep['type']) ?></span></td>
                                <td><span class="badge bg-<?= $rep['severity'] === 'critical' ? 'danger' : ($rep['severity'] === 'high' ? 'warning text-dark' : 'info text-dark') ?>"><?= strtoupper($rep['severity']) ?></span></td>
                                <td class="text-success font-monospace fw-bold fs-6">$<?= number_format($rep['bounty']) ?></td>
                                <td class="small text-muted"><?= $rep['date'] ?></td>
                                <td><span class="badge bg-success"><i class="fas fa-check me-1"></i> تم الصرف</span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
