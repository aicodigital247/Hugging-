<?php
/**
 * TradeNexa.com - AI Trading Signal Engine
 * Computes Server-Side RSI(14), EMA(9), EMA(21), Volume spikes, and outputs signals with entry/tp/sl bounds
 */

require_once __DIR__ . '/bybit_service.php';
require_once dirname(__DIR__) . '/core/db.php';
require_once dirname(__DIR__) . '/core/settings.php';

/**
 * Helper: Computes simple moving average
 */
function compute_sma($data, $period) {
    if (count($data) < $period) return 0;
    $sum = 0;
    for ($i = count($data) - $period; $i < count($data); $i++) {
        $sum += $data[$i];
    }
    return $sum / $period;
}

/**
 * Helper: Computes exponential moving average
 */
function compute_ema($data, $period) {
    $count = count($data);
    if ($count < $period) return 0;
    
    $k = 2 / ($period + 1);
    // Simple average is initial EMA
    $ema = compute_sma(array_slice($data, 0, $period), $period);
    
    for ($i = $period; $i < $count; $i++) {
        $ema = ($data[$i] * $k) + ($ema * (1 - $k));
    }
    return $ema;
}

/**
 * Helper: Computes RSI indicator
 */
function compute_rsi($closes, $period = 14) {
    $count = count($closes);
    if ($count <= $period) return 50.0; // default neural indicator
    
    $gains = [];
    $losses = [];
    
    for ($i = 1; $i < $count; $i++) {
        $diff = $closes[$i] - $closes[$i - 1];
        if ($diff >= 0) {
            $gains[] = $diff;
            $losses[] = 0;
        } else {
            $gains[] = 0;
            $losses[] = abs($diff);
        }
    }
    
    $avg_gain = array_sum(array_slice($gains, 0, $period)) / $period;
    $avg_loss = array_sum(array_slice($losses, 0, $period)) / $period;
    
    if ($avg_loss == 0) return 100;
    
    for ($i = $period; $i < count($gains); $i++) {
        $avg_gain = (($avg_gain * ($period - 1)) + $gains[$i]) / $period;
        $avg_loss = (($avg_loss * ($period - 1)) + $losses[$i]) / $period;
    }
    
    $rs = $avg_gain / $avg_loss;
    return 100 - (100 / (1 + $rs));
}

/**
 * Main: Analyzes trading candles to produce standard neural signals
 */
function ai_generate_signal($symbol = "BTCUSDT") {
    $symbol = strtoupper(trim($symbol));
    $candles = bybit_get_candles($symbol, '1h', 50); // get 50 hours of historical indicators
    
    $closes = [];
    $volumes = [];
    foreach ($candles as $c) {
        $closes[] = $c['close'];
        $volumes[] = $c['volume'];
    }

    $current_price = end($closes);
    
    // EMA calculations
    $ema9 = compute_ema($closes, 9);
    $ema21 = compute_ema($closes, 21);
    
    // RSI calculations
    $rsi = compute_rsi($closes, 14);
    
    // Volume surge checking (spike detection comparison relative to 10 session moving averages)
    $avg_volume = array_sum(array_slice($volumes, -10)) / 10;
    $current_volume = end($volumes);
    $vol_spike = ($current_volume > $avg_volume * 1.5);

    // Scoring system (Starts at 0, goes from -100 to +100)
    $score = 0;
    
    // 1. EMA trend alignment
    if ($ema9 > $ema21) {
        $score += 35; // bullish crossover trend
    } else {
        $score -= 35; // bearish trail
    }

    // 2. Relative Strength Index conditions
    if ($rsi < 30) {
        $score += 40; // extremely oversold, buy potential
    } elseif ($rsi > 70) {
        $score -= 40; // overbought, sell off likelihood
    } else {
        // Pullback trends
        if ($ema9 > $ema21 && $rsi < 55 && $rsi > 40) {
            $score += 15; // healthy pullback
        }
    }

    // 3. Volume confirmation momentum boost
    if ($vol_spike) {
        if ($score > 0) {
            $score += 15; // Confirm bull volume
        } else {
            $score -= 15; // Confirm sell intensity
        }
    }

    // Final trade decision bounds
    $signal_type = "HOLD";
    $confidence = 50;
    
    // Retrieve sensitivity configuration
    $sensitivity = strtolower(settings_get('signal_sensitivity', 'medium'));
    $multiplier = 1.0;
    if ($sensitivity === 'low') {
        $multiplier = 0.8;
    } elseif ($sensitivity === 'high') {
        $multiplier = 1.25;
    }
    
    if ($score >= 25) {
        $signal_type = "BUY";
        $confidence = min(99, intval(50 + ($score * 0.5 * $multiplier)));
    } elseif ($score <= -25) {
        $signal_type = "SELL";
        $confidence = min(99, intval(50 + (abs($score) * 0.5 * $multiplier)));
    }

    // Target configuration (Stop loss and Take profits)
    $atr = $current_price * 0.025; // 2.5% simple volatility range
    
    if ($signal_type === 'BUY') {
        $entry = $current_price;
        $tp = $current_price + ($atr * 1.5);
        $sl = $current_price - $atr;
    } elseif ($signal_type === 'SELL') {
        $entry = $current_price;
        $tp = $current_price - ($atr * 1.5);
        $sl = $current_price + $atr;
    } else {
        $entry = $current_price;
        $tp = $current_price;
        $sl = $current_price;
    }

    $signal_payload = [
        'symbol' => $symbol,
        'signal' => $signal_type,
        'confidence' => $confidence,
        'entry' => round($entry, 4),
        'tp' => round($tp, 4),
        'sl' => round($sl, 4),
        'rsi' => round($rsi, 2),
        'ema_fast' => round($ema9, 4),
        'ema_slow' => round($ema21, 4),
        'timestamp' => time()
    ];

    return $signal_payload;
}
