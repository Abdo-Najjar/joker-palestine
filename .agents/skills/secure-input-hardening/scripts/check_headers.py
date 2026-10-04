#!/usr/bin/env python3
"""
HTTP Security Headers Auditor & Hardening Evaluator.
Author: المهندس احمد سليم 🇵🇸 - مختبر الجوكر الفلسطيني
"""

import argparse
import sys
import urllib.request

# Ensure UTF-8 output on Windows console
if hasattr(sys.stdout, 'reconfigure'):
    sys.stdout.reconfigure(encoding='utf-8', errors='replace')

RECOMMENDED_HEADERS = {
    "X-Content-Type-Options": {
        "required_value": "nosniff",
        "description": "Prevents MIME-sniffing attacks.",
        "weight": 20
    },
    "X-Frame-Options": {
        "required_value": ["DENY", "SAMEORIGIN"],
        "description": "Protects against Clickjacking (UI redressing).",
        "weight": 20
    },
    "Content-Security-Policy": {
        "required_value": None,
        "description": "Mitigates XSS, data injection, and unauthorized execution.",
        "weight": 30
    },
    "Referrer-Policy": {
        "required_value": ["strict-origin-when-cross-origin", "no-referrer", "same-origin"],
        "description": "Controls how much referrer information is leaked.",
        "weight": 15
    },
    "Permissions-Policy": {
        "required_value": None,
        "description": "Restricts browser features like camera, microphone, geolocation.",
        "weight": 15
    }
}

def audit_url(url):
    print("=" * 75)
    print(f"🛡️  مختبر الجوكر الفلسطيني | فاحص ترويسات الحماية (Security Headers)")
    print(f"🌐 الرابط المستهدف: {url}")
    print("=" * 75)

    try:
        req = urllib.request.Request(url, headers={'User-Agent': 'JokerSecurityAudit/2.0'})
        with urllib.request.urlopen(req, timeout=5) as response:
            headers = {k.title(): v for k, v in response.headers.items()}
    except Exception as e:
        print(f"[-] تعذر الاتصال بالخادم: {e}")
        return

    score = 0
    max_score = sum(h["weight"] for h in RECOMMENDED_HEADERS.values())

    for header_name, meta in RECOMMENDED_HEADERS.items():
        found_val = None
        for k, v in headers.items():
            if k.lower() == header_name.lower():
                found_val = v
                break

        if found_val:
            score += meta["weight"]
            print(f"  ✅ [مفعّل] {header_name}: {found_val}")
        else:
            print(f"  ❌ [مفقود] {header_name} - {meta['description']}")

    pct = round((score / max_score) * 100)
    grade = "A+" if pct >= 90 else ("A" if pct >= 80 else ("B" if pct >= 65 else ("C" if pct >= 50 else "F")))

    print("\n" + "-" * 75)
    print(f"📊 التقييم الأمني للترويسات: {score}/{max_score} ({pct}%) | الدرجة المستحقة: [{grade}]")
    print("-" * 75)

if __name__ == "__main__":
    parser = argparse.ArgumentParser(description="Audit HTTP Security Headers")
    parser.add_argument("--url", default="http://localhost:8000", help="URL to audit")
    args = parser.parse_args()
    audit_url(args.url)
