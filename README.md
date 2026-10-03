# 🃏 مختبر الجوكر الأمني لاختبار اختراق تطبيقات الويب (Joker Security Lab v2.0)

> **منصة تعليمية وتطبيقية متكاملة لشرح وتطبيق أشهر وأخطر ثغرات تطبيقات الويب (OWASP Top 10) مع المقارنة بين الكود المصاب والكود الآمن.**  
> **إعداد وتطوير:** **الجوكر الفلسطيني احمد سليم** 🇵🇸

---

## 🌟 مميزات المختبر

1. **يعمل بتقنية PHP + SQLite (Zero Configuration):**
   * لا حاجة لتثبيت أو إعداد خادم MySQL منفصل.
   * قاعدة بيانات SQLite مدمجة تنشئ نفسها تلقائياً، وتعمل على أي استضافة مشتركة (cPanel / Apache / Nginx / PHP Built-in Server).
2. **شامل لأشهر وأخطر 16 تحدياً أمنياً (13 فئة من ثغرات OWASP):**
   * حقن قواعد البيانات (SQLi): تجاوز الدخول واستخراج البيانات عبر UNION.
   * السكربتات عبر المواقع (XSS): المنعكس (Reflected)، المخزن (Stored)، والمعتمد على الـ DOM.
   * تضمين الملفات واجتياز المسارات (LFI & Path Traversal) مع مشغلات PHP Wrappers.
   * رفع الملفات غير الآمن (Unrestricted File Upload to RCE).
   * حقن أوامر نظام التشغيل (OS Command Injection).
   * التحكم غير المباشر بالكائنات (IDOR / BOLA).
   * تزوير الطلبات عبر المواقع (CSRF) مع محاكاة هجوم خارجي.
   * كشف البيانات الحساسة وتحميل قاعدة البيانات (Sensitive Data Exposure).
   * التوجيه المفتوح (Open Redirect).
   * تزوير الطلب من جهة الخادم (SSRF).
   * التخمين وغياب حظر المحاولات (Brute Force & Rate Limiting).
   * مقارنات PHP الضعيفة (PHP Type Juggling & Magic Hashes).
   * التلاعب بالصلاحيات وملفات الكوكي (Broken Access Control & Privilege Escalation).
3. **مستويات أمان متغيرة (Security Levels):**
   * **مستوى ضعيف (Low):** الكود مصاب بالكامل لتجربة الاستغلال والبايلودات وشرحها للطلاب.
   * **مستوى محمي (Secure):** الكود مرقع بالطريقة البرمجية الصحيحة 100% ليقارن الطالب بين الخطأ والصواب.
4. **نظام الأعلام والمنافسة (CTF Flag System):**
   * عند نجاح كل استغلال، يحصل الطالب على علم فريد (Flag) يتم احتسابه في لوحة النتائج العلوية تلقائياً.
5. **زر إعادة ضبط فوري (One-Click Database Reset):**
   * لإعادة المختبر وحذف الملفات المرفوعة وإعادة بناء الجداول في ثانية واحدة بنقرة زر.
6. **كتيب ودليل توثيقي شامل بصيغة PDF:**
   * متوفر داخل المجلد: `docs/joker_security_lab_manual.pdf`.
   * يتضمن شروحات الثغرات، الأكواد المصابة، الأكواد الآمنة، وجدول الحلول المرجعية (Cheatsheet).
   * مذيل بعبارة: **إعداد: الجوكر الفلسطيني احمد سليم**.

---

## 🚀 طريقة التشغيل والتركيب

### 1. التشغيل المحلي الفوري (PHP Built-in Server)
لا تحتاج لأي برامج إضافية، فقط افتح موجه الأوامر (CMD / PowerShell / Terminal) داخل مجلد المشروع ونفذ:

```bash
php -S localhost:8000
```

ثم افتح المتصفح وتوجه إلى:
```
http://localhost:8000
```

### 2. النشر على استضافة ويب مشتركة (cPanel / DirectAdmin / VPS)
1. ارفع جميع ملفات مجلد المشروع إلى مجلد `public_html` (أو أي مجلد فرعي تفضله).
2. تأكد من أن إصدار PHP في الاستضافة هو 7.4 أو 8.x ومفعل به إضافة `pdo_sqlite` و `sqlite3` (تكون مفعلة افتراضياً في 99% من الاستضافات).
3. تأكد من إعطاء صلاحيات الكتابة (755 أو 777) لمجلد `uploads/` وملف `database.sqlite`.
4. افتح رابط موقعك ومبروك! سيعمل المختبر فوراً.

> ⚠️ **تنبيه أمني هام للمدرب:**
> نظراً لأن المختبر يحتوي عمداً على ثغرات تنفيذ أوامر حقيقية (Web Shell و Command Injection)، يُنصح بحماية مجلد المشروع بكلمة مرور عبر خيار **Directory Privacy** في لوحة التحكم cPanel لمنع المتطفلين من العبث به على الإنترنت.

---

## 📁 هيكلية ملفات المشروع

```text
joker-security-lab/
├── config.php            # الإعدادات العامة، الاتصال بـ SQLite، وتتبع الأعلام ومستوى الحماية
├── reset.php             # إعادة بناء قاعدة البيانات الافتراضية وتنظيف الملفات
├── index.php             # لوحة التحكم الرئيسية وفهرس التحديات
├── database.sqlite       # قاعدة بيانات SQLite المدمجة
├── test_lab.py           # سكربت فحص واختبار جميع الثغرات الـ 16 آلياً
├── generate_pdf.py       # سكربت بايثون لتوليد كتيب الشرح PDF
├── docs/
│   └── joker_security_lab_manual.pdf  # دليل المختبر الشامل للطباعة والقراءة
├── includes/
│   ├── header.php        # الترويسة والشريط العلوي وشريط التنقل الجانبي
│   └── footer.php        # التذييل وحقوق الإعداد والبرمجة
├── assets/
│   ├── css/style.css     # التنسيقات والمؤثرات البصرية
│   └── js/main.js        # وظائف التفاعل ونسخ الأعلام
├── uploads/              # مجلد رفع الملفات لتحدي الـ Web Shell
└── pages/
    ├── sqli.php          # 1. حقن قواعد البيانات
    ├── xss.php           # 2. السكربتات عبر المواقع
    ├── lfi.php           # 3. تضمين الملفات
    ├── upload.php        # 4. رفع الملفات غير الآمن
    ├── cmdi.php          # 5. أوامر النظام
    ├── idor.php          # 6. الصلاحيات غير المباشرة
    ├── csrf.php          # 7. تزوير الطلبات
    ├── sensitive.php     # 8. كشف البيانات الحساسة
    ├── redirect.php      # 9. التوجيه المفتوح
    ├── ssrf.php          # 10. تزوير السيرفر
    ├── bruteforce.php    # 11. التخمين
    ├── type_juggling.php # 12. مقارنات PHP الضعيفة
    ├── access_control.php# 13. التلاعب بالكوكي
    ├── submit_flag.php   # معالجة تسليم الأعلام يدوياً
    ├── about.txt         # ملفات تجريبية لـ LFI
    ├── contact.txt       # ملفات تجريبية لـ LFI
    └── secret_note.txt   # الملف السري المطلوب في LFI
```

---

## 🏆 جدول الأعلام والحلول المرجعية (Cheatsheet للمدرب)

| التحدي | فئة الثغرة | البايلود المقترح للحل | العلم السري (CTF Flag) |
| :--- | :--- | :--- | :--- |
| **SQLi Auth** | Injection | `admin' --` | `FLAG{SQLi_Auth_Bypass_Success_9281}` |
| **SQLi UNION** | Injection | `' UNION SELECT 1,'x','cat',0,secret_code FROM products --` | `FLAG{SQLi_Union_Extract_Secret_4812}` |
| **XSS Reflected** | XSS | `<script>alert(1)</script>` | `FLAG{XSS_Reflected_Payload_Found_7719}` |
| **XSS Stored** | XSS | كود JS في حقل التعليق | `FLAG{XSS_Stored_Script_Triggered_3381}` |
| **XSS DOM** | XSS | `#name=<img src=1 onerror=alert(1)>` | `FLAG{XSS_DOM_Execution_Caught_1928}` |
| **LFI** | Inclusion | `?file=secret_note.txt` | `FLAG{LFI_Local_File_Read_Exposed_8821}` |
| **File Upload** | RCE | رفع ملف `shell.php` | `FLAG{File_Upload_WebShell_RCE_5521}` |
| **Command Inj** | RCE | `127.0.0.1 && whoami` | `FLAG{Command_Injection_Pwned_9912}` |
| **IDOR** | Access Control | تعديل الرابط إلى `?msg_id=2` | `FLAG{IDOR_Unauthorized_Access_6619}` |
| **CSRF** | CSRF | إرسال نموذج خفي بتعديل البريد | `FLAG{CSRF_Request_Forged_Successfully_4421}` |
| **Sensitive Data**| Exposure | تحميل `database.sqlite` مباشرة | `FLAG{Sensitive_Data_Exposed_Download_1192}` |
| **Open Redirect** | Phishing | `?target=https://google.com` | `FLAG{Open_Redirect_Exploited_Safe_3301}` |
| **SSRF** | SSRF | إرسال `http://127.0.0.1` | `FLAG{SSRF_Internal_Server_Request_7710}` |
| **Brute Force** | Auth | تجربة كلمة `admin123` | `FLAG{Brute_Force_Password_Cracked_2281}` |
| **Type Juggling** | Logic Flaw | مصفوفة `api_key[]=bypass` أو `240610708` | `FLAG{PHP_Type_Juggling_Bypassed_6672}` |
| **Access Control**| Privilege Escalation| تعديل الكوكي `user_role=admin` | `FLAG{Privilege_Escalation_Admin_Role_5502}` |

---

## 📜 الحقوق والملكية
المشروع مفتوح المصدر للأغراض التعليمية والتدريبية ومسابقات الـ CTF.  
**إعداد وتطوير:** **الجوكر الفلسطيني احمد سليم** 🇵🇸
