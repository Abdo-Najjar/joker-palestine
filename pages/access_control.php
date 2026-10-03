<?php
// ==============================================================================
// 13. التلاعب بالصلاحيات وملفات الكوكي | مختبر الجوكر الفلسطيني
// إعداد: المهندس احمد سليم
// ==============================================================================

require_once __DIR__ . '/../config.php';
$sec = get_security_level();

// التأكد من وجود قيمة افتراضية للكوكي في المتصفح
if (!isset($_COOKIE['user_role'])) {
    setcookie('user_role', 'student', time() + 86400, '/');
    $_COOKIE['user_role'] = 'student';
}

// معالجة تغيير الرتبة (سواء بالأزرار السريعة أو الإدخال المخصص)
if (isset($_GET['set_role']) || isset($_POST['custom_cookie_role'])) {
    $new_role = trim($_GET['set_role'] ?? $_POST['custom_cookie_role'] ?? '');
    if (!empty($new_role)) {
        setcookie('user_role', $new_role, time() + 86400, '/');
        $_COOKIE['user_role'] = $new_role;
    }
    header("Location: access_control.php");
    exit;
}

// معالجة عمليات لوحة التحكم التجريبية للمدير
$admin_action_msg = null;
if (isset($_GET['admin_action'])) {
    $act = $_GET['admin_action'];
    if ($act === 'backup') {
        $admin_action_msg = "<div class='alert alert-success alert-dismissible fade show'><i class='fas fa-download me-2'></i> <strong>عملية إدارية ناجحة:</strong> تم إنشاء وتنزيل النسخة الاحتياطية لقاعدة البيانات السرية بنجاح! <button type='button' class='btn-close' data-bs-dismiss='alert'></button></div>";
    } elseif ($act === 'keys') {
        $admin_action_msg = "<div class='alert alert-info alert-dismissible fade show'><i class='fas fa-key me-2'></i> <strong>عملية إدارية ناجحة:</strong> تم توليد مفاتيح API رئيسية جديدة: <code>" . bin2hex(random_bytes(16)) . "</code> <button type='button' class='btn-close' data-bs-dismiss='alert'></button></div>";
    } elseif ($act === 'purge') {
        $admin_action_msg = "<div class='alert alert-warning alert-dismissible fade show'><i class='fas fa-broom me-2'></i> <strong>عملية إدارية ناجحة:</strong> تم تنظيف سجلات التدقيق والأمان للنظام بنجاح! <button type='button' class='btn-close' data-bs-dismiss='alert'></button></div>";
    }
}

$client_cookie_role = $_COOKIE['user_role'] ?? 'student';
$server_session_role = 'student'; // في الوضع الآمن الجلسة الحقيقية في السيرفر هي طالب فقط

$is_admin = false;
$tampering_blocked = false;

if ($sec === 'low') {
    // كود مصاب: السيرفر يثق ثقة عمياء في الكوكي المرسل من العميل!
    if ($client_cookie_role === 'admin') {
        $is_admin = true;
        award_flag('access_control');
    }
} else {
    // كود آمن: السيرفر يتجاهل تماماً كوكيز العميل ويعتمد حصرياً على جلسة السيرفر الموثوقة!
    if ($server_session_role === 'admin') {
        $is_admin = true;
    } else {
        $is_admin = false;
        // إذا كان المهاجم أرسل كوكي admin، نرصد محاولة التلاعب ونوضح له بالدليل سبب الرفض
        if ($client_cookie_role === 'admin') {
            $tampering_blocked = true;
        }
    }
}

$page_title = "التحكم في الصلاحيات والكوكي (Broken Access Control) | مختبر الجوكر الفلسطيني";
require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom border-secondary">
    <div>
        <h2 class="fw-bold text-white mb-2"><i class="fas fa-cookie-bite text-warning me-2"></i> 13. التلاعب بالصلاحيات وملفات الكوكي (Cookie Manipulation)</h2>
        <p class="challenge-title-desc mb-0">تحدث ثغرة Broken Access Control عند تخزين رتبة المستخدم أو صلاحياته في ملف تعريف ارتباط (Client-Side Cookie) غير موقّع ولا مشفر، مما يتيح التلاعب به وتصعيد الصلاحيات (Privilege Escalation).</p>
    </div>
    <span class="badge bg-warning text-dark fs-6 px-3 py-2 text-nowrap"><i class="fas fa-user-shield me-1"></i> تصعيد صلاحيات: Privilege Escalation</span>
</div>

<?= render_flag_box('access_control'); ?>
<?= $admin_action_msg ?? ''; ?>

<!-- بطاقة التحدي الرئيسية: لوحة الإدارة -->
<div class="card card-cyber mb-4">
    <div class="card-cyber-header d-flex justify-content-between align-items-center">
        <h4 class="mb-0 text-info"><i class="fas fa-sliders-h me-2"></i> لوحة التحكم والإدارة المحمية بالصلاحيات (Admin Control Area)</h4>
        <span class="badge <?= $is_admin ? 'bg-success' : 'bg-danger' ?> fs-6">
            <?= $is_admin ? 'صلاحيات كاملة: مسموح' : 'دخول مقيد: محظور' ?>
        </span>
    </div>
    <div class="card-body p-4">
        
        <?php if ($is_admin): ?>
            <!-- ===================== لوحة المدير التفاعلية عند نجاح الاختراق ===================== -->
            <div class="alert alert-success border-success bg-success bg-opacity-10 p-4 mb-4 rounded-3 shadow">
                <div class="d-flex align-items-center mb-2">
                    <span class="fs-1 text-warning me-3">👑</span>
                    <div>
                        <h4 class="text-success fw-bold mb-1">مرحباً بك في لوحة تحكم المدير العام (Master Admin Panel)!</h4>
                        <p class="text-light mb-0">تم تجاوز الفحص وتصعيد صلاحياتك بنجاح عبر التلاعب بملف الكوكي <code>user_role=admin</code>! السيرفر وثق في مدخلك ومنحك السيطرة الكاملة.</p>
                    </div>
                </div>
            </div>

            <!-- إحصائيات السيرفر للمدير -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="p-3 bg-dark rounded border border-secondary text-center">
                        <div class="text-muted small">المستخدمين المسجلين</div>
                        <div class="fs-4 fw-bold text-info"><i class="fas fa-users me-1"></i> 142 مستخدم</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-3 bg-dark rounded border border-secondary text-center">
                        <div class="text-muted small">رتبتك المفعلة</div>
                        <div class="fs-4 fw-bold text-success"><i class="fas fa-user-shield me-1"></i> Root / Admin</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-3 bg-dark rounded border border-secondary text-center">
                        <div class="text-muted small">حالة الاتصال</div>
                        <div class="fs-4 fw-bold text-warning"><i class="fas fa-satellite-dish me-1"></i> جلسة مفتوحة</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-3 bg-dark rounded border border-secondary text-center">
                        <div class="text-muted small">مستوى الأمان</div>
                        <div class="fs-4 fw-bold text-danger"><i class="fas fa-shield-virus me-1"></i> Low (مخترق)</div>
                    </div>
                </div>
            </div>

            <!-- جدول إدارة حسابات النظام -->
            <h5 class="text-white fw-bold mb-3"><i class="fas fa-user-cog text-info me-2"></i> إدارة حسابات النظام والمستخدمين:</h5>
            <div class="table-responsive mb-4">
                <table class="table table-bordered table-dark-custom align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>#ID</th>
                            <th>اسم الحساب</th>
                            <th>الرتبة</th>
                            <th>الحالة</th>
                            <th>العمليات الإدارية المتاحة لك</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1</td>
                            <td class="fw-bold text-white"><i class="fas fa-user-circle me-1 text-danger"></i> admin</td>
                            <td><span class="badge bg-danger">مدير عام (Admin)</span></td>
                            <td><span class="badge bg-success">نشط</span></td>
                            <td><button class="btn btn-outline-secondary btn-sm" disabled>الحساب الحالي</button></td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td class="fw-bold text-white"><i class="fas fa-user-circle me-1 text-info"></i> instructor_ahmed</td>
                            <td><span class="badge bg-primary">مدرب أمني</span></td>
                            <td><span class="badge bg-success">نشط</span></td>
                            <td>
                                <a href="?admin_action=keys" class="btn btn-outline-warning btn-sm"><i class="fas fa-key me-1"></i> تجديد المفتاح</a>
                            </td>
                        </tr>
                        <tr>
                            <td>3</td>
                            <td class="fw-bold text-white"><i class="fas fa-user-circle me-1 text-secondary"></i> student_victim</td>
                            <td><span class="badge bg-secondary">طالب متدرب</span></td>
                            <td><span class="badge bg-success">نشط</span></td>
                            <td>
                                <a href="?admin_action=purge" class="btn btn-outline-danger btn-sm"><i class="fas fa-ban me-1"></i> حظر الحساب</a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- العمليات الحساسة الخاصة بالمدير -->
            <div class="p-3 bg-dark rounded border border-warning">
                <h6 class="text-warning fw-bold mb-2"><i class="fas fa-radiation-alt me-1"></i> عمليات المدير العام الحساسة (Administrative Actions):</h6>
                <p class="text-light small mb-3">بصفتك مديراً عاماً مخترقاً للنظام، يمكنك تجربة تشغيل هذه المهام الحساسة بنقرة زر:</p>
                <div class="d-flex flex-wrap gap-2">
                    <a href="?admin_action=backup" class="btn btn-success fw-bold btn-sm">
                        <i class="fas fa-file-export me-1"></i> تصدير النسخة الاحتياطية للنظام (Backup DB)
                    </a>
                    <a href="?admin_action=keys" class="btn btn-info text-dark fw-bold btn-sm">
                        <i class="fas fa-key me-1"></i> توليد مفاتيح تشفير رئيسية جديدة
                    </a>
                    <a href="?admin_action=purge" class="btn btn-outline-danger btn-sm fw-bold">
                        <i class="fas fa-trash-alt me-1"></i> تنظيف سجلات التدقيق (Clear Audit Logs)
                    </a>
                </div>
            </div>

        <?php elseif ($tampering_blocked): ?>
            <!-- ===================== حالة الوضع المحمي عند رصد التلاعب ===================== -->
            <div class="alert alert-danger border-danger bg-danger bg-opacity-10 p-4 mb-4 rounded-3 shadow">
                <div class="d-flex align-items-center mb-2">
                    <span class="fs-1 text-danger me-3">🛡️</span>
                    <div>
                        <h4 class="text-danger fw-bold mb-1">تم رصد وإحباط محاولة التلاعب بالكوكي بنجاح (Cookie Tampering Blocked)!</h4>
                        <p class="text-light mb-0">المتصفح يرسل كوكي: <code class="text-warning">user_role = admin</code>، ولكن السيرفر في المستوى المحمي <strong>(Secure)</strong> يرفض تماماً اعتماد الصلاحيات من العميل، ويعتمد حصرياً على جلسة السيرفر المحمية <code class="text-info">$_SESSION['server_auth_role'] = student</code>.</p>
                    </div>
                </div>
                <hr class="border-danger opacity-25">
                <div class="small text-light">
                    <i class="fas fa-info-circle me-1 text-info"></i> <strong>الدرس الأمني:</strong> هجوم تصعيد الصلاحيات فشل لأن التطبيق لم يعد يثق بالبيانات القادمة من جهة العميل.
                </div>
            </div>

        <?php else: ?>
            <!-- ===================== حالة الدخول العادي لطالب ===================== -->
            <div class="alert alert-secondary bg-dark border-secondary p-4 mb-4 rounded-3 text-center">
                <div class="fs-1 text-secondary mb-2">🔒</div>
                <h4 class="text-warning fw-bold mb-2">منطقة مقيدة: يلزم صلاحية مدير عام (Admin Privilege Required)</h4>
                <p class="text-light mb-2">عذراً، رتبتك الحالية في ملف الكوكي هي: <strong class="text-info font-monospace"><?= htmlspecialchars($client_cookie_role) ?></strong>. هذه اللوحة مقفلة ولا يمكن فتحها إلا برتبة <code>admin</code>.</p>
                <div class="badge bg-danger p-2 fs-6">الوصول محظور (403 Forbidden)</div>
            </div>
        <?php endif; ?>

    </div>
</div>

<!-- وحدة فاحص ومعدل الكوكيز التفاعلية المباشرة -->
<div class="card card-cyber mb-4">
    <div class="card-header bg-dark text-warning fw-bold py-2 d-flex justify-content-between align-items-center">
        <span><i class="fas fa-cookie me-2 text-warning"></i> وحدة فاحص ومعدل الكوكيز المباشر (Live Cookie Inspector & Manipulator)</span>
        <span class="badge bg-black text-info border border-secondary font-monospace">user_role = <?= htmlspecialchars($client_cookie_role) ?></span>
    </div>
    <div class="card-body p-4">
        
        <div class="row g-4 align-items-center">
            <!-- الفحص الأمني للكوكي -->
            <div class="col-lg-6">
                <h6 class="text-white fw-bold mb-3"><i class="fas fa-search text-info me-1"></i> الفحص الأمني لملف تعريف الارتباط (Cookie Audit):</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered table-dark-custom small" style="direction:ltr; text-align:left;">
                        <tbody>
                            <tr>
                                <th class="text-light" style="width: 35%;">Cookie Name</th>
                                <td class="text-info font-monospace fw-bold">user_role</td>
                            </tr>
                            <tr>
                                <th class="text-light">Current Value</th>
                                <td>
                                    <span class="badge <?= $client_cookie_role === 'admin' ? 'bg-success' : 'bg-warning text-dark' ?> font-monospace fs-6">
                                        <?= htmlspecialchars($client_cookie_role) ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th class="text-light">HttpOnly Flag</th>
                                <td><span class="badge bg-danger">False ❌</span> (مكشوف لجافاسكربت وتعديلات المتصفح)</td>
                            </tr>
                            <tr>
                                <th class="text-light">Secure Flag</th>
                                <td><span class="badge bg-secondary">False</span> (يُرسل عبر اتصالات غير مشفرة)</td>
                            </tr>
                            <tr>
                                <th class="text-light">Server Check Mode</th>
                                <td>
                                    <?php if ($sec === 'low'): ?>
                                        <span class="badge bg-danger">Low: يثق بالكوكي مباشرة (مصاب)</span>
                                    <?php else: ?>
                                        <span class="badge bg-success text-dark fw-bold">Secure: يعتمد على Session السيرفر (محمي)</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- أزرار التلاعب السريع والإدخال المخصص -->
            <div class="col-lg-6">
                <div class="p-3 bg-dark rounded border border-secondary">
                    <h6 class="text-info fw-bold mb-2"><i class="fas fa-magic me-1"></i> أزرار التلاعب السريع بالكوكي:</h6>
                    <p class="text-muted small mb-3">يمكنك تعديل الكوكي بنقرة زر، أو تجربة كتابة أي رتبة مخصصة بنفسك:</p>
                    
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <a href="access_control.php?set_role=admin" class="btn btn-warning fw-bold btn-sm shadow">
                            <i class="fas fa-crown me-1 text-dark"></i> ترقية فورية إلى Admin (محاكاة الاختراق)
                        </a>
                        <a href="access_control.php?set_role=student" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-undo me-1"></i> إعادة التعيين إلى Student (الافتراضي)
                        </a>
                    </div>

                    <!-- نموذج إدخال رتبة مخصصة -->
                    <form method="POST" action="access_control.php" class="mt-2 pt-2 border-top border-secondary">
                        <label class="form-label text-light small fw-bold">أو جرب كتابة رتبة مخصصة بنفسك:</label>
                        <div class="input-group input-group-sm">
                            <input type="text" name="custom_cookie_role" dir="ltr" class="form-control font-monospace" placeholder="مثال: manager, root, superadmin..." required>
                            <button type="submit" class="btn btn-outline-info fw-bold">تطبيق على الكوكي ⚡</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- دليل التعديل اليدوي من F12 -->
        <div class="mt-4 p-3 bg-info bg-opacity-10 rounded border border-info">
            <h6 class="text-info fw-bold mb-2"><i class="fas fa-laptop-code me-2"></i> كيف تطبق هذا الهجوم يدوياً كالمخترقين المحترفين عبر المتصفح (F12)؟</h6>
            <div class="row small text-light g-3">
                <div class="col-md-3">
                    <div class="p-2 bg-dark rounded border border-secondary text-center h-100">
                        <div class="badge bg-primary mb-1">الخطوة 1</div>
                        <p class="mb-0">اضغط على زر <kbd>F12</kbd> في لوحة المفاتيح لفتح أدوات المطورين (DevTools).</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-2 bg-dark rounded border border-secondary text-center h-100">
                        <div class="badge bg-primary mb-1">الخطوة 2</div>
                        <p class="mb-0">انتقل لتبويب <strong>Application</strong> في كروم/إيدج أو <strong>Storage</strong> في فايرفوكس.</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-2 bg-dark rounded border border-secondary text-center h-100">
                        <div class="badge bg-primary mb-1">الخطوة 3</div>
                        <p class="mb-0">اضغط على <strong>Cookies</strong> ثم اختر <code>http://localhost:8000</code>.</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-2 bg-dark rounded border border-secondary text-center h-100">
                        <div class="badge bg-success mb-1">الخطوة 4</div>
                        <p class="mb-0">انقر مرتين على قيمة <code>user_role</code> وغيّرها إلى <code class="text-warning">admin</code> ثم حدّث الصفحة (<kbd>F5</kbd>)!</p>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<?= render_hints([
    "في المستوى الضعيف (Low): اضغط على زر <strong>(ترقية فورية إلى Admin)</strong> أو عدّل الكوكي بنفسك من المتصفح عبر <code>F12</code>.",
    "بمجرد أن تصبح قيمة الكوكي <code>admin</code> وتحدّث الصفحة، سيثق السيرفر فيك ويفتح لك لوحة تحكم المدير الكاملة مع العلم!",
    "جرب الآن تغيير المستوى بالأعلى إلى <strong>Secure (محمي)</strong> واضغط على زر الترقية مرة أخرى، ولاحظ كيف يتم صده فوراً وتوضيح رسالة الحماية لأن السيرفر يعتمد على الجلسة وليس الكوكي."
]); ?>

<?= render_code_comparison(
    '// كود مصاب في PHP: الاعتماد المباشر على الكوكي القادم من المتصفح\n$role = $_COOKIE["user_role"] ?? "student";\nif ($role === "admin") {\n    // ثغرة كارثية: أي زائر يعدل الكوكي يصبح مديراً عاماً!\n    render_admin_panel();\n    award_flag();\n}',
    '// كود آمن 100%: حفظ الصلاحيات في Session السيرفر مع خاصية HttpOnly\nif (!isset($_SESSION["auth_user"]) || $_SESSION["auth_user"]["role"] !== "admin") {\n    header("HTTP/1.1 403 Forbidden");\n    die("عذراً، الدخول مقيد للمدراء المسجلين فقط في جلسة السيرفر!");\n}\n// عند إنشاء الكوكي للجلسة، نفعّل HttpOnly و Secure لمنع تعديلها:\nsetcookie("session_id", $session_id, [\n    "httponly" => true,\n    "secure"   => true,\n    "samesite" => "Strict"\n]);',
    'القاعدة الذهبية لمنع ثغرات Broken Access Control: لا تثق أبداً في أي معرّف أو رتبة يرسلها العميل (Client-Side). جميع الصلاحيات والهويات يجب أن تُدار في جلسة السيرفر المحمية (Server-Side Session)، ويُمنح المتصفح فقط معرف جلسة عشوائي وغير قابل للتنبؤ مع تشغيل خاصية HttpOnly.'
); ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
