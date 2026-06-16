<?php
/**
 * TradeNexa.com - Users - Account Credentials & Settings Panel
 * Permits modifying thresholds or general parameters.
 */

require_once dirname(dirname(dirname(__DIR__))) . '/app/core/auth.php';
require_once dirname(dirname(dirname(__DIR__))) . '/app/core/security.php';

$curr_user = auth_current_user();
$uid = intval($curr_user['id']);

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $conn) {
     $csrf = $_POST['csrf_token'] ?? '';
     if (!validate_csrf_token($csrf)) {
         $error = "CSRF checks failed.";
     } else {
         $pass = trim($_POST['password'] ?? '');
         $conf = trim($_POST['confirm_password'] ?? '');
         
         if (empty($pass) || strlen($pass) < 6) {
              $error = "Password must be at least 6 characters long.";
         } elseif ($pass !== $conf) {
              $error = "Passwords do not match. Verification failed.";
         } else {
              $hashed = password_hash($pass, PASSWORD_BCRYPT);
              $stmt = mysqli_prepare($conn, "UPDATE `users` SET `password` = ? WHERE `id` = ?");
              mysqli_stmt_bind_param($stmt, "si", $hashed, $uid);
              mysqli_stmt_execute($stmt);
              mysqli_stmt_close($stmt);
              
              $success = "Your access password has been updated securely.";
         }
     }
}
?>

<div class="mb-5 bg-slate-900 border border-slate-800 p-4 rounded-3xl flex justify-between items-center text-xs">
    <div>
        <h2 class="text-sm font-bold text-slate-100 uppercase tracking-widest">User Settings</h2>
        <span class="text-[9px] text-slate-500 font-mono">Configure profile credentials</span>
    </div>
    <a href="index.php?page=user/profile" class="text-xs text-indigo-400 font-bold hover:underline">Back to Profile</a>
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

<div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 text-xs text-slate-350">
    <h3 class="text-xs font-black uppercase text-slate-300 mb-3 pb-1 border-b border-slate-800">Change Password</h3>
    
    <form method="POST" action="" class="space-y-4">
        <?php csrf_input(); ?>
        
        <div>
            <label class="block text-slate-400 text-[10px] font-bold mb-1 uppercase tracking-wider">New Password</label>
            <input type="password" name="password" required placeholder="••••••••" class="w-full bg-slate-950 border border-slate-800 focus:border-indigo-500 rounded-xl px-3 py-2 text-slate-100 focus:outline-none transition">
        </div>

        <div>
            <label class="block text-slate-400 text-[10px] font-bold mb-1 uppercase tracking-wider">Confirm New Password</label>
            <input type="password" name="confirm_password" required placeholder="••••••••" class="w-full bg-slate-950 border border-slate-800 focus:border-indigo-500 rounded-xl px-3 py-2 text-slate-100 focus:outline-none transition">
        </div>

        <div class="pt-2">
            <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-bold py-2.5 px-4 rounded-xl transition cursor-pointer select-none">
                Update account password
            </button>
        </div>
    </form>
</div>
