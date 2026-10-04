#!/usr/bin/env python3
"""
Scaffolder for 'مختبر الجوكر الفلسطيني' CTF challenges.
Author: المهندس احمد سليم 🇵🇸
"""

import argparse
import os
import sys

TEMPLATE = """<?php
$page_title = "{title} | مختبر الجوكر الفلسطيني";
require_once __DIR__ . '/../includes/header.php';

$sec = get_security_level();
$msg = "";
$result = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {{
    $input = $_POST['data_input'] ?? '';

    if ($sec === 'low') {{
        // كود مصاب: معالجة غير آمنة للمدخلات
        if (strpos($input, 'EXPLOIT') !== false) {{
            award_flag('{key}');
            $result = "تم تنفيذ الاستغلال بنجاح! راجع راية العلم بالأعلى.";
        }} else {{
            $result = "تمت معالجة المدخلات بنجاح.";
        }}
    }} else {{
        // كود آمن: تحقق صارم وترقيع كامل للثغرة
        $safe_input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
        $result = "تمت المعالجة بأمان تام مع التحقق من المدخلات.";
    }}
}}
?>

<div class="card border-0 shadow-lg mb-4" style="background-color: var(--bg-card);">
    <div class="card-header bg-dark border-bottom border-secondary d-flex justify-content-between align-items-center py-3">
        <h4 class="mb-0 text-white fw-bold">
            <i class="fas fa-shield-alt text-warning me-2"></i> {title}
        </h4>
        <span class="badge bg-secondary">{category}</span>
    </div>
    <div class="card-body p-4">
        <p class="text-light">
            <strong>الهدف:</strong> استغلال الثغرة في المستوى الضعيف للحصول على العلم (Flag)، وملاحظة الفرق عند التبديل للوضع المحمي.
        </p>

        <?= render_flag_box('{key}'); ?>

        <?php if (!empty($result)): ?>
            <div class="alert alert-info shadow-sm mb-3">
                <i class="fas fa-info-circle me-1"></i> <?= htmlspecialchars($result) ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="p-3 rounded bg-dark border border-secondary" style="max-width: 600px;">
            <div class="mb-3">
                <label class="form-label text-light">المدخلات (Input):</label>
                <input type="text" name="data_input" class="form-control font-monospace" placeholder="أدخل بيانات الاختبار..." required>
            </div>
            <button type="submit" class="btn btn-warning fw-bold">
                <i class="fas fa-paper-plane me-1"></i> إرسال البيانات
            </button>
        </form>
    </div>
</div>

<?= render_hints([
    "قم بتحليل المدخلات وكيفية تعامل السيرفر معها.",
    "جرب إدخال القيمة المتوقعة لاختبار استجابة النظام في الوضع الضعيف.",
    "قم بالتبديل إلى الوضع المحمي (Secure) لملاحظة كيف تم ترقيع الخلل برمجياً."
]); ?>

<?= render_code_comparison(
    '// الكود المصاب (Vulnerable - Low)\n$input = $_POST["data_input"];\nprocess_raw_input($input);',
    '// الكود الآمن (Secure - High)\n$input = filter_var($_POST["data_input"], FILTER_SANITIZE_SPECIAL_CHARS);\nvalidate_and_process($input);',
    'يتم ترقيع هذه الثغرة من خلال التحقق الصارم من صحة المدخلات (Input Validation) واستخدام الترميز الصحيح (Output Encoding).'
); ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
"""

def main():
    parser = argparse.ArgumentParser(description="Scaffold a new challenge for Joker Lab")
    parser.add_argument("--key", required=True, help="Unique challenge key, e.g. xxe")
    parser.add_argument("--title", required=True, help="Arabic title of challenge")
    parser.add_argument("--category", default="Web Security", help="Category name")
    parser.add_argument("--flag", required=True, help="Flag code, e.g. FLAG{...}")
    args = parser.parse_args()

    project_root = os.path.abspath(os.path.join(os.path.dirname(__file__), "../../../"))
    pages_dir = os.path.join(project_root, "pages")
    target_file = os.path.join(pages_dir, f"{args.key}.php")

    if os.path.exists(target_file):
        print(f"[-] Error: File {target_file} already exists!")
        sys.exit(1)

    content = TEMPLATE.format(
        key=args.key,
        title=args.title,
        category=args.category,
        flag=args.flag
    )

    with open(target_file, "w", encoding="utf-8") as f:
        f.write(content)

    print(f"[+] Successfully scaffolded challenge at: {target_file}")
    print(f"[+] Remember to register '{args.key}' in config.php with flag '{args.flag}'!")

if __name__ == "__main__":
    main()
