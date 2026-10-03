<?php
// ==============================================================================
// معالجة تسليم الأعلام (Flag Verification & Submission)
// إعداد: الجوكر الفلسطيني احمد سليم
// ==============================================================================

require_once __DIR__ . '/../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['flag_input'])) {
    $submitted = trim($_POST['flag_input']);
    $matched_key = null;

    foreach ($CHALLENGE_FLAGS as $key => $info) {
        if ($info['flag'] === $submitted) {
            $matched_key = $key;
            break;
        }
    }

    if ($matched_key) {
        award_flag($matched_key, true);
        $_SESSION['celebration'] = [
            'key' => $matched_key,
            'title' => $CHALLENGE_FLAGS[$matched_key]['title'],
            'flag' => $CHALLENGE_FLAGS[$matched_key]['flag'],
            'is_new' => true
        ];
        $_SESSION['flash_msg'] = [
            'type' => 'success',
            'text' => "🎉 أحسنت صنعاً! تم قبول العلم الخاص بتحدي: <strong>{$CHALLENGE_FLAGS[$matched_key]['title']}</strong> بنجاح!"
        ];
    } else {
        $_SESSION['flash_msg'] = [
            'type' => 'danger',
            'text' => "❌ عذراً، العلم الذي أدخلته غير صحيح! تأكد من النص وحاول مجدداً."
        ];
    }
}

$ref = $_SERVER['HTTP_REFERER'] ?? '../index.php';
header("Location: $ref");
exit;
