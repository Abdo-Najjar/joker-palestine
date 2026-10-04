---
name: cyberpunk-chart-viz
description: "Cyberpunk and dark-mode data visualizer for CTF platforms, web security training labs, and vulnerability scorecards. Generates aesthetic Radar charts, Doughnut completion metrics, and attack surface matrices using Chart.js with neon palettes (cyan, emerald, gold, crimson, purple)."
---

# Cyberpunk Chart Visualizer (`cyberpunk-chart-viz`)

A specialized skill for building high-contrast, visually stunning dark cyberpunk analytics charts for web security labs, CTF leaderboards, and student progression profiles.

## Palette & Design Tokens

| Token | Hex | Role |
| :--- | :--- | :--- |
| **Neon Cyan** | `#06b6d4` | Injection & Active Attacks (SQLi, CMDi, LFI) |
| **Neon Green** | `#10b981` | Solved Challenges & Hardened Defenses |
| **Amber Gold** | `#f59e0b` | Broken Access, IDOR & Cookies |
| **Crimson Red** | `#ef4444` | High Severity & Unsolved Vulnerabilities |
| **Cyber Purple** | `#8b5cf6` | Logic Flaws & Juggling |
| **Deep Dark Background** | `#0b0f19` | Canvas & Card Surfaces |
| **Border Slate** | `#243149` | Grid lines and dividers |

## Chart Types Provided

1. **Vulnerability Category Radar Chart (`radar`)**:
   - Evaluates competence across 6 key attack domains:
     - Injection (SQLi, CMDi)
     - Client-side (XSS, CSRF)
     - Access Control & IDOR
     - Server-Side & SSRF
     - File Handling & Upload
     - Business Logic & Type Juggling
2. **Flag Completion Doughnut (`doughnut`)**:
   - High-contrast segmented ring chart displaying Solved vs. Pending challenges.
3. **Attack Severity Bar Matrix (`bar`)**:
   - Horizontal bars indicating solved vs remaining by OWASP risk rating.

## Automation Script

The skill includes a Python generator script `scripts/generate_chart_config.py` to output ready-to-inject Chart.js configurations for PHP or static HTML pages.

### Usage:

```bash
python scripts/generate_chart_config.py --solved 6 --total 16 --output-format json
```
