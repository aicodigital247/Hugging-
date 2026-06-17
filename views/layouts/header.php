<?php
/**
 * TradeNexa.com - Global Platform Layout Header
 * Mobile-First Dark UI default
 */

require_once dirname(dirname(__DIR__)) . '/app/core/session.php';
require_once dirname(dirname(__DIR__)) . '/app/core/auth.php';
require_once dirname(dirname(__DIR__)) . '/app/core/settings.php';
require_once dirname(dirname(__DIR__)) . '/app/core/saas.php';
require_once dirname(dirname(__DIR__)) . '/app/core/i18n.php';

$curr_user = auth_current_user();
$site_name = settings_get('site_name', 'Bybit Intel');
$page_title = isset($page_title) ? $page_title : 'AI Crypto Intelligence';
$tier = saas_get_current_tier();
?>
<!DOCTYPE html>
<html lang="<?= APP_LANG ?>" class="h-full bg-[#0B0E11] text-[#EAECEF] select-none">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="theme-color" content="#0B0E11">
    <title><?= secure_escape($page_title) ?> — <?= secure_escape($site_name) ?></title>
    <!-- Inter + JetBrains Mono Google Google fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;900&family=JetBrains+Mono:wght@400;600;700&display=swap" rel="stylesheet">
    <!-- Tailwind CSS CDN configured with Bybit theme rules -->
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
                            50: '#fffbf0',
                            100: '#fef5d8',
                            200: '#fde492',
                            500: '#F0B90B', // Bybit vibrant yellow
                            600: '#d19900',
                            950: '#1e1400',
                        },
                        bybitDark: {
                            base: '#0B0E11',    // Main viewport background
                            card: '#161A1E',    // Component panels
                            border: '#20262D',  // Subtle grey boundaries
                            input: '#1B2026',   // Fields and inputs
                            tag: '#2A3037'      // Standard badge tags
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
<body class="flex flex-col min-h-screen bg-[#0B0E11] text-[#EAECEF] pb-20 justify-start items-center">

    <!-- Mobile Screen Frame Constraint (Centers it on screen if previewing on desktop, but feels like native mobile) -->
    <div class="w-full max-w-md min-h-screen flex flex-col bg-[#0B0E11] border-x border-[#20262D] shadow-2xl relative">
        
        <!-- Top Status Interface Header -->
        <header class="w-full bg-[#0B0E11]/90 backdrop-blur-md sticky top-0 z-40 border-b border-[#20262D] px-4 py-3 flex items-center justify-between">
            <div class="flex items-center gap-1.5">
                <div class="w-7 h-7 bg-[#F0B90B] rounded-lg flex items-center justify-center shadow-lg shadow-[#F0B90B]/20 font-black text-black text-xs font-mono">
                    BB
                </div>
                <div>
                    <h2 class="font-bold text-xs tracking-tight text-[#EAECEF] leading-none">
                        <?= secure_escape($site_name) ?>
                    </h2>
                    <span class="text-[8px] uppercase text-[#F0B90B] font-mono tracking-widest block mt-0.5 font-bold">PRO</span>
                </div>

                <!-- Persistent Lang Switcher -->
                <div class="flex items-center gap-0.5 bg-[#161A1E] border border-[#20262D] rounded-lg p-0.5 ml-1">
                    <a href="?<?= http_build_query(array_merge($_GET, ['lang' => 'en'])) ?>" class="text-[8px] px-1 py-0.5 rounded font-black font-mono <?= APP_LANG === 'en' ? 'bg-[#F0B90B] text-black' : 'text-gray-400' ?>">EN</a>
                    <a href="?<?= http_build_query(array_merge($_GET, ['lang' => 'es'])) ?>" class="text-[8px] px-1 py-0.5 rounded font-black font-mono <?= APP_LANG === 'es' ? 'bg-[#F0B90B] text-black' : 'text-gray-400' ?>">ES</a>
                    <a href="?<?= http_build_query(array_merge($_GET, ['lang' => 'zh'])) ?>" class="text-[8px] px-1 py-0.5 rounded font-black font-mono <?= APP_LANG === 'zh' ? 'bg-[#F0B90B] text-black' : 'text-gray-400' ?>">ZH</a>
                </div>
            </div>

            <!-- Header Balance or Auth Pill -->
            <div class="flex items-center gap-1.5">
                <?php if ($curr_user): ?>
                    <!-- Quick Wallet Payout link -->
                    <a href="index.php?page=user/wallet" class="bg-[#161A1E] border border-[#20262D] rounded-xl px-2.5 py-1 text-right hover:border-[#2A3037] transition flex items-center gap-1.5 cursor-pointer font-mono">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#F0B90B] animate-pulse"></span>
                        <span class="font-mono text-[10px] font-bold text-[#F0B90B]">$<?= number_format(floatval($curr_user['wallet_balance']), 2) ?></span>
                    </a>
                    
                    <!-- Premium Tier Tag -->
                    <span class="text-[9px] font-extrabold uppercase px-1.5 py-0.5 rounded tracking-wide border
                        <?php if($tier === TIER_VIP): ?>
                            bg-amber-950/40 text-amber-400 border-amber-800
                        <?php elseif($tier === TIER_PRO): ?>
                            bg-[#2A3037] text-[#F0B90B] border-[#2D343B]
                        <?php else: ?>
                            bg-[#1B2026] text-gray-500 border-[#20262D]
                        <?php endif; ?>">
                        <?= strtoupper($tier) ?>
                    </span>
                <?php else: ?>
                    <a href="index.php?page=auth/login" class="text-xs bg-[#F0B90B] hover:bg-[#F0B90B]/90 text-black font-black py-1 px-3 rounded-lg shadow-md transition cursor-pointer font-sans">
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
                        bg-amber-950/50 text-[#F0B90B] border-amber-900
                    <?php elseif($flash['type'] === 'error'): ?>
                        bg-rose-950/50 text-rose-300 border-rose-900
                    <?php else: ?>
                        bg-blue-950/50 text-blue-300 border-blue-900
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
