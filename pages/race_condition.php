<?php
// ==============================================================================
// مشروع: مختبر الجوكر الفلسطيني لاختبار اختراق تطبيقات الويب (Joker Security Lab)
// التحدي 18: سباق العمليات والتكرار الزمني (Race Condition / TOCTOU)
// إعداد وتطوير: المهندس احمد سليم 🇵🇸
// ==============================================================================

$page_title = "سباق العمليات (Race Condition) | مختبر الجوكر الفلسطيني";
require_once __DIR__ . '/../includes/header.php';

$sec = get_security_level();

// تهيئة بيانات المحفظة والكوبون في الجلسة
if (!isset($_SESSION['wallet_balance'])) {
    $_SESSION['wallet_balance'] = 50; // رصيد البدء: 50 دولار
}

if (!isset($_SESSION['coupon_used'])) {
    $_SESSION['coupon_used'] = false;
}

if (!isset($_SESSION['redemption_count'])) {
    $_SESSION['redemption_count'] = 0;
}

$active_coupon = "JOKER-GIFT-50";

// معالجة طلب إعادة تعيين المحفظة
if (isset($_POST['reset_wallet'])) {
    $_SESSION['wallet_balance'] = 50;
    $_SESSION['coupon_used'] = false;
    $_SESSION['redemption_count'] = 0;
    $msg = "تمت إعادة تعيين رصيد المحفظة إلى $50 والكوبون متاح مجدداً.";
}

// معالجة طلب صرف الكوبون عبر AJAX أو نموذج POST
if (isset($_POST['redeem_coupon'])) {
    $code = trim($_POST['coupon_code'] ?? '');
    $is_ajax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) || (isset($_POST['ajax']) && $_POST['ajax'] === '1');

    $response = [
        'success' => false,
        'message' => '',
        'balance' => $_SESSION['wallet_balance'],
        'count' => $_SESSION['redemption_count']
    ];

    if ($code !== $active_coupon) {
        $response['message'] = "رمز الكوبون غير صحيح!";
    } else {
        if (!empty($_POST['burst_attack']) || !empty($_GET['burst'])) {
            if ($sec === 'low') {
                // محاكاة نجاح حزمة الطلبات المتزامنة في تجاوز نافذة الفحص
                $_SESSION['wallet_balance'] += 150;
                $_SESSION['redemption_count'] += 3;
                $flag = award_flag('race_condition');

                $response['success'] = true;
                $response['message'] = "💥 [نجاح هجوم Race Condition]: اجتازت الطلبات المتزامنة الفحص في نفس الميلي ثانية! تم مضاعفة الرصيد إلى $" . number_format($_SESSION['wallet_balance'], 2);
                $response['balance'] = $_SESSION['wallet_balance'];
                $response['count'] = $_SESSION['redemption_count'];
                $response['flag_unlocked'] = true;
                $response['flag'] = $flag;
            } else {
                if (!$_SESSION['coupon_used']) {
                    $_SESSION['coupon_used'] = true;
                    $_SESSION['wallet_balance'] += 50;
                    $_SESSION['redemption_count'] = 1;
                    $response['success'] = true;
                    $response['message'] = "تم صرف الكوبون مرة واحدة فقط ($50). تم صد كافة الطلبات المتزامنة الأخرى بنجاح عبر القفل الذري (Atomic Lock).";
                    $response['balance'] = $_SESSION['wallet_balance'];
                    $response['count'] = $_SESSION['redemption_count'];
                } else {
                    $response['message'] = "⛔ تم حظر كافة الطلبات المتزامنة! الكوبون مستهلك مسبقاً (Atomic Locking Blocked Concurrency).";
                }
            }
        } elseif ($sec === 'low') {
            // ❌ كود مصاب: فحص الشرط متبوعاً بتأخير زمني دون قفل تزامني (TOCTOU Race Condition)
            if (!$_SESSION['coupon_used']) {
                // محاكاة تأخير معالجة قاعدة البيانات أو الدفع الخارجي (120ms)
                usleep(120000);

                // صرف الكوبون وإضافة الرصيد
                $_SESSION['wallet_balance'] += 50;
                $_SESSION['redemption_count']++;
                $_SESSION['coupon_used'] = true;

                $response['success'] = true;
                $response['message'] = "تم صرف الكوبون بنجاح! تم إضافة $50 للمحفظة.";
                $response['balance'] = $_SESSION['wallet_balance'];
                $response['count'] = $_SESSION['redemption_count'];

                // إذا تم صرف الكوبون أكثر من مرة واحدة بسبب هجوم التزامن
                if ($_SESSION['redemption_count'] > 1 || $_SESSION['wallet_balance'] > 100) {
                    award_flag('race_condition');
                    $response['flag_unlocked'] = true;
                }
            } else {
                $response['message'] = "عذراً! هذا الكوبون تم استخدامه بالفعل.";
            }
        } else {
            // ✅ كود محمي: التحقق والقفل الذري الفوري (Atomic Check & Set) لمنع هجمات التزامن
            // يتم حجز الكوبون فوراً قبل أي تأخير أو استدعاء خارجي
            if (!$_SESSION['coupon_used']) {
                // قفل فوري ذري
                $_SESSION['coupon_used'] = true;
                $_SESSION['wallet_balance'] += 50;
                $_SESSION['redemption_count']++;

                $response['success'] = true;
                $response['message'] = "تم صرف الكوبون بأمان تام عبر القفل الذري (Atomic Transaction).";
                $response['balance'] = $_SESSION['wallet_balance'];
                $response['count'] = $_SESSION['redemption_count'];
            } else {
                $response['message'] = "⛔ تم حظر الطلب المتزامن! الكوبون مستهلك مسبقاً (Atomic Locking Blocked Concurrency).";
            }
        }
    }

    if ($is_ajax) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($response);
        exit;
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom border-secondary">
    <div>
        <h2 class="fw-bold text-white mb-1">
            <i class="fas fa-stopwatch text-warning me-2"></i> 18. سباق العمليات والطلبات المتزامنة (Race Condition / TOCTOU)
        </h2>
        <p class="text-muted mb-0">
            تحدث ثغرة Race Condition (CWE-362) عندما لا تضمن التطبيقات الحصانة والتزامن الذري (Atomic Synchronization) للعمليات المشتركة، مما يسمح بإرسال طلبات متعددة في نفس جزء من الثانية لتجاوز القيود وتكرار المكافآت أو السحب المالي.
        </p>
    </div>
    <span class="badge bg-danger fs-6 px-3 py-2">
        <i class="fas fa-bolt me-1"></i> ثغرات المنطق البرمجي (Business Logic Flaws)
    </span>
</div>

<!-- Architecture Flow Diagram Card -->
<div class="card card-cyber mb-4">
    <div class="card-cyber-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0 text-info"><i class="fas fa-project-diagram me-2"></i> مسار استغلال سباق العمليات (Time-of-Check to Time-of-Use Flow)</h5>
        <span class="badge bg-dark border border-info text-info">TOCTOU Race Window</span>
    </div>
    <div class="card-body p-4 text-center">
        <div class="row align-items-center g-3">
            <div class="col-md-3">
                <div class="p-3 rounded bg-black border border-warning text-center h-100">
                    <span class="badge bg-warning text-dark mb-2">1. إرسال حزمة طلبات متزامنة</span>
                    <h6 class="text-warning fw-bold mb-1">5 طلبات في نفس اللحظة</h6>
                    <small class="text-light text-opacity-75">المهاجم يرسل طلبات صرف الكوبون بالتوازي في نفس الميلي ثانية (Multi-threaded Burst)</small>
                </div>
            </div>
            <div class="col-md-1 d-flex justify-content-center">
                <i class="fas fa-arrow-left text-info fs-3 d-none d-md-block"></i>
                <i class="fas fa-arrow-down text-info fs-3 d-md-none"></i>
            </div>
            <div class="col-md-4">
                <div class="p-3 rounded bg-black border border-danger text-center h-100">
                    <span class="badge bg-danger mb-2">2. نافذة السباق (Race Window)</span>
                    <h6 class="text-danger fw-bold mb-1">كل الطلبات تجتاز الفحص معاً!</h6>
                    <small class="text-light text-opacity-75">قبل أن يتم تحديث <code>used = true</code>، تقرأ كافة الخيوط (Threads) أن الكوبون صالح ومتاح!</small>
                </div>
            </div>
            <div class="col-md-1 d-flex justify-content-center">
                <i class="fas fa-arrow-left text-success fs-3 d-none d-md-block"></i>
                <i class="fas fa-arrow-down text-success fs-3 d-md-none"></i>
            </div>
            <div class="col-md-3">
                <div class="p-3 rounded bg-black border border-success text-center h-100">
                    <span class="badge bg-success mb-2">3. مضاعفة الرصيد المالي</span>
                    <h6 class="text-success fw-bold mb-1">صرف متكرر لنفس الكوبون</h6>
                    <small class="text-light text-opacity-75">يتم إضافة الرصيد 5 مرات وتتجاوز المحفظة الحد المسموح واقتناص العلم!</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Interactive Challenge Card -->
<div class="card card-cyber mb-4">
    <div class="card-cyber-header d-flex justify-content-between align-items-center">
        <h4 class="mb-0 text-info"><i class="fas fa-wallet me-2"></i> بوابة صرف كوبونات المكافآت والمحفظة الرقمية (Digital Wallet)</h4>
        <span class="badge bg-secondary">تحدي Race Condition</span>
    </div>
    <div class="card-body p-4">
        <p class="text-light">
            <strong>الهدف:</strong> الكوبون <code><?= $active_coupon ?></code> مخصص للاستخدام <strong>مرة واحدة فقط</strong> لإضافة $50. قم باستغلال نافذة التزامن في المستوى الضعيف وصرف الكوبون عدة مرات بالتوازي لرفع الرصيد فوق $100 واقتناص راية العلم.
        </p>

        <?= render_flag_box('race_condition'); ?>

        <!-- Wallet Status Dashboard -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="p-3 rounded bg-black border border-secondary text-center">
                    <small class="text-muted d-block mb-1">الرصيد الحالي بالمحفظة</small>
                    <h2 class="text-success fw-bold mb-0 font-monospace" id="wallet-balance-display">
                        $<?= number_format($_SESSION['wallet_balance'], 2) ?>
                    </h2>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3 rounded bg-black border border-secondary text-center">
                    <small class="text-muted d-block mb-1">حالة الكوبون الحالي</small>
                    <h4 class="mb-0 font-monospace" id="coupon-status-display">
                        <?php if ($_SESSION['coupon_used']): ?>
                            <span class="badge bg-danger">مستهلك (Used)</span>
                        <?php else: ?>
                            <span class="badge bg-success">متاح للصرف (Valid)</span>
                        <?php endif; ?>
                    </h4>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3 rounded bg-black border border-secondary text-center">
                    <small class="text-muted d-block mb-1">عدد مرات الصرف المسجلة</small>
                    <h2 class="text-info fw-bold mb-0 font-monospace" id="redemption-count-display">
                        <?= $_SESSION['redemption_count'] ?>
                    </h2>
                </div>
            </div>
        </div>

        <!-- Action Controls -->
        <div class="row g-4">
            <div class="col-lg-6">
                <div class="p-3 rounded bg-dark border border-secondary h-100">
                    <h5 class="text-warning fw-bold mb-3">
                        <i class="fas fa-ticket-alt me-1"></i> صرف الكوبون (Coupon Redemption):
                    </h5>
                    
                    <div class="mb-3">
                        <label class="form-label text-light small">كود الكوبون المتاح (قيمة $50):</label>
                        <input type="text" id="coupon-code-input" class="form-control font-monospace bg-black text-warning border-secondary text-center fw-bold fs-5" value="<?= $active_coupon ?>" readonly>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-outline-info fw-bold py-2" onclick="sendSingleRequest()">
                            <i class="fas fa-paper-plane me-1"></i> إرسال طلب صرف عادي (Single Request)
                        </button>
                        
                        <button type="button" class="btn btn-danger fw-bold py-2 shadow" onclick="triggerRaceAttack()">
                            <i class="fas fa-bolt me-1"></i> ⚡ إطلاق 5 طلبات متزامنة في نفس اللحظة (Race Condition Burst)
                        </button>
                    </div>

                    <form method="POST" class="mt-3">
                        <button type="submit" name="reset_wallet" class="btn btn-outline-secondary btn-sm w-100">
                            <i class="fas fa-redo-alt me-1"></i> إعادة تعيين المحفظة والكوبون للبداية ($50)
                        </button>
                    </form>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card bg-dark border-secondary h-100">
                    <div class="card-header bg-black text-white d-flex justify-content-between align-items-center py-2">
                        <span class="small fw-bold"><i class="fas fa-terminal text-info me-1"></i> سجل استجابات الطلبات المتزامنة (Live Concurrency Log):</span>
                        <button class="btn btn-outline-secondary btn-sm py-0 px-2" onclick="document.getElementById('race-logs').innerHTML = '';">مسح</button>
                    </div>
                    <div class="card-body p-3">
                        <div id="race-logs" class="p-2 rounded bg-black border border-secondary font-monospace small" style="min-height: 220px; max-height: 260px; overflow-y: auto; direction:ltr; text-align:left;">
                            <span class="text-muted">// جاهز لإرسال الطلبات... اضغط على زر الصرف العادي أو المتزامن.</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= render_hints([
    "في الأنظمة المالية وتطبيقات الويب، يحدث السباق الزمني بين لحظة فحص البيانات (Check) ولحظة تسجيل النتيجة (Update).",
    "عند إرسال طلب واحد، يتحقق السيرفر من أن الكوبون غير مستخدم، فيضيف $50 ثم يضع علامة <code>used = true</code>.",
    "لكن إذا أرسلت 5 طلبات في نفس الميلي ثانية بالتوازي (Parallel Requests)، فإن كافة الطلبات تجتاز شرط <code>!used</code> قبل أن يسبق أي منها لتحديث حالة الكوبون!",
    "اضغط على زر <strong>(⚡ إطلاق 5 طلبات متزامنة)</strong> لمشاهدة نجاح أكثر من طلب ومضاعفة الرصيد واقتناص العلم."
]); ?>

<?= render_code_comparison(
    '// ❌ كود مصاب: فحص الكوبون وتأخير قبل التحديث (TOCTOU Flaw)\nif (!$coupon["is_used"]) {\n    // استدعاء بوابة الدفع أو انتظار خارجي\n    process_payment_delay();\n    $wallet += 50;\n    $coupon["is_used"] = true;\n}',
    '// ✅ كود محمي: قفل ذري أو معاملة مصرفية ذرية (Atomic Transaction)\n$db->beginTransaction();\n// تحديث فوري مشروط في قاعدة البيانات\n$stmt = $db->prepare("UPDATE coupons SET is_used = 1 WHERE code = ? AND is_used = 0");\n$stmt->execute([$code]);\nif ($stmt->rowCount() === 1) {\n    $wallet += 50;\n    $db->commit();\n} else {\n    $db->rollBack();\n    die("Coupon already redeemed!");\n}',
    'لمنع هجمات Race Condition، يجب استخدام المعاملات الذرية (Atomic DB Transactions)، وأقفال الصفوف (Row-Level Locking مثل SELECT ... FOR UPDATE)، ومزامنة الموارد المشتركة عبر Redis Locks أو أقفال الـ Mutex.'
); ?>

<script>
function appendLog(text, color = 'text-light') {
    const logBox = document.getElementById('race-logs');
    const entry = document.createElement('div');
    entry.className = color + ' mb-1';
    entry.innerHTML = `[${new Date().toLocaleTimeString()}] ${text}`;
    logBox.appendChild(entry);
    logBox.scrollTop = logBox.scrollHeight;
}

function updateDisplays(data) {
    if (data.balance !== undefined) {
        document.getElementById('wallet-balance-display').innerText = '$' + parseFloat(data.balance).toFixed(2);
    }
    if (data.count !== undefined) {
        document.getElementById('redemption-count-display').innerText = data.count;
    }
    const statusBox = document.getElementById('coupon-status-display');
    if (data.count > 0) {
        statusBox.innerHTML = '<span class="badge bg-danger">مستهلك (Used)</span>';
    }
    if (data.flag_unlocked) {
        setTimeout(() => location.reload(), 1200);
    }
}

function sendSingleRequest() {
    appendLog("إرسال طلب صرف عادي (Single HTTP POST)...", "text-info");
    const formData = new FormData();
    formData.append('redeem_coupon', '1');
    formData.append('coupon_code', '<?= $active_coupon ?>');
    formData.append('ajax', '1');

    fetch('race_condition.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            appendLog((data.success ? '✅ ' : '❌ ') + data.message, data.success ? 'text-success' : 'text-danger');
            updateDisplays(data);
        })
        .catch(err => appendLog("Network Error: " + err, "text-danger"));
}

function triggerRaceAttack() {
    appendLog("🚀 بدء هجوم Race Condition: إطلاق 5 طلبات متزامنة في نفس اللحظة...", "text-warning fw-bold");
    
    const requests = [];
    for (let i = 1; i <= 5; i++) {
        const formData = new FormData();
        formData.append('redeem_coupon', '1');
        formData.append('coupon_code', '<?= $active_coupon ?>');
        formData.append('burst_attack', '1');
        formData.append('ajax', '1');

        requests.push(
            fetch('race_condition.php', { method: 'POST', body: formData })
                .then(r => r.json())
                .then(data => {
                    const icon = data.success ? '💥 [نجاح متزامن #'+i+'] ' : '⛔ [مرفوض #'+i+'] ';
                    appendLog(icon + data.message, data.success ? 'text-success fw-bold' : 'text-danger');
                    return data;
                })
        );
    }

    Promise.all(requests).then(results => {
        const last = results[results.length - 1];
        if (last) updateDisplays(last);
        appendLog("🏁 اكتملت حزمة الطلبات المتزامنة.", "text-info");
    });
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
