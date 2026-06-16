<?php
/**
 * TradeNexa.com - Users - Profile & Memberships Manager
 * Visualizes active subscriptions and permits direct simulated deposit triggers as required
 */

require_once dirname(dirname(dirname(__DIR__))) . '/app/core/auth.php';
require_once dirname(dirname(dirname(__DIR__))) . '/app/core/saas.php';
require_once dirname(dirname(dirname(__DIR__))) . '/app/services/subscription_service.php';

$user = auth_current_user();
$tier = saas_get_current_tier();
$plans = saas_get_pricing_plans();

// Handle mock deposit claim inputs
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['claim_credits'])) {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!validate_csrf_token($csrf)) {
        set_flash_message('error', "CSRF security checks failed. Attempt aborted.");
    } else {
        $claim_amount = 250.00;
        $res = subscription_deposit_mock_funds($user['id'], $claim_amount);
        if ($res['success']) {
            set_flash_message('success', "Mock injection complete! Claimed \$250.00 in test tokens.");
            header("Location: index.php?page=user/profile");
            exit;
        } else {
            set_flash_message('error', $res['message']);
        }
    }
}
?>

<div class="mb-5 bg-slate-900 border border-slate-800 rounded-3xl p-5 flex items-center gap-4 relative overflow-hidden">
    <!-- Accent backdrop -->
    <div class="absolute -top-12 -right-12 w-28 h-28 bg-emerald-500/5 rounded-full blur-xl"></div>
    
    <div class="w-12 h-12 bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 rounded-2xl flex items-center justify-center font-bold text-lg">
        <?= strtoupper(substr($user['email'], 0, 1)) ?>
    </div>
    
    <div class="flex-1 pr-6 truncate">
        <h3 class="font-bold text-base text-slate-100 truncate"><?= secure_escape($user['email']) ?></h3>
        <span class="text-[10px] text-slate-500 font-mono">Registered: <?= date('Y-m-d', strtotime($user['created_at'])) ?></span>
    </div>
</div>

<!-- Ledger Portfolio Brief -->
<div class="mb-5 bg-slate-900 border border-slate-800 rounded-3xl p-5">
    <span class="text-[9px] text-slate-500 uppercase tracking-widest font-semibold block">Simulated Wallet Balance</span>
    <div class="flex items-baseline justify-between mt-1">
        <h2 class="font-mono text-2xl font-black text-emerald-400">$<?= number_format(floatval($user['wallet_balance']), 2) ?></h2>
        
        <form method="POST" action="">
            <?php csrf_input(); ?>
            <button type="submit" name="claim_credits" value="1" class="text-[10px] font-bold bg-slate-950 hover:bg-slate-800 border border-slate-800 hover:border-slate-700 text-slate-300 px-3 py-1.5 rounded-xl cursor-pointer select-none transition">
                + Claim Free $250
            </button>
        </form>
    </div>
    <p class="text-[9px] text-slate-500 leading-relaxed mt-2.5">
        Ledger ledger balances are simulated for sandbox evaluation. Claim free test tokens to acquire Pro or VIP subscriptions.
    </p>
</div>

<!-- Membership Subscription Plans -->
<div class="mb-5">
    <h3 class="text-xs font-semibold text-slate-400 uppercase tracking-widest mb-3.5">TradeNexa Licensing Plans</h3>
    
    <div class="space-y-4">
        <?php foreach ($plans as $plan_key => $p): ?>
            <div class="border rounded-2xl p-4 relative overflow-hidden transition
                <?php if ($tier === $plan_key): ?>
                    bg-slate-900 border-indigo-500
                <?php else: ?>
                    bg-slate-900/60 border-slate-800/80
                <?php endif; ?>">
                
                <!-- Active badge -->
                <?php if ($tier === $plan_key): ?>
                    <span class="absolute top-3 right-3 text-[9px] font-bold text-indigo-400 uppercase bg-indigo-500/10 border border-indigo-500/20 px-2 py-0.5 rounded-full">Active Plan</span>
                <?php endif; ?>

                <h4 class="font-black text-sm text-slate-200"><?= secure_escape($p['name']) ?></h4>
                <div class="mt-1.5 flex items-baseline gap-1">
                    <span class="font-mono text-base font-black text-slate-100">$<?= secure_escape($p['price']) ?></span>
                    <span class="text-[10px] text-slate-500">/ month (simulated)</span>
                </div>

                <ul class="mt-3.5 space-y-1.5 border-t border-slate-800/60 pt-3">
                    <?php foreach ($p['features'] as $f): ?>
                        <li class="text-[10px] text-slate-400 flex items-center gap-1.5">
                            <span class="text-emerald-500">✓</span>
                            <span><?= secure_escape($f) ?></span>
                        </li>
                    <?php endstyle; ?>
                </ul>

                <?php if ($tier !== $plan_key): ?>
                    <div class="mt-4">
                        <a href="index.php?page=billing/plans&upgrade=<?= $plan_key ?>" class="block w-full text-center text-xs font-bold py-2.5 px-3 rounded-xl border border-slate-800 bg-slate-950 hover:bg-slate-900 text-slate-300 hover:text-slate-100 cursor-pointer select-none transition">
                            Buy/Upgrade License
                        </a>
                    </div>
                <?php endif; ?>

            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Extra Utilities -->
<div class="space-y-2">
    <a href="index.php?page=user/wallet" class="block w-full bg-slate-900 border border-slate-800 hover:border-slate-700 p-3.5 rounded-2xl text-xs font-bold text-slate-300 flex items-center justify-between cursor-pointer">
        <span>🧾 View Wallet Ledger Record Book</span>
        <span>→</span>
    </a>
    
    <a href="index.php?page=auth/logout" class="block w-full bg-rose-950/20 border border-rose-900/30 hover:border-rose-900/50 p-3.5 rounded-2xl text-xs font-bold text-rose-400 flex items-center justify-between cursor-pointer">
        <span>🚪 Sign Out of Platform Safely</span>
        <span>→</span>
    </a>
</div>
