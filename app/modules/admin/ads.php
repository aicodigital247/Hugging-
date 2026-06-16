<?php
/**
 * TradeNexa.com - Admin - Monetization Advertisements
 * Permits dynamic allocation of banner placements, tracking campaigns and toggles
 */

require_once dirname(dirname(dirname(__DIR__))) . '/app/core/db.php';
require_once dirname(dirname(dirname(__DIR__))) . '/app/core/security.php';

$error = '';
$success = '';

// Handle Ad registration form submits
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $conn) {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!validate_csrf_token($csrf)) {
         $error = "CSRF checks failed.";
    } else {
        $placement = trim($_POST['placement'] ?? 'in_feed');
        $title = trim($_POST['title'] ?? '');
        $img = trim($_POST['image_url'] ?? '');
        $link = trim($_POST['link_url'] ?? '#');
        
        if (empty($title) || empty($img)) {
            $error = "Please provide both banner title and image address assets.";
        } else {
            $stmt = mysqli_prepare($conn, "INSERT INTO `ads` (`placement`, `title`, `image_url`, `link_url`, `active`) VALUES (?, ?, ?, ?, 1)");
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "ssss", $placement, $title, $img, $link);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
                $success = "Promotional campaign '$title' registered and deployed in active rotation.";
            } else {
                $error = "Database write error.";
            }
        }
    }
}

// Fetch all ads
$ads = [];
if ($conn) {
    $res = mysqli_query($conn, "SELECT * FROM `ads` ORDER BY `id` DESC LIMIT 40");
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $ads[] = $row;
        }
    }
}
?>

<div class="mb-5 flex justify-between items-center bg-slate-900 border border-slate-800 p-4 rounded-3xl">
    <div>
        <h2 class="text-sm font-black text-slate-100 uppercase tracking-widest">Promotion Slates</h2>
        <span class="text-[10px] text-slate-500 font-mono">Monetization setup desk</span>
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

<!-- Create Ad campaign form widget -->
<div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 mb-5 text-xs">
    <h3 class="text-xs font-black uppercase text-slate-350 tracking-wider mb-4 border-b border-sidebar-line pb-2">Deploy Campaign Slate</h3>
    
    <form method="POST" action="" class="space-y-3.5">
        <?php csrf_input(); ?>

        <div>
            <label class="block text-slate-400 text-[10px] font-bold mb-1 uppercase tracking-wider">Placement Slot</label>
            <select name="placement" class="w-full bg-slate-950 border border-slate-800 focus:border-indigo-500 rounded-xl px-3 py-2 text-slate-100 focus:outline-none cursor-pointer">
                <option value="in_feed">Standard In-Feed ad</option>
                <option value="banner">Bottom Banner slot</option>
                <option value="market">Markets index slot</option>
            </select>
        </div>

        <div>
            <label class="block text-slate-400 text-[10px] font-bold mb-1 uppercase tracking-wider">Campaign Title</label>
            <input type="text" name="title" required placeholder="E.g. Get 20% cashback on Bybit" class="w-full bg-slate-950 border border-slate-800 focus:border-indigo-500 rounded-xl px-3 py-2 text-slate-100 focus:outline-none transition">
        </div>

        <div>
            <label class="block text-slate-400 text-[10px] font-bold mb-1 uppercase tracking-wider">Creative Image URL</label>
            <input type="url" name="image_url" required placeholder="https://domain.com/photo.png" class="w-full bg-slate-950 border border-slate-800 focus:border-indigo-500 rounded-xl px-3 py-2 text-slate-100 focus:outline-none transition">
        </div>

        <div>
            <label class="block text-slate-400 text-[10px] font-bold mb-1 uppercase tracking-wider">Destination Links</label>
            <input type="url" name="link_url" placeholder="https://bybit.com/" class="w-full bg-slate-950 border border-slate-800 focus:border-indigo-500 rounded-xl px-3 py-2 text-slate-100 focus:outline-none transition">
        </div>

        <div class="pt-2">
            <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-bold py-2.5 px-4 rounded-xl transition cursor-pointer select-none">
                Launch Campaign
            </button>
        </div>
    </form>
</div>

<!-- campaigns in rotation -->
<h3 class="text-xs font-semibold text-slate-400 uppercase tracking-widest mb-3.5">Active Ad campaigns</h3>
<div class="space-y-3">
    <?php if (empty($ads)): ?>
        <div class="text-center p-4 bg-slate-900 border border-slate-800/65 rounded-2xl text-[11px] text-slate-500 font-mono">
            No dynamic ad Slates registered yet.
        </div>
    <?php else: ?>
        <?php foreach ($ads as $ad): ?>
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-3 flex gap-3 text-xs items-center relative">
                <img src="<?= secure_escape($ad['image_url']) ?>" referrerPolicy="no-referrer" alt="Ad" class="w-10 h-10 object-cover rounded-lg border border-slate-800">
                <div class="flex-1 truncate pr-16">
                    <h4 class="font-extrabold text-slate-200 truncate"><?= secure_escape($ad['title']) ?></h4>
                    <span class="text-[9px] font-mono font-semibold text-slate-500 uppercase block mt-1">Slot: <?= $ad['placement'] ?></span>
                </div>
                <div class="absolute right-3.5 top-1/2 -translate-y-1/2">
                    <span class="text-[9px] font-bold py-0.5 px-1.5 rounded uppercase border bg-slate-950 text-emerald-400 border-emerald-950">Active</span>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
