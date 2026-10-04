#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Cyberpunk Chart Configuration Generator
Skill: cyberpunk-chart-viz
Author: المهندس احمد سليم 🇵🇸
"""

import sys
import json
import argparse

if hasattr(sys.stdout, 'reconfigure'):
    sys.stdout.reconfigure(encoding='utf-8', errors='replace')

def get_radar_config(scores=None):
    if scores is None:
        scores = [85, 70, 90, 60, 75, 80]
    
    labels = [
        "حقن البيانات (SQLi / CMDi)",
        "أمن العميل (XSS / CSRF)",
        "التحكم بالصلاحيات (IDOR)",
        "تزوير الخادم (SSRF)",
        "أمن الملفات (LFI / Upload)",
        "المنطق البرمجي (Type Juggling)"
    ]
    
    return {
        "type": "radar",
        "data": {
            "labels": labels,
            "datasets": [
                {
                    "label": "مستوى إتقان الباحث الأمني (%)",
                    "data": scores,
                    "backgroundColor": "rgba(6, 182, 212, 0.25)",
                    "borderColor": "#06b6d4",
                    "pointBackgroundColor": "#f59e0b",
                    "pointBorderColor": "#fff",
                    "pointHoverBackgroundColor": "#fff",
                    "pointHoverBorderColor": "#06b6d4",
                    "borderWidth": 2
                }
            ]
        },
        "options": {
            "responsive": True,
            "maintainAspectRatio": False,
            "scales": {
                "r": {
                    "angleLines": {"color": "rgba(255, 255, 255, 0.1)"},
                    "grid": {"color": "rgba(255, 255, 255, 0.08)"},
                    "pointLabels": {
                        "color": "#94a3b8",
                        "font": {"size": 11, "family": "'Cairo', sans-serif"}
                    },
                    "ticks": {
                        "color": "#64748b",
                        "backdropColor": "transparent",
                        "stepSize": 20
                    },
                    "suggestedMin": 0,
                    "suggestedMax": 100
                }
            },
            "plugins": {
                "legend": {
                    "labels": {
                        "color": "#e2e8f0",
                        "font": {"family": "'Cairo', sans-serif"}
                    }
                }
            }
        }
    }

def get_doughnut_config(solved, total):
    remaining = max(0, total - solved)
    return {
        "type": "doughnut",
        "data": {
            "labels": ["الأعلام المكتشفة", "التحديات المتبقية"],
            "datasets": [
                {
                    "data": [solved, remaining],
                    "backgroundColor": ["#10b981", "#1e293b"],
                    "borderColor": ["#059669", "#334155"],
                    "borderWidth": 2,
                    "hoverOffset": 4
                }
            ]
        },
        "options": {
            "responsive": True,
            "maintainAspectRatio": False,
            "cutout": "75%",
            "plugins": {
                "legend": {
                    "position": "bottom",
                    "labels": {
                        "color": "#e2e8f0",
                        "font": {"family": "'Cairo', sans-serif"}
                    }
                }
            }
        }
    }

def main():
    parser = argparse.ArgumentParser(description="توليد إعدادات الرسوم البيانية السيبرانية (Cyberpunk Chart Viz)")
    parser.add_argument("--solved", type=int, default=4, help="عدد التحديات المحلولة")
    parser.add_argument("--total", type=int, default=16, help="إجمالي التحديات")
    parser.add_argument("--format", choices=["json", "html"], default="json", help="صيغة الإخراج")
    
    args = parser.parse_args()
    
    result = {
        "radar": get_radar_config(),
        "doughnut": get_doughnut_config(args.solved, args.total)
    }
    
    if args.format == "json":
        print(json.dumps(result, ensure_ascii=False, indent=2))
    else:
        print("<!-- Cyberpunk Chart Configuration Ready -->")
        print(f"<script>const chartData = {json.dumps(result, ensure_ascii=False)};</script>")

if __name__ == "__main__":
    main()
