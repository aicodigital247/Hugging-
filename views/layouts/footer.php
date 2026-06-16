<?php
/**
 * TradeNexa.com - Global Platform Layout Footer & Bottom Tab Navbar
 */

require_once dirname(dirname(__DIR__)) . '/app/core/auth.php';

$active_page = isset($_GET['page']) ? $_GET['page'] : 'user/market';
$is_admin = auth_is_admin();

// Helper to render high-contrast active state
function get_active_class($page_matches, $current_loaded) {
    if (in_array($current_loaded, $page_matches)) {
        return 'text-emerald-400 font-bold translate-y-[-2px] scale-102';
    }
    return 'text-slate-500 hover:text-slate-300';
}
?>
        </main> <!-- End of view output container container -->

        <!-- Bottom Tabbed Bar Navigation Menu (Mobile-First touch target optimized) -->
        <nav class="w-full max-w-md bg-slate-950/90 backdrop-blur-md border-t border-slate-900 fixed bottom-0 z-40 px-2 py-2 flex items-center justify-around shadow-2xl">
            
            <!-- Tab: Markets -->
            <a href="index.php?page=user/market" class="flex flex-col items-center justify-center py-1 px-2.5 transition text-center cursor-pointer select-none <?= get_active_class(['user/market', ''], $active_page) ?>">
                <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                </svg>
                <span class="text-[9px] tracking-wide font-medium">Market</span>
            </a>

            <!-- Tab: Interactive Charts -->
            <a href="index.php?page=market/charts" class="flex flex-col items-center justify-center py-1 px-2.5 transition text-center cursor-pointer select-none <?= get_active_class(['market/charts'], $active_page) ?>">
                <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
                <span class="text-[9px] tracking-wide font-medium">Charts</span>
            </a>

            <!-- Tab: AI Signals -->
            <a href="index.php?page=user/signals" class="flex flex-col items-center justify-center py-1 px-2.5 transition text-center cursor-pointer select-none relative <?= get_active_class(['user/signals'], $active_page) ?>">
                <!-- Flashy Notification Badge for active signal changes -->
                <span class="absolute top-1 right-2.5 w-1.5 h-1.5 bg-rose-500 rounded-full animate-ping"></span>
                <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                </svg>
                <span class="text-[9px] tracking-wide font-medium">Signals</span>
            </a>

            <!-- Tab: Wallet Ledger -->
            <a href="index.php?page=user/wallet" class="flex flex-col items-center justify-center py-1 px-2.5 transition text-center cursor-pointer select-none <?= get_active_class(['user/wallet'], $active_page) ?>">
                <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span class="text-[9px] tracking-wide font-medium">Ledger</span>
            </a>

            <!-- Tab: Profile Settings / Admin Core Portal -->
            <?php if ($is_admin): ?>
                <a href="index.php?page=admin/dashboard" class="flex flex-col items-center justify-center py-1 px-2.5 transition text-center cursor-pointer select-none <?= get_active_class(['admin/dashboard', 'admin/users', 'admin/signals', 'admin/ads', 'admin/messages', 'admin/settings'], $active_page) ?>">
                    <svg class="w-5 h-5 mb-0.5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path>
                    </svg>
                    <span class="text-[9px] tracking-wide font-bold text-indigo-300">Admin</span>
                </a>
            <?php else: ?>
                <a href="index.php?page=user/profile" class="flex flex-col items-center justify-center py-1 px-2.5 transition text-center cursor-pointer select-none <?= get_active_class(['user/profile', 'user/settings', 'billing/plans'], $active_page) ?>">
                    <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    <span class="text-[9px] tracking-wide font-medium">Profile</span>
                </a>
            <?php endif; ?>

        </nav>

    </div> <!-- End of Mobile Screen Frame Constraint -->

</body>
</html>
