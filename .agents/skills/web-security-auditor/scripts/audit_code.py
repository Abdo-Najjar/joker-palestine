#!/usr/bin/env python3
"""
Static Security Code Auditor for PHP & Web Applications.
Author: المهندس احمد سليم 🇵🇸 - مختبر الجوكر الفلسطيني
"""

import argparse
import os
import re
import sys

# Ensure UTF-8 output on Windows console
if hasattr(sys.stdout, 'reconfigure'):
    sys.stdout.reconfigure(encoding='utf-8', errors='replace')

RULES = [
    {
        "id": "SEC-SQLI-001",
        "name": "SQL Injection Risk",
        "severity": "CRITICAL",
        "cwe": "CWE-89",
        "pattern": r"(SELECT|INSERT|UPDATE|DELETE).*\$(\w+|_[A-Z]+\[.+?\])",
        "explanation": "Direct variable interpolation detected inside SQL query string.",
        "remediation": "Use prepared statements with PDO or MySQLi parameterized queries."
    },
    {
        "id": "SEC-CMD-002",
        "name": "Command Injection Risk",
        "severity": "CRITICAL",
        "cwe": "CWE-78",
        "pattern": r"\b(shell_exec|exec|system|passthru|popen|proc_open)\s*\([^)]*\$(_[A-Z]+\[.+?\]|\w+)",
        "explanation": "Dynamic or user-controlled input passed directly to system execution function.",
        "remediation": "Avoid system execution functions. If unavoidable, sanitize using escapeshellarg() or filter_var()."
    },
    {
        "id": "SEC-XSS-003",
        "name": "Cross-Site Scripting (XSS) Risk",
        "severity": "HIGH",
        "cwe": "CWE-79",
        "pattern": r"\b(echo|print)\s+.*\$_(GET|POST|REQUEST|COOKIE)\[.+?\](?!.*htmlspecialchars)",
        "explanation": "Direct output of unescaped superglobal data to the HTTP response.",
        "remediation": "Wrap output with htmlspecialchars($data, ENT_QUOTES, 'UTF-8')."
    },
    {
        "id": "SEC-LFI-004",
        "name": "File Inclusion / Path Traversal Risk",
        "severity": "HIGH",
        "cwe": "CWE-22 / CWE-98",
        "pattern": r"\b(include|require|include_once|require_once|file_get_contents|readfile)\s*\([^)]*\$_(GET|POST|REQUEST)\[.+?\]",
        "explanation": "Dynamic file path constructed from user input without validation.",
        "remediation": "Use strict filename whitelisting or sanitize using basename() with realpath() checks."
    },
    {
        "id": "SEC-COOKIE-005",
        "name": "Insecure Cookie Configuration",
        "severity": "MEDIUM",
        "cwe": "CWE-614 / CWE-1004",
        "pattern": r"setcookie\s*\([^;)]+\)(?!.*(httponly|samesite))",
        "explanation": "Cookie set without explicitly configuring HttpOnly or SameSite flags.",
        "remediation": "Always supply options array: ['httponly' => true, 'secure' => true, 'samesite' => 'Strict']."
    },
    {
        "id": "SEC-EVAL-006",
        "name": "Dangerous Code Execution Function",
        "severity": "CRITICAL",
        "cwe": "CWE-95",
        "pattern": r"\b(eval|create_function|assert)\s*\(",
        "explanation": "Use of dynamic code evaluation functions allows arbitrary PHP code execution.",
        "remediation": "Refactor logic to eliminate eval() and dynamic code evaluation entirely."
    }
]

def scan_file(filepath):
    findings = []
    try:
        with open(filepath, "r", encoding="utf-8", errors="ignore") as f:
            lines = f.readlines()
    except Exception as e:
        return [("ERROR", 0, f"Could not read file: {e}", "")]

    for line_idx, line in enumerate(lines, start=1):
        stripped = line.strip()
        # Skip pure comments
        if stripped.startswith("//") or stripped.startswith("*") or stripped.startswith("/*"):
            continue

        for rule in RULES:
            if re.search(rule["pattern"], line, re.IGNORECASE):
                findings.append({
                    "rule": rule,
                    "line_num": line_idx,
                    "code_snippet": stripped[:120]
                })

    return findings

def main():
    parser = argparse.ArgumentParser(description="Web Security Code Auditor - Joker Lab")
    parser.add_argument("--target", help="Single PHP/JS file to audit")
    parser.add_argument("--dir", help="Directory of source code to recursively audit")
    args = parser.parse_args()

    if not args.target and not args.dir:
        parser.print_help()
        sys.exit(1)

    targets = []
    if args.target:
        if os.path.isfile(args.target):
            targets.append(args.target)
        else:
            print(f"[-] Target file not found: {args.target}")
            sys.exit(1)
    elif args.dir:
        for root, _, files in os.walk(args.dir):
            for file in files:
                if file.endswith((".php", ".inc", ".html")):
                    targets.append(os.path.join(root, file))

    print("=" * 80)
    print("🛡️  مختبر الجوكر الفلسطيني | فاحص الأكواد الأمني ومولد تقارير الترقيع")
    print(f"🔍 إجمالي الملفات المفحوصة: {len(targets)}")
    print("=" * 80)

    total_findings = 0
    for target in targets:
        findings = scan_file(target)
        if findings:
            rel_path = os.path.relpath(target)
            print(f"\n📁 الملف: {rel_path} [{len(findings)} ملاحظات أمنية]")
            for f in findings:
                total_findings += 1
                r = f["rule"]
                sev_color = "\033[91m" if r["severity"] == "CRITICAL" else "\033[93m"
                reset = "\033[0m"
                print(f"  • [سطر {f['line_num']}] {sev_color}[{r['severity']}] {r['name']} ({r['cwe']}){reset}")
                print(f"    الكود: {f['code_snippet']}")
                print(f"    الخلل: {r['explanation']}")
                print(f"    الحل المقترح: {r['remediation']}")

    print("\n" + "=" * 80)
    print(f"✅ اكتمل الفحص! تم رصد {total_findings} نقطة أمنية تحتاج مراجعة أو ترقيع.")
    print("=" * 80)

if __name__ == "__main__":
    main()
