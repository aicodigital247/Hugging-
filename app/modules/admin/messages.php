<?php
/**
 * TradeNexa.com - Admin - Messages & Bulletin Publishing
 * Handles authoring bulletins, announcements, and global system notices
 */

require_once dirname(dirname(dirname(__DIR__))) . '/app/services/notification_service.php';
require_once dirname(dirname(dirname(__DIR__))) . '/app/core/security.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $conn) {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!validate_csrf_token($csrf)) {
        $error = "CSRF checks failed.";
    } else {
        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $type = trim($_POST['type'] ?? 'broadcast');
        
        if (empty($title) || empty($content)) {
            $error = "Subject and bulletin details cannot be blank.";
        } else {
            $res = notification_create($title, $content, $type);
            if ($res) {
                $success = "Successfully published global notification: '$title'";
            } else {
                $error = "Could not save message to system table.";
            }
        }
    }
}

$notices = notification_get_all(15);
?>

<div class="mb-5 flex justify-between items-center bg-slate-900 border border-slate-800 p-4 rounded-3xl">
    <div>
        <h2 class="text-sm font-black text-slate-100 uppercase tracking-widest">Global Bulletins</h2>
        <span class="text-[10px] text-slate-500 font-mono">Broadcast notifications panel</span>
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

<!-- Bulletin creation form -->
<div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 mb-5 text-xs">
    <h3 class="text-xs font-black uppercase text-slate-350 tracking-wider mb-4 border-b border-sidebar-line pb-2">Publish Bulletin</h3>
    
    <form method="POST" action="" class="space-y-3.5">
        <?php csrf_input(); ?>

        <div>
            <label class="block text-slate-400 text-[10px] font-bold mb-1 uppercase tracking-wider">Notice Category</label>
            <select name="type" class="w-full bg-slate-950 border border-slate-800 focus:border-indigo-500 rounded-xl px-3 py-2 text-slate-100 focus:outline-none cursor-pointer">
                <option value="broadcast">Global Broadcast News</option>
                <option value="alert">System Alert Warning</option>
                <option value="signal">AI Signal announcement</option>
            </select>
        </div>

        <div>
            <label class="block text-slate-400 text-[10px] font-bold mb-1 uppercase tracking-wider">Bullet Heading / Title</label>
            <input type="text" name="title" required placeholder="E.g. System upgrade scheduled" class="w-full bg-slate-950 border border-slate-800 focus:border-indigo-500 rounded-xl px-3 py-2 text-slate-100 focus:outline-none transition">
        </div>

        <div>
            <label class="block text-slate-400 text-[10px] font-bold mb-1 uppercase tracking-wider">Detailed Message Content</label>
            <textarea name="content" required rows="3" placeholder="Explain the notification context..." class="w-full bg-slate-950 border border-slate-800 focus:border-indigo-500 rounded-xl px-3 py-2 text-slate-100 focus:outline-none transition"></textarea>
        </div>

        <div class="pt-2">
            <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-bold py-2.5 px-4 rounded-xl transition cursor-pointer select-none">
                Broadcast Globals
            </button>
        </div>
    </form>
</div>

<!-- Published history log list -->
<h3 class="text-xs font-semibold text-slate-400 uppercase tracking-widest mb-3.5 font-mono">Archive roster</h3>
<div class="space-y-2.5">
    <?php if (empty($notices)): ?>
        <div class="text-center p-4 bg-slate-900 border border-slate-800/65 rounded-2xl text-[10px] text-slate-500 font-mono">
            No published histories found.
        </div>
    <?php else: ?>
        <?php foreach ($notices as $nt): ?>
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-3.5 text-xs relative">
                <div class="flex justify-between items-baseline mb-2">
                    <h4 class="font-extrabold text-slate-200 truncate pr-16"><?= secure_escape($nt['title']) ?></h4>
                    <span class="text-[9px] font-mono text-indigo-400 uppercase">[<?= $nt['type'] ?>]</span>
                </div>
                <p class="text-[10px] text-slate-400 leading-relaxed"><?= secure_escape($nt['content']) ?></p>
                <div class="text-[9px] text-slate-500 font-mono mt-2 pt-1.5 border-t border-slate-950/40">
                    Published: <?= date('Y-m-d H:i:s', strtotime($nt['created_at'])) ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
