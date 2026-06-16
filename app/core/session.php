<?php
/**
 * TradeNexa.com - Secure Session Manager
 * Handles user, admin, and billing sessions securely
 */

if (session_status() === PHP_SESSION_NONE) {
    // Inject secure cookie attributes for SaaS context
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_samesite', 'Lax');
    
    // In production systems, enforce secure SSL session cookies
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        ini_set('session.cookie_secure', 1);
    }
    
    session_start();
}

/**
 * Regenerate session securely on identity transitions to bypass session fixation
 */
function secure_session_regenerate() {
    session_regenerate_id(true);
}

/**
 * Ensures CSRF prevention tokens exist
 */
function get_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validates postback CSRF fields against session keys
 */
function validate_csrf_token($token) {
    $session_token = isset($_SESSION['csrf_token']) ? $_SESSION['csrf_token'] : '';
    if (empty($token) || empty($session_token)) {
        return false;
    }
    return hash_equals($session_token, $token);
}

/**
 * Prints a hidden secure form input for CSRF validation
 */
function csrf_input() {
    $token = get_csrf_token();
    echo '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
}

/**
 * Sets session flash messages
 */
function set_flash_message($type, $msg) {
    $_SESSION['flash'] = [
        'type' => $type, // 'success', 'error', 'info', 'warning'
        'message' => $msg
    ];
}

/**
 * Retrieves and clears active flash message
 */
function get_flash_message() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}
