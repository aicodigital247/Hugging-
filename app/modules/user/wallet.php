<?php
/**
 * TradeNexa.com - Users - Financial Ledger cockpit
 * Strictly implements ledger-only record tracking (no direct column manipulation without ledger logs)
 */

require_once dirname(dirname(dirname(__DIR__))) . '/app/services/subscription_service.php';
require_once dirname(dirname(dirname(__DIR__))) . '/app/core/auth.php';
require_once dirname(dirname(dirname(__DIR__))) . '/app/core/db.php';
require_once dirname(dirname(dirname(__DIR__))) . '/app/core/security.php';

$user = auth_current_user();
$uid = intval($user['id']);

$error = '';
$success = '';

// Handle simulation coin deposits
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['deposit_amount'])) {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!validate_csrf_token($csrf)) {
        $error = "Security validation error. Action halted.";
    } else {
        $amount = floatval($_POST['deposit_amount']);
        if ($amount <= 0 || $amount > 5000) {
            $error = "Deposit limits restricted from \$1 to \$5,000 per simulated transaction.";
        } else {
            $res = subscription_deposit_mock_funds($uid, $amount);
            if ($res['success']) {
                $user['wallet_balance'] = $res['new_balance'];
                $success = $res['message'];
            } else {
                $error = $res['message'];
            }
        }
    }
}

// Fetch all ledger transactions from database
$ledgers = [];
if ($conn) {
    $res = mysqli_query($conn, "SELECT * FROM `wallet_ledger` WHERE `user_id` = $uid ORDER BY `timestamp` DESC");
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $ledgers[] = $row;
        }
    }
}
?>

<div class="mb-5 bg-gradient-to-br from-slate-900 to-indigo-950/40 border border-slate-800 rounded-3xl p-5 shadow-sm relative overflow-hidden">
    <div class="absolute -bottom-16 -right-16 w-36 h-36 bg-emerald-500/5 rounded-full blur-2xl"></div>
    
    <span class="text-[9px] font-bold text-slate-500 uppercase tracking-widest font-mono">Simulated Wallet Ledger</span>
    <h2 class="font-mono text-2xl font-black text-emerald-400 mt-1">$<?= number_format(floatval($user['wallet_balance']), 2) ?> USD</h2>
    <p class="text-[10px] text-slate-400 mt-2.5 leading-relaxed">
        Our architecture enforces a rigorous **double-entry ledger system**. Direct account balance modifications are forbidden. Balance statements are derived entirely from atomic transaction hashes.
    </p>
</div>

<!-- Simulated deposit widget -->
<div class="mb-5 bg-slate-900 border border-slate-800 rounded-3xl p-5">
    <h3 class="text-xs font-bold text-slate-200 uppercase tracking-wider mb-3">Deposit Simulated Credits</h3>
    
    <?php if (!empty($error)): ?>
        <div class="bg-rose-950/40 border border-rose-900 rounded-xl p-3 mb-4 text-xs text-rose-300">
            <?= secure_escape($error) ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div class="bg-emerald-950/40 border border-emerald-900 rounded-xl p-3 mb-4 text-xs text-emerald-300">
            <?= secure_escape($success) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="" class="flex gap-2">
        <?php csrf_input(); ?>
        <input type="number" step="10" min="10" max="5000" name="deposit_amount" value="500" required class="flex-1 bg-slate-950 border border-slate-800 focus:border-indigo-500 rounded-xl px-3 py-2 text-sm text-slate-100 font-mono focus:outline-none transition">
        <button type="submit" class="bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold py-2 px-4 rounded-xl text-xs transition whitespace-nowrap cursor-pointer select-none">
            Deposit Coins
        </button>
    </form>
</div>

<!-- Ledger Transactions Statement Table -->
<div class="mb-2">
    <h3 class="text-xs font-semibold text-slate-400 uppercase tracking-widest mb-3.5">Double-Entry Ledger logs</h3>
    
    <div class="space-y-2.5">
        <?php if (empty($ledgers)): ?>
            <div class="text-center p-6 bg-slate-900 border border-slate-800/60 rounded-2xl text-xs text-slate-550 leading-relaxed font-mono">
                [SYSTEM MESSAGE] No ledger hashes documented. Run database seed files or deposit local simulated coins.
            </div>
        <?php else: ?>
            <?php foreach ($ledgers as $lg): ?>
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-3 text-xs">
                    
                    <div class="flex justify-between items-start gap-4 mb-2">
                        <div>
                            <span class="text-[9px] uppercase font-bold tracking-wider px-1.5 py-0.5 rounded
                                <?php if($lg['type'] === 'credit'): ?>
                                    bg-emerald-950 text-emerald-400 border border-emerald-900/60
                                <?php else: ?>
                                    bg-rose-950 text-rose-400 border border-rose-900/60
                                <?php endif; ?>">
                                [<?= strtoupper($lg['type']) ?>]
                            </span>
                            <h4 class="font-bold text-slate-200 mt-1.5 leading-snug"><?= secure_escape($lg['reason']) ?></h4>
                        </div>
                        
                        <div class="text-right flex-shrink-0">
                            <span class="font-mono font-black text-sm <?= ($lg['type'] === 'credit' ? 'text-emerald-400' : 'text-slate-300') ?>">
                                <?= ($lg['type'] === 'credit' ? '+' : '–') ?>$<?= number_format(floatval($lg['amount']), 2) ?>
                            </span>
                        </div>
                    </div>

                    <div class="flex justify-between items-center text-[10px] text-slate-500 border-t border-slate-950/60 pt-2 font-mono">
                        <span>Balance after: <strong class="text-slate-400">$<?= number_format(floatval($lg['balance_after']), 2) ?></strong></span>
                        <span><?= date('Y-m-d H:i:s', strtotime($lg['timestamp'])) ?></span>
                    </div>

                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
