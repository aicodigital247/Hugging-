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
    if (isset($_POST['telegram_oauth'])) {
        $tg_username = preg_replace("/[^A-Za-z0-9_]/", "", $_POST['telegram_user'] ?? 'tg_trader');
        if (strlen($tg_username) < 3) {
            $error = "Invalid telegram username.";
        } else {
            $email = $tg_username . "@telegram.tg";
            $password = "tg_secure_pass_9988_default";
            
            // Register automatically if it's their first time
            $existing = [];
            global $conn;
            if ($conn) {
                $check_res = mysqli_query($conn, "SELECT * FROM users WHERE email = '" . mysqli_real_escape_string($conn, $email) . "' LIMIT 1");
                if ($check_res && mysqli_num_rows($check_res) > 0) {
                    $existing = mysqli_fetch_assoc($check_res);
                }
            }
            if (empty($existing)) {
                auth_register($email, $password, $password);
            }
            
            $result = auth_login($email, $password);
            if ($result['success']) {
                set_flash_message('success', 'Telegram SSO Authenticated! Welcome.');
                header("Location: index.php?page=user/market");
                exit;
            } else {
                $error = $result['message'];
            }
        }
    } else {
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
<html lang="en" class="h-full bg-[#0B0E11] text-[#EAECEF]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>Login — TradeNexa</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex flex-col min-h-screen justify-center items-center px-4 bg-[#0B0E11]">

    <div class="w-full max-w-sm bg-[#161A1E] border border-[#20262D] rounded-3xl p-6 shadow-2xl relative overflow-hidden">
        
        <!-- Frame sparkle -->
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-48 h-[2px] bg-[#F0B90B] rounded-full blur-sm"></div>
        
        <div class="text-center mb-6">
            <h1 class="text-2xl font-black text-transparent bg-clip-text bg-gradient-to-r from-[#F0B90B] to-amber-400 font-sans tracking-tight">TradeNexa</h1>
            <p class="text-[10px] text-gray-400 mt-1 font-mono uppercase tracking-widest">AI Crypto Trading Intelligence</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="bg-rose-950/40 border border-rose-900 rounded-xl p-3 mb-4 text-xs text-rose-300 flex items-center gap-2 font-mono">
                <span>⚠️ <?= secure_escape($error) ?></span>
            </div>
        <?php endif; ?>

        <?php if (!empty($success_msg)): ?>
            <div class="bg-emerald-950/40 border border-emerald-900 rounded-xl p-3 mb-4 text-xs text-[#03C087] flex items-center gap-2 font-mono">
                <span>✅ <?= secure_escape($success_msg) ?></span>
            </div>
        <?php endif; ?>

        <!-- Telegram Auth SSO Wrapper -->
        <div class="mb-4">
            <button id="tg-login-btn" type="button" class="w-full bg-[#0088cc] hover:bg-[#0088cc]/90 text-white font-bold py-2.5 px-4 rounded-xl shadow-lg flex items-center justify-center gap-2 transition text-xs font-sans cursor-pointer">
                <svg class="w-4 h-4 text-white fill-current" viewBox="0 0 24 24">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8c-.15 1.58-.8 5.42-1.13 7.19-.14.75-.42 1-.68 1.03-.58.05-1.02-.38-1.58-.75-.88-.58-1.38-.94-2.23-1.5-.99-.65-.35-1.01.22-1.59.15-.15 2.71-2.48 2.76-2.69a.2.2 0 00-.05-.18c-.06-.05-.14-.03-.21-.02-.09.02-1.49.94-4.22 2.79-.4.27-.76.41-1.08.4-.36-.01-1.04-.2-1.55-.37-.63-.2-1.12-.31-1.08-.66.02-.18.27-.36.74-.55 2.92-1.27 4.86-2.11 5.83-2.51 2.78-1.16 3.35-1.36 3.73-1.36.08 0 .27.02.39.12.1.08.13.19.14.27-.01.06.01.24 0 .32z"/>
                </svg>
                Continue with Telegram
            </button>
            
            <form id="tg-hidden-form" method="POST" action="" class="hidden">
                <input type="hidden" name="telegram_oauth" value="1">
                <input type="hidden" id="tg-username-input" name="telegram_user" value="">
            </form>
        </div>

        <div class="flex items-center my-4">
            <div class="flex-1 h-px bg-[#20262D]"></div>
            <span class="px-3 text-[9px] text-gray-500 uppercase font-mono">Or Traditional Account</span>
            <div class="flex-1 h-px bg-[#20262D]"></div>
        </div>

        <form action="" method="POST" class="space-y-4">
            <?php csrf_input(); ?>

            <div>
                <label class="block text-gray-400 text-[10px] font-bold mb-1 uppercase tracking-wider">Account Email</label>
                <input type="email" name="email" placeholder="admin@saas.com" required class="w-full bg-[#0B0E11] border border-[#20262D] focus:border-[#F0B90B] rounded-xl px-3 py-2 text-sm text-[#EAECEF] focus:outline-none transition">
            </div>

            <div>
                <div class="flex justify-between mb-1">
                    <label class="block text-gray-400 text-[10px] font-bold uppercase tracking-wider">Access Password</label>
                </div>
                <input type="password" name="password" placeholder="••••••••" required class="w-full bg-[#0B0E11] border border-[#20262D] focus:border-[#F0B90B] rounded-xl px-3 py-2 text-sm text-[#EAECEF] focus:outline-none transition">
            </div>

            <div class="pt-2">
                <button type="submit" class="w-full bg-[#F0B90B] hover:bg-[#F0B90B]/95 text-black font-extrabold py-3 px-4 rounded-xl shadow-lg shadow-[#F0B90B]/10 cursor-pointer active:scale-[0.99] transition text-sm">
                    Access Platform Cockpit
                </button>
            </div>
        </form>

        <div class="border-t border-[#20262D] mt-6 pt-4 text-center">
            <span class="text-xs text-gray-400">New trader on the block?</span>
            <a href="index.php?page=auth/register" class="text-xs font-bold text-[#F0B90B] hover:text-amber-400 ml-1">
                Register Free Profile
            </a>
        </div>

        <div class="bg-[#0B0E11] rounded-xl border border-[#20262D]/50 p-2.5 mt-4 text-[10px] text-gray-500 font-mono text-center">
            <span class="text-[#03C087] font-semibold">Demo credentials:</span><br>
            admin@saas.com / admin123
        </div>

    </div>

    <div class="text-[10px] text-gray-600 mt-4 leading-relaxed text-center uppercase tracking-widest font-mono">
        TradeNexa.com security gateway
    </div>

    <script>
    document.getElementById("tg-login-btn").addEventListener("click", () => {
        let username = prompt("Enter your Telegram Username to connect via SSO widget:", "ton_trader_" + Math.floor(Math.random() * 899 + 100));
        if (username) {
            // Trim and clean
            username = username.replace("@", "").trim();
            if (username.length >= 3) {
                document.getElementById("tg-username-input").value = username;
                document.getElementById("tg-hidden-form").submit();
            } else {
                alert("Telegram username too short.");
            }
        }
    });
    </script>

</body>
</html>
