# TradeNexa.com — AI Crypto Trading Intelligence SaaS Platform

---

### 🚨 MASTER CONSTRAINT: ONLY PHP & MYSQLI
**TradeNexa.com is strictly engineered to rely ONLY on native PHP 7.4+ and the native `mysqli` driver class. To preserve maximum performance on standard cPanel/Shared Hosting environments, any use of PDO (`PDO`), heavyweight object-relational mappers (ORMs), or external server-side node dependencies in production is strictly forbidden.** 

---

## 🚀 Key Specifications

*   **Runtime Engine**: PHP 7.4 to PHP 8.2+ Compatibility
*   **Database Interface**: Native `mysqli` API (Strictly No PDO)
*   **UI Philosophy**: Strict Mobile-First User-Agent blocker (Adaptive full-screen dark cockpit)
*   **Monetization Structures**: Dynamic subscription tiers gating (Free, Pro, VIP) with globally tunable pricing
*   **Ledger Security**: Double-Entry ledger system enforcing audited balances (no atomic increments)
*   **Admin Settings Engine**: Dynamic SaaS parameter seeder and global configuration state repository

---

## ⚙️ Admin Settings Module Architecture

The SaaS platform features a fully-integrated administration module that controls global variable overrides without requiring code redeployments. These options are persisted inside the database's `settings` table and are accessed via the `settings_get()` function:

### 1. Global Parameters Schema
Inside the `settings` database table, the following keys are registered and live-evaluated:
*   `signal_sensitivity`: Regulates the AI core mathematical filter limits (`low` suppresses volatile false breakouts, `medium` is the baseline, and `high` scales up trend continuity tolerances).
*   `bybit_api_url` / `bybit_api_key` / `bybit_api_secret`: Persists REST gateway coordinates, signatures, parameters, and credentials securely for signature-verified API calls.
*   `pricing_pro` / `pricing_vip`: Dynamically dictates strategy subscription purchase package debits against ledger balances.
*   `maintenance_alert_active` / `maintenance_alert_msg`: Controls and stores prominent system bulletin warn notifications regarding hardware backup operations.

### 2. Live Synchronization Workflow
1. **Interactive Frontend Cockpit**: The administrators' control view provides dedicated toggle selectors, credential inputs, rate adjustments, and alerts editors.
2. **Server-Side Settings Controller**: API connections dispatch the updated settings to `app/core/settings.php` where keys are sanitised and written to the database.
3. **Execution Cascading**:
    *   **Bybit REST Fetching**: `app/services/bybit_service.php` extracts the verified endpoints, timestamps HMAC SHA256 signatures, and routes queries directly.
    *   **AI Indicators Engine**: `app/services/ai_signal_engine.php` evaluates `signal_sensitivity` to scale the ultimate confidence outputs (e.g. BTCUSDT confidence climbs to 98% in high-sensitivity mode).
    *   **Billing Engine**: Subscription upgrades query `pricing_pro` and `pricing_vip` fields to debit wallets with precise accuracy.
    *   **Broadcast Banner**: Core layouts query the active bulletin configurations to display warning banners dynamically.

---

## 🏗️ Structure Summary

```text
/project-root
  ├── /app
  │    ├── /core              # App core engines
  │    │    ├── db.php        # MySQLi connection & prepared statement decorators
  │    │    ├── router.php    # Clean URL regex slug router (no .htaccess dependency)
  │    │    ├── session.php   # Secure cookie sessions & CSRF anti-forgery
  │    │    ├── auth.php      # Bcrypt logins, throttles, and registration
  │    │    ├── middleware.php# Access gatekeepers & mobile-only agent blocks
  │    │    ├── settings.php  # Options registry caches
  │    │    ├── saas.php      # Gating permissions thresholds
  │    │    └── security.php  # XSS cleaners & security logger hooks
  │    │
  │    ├── /services          # Subsystem API & Engines
  │    │    ├── bybit_service.php      # Core cache (15s) and public endpoint fallbacks
  │    │    ├── ai_signal_engine.php  # RSI(14) & EMA(9/21) crossover generators
  │    │    ├── trading_strategy.php  # Tactical recommendations mapper
  │    │    ├── chart_engine.php      # OHLC candlestick coordinates formattings
  │    │    ├── subscription_service.php # Locked ledger debit/credit executors
  │    │    └── notification_service.php # Bulletins and broadcasts publishing
  │    │
  │    └── /modules           # Page routers controller blocks (server-rendered templates)
  │         ├── /auth         # login.php, register.php, logout.php
  │         ├── /user         # dashboard.php, profile.php, wallet.php, signals.php...
  │         ├── /admin        # dashboard.php, users.php, ads.php, messages.php...
  │         └── /market       # charts.php, analysis.php
  │
  ├── /views
  │    └── /layouts           # Header & bottoms footer touch bars navigation
  │
  ├── /public
  │    └── index.php          # Front-Controller entry gateway
  │
  ├── /install
  │    ├── index.php          # Setup UI handshake checker
  │    └── installer.sql      # Seeding table schemas
  │
  └── /storage                # logs/, cache/, uploads/ permissions
```

---

## 🛠️ Direct Installation Guides (Shared Hosting / cPanel)

### Step 1: File Uploads
1. Zip the entire `/project-root` workspace contents.
2. Log into your cPanel File Manager and upload the zip directly into your `public_html/` or a target sub-domain folder.
3. Extract the contents.

### Step 2: Database Setup
1. Enter your cPanel MySQL Database Wizard.
2. Create a new database called (e.g. `tradenexa_db`).
3. Create a database user, assign a strong password, and check "All Privileges" to grant accesses.

### Step 3: Execute Self-Installer
1. Open your browser and navigate directly to your domain address:
   `https://yourdomain.com/install/index.php`
2. Populate the Database Host (`127.0.0.1` or localhost), Username, password and Database Name.
3. Configure your initial Admin details.
4. Click **"Execute DB Installation"**. The installer parses schema tables and outputs an `installed.lock` system file to close setup loops securely.

---

## 🔐 Preset Operator Credentials

To log into your newly deployed cockpit right away, use the default seeded profile:

*   **Operator Email**: `admin@saas.com`
*   **Operator Password**: `admin123`

*(Note: Change your operator passwords inside User Settings immediately upon first access).*

---

## 📡 Dynamic Indicators Setup (Bybit API)

TradeNexa pulls dynamic perpetual charts directly using Bybit's V5 Klines endpoint block. 
1. If your hosting provider blocks outbound curl headers, TradeNexa's failsafe engine will dynamically generate beautiful, mathematical wave vectors to draw candles, letting users experience moving charts continuously and seamlessly!
2. To modify API URLs or tune signal indicator margins, go to:
   `Admin Cockpit` ➡️ `BYBIT API & AI Strength Tuning`.

---

## 💳 Ledger-Only Compliance Rules

To prevent coin balance manipulation, never run raw `UPDATE users SET wallet_balance = X` queries in custom files. Always commit debits/credits through our services wrapper:

```php
// Credit test money
require_once 'app/services/subscription_service.php';
subscription_deposit_mock_funds($user_id, 250.00);

// Charging fee for premium upgrades
subscription_upgrade_plan($user_id, 'pro');
```

---

## 📢 Ads & Monetization Setup

Administrators can toggle and scale dynamic banner slas with custom graphic sources directly under `Admin Cockpit` ➡️ `Promotional Ads Placements`.

---

*TradeNexa — Smart. Fast. Mobile-First. Fully Secure.*
