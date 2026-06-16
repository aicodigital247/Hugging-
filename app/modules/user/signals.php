<?php
/**
 * TradeNexa.com - Users - AI Signal Engine Feed
 * Evaluates real-time RSI, EMA crossovers, and gates parameters appropriately.
 */

require_once dirname(dirname(dirname(__DIR__))) . '/app/services/ai_signal_engine.php';
require_once dirname(dirname(dirname(__DIR__))) . '/app/services/trading_strategy.php';
require_once dirname(dirname(dirname(__DIR__))) . '/app/core/saas.php';

$symbols = ['BTCUSDT', 'ETHUSDT', 'SOLUSDT', 'ADAUSDT'];
$tier = saas_get_current_tier();
$can_access = saas_can_access_signals();
?>

<!-- Info/Feature Gating explanation -->
<div class="mb-4 bg-slate-900 border border-slate-800 rounded-3xl p-4.5">
    <div class="flex items-center gap-2 mb-2">
        <span class="w-2.5 h-2.5 rounded-full bg-indigo-500 animate-pulse"></span>
        <h3 class="text-xs font-black uppercase text-slate-300">Live AI Signal Engine</h3>
    </div>
    <p class="text-[10px] text-slate-400 leading-relaxed">
        Our proprietary server-side signal generator processes 1-hour OHLC candle crossovers in real-time, matching Exponential Moving Averages (9 & 21) against Relative Strength Index (RSI 14) boundaries to produce precise actions.
    </p>
    
    <!-- Tier Limits Indicator -->
    <div class="mt-3.5 pt-3 border-t border-slate-800/80 grid grid-cols-2 gap-3.5">
        <div>
            <span class="text-[8px] text-slate-500 uppercase font-semibold">Signal Latency:</span>
            <p class="text-xs font-bold text-slate-300">
                <?= ($tier === TIER_VIP) ? '⚡ Real-time (0s)' : (($tier === TIER_PRO) ? '⏰ Standard (~10s)' : '🐢 Throttled (15m delay)'); ?>
            </p>
        </div>
        <div>
            <span class="text-[8px] text-slate-500 uppercase font-semibold">Indicator Overlay:</span>
            <p class="text-xs font-bold text-slate-300">
                <?= ($tier === TIER_VIP) ? '📈 RSI + EMA (Full)' : (($tier === TIER_PRO) ? '📈 EMA Crossover' : '🔒 Basic Candle only'); ?>
            </p>
        </div>
    </div>
</div>

<!-- Signals Feed Loop -->
<div class="space-y-4">
    <?php foreach ($symbols as $sym): 
        $raw_signal = ai_generate_signal($sym);
        $signal = saas_gate_signal($raw_signal);
        $strategy = strategy_get_recommendation($raw_signal);
    ?>
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-sm relative overflow-hidden">
            
            <!-- Glow background depending on action -->
            <?php if ($raw_signal['signal'] === 'BUY'): ?>
                <div class="absolute -top-16 -right-16 w-32 h-32 bg-emerald-500/5 rounded-full blur-2xl"></div>
            <?php elseif ($raw_signal['signal'] === 'SELL'): ?>
                <div class="absolute -top-16 -right-16 w-32 h-32 bg-rose-500/5 rounded-full blur-2xl"></div>
            <?php endif; ?>

            <!-- Pair header line -->
            <div class="flex justify-between items-center mb-3">
                <div class="flex items-center gap-1.5">
                    <span class="font-mono text-sm font-black text-slate-200"><?= $sym ?></span>
                    <span class="text-[9px] bg-slate-950 border border-slate-850 text-slate-500 px-1.5 py-0.5 rounded font-semibold uppercase tracking-wider">Perpetual</span>
                </div>
                
                <!-- Action directive -->
                <span class="text-xs font-black px-3 py-1 rounded-xl shadow-sm uppercase tracking-wide
                    <?php if ($raw_signal['signal'] === 'BUY'): ?>
                        bg-emerald-500/10 text-emerald-400 border border-emerald-900/40
                    <?php elseif ($raw_signal['signal'] === 'SELL'): ?>
                        bg-rose-500/10 text-rose-400 border border-rose-900/40
                    <?php else: ?>
                        bg-slate-950 text-slate-500 border border-slate-800
                    <?php endif; ?>">
                    <?= $raw_signal['signal'] ?>
                </span>
            </div>

            <!-- Signal metrics grid -->
            <div class="grid grid-cols-3 gap-2.5 bg-slate-950/60 p-3 rounded-2xl border border-slate-850/60 mb-3.5">
                <div class="text-center">
                    <span class="text-[8px] text-slate-500 uppercase font-semibold">Entry target</span>
                    <p class="font-mono text-xs font-bold text-slate-300 mt-1"><?= secure_escape($signal['entry']) ?></p>
                </div>
                <div class="text-center">
                    <span class="text-[8px] text-slate-500 uppercase font-semibold">Take Profit</span>
                    <p class="font-mono text-xs font-bold text-emerald-400 mt-1"><?= secure_escape($signal['tp']) ?></p>
                </div>
                <div class="text-center">
                    <span class="text-[8px] text-slate-500 uppercase font-semibold">Stop Loss</span>
                    <p class="font-mono text-xs font-bold text-rose-400 mt-1"><?= secure_escape($signal['sl']) ?></p>
                </div>
            </div>

            <!-- Strategy notes / descriptive details -->
            <div class="space-y-2 text-xs border-t border-slate-800/60 pt-3">
                <div class="flex justify-between">
                    <span class="text-slate-500 text-[10px]">AI Accuracy Score:</span>
                    <span class="font-bold text-indigo-400 font-mono text-[10px]">
                        <?php if ($signal['confidence'] === '🔒'): ?>
                            🔒 <a href="index.php?page=billing/plans" class="underline hover:text-indigo-300">Upgrade to unlock</a>
                        <?php else: ?>
                            <?= $signal['confidence'] ?>% Confidence
                        <?php endif; ?>
                    </span>
                </div>

                <div class="flex justify-between">
                    <span class="text-slate-500 text-[10px]">Crossover Strategy:</span>
                    <span class="font-semibold text-slate-300 text-[10px] text-right truncate pl-4"><?= secure_escape($strategy['strategy']) ?></span>
                </div>

                <p class="text-[10px] text-slate-400 leading-relaxed bg-slate-950/30 p-2.5 rounded-xl border border-slate-850/50 mt-1">
                    <strong class="text-indigo-400">Tactical Recommendation:</strong><br>
                    <?= secure_escape($strategy['notes']) ?>
                </p>
            </div>

        </div>
    <?php endforeach; ?>
</div>
