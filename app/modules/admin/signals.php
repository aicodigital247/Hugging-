<?php
/**
 * TradeNexa.com - Admin - Signals History & Seeder
 * Allows auditing active signal tables and injecting custom signal triggers
 */

require_once dirname(dirname(dirname(__DIR__))) . '/app/core/db.php';
require_once dirname(dirname(dirname(__DIR__))) . '/app/core/security.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $conn) {
     $csrf = $_POST['csrf_token'] ?? '';
     if (!validate_csrf_token($csrf)) {
         $error = "CSRF checks failed.";
     } else {
         $symbol = strtoupper(trim($_POST['symbol'] ?? 'BTCUSDT'));
         $action = trim($_POST['signal_type'] ?? 'BUY');
         $confidence = intval($_POST['confidence'] ?? 90);
         $entry = floatval($_POST['entry_price'] ?? 60000);
         $tp = floatval($_POST['target_price'] ?? 62000);
         $sl = floatval($_POST['stop_loss'] ?? 59000);
         
         $stmt = mysqli_prepare($conn, "INSERT INTO `signals` (`symbol`, `signal_type`, `confidence`, `entry_price`, `target_price`, `stop_loss`, `rsi_value`, `ema_fast`, `ema_slow`, `status`) VALUES (?, ?, ?, ?, ?, ?, 50.0, ?, ?, 'active')");
         if ($stmt) {
             mysqli_stmt_bind_param($stmt, "ssiddddd", $symbol, $action, $confidence, $entry, $tp, $sl, $entry, $entry);
             mysqli_stmt_execute($stmt);
             mysqli_stmt_close($stmt);
             $success = "Successfully force-seeded signal table with dummy parameter payload.";
         } else {
             $error = "Database constraint write error.";
         }
     }
}

$signals = [];
if ($conn) {
    $res = mysqli_query($conn, "SELECT * FROM `signals` ORDER BY `id` DESC LIMIT 40");
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $signals[] = $row;
        }
    }
}
?>

<div class="mb-5 flex justify-between items-center bg-slate-900 border border-slate-800 p-4 rounded-3xl">
    <div>
        <h2 class="text-sm font-black text-slate-100 uppercase tracking-widest">Global Signals</h2>
        <span class="text-[10px] text-slate-500 font-mono">Telemetry database logs</span>
    </div>
    <a href="index.php?page=admin/dashboard" class="text-xs text-indigo-400 font-bold hover:underline">Back to Central</a>
</div>

<?php if(!empty($error)): ?>
    <div class="bg-rose-950/40 border border-rose-900 rounded-xl p-3 mb-4 text-xs text-rose-300">
        <?= secure_escape($error) ?>
    </div>
<?php endif; ?>

<?php if(!empty($success)): ?>
    <div class="bg-emerald-950/40 border border-emerald-950 rounded-xl p-3 mb-4 text-xs text-emerald-300">
        <?= secure_escape($success) ?>
    </div>
<?php endif; ?>

<!-- Signals seeder form widget -->
<div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 mb-5 text-xs text-slate-350">
    <h3 class="text-xs font-black uppercase text-slate-300 mb-3 pb-1.5 border-b border-slate-950">Seeding Force Signal</h3>
    
    <form method="POST" action="" class="space-y-3.5">
        <?php csrf_input(); ?>
        
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="block text-slate-400 text-[9px] font-bold mb-1 uppercase tracking-wider">Trading Symbol</label>
                <input type="text" name="symbol" value="BTCUSDT" required class="w-full bg-slate-950 border border-slate-800 focus:border-indigo-500 rounded-xl px-2.5 py-1.5 text-slate-100 focus:outline-none transition font-mono">
            </div>
            <div>
                <label class="block text-slate-400 text-[9px] font-bold mb-1 uppercase tracking-wider">Directive</label>
                <select name="signal_type" class="w-full bg-slate-950 border border-slate-800 focus:border-indigo-500 rounded-xl px-2.5 py-1.5 text-slate-100 focus:outline-none cursor-pointer">
                    <option value="BUY">BUY Action</option>
                    <option value="SELL">SELL Action</option>
                    <option value="HOLD">HOLD Position</option>
                </select>
            </div>
        </div>

        <div class="grid grid-cols-3 gap-2.5">
            <div>
                <label class="block text-slate-400 text-[8px] font-bold mb-1 uppercase tracking-wider">Entry ($)</label>
                <input type="number" step="0.01" name="entry_price" value="65000" class="w-full bg-slate-950 border border-slate-800 focus:border-indigo-500 rounded-xl px-2.5 py-1.5 text-slate-100 focus:outline-none transition font-mono">
            </div>
            <div>
                <label class="block text-slate-400 text-[8px] font-bold mb-1 uppercase tracking-wider">Take Profit ($)</label>
                <input type="number" step="0.01" name="target_price" value="67000" class="w-full bg-slate-950 border border-slate-800 focus:border-indigo-500 rounded-xl px-2.5 py-1.5 text-slate-100 focus:outline-none transition font-mono">
            </div>
            <div>
                <label class="block text-slate-400 text-[8px] font-bold mb-1 uppercase tracking-wider">Stop Loss ($)</label>
                <input type="number" step="0.01" name="stop_loss" value="64000" class="w-full bg-slate-950 border border-slate-800 focus:border-indigo-500 rounded-xl px-2.5 py-1.5 text-slate-100 focus:outline-none transition font-mono">
            </div>
        </div>

        <div>
            <label class="block text-slate-400 text-[8px] font-bold mb-1 uppercase tracking-wider">Confidence Gauge (%)</label>
            <input type="number" min="10" max="100" name="confidence" value="88" class="w-full bg-slate-950 border border-slate-800 focus:border-indigo-500 rounded-xl px-2.5 py-1.5 text-slate-100 focus:outline-none transition font-mono">
        </div>

        <div class="pt-1">
            <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-bold py-2.5 px-4 rounded-xl transition cursor-pointer select-none">
                Execute Test Seed
            </button>
        </div>
    </form>
</div>

<!-- Signals logs table listing -->
<h3 class="text-xs font-semibold text-slate-400 uppercase tracking-widest mb-3 font-mono">Roster active</h3>
<div class="space-y-3">
    <?php if (empty($signals)): ?>
        <div class="text-center p-4 bg-slate-900 border border-slate-800/65 rounded-2xl text-[10px] text-slate-500 font-mono">
            No signal indices written to system table.
        </div>
    <?php else: ?>
        <?php foreach ($signals as $sg): ?>
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 text-xs font-mono relative">
                <div class="flex justify-between items-center mb-2">
                    <span class="font-bold text-slate-100"><?= secure_escape($sg['symbol']) ?></span>
                    <span class="text-[9px] font-bold px-1.5 py-0.5 rounded
                        <?php if($sg['signal_type'] === 'BUY'): ?>
                            bg-emerald-950 text-emerald-400
                        <?php else: ?>
                            bg-rose-950 text-rose-400
                        <?php endif; ?>">
                        <?= $sg['signal_type'] ?> (<?= $sg['confidence'] ?>%)
                    </span>
                </div>
                <div class="grid grid-cols-3 gap-1 text-[10px] text-slate-400 border-t border-slate-950/60 pt-2 text-center">
                    <span>Entry: <?= number_format($sg['entry_price'], 2) ?></span>
                    <span>TP: <?= number_format($sg['target_price'], 2) ?></span>
                    <span>SL: <?= number_format($sg['stop_loss'], 2) ?></span>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
