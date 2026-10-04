---
name: secure-input-hardening
description: "Defensive web security engineering and input hardening skill. Use when implementing robust input validation, context-aware output encoding, HTTP security headers (CSP, HSTS, XFO, XCTO), secure session configurations, and defensive filter testing."
---

# Secure Input Hardening & Defensive Engineering | مختبر الجوكر الفلسطيني

A comprehensive guide and utility suite for defensive hardening of web applications.

## Defense-in-Depth Principles

1. **Input Validation (Whitelisting)**:
   - Always validate data types, formats, and ranges on the server side.
   - Use PHP's native `filter_var()` with appropriate filters (e.g. `FILTER_VALIDATE_INT`, `FILTER_VALIDATE_EMAIL`, `FILTER_VALIDATE_IP`).
2. **Context-Aware Output Encoding**:
   - For HTML body: `htmlspecialchars($str, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')`
   - For JSON/API: `json_encode($data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP)`
3. **HTTP Security Headers**:
   ```php
   header("Content-Security-Policy: default-src 'self'; script-src 'self' https://cdn.jsdelivr.net;");
   header("X-Content-Type-Options: nosniff");
   header("X-Frame-Options: SAMEORIGIN");
   header("Referrer-Policy: strict-origin-when-cross-origin");
   ```
4. **Session Protection**:
   ```php
   session_set_cookie_params([
       'lifetime' => 0,
       'path' => '/',
       'domain' => '',
       'secure' => true,
       'httponly' => true,
       'samesite' => 'Strict'
   ]);
   ```

## Header Auditing Tool

Check the security posture and headers of any endpoint:

```bash
python .agents/skills/secure-input-hardening/scripts/check_headers.py --url http://localhost:8000
```
