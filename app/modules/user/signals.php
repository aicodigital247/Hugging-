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
<div class="mb-4 bg-[#161A1E] border border-[#20262D] rounded-3xl p-4">
    <div class="flex items-center gap-2 mb-2">
        <span class="w-2.5 h-2.5 rounded-full bg-[#F0B90B] animate-pulse"></span>
        <h3 class="text-xs font-black uppercase text-[#EAECEF] font-sans"><?= __('signalHeader', 'Live AI Signal Feed') ?></h3>
    </div>
    <p class="text-[10px] text-gray-400 leading-relaxed font-sans">
        Our proprietary server-side signal generator processes 1-hour OHLC candle crossovers in real-time, matching Exponential Moving Averages (9 & 21) against Relative Strength Index (RSI 14) boundaries to produce precise actions.
    </p>
    
    <!-- Tier Limits Indicator -->
    <div class="mt-3 pt-3 border-t border-[#20262D] grid grid-cols-2 gap-3.5">
        <div>
            <span class="text-[8px] text-gray-500 uppercase font-semibold">Signal Latency:</span>
            <p class="text-xs font-bold text-gray-300 font-mono">
                <?= ($tier === TIER_VIP) ? '⚡ Real-time (0s)' : (($tier === TIER_PRO) ? '⏰ Standard (~10s)' : 'Throttled (15m delay)'); ?>
            </p>
        </div>
        <div>
            <span class="text-[8px] text-gray-500 uppercase font-semibold">Indicator Overlay:</span>
            <p class="text-xs font-bold text-gray-300 font-mono">
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
        <div class="bg-[#161A1E] border border-[#20262D] rounded-3xl p-4 shadow-sm relative overflow-hidden transition-all duration-300 hover:border-brand-500/40">
            
            <!-- Glow background depending on action -->
            <?php if ($raw_signal['signal'] === 'BUY'): ?>
                <div class="absolute -top-16 -right-16 w-32 h-32 bg-[#03C087]/5 rounded-full blur-2xl"></div>
            <?php elseif ($raw_signal['signal'] === 'SELL'): ?>
                <div class="absolute -top-16 -right-16 w-32 h-32 bg-[#F04F56]/5 rounded-full blur-2xl"></div>
            <?php endif; ?>

            <!-- Pair header line -->
            <div class="flex justify-between items-center mb-3">
                <div class="flex items-center gap-1.5">
                    <span class="font-mono text-sm font-black text-[#EAECEF]"><?= $sym ?></span>
                    <span class="text-[8px] bg-gray-900 border border-[#20262D] text-[#F0B90B] px-1.5 py-0.5 rounded font-black font-mono uppercase tracking-wider">Perpetual</span>
                </div>
                
                <!-- Action directive -->
                <span class="text-[10px] font-black px-2.5 py-1 rounded-lg uppercase tracking-wide font-mono
                    <?php if ($raw_signal['signal'] === 'BUY'): ?>
                        bg-[#03C087]/15 text-[#03C087] border border-[#03C087]/30
                    <?php elseif ($raw_signal['signal'] === 'SELL'): ?>
                        bg-[#F04F56]/15 text-[#F04F56] border border-[#F04F56]/30
                    <?php else: ?>
                        bg-gray-800 text-gray-400 border border-transparent
                    <?php endif; ?>">
                    <?= $raw_signal['signal'] ?>
                </span>
            </div>

            <!-- Signal metrics grid -->
            <div class="grid grid-cols-3 gap-2 bg-[#0B0E11] p-3 rounded-2xl border border-[#20262D] mb-3.5">
                <div class="text-center border-r border-[#20262D]">
                    <span class="text-[8px] text-gray-500 uppercase font-black tracking-wide">Entry Price</span>
                    <p class="font-mono text-xs font-bold text-gray-300 mt-1"><?= secure_escape($signal['entry']) ?></p>
                </div>
                <div class="text-center border-r border-[#20262D]">
                    <span class="text-[8px] text-gray-500 uppercase font-black tracking-wide">Take Profit</span>
                    <p class="font-mono text-xs font-bold text-[#03C087] mt-1"><?= secure_escape($signal['tp']) ?></p>
                </div>
                <div class="text-center">
                    <span class="text-[8px] text-gray-500 uppercase font-black tracking-wide">Stop Loss</span>
                    <p class="font-mono text-xs font-bold text-[#F04F56] mt-1"><?= secure_escape($signal['sl']) ?></p>
                </div>
            </div>

            <!-- Strategy notes / descriptive details -->
            <div class="space-y-2 text-xs border-t border-[#20262D] pt-3">
                <div class="flex justify-between items-center text-[10px]">
                    <span class="text-gray-500 font-bold">Accuracy Score:</span>
                    <span class="font-mono font-black text-[#F0B90B]">
                        <?php if ($signal['confidence'] === '🔒'): ?>
                            🔒 <a href="index.php?page=billing/plans" class="underline hover:text-amber-400 font-sans">Unlock</a>
                        <?php else: ?>
                            <?= $signal['confidence'] ?>% Confidence
                        <?php endif; ?>
                    </span>
                </div>

                <div class="flex justify-between items-center text-[10px]">
                    <span class="text-gray-500 font-bold">Crossover Index:</span>
                    <span class="font-mono text-gray-300"><?= secure_escape($strategy['strategy']) ?></span>
                </div>

                <!-- Contributing Signals Explainer board (Dynamic details) -->
                <div class="bg-[#0B0E11] border border-[#20262D] rounded-2xl p-3 mt-1.5 space-y-2">
                    <div class="flex items-center justify-between text-[9px] border-b border-[#20262D]/60 pb-1.5">
                        <span class="text-[#F0B90B] font-black font-sans tracking-tight uppercase">📈 Contributing Factors Explainer</span>
                        <span class="text-gray-500 font-mono">1H Indicators</span>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-2 text-[9px]">
                        <div class="bg-[#161A1E] p-1.5 rounded-lg border border-[#20262D]/80">
                            <span class="text-gray-500 block">RSI Value (14):</span>
                            <span class="font-mono font-black <?= ($raw_signal['rsi'] > 70 ? 'text-[#F04F56]' : ($raw_signal['rsi'] < 30 ? 'text-[#03C087]' : 'text-gray-300')) ?>">
                                <?= secure_escape($raw_signal['rsi']) ?> (<?= ($raw_signal['rsi'] > 70 ? 'Overbought' : ($raw_signal['rsi'] < 30 ? 'Oversold' : 'Neutral')) ?>)
                            </span>
                        </div>
                        <div class="bg-[#161A1E] p-1.5 rounded-lg border border-[#20262D]/80">
                            <span class="text-gray-500 block">EMA Fast/Slow Diff:</span>
                            <span class="font-mono font-black text-gray-300">
                                <?= number_format($raw_signal['ema_fast'] - $raw_signal['ema_slow'], 4) ?> (<?= ($raw_signal['ema_fast'] > $raw_signal['ema_slow'] ? 'Bullish Gap' : 'Bearish Gap') ?>)
                            </span>
                        </div>
                    </div>

                    <p class="text-[9px] text-gray-400 font-mono mt-2 leading-relaxed">
                        <strong class="text-[#F0B90B] font-sans">Trigger Justification:</strong><br>
                        <?php if ($raw_signal['signal'] === 'BUY'): ?>
                            The entry is set near current asset price with Stop Loss strategically placed below the 4-hour EMA(21) support cluster to prevent fakeout stop triggers, while Take Profit uses a 1.5x ATR ratio for maximum risk-reward yield.
                        <?php elseif ($raw_signal['signal'] === 'SELL'): ?>
                            The sell trigger is calculated on immediate EMA bearish crossover. Stop Loss is set just above the recent local structural high, giving the order ample breathing room to clear high-liquidity order blocks.
                        <?php else: ?>
                            Indicators are balanced within the neutral channel. Wait for EMA crossover patterns and RSI divergence confirmations before establishing dynamic order placements.
                        <?php endif; ?>
                    </p>
                </div>

                <p class="text-[10px] text-gray-400 leading-relaxed bg-[#0B0E11]/80 p-2 rounded-xl mt-1.5 border border-[#20262D]">
                    <strong class="text-[#F0B90B]">Tactical Recommendation:</strong><br>
                    <?= secure_escape($strategy['notes']) ?>
                </p>
            </div>

        </div>
    <?php endforeach; ?>
</div>
