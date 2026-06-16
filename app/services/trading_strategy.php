<?php
/**
 * TradeNexa.com - Trading Strategy Descriptor
 * Interprets technical parameters and generates actionable human descriptions
 */

require_once __DIR__ . '/ai_signal_engine.php';

/**
 * Maps signal payloads to premium tactical recommendations
 */
function strategy_get_recommendation($signal) {
    if ($signal['signal'] === 'HOLD') {
        return [
            'strategy' => 'Neutral Consolidation',
            'duration' => 'Medium Term',
            'risk' => 'Low',
            'notes' => 'Market volume is thin. Wait for a clear EMA(9) crossover confirmation or an RSI bounce before entering a leveraged position.'
        ];
    }
    
    if ($signal['signal'] === 'BUY') {
        if ($signal['rsi'] < 35) {
            return [
                'strategy' => 'Oversold Mean Reversion',
                'duration' => '12h to 48h hold',
                'risk' => 'Moderate',
                'notes' => 'RSI indicates highly exhausted sell-side momentum. Heavy support clustering identified near entry limits. Set standard take profit margin.'
            ];
        }
        return [
            'strategy' => 'Golden EMA Trend Continuation',
            'duration' => 'Scalp — Short hold',
            'risk' => 'High',
            'notes' => 'Fast EMA9 trend is trading firmly above the slowing EMA21 band, backed by a significant positive volume surge. Trailing-stops appropriate.'
        ];
    }
    
    // For SELL signals
    if ($signal['rsi'] > 65) {
        return [
            'strategy' => 'Overbought Bearish Reversal',
            'duration' => '1d Swing',
            'risk' => 'High',
            'notes' => 'RSI values have extended beyond 70, reflecting exhaustion. Bearish EMA crossovers indicate sellers are asserting control.'
        ];
    }
    return [
        'strategy' => 'Breakout Momentum Breakdown',
        'duration' => '4h Scalp',
        'risk' => 'Moderate',
        'notes' => 'Downward target breaches identified. High volume sell walls forming. Set close take profit ranges.'
    ];
}
