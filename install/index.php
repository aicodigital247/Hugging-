<?php
/**
 * TradeNexa.com - DB and System Installer
 * Mobile-First Professional Setup Flow
 */

define('INSTALL_LOCK_FILE', dirname(__DIR__) . '/installed.lock');
define('CONFIG_FILE', dirname(__DIR__) . '/app/core/db.php');

if (file_exists(INSTALL_LOCK_FILE)) {
    die("TradeNexa is already installed. Delete 'installed.lock' to re-run setup.");
}

$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db_host = trim($_POST['db_host'] ?? '127.0.0.1');
    $db_user = trim($_POST['db_user'] ?? '');
    $db_pass = trim($_POST['db_pass'] ?? '');
    $db_name = trim($_POST['db_name'] ?? '');
    $admin_email = trim($_POST['admin_email'] ?? 'admin@saas.com');
    $admin_pass = trim($_POST['admin_pass'] ?? 'admin123');

    if (empty($db_user) || empty($db_name) || empty($admin_email) || empty($admin_pass)) {
        $error = "All fields are required.";
    } else {
        // Attempt database handshake using mysqli
        $conn = @mysqli_connect($db_host, $db_user, $db_pass);
        if (!$conn) {
            $error = "Database Connection Failed: " . mysqli_connect_error();
        } else {
            // Attempt to create database if non-existent
            $db_selected = @mysqli_select_db($conn, $db_name);
            if (!$db_selected) {
                $create_query = "CREATE DATABASE IF NOT EXISTS `" . mysqli_real_escape_string($conn, $db_name) . "` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
                if (!@mysqli_query($conn, $create_query)) {
                    $error = "Failed to create database: " . mysqli_error($conn);
                } else {
                    mysqli_select_db($conn, $db_name);
                }
            }

            if (empty($error)) {
                // Read and execute installer.sql
                $sql_file = dirname(__DIR__) . '/install/installer.sql';
                if (!file_exists($sql_file)) {
                    $error = "installer.sql was not found in the install directory.";
                } else {
                    $sql_content = file_get_contents($sql_file);
                    
                    // Basic SQL parser for multi-queries
                    $queries = preg_split("/;+(?=(?:[^'\"]*['\"][^'\"]*['\"])*[^'\"]*$)/", $sql_content);
                    $db_success = true;
                    
                    foreach ($queries as $query) {
                        $trimmed = trim($query);
                        if (!empty($trimmed)) {
                            if (!@mysqli_query($conn, $trimmed)) {
                                $error = "SQL Execute Error on Query: " . htmlspecialchars($trimmed) . " — [" . mysqli_error($conn) . "]";
                                $db_success = false;
                                break;
                            }
                        }
                    }

                    if ($db_success) {
                        // Insert/Update specialized admin details if changed
                        $hashed_pass = password_hash($admin_pass, PASSWORD_BCRYPT);
                        $admin_stmt = mysqli_prepare($conn, "INSERT INTO `users` (`email`, `password`, `role`, `plan`, `status`, `wallet_balance`) VALUES (?, ?, 'admin', 'vip', 'active', '1000.00000000') ON DUPLICATE KEY UPDATE `password` = ?, `role` = 'admin'");
                        if ($admin_stmt) {
                            mysqli_stmt_bind_param($admin_stmt, "ssss", $admin_email, $hashed_pass, $hashed_pass);
                            mysqli_stmt_execute($admin_stmt);
                        }

                        // Write Configuration DB file
                        $config_content = "<?php\n";
                        $config_content .= "/**\n * TradeNexa.com - Auto-Generated DB Connection\n */\n\n";
                        $config_content .= "define('DB_HOST', '" . addslashes($db_host) . "');\n";
                        $config_content .= "define('DB_USER', '" . addslashes($db_user) . "');\n";
                        $config_content .= "define('DB_PASS', '" . addslashes($db_pass) . "');\n";
                        $config_content .= "define('DB_NAME', '" . addslashes($db_name) . "');\n\n";
                        $config_content .= "\$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);\n\n";
                        $config_content .= "if (!\$conn) {\n";
                        $config_content .= "    die(\"Database connection error: \" . mysqli_connect_error());\n";
                        $config_content .= "}\n";
                        $config_content .= "mysqli_set_charset(\$conn, 'utf8mb4');\n";

                        // Ensure directory exists
                        if (!is_dir(dirname(CONFIG_FILE))) {
                            mkdir(dirname(CONFIG_FILE), 0755, true);
                        }

                        if (file_put_contents(CONFIG_FILE, $config_content) === false) {
                            $error = "Could not write database configuration to " . CONFIG_FILE . ". Verify folder permissions.";
                        } else {
                            // Generate Lock File
                            file_put_contents(INSTALL_LOCK_FILE, "Installed on " . date('Y-m-d H:i:s'));
                            $success = true;
                        }
                    }
                }
            }
            mysqli_close($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-950 text-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Install — TradeNexa.com</title>
    <!-- Tailwind CDN for Mobile Installer layout (Dark Mode default) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }
    </style>
</head>
<body class="flex flex-col min-h-screen justify-center items-center px-4 py-8 select-none">
    
    <!-- Mobile Container Block -->
    <div class="w-full max-w-sm bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-2xl flex flex-col relative overflow-hidden">
        
        <!-- Header Sparkle -->
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-48 h-1 bg-gradient-to-r from-emerald-500 to-indigo-500 rounded-full blur-sm opacity-80"></div>
        
        <div class="text-center mb-6 mt-2">
            <h1 class="text-2xl font-black tracking-tight text-transparent bg-clip-text bg-gradient-to-r from-emerald-400 to-teal-300">TradeNexa.com</h1>
            <p class="text-xs text-slate-400 mt-1">SaaS Installer Engine v1.0</p>
        </div>

        <?php if ($success): ?>
            <div class="bg-emerald-950/40 border border-emerald-800 rounded-2xl p-4 text-center mt-2 mb-6">
                <svg class="w-12 h-12 text-emerald-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <h3 class="text-emerald-300 font-bold text-lg">Installation Success Check!</h3>
                <p class="text-xs text-slate-300 mt-2">Database is fully functional, tables are seeded, configuration is locked, and admin is set up.</p>
                <p class="text-xs font-mono bg-slate-950 p-2 rounded-xl border border-slate-800 mt-3 text-slate-400">
                    email: <span class="text-slate-100">admin@saas.com</span><br>
                    pass: <span class="text-slate-100">admin123</span>
                </p>
                <div class="mt-6">
                    <a href="../public/index.php" class="inline-block w-full text-center bg-emerald-500 text-slate-950 py-3 font-bold px-4 rounded-xl shadow-lg shadow-emerald-500/20 hover:scale-[1.02] active:scale-[0.98] transition">
                        Enter Core Dashboard
                    </a>
                </div>
            </div>
        <?php else: ?>

            <?php if (!empty($error)): ?>
                <div class="bg-rose-950/40 border border-rose-900 rounded-xl p-3 mb-4 text-xs text-rose-300 flex items-center gap-2">
                    <svg class="w-5 h-5 flex-shrink-0 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                    <span><?= $error ?></span>
                </div>
            <?php endif; ?>

            <form action="" method="POST" class="space-y-4">
                
                <div>
                    <label class="block text-slate-400 text-xs font-semibold mb-1 uppercase tracking-wider">MySQL Database Host</label>
                    <input type="text" name="db_host" value="127.0.0.1" required class="w-full bg-slate-950 border border-slate-800 focus:border-indigo-500 rounded-xl px-3 py-2 text-sm text-slate-100 focus:outline-none transition">
                </div>

                <div>
                    <label class="block text-slate-400 text-xs font-semibold mb-1 uppercase tracking-wider">MySQL User</label>
                    <input type="text" name="db_user" placeholder="root" required class="w-full bg-slate-950 border border-slate-800 focus:border-indigo-500 rounded-xl px-3 py-2 text-sm text-slate-100 focus:outline-none transition">
                </div>

                <div>
                    <label class="block text-slate-400 text-xs font-semibold mb-1 uppercase tracking-wider">MySQL Password</label>
                    <input type="password" name="db_pass" placeholder="Database password" class="w-full bg-slate-950 border border-slate-800 focus:border-indigo-500 rounded-xl px-3 py-2 text-sm text-slate-100 focus:outline-none transition">
                </div>

                <div>
                    <label class="block text-slate-400 text-xs font-semibold mb-1 uppercase tracking-wider">MySQL Database Name</label>
                    <input type="text" name="db_name" placeholder="tradenexa_db" required class="w-full bg-slate-950 border border-slate-800 focus:border-indigo-500 rounded-xl px-3 py-2 text-sm text-slate-100 focus:outline-none transition">
                </div>

                <div class="border-t border-slate-800 pt-3">
                    <label class="block text-slate-400 text-xs font-semibold mb-1 uppercase tracking-wider">Admin Account Email</label>
                    <input type="email" name="admin_email" value="admin@saas.com" required class="w-full bg-slate-950 border border-slate-800 focus:border-indigo-500 rounded-xl px-3 py-2 text-sm text-slate-100 focus:outline-none transition">
                </div>

                <div>
                    <label class="block text-slate-400 text-xs font-semibold mb-1 uppercase tracking-wider">Admin Password</label>
                    <input type="password" name="admin_pass" value="admin123" required class="w-full bg-slate-950 border border-slate-800 focus:border-indigo-500 rounded-xl px-3 py-2 text-sm text-slate-100 focus:outline-none transition">
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full bg-indigo-600 text-white font-bold py-3 px-4 rounded-xl shadow-lg shadow-indigo-600/30 hover:bg-indigo-500 hover:scale-[1.01] active:scale-[0.99] transition cursor-pointer">
                        Execute DB Installation
                    </button>
                    <p class="text-[10px] text-slate-500 text-center mt-2">This installer compiles schema arrays & seeds static setting parameters key-pairs automatically.</p>
                </div>
            </form>
        <?php endif; ?>
    </div>
    
    <div class="text-center text-[10px] text-slate-600 mt-4 leading-relaxed">
        TradeNexa Inc. — Secure Crypto Intelligence Applet.<br>Designed for cPanel, Shared Apache, or VPS deployment.
    </div>

</body>
</html>
