<?php
/**
 * TradeNexa.com - Main Gateway Entry Point
 * Hosts core, redirects to installer if unconfigured
 */

define('TRADENEXA_ROOT', dirname(__DIR__));

// Check if locked; if not force installer configuration
if (!file_exists(TRADENEXA_ROOT . '/installed.lock')) {
    header("Location: ../install/index.php");
    exit;
}

// Bootstrap router
require_once TRADENEXA_ROOT . '/app/core/db.php';
require_once TRADENEXA_ROOT . '/app/core/router.php';

router_execute();
