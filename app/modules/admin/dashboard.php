<?php
/**
 * TradeNexa.com - Admin - Operational Command Center
 * Serves platform telemetry, user metrics, and transaction summaries
 */

require_once dirname(dirname(dirname(__DIR__))) . '/app/core/db.php';
require_once dirname(dirname(dirname(__DIR__))) . '/app/core/settings.php';

$users_count = 0;
$banned_count = 0;
$pro_vip_count = 0;
$ledger_volume = 0;

if ($conn) {
    // 1. User matrix counts
    $res = mysqli_query($conn, "SELECT COUNT(*) as cnt, SUM(CASE WHEN `status`='banned' THEN 1 ELSE 0 END) as banned, SUM(CASE WHEN `plan`!='free' THEN 1 ELSE 0 END) as premium FROM `users`");
    if ($res) {
        $row = mysqli_fetch_assoc($res);
        $users_count = intval($row['cnt']);
        $banned_count = intval($row['banned']);
        $pro_vip_count = intval($row['premium']);
    }

    // 2. Financial ledger cumulative turnover
    $res2 = mysqli_query($conn, "SELECT SUM(`amount`) as vol FROM `wallet_ledger`");
    if ($res2) {
        $row2 = mysqli_fetch_assoc($res2);
        $ledger_volume = floatval($row2['vol']);
    }
}
?>

<div class="mb-5 bg-gradient-to-br from-indigo-950/40 to-slate-900 border border-slate-800 rounded-3xl p-5 shadow-sm">
    <span class="text-[9px] font-bold text-indigo-400 uppercase tracking-widest">Admin Control Tower</span>
    <h2 class="text-xl font-black mt-1 leading-snug">SaaS Operator cockpit</h2>
    <p class="text-[10px] text-slate-400 mt-1 lines-relaxed">Manage system configurations, edit promotional ad slots, broadcast bulletins, and audit financial records.</p>
</div>

<!-- Grid metrics -->
<div class="grid grid-cols-2 gap-3.5 mb-5 text-xs">
    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-3.5">
        <span class="text-[8px] text-slate-500 uppercase font-semibold">Registered Traders</span>
        <h3 class="font-mono text-base font-black text-slate-100 mt-1"><?= $users_count ?> users</h3>
        <p class="text-[9px] text-slate-500 mt-1"><?= $pro_vip_count ?> Premium licenses</p>
    </div>
    
    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-3.5">
        <span class="text-[8px] text-slate-500 uppercase font-semibold">Ledger Turnover</span>
        <h3 class="font-mono text-base font-black text-emerald-400 mt-1">$<?= number_format($ledger_volume, 2) ?></h3>
        <p class="text-[9px] text-slate-500 mt-1"><?= $banned_count ?> accounts restricted</p>
    </div>
</div>

<!-- Admin Command Menu Structure (Optimized for Mobile Navigation) -->
<h3 class="text-xs font-semibold text-slate-400 uppercase tracking-widest mb-3.5">Control Sub-stations</h3>
<div class="space-y-2">
    
    <a href="index.php?page=admin/users" class="flex items-center justify-between p-3.5 rounded-2xl bg-slate-900 border border-slate-800 hover:border-slate-700 text-slate-300 font-bold transition cursor-pointer">
        <div class="flex items-center gap-2.5">
            <span>👥</span>
            <span>Manage Registrants (Ban / Upgrade)</span>
        </div>
        <span>→</span>
    </a>

    <a href="index.php?page=admin/ads" class="flex items-center justify-between p-3.5 rounded-2xl bg-slate-900 border border-slate-800 hover:border-slate-700 text-slate-300 font-bold transition cursor-pointer">
        <div class="flex items-center gap-2.5">
            <span>📢</span>
            <span>Promotional Ads Placements</span>
        </div>
        <span>→</span>
    </a>

    <a href="index.php?page=admin/messages" class="flex items-center justify-between p-3.5 rounded-2xl bg-slate-900 border border-slate-800 hover:border-slate-700 text-slate-300 font-bold transition cursor-pointer">
        <div class="flex items-center gap-2.5">
            <span>📣</span>
            <span>Publish Broadcast Bulletins</span>
        </div>
        <span>→</span>
    </a>

    <a href="index.php?page=admin/settings" class="flex items-center justify-between p-3.5 rounded-2xl bg-slate-900 border border-slate-800 hover:border-slate-700 text-indigo-300 font-black transition cursor-pointer">
        <div class="flex items-center gap-2.5">
            <span>⚙</span>
            <span>BYBIT API & AI Strength Tuning</span>
        </div>
        <span>→</span>
    </a>

</div>
