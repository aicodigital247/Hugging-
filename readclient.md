# TradeNexa.com — AI Crypto Trading Intelligence SaaS Platform

Welcome to the production repository of **TradeNexa.com**, a modern, mobile-first cryptocurrency market intelligence SaaS platform built natively for shared hosting environments. Traditional frameworks consume significant memory resources; TradeNexa is architected using **pure PHP 7.4+ and MySQLi (with absolutely zero dependencies or bloated Node packages required for production)**.

---

## 🚀 Key Specifications

*   **Runtime Engine**: PHP 7.4 to PHP 8.2+ Compatibility
*   **Database Interface**: Native `mysqli` API (Strictly No PDO)
*   **UI Philosophy**: Strict Mobile-First User-Agent blocker (Adaptive full-screen dark cockpit)
*   **Monetization Structures**: Pricing tier levels gating (Free, Pro, VIP) + Dynamic Google Ad banner positions
*   **Ledger Security**: Double-Entry ledger system enforcing audited balances (no atomic increments)

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
