<?php
/**
 * TradeNexa.com - Market - On-Chain Analysis Dashboard
 * Shows analytical statistics details
 */

require_once dirname(dirname(dirname(__DIR__))) . '/app/core/saas.php';

$tier = saas_get_current_tier();
?>

<div class="mb-5 bg-gradient-to-br from-amber-950/10 to-[#161A1E] border border-[#20262D] p-5 rounded-3xl text-xs">
    <h2 class="text-sm font-black text-[#EAECEF] uppercase tracking-widest mb-1 flex items-center gap-1.5">
        <span class="w-1.5 h-3 bg-[#F0B90B] rounded-sm"></span> On-Chain Alarms
    </h2>
    <span class="text-[9px] text-gray-500 font-mono">Market analysis desk indicators</span>
    <p class="text-gray-400 leading-relaxed mt-2.5">
        Review real-time transaction speeds, distribution indices, and address growth.
    </p>
</div>

<div class="bg-[#161A1E] border border-[#20262D] rounded-3xl p-5 mb-4 text-xs font-mono relative">
    
    <div class="flex items-center justify-between mb-4 border-b border-[#20262D] pb-2.5">
        <h3 class="font-extrabold text-[#EAECEF]">Whale Distribution index</h3>
        <span class="text-[9px] text-[#03C087] bg-[#03C087]/10 py-0.5 px-1.5 rounded font-bold">Stable</span>
    </div>

    <div class="space-y-3.5 text-[11px] text-gray-400 leading-relaxed">
        <div class="flex justify-between">
            <span>Whale transaction count (>$1M):</span>
            <span class="font-bold text-[#EAECEF]">1,482 daily</span>
        </div>
        <div class="flex justify-between border-t border-[#20262D] pt-2">
            <span>Accumulation address growth:</span>
            <span class="font-bold text-[#EAECEF]">+4.12% YoY</span>
        </div>
        <div class="flex justify-between border-t border-[#20262D] pt-2">
            <span>Exchange Reserves status:</span>
            <span class="font-bold text-[#03C087]">Low Reserves (Bullish)</span>
        </div>
    </div>

</div>

<div class="bg-[#1B2026] border border-[#20262D] rounded-2xl p-4 flex gap-3 text-xs text-gray-400 leading-relaxed">
    <div class="text-sm text-[#F0B90B]">💡</div>
    <p class="text-[10px] text-gray-400">
        These parameters are updated twice daily. Use this telemetry alongside raw RSI alerts to backtest trading setups with maximum accuracy models.
    </p>
</div>
