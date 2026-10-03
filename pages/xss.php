<?php
$page_title = "ثغرات السكربتات عبر المواقع (XSS) | مختبر الجوكر الأمني";
require_once __DIR__ . '/../includes/header.php';

$db = get_db();
$sec = get_security_level();

// معالجة التعليقات لـ Stored XSS
$comment_msg = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_comment') {
    $author = trim($_POST['author'] ?? 'مجهول');
    $comment = trim($_POST['comment'] ?? '');

    if (!empty($comment)) {
        $stmt = $db->prepare("INSERT INTO comments (author, comment) VALUES (?, ?)");
        $stmt->execute([$author, $comment]);
        $comment_msg = "<div class='alert alert-success py-2'>تمت إضافة تعليقك بنجاح!</div>";

        if (stripos($comment, '<script') !== false || stripos($comment, 'onerror=') !== false || stripos($comment, 'onload=') !== false) {
            award_flag('xss_stored');
        }
    }
}

// قراءة التعليقات
$comments = $db->query("SELECT * FROM comments ORDER BY id DESC LIMIT 10")->fetchAll();

// فحص Reflected XSS
$search_xss = $_GET['q'] ?? '';
if (!empty($search_xss)) {
    if (stripos($search_xss, '<script') !== false || stripos($search_xss, 'onerror=') !== false || stripos($search_xss, 'onload=') !== false) {
        award_flag('xss_reflected');
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom border-secondary">
    <div>
        <h2 class="fw-bold text-white mb-1"><i class="fas fa-code text-info me-2"></i> 2. ثغرات السكربتات عبر المواقع (Cross-Site Scripting - XSS)</h2>
        <p class="text-muted mb-0">تتيح ثغرة XSS للمهاجم حقن وتشغيل أكواد JavaScript خبيثة في متصفح الضحايا، مما يؤدي لسرقة ملفات تعريف الارتباط (Cookies) وتجاوز الصلاحيات.</p>
    </div>
    <span class="badge bg-info text-dark fs-6 px-3 py-2"><i class="fas fa-bug me-1"></i> الأنواع: المنعكس، المخزن، وDOM</span>
</div>

<!-- ======================= 1. Reflected XSS ======================= -->
<div class="card card-cyber mb-5">
    <div class="card-cyber-header d-flex justify-content-between align-items-center">
        <h4 class="mb-0 text-info"><i class="fas fa-reply me-2"></i> 1. ثغرة XSS المنعكس (Reflected XSS)</h4>
        <span class="badge bg-secondary">تحدي #1</span>
    </div>
    <div class="card-body p-4">
        <p class="text-light">
            <strong>السيناريو:</strong> شريط بحث يعيد طباعة الكلمة التي يبحث عنها المستخدم في الصفحة مباشرة دون معالجة أو ترميز للحروف الخاصة.
        </p>

        <?= render_flag_box('xss_reflected'); ?>

        <form method="GET" class="row g-2 mb-3" style="max-width: 600px;">
            <div class="col-8">
                <input type="text" name="q" class="form-control" placeholder="اكتب عبارة للبحث أو بايلود XSS..." value="<?= htmlspecialchars($search_xss) ?>">
            </div>
            <div class="col-4">
                <button type="submit" class="btn btn-info fw-bold w-100"><i class="fas fa-search me-1"></i> اختبار البحث</button>
            </div>
        </form>

        <?php if (!empty($search_xss)): ?>
            <div class="alert alert-dark border-info p-3">
                <span class="text-muted">نتائج البحث عن: </span>
                <span class="fw-bold fs-5">
                    <?php if ($sec === 'low'): ?>
                        <!-- كود مصاب: طباعة مباشرة دون ترميز -->
                        <?= $search_xss ?>
                    <?php else: ?>
                        <!-- كود آمن: استخدام htmlspecialchars -->
                        <?= htmlspecialchars($search_xss, ENT_QUOTES, 'UTF-8') ?>
                    <?php endif; ?>
                </span>
            </div>
        <?php endif; ?>

        <?= render_hints([
            "جرب إرسال بايلود بسيط مثل: <code>&lt;script&gt;alert('Reflected XSS')&lt;/script&gt;</code>",
            "أو استخدم وسم صورة مع حدث خطأ: <code>&lt;img src=x onerror=alert(1)&gt;</code>"
        ]); ?>

        <?= render_code_comparison(
            '// كود مصاب\necho "نتائج البحث عن: " . $_GET["q"];',
            '// كود آمن\necho "نتائج البحث عن: " . htmlspecialchars($_GET["q"], ENT_QUOTES, "UTF-8");',
            'تضمن دالة htmlspecialchars تحويل الحروف الخاصة مثل < و > و " إلى كيانات HTML آمنة (&lt; &gt; &quot;) فيعاملها المتصفح كنص عادي وليس كود برمجي تنفيذي.'
        ); ?>
    </div>
</div>

<!-- ======================= 2. Stored XSS ======================= -->
<div class="card card-cyber mb-5">
    <div class="card-cyber-header d-flex justify-content-between align-items-center">
        <h4 class="mb-0 text-warning"><i class="fas fa-save me-2"></i> 2. ثغرة XSS المخزن (Stored / Persistent XSS)</h4>
        <span class="badge bg-secondary">تحدي #2</span>
    </div>
    <div class="card-body p-4">
        <p class="text-light">
            <strong>السيناريو:</strong> كتاب زوار / قسم تعليقات يقوم بحفظ نصوص المستخدمين في قاعدة البيانات ثم عرضها لكل من يزور الصفحة، مما يجعل الهجوم مستمراً ودائماً.
        </p>

        <?= render_flag_box('xss_stored'); ?>
        <?= $comment_msg; ?>

        <div class="row">
            <div class="col-md-5 mb-4">
                <form method="POST" class="p-3 bg-dark rounded border border-secondary">
                    <input type="hidden" name="action" value="add_comment">
                    <div class="mb-3">
                        <label class="form-label text-light">الاسم المستعار:</label>
                        <input type="text" name="author" class="form-control" placeholder="اسمك..." required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-light">التعليق:</label>
                        <textarea name="comment" class="form-control" rows="3" placeholder="اكتب تعليقك أو بايلود XSS هنا..." required></textarea>
                    </div>
                    <button type="submit" class="btn btn-warning fw-bold"><i class="fas fa-paper-plane me-1"></i> نشر التعليق</button>
                </form>
            </div>
            <div class="col-md-7">
                <h5 class="text-light mb-3"><i class="fas fa-comments text-info me-2"></i> التعليقات المنشورة مؤخراً:</h5>
                <div class="list-group">
                    <?php if (empty($comments)): ?>
                        <div class="alert alert-secondary">لا توجد تعليقات حتى الآن.</div>
                    <?php endif; ?>
                    <?php foreach ($comments as $c): ?>
                        <div class="list-group-item list-group-item-dark border-secondary mb-2 rounded">
                            <div class="d-flex justify-content-between">
                                <strong class="text-info"><?= htmlspecialchars($c['author']) ?></strong>
                                <small class="text-muted"><?= htmlspecialchars($c['created_at']) ?></small>
                            </div>
                            <div class="mt-2 text-white">
                                <?php if ($sec === 'low'): ?>
                                    <!-- كود مصاب: طباعة التعليق مباشرة دون فلترة -->
                                    <?= $c['comment'] ?>
                                <?php else: ?>
                                    <!-- كود آمن: حماية ضد XSS -->
                                    <?= htmlspecialchars($c['comment'], ENT_QUOTES, 'UTF-8') ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <?= render_hints([
            "اكتب تعليقاً يحتوي على: <code>&lt;script&gt;alert(document.domain)&lt;/script&gt;</code>",
            "لاحظ أنه بمجرد تحديث الصفحة أو دخول أي زائر آخر، سيظهر التنبيه تلقائياً لأن الكود تم حفظه في قاعدة البيانات."
        ]); ?>

        <?= render_code_comparison(
            '// كود مصاب عند العرض\nforeach ($comments as $c) {\n    echo "<p>" . $c["comment"] . "</p>";\n}',
            '// كود آمن عند العرض\nforeach ($comments as $c) {\n    echo "<p>" . htmlspecialchars($c["comment"], ENT_QUOTES, "UTF-8") . "</p>";\n}',
            'أفضل ممارسة للحماية من Stored XSS هي تطبيق Context-Aware Output Encoding عند عرض البيانات المخزنة من قاعدة البيانات إلى المتصفح.'
        ); ?>
    </div>
</div>

<!-- ======================= 3. DOM-based XSS ======================= -->
<div class="card card-cyber">
    <div class="card-cyber-header d-flex justify-content-between align-items-center">
        <h4 class="mb-0 text-success"><i class="fas fa-laptop-code me-2"></i> 3. ثغرة XSS المعتمد على الـ DOM (DOM-based XSS)</h4>
        <span class="badge bg-secondary">تحدي #3</span>
    </div>
    <div class="card-body p-4">
        <p class="text-light">
            <strong>السيناريو:</strong> يتم استخراج بيانات من رابط الصفحة (`location.hash` أو المعاملات) وكتابتها مباشرة إلى كائنات الـ DOM في الصفحة عبر `innerHTML` دون أن تمر بالسيرفر.
        </p>

        <?= render_flag_box('xss_dom'); ?>

        <div class="p-3 bg-dark rounded border border-secondary mb-3" style="max-width: 600px;">
            <label class="form-label text-light">الاسم الترحيبي (يتم قراءته من الرابط عبر جافاسكربت):</label>
            <div class="input-group">
                <input type="text" id="dom-input" class="form-control" placeholder="أدخل اسمك أو بايلود DOM...">
                <button type="button" id="dom-btn" class="btn btn-success fw-bold">تحديث الرابط والترحيب</button>
            </div>
        </div>

        <div class="alert alert-dark border-success p-3">
            <h5 class="alert-heading text-success mb-1">منطقة الترحيب:</h5>
            <div id="welcome-box" class="fs-5 text-light">أهلاً بك يا زائرنا الكريم!</div>
        </div>

        <script>
            // معالجة DOM في متصفح العميل
            function updateGreeting() {
                var hash = window.location.hash.substring(1);
                var params = new URLSearchParams(hash);
                var name = params.get('name');

                if (name) {
                    var box = document.getElementById('welcome-box');
                    var sec = "<?= $sec ?>";

                    if (sec === 'low') {
                        // كود مصاب: حقن عبر innerHTML
                        box.innerHTML = "مرحباً بك: " + name;
                    } else {
                        // كود آمن: استخدام textContent
                        box.textContent = "مرحباً بك: " + name;
                    }

                    if (name.includes('<img') || name.includes('<script') || name.includes('onerror=')) {
                        fetch('submit_flag.php', {
                            method: 'POST',
                            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                            body: 'flag_input=FLAG{XSS_DOM_Execution_Caught_1928}'
                        });
                    }
                }
            }

            document.getElementById('dom-btn').addEventListener('click', function() {
                var val = document.getElementById('dom-input').value;
                window.location.hash = 'name=' + encodeURIComponent(val);
                updateGreeting();
            });

            window.addEventListener('load', updateGreeting);
        </script>

        <?= render_hints([
            "اضغط على زر التحديث بعد وضع بايلود مثل: <code>&lt;img src=1 onerror=alert('DOM_XSS')&gt;</code>",
            "انظر إلى شريط عنوان المتصفح، ستلاحظ أن الرابط أصبح ينتهي بـ <code>#name=...</code> وجافاسكربت ينفذه مباشرة."
        ]); ?>

        <?= render_code_comparison(
            '// كود جافاسكربت مصاب\ndocument.getElementById("welcome").innerHTML = "مرحباً: " + userName;',
            '// كود جافاسكربت آمن\ndocument.getElementById("welcome").textContent = "مرحباً: " + userName;\n// أو استخدام document.createTextNode()',
            'في جانب العميل (Client-Side)، تجنب استخدام innerHTML أو document.write مع مدخلات المستخدم واستبدلها بـ textContent أو innerText.'
        ); ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
