<?php
/**
 * TradeNexa.com - Admin - General SaaS Options Registry
 * Controls pricing ratios, Bybit API endpoints, and AI engine sensitivity factors
 */

require_once dirname(dirname(dirname(__DIR__))) . '/app/core/settings.php';
require_once dirname(dirname(dirname(__DIR__))) . '/app/core/security.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!validate_csrf_token($csrf)) {
        $error = "CSRF validation failed.";
    } else {
        // Save targeted options keys
        settings_set('site_name', trim($_POST['site_name'] ?? 'TradeNexa'));
        settings_set('bybit_api_url', trim($_POST['bybit_api_url'] ?? 'https://api.bybit.com'));
        settings_set('signal_sensitivity', trim($_POST['signal_sensitivity'] ?? 'medium'));
        settings_set('ai_strength_tuning', trim($_POST['ai_strength_tuning'] ?? '90'));
        settings_set('pricing_pro', trim($_POST['pricing_pro'] ?? '29.99'));
        settings_set('pricing_vip', trim($_POST['pricing_vip'] ?? '79.99'));
        settings_set('maintenance_mode', (isset($_POST['maintenance_mode']) ? '1' : '0'));
        settings_set('maintenance_alert_text', trim($_POST['maintenance_alert_text'] ?? ''));
        
        $success = "TradeNexa SaaS settings registry updated successfully.";
    }
}

// Load configurations
$site_name = settings_get('site_name', 'TradeNexa');
$bybit_url = settings_get('bybit_api_url', 'https://api.bybit.com');
$sensitivity = settings_get('signal_sensitivity', 'medium');
$ai_strength = settings_get('ai_strength_tuning', '90');
$pro_price = settings_get('pricing_pro', '29.99');
$vip_price = settings_get('pricing_vip', '79.99');
$maint_mode = settings_get('maintenance_mode', '0');
$maint_text = settings_get('maintenance_alert_text', 'Scheduled system upgrade active. Real-time updates might trigger lag.');
?>

<div class="mb-5 flex justify-between items-center bg-[#161A1E] border border-[#20262D] p-4 rounded-3xl">
    <div>
        <h2 class="text-sm font-black text-[#EAECEF] uppercase tracking-widest font-sans">SaaS Parameters</h2>
        <span class="text-[10px] text-gray-500 font-mono">Operations registry editor</span>
    </div>
    <a href="index.php?page=admin/dashboard" class="text-xs text-[#F0B90B] font-bold hover:underline font-sans">Back to Central</a>
</div>

<?php if(!empty($error)): ?>
    <div class="bg-rose-950/40 border border-rose-900 rounded-xl p-3 mb-4 text-xs text-rose-300 font-mono">
        <?= secure_escape($error) ?>
    </div>
<?php endif; ?>

<?php if(!empty($success)): ?>
    <div class="bg-emerald-950/40 border border-emerald-950 rounded-xl p-3 mb-4 text-xs text-emerald-300 font-mono">
        <?= secure_escape($success) ?>
    </div>
<?php endif; ?>

<!-- Core Configuration Form -->
<div class="bg-[#161A1E] border border-[#20262D] rounded-3xl p-5 text-xs text-gray-400">
    <form method="POST" action="" class="space-y-4">
        <?php csrf_input(); ?>

        <div>
            <label class="block text-gray-400 text-[10px] font-bold mb-1 uppercase tracking-wider font-sans">Dynamic Platform Name</label>
            <input type="text" name="site_name" value="<?= secure_escape($site_name) ?>" required class="w-full bg-[#0B0E11] border border-[#20262D] focus:border-[#F0B90B] rounded-xl px-3 py-2 text-[#EAECEF] focus:outline-none transition">
        </div>

        <div>
            <label class="block text-gray-400 text-[10px] font-bold mb-1 uppercase tracking-wider font-sans">Bybit API REST URL</label>
            <input type="url" name="bybit_api_url" value="<?= secure_escape($bybit_url) ?>" required class="w-full bg-[#0B0E11] border border-[#20262D] focus:border-[#F0B90B] rounded-xl px-3 py-2 text-[#EAECEF] focus:outline-none transition font-mono">
            <span class="text-[9px] text-gray-500 mt-1 block leading-relaxed font-mono">Default is **https://api.bybit.com** (linear perpetual klines endpoint).</span>
        </div>

        <div class="grid grid-cols-2 gap-3.5">
            <div>
                <label class="block text-gray-400 text-[10px] font-bold mb-1 uppercase tracking-wider font-sans">Signal Strategy</label>
                <select name="signal_sensitivity" class="w-full bg-[#0B0E11] border border-[#20262D] focus:border-[#F0B90B] rounded-xl px-3 py-2 text-[#EAECEF] focus:outline-none cursor-pointer">
                    <option value="high" <?= ($sensitivity === 'high' ? 'selected' : '') ?>>High Sensitivity</option>
                    <option value="medium" <?= ($sensitivity === 'medium' ? 'selected' : '') ?>>Medium (Optimized)</option>
                    <option value="low" <?= ($sensitivity === 'low' ? 'selected' : '') ?>>Conservative (Safe)</option>
                </select>
            </div>
            <div>
                <label class="block text-gray-400 text-[10px] font-bold mb-1 uppercase tracking-wider font-sans">Confidence Level Calibration</label>
                <input type="number" min="50" max="100" name="ai_strength_tuning" value="<?= secure_escape($ai_strength) ?>" required class="w-full bg-[#0B0E11] border border-[#20262D] focus:border-[#F0B90B] rounded-xl px-3 py-2 text-[#EAECEF] focus:outline-none transition font-mono">
            </div>
        </div>

        <div class="grid grid-cols-2 gap-3.5 border-t border-[#20262D] pt-4">
            <div>
                <label class="block text-gray-400 text-[10px] font-bold mb-1 uppercase tracking-wider font-sans">Pro Tier Rate ($)</label>
                <input type="number" step="0.01" min="0" name="pricing_pro" value="<?= secure_escape($pro_price) ?>" required class="w-full bg-[#0B0E11] border border-[#20262D] focus:border-[#F0B90B] rounded-xl px-3 py-2 text-[#EAECEF] focus:outline-none transition font-mono">
            </div>
            <div>
                <label class="block text-gray-400 text-[10px] font-bold mb-1 uppercase tracking-wider font-sans">VIP Tier Rate ($)</label>
                <input type="number" step="0.01" min="0" name="pricing_vip" value="<?= secure_escape($vip_price) ?>" required class="w-full bg-[#0B0E11] border border-[#20262D] focus:border-[#F0B90B] rounded-xl px-3 py-2 text-[#EAECEF] focus:outline-none transition font-mono">
            </div>
        </div>

        <!-- Global Maintenance settings -->
        <div class="border-t border-[#20262D] pt-4 space-y-3">
            <div class="flex items-center justify-between">
                <div>
                    <h4 class="text-xs font-black text-[#EAECEF] font-sans">System Maintenance Alerts</h4>
                    <span class="text-[9px] text-gray-500 font-sans">Inject systemic bulletins into trader layouts globally</span>
                </div>
                <input type="checkbox" name="maintenance_mode" value="1" <?= ($maint_mode === '1' ? 'checked' : '') ?> class="w-4 h-4 bg-[#0B0E11] border border-[#20262D] text-[#F0B90B] focus:ring-0 rounded cursor-pointer">
            </div>
            <div>
                <label class="block text-gray-400 text-[9px] font-bold mb-1 uppercase tracking-wider font-sans">Flash Message Headline Context</label>
                <textarea name="maintenance_alert_text" rows="2" class="w-full bg-[#0B0E11] border border-[#20262D] focus:border-[#F0B90B] rounded-xl px-3 py-2 text-[#EAECEF] focus:outline-none transition font-mono uppercase text-[10px]"><?= secure_escape($maint_text) ?></textarea>
            </div>
        </div>

        <div class="pt-2">
            <button type="submit" class="w-full bg-[#F0B90B] hover:bg-[#F0B90B]/90 text-black font-extrabold py-3 px-4 rounded-xl shadow-lg transition cursor-pointer select-none">
                Save Parameter Schema
            </button>
        </div>
    </form>
</div>
