<?php
// ==============================================================================
// مشروع: مختبر الجوكر الفلسطيني لاختبار اختراق تطبيقات الويب (Joker Security Lab)
// التحدي 16: حقن محركات القوالب من جانب السيرفر (Server-Side Template Injection - SSTI)
// إعداد وتطوير: المهندس احمد سليم 🇵🇸
// ==============================================================================

$page_title = "حقن محركات القوالب (SSTI) | مختبر الجوكر الفلسطيني";
require_once __DIR__ . '/../includes/header.php';

$sec = get_security_level();
$rendered_output = "";
$error_msg = "";
$success_msg = "";
$ssti_triggered = false;

$default_template = "أهلاً بك يا {{name}} في منصة التدريب!\nنتمنى لك رحلة موفقة في تعلم حماية تطبيقات الويب.";
$sample_math = "اختبار تعبير القالب الحسابي:\nنتيجة العملية الحسابية = {{7*7}}";
$sample_rce = "استدعاء واستخراج أوامر السيرفر عبر القالب:\nالمستخدم: {{system('whoami')}}\nنسخة البيئة: {{phpversion()}}";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['template_content'])) {
    $template_input = trim($_POST['template_content']);
    $user_name = trim($_POST['user_name'] ?? 'زائر');

    if (empty($template_input)) {
        $error_msg = "يرجى كتابة نص القالب المطلوب تصييره (Render).";
    } else {
        if ($sec === 'low') {
            // كود مصاب: دمج وتفسير تعبيرات القوالب بشكل مباشر دون حجر صحي (Sandboxing)
            $template_processed = str_replace('{{name}}', htmlspecialchars($user_name), $template_input);

            // محاكي محرك القوالب (Template Engine Parser)
            $rendered_output = preg_replace_callback('/\{\{(.*?)\}\}/s', function($matches) use (&$ssti_triggered) {
                $expr = trim($matches[1]);

                // فحص العمليات الحسابية الشائعة في اكتشاف SSTI مثل 7*7
                if (preg_match('/^[0-9\+\-\*\/\s\(\)]+$/', $expr)) {
                    $ssti_triggered = true;
                    try {
                        // محاكاة تقييم محرك القوالب (مثل Jinja2 / Twig / Blade)
                        $calc_result = @eval("return ($expr);");
                        return "<span class='badge bg-warning text-dark font-monospace'>[SSTI Executed: $calc_result]</span>";
                    } catch (Throwable $t) {
                        return "[Eval Error]";
                    }
                }

                // فحص استدعاء دوال النظام أو قراءة متغيرات السيرفر
                if (preg_match('/(system|exec|passthru|shell_exec|phpversion|phpinfo|flag|whoami|id)/i', $expr)) {
                    $ssti_triggered = true;
                    if (stripos($expr, 'whoami') !== false || stripos($expr, 'id') !== false) {
                        $whoami = PHP_OS_FAMILY === 'Windows' ? get_current_user() : 'www-data';
                        return "<span class='badge bg-danger text-white font-monospace'>[RCE Output: $whoami (priv: server)]</span>";
                    }
                    if (stripos($expr, 'phpversion') !== false) {
                        return "<span class='badge bg-info text-dark font-monospace'>[PHP Version: " . phpversion() . "]</span>";
                    }
                    if (stripos($expr, 'flag') !== false) {
                        return "<span class='badge bg-success text-white font-monospace'>[FLAG Triggered]</span>";
                    }
                    return "<span class='badge bg-danger text-white font-monospace'>[SSTI Injected: Function Call $expr]</span>";
                }

                return "[Unknown Var: " . htmlspecialchars($expr) . "]";
            }, $template_processed);

            $success_msg = "تم تصيير وتوليد القالب بنجاح!";

            // فحص نجاح استغلال SSTI
            if ($ssti_triggered) {
                award_flag('ssti');
            }
        } else {
            // كود محمي: التعامل مع المدخلات كبيانات بحتة والهروب الصارم لوسوم القوالب
            // عدم تقييم التعبيرات برمجياً وحظر أقواس القوالب {{ }}
            $safe_template = htmlspecialchars($template_input, ENT_QUOTES, 'UTF-8');
            $safe_name = htmlspecialchars($user_name, ENT_QUOTES, 'UTF-8');

            // الاستبدال الآمن فقط للمتغيرات المصرح بها (Whitelisted Placeholders)
            $rendered_output = str_replace('{{name}}', $safe_name, $safe_template);
            
            if (preg_match('/\{\{(.*?)\}\}/', $template_input)) {
                $error_msg = "⚠️ [محرك القوالب الآمن] تم تعطيل تنفيذ الشيفرات البرمجية داخل وسوم القوالب. تُعامل المدخلات كبيانات نصية بحتة.";
            }
            $success_msg = "تم التصيير بأمان تام عبر محرك القوالب المحمي (Strict Sandboxing).";
        }
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom border-secondary">
    <div>
        <h2 class="fw-bold text-white mb-1">
            <i class="fas fa-cubes text-warning me-2"></i> 16. حقن محركات القوالب من جانب السيرفر (Server-Side Template Injection - SSTI)
        </h2>
        <p class="text-muted mb-0">
            تحدث ثغرة SSTI عندما يدمج تطبيق الويب مدخلات المستخدم مباشرة داخل شيفرة القالب (Template Syntax) بدلاً من تمريرها كبيانات للمتغيرات، مما يقود إلى تنفيذ الأوامر عن بُعد (RCE).
        </p>
    </div>
    <span class="badge bg-danger fs-6 px-3 py-2">
        <i class="fas fa-terminal me-1"></i> تنفيذ أوامر النظام عن بُعد (Remote Code Execution)
    </span>
</div>

<!-- Architecture Flow Diagram Card -->
<div class="card card-cyber mb-4">
    <div class="card-cyber-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0 text-info"><i class="fas fa-project-diagram me-2"></i> مسار تدفق هجوم حقن القوالب (SSTI to RCE Workflow)</h5>
        <span class="badge bg-dark border border-info text-info">SSTI Exploit Architecture</span>
    </div>
    <div class="card-body p-4 text-center">
        <div class="row align-items-center g-3">
            <div class="col-md-3">
                <div class="p-3 rounded bg-black border border-warning text-center h-100">
                    <i class="fas fa-keyboard text-warning fs-1 mb-2"></i>
                    <h6 class="text-warning fw-bold mb-1">1. إرسال تعبير القالب</h6>
                    <small class="text-light text-opacity-75">المهاجم يرسل حمولة تعبيرية مثل <code>{{7*7}}</code> أو دوال النظام <code>{{system(...)}}</code></small>
                </div>
            </div>
            <div class="col-md-1 d-flex justify-content-center">
                <i class="fas fa-arrow-left text-info fs-3 d-none d-md-block"></i>
                <i class="fas fa-arrow-down text-info fs-3 d-md-none"></i>
            </div>
            <div class="col-md-4">
                <div class="p-3 rounded bg-black border border-danger text-center h-100">
                    <i class="fas fa-cogs text-danger fs-1 mb-2"></i>
                    <h6 class="text-danger fw-bold mb-1">2. تقييم المحرك (Engine Eval)</h6>
                    <small class="text-light text-opacity-75">محرك القوالب (Twig/Jinja2/Blade) يقوم بتنفيذ الشيفرة داخل السيرفر بدلاً من طباعتها كنص عادي!</small>
                </div>
            </div>
            <div class="col-md-1 d-flex justify-content-center">
                <i class="fas fa-arrow-left text-success fs-3 d-none d-md-block"></i>
                <i class="fas fa-arrow-down text-success fs-3 d-md-none"></i>
            </div>
            <div class="col-md-3">
                <div class="p-3 rounded bg-black border border-success text-center h-100">
                    <i class="fas fa-skull text-success fs-1 mb-2"></i>
                    <h6 class="text-success fw-bold mb-1">3. تنفيذ الأوامر (RCE)</h6>
                    <small class="text-light text-opacity-75">ظهور نتيجة الحساب <code>49</code> أو مخرجات أوامر الخادم الحساسة واستخراج راية العلم!</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Interactive Challenge Card -->
<div class="card card-cyber mb-4">
    <div class="card-cyber-header d-flex justify-content-between align-items-center">
        <h4 class="mb-0 text-info"><i class="fas fa-envelope-open-text me-2"></i> محرر وتخصيص قوالب الرسائل (Greeting Card Customizer)</h4>
        <span class="badge bg-secondary">تحدي SSTI</span>
    </div>
    <div class="card-body p-4">
        <p class="text-light">
            <strong>الهدف:</strong> قم باختبار محرك القوالب في المستوى الضعيف عبر حقن تعبيرات رياضية مثل <code>{{7*7}}</code> أو استدعاء دوال النظام لاقتناص علم التحدي.
        </p>

        <?= render_flag_box('ssti'); ?>

        <?php if (!empty($error_msg)): ?>
            <div class="alert alert-danger py-2 d-flex align-items-center mb-3">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <div><?= $error_msg ?></div>
            </div>
        <?php endif; ?>

        <?php if (!empty($success_msg)): ?>
            <div class="alert alert-success py-2 d-flex align-items-center mb-3">
                <i class="fas fa-check-circle me-2"></i>
                <div><?= $success_msg ?></div>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-lg-7">
                <form method="POST" class="p-3 rounded bg-dark border border-secondary shadow-sm">
                    <div class="mb-3">
                        <label class="form-label text-info fw-bold">اسم المستخدم (المتغير {{name}}):</label>
                        <input type="text" name="user_name" class="form-control bg-black text-white border-secondary" value="<?= htmlspecialchars($_POST['user_name'] ?? 'مهندس الأمن') ?>">
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="form-label text-warning fw-bold mb-0">
                            <i class="fas fa-code me-1"></i> نص قالب الرسالة (Template Syntax):
                        </label>
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-outline-info" onclick="loadTemplate('default')">قالب عادي</button>
                            <button type="button" class="btn btn-outline-warning" onclick="loadTemplate('math')">فحص {{7*7}}</button>
                            <button type="button" class="btn btn-outline-danger" onclick="loadTemplate('rce')">أوامر النظام RCE</button>
                        </div>
                    </div>

                    <textarea id="template_content" name="template_content" rows="7" class="form-control font-monospace bg-black text-light border-secondary mb-3" style="direction:ltr; text-align:left; font-size:0.9rem;" required><?= htmlspecialchars($_POST['template_content'] ?? $default_template) ?></textarea>

                    <button type="submit" class="btn btn-warning text-dark fw-bold px-4">
                        <i class="fas fa-magic me-1"></i> تصيير القالب (Render Template)
                    </button>
                </form>
            </div>

            <div class="col-lg-5 mt-4 mt-lg-0">
                <div class="card bg-dark border-secondary h-100">
                    <div class="card-header bg-black text-white d-flex justify-content-between align-items-center py-2">
                        <span class="small fw-bold"><i class="fas fa-desktop text-info me-1"></i> المعاينة المباشرة للقالب المُصيّر:</span>
                        <span class="badge bg-secondary"><?= $sec === 'low' ? 'وضع Low (مكشوف)' : 'وضع Secure (محمي)' ?></span>
                    </div>
                    <div class="card-body p-3">
                        <?php if (!empty($rendered_output)): ?>
                            <div class="p-3 rounded bg-black border <?= $ssti_triggered ? 'border-success' : 'border-secondary' ?>" style="min-height: 180px; white-space: pre-wrap; font-family: monospace;">
                                <?= $rendered_output ?>
                            </div>
                            <?php if ($ssti_triggered): ?>
                                <div class="mt-3 p-2 rounded bg-success bg-opacity-25 border border-success text-success small fw-bold text-center">
                                    <i class="fas fa-check-double me-1"></i> تم اكتشاف وتنفيذ حقن القالب SSTI بنجاح!
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="text-center text-muted py-5">
                                <i class="fas fa-window-maximize text-secondary fs-1 mb-2"></i>
                                <p class="mb-0">أدخل نص القالب واضغط على تصيير لمعاينة النتيجة هنا.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= render_hints([
    "أول خطوة لاكتشاف ثغرات SSTI هي حقن تعبير رياضي مثل <code>{{7*7}}</code> أو <code>\${7*7}</code> أو <code>&lt;%= 7*7 %&gt;</code>.",
    "إذا قام محرك القوالب بحساب القيمة وطباعة <code>49</code> بدلاً من النص الأصلي، فهذا دليل قاطع على وجود الثغرة.",
    "في البيئات الحقيقية مثل Jinja2 (Python) أو Twig (PHP)، يستخدم المهاجم كائنات البيئة (Objects) للوصول إلى دوال تنفيذ الأوامر مثل <code>system()</code> أو <code>subprocess.Popen</code>.",
    "اضغط على زر <strong>(فحص {{7*7}})</strong> أو <strong>(أوامر النظام RCE)</strong> لرؤية تقييم المحرك واستخراج راية العلم فوراً."
]); ?>

<?= render_code_comparison(
    '// ❌ كود مصاب: دمج مدخلات المستخدم داخل القالب مباشرة وتمريره للمحرك\n$template = "مرحباً " . $_POST["input"];\n$rendered = $engine->render($template); // تنفيذ ديناميكي للأكواد!',
    '// ✅ كود محمي: تمرير المدخلات كمتغيرات في سياق منفصل (Context Data)\n$template = "مرحباً {{ user_input }}";\n$rendered = $engine->render($template, [\n    "user_input" => htmlspecialchars($_POST["input"], ENT_QUOTES, "UTF-8")\n]);',
    'لمنع ثغرات SSTI، يجب عدم دمج أو تلصيق مدخلات المستخدم داخل نصوص وسلسلة القوالب إطلاقاً، بل تمريرها كبيانات مستقلة داخل مصفوفة المتغيرات (Context Data)، مع تفعيل وضع الحجر الصحي (Sandbox Mode) في محركات القوالب.'
); ?>

<script>
function loadTemplate(type) {
    const editor = document.getElementById('template_content');
    if (type === 'default') {
        editor.value = <?= json_encode($default_template) ?>;
    } else if (type === 'math') {
        editor.value = <?= json_encode($sample_math) ?>;
    } else if (type === 'rce') {
        editor.value = <?= json_encode($sample_rce) ?>;
    }
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
