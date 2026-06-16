<?php
/**
 * TradeNexa.com - Market - On-Chain Analysis Dashboard
 * Shows analytical statistics details
 */

require_once dirname(dirname(dirname(__DIR__))) . '/app/core/saas.php';

$tier = saas_get_current_tier();
?>

<div class="mb-5 bg-gradient-to-br from-indigo-950/40 to-slate-900 border border-slate-800 p-5 rounded-3xl text-xs">
    <h2 class="text-sm font-black text-slate-100 uppercase tracking-widest mb-1">On-Chain Alarms</h2>
    <span class="text-[9px] text-zinc-500 font-mono">Market analysis desk indicators</span>
    <p class="text-slate-400 leading-relaxed mt-2.5">
        Review real-time transaction speeds, distribution indices, and address growth.
    </p>
</div>

<div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 mb-4 text-xs font-mono relative">
    
    <div class="flex items-center justify-between mb-4 border-b border-slate-950/60 pb-2.5">
        <h3 class="font-extrabold text-slate-200">Whale Distribution index</h3>
        <span class="text-[9px] text-emerald-400 bg-emerald-950/40 py-0.5 px-1.5 rounded font-bold">Stable</span>
    </div>

    <div class="space-y-3.5 text-[11px] text-slate-400 leading-relaxed">
        <div class="flex justify-between">
            <span>Whale transaction count (>$1M):</span>
            <span class="font-bold text-slate-200">1,482 daily</span>
        </div>
        <div class="flex justify-between border-t border-slate-950/30 pt-2">
            <span>Accumulation address growth:</span>
            <span class="font-bold text-slate-200">+4.12% YoY</span>
        </div>
        <div class="flex justify-between border-t border-slate-950/30 pt-2">
            <span>Exchange Reserves status:</span>
            <span class="font-bold text-emerald-400">Low Reserves (Bullish)</span>
        </div>
    </div>

</div>

<div class="bg-indigo-950/10 border border-indigo-950/40 rounded-2xl p-4 flex gap-3 text-xs text-slate-400 leading-relaxed">
    <div class="text-sm">💡</div>
    <p class="text-[10px]">
        These parameters are updated twice daily. Use this telemetry alongside raw RSI alerts to backtest trading setups with maximum accuracy models.
    </p>
</div>
