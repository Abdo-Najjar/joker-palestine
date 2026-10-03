<?php
// ==============================================================================
// Header Template | مختبر الجوكر الأمني
// إعداد: الجوكر الفلسطيني احمد سليم
// ==============================================================================

require_once __DIR__ . '/../config.php';
$score = get_score();
$current_page = basename($_SERVER['PHP_SELF']);
$sec_level = get_security_level();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? 'مختبر الجوكر الأمني | Joker Security Lab' ?></title>
    <!-- Bootstrap 5 RTL -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <!-- Cairo Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <!-- Custom Cyberpunk / Dark Security CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        :root {
            --bg-main: #0b0f19;
            --bg-card: #151c2c;
            --bg-sidebar: #0e1424;
            --accent-green: #10b981;
            --accent-cyan: #06b6d4;
            --accent-red: #ef4444;
            --accent-amber: #f59e0b;
            --border-color: #243149;
            --text-main: #e2e8f0;
            --text-muted: #94a3b8;
        }
        body {
            font-family: 'Cairo', sans-serif;
            background-color: var(--bg-main);
            color: var(--text-main);
            min-height: 100vh;
        }
        .navbar-cyber {
            background-color: var(--bg-sidebar);
            border-bottom: 2px solid var(--border-color);
        }
        .sidebar {
            background-color: var(--bg-sidebar);
            border-left: 1px solid var(--border-color);
            min-height: calc(100vh - 70px);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .sidebar .nav-link {
            color: var(--text-muted);
            padding: 10px 16px;
            border-radius: 8px;
            margin-bottom: 4px;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: all 0.2s ease;
        }
        .sidebar .nav-link:hover {
            color: #fff;
            background: rgba(6, 182, 212, 0.1);
            transform: translateX(-4px);
        }
        .sidebar .nav-link.active {
            color: #fff;
            background: linear-gradient(135deg, #0284c7, #06b6d4);
            box-shadow: 0 4px 12px rgba(6, 182, 212, 0.3);
        }
        .card-cyber {
            background-color: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
        }
        .card-cyber-header {
            border-bottom: 1px solid var(--border-color);
            background: rgba(255, 255, 255, 0.02);
            padding: 15px 20px;
        }
        .badge-solved {
            background-color: var(--accent-green) !important;
            color: #000 !important;
            font-weight: bold;
        }
        .glow-cyan {
            text-shadow: 0 0 10px rgba(6, 182, 212, 0.6);
        }
        .table-dark-custom {
            --bs-table-bg: transparent;
            --bs-table-color: var(--text-main);
            --bs-table-border-color: var(--border-color);
        }
        pre code {
            font-family: 'Courier New', Courier, monospace;
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark navbar-cyber sticky-top px-3 py-2">
    <div class="container-fluid">
        <a class="navbar-brand d-flex align-items-center fw-bold fs-4 text-white" href="../index.php">
            <span class="text-danger me-2 fs-3">🃏</span>
            <span class="glow-cyan text-info">مختبر الجوكر الأمني</span>
            <span class="badge bg-secondary ms-2 fs-6">v2.0 Web Sec Lab</span>
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarMain">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 align-items-lg-center">
                <!-- CTF Solved Counter -->
                <li class="nav-item me-3">
                    <span class="badge bg-dark border border-warning text-warning p-2 fs-6 shadow-sm">
                        <i class="fas fa-flag me-1"></i> الأعلام المكتشفة: 
                        <strong class="text-white"><?= $score['solved'] ?></strong> / <?= $score['total'] ?> 
                        (<?= $score['percentage'] ?>%)
                    </span>
                </li>

                <!-- Security Level Switcher -->
                <li class="nav-item dropdown me-3">
                    <button class="btn btn-sm <?= $sec_level === 'low' ? 'btn-danger' : 'btn-success' ?> dropdown-toggle fw-bold" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-shield-alt me-1"></i>
                        المستوى: <?= $sec_level === 'low' ? 'ضعيف (Vulnerable)' : 'محمي (Secure)' ?>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-dark">
                        <li>
                            <a class="dropdown-item <?= $sec_level === 'low' ? 'active' : '' ?>" href="?set_sec=low">
                                <span class="badge bg-danger me-2">Low</span> ضعيف (للتطبيق والشرح)
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item <?= $sec_level === 'secure' ? 'active' : '' ?>" href="?set_sec=secure">
                                <span class="badge bg-success me-2">Secure</span> محمي (الكود الآمن بعد الترقيع)
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>

            <div class="d-flex align-items-center gap-2">
                <a href="../docs/joker_security_lab_manual.pdf" target="_blank" class="btn btn-outline-info btn-sm fw-bold text-nowrap">
                    <i class="fas fa-file-pdf me-1 text-danger"></i> دليل المختبر PDF
                </a>
                <a href="../reset.php" class="btn btn-outline-warning btn-sm fw-bold text-nowrap">
                    <i class="fas fa-redo-alt me-1"></i> إعادة ضبط البيانات
                </a>
                <div class="d-none d-xl-inline-block text-nowrap py-1 px-3 rounded-pill bg-black border border-warning shadow-sm ms-2">
                    <span class="text-white-50 small">إعداد: </span>
                    <strong class="text-warning small">الجوكر الفلسطيني احمد سليم</strong>
                    <span class="text-danger small ms-1">🇵🇸</span>
                </div>
            </div>
        </div>
    </div>
</nav>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar Navigation -->
        <nav class="col-md-3 col-lg-2 d-md-block sidebar py-3 px-2 collapse d-md-block">
            <div class="px-3 mb-3">
                <small class="text-muted text-uppercase fw-bold"><i class="fas fa-list-check me-1"></i> قائمة الثغرات والتحديات</small>
            </div>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link <?= $current_page == 'index.php' ? 'active' : '' ?>" href="../index.php">
                        <span><i class="fas fa-home me-2 text-primary"></i> الرئيسية واللوحة</span>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link <?= $current_page == 'sqli.php' ? 'active' : '' ?>" href="sqli.php">
                        <span><i class="fas fa-database me-2 text-warning"></i> 1. حقن SQL (SQLi)</span>
                        <?php if (is_flag_solved('sqli_auth') || is_flag_solved('sqli_union')): ?>
                            <span class="badge badge-solved"><i class="fas fa-check"></i></span>
                        <?php endif; ?>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link <?= $current_page == 'xss.php' ? 'active' : '' ?>" href="xss.php">
                        <span><i class="fas fa-code me-2 text-info"></i> 2. ثغرات XSS</span>
                        <?php if (is_flag_solved('xss_reflected') || is_flag_solved('xss_stored') || is_flag_solved('xss_dom')): ?>
                            <span class="badge badge-solved"><i class="fas fa-check"></i></span>
                        <?php endif; ?>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link <?= $current_page == 'lfi.php' ? 'active' : '' ?>" href="lfi.php">
                        <span><i class="fas fa-folder-open me-2 text-danger"></i> 3. تضمين الملفات (LFI)</span>
                        <?php if (is_flag_solved('lfi')): ?>
                            <span class="badge badge-solved"><i class="fas fa-check"></i></span>
                        <?php endif; ?>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link <?= $current_page == 'upload.php' ? 'active' : '' ?>" href="upload.php">
                        <span><i class="fas fa-upload me-2 text-success"></i> 4. رفع الملفات (Upload)</span>
                        <?php if (is_flag_solved('upload')): ?>
                            <span class="badge badge-solved"><i class="fas fa-check"></i></span>
                        <?php endif; ?>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link <?= $current_page == 'cmdi.php' ? 'active' : '' ?>" href="cmdi.php">
                        <span><i class="fas fa-terminal me-2 text-danger"></i> 5. أوامر النظام (Cmd Inj)</span>
                        <?php if (is_flag_solved('cmdi')): ?>
                            <span class="badge badge-solved"><i class="fas fa-check"></i></span>
                        <?php endif; ?>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link <?= $current_page == 'idor.php' ? 'active' : '' ?>" href="idor.php">
                        <span><i class="fas fa-id-card me-2 text-warning"></i> 6. الصلاحيات (IDOR)</span>
                        <?php if (is_flag_solved('idor')): ?>
                            <span class="badge badge-solved"><i class="fas fa-check"></i></span>
                        <?php endif; ?>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link <?= $current_page == 'csrf.php' ? 'active' : '' ?>" href="csrf.php">
                        <span><i class="fas fa-random me-2 text-info"></i> 7. تزوير الطلب (CSRF)</span>
                        <?php if (is_flag_solved('csrf')): ?>
                            <span class="badge badge-solved"><i class="fas fa-check"></i></span>
                        <?php endif; ?>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link <?= $current_page == 'sensitive.php' ? 'active' : '' ?>" href="sensitive.php">
                        <span><i class="fas fa-user-secret me-2 text-secondary"></i> 8. كشف البيانات الحساسة</span>
                        <?php if (is_flag_solved('sensitive')): ?>
                            <span class="badge badge-solved"><i class="fas fa-check"></i></span>
                        <?php endif; ?>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link <?= $current_page == 'redirect.php' ? 'active' : '' ?>" href="redirect.php">
                        <span><i class="fas fa-external-link-alt me-2 text-primary"></i> 9. التوجيه المفتوح</span>
                        <?php if (is_flag_solved('redirect')): ?>
                            <span class="badge badge-solved"><i class="fas fa-check"></i></span>
                        <?php endif; ?>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link <?= $current_page == 'ssrf.php' ? 'active' : '' ?>" href="ssrf.php">
                        <span><i class="fas fa-network-wired me-2 text-warning"></i> 10. تزوير السيرفر (SSRF)</span>
                        <?php if (is_flag_solved('ssrf')): ?>
                            <span class="badge badge-solved"><i class="fas fa-check"></i></span>
                        <?php endif; ?>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link <?= $current_page == 'bruteforce.php' ? 'active' : '' ?>" href="bruteforce.php">
                        <span><i class="fas fa-key me-2 text-danger"></i> 11. التخمين (Brute Force)</span>
                        <?php if (is_flag_solved('bruteforce')): ?>
                            <span class="badge badge-solved"><i class="fas fa-check"></i></span>
                        <?php endif; ?>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link <?= $current_page == 'type_juggling.php' ? 'active' : '' ?>" href="type_juggling.php">
                        <span><i class="fas fa-balance-scale me-2 text-info"></i> 12. مقارنات PHP الضعيفة</span>
                        <?php if (is_flag_solved('type_juggling')): ?>
                            <span class="badge badge-solved"><i class="fas fa-check"></i></span>
                        <?php endif; ?>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link <?= $current_page == 'access_control.php' ? 'active' : '' ?>" href="access_control.php">
                        <span><i class="fas fa-cookie-bite me-2 text-warning"></i> 13. التلاعب بالكوكي</span>
                        <?php if (is_flag_solved('access_control')): ?>
                            <span class="badge badge-solved"><i class="fas fa-check"></i></span>
                        <?php endif; ?>
                    </a>
                </li>
            </ul>

            <div class="sidebar-flag-card mx-2 mt-auto my-3 p-3 rounded-3 bg-dark border border-secondary text-center shadow-sm">
                <div class="text-warning small fw-bold mb-2 d-flex align-items-center justify-content-center">
                    <i class="fas fa-flag-checkered text-info me-2"></i>
                    <span>تسليم علم يدوياً (Manual Flag)</span>
                </div>
                <form action="submit_flag.php" method="POST">
                    <div class="input-group input-group-sm">
                        <input type="text" name="flag_input" dir="ltr" class="form-control bg-black text-white text-center font-monospace border-secondary" placeholder="FLAG{...}" required style="letter-spacing: 1px;">
                        <button class="btn btn-info text-dark fw-bold px-3" type="submit" title="إرسال العلم">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                </form>
                <small class="text-light text-opacity-50 d-block mt-2" style="font-size: 0.72rem;">
                    أدخل كود العلم المكتشف لتسجيل النقاط
                </small>
            </div>
        </nav>

        <!-- Main Content Area -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4 d-flex flex-column" style="min-height: calc(100vh - 70px);">
            <div class="flex-grow-1">
