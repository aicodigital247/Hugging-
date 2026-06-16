<?php
/**
 * TradeNexa.com - Global Platform Layout Header
 * Mobile-First Dark UI default
 */

require_once dirname(dirname(__DIR__)) . '/app/core/session.php';
require_once dirname(dirname(__DIR__)) . '/app/core/auth.php';
require_once dirname(dirname(__DIR__)) . '/app/core/settings.php';
require_once dirname(dirname(__DIR__)) . '/app/core/saas.php';

$curr_user = auth_current_user();
$site_name = settings_get('site_name', 'TradeNexa');
$page_title = isset($page_title) ? $page_title : 'AI Crypto Intelligence';
$tier = saas_get_current_tier();
?>
<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-950 text-slate-100 select-none">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="theme-color" content="#020617">
    <title><?= secure_escape($page_title) ?> — TradeNexa</title>
    <!-- Inter + JetBrains Mono Google Google fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;900&family=JetBrains+Mono:wght@400;600;700&display=swap" rel="stylesheet">
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        mono: ['JetBrains Mono', 'monospace'],
                    },
                    colors: {
                        brand: {
                            50: '#f0fdf4',
                            100: '#dcfce7',
                            200: '#bbf7d0',
                            500: '#10b981', // emerald
                            600: '#059669',
                            950: '#022c22',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            -webkit-tap-highlight-color: transparent;
        }
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>
</head>
<body class="flex flex-col min-h-screen bg-slate-950 text-slate-100 pb-20 justify-start items-center">

    <!-- Mobile Screen Frame Constraint (Centers it on screen if previewing on desktop, but feels like native mobile) -->
    <div class="w-full max-w-md min-h-screen flex flex-col bg-slate-950 border-x border-slate-900 shadow-2xl relative">
        
        <!-- Top Status Interface Header -->
        <header class="w-full bg-slate-950/80 backdrop-blur-md sticky top-0 z-40 border-b border-slate-900 px-4 py-3 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div class="w-7 h-7 bg-emerald-500 rounded-lg flex items-center justify-center shadow-lg shadow-emerald-500/20 font-black text-slate-950 text-sm">
                    TN
                </div>
                <div>
                    <h2 class="font-bold text-sm tracking-tight text-slate-100 flex items-center gap-1">
                        <?= secure_escape($site_name) ?>
                        <span class="text-[9px] uppercase border bg-slate-900 border-slate-800 text-slate-400 rounded px-1 scale-90 font-semibold leading-relaxed">SaaS</span>
                    </h2>
                </div>
            </div>

            <!-- Header Balance or Auth Pill -->
            <div class="flex items-center gap-2">
                <?php if ($curr_user): ?>
                    <!-- Quick Wallet Payout link -->
                    <a href="index.php?page=user/wallet" class="bg-slate-900 border border-slate-800 rounded-xl px-2.5 py-1 text-right hover:border-slate-700 transition flex items-center gap-1.5 cursor-pointer">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span class="font-mono text-[10px] font-bold text-emerald-400">$<?= number_format(floatval($curr_user['wallet_balance']), 2) ?></span>
                    </a>
                    
                    <!-- Premium Tier Tag -->
                    <span class="text-[9px] font-extrabold uppercase px-1.5 py-0.5 rounded tracking-wide border
                        <?php if($tier === TIER_VIP): ?>
                            bg-purple-950/40 text-purple-400 border-purple-800
                        <?php elseif($tier === TIER_PRO): ?>
                            bg-indigo-950/40 text-indigo-400 border-indigo-800
                        <?php else: ?>
                            bg-slate-900 text-slate-500 border-slate-800
                        <?php endif; ?>">
                        <?= strtoupper($tier) ?>
                    </span>
                <?php else: ?>
                    <a href="index.php?page=auth/login" class="text-xs bg-indigo-600 hover:bg-indigo-500 text-white font-semibold py-1 px-3 rounded-lg shadow-md transition cursor-pointer">
                        Login
                    </a>
                <?php endif; ?>
            </div>
        </header>

        <!-- Flash messages system -->
        <?php $flash = get_flash_message(); if ($flash): ?>
            <div class="px-4 pt-4">
                <div class="rounded-xl p-3 text-xs border flex items-start gap-2.5 shadow-lg animate-bounce
                    <?php if($flash['type'] === 'success'): ?>
                        bg-emerald-950/50 text-emerald-300 border-emerald-900
                    <?php elseif($flash['type'] === 'error'): ?>
                        bg-rose-950/50 text-rose-300 border-rose-900
                    <?php else: ?>
                        bg-indigo-950/50 text-indigo-300 border-indigo-900
                    <?php endif; ?>">
                    <svg class="w-4 h-4 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span><?= secure_escape($flash['message']) ?></span>
                </div>
            </div>
        <?php endif; ?>

        <!-- Active View Output container -->
        <main class="flex-1 w-full flex flex-col p-4 relative">
