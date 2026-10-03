import os
import sys
import arabic_reshaper
from bidi.algorithm import get_display

from reportlab.lib import colors
from reportlab.lib.pagesizes import A4
from reportlab.lib.styles import getSampleStyleSheet, ParagraphStyle
from reportlab.platypus import (
    SimpleDocTemplate, Paragraph, Spacer, Table, TableStyle, PageBreak, HRFlowable
)
from reportlab.pdfgen import canvas
from reportlab.pdfbase import pdfmetrics
from reportlab.pdfbase.ttfonts import TTFont

# تسجيل الخطوط العربية من الويندوز
font_regular = r'C:\Windows\Fonts\arial.ttf'
font_bold = r'C:\Windows\Fonts\arialbd.ttf'

pdfmetrics.registerFont(TTFont('ArabicFont', font_regular))
pdfmetrics.registerFont(TTFont('ArabicFontBold', font_bold))

import html

def ar(text):
    """تهيئة النص العربي للعرض من اليمين لليسار مع حماية كيانات HTML"""
    if not text:
        return ""
    # حماية الرموز مثل < و > في البايلودات لتفادي خطأ paraparser
    safe_text = html.escape(str(text))
    lines = safe_text.split('\n')
    reshaped_lines = [get_display(arabic_reshaper.reshape(l)) for l in lines]
    return "<br/>".join(reshaped_lines)

class NumberedCanvas(canvas.Canvas):
    """Canvas مخصص لإضافة ترقيم الصفحات والترويسة وتذييل إعداد الجوكر الفلسطيني احمد سليم في كل الصفحات"""
    def __init__(self, *args, **kwargs):
        super().__init__(*args, **kwargs)
        self._saved_page_states = []

    def showPage(self):
        self._saved_page_states.append(dict(self.__dict__))
        self._startPage()

    def save(self):
        num_pages = len(self._saved_page_states)
        for state in self._saved_page_states:
            self.__dict__.update(state)
            self.draw_page_decorations(num_pages)
            super().showPage()
        super().save()

    def draw_page_decorations(self, page_count):
        self.saveState()
        w, h = A4

        # إذا كانت صفحة الغلاف (الصفحة الأولى)
        if self._pageNumber == 1:
            self.setStrokeColor(colors.HexColor('#0284c7'))
            self.setLineWidth(3)
            self.rect(20, 20, w - 40, h - 40)
            
            self.setStrokeColor(colors.HexColor('#0f172a'))
            self.setLineWidth(1)
            self.rect(25, 25, w - 50, h - 50)
            self.restoreState()
            return

        # الصفحات الداخلية: ترويسة علوية
        self.setStrokeColor(colors.HexColor('#cbd5e1'))
        self.setLineWidth(0.75)
        self.line(40, h - 40, w - 40, h - 40)

        self.setFont('ArabicFont', 9)
        self.setFillColor(colors.HexColor('#475569'))
        self.drawRightString(w - 40, h - 34, get_display(arabic_reshaper.reshape('مختبر الجوكر الأمني لاختبار اختراق تطبيقات الويب (Joker Security Lab)')))
        self.drawString(40, h - 34, 'OWASP Web Security Lab')

        # تذييل سفلي
        self.line(40, 45, w - 40, 45)
        self.setFont('ArabicFontBold', 10)
        self.setFillColor(colors.HexColor('#0369a1'))
        
        # المطلوب بالتحديد في كل صفحة: "إعداد: الجوكر الفلسطيني احمد سليم"
        footer_author = get_display(arabic_reshaper.reshape('إعداد: الجوكر الفلسطيني احمد سليم'))
        self.drawRightString(w - 40, 30, footer_author)

        # رقم الصفحة
        page_str = get_display(arabic_reshaper.reshape(f"صفحة {self._pageNumber} من {page_count}"))
        self.setFont('ArabicFont', 9)
        self.setFillColor(colors.HexColor('#64748b'))
        self.drawString(40, 30, page_str)

        self.restoreState()

def build_pdf():
    pdf_path = os.path.join(os.path.dirname(__file__), 'docs', 'joker_security_lab_manual.pdf')
    os.makedirs(os.path.dirname(pdf_path), exist_ok=True)

    doc = SimpleDocTemplate(
        pdf_path,
        pagesize=A4,
        rightMargin=40,
        leftMargin=40,
        topMargin=55,
        bottomMargin=55
    )

    styles = getSampleStyleSheet()

    title_cover = ParagraphStyle(
        'CoverTitle',
        fontName='ArabicFontBold',
        fontSize=24,
        leading=34,
        alignment=1, # Center
        textColor=colors.HexColor('#0f172a')
    )

    subtitle_cover = ParagraphStyle(
        'CoverSubtitle',
        fontName='ArabicFont',
        fontSize=12,
        leading=20,
        alignment=1,
        textColor=colors.HexColor('#334155')
    )

    author_cover = ParagraphStyle(
        'CoverAuthor',
        fontName='ArabicFontBold',
        fontSize=15,
        leading=24,
        alignment=1,
        textColor=colors.HexColor('#0284c7')
    )

    h1_style = ParagraphStyle(
        'Heading1_Ar',
        fontName='ArabicFontBold',
        fontSize=15,
        leading=22,
        alignment=2, # Right
        textColor=colors.HexColor('#0369a1'),
        spaceAfter=6,
        spaceBefore=12
    )

    h2_style = ParagraphStyle(
        'Heading2_Ar',
        fontName='ArabicFontBold',
        fontSize=11.5,
        leading=18,
        alignment=2,
        textColor=colors.HexColor('#0f172a'),
        spaceAfter=4,
        spaceBefore=6
    )

    body_style = ParagraphStyle(
        'Body_Ar',
        fontName='ArabicFont',
        fontSize=9.5,
        leading=15,
        alignment=2,
        textColor=colors.HexColor('#1e293b'),
        spaceAfter=4
    )

    code_style = ParagraphStyle(
        'Code_En',
        fontName='Courier',
        fontSize=8,
        leading=11,
        alignment=0, # Left
        textColor=colors.HexColor('#0f172a')
    )

    callout_style = ParagraphStyle(
        'Callout_Ar',
        fontName='ArabicFont',
        fontSize=9,
        leading=14,
        alignment=2,
        textColor=colors.HexColor('#0369a1')
    )

    story = []

    # =========================================================================
    # صفحة الغلاف (Cover Page)
    # =========================================================================
    story.append(Spacer(1, 50))
    story.append(Paragraph("[ JOKER WEB SECURITY LAB ]", ParagraphStyle('CoverBadge', fontName='Helvetica-Bold', fontSize=14, alignment=1, textColor=colors.HexColor('#0284c7'))))
    story.append(Spacer(1, 15))
    story.append(Paragraph(ar('مختبر الجوكر الأمني لتطبيقات الويب'), title_cover))
    story.append(Paragraph('(Joker Security Lab v2.0)', ParagraphStyle('EnSub', fontName='Helvetica-Bold', fontSize=14, alignment=1, textColor=colors.HexColor('#64748b'))))
    story.append(Spacer(1, 15))
    
    story.append(Paragraph(ar('الدليل الأكاديمي والتطبيقي الشامل لتدريب الطلاب على أشهر ثغرات الويب'), subtitle_cover))
    story.append(Paragraph(ar('شرح نظري متعمق، سيناريوهات استغلال عملية، مقارنات الكود المصاب والآمن، ونظام الأعلام (CTF Flags)'), subtitle_cover))
    
    story.append(Spacer(1, 40))
    
    author_box = [
        [Paragraph(ar('المشروع مخصص للأغراض التعليمية والأمن الأخلاقي'), ParagraphStyle('P1', fontName='ArabicFont', fontSize=10, alignment=1, textColor=colors.HexColor('#475569')))],
        [Spacer(1, 5)],
        [Paragraph(ar('إعداد وتطوير:'), ParagraphStyle('P2', fontName='ArabicFont', fontSize=11, alignment=1, textColor=colors.HexColor('#0f172a')))],
        [Paragraph(ar('الجوكر الفلسطيني احمد سليم'), author_cover)],
        [Spacer(1, 5)],
        [Paragraph(ar('بيئة عمل خفيفة مبنية بـ PHP و SQLite للتشغيل الفوري والنشر على الاستضافات'), ParagraphStyle('P3', fontName='ArabicFont', fontSize=9, alignment=1, textColor=colors.HexColor('#64748b')))]
    ]
    t_cover = Table(author_box, colWidths=[420])
    t_cover.setStyle(TableStyle([
        ('BACKGROUND', (0,0), (-1,-1), colors.HexColor('#f1f5f9')),
        ('BOX', (0,0), (-1,-1), 1.5, colors.HexColor('#cbd5e1')),
        ('ALIGN', (0,0), (-1,-1), 'CENTER'),
        ('TOPPADDING', (0,0), (-1,-1), 12),
        ('BOTTOMPADDING', (0,0), (-1,-1), 12),
    ]))
    story.append(t_cover)

    story.append(PageBreak())

    # =========================================================================
    # المقدمة وتجهيز البيئة
    # =========================================================================
    story.append(Paragraph(ar('مقدمة عن مختبر الجوكر الأمني'), h1_style))
    story.append(HRFlowable(width="100%", thickness=1.5, color=colors.HexColor('#0284c7'), spaceAfter=8))
    
    story.append(Paragraph(ar(
        'يعد مختبر الجوكر الأمني منصة تدريبية وتطبيقية متكاملة تهدف إلى تزويد طلاب أمن المعلومات بتجربة عملية واقعية لاختبار اختراق تطبيقات الويب وفهم أسباب حدوث الثغرات الأمنية وكيفية ترقيعها برمجياً.'
    ), body_style))
    story.append(Paragraph(ar(
        'تم بناء المنصة بلغة PHP وقاعدة بيانات SQLite 3 لتفادي أي تعقيدات في إعداد خوادم قواعد البيانات المنفصلة. يتيح ذلك تشغيل المختبر بنقرة واحدة محلياً أو رفعه مباشرة على أي استضافة ويب مشتركة (Shared Hosting).'
    ), body_style))

    story.append(Spacer(1, 6))
    story.append(Paragraph(ar('طريقة تشغيل وتثبيت المختبر:'), h2_style))
    
    setup_data = [
        [
            Paragraph(ar('1. التشغيل السريع محلياً (Local Development):\nافتح منفذ الأوامر داخل مجلد المشروع ونفذ الأمر:\nphp -S localhost:8000\nثم افتح المتصفح على الرابط: http://localhost:8000'), body_style)
        ],
        [
            Paragraph(ar('2. النشر على استضافة ويب (Web Hosting / cPanel):\nارفع مجلد المشروع كاملاً إلى مجلد public_html. سيعمل الموقع تلقائياً. تأكد من تفعيل صلاحيات الكتابة لمجلد uploads وملف قاعدة البيانات database.sqlite.'), body_style)
        ],
        [
            Paragraph(ar('3. تنبيه أمني هام للمدرب:\nالمشروع يحتوي عمداً على ثغرات تنفيذ أوامر حقيقية (RCE و Command Injection). إذا قمت بنشره على سيرفر عام متصل بالإنترنت، احرص على حمايته بكلمة مرور عبر Directory Privacy.'), callout_style)
        ]
    ]
    t_setup = Table(setup_data, colWidths=[510])
    t_setup.setStyle(TableStyle([
        ('BACKGROUND', (0,0), (-1,1), colors.HexColor('#f8fafc')),
        ('BACKGROUND', (0,2), (-1,2), colors.HexColor('#fef3c7')),
        ('BOX', (0,0), (-1,-1), 1, colors.HexColor('#e2e8f0')),
        ('INNERGRID', (0,0), (-1,-1), 0.5, colors.HexColor('#e2e8f0')),
        ('PADDING', (0,0), (-1,-1), 7),
    ]))
    story.append(t_setup)

    story.append(Spacer(1, 10))

    # =========================================================================
    # الثغرات بالتفصيل
    # =========================================================================
    
    vulns = [
        {
            'num': '1',
            'title': 'حقن قواعد البيانات (SQL Injection - SQLi)',
            'desc': 'تحدث ثغرة SQLi عند دمج مدخلات المستخدم مباشرة داخل استعلام SQL دون تعقيم أو استخدام استعلامات مجهزة (Prepared Statements). يتيح ذلك للمهاجم تعديل بنية الاستعلام وتنفيذ أوامر قاعدة بيانات غير مصرح بها.',
            'scenarios': [
                ('تجاوز شاشة الدخول (Auth Bypass)', "باستخدام البايلود الشهير: admin' -- في حقل اسم المستخدم، يتم إلغاء شرط فحص كلمة المرور وتسجيل الدخول كمدير."),
                ('استخراج البيانات عبر UNION', "استخدام ' UNION SELECT 1, 2, 3, 4, secret_code FROM products -- لاستخراج البيانات السرية والأعلام المخفية في جداول أخرى.")
            ],
            'vuln_code': '$user = $_POST["username"];\n$pass = $_POST["password"];\n$sql = "SELECT * FROM users WHERE username = \'$user\' AND password = \'$pass\'";\n$db->query($sql);',
            'secure_code': '$stmt = $db->prepare("SELECT * FROM users WHERE username = :u AND password = :p");\n$stmt->execute([":u" => $user, ":p" => md5($pass)]);\n$row = $stmt->fetch();',
            'flag': 'FLAG{SQLi_Auth_Bypass_Success_9281}'
        },
        {
            'num': '2',
            'title': 'السكربتات عبر المواقع (Cross-Site Scripting - XSS)',
            'desc': 'تسمح للمهاجم بحقن أكواد JavaScript خبيثة تُنفذ في متصفحات الضحايا، مما يتيح سرقة ملفات الجلسة (Session Cookies) أو تشويه الصفحات أو توجيه المستخدمين لمواقع مشبوهة.',
            'scenarios': [
                ('XSS المنعكس (Reflected)', 'يحدث عند طباعة نص البحث مباشرة في الصفحة: <script>alert(1)</script>'),
                ('XSS المخزن (Stored)', 'حفظ التعليقات في قاعدة البيانات وعرضها للزوار دون ترميز، مما يجعل الهجوم دائماً.'),
                ('XSS المعتمد على DOM', 'استخراج معاملات الرابط مثل #name=... عبر جافاسكربت وكتابتها عبر innerHTML.')
            ],
            'vuln_code': '// طباعة مباشرة دون ترميز:\necho "<p>نتائج البحث: " . $_GET["q"] . "</p>";',
            'secure_code': '// استخدام htmlspecialchars لتحويل الحروف الحساسة إلى كيانات:\necho "<p>نتائج البحث: " . htmlspecialchars($_GET["q"], ENT_QUOTES, "UTF-8") . "</p>";',
            'flag': 'FLAG{XSS_Reflected_Payload_Found_7719}'
        },
        {
            'num': '3',
            'title': 'تضمين الملفات واجتياز المسارات (LFI & Path Traversal)',
            'desc': 'استغلال دوال التضمين (include, require) لقراءة ملفات حساسة من السيرفر عبر مسارات نسبية مثل ../../../../etc/passwd أو استخدام بروتوكولات PHP مثل php://filter.',
            'scenarios': [
                ('اجتياز المسارات (Path Traversal)', 'طلب مسار نسبي: ?file=secret_note.txt أو ../../../win.ini'),
                ('مشغلات PHP المتقدمة', 'استخراج كود المصدر مشفراً: php://filter/convert.base64-encode/resource=sqli.php')
            ],
            'vuln_code': '$file = $_GET["file"];\ninclude($file);',
            'secure_code': '$allowed = ["about.txt", "contact.txt"];\n$file = basename($_GET["file"]);\nif (in_array($file, $allowed)) { include($file); } else { die("Access Denied"); }',
            'flag': 'FLAG{LFI_Local_File_Read_Exposed_8821}'
        },
        {
            'num': '4',
            'title': 'رفع الملفات غير الآمن (Unrestricted File Upload)',
            'desc': 'السماح برفع ملفات برمجية تنفيذية (.php) دون التحقق الصارم من امتداد الملف أو محتواه الحقيقي، مما يمنح المهاجم شيل تحكم كامل (Web Shell) يؤدي إلى Remote Code Execution.',
            'scenarios': [
                ('رفع شيل PHP', 'إنشاء ملف shell.php يحتوي على دالة تنفيذ الأوامر ورفعه مباشرة كصورة بروفايل.')
            ],
            'vuln_code': '$dest = "uploads/" . $_FILES["avatar"]["name"];\nmove_uploaded_file($_FILES["avatar"]["tmp_name"], $dest);',
            'secure_code': '$ext = strtolower(pathinfo($_FILES["avatar"]["name"], PATHINFO_EXTENSION));\nif (in_array($ext, ["jpg","png"])) {\n    $safe_name = bin2hex(random_bytes(10)) . "." . $ext;\n    move_uploaded_file($_FILES["avatar"]["tmp_name"], "uploads/" . $safe_name);\n}',
            'flag': 'FLAG{File_Upload_WebShell_RCE_5521}'
        },
        {
            'num': '5',
            'title': 'حقن أوامر نظام التشغيل (OS Command Injection)',
            'desc': 'استدعاء دوال النظام مثل shell_exec أو exec مع دمج مدخلات غير منقاة، مما يمكن المهاجم من دمج أوامر نظام إضافية باستخدام الرموز &, &&, |, ;.',
            'scenarios': [
                ('حقن أمر عبر أداة Ping', 'إدخال: 127.0.0.1 && whoami أو 127.0.0.1 & dir لتنفيذ أوامر السيرفر.')
            ],
            'vuln_code': '$ip = $_POST["ip"];\n$out = shell_exec("ping -c 1 " . $ip);',
            'secure_code': '$ip = $_POST["ip"];\nif (filter_var($ip, FILTER_VALIDATE_IP)) {\n    $safe = escapeshellarg($ip);\n    $out = shell_exec("ping -c 1 " . $safe);\n}',
            'flag': 'FLAG{Command_Injection_Pwned_9912}'
        },
        {
            'num': '6',
            'title': 'التحكم غير المباشر بالكائنات (IDOR / BOLA)',
            'desc': 'الاعتماد على معرف الكائن (msg_id أو user_id) المرسل من المتصفح في جلب بيانات حساسة دون التأكد من أن صاحب الجلسة الحالية يملك صلاحية الاطلاع على هذا السجل.',
            'scenarios': [
                ('قراءة رسائل الإدارة السرية', 'تغيير الرابط من ?msg_id=1 الخاص بالمستخدم إلى ?msg_id=2 لقراءة التقرير الأمني السري.')
            ],
            'vuln_code': '$id = $_GET["msg_id"];\n$msg = $db->query("SELECT * FROM messages WHERE id = $id")->fetch();',
            'secure_code': '$id = (int)$_GET["msg_id"];\n$uid = $_SESSION["user_id"];\n$stmt = $db->prepare("SELECT * FROM messages WHERE id = ? AND (receiver_id = ? OR sender_id = ?)");\n$stmt->execute([$id, $uid, $uid]);',
            'flag': 'FLAG{IDOR_Unauthorized_Access_6619}'
        },
        {
            'num': '7',
            'title': 'تزوير الطلبات عبر المواقع (CSRF)',
            'desc': 'إجبار متصفح الضحية المصادق عليه على إرسال طلب غير مرغوب فيه لتغيير بيانات حساسة (مثل البريد أو كلمة المرور) في غياب توكن الحماية Anti-CSRF Token.',
            'scenarios': [
                ('هجوم تغيير البريد الخفي', 'صفحة خارجية للمهاجم ترسل نموذجاً مخفياً يغير بريد المستخدم إلى hacker@evil.com.')
            ],
            'vuln_code': '$new_email = $_POST["email"];\n$db->query("UPDATE users SET email = \'$new_email\' WHERE id = $uid");',
            'secure_code': 'if (!hash_equals($_SESSION["csrf_token"], $_POST["csrf_token"])) { die("Invalid Token"); }\n// مع تفعيل SameSite=Strict لكوكيز الجلسة',
            'flag': 'FLAG{CSRF_Request_Forged_Successfully_4421}'
        },
        {
            'num': '8',
            'title': 'كشف البيانات وقاعدة بيانات SQLite (Data Exposure)',
            'desc': 'ترك ملف قاعدة البيانات database.sqlite في المسار العام للموقع دون منعه في إعدادات السيرفر، مما يتيح لأي زائر تحميل قاعدة البيانات كاملة والاطلاع على التجزئات المشفرة والبيانات السرية.',
            'scenarios': [
                ('تحميل قاعدة البيانات المباشر', 'طلب الرابط مباشرة عبر المتصفح: http://example.com/database.sqlite')
            ],
            'vuln_code': '# ملف قاعدة البيانات مخزن في المجلد العام دون حماية .htaccess',
            'secure_code': '# منع التحميل في ملف .htaccess:\n<FilesMatch "\\.(sqlite|db|env)$">\n    Require all denied\n</FilesMatch>',
            'flag': 'FLAG{Sensitive_Data_Exposed_Download_1192}'
        },
        {
            'num': '9',
            'title': 'التوجيه المفتوح (Open Redirect)',
            'desc': 'توجيه الزائر إلى رابط خارجي يحدده المستخدم في المعامل دون التحقق من النطاق، مما يسهل عمليات التصيد الاحتيالي (Phishing) باستخدام نطاق الموقع الموثوق.',
            'scenarios': [
                ('رابط تصيد احتيالي', 'إرسال رابط يبدأ باسم الموقع الموثوق: redirect.php?target=https://phishing-site.com')
            ],
            'vuln_code': '$url = $_GET["target"];\nheader("Location: " . $url);',
            'secure_code': '$url = $_GET["target"];\nif (strpos($url, "/") === 0 && strpos($url, "//") !== 0) {\n    header("Location: " . $url);\n}',
            'flag': 'FLAG{Open_Redirect_Exploited_Safe_3301}'
        },
        {
            'num': '10',
            'title': 'تزوير الطلب من جانب الخادم (SSRF)',
            'desc': 'استغلال ميزات جلب الروابط من السيرفر لطلب عناوين شبكة داخلية خاصة (Localhost أو Private IPs) واختراق خدمات داخلية أو فحص المنافذ.',
            'scenarios': [
                ('فحص الخدمات الداخلية', 'إدخال http://127.0.0.1 أو http://localhost لجعل السيرفر يطلب نفسه داخلياً.')
            ],
            'vuln_code': '$url = $_POST["url"];\n$content = file_get_contents($url);',
            'secure_code': '$ip = gethostbyname(parse_url($url, PHP_URL_HOST));\nif (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {\n    die("Private IPs Blocked!");\n}',
            'flag': 'FLAG{SSRF_Internal_Server_Request_7710}'
        },
        {
            'num': '11',
            'title': 'التخمين وغياب حماية المحاولات (Brute Force)',
            'desc': 'غياب آليات الحد من تكرار المحاولات (Rate Limiting) ورموز التحقق البشري (CAPTCHA)، مما يتيح تجربة قواميس كلمات مرور شائعة لكسر حساب المدير.',
            'scenarios': [
                ('تخمين كلمة مرور الحساب', 'استخدام كلمة مرور ضعيفة admin123 ضد حساب admin عبر Burp Suite أو قاموس تجريبي.')
            ],
            'vuln_code': 'if ($user === "admin" && $pass === $db_pass) { login(); }',
            'secure_code': 'if ($_SESSION["failed_attempts"] >= 3) {\n    $_SESSION["lockout"] = time() + 60;\n    die("Account Locked for 60 seconds");\n}',
            'flag': 'FLAG{Brute_Force_Password_Cracked_2281}'
        },
        {
            'num': '12',
            'title': 'مقارنات PHP الضعيفة والأنواع (Type Juggling)',
            'desc': 'خلل في منطق المقارنة ناجم عن استخدام المقارنة الضعيفة (==) أو دوال مثل strcmp مع مصفوفات أو مقارنة سلاسل تبدأ بـ 0e (Magic Hashes).',
            'scenarios': [
                ('تجاوز strcmp بالمصفوفة', 'إرسال api_key[]=bypass في POST لتمرير مصفوفة تجعل الدالة تتجاوز الفحص.'),
                ('الهاش السحري Magic Hash', 'إرسال نص 240610708 الذي ينتج عنه MD5 يبدأ بـ 0e ويساوي صفراً في المقارنة الضعيفة.')
            ],
            'vuln_code': 'if (strcmp($secret, $_POST["api_key"]) == 0) { grant(); }',
            'secure_code': 'if (is_string($_POST["api_key"]) && hash_equals($secret, $_POST["api_key"])) { grant(); }',
            'flag': 'FLAG{PHP_Type_Juggling_Bypassed_6672}'
        },
        {
            'num': '13',
            'title': 'التلاعب بالصلاحيات والكوكي (Broken Access Control)',
            'desc': 'تخزين رتبة المستخدم أو صلاحياته في ملف تعريف ارتباط (Cookie) لدى المتصفح دون تشفير، مما يتيح للطالب تعديل قيمته من student إلى admin.',
            'scenarios': [
                ('تصعيد الصلاحيات (Privilege Escalation)', 'تعديل الكوكي user_role من student إلى admin عبر F12 DevTools لدخول لوحة التحكم.')
            ],
            'vuln_code': '$role = $_COOKIE["user_role"];\nif ($role === "admin") { show_admin_panel(); }',
            'secure_code': '// حفظ الصلاحيات في Session السيرفر وليس الكوكي:\nif ($_SESSION["user"]["role"] !== "admin") { die("Access Denied"); }',
            'flag': 'FLAG{Privilege_Escalation_Admin_Role_5502}'
        }
    ]

    for v in vulns:
        story.append(Paragraph(ar(f"{v['num']}. {v['title']}"), h1_style))
        story.append(HRFlowable(width="100%", thickness=1, color=colors.HexColor('#0284c7'), spaceAfter=6))
        story.append(Paragraph(ar(v['desc']), body_style))

        # السيناريوهات
        for s_title, s_text in v['scenarios']:
            story.append(Paragraph(ar(f"• {s_title}: {s_text}"), body_style))

        story.append(Spacer(1, 3))

        # جدول الكود المصاب vs الكود الآمن
        code_table_data = [
            [
                Paragraph(ar('الكود المصاب (Vulnerable - Low):'), ParagraphStyle('TH1', fontName='ArabicFontBold', fontSize=8.5, textColor=colors.HexColor('#b91c1c'), alignment=2)),
                Paragraph(ar('الكود الآمن بعد الترقيع (Secure - High):'), ParagraphStyle('TH2', fontName='ArabicFontBold', fontSize=8.5, textColor=colors.HexColor('#15803d'), alignment=2))
            ],
            [
                Paragraph(html.escape(v['vuln_code']).replace('\n', '<br/>'), code_style),
                Paragraph(html.escape(v['secure_code']).replace('\n', '<br/>'), code_style)
            ]
        ]
        t_code = Table(code_table_data, colWidths=[250, 260])
        t_code.setStyle(TableStyle([
            ('BACKGROUND', (0,0), (0,-1), colors.HexColor('#fef2f2')),
            ('BACKGROUND', (1,0), (1,-1), colors.HexColor('#f0fdf4')),
            ('BOX', (0,0), (-1,-1), 0.5, colors.HexColor('#cbd5e1')),
            ('INNERGRID', (0,0), (-1,-1), 0.5, colors.HexColor('#e2e8f0')),
            ('PADDING', (0,0), (-1,-1), 5),
            ('VALIGN', (0,0), (-1,-1), 'TOP'),
        ]))
        story.append(t_code)

        # العلم
        flag_box = [
            [
                Paragraph(ar('العلم السري للتحدي (Flag):'), ParagraphStyle('FLG_LBL', fontName='ArabicFontBold', fontSize=8.5, textColor=colors.HexColor('#0369a1'), alignment=2)),
                Paragraph(v['flag'], ParagraphStyle('FLG_VAL', fontName='Courier-Bold', fontSize=8, textColor=colors.HexColor('#0284c7'), alignment=0))
            ]
        ]
        t_flag = Table(flag_box, colWidths=[150, 360])
        t_flag.setStyle(TableStyle([
            ('BACKGROUND', (0,0), (-1,-1), colors.HexColor('#f8fafc')),
            ('BOX', (0,0), (-1,-1), 0.5, colors.HexColor('#e2e8f0')),
            ('PADDING', (0,0), (-1,-1), 3),
            ('VALIGN', (0,0), (-1,-1), 'MIDDLE')
        ]))
        story.append(Spacer(1, 3))
        story.append(t_flag)
        story.append(Spacer(1, 8))

    # =========================================================================
    # جدول الأعلام والحلول المرجعية الشاملة (Cheatsheet)
    # =========================================================================
    story.append(PageBreak())
    story.append(Paragraph(ar('جدول الأعلام والحلول المرجعية السريعة (CTF Cheatsheet)'), h1_style))
    story.append(HRFlowable(width="100%", thickness=1.5, color=colors.HexColor('#0284c7'), spaceAfter=8))
    story.append(Paragraph(ar('هذا الجدول مرجع خاص للمدرب لمتابعة حلول الطلاب والتأكد من الأعلام المحصلة لكل تحدٍ:'), body_style))

    cheatsheet_rows = [
        [
            Paragraph(ar('التحدي'), ParagraphStyle('CTH', fontName='ArabicFontBold', fontSize=9, alignment=1, textColor=colors.white)),
            Paragraph(ar('طريقة الحل / البايلود المقترح'), ParagraphStyle('CTH', fontName='ArabicFontBold', fontSize=9, alignment=1, textColor=colors.white)),
            Paragraph('CTF Flag', ParagraphStyle('CTH_EN', fontName='Helvetica-Bold', fontSize=9, alignment=1, textColor=colors.white))
        ]
    ]

    cheatsheet_data = [
        ("SQLi Auth", "اسم المستخدم: admin' --", "FLAG{SQLi_Auth_Bypass_Success_9281}"),
        ("SQLi UNION", "' UNION SELECT 1,'x','cat',0,secret_code FROM products --", "FLAG{SQLi_Union_Extract_Secret_4812}"),
        ("XSS Reflected", "<script>alert(1)</script>", "FLAG{XSS_Reflected_Payload_Found_7719}"),
        ("XSS Stored", "نشر كود جافاسكربت في التعليقات", "FLAG{XSS_Stored_Script_Triggered_3381}"),
        ("XSS DOM", "تحديث الرابط بـ #name=<img src=1 onerror=alert(1)>", "FLAG{XSS_DOM_Execution_Caught_1928}"),
        ("LFI", "طلب الملف: ?file=secret_note.txt", "FLAG{LFI_Local_File_Read_Exposed_8821}"),
        ("File Upload", "رفع ملف shell.php واستدعاؤه في المتصفح", "FLAG{File_Upload_WebShell_RCE_5521}"),
        ("Command Inj", "إرسال: 127.0.0.1 && whoami", "FLAG{Command_Injection_Pwned_9912}"),
        ("IDOR", "تعديل المعامل في الرابط إلى ?msg_id=2", "FLAG{IDOR_Unauthorized_Access_6619}"),
        ("CSRF", "إرسال طلب POST لتعديل البريد إلى hacker@evil.com", "FLAG{CSRF_Request_Forged_Successfully_4421}"),
        ("Sensitive Data", "تحميل ملف database.sqlite مباشرة من الرابط", "FLAG{Sensitive_Data_Exposed_Download_1192}"),
        ("Open Redirect", "طلب: ?target=https://google.com", "FLAG{Open_Redirect_Exploited_Safe_3301}"),
        ("SSRF", "طلب السيرفر الداخلي: http://127.0.0.1", "FLAG{SSRF_Internal_Server_Request_7710}"),
        ("Brute Force", "تخمين كلمة مرور admin: admin123", "FLAG{Brute_Force_Password_Cracked_2281}"),
        ("Type Juggling", "إرسال مصفوفة api_key[]=bypass أو هاش 240610708", "FLAG{PHP_Type_Juggling_Bypassed_6672}"),
        ("Access Control", "تعديل الكوكي user_role إلى admin", "FLAG{Privilege_Escalation_Admin_Role_5502}")
    ]

    for title, payload, flag in cheatsheet_data:
        cheatsheet_rows.append([
            Paragraph(ar(title), ParagraphStyle('C1', fontName='ArabicFont', fontSize=8.5, alignment=1)),
            Paragraph(ar(payload), ParagraphStyle('C2', fontName='ArabicFont', fontSize=8, alignment=2)),
            Paragraph(flag, ParagraphStyle('C3', fontName='Courier', fontSize=7, alignment=0))
        ])

    t_sheet = Table(cheatsheet_rows, colWidths=[90, 200, 220])
    t_sheet.setStyle(TableStyle([
        ('BACKGROUND', (0,0), (-1,0), colors.HexColor('#0f172a')),
        ('BOX', (0,0), (-1,-1), 1, colors.HexColor('#cbd5e1')),
        ('INNERGRID', (0,0), (-1,-1), 0.5, colors.HexColor('#e2e8f0')),
        ('VALIGN', (0,0), (-1,-1), 'MIDDLE'),
        ('PADDING', (0,0), (-1,-1), 4),
    ]))
    story.append(t_sheet)

    story.append(Spacer(1, 20))

    # بطاقة الخاتمة
    closing_card = [
        [Paragraph(ar('تم بحمد الله وتوفيقه'), ParagraphStyle('Cl1', fontName='ArabicFontBold', fontSize=13, alignment=1, textColor=colors.HexColor('#0f172a')))],
        [Paragraph(ar('نتمنى أن يكون هذا المختبر دليلاً نافعاً وممتعاً في تعليم واحتراف أمن تطبيقات الويب.'), ParagraphStyle('Cl2', fontName='ArabicFont', fontSize=10, alignment=1, textColor=colors.HexColor('#475569')))],
        [Spacer(1, 4)],
        [Paragraph(ar('إعداد: الجوكر الفلسطيني احمد سليم'), ParagraphStyle('Cl3', fontName='ArabicFontBold', fontSize=13, alignment=1, textColor=colors.HexColor('#0284c7')))]
    ]
    t_close = Table(closing_card, colWidths=[510])
    t_close.setStyle(TableStyle([
        ('BACKGROUND', (0,0), (-1,-1), colors.HexColor('#f1f5f9')),
        ('BOX', (0,0), (-1,-1), 1, colors.HexColor('#0284c7')),
        ('PADDING', (0,0), (-1,-1), 10),
    ]))
    story.append(t_close)

    # بناء المستند
    doc.build(story, canvasmaker=NumberedCanvas)
    print(f"[+] تم إنشاء الكتيب بنجاح: {pdf_path}")

if __name__ == '__main__':
    build_pdf()
