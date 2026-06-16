<?php
/**
 * TradeNexa.com - Billing Subscription checkout
 * Processes ledger charges and updates profile credentials securely
 */

require_once dirname(dirname(dirname(__DIR__))) . '/app/services/subscription_service.php';
require_once dirname(dirname(dirname(__DIR__))) . '/app/core/saas.php';
require_once dirname(dirname(dirname(__DIR__))) . '/app/core/auth.php';
require_once dirname(dirname(dirname(__DIR__))) . '/app/core/security.php';

$curr_user = auth_current_user();
$uid = intval($curr_user['id']);

$plan_key = isset($_GET['plan']) ? strtolower(trim($_GET['plan'])) : '';
$plans = saas_get_pricing_plans();

if (empty($plan_key) || !isset($plans[$plan_key])) {
    set_flash_message('error', "Invalid subscription plan selection.");
    header("Location: index.php?page=billing/plans");
    exit;
}

$plan_details = $plans[$plan_key];
$error = '';
$success = false;

// Handle subscription purchase order post
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!validate_csrf_token($csrf)) {
        $error = "CSRF verification failed. Try again.";
    } else {
         $res = subscription_upgrade_plan($uid, $plan_key);
         if ($res['success']) {
              set_flash_message('success', $res['message']);
              header("Location: index.php?page=user/profile");
              exit;
         } else {
              $error = $res['message'];
         }
    }
}
?>

<div class="mb-5 bg-slate-900 border border-slate-800 p-4 rounded-3xl">
    <h2 class="text-sm font-black text-slate-100 uppercase tracking-widest">Upgrade Checkout</h2>
    <span class="text-[10px] text-slate-500 font-mono">Verify order parameters</span>
</div>

<?php if (!empty($error)): ?>
    <div class="bg-rose-950/40 border border-rose-900 rounded-xl p-3.5 mb-4 text-xs text-rose-300">
        <?= secure_escape($error) ?>
    </div>
<?php endif; ?>

<!-- Order Summary Card -->
<div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 text-xs">
    <h3 class="text-xs font-black uppercase text-slate-350 tracking-wider mb-4 border-b border-slate-950 pb-2">Purchase Review</h3>
    
    <div class="space-y-3 font-mono text-[11px]">
        <div class="flex justify-between border-b border-slate-950/40 pb-2">
            <span class="text-slate-500">Selected Product:</span>
            <span class="font-bold text-slate-200"><?= secure_escape($plan_details['name']) ?></span>
        </div>
        <div class="flex justify-between border-b border-slate-950/40 pb-2">
            <span class="text-slate-500">Subscription Period:</span>
            <span class="font-bold text-slate-200">Monthly</span>
        </div>
        <div class="flex justify-between border-b border-slate-950/40 pb-2">
            <span class="text-slate-500">Purchase Rate:</span>
            <span class="font-bold text-emerald-400 font-black">$<?= secure_escape($plan_details['price']) ?></span>
        </div>
    </div>

    <!-- Confirm Upgrade Post -->
    <form method="POST" action="" class="mt-6 pt-2">
        <?php csrf_input(); ?>
        <button type="submit" class="w-full bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold py-3 px-4 rounded-xl shadow-lg transition cursor-pointer select-none">
            Approve & Debit Ledger
        </button>
        <p class="text-[9px] text-slate-500 text-center mt-2.5 leading-snug">
            Your simulation wallet balance will be debited. Subscription features unlock instantly. Zero real currencies processed.
        </p>
    </form>
</div>

<div class="mt-4">
    <a href="index.php?page=billing/plans" class="block w-full text-center text-xs font-bold py-3 border border-slate-800 bg-slate-950 hover:bg-slate-900 text-slate-400 rounded-xl transition">
        Cancel & Back
    </a>
</div>
