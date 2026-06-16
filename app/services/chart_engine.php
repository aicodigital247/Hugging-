<?php
/**
 * TradeNexa.com - Technical Chart Engine
 * Compiles candlestick datasets and overlays technical EMA metrics for the UI rendering layer
 */

require_once __DIR__ . '/bybit_service.php';
require_once __DIR__ . '/ai_signal_engine.php';

/**
 * Builds overlay series bounds suitable for Chart canvas outputs
 */
function chart_compile_series($symbol = 'BTCUSDT', $timeframe = '1h', $limit = 45) {
    // Collect raw candles
    $candles = bybit_get_candles($symbol, $timeframe, $limit);
    
    $closes = [];
    foreach ($candles as $c) {
        $closes[] = $c['close'];
    }

    // Build cumulative indicator lists
    $final_points = [];
    $total = count($candles);
    
    for ($i = 0; $i < $total; $i++) {
        $slice = array_slice($closes, 0, $i + 1);
        
        $ema9 = ($i >= 9) ? round(compute_ema($slice, 9), 4) : null;
        $ema21 = ($i >= 21) ? round(compute_ema($slice, 21), 4) : null;
        
        $c = $candles[$i];
        
        // Tag trend zones
        $trend = 'HOLD';
        if ($ema9 && $ema21) {
            $trend = ($ema9 > $ema21) ? 'BULL' : 'BEAR';
        }

        $final_points[] = [
            'time' => $c['timestamp'],
            'open' => round($c['open'], 4),
            'high' => round($c['high'], 4),
            'low' => round($c['low'], 4),
            'close' => round($c['close'], 4),
            'volume' => round($c['volume'], 2),
            'ema9' => $ema9,
            'ema21' => $ema21,
            'trend' => $trend
        ];
    }
    
    return $final_points;
}
