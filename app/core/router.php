<?php
/**
 * TradeNexa.com - Core Routing Table
 * Zero-dependency custom router mapping paths and fallback parameters directly to view modules
 */

require_once __DIR__ . '/session.php';
require_once __DIR__ . '/middleware.php';

/**
 * Executes standard routing matches and serves active panels
 */
function router_execute() {
    // 1. Strictly enforce Mobile-First block checks at the door
    middleware_block_desktop_users();

    $page = isset($_GET['page']) ? trim($_GET['page']) : '';
    $symbol = isset($_GET['symbol']) ? strtoupper(trim($_GET['symbol'])) : 'BTCUSDT';
    
    // Parse REQUET_URI for clean URI slugs (e.g. /trade/BTCUSDT or /market/charts)
    $req = $_SERVER['REQUEST_URI'] ?? '';
    // Strip parent index folders if any
    $parsed_path = parse_url($req, PHP_URL_PATH);
    $script_name = $_SERVER['SCRIPT_NAME'] ?? '';
    $sub_folder = dirname($script_name);
    
    if ($sub_folder !== '/' && strpos($parsed_path, $sub_folder) === 0) {
        $parsed_path = substr($parsed_path, strlen($sub_folder));
    }
    
    $parsed_path = trim($parsed_path, '/');
    
    // Decipher route paths
    if (empty($page)) {
        if (!empty($parsed_path) && $parsed_path !== 'index.php' && $parsed_path !== 'public/index.php') {
            $parts = explode('/', $parsed_path);
            
            if ($parts[0] === 'trade' && isset($parts[1])) {
                $page = 'market/charts';
                $symbol = strtoupper($parts[1]);
                $_GET['symbol'] = $symbol;
            } elseif (count($parts) >= 2) {
                $page = $parts[0] . '/' . $parts[1];
            } else {
                $page = $parts[0];
            }
        }
    }

    if (empty($page) || $page === 'index.php') {
        $page = 'user/market'; // default page
    }

    // List of registered routes and their absolute file paths inside /app/modules/
    $modules_dir = dirname(__DIR__) . '/modules/';
    $target_file = $modules_dir . $page . '.php';

    // Verify file limits and include layouts
    if (file_exists($target_file)) {
        // Enforce login gatekeepers on private modules
        if (strpos($page, 'user/') === 0 && $page !== 'user/market') {
            middleware_require_login();
        }
        
        if (strpos($page, 'admin/') === 0) {
            middleware_require_admin();
        }
        
        if (strpos($page, 'billing/') === 0) {
            middleware_require_login();
        }

        // Render header and footer for non-auth layout views
        $is_auth_layout = (strpos($page, 'auth/') === 0);
        
        if (!$is_auth_layout) {
            $page_title = ucwords(str_replace('user/', '', str_replace('market/', '', $page))) . ' Cockpit';
            include dirname(dirname(__DIR__)) . '/views/layouts/header.php';
        }
        
        // Execute operational module file
        include $target_file;
        
        if (!$is_auth_layout) {
            include dirname(dirname(__DIR__)) . '/views/layouts/footer.php';
        }
    } else {
        // Fallback 404
        include dirname(dirname(__DIR__)) . '/views/layouts/header.php';
        ?>
        <div class="flex-1 flex flex-col justify-center items-center text-center p-6 bg-slate-900 border border-slate-800 rounded-3xl mt-12 py-16">
            <div class="w-16 h-16 bg-rose-500/10 text-rose-500 border border-rose-500/20 rounded-2xl flex items-center justify-center mb-6">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
            </div>
            <h1 class="text-2xl font-black text-slate-100">Page Not Found</h1>
            <p class="text-xs text-slate-400 mt-2 max-w-xs leading-relaxed">The TradeNexa routing table is unable to bind standard controller parameters for route code '<?= secure_escape($page) ?>'. Check address bounds.</p>
            <div class="mt-8 w-full">
                <a href="index.php?page=user/market" class="block w-full bg-indigo-600 hover:bg-indigo-500 text-white font-bold py-3 px-4 rounded-xl transition cursor-pointer">
                    Back to Market Core
                </a>
            </div>
        </div>
        <?php
        include dirname(dirname(__DIR__)) . '/views/layouts/footer.php';
    }
}
