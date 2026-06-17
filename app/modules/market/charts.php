<?php
/**
 * TradeNexa.com - Market - Advanced Candlestick Charts Terminal
 * Renders OHLC candles, EMA 9/21 curves, RSI indicators, and volume bars using pure Vanilla JS
 */

require_once dirname(dirname(dirname(__DIR__))) . '/app/services/chart_engine.php';
require_once dirname(dirname(dirname(__DIR__))) . '/app/core/saas.php';

$symbol = isset($_GET['symbol']) ? strtoupper(trim($_GET['symbol'])) : 'BTCUSDT';
$timeframe = isset($_GET['timeframe']) ? strtolower(trim($_GET['timeframe'])) : '1h';

// Establish valid timeframes
$valid_tfs = ['1m', '5m', '15m', '1h', '4h', '1d'];
if (!in_array($timeframe, $valid_tfs)) {
    $timeframe = '1h';
}

$tier = saas_get_current_tier();
$has_indicators = saas_has_indicator_access();

// Compile data on the server side
$candles = chart_compile_series($symbol, $timeframe, 45);
$json_data = json_encode($candles);
?>

<!-- Asset & Timeframe Header -->
<div class="mb-4 bg-[#161A1E] border border-[#20262D] rounded-3xl p-4">
    <div class="flex items-center justify-between mb-3.5">
        
        <!-- Asset Selector -->
        <div>
            <form id="symbolForm" method="GET" action="index.php">
                <input type="hidden" name="page" value="market/charts">
                <input type="hidden" name="timeframe" value="<?= $timeframe ?>">
                
                <select name="symbol" onchange="document.getElementById('symbolForm').submit()" class="bg-[#0B0E11] border border-[#20262D] text-[#EAECEF] rounded-xl px-3 py-1.5 text-sm font-bold font-mono focus:outline-none focus:border-[#F0B90B] cursor-pointer">
                    <option value="BTCUSDT" <?= ($symbol === 'BTCUSDT' ? 'selected' : '') ?>>BTC/USDT Persistent</option>
                    <option value="ETHUSDT" <?= ($symbol === 'ETHUSDT' ? 'selected' : '') ?>>ETH/USDT Persistent</option>
                    <option value="SOLUSDT" <?= ($symbol === 'SOLUSDT' ? 'selected' : '') ?>>SOL/USDT Persistent</option>
                    <option value="ADAUSDT" <?= ($symbol === 'ADAUSDT' ? 'selected' : '') ?>>ADA/USDT Persistent</option>
                </select>
            </form>
        </div>

        <!-- Premium Status Pill -->
        <div>
            <?php if (!$has_indicators): ?>
                <span class="text-[8px] font-bold text-[#F0B90B] bg-[#F0B90B]/15 border border-[#F0B90B]/20 px-2 py-1 rounded-lg animate-pulse font-mono tracking-wider">LITE FEED</span>
            <?php else: ?>
                <span class="text-[8px] font-black text-black bg-[#F0B90B] px-2 py-1 rounded-lg font-mono tracking-wider">BYBIT WEBSOCKET PRO</span>
            <?php endif; ?>
        </div>
    </div>

    <!-- Timeframe picker line -->
    <div class="flex gap-1 overflow-x-auto no-scrollbar pb-1 border-t border-[#20262D]/60 pt-3">
        <?php foreach ($valid_tfs as $tf): ?>
            <a href="index.php?page=market/charts&symbol=<?= $symbol ?>&timeframe=<?= $tf ?>" class="px-3 py-1.5 text-xs font-mono font-bold rounded-lg transition text-center select-none cursor-pointer
                <?= ($timeframe === $tf ? 'bg-[#F0B90B] text-black font-black' : 'bg-[#0B0E11] border border-[#20262D] text-gray-400 hover:text-gray-200') ?>">
                <?= $tf ?>
            </a>
        <?php endforeach; ?>
    </div>
</div>

<!-- Interactive Chart Drawing Stage container -->
<div class="bg-[#161A1E] border border-[#20262D] rounded-3xl p-4 mb-4 select-none relative overflow-hidden">
    
    <!-- Legend Info overlay -->
    <div class="absolute top-4 left-4 z-10 space-y-1">
        <span class="text-[9px] font-mono font-black text-slate-350"><?= $symbol ?> (<?= strtoupper($timeframe) ?>)</span>
        <div class="flex gap-2.5">
            <?php if ($has_indicators): ?>
                <span class="text-[8px] font-mono text-[#F0B90B]">EMA(9): <span id="ema9Legend">...</span></span>
                <span class="text-[8px] font-mono text-purple-400">EMA(21): <span id="ema21Legend">...</span></span>
                <span class="text-[8px] font-mono text-cyan-400">RSI(14): <span id="rsiLegend">...</span></span>
            <?php else: ?>
                <span class="text-[8px] font-mono text-gray-500">indicators disabled — <a href="index.php?page=billing/plans" class="text-[#F0B90B] font-bold hover:underline">Upgrade</a></span>
            <?php endif; ?>
        </div>
    </div>

    <!-- MAIN CANDLESTICK CANVAS -->
    <div class="w-full h-52 bg-[#0B0E11] rounded-2xl relative border border-[#20262D] mt-5">
        <svg id="candleSvg" class="w-full h-full" xmlns="http://www.w3.org/2000/svg">
            <!-- Drawn Dynamically -->
        </svg>
    </div>

    <!-- VOLUME BARS CANVAS -->
    <div class="w-full h-12 bg-[#0B0E11] rounded-xl relative border border-[#20262D] mt-2 flex items-end">
        <svg id="volumeSvg" class="w-full h-full" xmlns="http://www.w3.org/2000/svg">
            <!-- Drawn Dynamically -->
        </svg>
    </div>

    <!-- RSI SUB-CHART (Gated for Pro/VIP) -->
    <div class="w-full h-14 bg-[#0B0E11] rounded-xl relative border border-[#20262D] mt-2 flex items-center relative overflow-hidden">
        <?php if (!$has_indicators): ?>
            <!-- Lock Screen covering secondary chart -->
            <div class="absolute inset-0 bg-[#0B0E11]/85 backdrop-blur-[1px] flex flex-col justify-center items-center text-center p-3 z-10 select-none">
                <span class="text-xs">🔒</span>
                <h4 class="text-[9px] font-bold text-gray-400 mt-1">Upgrade to Premium for RSI oscillation waves</h4>
            </div>
        <?php endif; ?>
        <svg id="rsiSvg" class="w-full h-full" xmlns="http://www.w3.org/2000/svg">
            <!-- Drawn Dynamically -->
        </svg>
    </div>

</div>

<!-- Market indicators list view card -->
<div class="bg-[#161A1E] border border-[#20262D] rounded-3xl p-4">
    <div class="flex items-center justify-between mb-3">
        <h4 class="text-xs font-bold uppercase text-gray-400 tracking-wider">Dynamic Oscillator Status</h4>
        <span class="text-[8px] uppercase tracking-wider font-bold bg-[#0B0E11] border border-[#20262D] px-1.5 py-0.5 rounded text-[#F0B90B] font-mono">Bybit Linear</span>
    </div>

    <div class="space-y-2 text-xs">
        <div class="flex justify-between items-center p-2 rounded-xl bg-[#0B0E11]/50">
            <span class="text-[10px] text-gray-400 font-medium">EMA 9 / 21 Trend Zone</span>
            <span id="emaStatusCell" class="font-bold text-[10px]">Processing...</span>
        </div>
        <div class="flex justify-between items-center p-2 rounded-xl bg-[#0B0E11]/50">
            <span class="text-[10px] text-gray-400 font-medium">Relative Strength Index (RSI)</span>
            <span id="rsiStatusCell" class="font-bold text-[10px]">Processing...</span>
        </div>
        <div class="flex justify-between items-center p-2 rounded-xl bg-[#0B0E11]/50">
            <span class="text-[10px] text-gray-400 font-medium">Active volume signals</span>
            <span id="volStatusCell" class="font-bold text-[10px]">Processing...</span>
        </div>
    </div>
</div>

<!-- Inline JavaScript Draw engine (Pure Vanilla JS complying with strict rules) -->
<script>
document.addEventListener("DOMContentLoaded", function() {
    // Inject server compiled variable arrays
    const chartData = <?= $json_data ?>;
    const hasIndicators = <?= $has_indicators ? 'true' : 'false' ?>;

    if (!chartData || chartData.length === 0) return;

    const candleSvg = document.getElementById("candleSvg");
    const volumeSvg = document.getElementById("volumeSvg");
    const rsiSvg = document.getElementById("rsiSvg");

    // Dynamic Sizing calculations using container sizes
    const candleWidth = candleSvg.clientWidth;
    const candleHeight = candleSvg.clientHeight;
    
    const volWidth = volumeSvg.clientWidth;
    const volHeight = volumeSvg.clientHeight;

    const rsiWidth = rsiSvg.clientWidth;
    const rsiHeight = rsiSvg.clientHeight;

    // Identify minimum and maximum margins
    const prices = chartData.map(d => d.close).concat(chartData.map(d => d.high), chartData.map(d => d.low));
    const minPrice = Math.min(...prices) * 0.999;
    const maxPrice = Math.max(...prices) * 1.001;
    const priceDiff = maxPrice - minPrice;

    const volumes = chartData.map(d => d.volume);
    const maxVol = Math.max(...volumes) || 1;

    // Coordinate conversion helper
    function getX(i) {
        return (i / (chartData.length - 1)) * (candleWidth - 24) + 12;
    }
    
    function getY(p) {
        return candleHeight - ((p - minPrice) / priceDiff) * (candleHeight - 34) - 17;
    }

    // 1. DRAW PRICE CANDLESTICKS ON SVG
    let candleHtml = "";
    
    // Draw background guide grid lines
    for (let j = 0.2; j < 1.0; j += 0.2) {
        let gridY = candleHeight * j;
        candleHtml += `<line x1="0" y1="${gridY}" x2="${candleWidth}" y2="${gridY}" stroke="#1B2026" stroke-dasharray="3,3"/>`;
    }

    let ema9Pts = [];
    let ema21Pts = [];

    chartData.forEach((d, i) => {
        const cx = getX(i);
        const cyOpen = getY(d.open);
        const cyClose = getY(d.close);
        const cyHigh = getY(d.high);
        const cyLow = getY(d.low);

        const isGreen = d.close >= d.open;
        const color = isGreen ? "#03C087" : "#F04F56"; // Up/Down candle color scheme of Bybit

        // Gather coordinates for EMAs
        if (d.ema9 !== null) ema9Pts.push(`${cx},${getY(d.ema9)}`);
        if (d.ema21 !== null) ema21Pts.push(`${cx},${getY(d.ema21)}`);

        // Draw wick
        candleHtml += `<line x1="${cx}" y1="${cyHigh}" x2="${cx}" y2="${cyLow}" stroke="${color}" stroke-width="1.2"/>`;
        // Draw body
        const rectY = Math.min(cyOpen, cyClose);
        const rectH = Math.max(2, Math.abs(cyOpen - cyClose));
        const rectW = Math.max(3, (candleWidth / chartData.length) * 0.62);
        
        candleHtml += `<rect x="${cx - rectW/2}" y="${rectY}" width="${rectW}" height="${rectH}" fill="${color}" rx="0.5"/>`;

        // Draw crossover signal markers (triangles)
        if (hasIndicators && d.trend && i > 1) {
            const prev = chartData[i-1];
            if (prev.trend !== d.trend) {
                if (d.trend === 'BULL') {
                    // Buy signal triangle under low wick
                    candleHtml += `<polygon points="${cx},${cyLow + 4} ${cx - 4},${cyLow + 11} ${cx + 4},${cyLow + 11}" fill="#03C087"/>`;
                } else if (d.trend === 'BEAR') {
                    // Sell signal triangle above high wick
                    candleHtml += `<polygon points="${cx},${cyHigh - 4} ${cx - 4},${cyHigh - 11} ${cx + 4},${cyHigh - 11}" fill="#F04F56"/>`;
                }
            }
        }
    });

    // Draw EMA trend lines if allowed
    if (hasIndicators) {
        if (ema9Pts.length > 1) {
            candleHtml += `<polyline points="${ema9Pts.join(" ")}" fill="none" stroke="#F0B90B" stroke-width="1.8" stroke-linecap="round"/>`;
        }
        if (ema21Pts.length > 1) {
            candleHtml += `<polyline points="${ema21Pts.join(" ")}" fill="none" stroke="#9064FA" stroke-width="1.8" stroke-linecap="round"/>`;
        }
    }

    candleSvg.innerHTML = candleHtml;

    // 2. DRAW VOLUME BARS ON SVG
    let volHtml = "";
    chartData.forEach((d, i) => {
        const cx = getX(i);
        const isGreen = d.close >= d.open;
        const color = isGreen ? "#03C08735" : "#F04F5635"; // washed opacity representation
        const h = (d.volume / maxVol) * (volHeight - 10) + 2;
        const rectW = Math.max(3, (volWidth / chartData.length) * 0.62);

        volHtml += `<rect x="${cx - rectW/2}" y="${volHeight - h}" width="${rectW}" height="${h}" fill="${color}" rx="0.5"/>`;
    });
    volumeSvg.innerHTML = volHtml;

    // 3. DRAW RSI OSCILLATION WAVES ON SVG
    if (hasIndicators) {
        let rsiHtml = "";
        let rsiPts = [];
        
        // Draw 30 / 70 guide lines
        const y70 = rsiHeight - (70 / 100) * (rsiHeight - 12) - 6;
        const y30 = rsiHeight - (30 / 100) * (rsiHeight - 12) - 6;
        
        rsiHtml += `<line x1="0" y1="${y70}" x2="${rsiWidth}" y2="${y70}" stroke="#F04F5630" stroke-dasharray="2,2"/>`;
        rsiHtml += `<line x1="0" y1="${y30}" x2="${rsiWidth}" y2="${y30}" stroke="#03C08730" stroke-dasharray="2,2"/>`;

        chartData.forEach((d, i) => {
            const cx = getX(i);
            const rVal = d.rsi || 50;
            const cyRsi = rsiHeight - (rVal / 100) * (rsiHeight - 12) - 6;
            rsiPts.push(`${cx},${cyRsi}`);
        });

        if (rsiPts.length > 1) {
            rsiHtml += `<polyline points="${rsiPts.join(" ")}" fill="none" stroke="#38BDF8" stroke-width="1.8"/>`;
        }
        rsiSvg.innerHTML = rsiHtml;
    }

    // 4. GENERATE LABELS AND OVERLAYS LEDGERS
    const lastElement = chartData[chartData.length - 1];
    
    // Update active pricing indicators
    if (hasIndicators) {
        document.getElementById("ema9Legend").innerText = `$${lastElement.ema9 ? lastElement.ema9.toFixed(2) : '...'}`;
        document.getElementById("ema21Legend").innerText = `$${lastElement.ema21 ? lastElement.ema21.toFixed(2) : '...'}`;
        document.getElementById("rsiLegend").innerText = `${lastElement.rsi ? lastElement.rsi.toFixed(2) : '...'}`;
    }

    // Dynamically evaluate indicators panel on screen
    const emaStatusCell = document.getElementById("emaStatusCell");
    const rsiStatusCell = document.getElementById("rsiStatusCell");
    const volStatusCell = document.getElementById("volStatusCell");

    // Decouple EMA Statuses
    if (lastElement.trend === 'BULL') {
        emaStatusCell.innerText = "📈 BULLISH CROSSOVER";
        emaStatusCell.className = "font-black text-emerald-400 text-[10px]";
    } else if (lastElement.trend === 'BEAR') {
        emaStatusCell.innerText = "📉 BEARISH SELECTION";
        emaStatusCell.className = "font-black text-rose-400 text-[10px]";
    } else {
        emaStatusCell.innerText = "⚖ NEUTRAL ACCUMULATION";
        emaStatusCell.className = "font-bold text-slate-500 text-[10px]";
    }

    // RSI status
    if (lastElement.rsi > 70) {
        rsiStatusCell.innerText = `🔥 OVERBOUGHT (${lastElement.rsi.toFixed(1)}) — SELL FLAGGED`;
        rsiStatusCell.className = "font-black text-rose-400 text-[10px]";
    } else if (lastElement.rsi < 30) {
        rsiStatusCell.innerText = `❄ OVERSOLD (${lastElement.rsi.toFixed(1)}) — BUY ACCUMULATING`;
        rsiStatusCell.className = "font-black text-emerald-400 text-[10px]";
    } else {
        rsiStatusCell.innerText = `⚖ BALANCED INDEX (${lastElement.rsi ? lastElement.rsi.toFixed(1) : '50.0'})`;
        rsiStatusCell.className = "font-bold text-slate-400 text-[10px]";
    }

    // Vol statuses
    const avgVolVal = volumes.reduce((a,b)=>a+b,0) / volumes.length;
    if (lastElement.volume > avgVolVal * 1.5) {
        volStatusCell.innerText = "⚡ MASSIVE SPIKE CONFIRMED";
        volStatusCell.className = "font-black text-emerald-400 text-[10px]";
    } else {
        volStatusCell.innerText = "⚖ MODERATE ACTIVITY FLOW";
        volStatusCell.className = "font-bold text-slate-400 text-[10px]";
    }

});
</script>
