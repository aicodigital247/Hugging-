-- TradeNexa.com - Database Installer SQL
-- =========================================================
-- ⚠️ ARCHITECTURAL MASTER CONSTRAINT (DO NOT REMOVE) ⚠️
-- 1. Strictly ONLY PHP 7.4+ and native MySQLi API.
--    No PDO (`PDO`), ORMs, or external frameworks are permitted.
-- 2. 100% Dependency-Free Production Runtime environment.
-- 3. Double-entry ledger architecture enforces audit trail (no atomic increments).
-- =========================================================
-- Production-Ready for PHP 7.4+ & MySQLi

-- --------------------------------------------------------
-- Table structure for table `users`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `email` VARCHAR(255) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('user', 'admin') NOT NULL DEFAULT 'user',
  `plan` ENUM('free', 'pro', 'vip') NOT NULL DEFAULT 'free',
  `status` ENUM('active', 'banned') NOT NULL DEFAULT 'active',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ip_address` VARCHAR(45) NULL,
  `wallet_balance` DECIMAL(18,8) NOT NULL DEFAULT '0.00000000',
  INDEX (`email`),
  INDEX (`plan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `wallet_ledger`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `wallet_ledger` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `type` ENUM('credit', 'debit') NOT NULL,
  `amount` DECIMAL(18,8) NOT NULL,
  `reason` VARCHAR(255) NOT NULL,
  `balance_after` DECIMAL(18,8) NOT NULL,
  `timestamp` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  INDEX (`user_id`),
  INDEX (`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `cached_candles`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `cached_candles` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `symbol` VARCHAR(20) NOT NULL,
  `timeframe` VARCHAR(10) NOT NULL,
  `open` DECIMAL(18,8) NOT NULL,
  `high` DECIMAL(18,8) NOT NULL,
  `low` DECIMAL(18,8) NOT NULL,
  `close` DECIMAL(18,8) NOT NULL,
  `volume` DECIMAL(18,8) NOT NULL,
  `timestamp` BIGINT NOT NULL,
  UNIQUE KEY `symbol_tf_ts` (`symbol`, `timeframe`, `timestamp`),
  INDEX (`symbol`, `timeframe`),
  INDEX (`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `signals`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `signals` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `symbol` VARCHAR(20) NOT NULL,
  `signal_type` ENUM('BUY', 'SELL', 'HOLD') NOT NULL,
  `confidence` INT NOT NULL,
  `entry_price` DECIMAL(18,8) NOT NULL,
  `target_price` DECIMAL(18,8) NOT NULL,
  `stop_loss` DECIMAL(18,8) NOT NULL,
  `rsi_value` DECIMAL(6,2) NOT NULL,
  `ema_fast` DECIMAL(18,8) NOT NULL,
  `ema_slow` DECIMAL(18,8) NOT NULL,
  `status` ENUM('active', 'filled', 'stopped', 'expired') NOT NULL DEFAULT 'active',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX (`symbol`),
  INDEX (`created_at`),
  INDEX (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `settings`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `settings` (
  `setting_key` VARCHAR(100) PRIMARY KEY,
  `setting_value` TEXT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `ads`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `ads` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `placement` ENUM('banner', 'in_feed', 'market') NOT NULL DEFAULT 'banner',
  `title` VARCHAR(255) NOT NULL,
  `image_url` VARCHAR(500) NOT NULL,
  `link_url` VARCHAR(500) NOT NULL,
  `impressions` INT NOT NULL DEFAULT 0,
  `clicks` INT NOT NULL DEFAULT 0,
  `active` TINYINT(1) NOT NULL DEFAULT 1,
  INDEX (`placement`),
  INDEX (`active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `messages`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `messages` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `content` TEXT NOT NULL,
  `type` ENUM('broadcast', 'alert', 'signal') NOT NULL DEFAULT 'broadcast',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX (`created_at`),
  INDEX (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `push_notifications`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `push_notifications` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `event_type` VARCHAR(50) NOT NULL, -- 'signal_alert', 'market_alert', 'admin_msg'
  `title` VARCHAR(255) NOT NULL,
  `message` TEXT NOT NULL,
  `metadata` TEXT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX (`created_at`),
  INDEX (`event_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `price_alerts`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `price_alerts` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `symbol` VARCHAR(20) NOT NULL,
  `target_price` DECIMAL(18,8) NOT NULL,
  `direction` ENUM('above', 'below') NOT NULL,
  `active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  INDEX (`user_id`),
  INDEX (`symbol`),
  INDEX (`active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `trading_history`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `trading_history` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `symbol` VARCHAR(20) NOT NULL,
  `side` ENUM('buy', 'sell') NOT NULL,
  `order_type` ENUM('market', 'limit') NOT NULL DEFAULT 'market',
  `price` DECIMAL(18,8) NOT NULL,
  `amount` DECIMAL(18,8) NOT NULL,
  `total` DECIMAL(18,8) NOT NULL,
  `status` ENUM('filled', 'pending', 'cancelled') NOT NULL DEFAULT 'filled',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  INDEX (`user_id`),
  INDEX (`symbol`),
  INDEX (`side`),
  INDEX (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `referrals`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `referrals` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NULL,
  `telegram_id` VARCHAR(100) NULL,
  `referee_username` VARCHAR(100) NOT NULL,
  `registered_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `reward_days` INT NOT NULL DEFAULT 7,
  `status` ENUM('pending', 'claimed') NOT NULL DEFAULT 'claimed',
  INDEX (`referee_username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `investment_vaults`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `investment_vaults` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `principal` DECIMAL(18,8) NOT NULL,
  `token` ENUM('USDT', 'TON') NOT NULL DEFAULT 'USDT',
  `apy_rate` DECIMAL(5,2) NOT NULL,
  `daily_accrual` DECIMAL(18,8) NOT NULL,
  `remaining_days` INT NOT NULL DEFAULT 30,
  `status` ENUM('active', 'completed') NOT NULL DEFAULT 'active',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  INDEX (`user_id`),
  INDEX (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `connected_wallets`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `connected_wallets` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `wallet_address` VARCHAR(255) NOT NULL,
  `wallet_provider` VARCHAR(50) NOT NULL, -- 'keeper', 'trust_wallet', 'ton_keeper'
  `connected_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  UNIQUE KEY `user_wallet` (`user_id`, `wallet_address`),
  INDEX (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `deposits`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `deposits` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `reference` VARCHAR(100) NOT NULL UNIQUE,
  `amount` DECIMAL(18,8) NOT NULL,
  `status` ENUM('pending', 'success', 'failed') NOT NULL DEFAULT 'pending',
  `gateway` VARCHAR(50) NOT NULL DEFAULT 'paystack',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  INDEX (`user_id`),
  INDEX (`reference`),
  INDEX (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Seed Initial Base Settings
-- --------------------------------------------------------
INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES
('site_name', 'Bybit Intel'),
('bybit_api_url', 'https://api.bybit.com/v5/market'),
('bybit_api_key', 'db_admin_bybit_read_key'),
('bybit_api_secret', 'db_admin_bybit_secret_secure_key'),
('telegram_bot_token', 'bot7594032148:AAFl_M392810_SecureKey'),
('telegram_webhook_url', 'https://tradenexa.com/app/webhooks/telegram_webhook.php'),
('lang_default', 'en'),
('signal_sensitivity', 'medium'),
('ai_strength_tuning', '95'),
('pricing_pro', '29.99'),
('pricing_vip', '79.99'),
('ads_enabled', '1'),
('maintenance_alert_active', '1'),
('maintenance_alert_msg', 'Bybit Real-time indicators are live synchronized. Server running via supercharged PHP 8.2 backend.')
ON DUPLICATE KEY UPDATE `setting_value` = VALUES(`setting_value`);

-- --------------------------------------------------------
-- Seed Seed Admin Details (Password hash of 'admin123')
-- --------------------------------------------------------
INSERT INTO `users` (`email`, `password`, `role`, `plan`, `status`, `wallet_balance`) VALUES
('admin@saas.com', '$2y$10$wEExBv.I6c6W7FpU5pE5feorI7bAitLhX6q88Y0a3iOnH7h/9pYWe', 'admin', 'vip', 'active', '1000.00000000')
ON DUPLICATE KEY UPDATE `email` = `email`;

-- --------------------------------------------------------
-- Seed Default Ads
-- --------------------------------------------------------
INSERT INTO `ads` (`placement`, `title`, `image_url`, `link_url`, `active`) VALUES
('banner', 'Bybit Promo - Stand a chance to win 1 BTC!', 'https://images.unsplash.com/photo-1621761191319-c6fb62004040?autoforrmat=fit&fit=crop&w=400&q=80', 'https://bybit.com', 1),
('in_feed', 'TradeNexa VIP - Unlock 99% accuracy signal alerts', 'https://images.unsplash.com/photo-1622630998477-20aa696ecb05?auto=format&fit=crop&w=400&q=80', '#/billing/plans', 1),
('market', 'Hardware Wallet Secure Deal - 20% Off Ledger', 'https://images.unsplash.com/photo-1639762681485-074b7f938ba0?auto=format&fit=crop&w=400&q=80', 'https://ledger.com', 1);

-- --------------------------------------------------------
-- Seed Sample Signals
-- --------------------------------------------------------
INSERT INTO `signals` (`symbol`, `signal_type`, `confidence`, `entry_price`, `target_price`, `stop_loss`, `rsi_value`, `ema_fast`, `ema_slow`, `status`) VALUES
('BTCUSDT', 'BUY', 89, 67200.00000000, 69500.00000000, 65500.00000000, 62.42, 67100.00, 66850.00, 'active'),
('ETHUSDT', 'BUY', 76, 3510.50000000, 3680.00000000, 3420.00000000, 58.12, 3505.00, 3490.00, 'active'),
('SOLUSDT', 'SELL', 82, 148.20000000, 137.50000000, 154.00000000, 71.90, 147.50, 148.70, 'active'),
('ADAUSDT', 'HOLD', 50, 0.48500000, 0.52000000, 0.46000000, 48.50, 0.482, 0.483, 'active');
