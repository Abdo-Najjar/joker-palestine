<?php
$page_title = "التحكم غير المباشر بالكائنات (IDOR) | مختبر الجوكر الفلسطيني";
require_once __DIR__ . '/../includes/header.php';

$db = get_db();
$sec = get_security_level();

// نعتبر أن المستخدم الحالي المسجل هو user1 (ID = 2)
$current_user_id = 2;
$current_username = "user1";

$view_id = isset($_GET['msg_id']) ? (int)$_GET['msg_id'] : 1;
$msg_data = null;
$error = "";

if ($sec === 'low') {
    // كود مصاب: جلب الرسالة برقم المعرف فقط دون فحص صاحب الجلسة
    $stmt = $db->prepare("SELECT m.*, u.username as sender_name FROM messages m LEFT JOIN users u ON m.sender_id = u.id WHERE m.id = ?");
    $stmt->execute([$view_id]);
    $msg_data = $stmt->fetch();

    if ($msg_data && strpos($msg_data['message'], 'FLAG{IDOR_Unauthorized_Access_6619}') !== false) {
        award_flag('idor');
    }
} else {
    // كود آمن: التحقق من أن الرسالة تخص المستخدم الحالي فقط
    $stmt = $db->prepare("SELECT m.*, u.username as sender_name FROM messages m LEFT JOIN users u ON m.sender_id = u.id WHERE m.id = ? AND (m.receiver_id = ? OR m.sender_id = ?)");
    $stmt->execute([$view_id, $current_user_id, $current_user_id]);
    $msg_data = $stmt->fetch();

    if (!$msg_data) {
        $error = "تم رفض الوصول! هذه الرسالة خاصة ولا تملك صلاحية عرضها (Access Denied / 403 Forbidden).";
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom border-secondary">
    <div>
        <h2 class="fw-bold text-white mb-1"><i class="fas fa-id-card text-warning me-2"></i> 6. ثغرة الإسناد المباشر غير الآمن للكائنات (IDOR)</h2>
        <p class="text-muted mb-0">تحدث ثغرة Insecure Direct Object Reference عندما يعتمد السيرفر على معرف الكائن (ID) القادم من العميل لجلب بيانات خاصة دون التحقق من صلاحية صاحب الجلسة.</p>
    </div>
    <span class="badge bg-warning text-dark fs-6 px-3 py-2"><i class="fas fa-user-lock me-1"></i> Broken Object Level Authorization</span>
</div>

<div class="card card-cyber mb-4">
    <div class="card-cyber-header d-flex justify-content-between align-items-center">
        <h4 class="mb-0 text-info"><i class="fas fa-envelope-open-text me-2"></i> صندوق الرسائل السرية (Private Messages Inbox)</h4>
        <span class="badge bg-secondary">تحدي IDOR</span>
    </div>
    <div class="card-body p-4">
        <div class="alert alert-dark border-secondary d-flex justify-content-between align-items-center py-2 mb-4">
            <div>
                <i class="fas fa-user text-info me-2"></i> أنت مسجل الدخول حالياً بحساب: <strong class="text-warning"><?= $current_username ?></strong> (معرف المستخدم: <code>#<?= $current_user_id ?></code>)
            </div>
            <span class="badge bg-primary">طالب</span>
        </div>

        <p class="text-light">
            <strong>الهدف:</strong> أنت كطالب تمتلك حق قراءة رسائلك فقط (مثل الرسالة <code>#1</code>)، ولكن الإدارة تمتلك رسائل سرية للغاية تحتوي على العلم. استغل ثغرة IDOR في الرابط لقراءة رسائل الإدارة السرية.
        </p>

        <?= render_flag_box('idor'); ?>

        <div class="mb-3 d-flex gap-2">
            <a href="idor.php?msg_id=1" class="btn btn-outline-info btn-sm fw-bold">رسالتي العادية (#1)</a>
            <a href="idor.php?msg_id=3" class="btn btn-outline-info btn-sm fw-bold">استفساري للمشرف (#3)</a>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger py-2"><?= $error ?></div>
        <?php endif; ?>

        <?php if ($msg_data): ?>
            <div class="card bg-dark border-secondary mt-3">
                <div class="card-header bg-black text-white d-flex justify-content-between align-items-center">
                    <div>
                        <span class="badge bg-warning text-dark me-2">رسالة رقم #<?= $msg_data['id'] ?></span>
                        <strong class="text-info"><?= htmlspecialchars($msg_data['subject']) ?></strong>
                    </div>
                    <small class="text-muted">المرسل: <?= htmlspecialchars($msg_data['sender_name']) ?> | التاريخ: <?= htmlspecialchars($msg_data['created_at']) ?></small>
                </div>
                <div class="card-body p-3">
                    <p class="mb-0 text-white fs-6"><?= nl2br(htmlspecialchars($msg_data['message'])) ?></p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?= render_hints([
    "انظر إلى رابط المتصفح: <code>idor.php?msg_id=1</code>.",
    "جرب تغيير رقم الرسالة إلى رقم آخر لم يظهر في قائمتك، مثل: <code>idor.php?msg_id=2</code>.",
    "ستلاحظ أنك استطعت قراءة رسالة موجهة للإدارة وتحتوي على العلم بكل سهولة!"
]); ?>

<?= render_code_comparison(
    '// كود مصاب\n$msg_id = $_GET["msg_id"];\n$stmt = $db->query("SELECT * FROM messages WHERE id = $msg_id");\n$msg = $stmt->fetch();',
    '// كود آمن\n$msg_id = (int)$_GET["msg_id"];\n$user_id = $_SESSION["user_id"];\n$stmt = $db->prepare("SELECT * FROM messages WHERE id = ? AND (receiver_id = ? OR sender_id = ?)");\n$stmt->execute([$msg_id, $user_id, $user_id]);\n$msg = $stmt->fetch();\nif (!$msg) die("Unauthorized!");',
    'التحقق من الصلاحيات (Access Control Check) يجب أن يتم في جانب السيرفر مع كل طلب، عبر مطابقة معرف المستخدم المخزن في الجلسة (Session) مع صاحب السجل المطلوب في قاعدة البيانات.'
); ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
