<?php
/**
 * TradeNexa.com - Site Settings Registry
 * Load, store, and edit dynamic SaaS configs (Bybit, pricing tiers, signal engine coefficients)
 */

require_once __DIR__ . '/db.php';

$_settings_cache = null;

/**
 * Loads all settings from datastore into local memory
 */
function settings_load_all() {
    global $conn, $_settings_cache;
    
    if ($_settings_cache !== null) {
        return $_settings_cache;
    }
    
    $_settings_cache = [];
    if (!$conn) {
        return $_settings_cache;
    }
    
    $res = mysqli_query($conn, "SELECT `setting_key`, `setting_value` FROM `settings`");
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $_settings_cache[$row['setting_key']] = $row['setting_value'];
        }
    }
    
    return $_settings_cache;
}

/**
 * Retrieves a targeted setting key or returns fallbacks
 */
function settings_get($key, $default = '') {
    $all = settings_load_all();
    return isset($all[$key]) ? $all[$key] : $default;
}

/**
 * Sets or updates a SaaS setting key value
 */
function settings_set($key, $value) {
    global $conn, $_settings_cache;
    
    if (!$conn) {
        return false;
    }
    
    $val_str = strval($value);
    $stmt = mysqli_prepare($conn, "INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `setting_value` = ?");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "sss", $key, $val_str, $val_str);
        $result = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        // Refresh local memory cache state
        if ($result && $_settings_cache !== null) {
            $_settings_cache[$key] = $val_str;
        }
        return $result;
    }
    
    return false;
}
