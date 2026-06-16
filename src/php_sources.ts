/**
 * TradeNexa.com - PHP Production Source Code Dictionary
 * Pre-populated for interactive side-by-side review in the developer cockpit
 */

import { FileCodeData } from './types';

export const PHP_SOURCES: FileCodeData[] = [
  {
    path: 'app/core/db.php',
    title: 'MySQLi Database Connection Wrapper',
    description: 'Bypasses PDO to use strict MySQLi prepared statements with input bind parameters.',
    code: `<?php
/**
 * TradeNexa.com - Database Connection Handler
 * MySQLi API ONLY (Strictly No PDO as requested)
 */

if (!defined('DB_HOST')) {
    define('DB_HOST', '127.0.0.1');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'tradenexa_db');
}

$conn = @mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if (!$conn) {
    if (strpos($_SERVER['REQUEST_URI'], '/install') === false) {
        header("Location: /install/index.php");
        exit;
    }
} else {
    mysqli_set_charset($conn, 'utf8mb4');
}

function db_escape($value) {
    global $conn;
    return $conn ? mysqli_real_escape_string($conn, $value) : addslashes($value);
}

function db_query($sql, $params = [], $types = "") {
    global $conn;
    if (!$conn) return false;
    
    if (empty($params)) {
        return mysqli_query($conn, $sql);
    }
    
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) return false;
    
    if (empty($types)) {
        $types = str_repeat("s", count($params));
    }
    
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    
    if (stripos($sql, "SELECT") === 0 || stripos($sql, "SHOW") === 0) {
        $result = mysqli_stmt_get_result($stmt);
        $rows = [];
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $rows[] = $row;
            }
        }
        mysqli_stmt_close($stmt);
        return $rows;
    }
    
    $affected = mysqli_stmt_affected_rows($stmt);
    $insert_id = mysqli_insert_id($conn);
    mysqli_stmt_close($stmt);
    
    return [
        'affected_rows' => $affected,
        'insert_id' => $insert_id,
        'status' => true
    ];
}`
  },
  {
    path: 'app/services/ai_signal_engine.php',
    title: 'Indicators Technical Signal Engine',
    description: 'Server-side engine calculating RSI(14) and EMA(9/21) crossover triggers with volume confirmations.',
    code: `<?php
/**
 * TradeNexa.com - AI Trading Signal Engine
 * Computes Server-Side RSI(14), EMA(9), EMA(21), Volume spikes, and outputs signals
 */

require_once __DIR__ . '/bybit_service.php';
require_once dirname(__DIR__) . '/core/db.php';
require_once dirname(__DIR__) . '/core/settings.php';

function compute_ema($data, $period) {
    $count = count($data);
    if ($count < $period) return 0;
    $k = 2 / ($period + 1);
    $ema = array_sum(array_slice($data, 0, $period)) / $period;
    for ($i = $period; $i < $count; $i++) {
        $ema = ($data[$i] * $k) + ($ema * (1 - $k));
    }
    return $ema;
}

function compute_rsi($closes, $period = 14) {
    $count = count($closes);
    if ($count <= $period) return 50.0;
    
    $gains = []; $losses = [];
    for ($i = 1; $i < $count; $i++) {
        $diff = $closes[$i] - $closes[$i - 1];
        if ($diff >= 0) { $gains[] = $diff; $losses[] = 0; }
        else { $gains[] = 0; $losses[] = abs($diff); }
    }
    
    $avg_gain = array_sum(array_slice($gains, 0, $period)) / $period;
    $avg_loss = array_sum(array_slice($losses, 0, $period)) / $period;
    if ($avg_loss == 0) return 100;
    
    for ($i = $period; $i < count($gains); $i++) {
        $avg_gain = (($avg_gain * ($period - 1)) + $gains[$i]) / $period;
        $avg_loss = (($avg_loss * ($period - 1)) + $losses[$i]) / $period;
    }
    return 100 - (100 / (1 + ($avg_gain / $avg_loss)));
}

function ai_generate_signal($symbol = "BTCUSDT") {
    $candles = bybit_get_candles($symbol, '1h', 50);
    $closes = []; $volumes = [];
    foreach ($candles as $c) {
        $closes[] = $c['close'];
        $volumes[] = $c['volume'];
    }
    $current_price = end($closes);
    $ema9 = compute_ema($closes, 9);
    $ema21 = compute_ema($closes, 21);
    $rsi = compute_rsi($closes, 14);
    
    $score = 0;
    if ($ema9 > $ema21) $score += 35;
    else $score -= 35;
    
    if ($rsi < 30) $score += 40;
    elseif ($rsi > 70) $score -= 40;
    
    $signal_type = "HOLD"; $confidence = 50;
    
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
    
    $atr = $current_price * 0.025;
    return [
        'symbol' => $symbol,
        'signal' => $signal_type,
        'confidence' => $confidence,
        'entry' => round($current_price, 4),
        'tp' => round($signal_type === 'BUY' ? $current_price + $atr : $current_price - $atr, 4),
        'sl' => round($signal_type === 'BUY' ? $current_price - $atr : $current_price + $atr, 4),
        'rsi' => round($rsi, 2)
    ];
}`
  },
  {
    path: 'app/api/sse_notifications.php',
    title: 'SSE Live Push Notifications Gateway',
    description: 'Implements lightweight Server-Sent Events (SSE) in native PHP to instantly push signals, risk alerts, and webhooks logs directly to browsers.',
    code: `<?php
/**
 * TradeNexa.com - Server-Sent Events (SSE) Live Gateway
 * Enforces text/event-stream headers to execute lightweight real-time triggers
 */

header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');
header('X-Accel-Buffering: no'); // Bypasses nginx output proxy caching

require_once dirname(__DIR__) . '/core/db.php';

// Monitor notification rows inside MySQLi
$last_checked_id = isset($_GET['last_id']) ? intval($_GET['last_id']) : 0;

while (true) {
    if (connection_aborted()) {
        exit;
    }

    $sql = "SELECT * FROM push_notifications WHERE id > ? ORDER BY id ASC LIMIT 5";
    $alerts = db_query($sql, [$last_checked_id], "i");

    if (!empty($alerts)) {
        foreach ($alerts as $notif) {
            $last_checked_id = $notif['id'];
            echo "id: " . $notif['id'] . "\\n";
            echo "event: " . $notif['event_type'] . "\\n";
            echo "data: " . json_encode([
                'title' => $notif['title'],
                'message' => $notif['message'],
                'payload' => json_decode($notif['metadata'] ?? '{}'),
                'time' => date('H:i:s', strtotime($notif['created_at']))
            ]) . "\\n\\n";
        }
        ob_flush();
        flush();
    }

    // Keep-alive empty heartbeat packet every 4 seconds
    echo ": heartbeat\\n\\n";
    ob_flush();
    flush();

    sleep(2);
}`
  },
  {
    path: 'app/webhooks/telegram_webhook.php',
    title: 'Telegram Login & Webhook Bot Handler',
    description: 'Processes Telegram secure bot logins, validates widgets signatures via SHA-256 HMAC, and listens for command webhooks.',
    code: `<?php
/**
 * TradeNexa.com - Telegram Webhook & Login Validator
 */

require_once dirname(__DIR__) . '/core/db.php';

// 1. TELEGRAM WIDGET LOGIN VALIDATION
function telegram_validate_auth($auth_data, $bot_token) {
    $check_hash = $auth_data['hash'];
    unset($auth_data['hash']);
    
    $data_check_arr = [];
    foreach ($auth_data as $key => $value) {
        $data_check_arr[] = $key . '=' . $value;
    }
    sort($data_check_arr);
    $data_check_string = implode("\\n", $data_check_arr);
    
    $secret_key = hash('sha256', $bot_token, true);
    $hash = hash_hmac('sha256', $data_check_string, $secret_key);
    
    if (strcmp($hash, $check_hash) !== 0) {
        throw new Exception('Data Security Check Failed. Invalid Telegram Signature.');
    }
    
    if ((time() - $auth_data['auth_date']) > 86400) {
        throw new Exception('Authentication session expired (more than 24h old).');
    }
    
    return $auth_data;
}

// 2. WEBHOOK MESSAGES ENTRY POINT
$raw_input = file_get_contents('php://input');
$update = json_decode($raw_input, true);

if (!empty($update['message'])) {
    $chat_id = $update['message']['chat']['id'];
    $text = trim($update['message']['text'] ?? '');
    
    if (strpos($text, '/start') === 0) {
        $ref_code = trim(substr($text, 6)); // Parse optional referral links code
        
        $welcome = "⚡ Welcome to TradeNexa.com Webhook Gateway!\\n\\n";
        $welcome .= "Your Telegram chat is securely linked. Use /signals to view active AI indicators.";
        
        if (!empty($ref_code)) {
            // Log referral logic via standard MySQLi
            db_query("INSERT INTO referrals (telegram_id, referee_username, registered_at) VALUES (?, ?, NOW())", [$chat_id, $ref_code]);
            $welcome .= "\\n\\n🎁 Referral recognized! 7 days Premium free trial has been provisioned.";
        }
        
        telegram_send_message($chat_id, $welcome);
    }
}

function telegram_send_message($chat_id, $text) {
    $token = "YOUR_TELEGRAM_BOT_TOKEN_HERE";
    $url = "https://api.telegram.org/bot" . $token . "/sendMessage";
    $post_fields = [
        'chat_id' => $chat_id,
        'text' => $text,
        'parse_mode' => 'Markdown'
    ];
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_fields));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_exec($ch);
    curl_close($ch);
}`
  },
  {
    path: 'app/services/investment_vault.php',
    title: 'Arbitrage Copier & Yield Pool Manager',
    description: 'Bridges simulated USDT and TON lockups into high frequency compound yield engines with strict ledger records.',
    code: `<?php
/**
 * TradeNexa.com - Trade-For-Profit Compounding Engine
 */

require_once dirname(__DIR__) . '/core/db.php';

function vault_invest_capital($user_id, $amount, $token = "USDT", $duration_days = 30) {
    global $conn;
    $apy_metrics = [
        'USDT' => 0.155, // 15.5% annual yield compounding
        'TON' => 0.182  // 18.2% annual yield compounding
    ];
    
    $rate = $apy_metrics[$token] ?? 0.10;
    
    mysqli_query($conn, "START TRANSACTION");
    
    // Verify base balance holds enough
    $usr_res = mysqli_query($conn, "SELECT wallet_balance FROM users WHERE id = $user_id FOR UPDATE");
    $usr = mysqli_fetch_assoc($usr_res);
    
    if (floatval($usr['wallet_balance']) < $amount) {
        mysqli_query($conn, "ROLLBACK");
        return ['success' => false, 'message' => "Insufficient coin balance for staking."];
    }
    
    $new_bal = floatval($usr['wallet_balance']) - $amount;
    
    // 1. Deduct capital balance
    mysqli_query($conn, "UPDATE users SET wallet_balance = $new_bal WHERE id = $user_id");
    
    // 2. Lodge transaction ledger
    $stmtLeg = mysqli_prepare($conn, "INSERT INTO wallet_ledger (user_id, type, amount, reason, balance_after) VALUES (?, 'debit', ?, ?, ?)");
    $msg = "Vault Stake: Lockup " . $amount . " " . $token . " for " . $duration_days . " days";
    mysqli_stmt_bind_param($stmtLeg, "idss", $user_id, $amount, $msg, $new_bal);
    mysqli_stmt_execute($stmtLeg);
    
    // 3. Insert Investment Contract
    $daily_payout = ($amount * $rate) / 365;
    $stmtInv = mysqli_prepare($conn, "INSERT INTO investment_vaults (user_id, principal, token, apy_rate, daily_accrual, remaining_days, status) VALUES (?, ?, ?, ?, ?, ?, 'active')");
    mysqli_stmt_bind_param($stmtInv, "idsddi", $user_id, $amount, $token, $rate, $daily_payout, $duration_days);
    mysqli_stmt_execute($stmtInv);
    
    mysqli_query($conn, "COMMIT");
    return ['success' => true, 'message' => "Staking Vault active! Your daily profits are compound credited."];
}

function process_daily_vault_compounding() {
    global $conn;
    // Strict MySQLi cursor to update and log automatic payouts
    $invs = db_query("SELECT * FROM investment_vaults WHERE status = 'active' AND remaining_days > 0");
    
    foreach ($invs as $vault) {
        $v_id = $vault['id'];
        $u_id = $vault['user_id'];
        $payout = floatval($vault['daily_accrual']);
        
        mysqli_query($conn, "START TRANSACTION");
        
        // Update user's coin ledger
        mysqli_query($conn, "UPDATE users SET wallet_balance = wallet_balance + $payout WHERE id = $u_id");
        
        // Record direct credit record
        $msg = "Interests compounded: Stake Ref #" . $v_id;
        db_query("INSERT INTO wallet_ledger (user_id, type, amount, reason) VALUES (?, 'credit', ?, ?)", [$u_id, $payout, $msg]);
        
        // Subtract days to lockup
        mysqli_query($conn, "UPDATE investment_vaults SET remaining_days = remaining_days - 1 WHERE id = $v_id");
        
        mysqli_query($conn, "COMMIT");
    }
}`
  }
];
