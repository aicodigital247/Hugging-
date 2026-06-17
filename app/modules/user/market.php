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
require_once dirname(dirname(dirname(__DIR__))) . '/app/core/security.php';
require_once dirname(dirname(dirname(__DIR__))) . '/app/core/i18n.php';

$tickers = bybit_get_market_tickers();
$show_ads = saas_should_show_ads();
$bulletins = notification_get_all(2);

$curr_user = auth_current_user();
$alert_error = '';
$alert_success = '';

// Handle setting custom price alerts
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_price_alert'])) {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!validate_csrf_token($csrf)) {
        $alert_error = "Security validation error.";
    } else {
        $alert_symbol = secure_escape($_POST['alert_symbol'] ?? '');
        $alert_target = floatval($_POST['alert_target'] ?? 0);
        $alert_direction = secure_escape($_POST['alert_direction'] ?? 'above');
        
        if ($alert_target <= 0) {
            $alert_error = "Please input a valid price limit.";
        } elseif ($curr_user) {
            $uid = intval($curr_user['id']);
            db_query("INSERT INTO price_alerts (user_id, symbol, target_price, direction, active) VALUES (?, ?, ?, ?, 1)", 
                [$uid, $alert_symbol, $alert_target, $alert_direction], "isds");
            $alert_success = "Alert active! You will be notified when " . $alert_symbol . " crosses $" . number_format($alert_target, 2);
        } else {
            $alert_error = "Please sign in to configure price alerts.";
        }
    }
}

// Retrieve active ads from database
$ads = [];
if ($conn) {
    $res = mysqli_query($conn, "SELECT * FROM `ads` WHERE `active` = 1 AND `placement` = 'in_feed' LIMIT 1");
    if ($res) {
        $ads = mysqli_fetch_assoc($res);
    }
}

// Fetch user's registered price alerts
$active_alerts = [];
if ($curr_user && $conn) {
    $uid = intval($curr_user['id']);
    $res = mysqli_query($conn, "SELECT * FROM `price_alerts` WHERE `user_id` = $uid AND `active` = 1 ORDER BY `created_at` DESC");
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $active_alerts[] = $row;
        }
    }
}
?>

<!-- Announcement Bulletin Section -->
<?php if (!empty($bulletins)): ?>
    <div class="mb-5">
        <div class="flex items-center justify-between mb-2">
            <span class="text-[10px] font-bold text-gray-500 uppercase tracking-widest font-sans">Active Announcements</span>
            <span class="w-1.5 h-1.5 rounded-full bg-[#F0B90B] animate-pulse"></span>
        </div>
        <div class="space-y-2">
            <?php foreach ($bulletins as $bulletin): ?>
                <div class="bg-[#161A1E] border border-[#20262D] rounded-2xl p-3 flex gap-2.5 items-start">
                    <div class="w-6 h-6 flex-shrink-0 bg-[#F0B90B]/10 text-[#F0B90B] border border-[#F0B90B]/20 rounded-lg flex items-center justify-center text-xs">
                        📣
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-[#EAECEF]"><?= secure_escape($bulletin['title']) ?></h4>
                        <p class="text-[10px] text-gray-400 mt-0.5 leading-relaxed"><?= secure_escape($bulletin['content']) ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

<!-- Core Market Tickers Heading -->
<div class="mb-4">
    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-widest mb-3 font-sans"><?= __('bybitMkt', 'Bybit Linear Market Feed') ?></h3>
    
    <!-- Tickers Scroll Feed List -->
    <div class="space-y-3">
        <?php foreach ($tickers as $symbol => $data): ?>
            <a href="index.php?page=market/charts&symbol=<?= $symbol ?>" class="block bg-[#161A1E] border border-[#20262D] rounded-2xl p-3.5 hover:border-[#2D343B] active:scale-[0.98] transition cursor-pointer relative overflow-hidden">
                <div class="flex items-center justify-between">
                    
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-[#0B0E11] flex items-center justify-center border border-[#20262D] font-mono font-bold text-[#F0B90B] text-xs">
                            <?= substr($symbol, 0, 3) ?>
                        </div>
                        <div>
                            <div class="flex items-center gap-1.5">
                                <h4 class="text-sm font-bold text-[#EAECEF] font-sans"><?= secure_escape($data['name']) ?></h4>
                                <span class="text-[9px] text-gray-400 font-mono font-bold"><?= $symbol ?></span>
                            </div>
                            <span class="text-[10px] text-gray-500">Live Bybit spot contract</span>
                        </div>
                    </div>

                    <div class="text-right">
                        <div class="font-mono text-xs font-black text-[#EAECEF]" data-price-symbol="<?= $symbol ?>">
                            $<?= number_format($data['price'], ($data['price'] < 5 ? 4 : 2)) ?>
                        </div>
                        <div class="font-mono text-[9px] font-bold mt-0.5 <?= ($data['change'] >= 0 ? 'text-[#03C087]' : 'text-[#F04F56]') ?>">
                            <?= ($data['change'] >= 0 ? '▲ +' : '▼ ') ?><?= number_format($data['change'], 2) ?>%
                        </div>
                    </div>

                </div>
            </a>
        <?php endforeach; ?>
    </div>
</div>

<!-- Dynamic Price Alerts UI Setup -->
<div class="mb-5 bg-[#161A1E] border border-[#20262D] rounded-3xl p-4">
    <div class="flex items-center justify-between mb-3">
        <h4 class="text-xs font-bold uppercase tracking-wider text-[#EAECEF] font-sans">Custom Price Alerts</h4>
        <span class="text-[8px] bg-[#F0B90B] text-black font-extrabold px-1.5 py-0.5 rounded uppercase font-mono">Real-time Push</span>
    </div>

    <?php if (!empty($alert_error)): ?>
        <div class="bg-rose-950/40 border border-rose-900 rounded-xl p-2.5 mb-3 text-[10px] text-[#F04F56]">
            <?= secure_escape($alert_error) ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($alert_success)): ?>
        <div class="bg-emerald-950/40 border border-emerald-900 rounded-xl p-2.5 mb-3 text-[10px] text-[#03C087]">
            <?= secure_escape($alert_success) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="" class="space-y-3 pb-3 border-b border-[#20262D]/60">
        <?php csrf_input(); ?>
        <input type="hidden" name="create_price_alert" value="1">
        
        <div class="grid grid-cols-3 gap-2">
            <div>
                <label class="text-[8px] text-gray-500 uppercase font-black tracking-wide block mb-1">Symbol</label>
                <select name="alert_symbol" class="w-full bg-[#0B0E11] border border-[#20262D] focus:border-[#F0B90B] rounded-xl px-2 py-2 text-[11px] text-[#EAECEF] font-mono focus:outline-none">
                    <?php foreach ($tickers as $symbol => $data): ?>
                        <option value="<?= $symbol ?>"><?= $symbol ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="text-[8px] text-gray-500 uppercase font-black tracking-wide block mb-1">Trigger Price</label>
                <input type="number" step="0.0001" name="alert_target" placeholder="72000" required class="w-full bg-[#0B0E11] border border-[#20262D] focus:border-[#F0B90B] rounded-xl px-2.5 py-1.5 text-xs text-[#EAECEF] font-mono focus:outline-none">
            </div>
            <div>
                <label class="text-[8px] text-gray-500 uppercase font-black tracking-wide block mb-1">Direction</label>
                <select name="alert_direction" class="w-full bg-[#0B0E11] border border-[#20262D] focus:border-[#F0B90B] rounded-xl px-2 py-2 text-[11px] text-[#EAECEF] font-mono focus:outline-none">
                    <option value="above">Above (>=)</option>
                    <option value="below">Below (<=)</option>
                </select>
            </div>
        </div>

        <button type="submit" class="w-full bg-[#F0B90B] hover:bg-[#F0B90B]/90 text-black font-black py-2 rounded-xl text-xs transition uppercase tracking-wide cursor-pointer">
            Set Custom Alert
        </button>
    </form>

    <!-- Active List -->
    <div class="mt-3">
        <span class="text-[9px] text-gray-500 font-bold uppercase tracking-widest block mb-2">My Active Price Targets</span>
        
        <?php if (empty($active_alerts)): ?>
            <p class="text-[10px] text-gray-500 font-mono">[No thresholds actively registered for this account]</p>
        <?php else: ?>
            <div class="space-y-1.5">
                <?php foreach ($active_alerts as $al): ?>
                    <div class="bg-[#0B0E11] border border-[#20262D] rounded-xl p-2 flex justify-between items-center text-xs font-mono" data-alert-id="<?= $al['id'] ?>" data-alert-symbol="<?= $al['symbol'] ?>" data-alert-target="<?= floatval($al['target_price']) ?>" data-alert-direction="<?= $al['direction'] ?>">
                        <div class="flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-[#F0B90B]"></span>
                            <span class="font-bold text-[#EAECEF]"><?= $al['symbol'] ?></span>
                        </div>
                        <div class="text-[10px] text-gray-400">
                            if crosses <strong class="text-brand-500"><?= $al['direction'] === 'above' ? '≥' : '≤' ?> $<?= number_format(floatval($al['target_price']), ($al['target_price'] < 5 ? 4 : 2)) ?></strong>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- SaaS Monetization System (Free Tier Native Banner Ad Showcase) -->
<?php if ($show_ads && !empty($ads)): ?>
    <div class="mt-2 mb-4">
        <div class="relative w-full rounded-2xl overflow-hidden border border-[#20262D] bg-[#161A1E] p-3.5 flex gap-3.5 items-center">
            
            <!-- Sponsored tag -->
            <span class="absolute top-2.5 right-3 text-[8px] font-bold text-gray-500 border border-[#20262D] bg-[#0B0E11] px-1.5 py-0.5 rounded uppercase tracking-wider font-mono">Sponsored</span>
            
            <img src="<?= secure_escape($ads['image_url']) ?>" referrerPolicy="no-referrer" alt="Ad" class="w-14 h-14 object-cover rounded-xl border border-[#20262D]">
            
            <div class="flex-1 pr-6">
                <h4 class="text-xs font-extrabold text-[#EAECEF] truncate"><?= secure_escape($ads['title']) ?></h4>
                <p class="text-[10px] text-gray-400 mt-1 leading-relaxed">Upgrade to <strong class="text-[#F0B90B]">PRO Limit</strong> or <strong class="text-amber-500">VIP</strong> to remove commercial banners.</p>
                <div class="mt-2 flex gap-2">
                    <a href="<?= secure_escape($ads['link_url']) ?>" target="_blank" class="text-[9px] font-black bg-[#F0B90B] hover:bg-[#F0B90B]/90 text-black px-2.5 py-1 rounded transition">
                        View Offer
                    </a>
                    <a href="index.php?page=billing/plans" class="text-[9px] font-bold border border-[#20262D] text-gray-400 hover:text-gray-200 px-2 py-1 rounded transition">
                        Remove Ads
                    </a>
                </div>
            </div>

        </div>
    </div>
<?php endif; ?>

<!-- Market Sentiment and Fast-Stats Panel -->
<div class="bg-[#161A1E] border border-[#20262D] rounded-3xl p-4 mt-2">
    <div class="flex items-center justify-between mb-3 border-b border-[#20262D] pb-3">
        <h4 class="text-xs font-bold uppercase tracking-wider text-gray-400 font-sans">Market Intelligence Brief</h4>
        <span class="text-[9px] font-mono font-bold bg-[#03C087]/15 text-[#03C087] py-0.5 px-2 rounded-lg">BULLISH ZONE</span>
    </div>
    
    <div class="grid grid-cols-2 gap-4">
        <div class="bg-[#0B0E11]/50 p-2.5 rounded-xl border border-[#20262D]/60">
            <span class="text-[9px] text-gray-500 uppercase font-semibold block">24h Global Volume</span>
            <p class="font-mono text-sm font-bold text-[#EAECEF] mt-0.5">$316.48B</p>
        </div>
        <div class="bg-[#0B0E11]/50 p-2.5 rounded-xl border border-[#20262D]/60">
            <span class="text-[9px] text-gray-500 uppercase font-semibold block">Bybit Fear & Greed</span>
            <p class="font-mono text-sm font-bold text-[#F0B90B] mt-0.5">72 (Greed)</p>
        </div>
    </div>

    <div class="mt-3.5 bg-[#1B2026] border border-[#20262D] rounded-xl p-2.5 flex items-start gap-2.5">
        <div class="text-xs mt-0.5 text-[#F0B90B]">💡</div>
        <p class="text-[10px] text-gray-400 leading-relaxed">
            Select any trading asset above to enter the **Charts Terminal**. Inside, you can toggle indicators like RSI and EMA crossovers in real-time.
        </p>
    </div>
</div>

<!-- Desktop Push-like Alert Dispatcher Engine -->
<div id="desktop-alert-container" class="fixed top-4 right-4 max-w-sm w-full z-50 pointer-events-none space-y-3"></div>

<script>
document.addEventListener("DOMContentLoaded", () => {
    // Request notification permission
    if ("Notification" in window) {
        if (Notification.permission !== "granted" && Notification.permission !== "denied") {
            Notification.requestPermission();
        }
    }

    const formatPrice = (price) => parseFloat(price).toFixed(price < 5 ? 4 : 2);

    const dispatchAlert = (title, text) => {
        // 1. Play auditory buzz
        try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.frequency.value = 880; // beautiful high-pitch synth beep
            gain.gain.setValueAtTime(0.12, ctx.currentTime);
            osc.start();
            osc.stop(ctx.currentTime + 0.15);
        } catch(e) {}

        // 2. Dispatch native push
        if ("Notification" in window && Notification.permission === "granted") {
            new Notification(title, { body: text });
        }

        // 3. Dispatch beautiful in-viewport popup
        const container = document.getElementById("desktop-alert-container");
        if (container) {
            const el = document.createElement("div");
            el.className = "bg-[#161A1E] border-2 border-[#F0B90B] rounded-2xl p-4 shadow-2xl flex items-start gap-3 pointer-events-auto transform translate-y-[-10px] opacity-0 transition-all duration-300";
            el.innerHTML = `
                <div class="w-7 h-7 bg-[#F0B90B]/10 rounded-full flex items-center justify-center text-xs text-[#F0B90B] font-bold">🔔</div>
                <div class="flex-1">
                    <h5 class="text-xs font-black text-[#F0B90B] uppercase tracking-wide">${title}</h5>
                    <p class="text-[10px] text-[#EAECEF] mt-1 font-mono">${text}</p>
                </div>
            `;
            container.appendChild(el);
            setTimeout(() => {
                el.classList.remove("translate-y-[-10px]", "opacity-0");
            }, 50);

            setTimeout(() => {
                el.classList.add("translate-y-[-10px]", "opacity-0");
                setTimeout(() => el.remove(), 300);
            }, 6000);
        }
    };

    // Alert Checker Interval
    setInterval(() => {
        const rows = document.querySelectorAll("[data-alert-id]");
        rows.forEach(row => {
            const id = row.getAttribute("data-alert-id");
            const symbol = row.getAttribute("data-alert-symbol");
            const target = parseFloat(row.getAttribute("data-alert-target"));
            const direction = row.getAttribute("data-alert-direction");

            // Fetch live current price from ticker row
            const tickerEl = document.querySelector(`[data-price-symbol="${symbol}"]`);
            if (tickerEl) {
                const rawPriceText = tickerEl.textContent.replace('$', '').replace(/,/g, '');
                const currentPrice = parseFloat(rawPriceText);

                if (!isNaN(currentPrice)) {
                    let triggered = false;
                    if (direction === 'above' && currentPrice >= target) {
                        triggered = true;
                    } else if (direction === 'below' && currentPrice <= target) {
                        triggered = true;
                    }

                    if (triggered) {
                        dispatchAlert(
                            `🚨 Price Target Triggered!`,
                            `${symbol} current price is $${formatPrice(currentPrice)} (Crossed your ${direction} Target price threshold of $${formatPrice(target)})`
                        );
                        row.remove(); // Remove locally so it only alerts once
                    }
                }
            }
        });
    }, 3000);
});
</script>
