<?php
$page_title = "اختبار الكفاءة والشهادة | مختبر الجوكر الفلسطيني";
require_once __DIR__ . '/../includes/header.php';

$score_info = get_score();

$questions = [
    1 => [
        'q' => 'ما هي الطريقة القياسية والأكثر أماناً لحماية تطبيقات الويب من ثغرة SQL Injection نهائياً؟',
        'options' => [
            'a' => 'استخدام دالة addslashes() لتنظيف المدخلات',
            'b' => 'استخدام الاستعلامات المجهزة المسبقة (Prepared Statements) والمعاملات المربوطة (Parameterized Queries)',
            'c' => 'فحص الكلمات المحجوزة مثل OR و AND وحذفها',
            'd' => 'تشفير كلمات المرور باستخدام MD5'
        ],
        'answer' => 'b',
        'explanation' => 'الاستعلامات المجهزة (Prepared Statements) تفصل كود الـ SQL عن بيانات المستخدم، مما يجعل الخادم يتعامل مع المدخلات كبيانات بحتة ولا ينفذها كأوامر أبداً.'
    ],
    2 => [
        'q' => 'كيف يمكن منع ثغرات Cross-Site Scripting (XSS) المنعكسة والمخزنة عند طباعة بيانات المستخدم في كود HTML؟',
        'options' => [
            'a' => 'ترميز المخرجات بالسياق المناسب باستخدام htmlspecialchars() مع ENT_QUOTES',
            'b' => 'حذف علامات التنصيص فقط',
            'c' => 'استخدام جدار ناري WAF فقط والاعتماد عليه كلياً',
            'd' => 'التحقق من طول النص المدخل فقط'
        ],
        'answer' => 'a',
        'explanation' => 'ترميز المخرجات (Context-Aware Output Encoding) مثل htmlspecialchars() يحول الرموز الخاصة مثل < و > إلى كيانات نصية (&lt; و &gt;) تمنع المتصفح من تشغيلها كأكواد JavaScript.'
    ],
    3 => [
        'q' => 'ما هو خط الدفاع الأساسي لحماية التطبيقات من هجمات تزوير الطلبات عبر المواقع (CSRF)؟',
        'options' => [
            'a' => 'استخدام طريقة GET في كافة النماذج',
            'b' => 'توليد واستخدام رمز سري عشوائي وغير متوقع لكل جلسة (Anti-CSRF Token) وتفعيل SameSite للكوكي',
            'c' => 'فحص كود الاستجابة HTTP',
            'd' => 'تشفير قاعدة البيانات'
        ],
        'answer' => 'b',
        'explanation' => 'رمز الـ CSRF السري يرتبط بجلسة المستخدم ويتم التحقق منه مع كل طلب يغير البيانات (POST/PUT/DELETE)، فلا يستطيع موقع المهاجم تخمينه أو سرقته.'
    ],
    4 => [
        'q' => 'في ثغرات حقن أوامر النظام (Command Injection)، ما هو الإجراء الدفاعي الأكثر أماناً؟',
        'options' => [
            'a' => 'تجنب دوال استدعاء النظام (مثل shell_exec و system) واستخدام دوال اللغة المدمجة، أو عزل المدخلات بـ escapeshellarg()',
            'b' => 'استبدال الفاصلة المنقوطة فقط',
            'c' => 'السماح للأدمن فقط بكتابة الأوامر دون قيود',
            'd' => 'تشغيل الخادم بصلاحيات root لتفادي الأخطاء'
        ],
        'answer' => 'a',
        'explanation' => 'الأفضل دائماً تجنب دوال النظام، وفي حال الضرورة القصوى يجب استخدام التحقق الصارم بالقائمة البيضاء ودوال الهروب الآمنة مثل escapeshellarg().'
    ],
    5 => [
        'q' => 'ما هو السبب الجذري لثغرة التحكم غير المباشر بالكائنات (IDOR)؟',
        'options' => [
            'a' => 'بطء استجابة السيرفر لقاعدة البيانات',
            'b' => 'غياب التحقق من صلاحية وصول المستخدم (Access Control Check) للكائن المطلوب بناءً على هويته المسجلة',
            'c' => 'استخدام أرقام المعرفات (IDs) في الرابط',
            'd' => 'عدم تفعيل شهادة SSL'
        ],
        'answer' => 'b',
        'explanation' => 'ثغرة IDOR تحدث عندما يعتمد السيرفر على المعرف القادم من المستخدم دون التحقق مما إذا كان هذا المستخدم يملك حق الوصول الفعلي لهذا السجل أم لا.'
    ],
    6 => [
        'q' => 'في ثغرة Broken Access Control، لماذا يعد الاعتماد على ملف الكوكي القادم من المتصفح (مثل user_role=admin) خطأ كارثياً؟',
        'options' => [
            'a' => 'لأن الكوكي ينتهي سريعاً',
            'b' => 'لأن الكوكي يقع تحت تحكم العميل بالكامل (Client-Controlled) ويمكن تعديله بسهولة من أدوات المطور (F12)',
            'c' => 'لأن الكوكي لا يدعم النصوص العربية',
            'd' => 'لأن المتصفح لا يرسل الكوكي مع الطلبات'
        ],
        'answer' => 'b',
        'explanation' => 'كل ما يرسله المتصفح قابل للتعديل والتحريف بواسطة المستخدم، ولذلك يجب تخزين الصلاحيات والرتب في الجلسة المحمية بالسيرفر (Session) وليس في كوكي مكشوف.'
    ],
    7 => [
        'q' => 'كيف يتم حماية السيرفر من ثغرة تضمين الملفات المحلية (Local File Inclusion - LFI)؟',
        'options' => [
            'a' => 'استخدام القائمة البيضاء (Whitelist) بالملفات المسموح طلبها فقط واستخدام basename() لمنع اجتياز المسارات (../)',
            'b' => 'إخفاء امتداد الملف فقط',
            'c' => 'تغيير اسم مجلد الصفحات كل يوم',
            'd' => 'السماح بقراءة ملفات .txt فقط'
        ],
        'answer' => 'a',
        'explanation' => 'القائمة البيضاء تضمن أن السيرفر لا يضمن إلا الملفات المحددة سلفاً، بينما تمنع basename() هجمات اجتياز المسارات (Path Traversal).'
    ]
];

$submitted = false;
$user_score = 0;
$total_q = count($questions);
$results = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit_quiz') {
    $submitted = true;
    foreach ($questions as $id => $q) {
        $user_ans = $_POST['q_' . $id] ?? '';
        $is_correct = ($user_ans === $q['answer']);
        if ($is_correct) {
            $user_score++;
        }
        $results[$id] = [
            'user' => $user_ans,
            'correct' => $q['answer'],
            'is_correct' => $is_correct,
            'explanation' => $q['explanation']
        ];
    }
    $percentage = round(($user_score / $total_q) * 100);
    $_SESSION['quiz_passed'] = ($percentage >= 70);
    $_SESSION['quiz_score'] = $percentage;
}

$quiz_passed = $_SESSION['quiz_passed'] ?? false;
$passed_percentage = $_SESSION['quiz_score'] ?? 0;
?>

<div class="row">
    <div class="col-lg-9 mx-auto">
        <!-- Header Banner -->
        <div class="card border-0 shadow-lg mb-4" style="background: linear-gradient(135deg, #151d30 0%, #0d1424 100%);">
            <div class="card-body p-4 text-center">
                <span class="fs-1 text-warning mb-2 d-inline-block">🎓</span>
                <h2 class="text-white fw-bold mb-1">اختبار الكفاءة والشهادة التقديرية</h2>
                <p class="text-info mb-2">مختبر الجوكر الفلسطيني لاختبار اختراق تطبيقات الويب (OWASP Top 10)</p>
                <div class="badge bg-black border border-info text-info p-2 px-3 fs-6 rounded-pill">
                    إعداد وتطوير: <strong class="text-warning">المهندس احمد سليم 🇵🇸</strong>
                </div>
            </div>
        </div>

        <?php if ($submitted): ?>
            <!-- Score Card -->
            <div class="card border-<?= $quiz_passed ? 'success' : 'danger' ?> shadow-lg mb-4 text-center p-4" style="background-color: var(--bg-card);">
                <div class="display-4 fw-bold text-<?= $quiz_passed ? 'success' : 'danger' ?> mb-2">
                    <?= $user_score ?> / <?= $total_q ?> (<?= $percentage ?>%)
                </div>
                <?php if ($quiz_passed): ?>
                    <h4 class="text-success fw-bold">🎉 تهانينا! لقد اجتزت اختبار الكفاءة الأمنية بنجاح!</h4>
                    <p class="text-light">لقد أثبتت فهمك العميق لآليات الثغرات وطرق ترقيعها وحمايتها عملياً ونظرياً.</p>
                    <div class="mt-3">
                        <a href="certificate.php" class="btn btn-warning btn-lg fw-bold px-4 py-2 shadow">
                            <i class="fas fa-award me-2"></i> استلام وتوليد شهادة الإتمام الرقمية 📜
                        </a>
                    </div>
                <?php else: ?>
                    <h4 class="text-danger fw-bold">تحتاج إلى 70% على الأقل لاجتياز الاختبار واستحقاق الشهادة.</h4>
                    <p class="text-light">راجع شروحات الأكواد والحلول بالأسفل ثم حاول مجدداً، فالتكرار يصنع المحترف!</p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Quiz Form -->
        <form method="POST" action="quiz.php">
            <input type="hidden" name="action" value="submit_quiz">

            <?php foreach ($questions as $id => $q): ?>
                <?php
                $res = $results[$id] ?? null;
                $border_class = "";
                if ($res) {
                    $border_class = $res['is_correct'] ? 'border-success' : 'border-danger';
                }
                ?>
                <div class="card my-3 shadow-sm <?= $border_class ?>" style="background-color: var(--bg-card); border-color: var(--border-color);">
                    <div class="card-header bg-dark text-white fw-bold d-flex justify-content-between align-items-center">
                        <span>
                            <span class="badge bg-warning text-dark me-2">سؤال <?= $id ?></span>
                            <?= htmlspecialchars($q['q']) ?>
                        </span>
                        <?php if ($res): ?>
                            <?php if ($res['is_correct']): ?>
                                <span class="badge bg-success"><i class="fas fa-check me-1"></i> إجابة صحيحة</span>
                            <?php else: ?>
                                <span class="badge bg-danger"><i class="fas fa-times me-1"></i> إجابة غير صحيحة</span>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <?php foreach ($q['options'] as $key => $option): ?>
                            <?php
                            $checked = (isset($_POST['q_' . $id]) && $_POST['q_' . $id] === $key) ? 'checked' : '';
                            $bg_style = "";
                            if ($res) {
                                if ($key === $q['answer']) {
                                    $bg_style = "background-color: rgba(16, 185, 129, 0.2); border: 1px solid #10b981;";
                                } elseif ($checked && !$res['is_correct']) {
                                    $bg_style = "background-color: rgba(239, 68, 68, 0.2); border: 1px solid #ef4444;";
                                }
                            }
                            ?>
                            <div class="form-check p-2 my-1 rounded" style="<?= $bg_style ?>">
                                <input class="form-check-input ms-2" type="radio" name="q_<?= $id ?>" id="q_<?= $id ?>_<?= $key ?>" value="<?= $key ?>" <?= $checked ?> required>
                                <label class="form-check-label text-light" for="q_<?= $id ?>_<?= $key ?>">
                                    <strong class="text-info"><?= strtoupper($key) ?>)</strong> <?= htmlspecialchars($option) ?>
                                </label>
                            </div>
                        <?php endforeach; ?>

                        <?php if ($res): ?>
                            <div class="alert alert-secondary py-2 px-3 mt-3 mb-0 small text-light bg-black bg-opacity-50 border-secondary">
                                <strong class="text-warning"><i class="fas fa-lightbulb me-1"></i> الشرح والتوضيح:</strong> <?= htmlspecialchars($res['explanation']) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>

            <div class="text-center my-4">
                <button type="submit" class="btn btn-primary btn-lg fw-bold px-5 py-3 shadow">
                    <i class="fas fa-check-double me-2"></i> تسليم الإجابات واعتماد النتيجة
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
