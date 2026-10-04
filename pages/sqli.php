<?php
$page_title = "حقن قواعد البيانات (SQL Injection) | مختبر الجوكر الفلسطيني";
require_once __DIR__ . '/../includes/header.php';

$db = get_db();
$sec = get_security_level();

// تحدي 1: تجاوز تسجيل الدخول (Auth Bypass)
$login_msg = "";
$login_user = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $last_login_query = "";
    if ($sec === 'low') {
        // كود مصاب: دمج المدخلات مباشرة داخل الاستعلام
        $query = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
        $last_login_query = $query;
        try {
            $stmt = $db->query($query);
            $login_user = $stmt->fetch();
        } catch (Exception $e) {
            $login_msg = "<div class='alert alert-danger shadow-sm'>
                <div class='fw-bold mb-1'><i class='fas fa-exclamation-triangle me-1'></i> خطأ في استعلام SQL (Syntax Error):</div>
                <code class='text-danger bg-dark px-2 py-1 rounded d-block mb-2' style='direction:ltr; text-align:left;'>" . htmlspecialchars($e->getMessage()) . "</code>
                <div class='small text-light mb-1'><strong>الاستعلام المنفذ الذي تسبب بالخطأ:</strong></div>
                <pre class='bg-dark text-warning p-2 rounded small mb-2' style='direction:ltr; text-align:left;'><code>" . htmlspecialchars($query) . "</code></pre>
                <div class='small text-info pt-2 border-top border-secondary'>
                    <i class='fas fa-info-circle me-1'></i> <strong>توضيح الخلل:</strong> علامات التنصيص المفردة (Quotes) غير متوازنة! لاحظ أين أغلقت علامة التنصيص <code>'</code> وما بعدها. جرب إغلاق علامة التنصيص بعد admin ثم وضع شرطتين <code>--</code> لتجاهل باقي الاستعلام.
                </div>
            </div>";
        }
    } else {
        // كود آمن: استخدام Prepared Statements
        $stmt = $db->prepare("SELECT * FROM users WHERE username = :u AND password = :p");
        $stmt->execute([':u' => $username, ':p' => md5($password)]);
        $login_user = $stmt->fetch();
    }

    if ($login_user) {
        $login_msg = "<div class='alert alert-success'><i class='fas fa-check-circle me-1'></i> تم تسجيل الدخول بنجاح بحساب: <strong>" . htmlspecialchars($login_user['username']) . "</strong> (الرتبة: " . htmlspecialchars($login_user['role']) . ")</div>";
        if ($login_user['role'] === 'admin') {
            award_flag('sqli_auth');
        }
    } else if (empty($login_msg)) {
        $login_msg = "<div class='alert alert-danger'><i class='fas fa-times-circle me-1'></i> بيانات الدخول غير صحيحة!</div>";
    }
}

// تحدي 2: استخراج البيانات عبر UNION (Search)
$search_results = [];
$search_msg = "";
$search_query_display = "";

if (isset($_GET['search'])) {
    $search = $_GET['search'];

    if ($sec === 'low') {
        // كود مصاب: دمج مدخل البحث مباشرة
        $raw_sql = "SELECT id, name, category, price, description FROM products WHERE name LIKE '%$search%'";
        $search_query_display = $raw_sql;
        try {
            $stmt = $db->query($raw_sql);
            $search_results = $stmt->fetchAll();
            // فحص إذا تم استخراج العلم
            foreach ($search_results as $r) {
                if (isset($r['description']) && strpos($r['description'], 'FLAG{') !== false) {
                    award_flag('sqli_union');
                }
            }
        } catch (Exception $e) {
            $search_msg = "<div class='alert alert-danger'>خطأ استعلام: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
    } else {
        // كود آمن: استخدام Parameterized Query
        $search_query_display = "SELECT id, name, category, price, description FROM products WHERE name LIKE :s";
        $stmt = $db->prepare($search_query_display);
        $stmt->execute([':s' => "%$search%"]);
        $search_results = $stmt->fetchAll();
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom border-secondary">
    <div>
        <h2 class="fw-bold text-white mb-1"><i class="fas fa-database text-warning me-2"></i> 1. حقن قواعد البيانات (SQL Injection)</h2>
        <p class="text-muted mb-0">تحدث ثغرة SQLi عندما يدمج المبرمج مدخلات المستخدم مباشرة داخل استعلام قاعدة البيانات دون تنقية أو استخدام Prepared Statements.</p>
    </div>
    <span class="badge bg-warning text-dark fs-6 px-3 py-2"><i class="fas fa-exclamation-triangle me-1"></i> ثغرة خطيرة جداً (High Risk)</span>
</div>

<!-- مخطط معماري توضيحي لمسار الهجوم -->
<?= render_diagram(
    'sqli_diagram.jpg',
    'مخطط توضيحي: مسار تدفق هجوم حقن قواعد البيانات (SQL Injection Flow)',
    'تحليل بصري لكيفية كسر سياق استعلامات قواعد البيانات، وتجاوز شاشات تسجيل الدخول واستخراج الجداول السرية عبر UNION.'
); ?>

<!-- ================================= تحدي 1 ================================= -->
<div class="card card-cyber mb-5">
    <div class="card-cyber-header d-flex justify-content-between align-items-center">
        <h4 class="mb-0 text-info"><i class="fas fa-key me-2"></i> التحدي الأول: تجاوز شاشة تسجيل الدخول (Authentication Bypass)</h4>
        <span class="badge bg-secondary">تحدي رقم #1</span>
    </div>
    <div class="card-body p-4">
        <p class="text-light">
            <strong>الهدف:</strong> تسجيل الدخول بحساب المدير (admin) دون معرفة كلمة المرور الخاصة به عبر حقن منطقي في استعلام الـ SQL.
        </p>

        <?= render_flag_box('sqli_auth'); ?>
        <?= $login_msg; ?>

        <form method="POST" class="row g-3 p-3 rounded bg-dark border border-secondary" style="max-width: 600px;">
            <input type="hidden" name="action" value="login">
            <div class="col-12">
                <label class="form-label text-light">اسم المستخدم (Username):</label>
                <input type="text" id="sqli-user-input" name="username" class="form-control font-monospace" placeholder="أدخل اسم المستخدم أو مدخل الحقن..." value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
            </div>
            <div class="col-12">
                <label class="form-label text-light">كلمة المرور (Password):</label>
                <input type="password" name="password" class="form-control" placeholder="أي كلمة مرور عشوائية...">
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-warning fw-bold"><i class="fas fa-sign-in-alt me-1"></i> تسجيل الدخول</button>
            </div>
        </form>
    </div>
</div>

<?= render_hints([
    "تذكر أن استعلام الـ SQL هو: <code>SELECT * FROM users WHERE username = '...' AND password = '...'</code>",
    "يمكنك إغلاق علامة التنصيص الفردية <code>'</code> وإضافة شرط منطقي يكون صحيحاً دائماً مثل <code>OR '1'='1</code> ثم تعليق باقي الاستعلام بشرطتين <code>--</code>.",
    "جرب وضع التالي في اسم المستخدم: <code class='text-warning bg-black px-2 py-1'>admin' --</code> مع أي كلمة مرور عشوائية."
]); ?>

<?= render_code_comparison(
    '// كود مصاب في PHP (دمج مباشر)\n$user = $_POST["username"];\n$pass = $_POST["password"];\n$query = "SELECT * FROM users WHERE username = \'$user\' AND password = \'$pass\'";\n$res = $db->query($query);',
    '// كود آمن (Prepared Statements عبر PDO)\n$stmt = $db->prepare("SELECT * FROM users WHERE username = :u AND password = :p");\n$stmt->execute([":u" => $user, ":p" => md5($pass)]);\n$res = $stmt->fetch();',
    'في الكود المصاب، يتم تفسير مدخلات المستخدم كأوامر SQL، مما يسمح بتغيير منطق الاستعلام. الحل الجذري هو استخدام الاستعلامات المجهزة (Prepared Statements) حيث يتم فصل كود الـ SQL عن البيانات المرسلة تماماً.'
); ?>

<!-- ================================= تحدي 2 ================================= -->
<div class="card card-cyber">
    <div class="card-cyber-header d-flex justify-content-between align-items-center">
        <h4 class="mb-0 text-info"><i class="fas fa-search me-2"></i> التحدي الثاني: استخراج بيانات حساسة عبر (UNION-Based SQLi)</h4>
        <span class="badge bg-secondary">تحدي رقم #2</span>
    </div>
    <div class="card-body p-4">
        <p class="text-light">
            <strong>الهدف:</strong> استخدام ثغرة الحقن في صندوق البحث لاستخراج محتويات الأعمدة السرية أو جداول أخرى داخل قاعدة بيانات SQLite عبر أمر <code>UNION SELECT</code>.
        </p>

        <?= render_flag_box('sqli_union'); ?>
        <?= $search_msg; ?>

        <form method="GET" class="row g-2 mb-4" style="max-width: 650px;">
            <div class="col-8">
                <input type="text" name="search" class="form-control" placeholder="ابحث عن اسم منتج..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
            </div>
            <div class="col-4">
                <button type="submit" class="btn btn-info text-dark fw-bold w-100"><i class="fas fa-search me-1"></i> بحث</button>
            </div>
        </form>

        <?php if (!empty($search_query_display)): ?>
            <div class="alert alert-dark border-secondary small py-2 mb-3">
                <i class="fas fa-terminal me-1 text-info"></i> الاستعلام المنفذ خلف الكواليس: <code><?= htmlspecialchars($search_query_display) ?></code>
            </div>
        <?php endif; ?>

        <?php if (!empty($search_results)): ?>
            <div class="table-responsive">
                <table class="table table-bordered table-dark-custom align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>اسم المنتج</th>
                            <th>التصنيف</th>
                            <th>السعر</th>
                            <th>الوصف</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($search_results as $row): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['id'] ?? '') ?></td>
                                <td class="fw-bold text-info"><?= htmlspecialchars($row['name'] ?? '') ?></td>
                                <td><?= htmlspecialchars($row['category'] ?? '') ?></td>
                                <td>$<?= htmlspecialchars($row['price'] ?? '') ?></td>
                                <td><?= htmlspecialchars($row['description'] ?? '') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php elseif (isset($_GET['search'])): ?>
            <div class="alert alert-secondary">لم يتم العثور على أي نتائج مطابقة لبحثك.</div>
        <?php endif; ?>
    </div>
</div>

<?= render_hints([
    "استعلام البحث الأصلي يسترجع 5 أعمدة: <code>id, name, category, price, description</code> من جدول <code>products</code>.",
    "في قواعد بيانات SQLite، يمكنك دمج النتائج باستخدام <code>' UNION SELECT 1, 2, 3, 4, 5 --</code> لمعرفة الأعمدة التي تظهر في الصفحة.",
    "لاستخراج الحقل السري <code>secret_code</code>، يمكنك إرسال: <code class='text-warning bg-black px-2 py-1'>' UNION SELECT 1, 'سري', 'Flags', 0, secret_code FROM products --</code>"
]); ?>

<?= render_code_comparison(
    '// كود مصاب\n$search = $_GET["search"];\n$sql = "SELECT id, name, category, price, description FROM products WHERE name LIKE \'%$search%\'";\n$res = $db->query($sql);',
    '// كود آمن\n$stmt = $db->prepare("SELECT id, name, category, price, description FROM products WHERE name LIKE :s");\n$stmt->execute([":s" => "%$search%"]);\n$res = $stmt->fetchAll();',
    'باستخدام استعلام مجهز مع المتغيرات (Bind Parameters)، لن يتمكن المهاجم من كسر سياق النص وإلحاق أمر UNION إضافي.'
); ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
