<?php
/**
 * TradeNexa.com - Multilingual i18n Translation Dictionary
 * Managed via session variable or active 'lang' cookies.
 * Supports dynamic toggle of Cockpit labels.
 */

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$active_lang = 'en';
if (isset($_GET['lang'])) {
    $active_lang = strtolower($_GET['lang']);
    $_SESSION['app_lang'] = $active_lang;
    setcookie('app_lang', $active_lang, time() + (86400 * 30), "/");
} elseif (isset($_SESSION['app_lang'])) {
    $active_lang = $_SESSION['app_lang'];
} elseif (isset($_COOKIE['app_lang'])) {
    $active_lang = $_COOKIE['app_lang'];
}

// Restrict to valid locales
if (!in_array($active_lang, ['en', 'es', 'zh'])) {
    $active_lang = 'en';
}

define('APP_LANG', $active_lang);

$GLOBALS['LANG_DICT'] = [
    'en' => [
        'title' => "TRADENEXA.AI COCKPIT",
        'desc' => "PHP 7.4+ Backend & Bybit API Architecture",
        'markets' => "Markets",
        'charts' => "Charts",
        'signals' => "AI Signals",
        'ledger' => "Ledger",
        'profile' => "Profile/Rewards",
        'admin' => "Admin Console",
        'tickerHeader' => "Hot Contracts Tickers",
        'signalHeader' => "Proprietary AI Signal Feed",
        'claimHeader' => "Seeding Free Test Credits",
        'activePlan' => "Membership Level",
        'upgrade' => "Upgrade",
        'deposit' => "Deposit",
        'investmentSystem' => "Smart Yield Vault (Invest to Trade)",
        'walletConnect' => "Web3 Wallet Portal",
        'alerts' => "Dynamic Price Alerts",
        'onboarding' => "Onboarding Tour",
        'telemetry' => "Telemetry overlays",
        'auditBook' => "Audit Book Ledger",
        'bybitMkt' => "Bybit Linear Market Feed"
    ],
    'es' => [
        'title' => "CABINA TRADENEXA.AI",
        'desc' => "Soporte de Backend PHP 7.4+ y Bybit API",
        'markets' => "Mercados",
        'charts' => "Gráficos",
        'signals' => "Señales IA",
        'ledger' => "Libro Mayor",
        'profile' => "Perfil/Premios",
        'admin' => "Consola Admin",
        'tickerHeader' => "Marcadores de Contrato",
        'signalHeader' => "Feed de Señales Propietarias de IA",
        'claimHeader' => "Sembrar Créditos Gratuitos",
        'activePlan' => "Nivel de Membresía",
        'upgrade' => "Mejorar",
        'deposit' => "Depositar",
        'investmentSystem' => "Bóveda de Rendimiento Inteligente",
        'walletConnect' => "Portal de Billetera Web3",
        'alerts' => "Alertas de Precios Activa",
        'onboarding' => "Tour de Bienvenida",
        'telemetry' => "Superposiciones de telemetría",
        'auditBook' => "Libro Contable Auditado",
        'bybitMkt' => "Feed de Mercado Bybit"
    ],
    'zh' => [
        'title' => "TRADENEXA.AI 智能舱",
        'desc' => "PHP 7.4+ 后端和 Bybit API 极速架构",
        'markets' => "主流合约",
        'charts' => "专业 K 线",
        'signals' => "AI 信号流",
        'ledger' => "精算账本",
        'profile' => "尊客服务与推介",
        'admin' => "管理后台",
        'tickerHeader' => "实时合约行情流",
        'signalHeader' => "AI 独家自动量化计算流",
        'claimHeader' => "充值免费沙盒测试资金",
        'activePlan' => "当前特权账户级别",
        'upgrade' => "立即升级特权",
        'deposit' => "一键入账",
        'investmentSystem' => "高频智能跟单理财 (一键托管)",
        'walletConnect' => "Web3 专业冷热钱包连接",
        'alerts' => "价格阈值秒级推送提醒",
        'onboarding' => "新手指引通关大礼包",
        'telemetry' => "高阶量化指标图层",
        'auditBook' => "哈希级双向资金对账账本",
        'bybitMkt' => "Bybit 永续合约官方行情源"
    ]
];

/**
 * Translate helper function
 */
function __($key, $fallback = '') {
    $lang = APP_LANG;
    if (isset($GLOBALS['LANG_DICT'][$lang][$key])) {
        return $GLOBALS['LANG_DICT'][$lang][$key];
    }
    if (isset($GLOBALS['LANG_DICT']['en'][$key])) {
        return $GLOBALS['LANG_DICT']['en'][$key];
    }
    return !empty($fallback) ? $fallback : $key;
}
