<?php
/**
 * TradeNexa.com - Broadcast & Notification Alerts Desk
 * Handles general market bulletins, announcement stores, and critical admin broadcasts
 */

require_once dirname(__DIR__) . '/core/db.php';
require_once dirname(__DIR__) . '/core/settings.php';

/**
 * Retrieves all valid broadcast bulletins
 */
function notification_get_all($limit = 10) {
    global $conn;
    $limit = intval($limit);
    
    $broadcasts = [];
    
    // Inject active system-wide maintenance alerts if activated in settings
    $m_active = settings_get('maintenance_alert_active', '1');
    if ($m_active === '1' || $m_active === 'true' || $m_active === 1) {
        $m_msg = settings_get('maintenance_alert_msg', 'Scheduled system-wide backup in progress. Rest assured, your ledger calculations are offline immutable!');
        $broadcasts[] = [
            'id' => 9999,
            'title' => '⚠️ Core System Maintenance Alert',
            'content' => $m_msg,
            'type' => 'alert',
            'created_at' => date('Y-m-d H:i:s')
        ];
    }
    
    if (!$conn) {
        // Fallback demo alerts if db is offline
        $broadcasts[] = [
            'id' => 1,
            'title' => 'TradeNexa AI Signal Engine Updated',
            'content' => 'We upgraded Bybit REST caching nodes to 15s. Enjoy high performance execution latency reductions.',
            'type' => 'broadcast',
            'created_at' => date('Y-m-d H:i:s', time() - 3600)
        ];
        $broadcasts[] = [
            'id' => 2,
            'title' => 'Market Warning: High Volatility Ahead',
            'content' => 'Heavy sell density has triggered Sol and BTC trend warning indicators. Set stop-loss levels appropriately on passive trades.',
            'type' => 'alert',
            'created_at' => date('Y-m-d H:i:s', time() - 7200)
        ];
        return $broadcasts;
    }

    $rows = db_query("SELECT * FROM `messages` ORDER BY `created_at` DESC LIMIT $limit");
    if ($rows !== false && !empty($rows)) {
        $broadcasts = array_merge($broadcasts, $rows);
    }
    
    return $broadcasts;
}

/**
 * Publishes a security broadcast message from Admin Cockpit
 */
function notification_create($title, $content, $type = 'broadcast') {
    global $conn;
    $title = trim($title);
    $content = trim($content);
    $type = strtolower(trim($type));
    
    if (empty($title) || empty($content)) {
        return false;
    }

    if (!$conn) {
        return false;
    }

    $stmt = mysqli_prepare($conn, "INSERT INTO `messages` (`title`, `content`, `type`) VALUES (?, ?, ?)");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "sss", $title, $content, $type);
        $res = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return $res;
    }
    return false;
}
