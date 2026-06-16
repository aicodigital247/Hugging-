<?php
/**
 * TradeNexa.com - Users - Main Dashboard Cockpit
 * Lists ledger histories, signals, strategy indicators and subscription tiers
 */

require_once dirname(dirname(dirname(__DIR__))) . '/app/core/auth.php';
require_once dirname(dirname(dirname(__DIR__))) . '/app/core/saas.php';
require_once dirname(dirname(dirname(__DIR__))) . '/app/core/db.php';

$curr_user = auth_current_user();
$uid = intval($curr_user['id']);

$ledgers = [];
if ($conn) {
    $res = mysqli_query($conn, "SELECT * FROM `wallet_ledger` WHERE `user_id` = $uid ORDER BY `timestamp` DESC LIMIT 3");
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $ledgers[] = $row;
        }
    }
}
?>

<div class="mb-5">
    <div class="bg-gradient-to-br from-indigo-900/40 via-slate-900 to-slate-900 border border-slate-800 rounded-3xl p-5 shadow-lg relative overflow-hidden">
        <div class="absolute top-0 right-0 w-36 h-36 bg-indigo-500/5 rounded-full blur-2xl"></div>
        
        <span class="text-[9px] font-bold text-indigo-400 bg-indigo-500/10 border border-indigo-500/20 px-2 py-0.5 rounded-full uppercase tracking-wider">Account Active</span>
        <h2 class="text-xl font-extrabold text-slate-100 mt-2 truncate">Hello, <?= secure_escape($curr_user['email']) ?></h2>
        <p class="text-xs text-slate-400 mt-1 leading-relaxed">Logged in since <?= date('Y-m-d') ?>. You are currently tracking real-time linear perpetual indicators.</p>
        
        <div class="mt-4 grid grid-cols-2 gap-3.5 border-t border-slate-800/80 pt-4">
            <div>
                <span class="text-[9px] text-slate-500 uppercase tracking-widest font-semibold">Simulation Ledger</span>
                <p class="font-mono text-lg font-black text-emerald-400 mt-0.5">$<?= number_format(floatval($curr_user['wallet_balance']), 2) ?></p>
            </div>
            <div>
                <span class="text-[9px] text-slate-500 uppercase tracking-widest font-semibold">Active Plan tier</span>
                <p class="text-sm font-bold text-indigo-300 mt-1 uppercase"><?= strtoupper(saas_get_current_tier()) ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Ledger Transactions Brief -->
<div class="mb-5">
    <div class="flex items-center justify-between mb-3.5">
        <h3 class="text-xs font-semibold text-slate-400 uppercase tracking-widest">Recent Ledger Events</h3>
        <a href="index.php?page=user/wallet" class="text-[10px] text-indigo-400 font-bold hover:underline">Full Statement</a>
    </div>

    <div class="space-y-2">
        <?php if (empty($ledgers)): ?>
            <div class="text-center p-4 bg-slate-900 border border-slate-800/50 rounded-2xl text-xs text-slate-500">
                No financial ledger records filed yet.
            </div>
        <?php else: ?>
            <?php foreach ($ledgers as $ledger): ?>
                <div class="bg-slate-900 border border-slate-800/70 rounded-2xl p-3 flex justify-between items-center text-xs">
                    <div class="flex items-start gap-2.5">
                        <span class="py-1 px-1.5 rounded-lg font-bold text-[10px] font-mono leading-none
                            <?= ($ledger['type'] === 'credit' ? 'bg-emerald-950/55 text-emerald-400' : 'bg-rose-950/55 text-rose-400') ?>">
                            <?= ($ledger['type'] === 'credit' ? '+' : '–') ?>
                        </span>
                        <div>
                            <h4 class="font-bold text-slate-200"><?= secure_escape($ledger['reason']) ?></h4>
                            <span class="text-[9px] text-slate-500 font-mono"><?= $ledger['timestamp'] ?></span>
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="font-mono font-bold <?= ($ledger['type'] === 'credit' ? 'text-emerald-400' : 'text-slate-300') ?>">
                            $<?= number_format(floatval($ledger['amount']), 2) ?>
                        </span>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<div class="grid grid-cols-2 gap-3.5">
    
    <a href="index.php?page=user/market" class="bg-slate-900 hover:bg-slate-900/80 border border-slate-800 rounded-2xl p-4 flex flex-col justify-between active:scale-[0.98] transition cursor-pointer">
        <span class="text-lg">📊</span>
        <div class="mt-3">
            <h4 class="text-xs font-bold text-slate-200">Markets Cockpit</h4>
            <span class="text-[9px] text-slate-500 mt-0.5 block">Analyze prices & changes</span>
        </div>
    </a>

    <a href="index.php?page=user/signals" class="bg-slate-900 hover:bg-slate-900/80 border border-slate-800 rounded-2xl p-4 flex flex-col justify-between active:scale-[0.98] transition cursor-pointer">
        <span class="text-lg">📡</span>
        <div class="mt-3">
            <h4 class="text-xs font-bold text-slate-200">AI Signals Feed</h4>
            <span class="text-[9px] text-slate-500 mt-0.5 block">Overbought & RSI alarms</span>
        </div>
    </a>

</div>
