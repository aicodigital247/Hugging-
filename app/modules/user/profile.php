<?php
/**
 * TradeNexa.com - Users - Profile & Memberships Manager
 * Visualizes active subscriptions and permits direct simulated deposit triggers as required
 */

require_once dirname(dirname(dirname(__DIR__))) . '/app/core/auth.php';
require_once dirname(dirname(dirname(__DIR__))) . '/app/core/saas.php';
require_once dirname(dirname(dirname(__DIR__))) . '/app/core/db.php';
require_once dirname(dirname(dirname(__DIR__))) . '/app/core/security.php';
require_once dirname(dirname(dirname(__DIR__))) . '/app/core/i18n.php';
require_once dirname(dirname(dirname(__DIR__))) . '/app/services/subscription_service.php';

$user = auth_current_user();
$uid = intval($user['id']);
$tier = saas_get_current_tier();
$plans = saas_get_pricing_plans();

$error = '';
$success = '';

// Handle mock deposit claim inputs
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['claim_credits'])) {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!validate_csrf_token($csrf)) {
        $error = "CSRF security checks failed.";
    } else {
        $claim_amount = 250.00;
        $res = subscription_deposit_mock_funds($uid, $claim_amount);
        if ($res['success']) {
            $user['wallet_balance'] = $res['new_balance'];
            set_flash_message('success', "Mock injection complete! Claimed \$250.00 in test tokens.");
            header("Location: index.php?page=user/profile");
            exit;
        } else {
            $error = $res['message'];
        }
    }
}

// Handle Copier Vault Investment Staking
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['stake_vault'])) {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!validate_csrf_token($csrf)) {
        $error = "Security validation error.";
    } else {
        $stake_amount = floatval($_POST['stake_amount'] ?? 0);
        $stake_token = secure_escape($_POST['stake_token'] ?? 'USDT');
        $apy = ($stake_token === 'USDT') ? 12.5 : 18.2; // TON pays more yield percentage
        
        if ($stake_amount <= 0) {
            $error = "Please enter a valid amount.";
        } elseif (floatval($user['wallet_balance']) < $stake_amount) {
            $error = "Insufficient funds in your main wallet to stake.";
        } else {
            // Deduct USDT from wallet balance
            $new_bal = floatval($user['wallet_balance']) - $stake_amount;
            db_query("UPDATE users SET wallet_balance = ? WHERE id = ?", [$new_bal, $uid], "di");
            
            // Log double entry ledger debit
            db_query("INSERT INTO wallet_ledger (user_id, type, amount, reason, balance_after) VALUES (?, 'debit', ?, ?, ?)",
                [$uid, $stake_amount, "Staked " . $stake_amount . " " . $stake_token . " in Smart Yield Copier", $new_bal], "idssd");
                
            // Create investment vault record
            $daily_accrual = ($stake_amount * ($apy / 100)) / 365;
            db_query("INSERT INTO investment_vaults (user_id, principal, token, apy_rate, daily_accrual, remaining_days, status) VALUES (?, ?, ?, ?, ?, 30, 'active')",
                [$uid, $stake_amount, $stake_token, $apy, $daily_accrual], "idddd");
                
            set_flash_message('success', "Staked " . $stake_amount . " " . $stake_token . " successfully! Yield Copier active.");
            header("Location: index.php?page=user/profile");
            exit;
        }
    }
}

// Handle advanced mock invite simulator trigger
if (isset($_GET['trigger_mock_referral'])) {
    $ref_username = "inviter_" . substr(md5(time()), 0, 5) . "@tradenexa.com";
    
    // Log referral
    db_query("INSERT INTO referrals (user_id, referee_username, reward_days, status) VALUES (?, ?, 7, 'claimed')", 
        [$uid, $ref_username], "is");
        
    // Upgrade current user to PRO level for 7 days automatically!
    $_SESSION['auth_user_plan'] = TIER_VIP;
    
    // Also reward 50.00 mock USDT credits directly to their ledger balance
    $new_bal = floatval($user['wallet_balance']) + 50.00;
    db_query("UPDATE users SET wallet_balance = ? WHERE id = ?", [$new_bal, $uid], "di");
    db_query("INSERT INTO wallet_ledger (user_id, type, amount, reason, balance_after) VALUES (?, 'credit', 50.00, 'Advanced Referral Promo Gift (7 Days VIP Active)', ?)", 
        [$uid, $new_bal], "id");
    
    set_flash_message('success', "Referral registered! You have been granted +7 Days VIP package and $50.00 cash rewards!");
    header("Location: index.php?page=user/profile");
    exit;
}

// Fetch active investment vaults
$active_vaults = [];
if ($conn) {
    $res = mysqli_query($conn, "SELECT * FROM `investment_vaults` WHERE `user_id` = $uid AND `status` = 'active' ORDER BY `created_at` DESC");
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $active_vaults[] = $row;
        }
    }
}

// Fetch general referral logs count
$referrals_count = 0;
if ($conn) {
    $res = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM `referrals` WHERE `user_id` = $uid");
    if ($res) {
        $row = mysqli_fetch_assoc($res);
        $referrals_count = intval($row['cnt']);
    }
}
?>

<div class="mb-5 bg-[#161A1E] border border-[#20262D] rounded-3xl p-5 flex items-center gap-4 relative overflow-hidden">
    <!-- Accent backdrop -->
    <div class="absolute -top-12 -right-12 w-28 h-28 bg-[#F0B90B]/5 rounded-full blur-xl"></div>
    
    <div class="w-12 h-12 bg-[#F0B90B]/10 border border-[#F0B90B]/20 text-[#F0B90B] rounded-2xl flex items-center justify-center font-bold text-lg font-mono">
        <?= strtoupper(substr($user['email'], 0, 1)) ?>
    </div>
    
    <div class="flex-1 pr-6 truncate">
        <h3 class="font-bold text-base text-[#EAECEF] truncate font-sans"><?= secure_escape($user['email']) ?></h3>
        <span class="text-[10px] text-gray-500 font-mono">Registered Account ID: <?= date('Y-m-d', strtotime($user['created_at'])) ?></span>
    </div>
</div>

<!-- Ledger Portfolio Brief -->
<div class="mb-5 bg-[#161A1E] border border-[#20262D] rounded-3xl p-5">
    <span class="text-[9px] text-[#F0B90B] uppercase tracking-widest font-bold block font-sans">Simulated Trading Wallet</span>
    <div class="flex items-baseline justify-between mt-1">
        <h2 class="font-mono text-xl font-black text-[#EAECEF]">$<?= number_format(floatval($user['wallet_balance']), 2) ?> USDT</h2>
        
        <form method="POST" action="">
            <?php csrf_input(); ?>
            <button type="submit" name="claim_credits" value="1" class="text-[9px] font-sans font-black bg-[#F0B90B] hover:bg-[#F0B90B]/90 text-black px-3 py-1.5 rounded-xl cursor-pointer select-none transition">
                + Claim $250
            </button>
        </form>
    </div>
</div>

<!-- Smart Compounding Copier Yield Vault -->
<div class="mb-5 bg-[#161A1E] border border-[#20262D] rounded-3xl p-4">
    <div class="flex items-center justify-between mb-3 border-b border-[#20262D]/60 pb-2">
        <h3 class="text-xs font-black uppercase text-[#EAECEF] tracking-wide"><?= __('investmentSystem', 'Smart Yield Vault') ?></h3>
        <span class="text-[8px] bg-[#03C087]/15 text-[#03C087] font-mono px-2 py-0.5 rounded font-black">15% Compound APY</span>
    </div>

    <!-- Active Staked Holdings -->
    <?php if (!empty($active_vaults)): ?>
        <div class="space-y-2 mb-3.5">
            <?php foreach ($active_vaults as $v): ?>
                <div class="bg-[#0B0E11] border border-[#20262D] rounded-xl p-2.5 text-xs font-mono">
                    <div class="flex justify-between items-center mb-1">
                        <span class="text-gray-400 font-sans font-bold">Invested capital</span>
                        <span class="text-[#03C087] font-black font-mono">$<?= number_format(floatval($v['principal']), 2) ?> (<?= secure_escape($v['token']) ?>)</span>
                    </div>
                    <div class="flex justify-between items-center text-[10px] text-gray-500">
                        <span>Compounding apy: <strong><?= floatval($v['apy_rate']) ?>%</strong></span>
                        <span>Compounded today: <strong class="text-[#F0B90B]">$<?= number_format(floatval($v['daily_accrual']), 5) ?></strong></span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="" class="space-y-2">
        <?php csrf_input(); ?>
        <input type="hidden" name="stake_vault" value="1">
        
        <div class="grid grid-cols-2 gap-2">
            <div>
                <span class="text-[8px] text-gray-500 font-bold block mb-1">Asset Option</span>
                <select name="stake_token" class="w-full bg-[#0B0E11] border border-[#20262D] focus:border-[#F0B90B] rounded-xl px-2 py-1.5 text-[11px] text-[#EAECEF] font-mono focus:outline-none">
                    <option value="USDT">USDT Staking (12.5% APY)</option>
                    <option value="TON">TON Ecosystem (18.2% APY)</option>
                </select>
            </div>
            <div>
                <span class="text-[8px] text-gray-500 font-bold block mb-1">Investment principal</span>
                <input type="number" name="stake_amount" step="10" min="10" placeholder="100" required class="w-full bg-[#0B0E11] border border-[#20262D] focus:border-[#F0B90B] rounded-xl px-2.5 py-1.5 text-xs text-[#EAECEF] font-mono focus:outline-none">
            </div>
        </div>

        <button type="submit" class="w-full bg-[#F0B90B] hover:bg-[#F0B90B]/90 text-black font-black py-2 rounded-xl text-xs transition uppercase font-sans tracking-wide">
            Stake Capital & Start Auto-Yield
        </button>
    </form>
</div>

<!-- Advanced Referral System (7 Days Free VIP) -->
<div class="mb-5 bg-[#161A1E] border border-[#20262D] rounded-3xl p-4">
    <div class="flex items-center justify-between mb-2">
        <h3 class="text-xs font-black uppercase text-[#EAECEF] font-sans">Advanced Dual-Rewards</h3>
        <span class="text-[8px] bg-[#F0B90B]/10 text-[#F0B90B] px-2 py-0.5 rounded font-mono font-bold">Earn 7 Days VIP</span>
    </div>
    <p class="text-[10px] text-gray-400 leading-relaxed font-sans mb-3">
        Share your unique referral code with fellow traders. Once they log in, BOTH of you instantly receive **7 Days Unlimited VIP license** + **$50.00 mock trial balance!**
    </p>

    <!-- Referral code tracker -->
    <div class="bg-[#0B0E11] border border-[#20262D] rounded-xl p-3 mb-3.5">
        <span class="text-[8px] text-gray-500 font-mono uppercase block">Your Referral Link</span>
        <div class="flex items-center justify-between gap-2 mt-1.5 text-xs font-mono">
            <span class="text-[#F0B90B] select-all truncate text-[10px]">https://tradenexa.com/index.php?ref=<?= urlencode($user['email']) ?></span>
            <div class="bg-[#20262D] px-1.5 py-0.5 rounded text-[9px] text-gray-400">Copy</div>
        </div>
    </div>

    <div class="flex items-center justify-between text-[10px] mb-3 font-mono text-gray-400">
        <span>Active Invite count:</span> 
        <strong class="text-brand-500 font-bold"><?= $referrals_count ?> claimed refers</strong>
    </div>

    <!-- Simulator Button for User to claim refer -->
    <a href="index.php?page=user/profile&trigger_mock_referral=1" class="block w-full text-center bg-gray-800 hover:bg-gray-700 border border-[#20262D] text-[#EAECEF] font-black py-2.5 rounded-xl text-xs transition">
        👥 Simulate New Friend Invite (+7 Days VIP)
    </a>
</div>

<!-- Membership Subscription Plans -->
<div class="mb-5">
    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-widest mb-3.5">TradeNexa Licensing Plans</h3>
    
    <div class="space-y-4">
        <?php foreach ($plans as $plan_key => $p): ?>
            <div class="border rounded-2xl p-4 relative overflow-hidden transition
                <?php if ($tier === $plan_key): ?>
                    bg-[#161A1E] border-[#F0B90B]
                <?php else: ?>
                    bg-[#161A1E]/60 border-[#20262D]
                <?php endif; ?>">
                
                <!-- Active badge -->
                <?php if ($tier === $plan_key): ?>
                    <span class="absolute top-3 right-3 text-[8px] font-black text-[#F0B90B] uppercase bg-[#F0B90B]/15 border border-[#F0B90B]/20 px-2 py-0.5 rounded-full">Active Plan</span>
                <?php endif; ?>

                <h4 class="font-extrabold text-sm text-[#EAECEF]"><?= secure_escape($p['name']) ?></h4>
                <div class="mt-1.5 flex items-baseline gap-1">
                    <span class="font-mono text-sm font-black text-[#EAECEF]">$<?= secure_escape($p['price']) ?></span>
                    <span class="text-[9px] text-gray-500">/ month(simulated)</span>
                </div>

                <ul class="mt-3.5 space-y-1.5 border-t border-[#20262D] pt-3">
                    <?php foreach ($p['features'] as $f): ?>
                        <li class="text-[9px] text-gray-400 flex items-center gap-1.5 font-mono">
                            <span class="text-[#03C087]">✓</span>
                            <span><?= secure_escape($f) ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <?php if ($tier !== $plan_key): ?>
                    <div class="mt-4">
                        <a href="index.php?page=billing/plans&upgrade=<?= $plan_key ?>" class="block w-full text-center text-xs font-bold py-2 px-3 rounded-xl border border-[#20262D] bg-[#0B0E11] hover:bg-[#161A1E] text-slate-300 hover:text-slate-100 cursor-pointer select-none transition">
                            Buy/Upgrade License
                        </a>
                    </div>
                <?php endif; ?>

            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Extra Utilities -->
<div class="space-y-2">
    <a href="index.php?page=user/wallet" class="block w-full bg-[#161A1E] border border-[#20262D] hover:border-[#2D343B] p-3 rounded-2xl text-xs font-bold text-gray-300 flex items-center justify-between cursor-pointer">
        <span>🧾 View Wallet Ledger Record Book</span>
        <span>→</span>
    </a>
    
    <a href="index.php?page=auth/logout" class="block w-full bg-rose-950/20 border border-rose-900/30 hover:border-rose-900/50 p-3 rounded-2xl text-xs font-bold text-rose-400 flex items-center justify-between cursor-pointer">
        <span>🚪 Sign Out of Platform Safely</span>
        <span>→</span>
    </a>
</div>
