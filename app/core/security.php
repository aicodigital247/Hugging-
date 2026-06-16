<?php
/**
 * TradeNexa.com - Core Security & Filter Engine
 * Implements defenses against XSS, input injection, handles logs, and logs request IPs
 */

require_once __DIR__ . '/db.php';

/**
 * Escapes values safe for HTML tags (defends against XSS)
 */
function secure_escape($value) {
    if ($value === null) {
        return '';
    }
    return htmlspecialchars(strval($value), ENT_QUOTES, 'UTF-8');
}

/**
 * Strips all dangerous HTML codes strictly
 */
function secure_sanitize_input($value) {
    return strip_tags(trim($value));
}

/**
 * Logs a system security transaction or access log
 */
function security_log_action($user_id, $action, $details = '') {
    global $conn;
    if (!$conn) {
        // Fallback write to disk logs if database is configured offline
        $log_dir = dirname(dirname(__DIR__)) . '/storage/logs/';
        if (!is_dir($log_dir)) {
            mkdir($log_dir, 0755, true);
        }
        $log_file = $log_dir . 'security_' . date('Y-m-d') . '.log';
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $time = date('Y-m-d H:i:s');
        $msg = "[$time] IP: $ip | USER: $user_id | ACTION: $action | DETAILS: $details" . PHP_EOL;
        file_put_contents($log_file, $msg, FILE_APPEND);
        return;
    }

    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $reason = substr($action . " — " . $details, 0, 255);
    
    // Express as system wallet ledger or custom activity trace if required
    // To ensure general audit capability:
    $stmt = mysqli_prepare($conn, "INSERT INTO `messages` (`title`, `content`, `type`) VALUES (?, ?, 'alert')");
    if ($stmt) {
        $title = "Audit Trace — User #" . intval($user_id);
        $content = "IP: " . $ip . " executed action: " . $reason;
        mysqli_stmt_bind_param($stmt, "ss", $title, $content);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
}
