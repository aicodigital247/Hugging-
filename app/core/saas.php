<?php
/**
 * TradeNexa.com - SaaS Subscription & Feature Gating Controller
 * Governs license permissions for Free, Pro, and VIP membership models
 */

require_once __DIR__ . '/session.php';
require_once __DIR__ . '/settings.php';

define('TIER_FREE', 'free');
define('TIER_PRO', 'pro');
define('TIER_VIP', 'vip');

/**
 * Gets the active session user category tier or falls back to 'free'
 */
function saas_get_current_tier() {
    return isset($_SESSION['auth_user_plan']) ? $_SESSION['auth_user_plan'] : TIER_FREE;
}

/**
 * Validates if the active tier has permission for premium elements
 */
function saas_can_access_signals() {
    $tier = saas_get_current_tier();
    // All tiers can access, but admin configurations or accuracy rates differ
    return true;
}

/**
 * Signal visibility gating based on Subscription tier (e.g. Free gets less accuracy confidence)
 */
function saas_gate_signal($signal) {
    $tier = saas_get_current_tier();
    
    // VIP gets raw values
    if ($tier === TIER_VIP) {
        return $signal;
    }
    
    // Pro gets slight delay but matches confidence
    if ($tier === TIER_PRO) {
        $signal['confidence'] = max(30, $signal['confidence'] - 2);
        return $signal;
    }
    
    // Free tier signals have restricted access: hide entry/tp/sl or hide high-confidence signals
    $signal['entry'] = '🔒 VIP';
    $signal['tp'] = '🔒 VIP';
    $signal['sl'] = '🔒 VIP';
    $signal['confidence'] = '🔒';
    return $signal;
}

/**
 * Checks whether user should be shown monetization ads (Free tiers only)
 */
function saas_should_show_ads() {
    $tier = saas_get_current_tier();
    return ($tier === TIER_FREE);
}

/**
 * Validates indicator access (RSI & EMA charts overlay)
 */
function saas_has_indicator_access() {
    $tier = saas_get_current_tier();
    return ($tier === TIER_PRO || $tier === TIER_VIP);
}

/**
 * Retrieves pricing configuration matrix
 */
function saas_get_pricing_plans() {
    return [
        TIER_FREE => [
            'name' => 'Free Trial',
            'price' => '0.00',
            'signals' => 'Delayed (No TP/SL)',
            'charts' => 'Basic candles',
            'ads' => 'Normal Banner Ads',
            'features' => ['Delayed Basic Signals', 'No RSI Indicators', 'Standard Latency', 'Ad Supported']
        ],
        TIER_PRO => [
            'name' => 'TradeNexa Pro',
            'price' => settings_get('pricing_pro', '29.99'),
            'signals' => 'Instant alerts',
            'charts' => 'All overlay indicators',
            'ads' => 'Ad Free Experience',
            'features' => ['Instant Trading Signals', 'TP, SL & Entry targets unlocked', 'EMA overlay charts', 'Ad-Free Operations']
        ],
        TIER_VIP => [
            'name' => 'TradeNexa VIP',
            'price' => settings_get('pricing_vip', '79.99'),
            'signals' => 'Advanced 99% accuracy tuning',
            'charts' => 'Full Candlestick & Volume',
            'ads' => 'Ultra high latency pipeline',
            'features' => ['Full High-Confidence Signals', 'Unlimited alerts', 'EMA + RSI on responsive chart', 'VIP Telegram Channel entry', 'Priority Support desk']
        ]
    ];
}
