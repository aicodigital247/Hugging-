<?php
/**
 * TradeNexa.com - Users - Financial Ledger cockpit
 * Strictly implements ledger-only record tracking with Bybit design parameters.
 */

require_once dirname(dirname(dirname(__DIR__))) . '/app/services/subscription_service.php';
require_once dirname(dirname(dirname(__DIR__))) . '/app/core/auth.php';
require_once dirname(dirname(dirname(__DIR__))) . '/app/core/db.php';
require_once dirname(dirname(dirname(__DIR__))) . '/app/core/security.php';
require_once dirname(dirname(dirname(__DIR__))) . '/app/core/i18n.php';

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

// Handle TON / USDT internal Swap
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['swap_action'])) {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!validate_csrf_token($csrf)) {
        $error = "Security verification expired.";
    } else {
        $swap_type = $_POST['swap_type'] ?? 'buy_ton'; // buy_ton = spend USDT get TON, sell_ton = give TON get USDT
        $swap_amount = floatval($_POST['swap_amount'] ?? 0);
        $ton_price = 7.25; // 1 TON = $7.25 USDT
        
        if ($swap_amount <= 0) {
            $error = "Please enter a valid amount to swap.";
        } else {
            if ($swap_type === 'buy_ton') {
                $cost_usdt = $swap_amount * $ton_price;
                if (floatval($user['wallet_balance']) < $cost_usdt) {
                    $error = "Insufficient USDT balance to complete TON buy.";
                } else {
                    // Deduct USDT, log ledger
                    $new_bal = floatval($user['wallet_balance']) - $cost_usdt;
                    db_query("UPDATE users SET wallet_balance = ? WHERE id = ?", [$new_bal, $uid], "di");
                    
                    // Log double-entry ledger debit
                    db_query("INSERT INTO wallet_ledger (user_id, type, amount, reason, balance_after) VALUES (?, 'debit', ?, ?, ?)", 
                        [$uid, $cost_usdt, "Inbuilt swap: Bought " . $swap_amount . " TON at $" . $ton_price, $new_bal], "idssd");
                    
                    $user['wallet_balance'] = $new_bal;
                    $success = "Successfully swapped $" . number_format($cost_usdt, 2) . " USDT for " . $swap_amount . " TON!";
                }
            } else {
                // Sell TON for USDT (simulated hold of TON, say user receives credit)
                $gain_usdt = $swap_amount * $ton_price;
                $new_bal = floatval($user['wallet_balance']) + $gain_usdt;
                db_query("UPDATE users SET wallet_balance = ? WHERE id = ?", [$new_bal, $uid], "di");
                
                // Log double-entry ledger credit
                db_query("INSERT INTO wallet_ledger (user_id, type, amount, reason, balance_after) VALUES (?, 'credit', ?, ?, ?)", 
                    [$uid, $gain_usdt, "Inbuilt swap: Sold " . $swap_amount . " TON at $" . $ton_price, $new_bal], "idssd");
                
                $user['wallet_balance'] = $new_bal;
                $success = "Successfully sold " . $swap_amount . " TON for $" . number_format($gain_usdt, 2) . " USDT!";
            }
        }
    }
}

// Handle Web3 simulation Wallet connection
if (isset($_GET['connect_mock_provider'])) {
    $provider = secure_escape($_GET['connect_mock_provider']);
    $mock_address = "EQ" . substr(md5(time() . $uid), 0, 16) . "..." . substr(md5($uid), -4);
    
    // Insert into DB
    db_query("INSERT INTO connected_wallets (user_id, wallet_address, wallet_provider) VALUES (?, ?, ?) 
              ON DUPLICATE KEY UPDATE wallet_address = VALUES(wallet_address)", [$uid, $mock_address, $provider], "iss");
    
    set_flash_message('success', "Web3 Wallet " . strtoupper($provider) . " connected successfully!");
    header("Location: index.php?page=user/wallet");
    exit;
}

// Fetch active Web3 Wallet connection
$web3_wallet = null;
$wallets_res = db_query("SELECT * FROM connected_wallets WHERE user_id = ? ORDER BY id DESC LIMIT 1", [$uid], "i");
if (!empty($wallets_res)) {
    $web3_wallet = $wallets_res[0];
}

// PAGINATION parameters
$page_size = 3;
$page = isset($_GET['p']) ? max(1, intval($_GET['p'])) : 1;
$offset = ($page - 1) * $page_size;

$total_rows = 0;
$ledgers = [];

if ($conn) {
    // Count total rows
    $count_res = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM `wallet_ledger` WHERE `user_id` = $uid");
    if ($count_res) {
        $count_row = mysqli_fetch_assoc($count_res);
        $total_rows = intval($count_row['cnt']);
    }
    
    // Fetchpaginated ledger entries
    $led_res = mysqli_query($conn, "SELECT * FROM `wallet_ledger` WHERE `user_id` = $uid ORDER BY `timestamp` DESC LIMIT $page_size OFFSET $offset");
    if ($led_res) {
        while ($row = mysqli_fetch_assoc($led_res)) {
            $ledgers[] = $row;
        }
    }
}
$total_pages = ceil($total_rows / $page_size);
if ($total_pages < 1) $total_pages = 1;
?>

<div class="mb-5 bg-gradient-to-br from-[#1b1c21] to-[#161A1E] border border-[#20262D] rounded-3xl p-5 shadow-sm relative overflow-hidden">
    <div class="absolute -bottom-16 -right-16 w-36 h-36 bg-[#F0B90B]/5 rounded-full blur-2xl"></div>
    
    <span class="text-[9px] font-bold text-gray-500 uppercase tracking-widest font-mono"><?= __('auditBook', 'Bybit Seeded Master Ledger') ?></span>
    <h2 class="font-mono text-2xl font-black text-[#F0B90B] mt-1">$<?= number_format(floatval($user['wallet_balance']), 2) ?> USDT</h2>
    <p class="text-[10px] text-gray-400 mt-2.5 leading-relaxed">
        TradeNexa ensures cryptographic security via standard state ledger rules. Direct data mutation is disabled.
    </p>
</div>

<!-- Web3 Wallet Connection Portal -->
<div class="mb-5 bg-[#161A1E] border border-[#20262D] rounded-3xl p-4">
    <div class="flex items-center justify-between mb-3">
        <h3 class="text-xs font-bold text-[#EAECEF] uppercase tracking-wider"><?= __('walletConnect', 'Web3 Wallet Portal') ?></h3>
        <?php if ($web3_wallet): ?>
            <span class="text-[8px] uppercase tracking-wider font-bold bg-[#03C087]/15 text-[#03C087] px-2 py-0.5 rounded-lg font-mono">Connected</span>
        <?php else: ?>
            <span class="text-[8px] uppercase tracking-wider font-bold bg-gray-800 text-gray-400 px-2 py-0.5 rounded-lg font-mono">Offline</span>
        <?php endif; ?>
    </div>

    <?php if ($web3_wallet): ?>
        <div class="bg-[#0B0E11] border border-[#20262D] rounded-xl p-3 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="text-lg">👛</span>
                <div>
                    <h4 class="text-[11px] font-bold text-[#EAECEF] capitalize"><?= secure_escape($web3_wallet['wallet_provider']) ?> Connected</h4>
                    <p class="text-[9px] text-gray-500 font-mono mt-0.5"><?= secure_escape($web3_wallet['wallet_address']) ?></p>
                </div>
            </div>
            <a href="index.php?page=user/wallet&connect_mock_provider=<?= $web3_wallet['wallet_provider'] ?>" class="text-[9px] font-mono font-bold text-[#F0B90B] hover:underline">Change</a>
        </div>
    <?php else: ?>
        <p class="text-[10px] text-gray-400 mb-3">Connect your secure TON, Trust, or Phantom Web3 wallet to authorize gas transfers:</p>
        <div class="grid grid-cols-3 gap-2">
            <a href="index.php?page=user/wallet&connect_mock_provider=keeper" class="bg-[#0B0E11] border border-[#20262D] hover:border-[#F0B90B]/60 rounded-xl p-2.5 text-center flex flex-col items-center justify-center transition cursor-pointer">
                <span class="text-lg">💎</span>
                <span class="text-[9px] text-[#EAECEF] font-bold mt-1 font-mono">Keeper</span>
            </a>
            <a href="index.php?page=user/wallet&connect_mock_provider=trust" class="bg-[#0B0E11] border border-[#20262D] hover:border-[#F0B90B]/60 rounded-xl p-2.5 text-center flex flex-col items-center justify-center transition cursor-pointer">
                <span class="text-lg">🛡️</span>
                <span class="text-[9px] text-[#EAECEF] font-bold mt-1 font-mono">Trust</span>
            </a>
            <a href="index.php?page=user/wallet&connect_mock_provider=phantom" class="bg-[#0B0E11] border border-[#20262D] hover:border-[#F0B90B]/60 rounded-xl p-2.5 text-center flex flex-col items-center justify-center transition cursor-pointer">
                <span class="text-lg">👻</span>
                <span class="text-[9px] text-[#EAECEF] font-bold mt-1 font-mono">Phantom</span>
            </a>
        </div>
    <?php endif; ?>
</div>

<!-- Inbuilt Buy / Sell Swap Terminal (TON / USDT) -->
<div class="mb-5 bg-[#161A1E] border border-[#20262D] rounded-3xl p-4">
    <div class="flex items-center justify-between mb-3">
        <h3 class="text-xs font-bold text-[#EAECEF] uppercase tracking-wider">Inbuilt TON/USDT Matrix</h3>
        <span class="text-[9px] font-mono text-[#F0B90B] font-bold">1 TON = $7.25 USDT</span>
    </div>

    <form method="POST" action="" class="space-y-3">
        <?php csrf_input(); ?>
        <input type="hidden" name="swap_action" value="execute">
        
        <div class="grid grid-cols-2 gap-2">
            <label class="block cursor-pointer">
                <input type="radio" name="swap_type" value="buy_ton" checked class="sr-only peer">
                <div class="bg-[#0B0E11] border border-[#20262D] peer-checked:border-[#F0B90B] peer-checked:bg-[#F0B90B]/5 rounded-xl p-2 text-center transition">
                    <span class="text-[10px] font-bold block text-emerald-400">Buy TON</span>
                </div>
            </label>
            <label class="block cursor-pointer">
                <input type="radio" name="swap_type" value="sell_ton" class="sr-only peer">
                <div class="bg-[#0B0E11] border border-[#20262D] peer-checked:border-[#F0B90B] peer-checked:bg-[#F0B90B]/5 rounded-xl p-2 text-center transition">
                    <span class="text-[10px] font-bold block text-rose-400">Sell TON</span>
                </div>
            </label>
        </div>

        <div class="flex gap-2">
            <input type="number" step="0.1" name="swap_amount" placeholder="Amount of TON" required class="flex-1 bg-[#0B0E11] border border-[#20262D] focus:border-[#F0B90B] rounded-xl px-3 py-2 text-xs text-[#EAECEF] font-mono focus:outline-none transition">
            <button type="submit" class="bg-[#F0B90B] hover:bg-[#F0B90B]/90 text-black font-black py-2 px-3.5 rounded-xl text-xs transition">
                Execute Swap
            </button>
        </div>
    </form>
</div>

<!-- Ledger Transactions Statement Table -->
<div class="mb-4">
    <div class="flex items-center justify-between mb-3.5">
        <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-widest font-sans">Double-Entry Account Logs</h3>
        <span class="text-[9px] font-mono text-gray-500">Page <?= $page ?> of <?= $total_pages ?> (Total: <?= $total_rows ?>)</span>
    </div>
    
    <div class="space-y-2.5">
        <?php if (empty($ledgers)): ?>
            <div class="text-center p-6 bg-[#161A1E] border border-[#20262D]/60 rounded-2xl text-xs text-slate-500 font-mono">
                [SYSTEM MESSAGE] No ledger hashes documented. Deposit mock capital to register indexes.
            </div>
        <?php else: ?>
            <?php foreach ($ledgers as $lg): ?>
                <div class="bg-[#161A1E] border border-[#20262D] rounded-2xl p-3 text-xs">
                    <div class="flex justify-between items-start gap-4 mb-2">
                        <div>
                            <span class="text-[8px] uppercase font-bold tracking-wider px-1.5 py-0.5 rounded font-mono
                                <?php if($lg['type'] === 'credit'): ?>
                                    bg-[#03C087]/15 text-[#03C087]
                                <?php else: ?>
                                    bg-[#F04F56]/15 text-[#F04F56]
                                <?php endif; ?>">
                                <?= strtoupper($lg['type']) ?>
                            </span>
                            <h4 class="font-bold text-[#EAECEF] mt-1.5 leading-snug"><?= secure_escape($lg['reason']) ?></h4>
                        </div>
                        <div class="text-right flex-shrink-0">
                            <span class="font-mono font-black text-xs <?= ($lg['type'] === 'credit' ? 'text-[#03C087]' : 'text-gray-300') ?>">
                                <?= ($lg['type'] === 'credit' ? '+' : '–') ?>$<?= number_format(floatval($lg['amount']), 2) ?>
                            </span>
                        </div>
                    </div>
                    <div class="flex justify-between items-center text-[9px] text-gray-500 border-t border-[#20262D]/60 pt-2 font-mono">
                        <span>Balance: <strong class="text-gray-400">$<?= number_format(floatval($lg['balance_after']), 2) ?></strong></span>
                        <span><?= date('Y-m-d H:i:s', strtotime($lg['timestamp'])) ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Interactive Navigation Pagination Controls -->
    <?php if ($total_pages > 1): ?>
        <div class="flex items-center justify-between mt-4">
            <a href="index.php?page=user/wallet&p=<?= max(1, $page-1) ?>" class="bg-[#161A1E] border border-[#20262D] hover:border-[#F0B90B]/50 px-3 py-1.5 rounded-xl text-[10px] text-[#EAECEF] font-bold font-sans tracking-wide flex items-center gap-1.5 transition <?= $page <= 1 ? 'opacity-40 pointer-events-none' : '' ?>">
                ← Previous
            </a>
            <div class="flex gap-1">
                <?php for($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="index.php?page=user/wallet&p=<?= $i ?>" class="w-6 h-6 rounded-lg text-[10px] font-mono flex items-center justify-center font-bold transition <?= $page === $i ? 'bg-[#F0B90B] text-black font-black' : 'bg-[#161A1E] border border-[#20262D] text-gray-400' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
            </div>
            <a href="index.php?page=user/wallet&p=<?= min($total_pages, $page+1) ?>" class="bg-[#161A1E] border border-[#20262D] hover:border-[#F0B90B]/50 px-3 py-1.5 rounded-xl text-[10px] text-[#EAECEF] font-bold font-sans tracking-wide flex items-center gap-1.5 transition <?= $page >= $total_pages ? 'opacity-40 pointer-events-none' : '' ?>">
                Next →
            </a>
        </div>
    <?php endif; ?>
</div>

<!-- Simulated deposit widget -->
<div class="mb-5 bg-[#161A1E] border border-[#20262D] rounded-3xl p-4">
    <h3 class="text-xs font-bold text-[#EAECEF] uppercase tracking-wider mb-3">Deposit Simulated Credits</h3>
    
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
        <input type="number" step="10" min="10" max="5000" name="deposit_amount" value="500" required class="flex-1 bg-[#0B0E11] border border-[#20262D] focus:border-[#F0B90B] rounded-xl px-3 py-2 text-xs text-[#EAECEF] font-mono focus:outline-none transition">
        <button type="submit" class="bg-[#F0B90B] hover:bg-[#F0B90B]/90 text-black font-black py-2 px-3.5 rounded-xl text-xs transition whitespace-nowrap cursor-pointer select-none">
            Deposit
        </button>
    </form>
</div>

