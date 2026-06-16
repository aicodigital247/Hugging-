<?php
/**
 * TradeNexa.com - Authentication - Logout Handler
 * Securely terminates sessions and clears state cookies
 */

require_once dirname(dirname(dirname(__DIR__))) . '/app/core/session.php';

// Empty actual session arrays
$_SESSION = [];

// Dissolve cookie instances
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy active backend structures
session_destroy();

session_start();
set_flash_message('success', 'You have been disconnected from the TradeNexa terminal safely.');
header("Location: index.php?page=auth/login");
exit;
