---
name: quiz-and-certification
description: "Interactive assessment, knowledge examination, and verifiable certificate issuance system for students of 'مختبر الجوكر الفلسطيني'. Use to evaluate student mastery of OWASP Top 10 vulnerabilities and generate official completion certificates."
---

# Quiz & Certification System | مختبر الجوكر الفلسطيني

A comprehensive training evaluation and certification suite designed for instructors and students.

## Architecture

1. **Evaluation Engine (`pages/quiz.php`)**:
   - Multi-category randomized or structured examination covering all 14 lab vulnerabilities.
   - Real-time feedback, grading, and pedagogical answer explanations.
   - Session-backed verification of mastery.
2. **Certificate Generator (`pages/certificate.php`)**:
   - Printable and verifiable cybersecurity training certificate.
   - Customizable recipient name.
   - Secure hash verification identifier (`JOKER-CERT-XXXX`).
   - Official seal and signature: **إعداد وتطوير: المهندس احمد سليم 🇵🇸**.
   - One-click print / export to PDF (`@media print` optimized).

## Navigation Integration

- Header Navbar: Includes direct link to "الاختبار النهائي والشهادة" (Final Quiz & Certificate).
- Main Dashboard: Unlocks celebration badge and direct certificate access upon completing challenges.
