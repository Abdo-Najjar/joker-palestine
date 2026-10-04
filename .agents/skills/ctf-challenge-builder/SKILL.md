---
name: ctf-challenge-builder
description: "Scaffold, design, and register new CTF security challenges for 'مختبر الجوكر الفلسطيني' (Joker Security Lab). Use when adding new web vulnerability challenges (e.g. XXE, SSTI, JWT, Prototype Pollution), generating vulnerable/secure code comparisons, creating flags, and configuring hints."
---

# CTF Challenge Builder | مختبر الجوكر الفلسطيني

A specialized procedure to design, implement, and register new web security training challenges in the Joker Security Lab.

## Challenge Architecture

Every challenge in the lab follows a consistent, educational design pattern:
1. **Title and Meta**: Defined with `$page_title` ending in `| مختبر الجوكر الفلسطيني`.
2. **Template Integration**: Includes `../includes/header.php` at top and `../includes/footer.php` at bottom.
3. **Dual Security Modes**:
   - `low`: Intentionally contains the real, reproducible vulnerability.
   - `secure`: Demonstrates the patched, hardened, and safe production implementation.
4. **Interactive Flag System**:
   - Registered in `$CHALLENGE_FLAGS` inside `config.php`.
   - Calling `award_flag('challenge_key')` upon successful exploitation.
   - Triggering celebratory confetti, victory fanfare, and the celebration modal.
   - Permanent flag box rendered via `render_flag_box('challenge_key')`.
5. **Progressive Disclosure Hints**:
   - Hints rendered via `render_hints([...])` (hidden by default).
6. **Code Comparison Section**:
   - Side-by-side comparison using `render_code_comparison($vuln_code, $secure_code, $explanation)`.

## Scaffolding Script

You can scaffold a new challenge using the helper script:

```bash
python .agents/skills/ctf-challenge-builder/scripts/scaffold.py --key "xxe" --title "حقن الكيانات الخارجية (XXE)" --category "Injection" --flag "FLAG{XXE_Xml_External_Entity_Injected_9921}"
```

## Manual Implementation Checklist

1. **Register in `config.php`**:
   Add entry to `$CHALLENGE_FLAGS`:
   ```php
   'xxe' => ['title' => 'XXE: حقن الكيانات الخارجية', 'flag' => 'FLAG{XXE_Xml_External_Entity_Injected_9921}'],
   ```
2. **Create Page in `pages/<key>.php`**:
   Follow the standard structure with form, vulnerability logic, flag awarding, hints, and code comparison.
3. **Update Sidebar in `includes/header.php`**:
   Add the challenge item under the challenges list with the green solved checkmark badge `is_flag_solved('key')`.
4. **Update Main Dashboard in `index.php`**:
   Add a challenge card to the grid in `index.php`.
