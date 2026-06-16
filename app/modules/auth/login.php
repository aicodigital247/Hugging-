<?php
/**
 * TradeNexa.com - Authentication - Login Panel
 * Full-Screen Dark Interface optimized for Mobile Viewports
 */

require_once dirname(dirname(dirname(__DIR__))) . '/app/core/auth.php';
require_once dirname(dirname(dirname(__DIR__))) . '/app/core/session.php';
require_once dirname(dirname(dirname(__DIR__))) . '/app/core/security.php';

if (auth_is_logged_in()) {
    header("Location: index.php?page=user/market");
    exit;
}

$error = '';
$success_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $csrf = $_POST['csrf_token'] ?? '';
    
    if (!validate_csrf_token($csrf)) {
        $error = "Security validation expired. Retry.";
    } elseif (empty($email) || empty($password)) {
        $error = "Please fill in all inputs.";
    } else {
        $result = auth_login($email, $password);
        if ($result['success']) {
            set_flash_message('success', 'Access granted! Welcome to your digital cockpit.');
            header("Location: index.php?page=user/market");
            exit;
        } else {
            $error = $result['message'];
        }
    }
}

// Retrieve any flash messages (e.g. from registration)
$flash = get_flash_message();
if ($flash) {
    if ($flash['type'] === 'success') {
        $success_msg = $flash['message'];
    } else {
        $error = $flash['message'];
    }
}
?>
<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-950 text-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>Login — TradeNexa</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex flex-col min-h-screen justify-center items-center px-4 bg-slate-950">

    <div class="w-full max-w-sm bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-2xl relative overflow-hidden">
        
        <!-- Frame sparkle -->
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-48 h-1 bg-gradient-to-r from-emerald-500 to-indigo-500 rounded-full blur-sm opacity-85"></div>
        
        <div class="text-center mb-6">
            <h1 class="text-2xl font-black text-transparent bg-clip-text bg-gradient-to-r from-emerald-400 to-teal-300">TradeNexa</h1>
            <p class="text-xs text-slate-400 mt-1">AI Crypto Trading Intelligence SaaS</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="bg-rose-950/40 border border-rose-900 rounded-xl p-3 mb-4 text-xs text-rose-300 flex items-center gap-2">
                <svg class="w-5 h-5 flex-shrink-0 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                <span><?= secure_escape($error) ?></span>
            </div>
        <?php endif; ?>

        <?php if (!empty($success_msg)): ?>
            <div class="bg-emerald-950/40 border border-emerald-900 rounded-xl p-3 mb-4 text-xs text-emerald-300 flex items-center gap-2">
                <svg class="w-5 h-5 flex-shrink-0 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span><?= secure_escape($success_msg) ?></span>
            </div>
        <?php endif; ?>

        <form action="" method="POST" class="space-y-4">
            <?php csrf_input(); ?>

            <div>
                <label class="block text-slate-400 text-xs font-semibold mb-1 uppercase tracking-wider">Account Email</label>
                <input type="email" name="email" placeholder="admin@saas.com" required class="w-full bg-slate-950 border border-slate-800 focus:border-indigo-500 rounded-xl px-3 py-2 text-sm text-slate-100 focus:outline-none transition">
            </div>

            <div>
                <div class="flex justify-between mb-1">
                    <label class="block text-slate-400 text-xs font-semibold uppercase tracking-wider">Access Password</label>
                </div>
                <input type="password" name="password" placeholder="••••••••" required class="w-full bg-slate-950 border border-slate-800 focus:border-indigo-500 rounded-xl px-3 py-2 text-sm text-slate-100 focus:outline-none transition">
            </div>

            <div class="pt-2">
                <button type="submit" class="w-full bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold py-3 px-4 rounded-xl shadow-lg shadow-emerald-500/10 cursor-pointer hover:scale-[1.01] active:scale-[0.99] transition">
                    Access Platform Cockpit
                </button>
            </div>
        </form>

        <div class="border-t border-slate-800/80 mt-6 pt-4 text-center">
            <span class="text-xs text-slate-400">New trader on the block?</span>
            <a href="index.php?page=auth/register" class="text-xs font-bold text-indigo-400 hover:text-indigo-300 ml-1">
                Register Free Profile
            </a>
        </div>

        <div class="bg-slate-950 rounded-xl border border-slate-800/50 p-2.5 mt-4 text-[10px] text-slate-500 font-mono text-center">
            <span class="text-emerald-400 font-semibold">Demo credentials:</span><br>
            admin@saas.com / admin123
        </div>

    </div>

    <div class="text-[10px] text-zinc-600 mt-4 leading-relaxed text-center uppercase tracking-widest font-mono">
        TradeNexa.com security gateway
    </div>

</body>
</html>
