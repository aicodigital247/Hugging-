<?php
/**
 * TradeNexa.com - Billing Plans Table
 * Lists membership packages, details, and lets users trigger upgrade orders
 */

require_once dirname(dirname(dirname(__DIR__))) . '/app/core/saas.php';
require_once dirname(dirname(dirname(__DIR__))) . '/app/core/auth.php';

$tier = saas_get_current_tier();
$plans = saas_get_pricing_plans();
$user = auth_current_user();

$upgrade_target = isset($_GET['upgrade']) ? strtolower(trim($_GET['upgrade'])) : '';

// If a target upgrade value is provided, auto-redirect or allow confirming the action
if (!empty($upgrade_target) && isset($plans[$upgrade_target])) {
     header("Location: index.php?page=billing/subscribe&plan=" . urlencode($upgrade_target));
     exit;
}
?>

<div class="mb-5 bg-gradient-to-tr from-slate-900 to-indigo-950/40 border border-slate-800 p-5 rounded-3xl text-xs">
    <h2 class="text-sm font-black text-slate-100 uppercase tracking-widest mb-1.5">TradeNexa Licensing Plans</h2>
    <p class="text-slate-400 leading-relaxed">Upgrade your license level to unlock advanced features. Your simulated credit wallet balance will be debited according to specifications.</p>
    
    <div class="mt-4 flex items-center justify-between bg-slate-950/50 p-3 rounded-2xl border border-slate-850">
        <span class="text-slate-500 font-mono">Available Wallet Credits:</span>
        <span class="font-mono font-black text-emerald-400">$<?= number_format(floatval($user['wallet_balance'] ?? 0.00), 2) ?></span>
    </div>
</div>

<div class="space-y-4">
    <?php foreach ($plans as $p_key => $p): ?>
        <div class="border rounded-3xl p-5 relative overflow-hidden transition
            <?php if ($tier === $p_key): ?>
                bg-slate-900 border-indigo-500
            <?php else: ?>
                bg-slate-900/60 border-slate-800/80
            <?php endif; ?>">
            
            <?php if ($tier === $p_key): ?>
                <span class="absolute top-4 right-4 text-[9px] font-bold text-indigo-400 uppercase bg-indigo-500/10 border border-indigo-500/20 px-2.5 py-0.5 rounded-full">Active Plan</span>
            <?php endif; ?>

            <h3 class="font-black text-sm text-slate-200"><?= secure_escape($p['name']) ?></h3>
            <div class="mt-1 flex items-baseline gap-1">
                <span class="font-mono text-base font-black text-slate-100">$<?= secure_escape($p['price']) ?></span>
                <span class="text-[10px] text-slate-500">/ month (simulated)</span>
            </div>

            <p class="text-[10px] text-indigo-300 font-semibold mt-3 bg-indigo-950/20 p-2 rounded-xl border border-indigo-950/30">
                ⭐ Core benefits: <?= secure_escape($p['signals']) ?>
            </p>

            <ul class="mt-4 space-y-1.5 border-t border-slate-800/60 pt-3.5">
                <?php foreach ($p['features'] as $f): ?>
                    <li class="text-[10px] text-slate-400 flex items-center gap-1.5">
                        <span class="text-emerald-400 text-xs">✓</span>
                        <span><?= secure_escape($f) ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>

            <div class="mt-5">
                <?php if ($tier === $p_key): ?>
                    <button disabled class="w-full text-center text-xs font-bold py-3 px-4 rounded-xl border border-slate-800 bg-slate-950 text-slate-600 cursor-not-allowed select-none transition">
                        License Active
                    </button>
                <?php else: ?>
                    <a href="index.php?page=billing/subscribe&plan=<?= secure_escape($p_key) ?>" class="block w-full text-center text-xs font-bold py-3 px-4 rounded-xl border bg-indigo-600 hover:bg-indigo-500 text-white shadow-lg transition cursor-pointer select-none">
                        Acquire <?= secure_escape($p['name']) ?>
                    </a>
                <?php endif; ?>
            </div>

        </div>
    <?php endforeach; ?>
</div>
