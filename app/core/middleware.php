<?php
/**
 * TradeNexa.com - Routing & Access Middlewares
 * Secures modules, monitors bans, and strictly enforces Mobile-Only layouts
 */

require_once __DIR__ . '/auth.php';

/**
 * Ensures user is authenticated; if not redirects to login
 */
function middleware_require_login() {
    if (!auth_is_logged_in()) {
        set_flash_message('warning', 'Please sign in to access your TradeNexa cockpit.');
        header("Location: index.php?page=auth/login");
        exit;
    }
    
    // Check ban status on active session users
    $user = auth_current_user();
    if ($user && $user['status'] === 'banned') {
        session_destroy();
        session_start();
        set_flash_message('error', 'Your session has been terminated. Account status: Suspended.');
        header("Location: index.php?page=auth/login");
        exit;
    }
}

/**
 * Ensures authenticated session is administrator; blocks standard users
 */
function middleware_require_admin() {
    middleware_require_login();
    if (!auth_is_admin()) {
        header("HTTP/1.1 403 Forbidden");
        die("<h3>403 Unauthorized Access</h3>The operational dashboard is reserved solely for TradeNexa Core Administrators.");
    }
}

/**
 * Blocks desktop users to maintain absolute visual and structural mobile design fidelity
 */
function middleware_block_desktop_users() {
    // If user explicitly requests bypass for testing or in iframe, skip
    if (isset($_GET['allow_desktop']) || isset($_SESSION['allow_desktop'])) {
        $_SESSION['allow_desktop'] = true;
        return;
    }

    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
    
    // Comprehensive Mobile detection match
    $is_mobile = (bool)preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i', $ua) ||
                 (bool)preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)\-|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|xda(\-|[2-7]|g)|yas\-|your|zeto|zte\-/i', substr($ua,0,4));

    if (!$is_mobile) {
        ?>
        <!DOCTYPE html>
        <html lang="en" class="h-full bg-slate-950 text-slate-100">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>TradeNexa — Smartphone Optimized Only </title>
            <script src="https://cdn.tailwindcss.com"></script>
        </head>
        <body class="flex flex-col min-h-screen justify-center items-center px-4 select-none text-center bg-radial from-slate-900 to-slate-950">
            <div class="max-w-md bg-slate-900 border border-slate-800 p-8 rounded-3xl shadow-2xl relative overflow-hidden">
                <div class="w-16 h-16 bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 rounded-2xl flex items-center justify-center mx-auto mb-6">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <h1 class="text-2xl font-black text-slate-100 mb-3 tracking-tight">TradeNexa Core Terminal</h1>
                <p class="text-sm text-slate-400 leading-relaxed mb-6">
                    To maintain the highest levels of visual fidelity, active chart layout precision, and signal-feed latency optimization, TradeNexa is compiled <strong class="text-emerald-400">exclusively for Mobile Safari and Chrome</strong> views.
                </p>
                <div class="bg-slate-950/55 rounded-2xl p-4 border border-slate-800/80 mb-6 font-mono text-[11px] text-slate-500 text-left">
                    <span class="text-indigo-400">System Constraint:</span> USER_AGENT_MOBILE_REQD<br>
                    <span class="text-indigo-400">Resolution:</span> Resize browser to Mobile (iPhone 12/14 Pro width < 480px) in web inspector, or visit via mobile app browser.
                </div>
                <div class="space-y-3">
                    <a href="index.php?allow_desktop=1" class="block w-full bg-indigo-600 hover:bg-indigo-500 text-white font-bold py-3 px-4 rounded-xl shadow-lg transition">
                        Bypass Restriction (Dev Mode)
                    </a>
                </div>
            </div>
            <div class="text-[10px] text-zinc-600 mt-4 font-mono uppercase tracking-widest">
                TradeNexa security protocols active
            </div>
        </body>
        </html>
        <?php
        exit;
    }
}
