<?php
/**
 * TradeNexa.com - Admin - Users Management
 * Regulates operator control over users status, custom membership upgrades, and credit monitoring
 */

require_once dirname(dirname(dirname(__DIR__))) . '/app/core/db.php';
require_once dirname(dirname(dirname(__DIR__))) . '/app/core/security.php';

$error = '';
$success = '';

// Handle actions (Ban / Unban / Upgrade Plan)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $conn) {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!validate_csrf_token($csrf)) {
        $error = "CSRF verification failed.";
    } else {
        $target_uid = intval($_POST['user_id'] ?? 0);
        $action = trim($_POST['action'] ?? '');
        
        if ($target_uid > 0) {
            // Protect admin self-banning
            if ($target_uid === intval($_SESSION['auth_user_id']) && $action === 'ban') {
                $error = "Administrators are strictly forbidden from suspending their own accounts.";
            } else {
                if ($action === 'ban') {
                    $stmt = mysqli_prepare($conn, "UPDATE `users` SET `status` = 'banned' WHERE `id` = ?");
                    mysqli_stmt_bind_param($stmt, "i", $target_uid);
                    mysqli_stmt_execute($stmt);
                    mysqli_stmt_close($stmt);
                    $success = "User #$target_uid has been successfully suspended (Banned status active).";
                } elseif ($action === 'unban') {
                    $stmt = mysqli_prepare($conn, "UPDATE `users` SET `status` = 'active' WHERE `id` = ?");
                    mysqli_stmt_bind_param($stmt, "i", $target_uid);
                    mysqli_stmt_execute($stmt);
                    mysqli_stmt_close($stmt);
                    $success = "User #$target_uid suspension removed successfully.";
                } elseif ($action === 'upgrade_pro') {
                    $stmt = mysqli_prepare($conn, "UPDATE `users` SET `plan` = 'pro' WHERE `id` = ?");
                    mysqli_stmt_bind_param($stmt, "i", $target_uid);
                    mysqli_stmt_execute($stmt);
                    mysqli_stmt_close($stmt);
                    $success = "User #$target_uid elevated to PRO tier license.";
                } elseif ($action === 'upgrade_vip') {
                    $stmt = mysqli_prepare($conn, "UPDATE `users` SET `plan` = 'vip' WHERE `id` = ?");
                    mysqli_stmt_bind_param($stmt, "i", $target_uid);
                    mysqli_stmt_execute($stmt);
                    mysqli_stmt_close($stmt);
                    $success = "User #$target_uid elevated to VIP tier license.";
                }
            }
        }
    }
}

// Fetch users
$users = [];
if ($conn) {
    $res = mysqli_query($conn, "SELECT `id`, `email`, `role`, `plan`, `status`, `wallet_balance`, `created_at` FROM `users` ORDER BY `id` ASC LIMIT 100");
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $users[] = $row;
        }
    }
}
?>

<div class="mb-5 flex justify-between items-center bg-slate-900 border border-slate-800 p-4 rounded-3xl">
    <div>
        <h2 class="text-sm font-black text-slate-100 uppercase tracking-widest">Traders Directory</h2>
        <span class="text-[10px] text-slate-500 font-mono">Operations desk registry</span>
    </div>
    <a href="index.php?page=admin/dashboard" class="text-xs text-indigo-400 font-bold hover:underline">Back to Central</a>
</div>

<?php if(!empty($error)): ?>
    <div class="bg-rose-950/40 border border-rose-900 rounded-xl p-3 mb-4 text-xs text-rose-300">
        <?= secure_escape($error) ?>
    </div>
<?php endif; ?>

<?php if(!empty($success)): ?>
    <div class="bg-emerald-950/40 border border-emerald-900 rounded-xl p-3 mb-4 text-xs text-emerald-300">
        <?= secure_escape($success) ?>
    </div>
<?php endif; ?>

<!-- Users roster list -->
<div class="space-y-4">
    <?php foreach ($users as $us): ?>
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-4.5 text-xs">
            
            <div class="flex justify-between items-baseline mb-2">
                <div>
                    <h3 class="font-extrabold text-slate-200 truncate max-w-[140px] inline-block"><?= secure_escape($us['email']) ?></h3>
                    <span class="text-[9px] font-mono font-bold text-slate-500 block">ID: #<?= $us['id'] ?> | Joined: <?= date('Y-m-d', strtotime($us['created_at'])) ?></span>
                </div>
                
                <div class="text-right">
                    <span class="text-[9px] font-mono font-bold px-1.5 py-0.5 rounded leading-none border uppercase
                        <?php if($us['status'] === 'banned'): ?>
                            bg-rose-950/60 text-rose-400 border-rose-800
                        <?php else: ?>
                            bg-emerald-950/60 text-emerald-400 border-emerald-800
                        <?php endif; ?>">
                        <?= strtoupper($us['status']) ?>
                    </span>
                    <span class="text-[9px] font-mono font-bold block mt-1.5 text-indigo-400 uppercase"><?= $us['plan'] ?> TIER</span>
                </div>
            </div>

            <!-- Stats parameters -->
            <div class="bg-slate-950/50 p-2.5 rounded-xl border border-slate-850/60 flex justify-between items-center text-[10px] font-mono mb-3.5">
                <span class="text-slate-500">Wallet balance:</span>
                <span class="font-bold text-slate-200">$<?= number_format(floatval($us['wallet_balance']), 2) ?></span>
            </div>

            <!-- Admin action operators -->
            <form method="POST" action="" class="flex gap-2 justify-end border-t border-slate-950/60 pt-3">
                <?php csrf_input(); ?>
                <input type="hidden" name="user_id" value="<?= $us['id'] ?>">
                
                <?php if ($us['status'] === 'active'): ?>
                    <button type="submit" name="action" value="ban" class="bg-rose-950 text-rose-400 font-bold border border-rose-900 px-3 py-1.5 rounded-xl text-[10px] hover:bg-rose-900 transition flex items-center justify-center cursor-pointer select-none">
                        Ban User
                    </button>
                <?php else: ?>
                    <button type="submit" name="action" value="unban" class="bg-emerald-950 text-emerald-400 font-bold border border-emerald-900 px-3 py-1.5 rounded-xl text-[10px] hover:bg-emerald-900 transition flex items-center justify-center cursor-pointer select-none">
                        Unban
                    </button>
                <?php endif; ?>

                <?php if ($us['plan'] !== 'vip'): ?>
                    <button type="submit" name="action" value="upgrade_vip" class="bg-indigo-950 text-indigo-400 font-bold border border-indigo-900 px-3 py-1.5 rounded-xl text-[10px] hover:bg-indigo-900 transition flex items-center justify-center cursor-pointer select-none">
                        VIP Upgrade
                    </button>
                <?php endif; ?>
            </form>

        </div>
    <?php endforeach; ?>
</div>
