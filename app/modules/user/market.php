<?php
/**
 * TradeNexa.com - Users - Main Market Cockpit
 * Lists major cryptocurrency ticker values, percentages, and AI Signal briefs
 */

require_once dirname(dirname(dirname(__DIR__))) . '/app/services/bybit_service.php';
require_once dirname(dirname(dirname(__DIR__))) . '/app/services/ai_signal_engine.php';
require_once dirname(dirname(dirname(__DIR__))) . '/app/services/notification_service.php';
require_once dirname(dirname(dirname(__DIR__))) . '/app/core/saas.php';
require_once dirname(dirname(dirname(__DIR__))) . '/app/core/db.php';

$tickers = bybit_get_market_tickers();
$show_ads = saas_should_show_ads();
$bulletins = notification_get_all(2);

// Retrieve active ads from database
$ads = [];
if ($conn) {
    $res = mysqli_query($conn, "SELECT * FROM `ads` WHERE `active` = 1 AND `placement` = 'in_feed' LIMIT 1");
    if ($res) {
        $ads = mysqli_fetch_assoc($res);
    }
}
?>

<!-- Announcement Bulletin Section -->
<?php if (!empty($bulletins)): ?>
    <div class="mb-5">
        <div class="flex items-center justify-between mb-2">
            <span class="text-xs font-semibold text-slate-400 uppercase tracking-widest">Active Bulletins</span>
            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
        </div>
        <div class="space-y-2">
            <?php foreach ($bulletins as $bulletin): ?>
                <div class="bg-slate-900/60 border border-slate-800/80 rounded-2xl p-3 flex gap-2.5 items-start">
                    <div class="w-6 h-6 flex-shrink-0 bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 rounded-lg flex items-center justify-center text-xs">
                        📣
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-slate-200"><?= secure_escape($bulletin['title']) ?></h4>
                        <p class="text-[10px] text-slate-400 mt-0.5 leading-relaxed"><?= secure_escape($bulletin['content']) ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

<!-- Core Market Tickers Heading -->
<div class="mb-4">
    <h3 class="text-xs font-semibold text-slate-400 uppercase tracking-widest mb-3">Crypto Assets Matrix</h3>
    
    <!-- Tickers Scroll Feed List -->
    <div class="space-y-3">
        <?php foreach ($tickers as $symbol => $data): ?>
            <a href="index.php?page=market/charts&symbol=<?= $symbol ?>" class="block bg-slate-900 border border-slate-800 rounded-2xl p-3.5 hover:border-slate-700 active:scale-[0.98] transition cursor-pointer">
                <div class="flex items-center justify-between">
                    
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-slate-950 flex items-center justify-center border border-slate-800 font-bold text-slate-300 text-xs">
                            <?= substr($symbol, 0, 3) ?>
                        </div>
                        <div>
                            <div class="flex items-center gap-1.5">
                                <h4 class="text-sm font-bold text-slate-200"><?= secure_escape($data['name']) ?></h4>
                                <span class="text-[9px] text-slate-500 font-mono font-bold"><?= $symbol ?></span>
                            </div>
                            <span class="text-[10px] text-slate-400">Trade Bybit linear perpetual</span>
                        </div>
                    </div>

                    <div class="text-right">
                        <div class="font-mono text-sm font-black text-slate-100">
                            $<?= number_format($data['price'], ($data['price'] < 5 ? 4 : 2)) ?>
                        </div>
                        <div class="font-mono text-[10px] font-bold mt-0.5 <?= ($data['change'] >= 0 ? 'text-emerald-400' : 'text-rose-400') ?>">
                            <?= ($data['change'] >= 0 ? '▲ +' : '▼ ') ?><?= number_format($data['change'], 2) ?>%
                        </div>
                    </div>

                </div>
            </a>
        <?php endforeach; ?>
    </div>
</div>

<!-- SaaS Monetization System (Free Tier Native Banner Ad Showcase) -->
<?php if ($show_ads && !empty($ads)): ?>
    <div class="mt-2 mb-4">
        <div class="relative w-full rounded-2xl overflow-hidden border border-slate-800/80 bg-slate-900/60 p-3.5 flex gap-3.5 items-center">
            
            <!-- Sponsored tag -->
            <span class="absolute top-2.5 right-3 text-[8px] font-bold text-slate-500 border border-slate-800 bg-slate-950 px-1 rounded uppercase tracking-widest leading-none">Sponsored</span>
            
            <img src="<?= secure_escape($ads['image_url']) ?>" referrerPolicy="no-referrer" alt="Ad" class="w-14 h-14 object-cover rounded-xl border border-slate-800">
            
            <div class="flex-1 pr-6">
                <h4 class="text-xs font-extrabold text-slate-200 truncate"><?= secure_escape($ads['title']) ?></h4>
                <p class="text-[10px] text-slate-400 mt-1 leading-relaxed">Upgrade to <strong class="text-indigo-400">TradeNexa Pro</strong> or <strong class="text-purple-400">VIP</strong> to remove banner feeds completely.</p>
                <div class="mt-2 flex gap-2">
                    <a href="<?= secure_escape($ads['link_url']) ?>" target="_blank" class="text-[9px] font-bold bg-indigo-500 hover:bg-indigo-400 text-white px-2 py-0.5 rounded transition">
                        View Offer
                    </a>
                    <a href="index.php?page=billing/plans" class="text-[9px] font-bold border border-slate-800 text-slate-400 hover:text-slate-200 px-2 py-0.5 rounded transition">
                        Remove Ads
                    </a>
                </div>
            </div>

        </div>
    </div>
<?php endif; ?>

<!-- Market Sentiment and Fast-Stats Panel -->
<div class="bg-slate-900 border border-slate-800 rounded-3xl p-4 mt-2">
    <div class="flex items-center justify-between mb-3 border-b border-slate-800 pb-2">
        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400">Market Intelligence Brief</h4>
        <span class="text-[9px] font-mono font-bold bg-emerald-950 text-emerald-400 py-0.5 px-1.5 rounded">Bullish Zone</span>
    </div>
    
    <div class="grid grid-cols-2 gap-4">
        <div class="bg-slate-950/50 p-2.5 rounded-xl border border-slate-800/60">
            <span class="text-[9px] text-slate-500 uppercase font-semibold">24h Global Volume</span>
            <p class="font-mono text-sm font-bold text-slate-200 mt-0.5">$316.48B</p>
        </div>
        <div class="bg-slate-950/50 p-2.5 rounded-xl border border-slate-800/60">
            <span class="text-[9px] text-slate-500 uppercase font-semibold">Crypto Fear/Greed Index</span>
            <p class="font-mono text-sm font-bold text-amber-400 mt-0.5">72 (Greed)</p>
        </div>
    </div>

    <div class="mt-3.5 bg-indigo-950/10 border border-indigo-950 rounded-xl p-2.5 flex items-start gap-2.5">
        <div class="text-xs mt-0.5">💡</div>
        <p class="text-[10px] text-slate-400 leading-relaxed">
            Select any trading asset above to enter the **Charts Terminal**. Inside, you can toggle indicators like RSI and EMA crossovers in real-time.
        </p>
    </div>
</div>
