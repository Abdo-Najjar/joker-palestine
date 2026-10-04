---
name: web-security-auditor
description: "Static security code audit and vulnerability analyzer for PHP and JavaScript web applications. Use to inspect codebases for OWASP Top 10 vulnerabilities (SQLi, XSS, CSRF, IDOR, LFI, RCE, insecure deserialization, hardcoded secrets), generate risk assessments, and produce remediated secure patches."
---

# Web Security Auditor | مختبر الجوكر الفلسطيني

A comprehensive static security analysis and code remediation skill for auditing web applications.

## Purpose

1. **Vulnerability Discovery**: Perform defensive static analysis on PHP, HTML, and JS source files.
2. **Impact Assessment**: Classify findings by severity (Critical, High, Medium, Low) and map to OWASP Top 10 & CWE standards.
3. **Remediation & Patching**: Produce drop-in secure code replacements adhering to modern security best practices (Parameterized Queries, Prepared Statements, Context-Aware Encoding, Token-based CSRF protection, Path Whitelisting).

## Running the Security Audit Tool

Invoke the bundled security auditor script from the project root:

```bash
# Scan a specific file
python .agents/skills/web-security-auditor/scripts/audit_code.py --target pages/sqli.php

# Scan an entire directory
python .agents/skills/web-security-auditor/scripts/audit_code.py --dir pages/
```

## Vulnerability Patterns Covered

| Vulnerability | Insecure Pattern Example | Secure Remediation |
| :--- | :--- | :--- |
| **SQL Injection** | `SELECT * FROM users WHERE u = '$user'` | `$stmt = $db->prepare('SELECT * FROM users WHERE u = :u'); $stmt->execute([':u' => $user]);` |
| **Cross-Site Scripting (XSS)** | `echo $_GET['q'];` | `echo htmlspecialchars($_GET['q'], ENT_QUOTES, 'UTF-8');` |
| **Command Injection (RCE)** | `shell_exec("ping " . $_POST['ip']);` | `escapeshellarg()` or native PHP networking functions |
| **Local File Inclusion (LFI)** | `include "pages/" . $_GET['page'];` | Strict basename/whitelisting `basename($_GET['page'])` |
| **Insecure Cookies** | `setcookie('role', 'admin');` | `setcookie('session_id', $token, ['httponly' => true, 'secure' => true, 'samesite' => 'Strict']);` |
| **Missing CSRF Token** | State-changing `POST` without token validation | Generate `bin2hex(random_bytes(32))` and verify with `hash_equals()` |
