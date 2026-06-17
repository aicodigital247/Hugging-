import React, { useState, useEffect } from 'react';
import {
  TrendingUp,
  Coins,
  Radio,
  User,
  Settings,
  Shield,
  FileCode,
  Activity,
  CheckCircle,
  ChevronRight,
  TrendingDown,
  Info,
  Lock,
  Search,
  Bell,
  RefreshCw,
  Eye,
  Plus,
  Globe,
  Wallet,
  Send,
  Share2,
  Sliders,
  Play,
  Check,
  Award,
  Zap,
  ArrowRightLeft,
  X,
  CreditCard,
  Percent,
  ListFilter,
  EyeOff,
  LayoutGrid,
  Laptop
} from 'lucide-react';
import { PHP_SOURCES } from './php_sources';
import { SimulatorUser, Ledgers, Signals, Bulletins, AdCampaigns, PriceAlert, InvestmentVault, SseEvent, Drawing } from './types';

// Modular Component Imports
import TradingViewChart from './components/TradingViewChart';
import PaystackSim from './components/PaystackSim';
import YieldVault from './components/YieldVault';

// Multilingual Cockpit Translations Dictionaries
const TRANSLATIONS = {
  en: {
    title: "TRADENEXA.AI COCKPIT",
    desc: "PHP 7.4+ Backend & MySQLi Architecture",
    markets: "Markets",
    charts: "Charts",
    signals: "AI Signals",
    ledger: "Ledger",
    profile: "Profile/Rewards",
    admin: "Admin Console",
    tickerHeader: "Hot Contracts Tickers",
    signalHeader: "Proprietary AI Signal Feed",
    claimHeader: "Seeding Free Test Credits",
    activePlan: "Membership Level",
    upgrade: "Upgrade",
    deposit: "Deposit",
    investmentSystem: "Smart Yield Vault (Invest to Trade)",
    walletConnect: "Web3 Wallet Portal",
    alerts: "Price Alarms",
    onboarding: "Onboarding Tour",
    telemetry: "Telemetry overlays",
    auditBook: "Audit Book Ledger",
    bybitMkt: "Bybit Linear Market Feed",
    dashboard: "Dashboard",
    developerCockpit: "Developer Cockpit",
    trade: "Trade",
    wallet: "Wallet",
    history: "Histories Log",
    performanceMode: "Performance Low Mode",
    performanceModeDesc: "Reduce ticker latency & freeze animations to save resources on older devices."
  },
  es: {
    title: "CABINA TRADENEXA.AI",
    desc: "Soporte de Backend PHP 7.4+ y MySQLi",
    markets: "Mercados",
    charts: "Gráficos",
    signals: "Señales IA",
    ledger: "Libro Mayor",
    profile: "Perfil/Premios",
    admin: "Consola Admin",
    tickerHeader: "Marcadores de Contrato",
    signalHeader: "Feed de Señales Propietarias de IA",
    claimHeader: "Sembrar Créditos Gratuitos",
    activePlan: "Nivel de Membresía",
    upgrade: "Mejorar",
    deposit: "Depositar",
    investmentSystem: "Bóveda de Rendimiento Inteligente",
    walletConnect: "Portal de Billetera Web3",
    alerts: "Alarmas de Precio",
    onboarding: "Tour de Bienvenida",
    telemetry: "Superposiciones de telemetría",
    auditBook: "Libro Contable Auditado",
    bybitMkt: "Feed de Mercado Bybit",
    dashboard: "Panel",
    developerCockpit: "Cabina de Códigos",
    trade: "Operar",
    wallet: "Billetera",
    history: "Historiales",
    performanceMode: "Modo de Rendimiento Bajo",
    performanceModeDesc: "Reduce la latencia de los tickers y congela animaciones en dispositivos antiguos."
  },
  zh: {
    title: "TRADENEXA.AI 智能舱",
    desc: "PHP 7.4+ 后端和 MySQLi 极速架构",
    markets: "主流合约",
    charts: "专业 K 线",
    signals: "AI 信号流",
    ledger: "精算账本",
    profile: "尊客服务与推介",
    admin: "管理后台",
    tickerHeader: "实时合约行情流",
    signalHeader: "AI 独家自动量化计算流",
    claimHeader: "充值免费沙盒测试资金",
    activePlan: "当前特权账户级别",
    upgrade: "立即升级特权",
    deposit: "一键入账",
    investmentSystem: "高频智能跟单理财 (一键托管)",
    walletConnect: "Web3 专业冷热钱包连接",
    alerts: "价格阈值秒级推送提醒",
    onboarding: "新手指引通关大礼包",
    telemetry: "高阶量化指标图层",
    auditBook: "哈希级双向资金对账账本",
    bybitMkt: "Bybit 永续合约官方行情源",
    dashboard: "账户首页",
    developerCockpit: "后台源码解析",
    trade: "合约操作",
    wallet: "资金划转",
    history: "多维对账单",
    performanceMode: "极限省电低配模式",
    performanceModeDesc: "减缓行情更新速度并冻结动画，以降低低端安卓手机的发热和卡顿。"
  }
};

export default function App() {
  // Multilingual and responsive layout states
  const [lang, setLang] = useState<'en' | 'es' | 'zh'>('en');
  const [splitView, setSplitView] = useState<boolean>(true); // Split view with phone simulator vs true widescreen mode!
  const [activeTab, setActiveTab] = useState<'dashboard' | 'markets' | 'trade' | 'wallet' | 'history' | 'settings'>('dashboard');

  // SVG Chart interactive drawing annotations states
  const [selectedTool, setSelectedTool] = useState<'none' | 'line' | 'channel'>('none');
  const [selectedDrawingColor, setSelectedDrawingColor] = useState<string>('#00FFA3');
  const [drawings, setDrawings] = useState<Drawing[]>([]);
  const [channelHeight, setChannelHeight] = useState<number>(-24); // default parallel channel spacing

  // Simulator State Configurations
  const [user, setUser] = useState<SimulatorUser>({
    id: 1,
    email: 'admin@saas.com',
    plan: 'free',
    status: 'active',
    balance: 852.00,
    createdAt: '2026-06-16'
  });

  const [selectedSymbol, setSelectedSymbol] = useState<'BTCUSDT' | 'ETHUSDT' | 'SOLUSDT' | 'ADAUSDT'>('BTCUSDT');
  const [timeframe, setTimeframe] = useState<'1m' | '5m' | '15m' | '1h' | '1d'>('15m');
  const [isChangingTimeframe, setIsChangingTimeframe] = useState<boolean>(false);
  const [indicatorsEnabled, setIndicatorsEnabled] = useState<boolean>(true);
  const [performanceMode, setPerformanceMode] = useState<boolean>(false);

  // Dynamic Open Position with simulated PnL
  const [openPositions, setOpenPositions] = useState<any[]>([
    { id: 1, symbol: 'BTCUSDT', type: 'LONG', size: 0.15, entryPrice: 68150.00, lev: 20, pnl: 45.50 }
  ]);

  // Staking Investment pools state
  const [investedPools, setInvestedPools] = useState<InvestmentVault[]>([
    { id: 1, amount: 200, token: 'USDT', apy: 15.5, interestAccrued: 1.45, daysRemaining: 30, date: '2026-06-16', status: 'active' }
  ]);

  const [ledgers, setLedgers] = useState<Ledgers[]>([
    { id: 1, type: 'credit', amount: 852.00, reason: 'Seeded Master Ledger Balance Injection', balanceAfter: 852.00, timestamp: '2026-06-16 12:00:00' }
  ]);

  const [bulletins, setBulletins] = useState<Bulletins[]>([
    { id: 1, title: 'EMA9 Crossover Signal Alert', content: 'Crossover spike on 1-hour candle EMA crossover trend. Volume remains stable.', type: 'signal', createdAt: '2026-06-16 14:15:00' },
    { id: 2, title: 'Dynamic API Gateway Upgrade', content: 'Switched back testing lines to secure Bybit linear API perpetual REST schemas.', type: 'broadcast', createdAt: '2026-06-16 11:30:00' }
  ]);

  const [campaigns, setCampaigns] = useState<AdCampaigns[]>([
    { id: 1, placement: 'market', title: 'Bybit Premium Cashback: Receive 20% on Trading Fees', imageUrl: 'https://images.unsplash.com/photo-1621761191319-c6fb62004040?auto=format&fit=crop&w=600&q=80', linkUrl: '#', active: true },
    { id: 2, placement: 'in_feed', title: 'Audit Report: 100% Secure Cryptographic Ledger Verified', imageUrl: 'https://images.unsplash.com/photo-1639762681485-074b7f938ba0?auto=format&fit=crop&w=600&q=80', linkUrl: '#', active: true }
  ]);

  // Flash Notifications UI
  const [flash, setFlash] = useState<{ type: 'success' | 'error'; message: string } | null>(null);

  // Dynamic Ticker Prices
  const [tickerPrices, setTickerPrices] = useState({
    BTCUSDT: { price: 68425.40, change: 2.15, vol: 8145.2 },
    ETHUSDT: { price: 3512.80, change: -1.04, vol: 924.5 },
    SOLUSDT: { price: 146.55, change: 4.88, vol: 2450.8 },
    ADAUSDT: { price: 0.442, change: -0.12, vol: 152.0 }
  });

  // Hot tickers search state
  const [searchQuery, setSearchQuery] = useState<string>('');

  // Web3 Connection states
  const [connectedWallet, setConnectedWallet] = useState<string | null>(null);
  const [tonBalance, setTonBalance] = useState<number>(0.0);

  // Price Alarms
  const [priceAlerts, setPriceAlerts] = useState<PriceAlert[]>([
    { id: 1, symbol: 'BTCUSDT', targetPrice: 68500, direction: 'above', active: true },
    { id: 2, symbol: 'SOLUSDT', targetPrice: 142, direction: 'below', active: true }
  ]);
  const [triggeredAlerts, setTriggeredAlerts] = useState<string[]>([]);

  // Step-by-step Onboarding flow
  const [showOnboarding, setShowOnboarding] = useState<boolean>(true);
  const [onboardingStep, setOnboardingStep] = useState<number>(1);

  // Admin Module Control States
  const [signalSensitivity, setSignalSensitivity] = useState<'LOW' | 'MEDIUM' | 'HIGH'>('MEDIUM');
  const [bybitApiEndpoint, setBybitApiEndpoint] = useState<string>('https://api.bybit.com/v5/market');
  const [bybitApiKey, setBybitApiKey] = useState<string>('db_admin_bybit_read_key');
  const [bybitApiSecret, setBybitApiSecret] = useState<string>('db_admin_bybit_secret_secure_key');
  const [bybitApiConnectStatus, setBybitApiConnectStatus] = useState<'CONNECTED' | 'DISCONNECTED' | 'TESTING'>('CONNECTED');
  const [proTierPrice, setProTierPrice] = useState<number>(29.99);
  const [vipTierPrice, setVipTierPrice] = useState<number>(79.99);
  const [maintenanceAlertActive, setMaintenanceAlertActive] = useState<boolean>(true);
  const [maintenanceAlertMsg, setMaintenanceAlertMsg] = useState<string>('Scheduled system-wide backup in progress. Rest assured, your ledger calculations are offline immutable!');

  // Server-Sent Events interactive simulator log
  const [sseEventsLog, setSseEventsLog] = useState<SseEvent[]>([
    { id: 1, event: 'SSE_AI_SIGNAL', title: 'New Buy Threshold', message: 'BTCUSDT crossed EMA Crossover on 15m timeframe.', time: '16:05:12' },
    { id: 2, event: 'SSE_TG_WEBHOOK', title: 'Telegram Command Triggered', message: 'User index @tg_trader requested /signals status.', time: '16:11:04' }
  ]);

  // Code Explorer state
  const [selectedFileIndex, setSelectedFileIndex] = useState<number>(0);
  const [isCopied, setIsCopied] = useState<boolean>(false);

  // Pagination states
  const [ledgerPage, setLedgerPage] = useState<number>(1);
  const ledgerLimit = 3;

  // Notification lists state
  const [showNotifications, setShowNotifications] = useState<boolean>(false);

  // Translations fetch shortener
  const t = (key: keyof typeof TRANSLATIONS['en']): string => {
    return TRANSLATIONS[lang][key] || TRANSLATIONS['en'][key] || String(key);
  };

  // Triggering smooth timeframe changes transitions
  const handleTimeframeChange = (tf: typeof timeframe) => {
    setIsChangingTimeframe(true);
    setTimeframe(tf);
    setTimeout(() => setIsChangingTimeframe(false), 240);
  };

  // Simulated live prices updating intervals + Alerts Trigger Check
  useEffect(() => {
    // If performanceMode is true, we update price events very slowly to save resource
    const intervalTicks = performanceMode ? 10000 : 3500;
    const timer = setInterval(() => {
      setTickerPrices(prev => {
        const updateVal = (val: number, multiplier = 0.0006) => {
          const delta = val * multiplier * (Math.random() - 0.49);
          return Number((val + delta).toFixed(3));
        };
        const next = {
          BTCUSDT: { price: updateVal(prev.BTCUSDT.price), change: Number((prev.BTCUSDT.change + (Math.random() - 0.5) * 0.12).toFixed(2)), vol: prev.BTCUSDT.vol },
          ETHUSDT: { price: updateVal(prev.ETHUSDT.price), change: Number((prev.ETHUSDT.change + (Math.random() - 0.5) * 0.08).toFixed(2)), vol: prev.ETHUSDT.vol },
          SOLUSDT: { price: updateVal(prev.SOLUSDT.price), change: Number((prev.SOLUSDT.change + (Math.random() - 0.5) * 0.18).toFixed(2)), vol: prev.SOLUSDT.vol },
          ADAUSDT: { price: updateVal(prev.ADAUSDT.price), change: Number((prev.ADAUSDT.change + (Math.random() - 0.5) * 0.05).toFixed(3)), vol: prev.ADAUSDT.vol }
        };

        // Live updating Position PnL
        setOpenPositions(positions => positions.map(pos => {
          const currentPrice = next[pos.symbol as keyof typeof next]?.price;
          const currentChange = pos.type === 'LONG' ? (currentPrice - pos.entryPrice) : (pos.entryPrice - currentPrice);
          const posPnl = currentChange * pos.size * pos.lev;
          return { ...pos, pnl: Number(posPnl.toFixed(2)) };
        }));

        // Run price alert criteria checks
        priceAlerts.forEach(alert => {
          if (!alert.active) return;
          const currentSym = alert.symbol as keyof typeof next;
          const currentPrice = next[currentSym]?.price;
          if (currentPrice) {
            let hit = false;
            if (alert.direction === 'above' && currentPrice >= alert.targetPrice) hit = true;
            if (alert.direction === 'below' && currentPrice <= alert.targetPrice) hit = true;

            if (hit) {
              alert.active = false; // deactivate
              const timestamp = new Date().toLocaleTimeString();
              const alertMsg = `🎯 Price Alert Triggered! ${alert.symbol} hit $${currentPrice.toLocaleString()} (Target: ${alert.targetPrice})`;
              setTriggeredAlerts(prevLogs => [alertMsg, ...prevLogs]);
              triggerFlash('success', alertMsg);
              
              // Push to simulated Server Sent Events logs
              setSseEventsLog(prevSse => [
                {
                  id: prevSse.length + 1,
                  event: 'SSE_MARKET_ALERT',
                  title: 'Price Threshold Crossed',
                  message: `${alert.symbol} touched ${alert.targetPrice} on server index.`,
                  time: timestamp
                },
                ...prevSse
              ]);
            }
          }
        });

        return next;
      });

      // Compound interest accruals ticks
      setInvestedPools(prevPools => prevPools.map(pool => {
        if (pool.status !== 'active') return pool;
        // Accrues microscopic compound ticks
        const accrualSpeed = performanceMode ? 0.00005 : 0.00015;
        const accruedInterest = pool.interestAccrued + (pool.amount * (pool.apy / 36500) * accrualSpeed);
        return { ...pool, interestAccrued: accruedInterest };
      }));

    }, intervalTicks);

    return () => clearInterval(timer);
  }, [priceAlerts, performanceMode]);

  const triggerFlash = (type: 'success' | 'error', message: string) => {
    setFlash({ type, message });
    setTimeout(() => setFlash(null), 3000);
  };

  // 1. Double-Entry ledger core actions
  const debitLedger = (cost: number, reason: string): boolean => {
    if (user.balance < cost) {
      triggerFlash('error', 'Debit rejected. Insufficient ledger reserves.');
      return false;
    }
    const newBal = Number((user.balance - cost).toFixed(2));
    setUser(prev => ({ ...prev, balance: newBal }));
    
    const newLg: Ledgers = {
      id: ledgers.length + 1,
      type: 'debit',
      amount: cost,
      reason,
      balanceAfter: newBal,
      timestamp: new Date().toISOString().replace('T', ' ').substring(0, 19)
    };
    setLedgers(prev => [newLg, ...prev]);
    return true;
  };

  const creditLedger = (credit: number, reason: string) => {
    const newBal = Number((user.balance + credit).toFixed(2));
    setUser(prev => ({ ...prev, balance: newBal }));
    
    const newLg: Ledgers = {
      id: ledgers.length + 1,
      type: 'credit',
      amount: credit,
      reason,
      balanceAfter: newBal,
      timestamp: new Date().toISOString().replace('T', ' ').substring(0, 19)
    };
    setLedgers(prev => [newLg, ...prev]);
  };

  // 2. Buy Pro / VIP memberships
  const handleUpgradeSubscription = (target: 'pro' | 'vip') => {
    if (user.plan === target) {
      triggerFlash('error', 'You already own this subscription plan license.');
      return;
    }
    const cost = target === 'pro' ? proTierPrice : vipTierPrice;
    const success = debitLedger(cost, `Subscription upgraded to ${target.toUpperCase()} Level Package`);
    if (success) {
      setUser(prev => ({ ...prev, plan: target }));
      triggerFlash('success', `Congratulations! Active subscription switched to ${target.toUpperCase()} tier.`);
    }
  };

  // 3. Staking Pools Stakemanager
  const handleStake = (amount: number, token: 'USDT' | 'TON', apy: number, days: number) => {
    const success = debitLedger(amount, `Locked Staking: locked ${amount} ${token} for ${days} days`);
    if (success) {
      const newPool: InvestmentVault = {
        id: investedPools.length + 1,
        amount,
        token,
        apy,
        interestAccrued: 0,
        daysRemaining: days,
        date: new Date().toISOString().substring(0, 10),
        status: 'active'
      };
      setInvestedPools(prev => [newPool, ...prev]);
    }
  };

  const handleClaimInterest = (poolId: number) => {
    const pool = investedPools.find(p => p.id === poolId);
    if (!pool || pool.interestAccrued <= 0) return;

    const gained = Number(pool.interestAccrued.toFixed(6));
    
    // update interest accrued back to 0
    setInvestedPools(prev => prev.map(p => p.id === poolId ? { ...p, interestAccrued: 0 } : p));
    
    // Credit main wallet equivalent
    const conversion = pool.token === 'TON' ? gained * 7.50 : gained;
    creditLedger(conversion, `Claimed yield accrued from ${pool.amount} ${pool.token} Staking Pool`);
    triggerFlash('success', `Claimed +${gained} ${pool.token}! Account credited +$${conversion.toFixed(2)} USDT.`);
  };

  // 4. Simulated deposit funding
  const handleClaimCredits = (amount: number) => {
    creditLedger(amount, 'Paystack checkout fund settlement');
    
    // Push simulated Notification
    setSseEventsLog(prev => [
      {
        id: prev.length + 1,
        event: 'SSE_PAYMENT_WEBHOOK',
        title: 'Paystack Hook Cleared',
        message: `Processed Deposit of $${amount.toFixed(2)} automatically via secure webhooks.`,
        time: new Date().toLocaleTimeString()
      },
      ...prev
    ]);
  };

  // 5. Place Simulated Position trade
  const handlePlaceTrade = (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault();
    const data = new FormData(e.currentTarget);
    const side = String(data.get('trade_side') || 'BUY') as 'BUY' | 'SELL';
    const margin = Number(data.get('trade_margin') || 50);
    const lev = Number(data.get('trade_lev') || 20);

    if (margin <= 0 || margin > user.balance) {
      triggerFlash('error', 'Trade margin size exceeds ledger assets!');
      return;
    }

    const price = tickerPrices[selectedSymbol]?.price || 68000;
    const contractSize = Number(((margin * lev) / price).toFixed(4));

    const success = debitLedger(margin, `Open ${side} Margin position on ${selectedSymbol} index`);
    if (success) {
      const newPos = {
        id: Date.now(),
        symbol: selectedSymbol,
        type: side === 'BUY' ? 'LONG' : 'SHORT',
        size: contractSize,
        entryPrice: price,
        lev: lev,
        pnl: 0
      };
      setOpenPositions(prev => [newPos, ...prev]);
      triggerFlash('success', `Placed order! Opened ${lev}x ${side === 'BUY' ? 'LONG' : 'SHORT'} position.`);
    }
  };

  const handleClosePosition = (posId: number) => {
    const pos = openPositions.find(p => p.id === posId);
    if (!pos) return;

    setOpenPositions(prev => prev.filter(p => p.id !== posId));
    const profit = pos.pnl;
    
    // return principal margin + PnL
    const settlement = Number((pos.size * pos.entryPrice / pos.lev + profit).toFixed(2));
    creditLedger(settlement, `Settled ${pos.symbol} position. Closed at profit/loss tally.`);
    triggerFlash('success', `Closed position! Net Settlement: $${settlement.toFixed(2)} USDT (PnL: $${profit.toFixed(2)})`);
  };

  // Copy code helper
  const handleCopyCode = (txt: string) => {
    navigator.clipboard.writeText(txt);
    setIsCopied(true);
    setTimeout(() => setIsCopied(false), 2000);
  };

  // Candlesticks generator helper
  const getCandlesticks = () => {
    const basePrices = { BTCUSDT: 68425, ETHUSDT: 3512, SOLUSDT: 146, ADAUSDT: 0.44 };
    const center = basePrices[selectedSymbol];
    const ticksCount = 42;
    const items = [];
    let rsiVal = 50;

    for (let i = 0; i < ticksCount; i++) {
      const idxOffset = i - ticksCount / 2;
      const wave = Math.sin(i * 0.45) * (center * 0.015) + Math.cos(i * 0.2) * (center * 0.008);
      const close = center + wave + (idxOffset * (center * 0.0005));
      const open = close - (Math.random() - 0.5) * (center * 0.008);
      const high = Math.max(open, close) + Math.random() * (center * 0.004);
      const low = Math.min(open, close) - Math.random() * (center * 0.004);
      const vol = Math.floor(Math.random() * 800) + 120;

      const ema9 = center + wave * 0.9 + (idxOffset * (center * 0.0004));
      const ema21 = center + wave * 0.75 + (idxOffset * (center * 0.0003));
      rsiVal = Number((50 + Math.sin(i * 0.5) * 22 + (Math.random() - 0.5) * 8).toFixed(1));

      items.push({ open, close, high, low, vol, ema9, ema21, rsi: rsiVal });
    }
    return items;
  };

  const candleData = getCandlesticks();
  const currentPhpSource = PHP_SOURCES[selectedFileIndex];

  // Search filtered perpetual tickers
  const getFilteredTickers = () => {
    const keys = Object.keys(tickerPrices) as string[];
    return keys.filter(sym => sym.toLowerCase().includes(searchQuery.toLowerCase()));
  };

  return (
    <div className="min-h-screen bg-[#07080b] text-zinc-200 flex flex-col antialiased selection:bg-[#00FFA3]/20">
      
      {/* GLOBAL BANNER NOTIFIERS */}
      {flash && (
        <div className={`fixed top-4 right-4 z-50 p-4 rounded-xl text-xs font-mono font-bold shadow-2xl animate-fade-in border flex items-center gap-2 max-w-sm
          ${flash.type === 'success' ? 'bg-[#0f1115] border-[#00FFA3]/40 text-[#00FFA3]' : 'bg-[#0f1115] border-[#FF4D4D]/40 text-[#FF4D4D]'}`}>
          <span className="w-1.5 h-1.5 rounded-full animate-ping bg-current" />
          {flash.message}
        </div>
      )}

      {/* TOP HEADER NAVIGATION BAR */}
      <header className="bg-[#0c0d10]/95 backdrop-blur-md border-b border-[#1f1f1f] h-16 px-4 md:px-6 flex justify-between items-center sticky top-0 z-40">
        <div className="flex items-center gap-3">
          <span className="w-8 h-8 rounded-lg bg-gradient-to-tr from-[#00FFA3]/20 to-[#7047EB]/20 border border-[#00FFA3]/30 flex items-center justify-center font-black text-[#00FFA3] text-sm shadow-md font-display">
            TN
          </span>
          <div>
            <h1 className="text-xs md:text-sm font-black tracking-widest text-white uppercase leading-none font-display">
              {t('title')}
            </h1>
            <p className="text-[8.5px] text-gray-500 font-mono mt-0.5 leading-none hidden sm:block">{t('desc')}</p>
          </div>
        </div>

        {/* Global persistent controllers actions */}
        <div className="flex items-center gap-2.5">
          {/* Portfolio quick balance review */}
          <div className="bg-[#14151a] border border-[#222] px-2.5 py-1 rounded-xl text-[9px] font-mono flex items-center gap-2.5 hidden sm:flex">
            <div>
              <span className="text-gray-500 uppercase tracking-tight block text-[7.5px] font-black">Main Wallet</span>
              <p className="text-[#00FFA3] font-extrabold leading-none mt-0.5">${user.balance.toFixed(2)} USDT</p>
            </div>
            <div className="border-l border-zinc-800 h-5" />
            <div>
              <span className="text-gray-500 uppercase tracking-tight block text-[7.5px] font-black">TON Balance</span>
              <p className="text-[#F0B90B] font-extrabold leading-none mt-0.5">{tonBalance.toFixed(2)} TON</p>
            </div>
          </div>

          {/* Persistent Language Switch Selector widget */}
          <div className="flex items-center bg-[#14151a] border border-[#222] p-1 rounded-xl gap-1">
            <Globe size={11} className="text-gray-500 ml-1" />
            {(['en', 'es', 'zh'] as const).map((l) => (
              <button
                key={l}
                onClick={() => {
                  setLang(l);
                  triggerFlash('success', `Language changed to ${l.toUpperCase()}`);
                }}
                className={`px-1.5 py-0.5 text-[8px] font-mono font-black rounded-lg uppercase cursor-pointer transition
                  ${lang === l ? 'bg-[#00FFA3]/15 text-[#00FFA3] border border-[#00FFA3]/25' : 'text-gray-500 hover:text-gray-300'}`}
              >
                {l}
              </button>
            ))}
          </div>

          {/* Workspace split controller Layout button */}
          <button
            onClick={() => {
              setSplitView(!splitView);
              triggerFlash('success', !splitView ? 'Developer Split Cockpit Layout activated' : 'Full-Screen Professional Platform activated');
            }}
            className="flex items-center gap-1 bg-[#14151a] hover:bg-zinc-800 border border-[#222] px-2.5 py-1 text-[9px] font-mono font-black rounded-xl transition cursor-pointer select-none text-gray-300 hover:text-white"
          >
            <Laptop size={11} className="text-[#00FFA3]" />
            <span className="hidden md:inline">{splitView ? "Widescreen Mode" : "Developer Split"}</span>
          </button>

          {/* Notification log alerts bell dropdown */}
          <div className="relative">
            <button
              onClick={() => setShowNotifications(!showNotifications)}
              className="w-8 h-8 rounded-xl bg-[#14151a] border border-[#222] flex items-center justify-center text-gray-400 hover:text-white transition cursor-pointer relative"
            >
              <Bell size={13} />
              <span className="absolute top-1 right-1 w-2 h-2 bg-[#FF4D4D] rounded-full" />
            </button>
            {showNotifications && (
              <div className="absolute right-0 mt-2.5 w-64 bg-[#0c0d10] border border-[#222] rounded-2xl shadow-2xl p-3 z-50 font-mono text-[9px] text-zinc-400">
                <div className="border-b border-[#222] pb-2 mb-2 flex justify-between items-center text-white font-extrabold text-[10px]">
                  <span>NOTIFICATIONS CENTRE</span>
                  <X size={10} className="cursor-pointer" onClick={() => setShowNotifications(false)} />
                </div>
                <div className="space-y-2 max-h-48 overflow-y-auto no-scrollbar">
                  {sseEventsLog.slice(0, 5).map(e => (
                    <div key={e.id} className="border-b border-[#1c1d24]/40 pb-2">
                      <p className="text-[#00FFA3] font-bold text-[8.5px] uppercase">{e.event}</p>
                      <p className="text-gray-200 font-bold mt-0.5">{e.title}</p>
                      <p className="text-[8.2px] text-gray-500 leading-snug">{e.message}</p>
                    </div>
                  ))}
                </div>
              </div>
            )}
          </div>
        </div>
      </header>

      {/* DUAL WORKSPACE LAYOUT WRAPPER */}
      <div className="flex-1 flex flex-col lg:flex-row overflow-hidden max-h-[calc(100vh-64px)]">
        
        {/* SIDE BAR TRADING MENU PANEL */}
        <aside className="w-full lg:w-48 bg-[#0c0d10] border-b lg:border-b-0 lg:border-r border-[#1f1f1f] flex lg:flex-col justify-between p-2 z-30 select-none overflow-x-auto no-scrollbar">
          <div className="flex lg:flex-col w-full gap-1">
            {[
              { id: 'dashboard' as const, label: t('dashboard'), icon: <LayoutGrid size={14} /> },
              { id: 'markets' as const, label: t('markets'), icon: <TrendingUp size={14} /> },
              { id: 'trade' as const, label: t('trade'), icon: <Activity size={14} /> },
              { id: 'wallet' as const, label: t('wallet'), icon: <Wallet size={14} /> },
              { id: 'history' as const, label: t('history'), icon: <Coins size={14} /> },
              { id: 'settings' as const, label: t('admin'), icon: <Shield size={14} /> }
            ].map((tab) => (
              <button
                key={tab.id}
                onClick={() => {
                  setActiveTab(tab.id);
                  setLedgerPage(1);
                }}
                className={`flex items-center gap-2.5 px-3.5 py-2.5 rounded-xl text-[10px] uppercase font-black transition cursor-pointer select-none text-left whitespace-nowrap lg:w-full
                  ${activeTab === tab.id ? 'text-black bg-[#00FFA3] font-black shadow-md shadow-[#00FFA3]/10' : 'text-gray-500 hover:text-gray-300'}`}
              >
                {tab.icon}
                <span>{tab.label}</span>
              </button>
            ))}
          </div>

          <div className="hidden lg:block p-3.5 bg-[#14151a]/40 border border-[#222]/60 rounded-xl">
            <span className="text-[7.5px] text-gray-500 font-mono block uppercase">Auth Web3 Session</span>
            {connectedWallet ? (
              <p className="text-[#00FFA3] text-[9px] font-bold mt-1 max-w-[120px] truncate">{connectedWallet}</p>
            ) : (
              <p className="text-gray-600 text-[8.5px] mt-1 font-mono">No Wallet Connected</p>
            )}
          </div>
        </aside>

        {/* PRIMARY COCKPIT PANELS MODULES */}
        <main className={`flex-1 overflow-y-auto p-4 md:p-6 space-y-6 ${splitView ? 'lg:max-w-[55%]' : ''}`}>
          
          {/* System maintenance warning banner alert */}
          {maintenanceAlertActive && maintenanceAlertMsg && (
            <div className="bg-amber-500/5 border border-amber-500/25 rounded-2xl p-4 flex items-start gap-3 relative overflow-hidden my-1 animate-fade-in">
              <span className="text-amber-400 text-sm mt-0.5">⚠️</span>
              <div className="flex-1">
                <p className="font-mono font-extrabold uppercase text-[8px] tracking-wider text-amber-500">System maintenance bulletin warning</p>
                <p className="text-gray-300 text-[9.5px] mt-1 leading-relaxed capitalize">{maintenanceAlertMsg}</p>
              </div>
            </div>
          )}

          {/* TAB 1: PORTFOLIO DASHBOARD PAGE */}
          {activeTab === 'dashboard' && (
            <div className="space-y-6 animate-fade-in">
              <div className="bg-gradient-to-br from-[#14151a] to-[#0c0d10] border border-[#222] rounded-2xl p-5 relative overflow-hidden">
                <div className="absolute top-0 right-0 w-32 h-32 bg-[#00FFA3]/5 rounded-full blur-3xl pointer-events-none" />
                <span className="text-[8px] tracking-widest font-mono text-[#00FFA3] font-extrabold uppercase block mb-1">
                  MEMBER OVERVIEW COCKPIT
                </span>
                <h2 className="text-lg font-black text-white">{user.email}</h2>
                <div className="mt-4 grid grid-cols-2 gap-4 pb-3 border-b border-zinc-800/65 text-xs">
                  <div>
                    <span className="text-[8px] text-gray-500 uppercase font-bold tracking-tight block">LEDGER SECURED USDT</span>
                    <h3 className="font-mono text-base font-black text-[#00FFA3] mt-0.5">${user.balance.toFixed(2)}</h3>
                  </div>
                  <div>
                    <span className="text-[8px] text-gray-500 uppercase font-bold tracking-tight block">COLD STORAGE TON</span>
                    <h3 className="font-mono text-base font-black text-[#F0B90B] mt-0.5">{tonBalance.toFixed(2)} TON</h3>
                  </div>
                </div>

                <div className="pt-3.5 flex flex-wrap items-center justify-between gap-3 text-[9px] font-mono leading-none">
                  <div className="flex items-center gap-1.5">
                    <span className="w-1.5 h-1.5 rounded-full bg-[#00FFA3] animate-ping" />
                    <span className="text-gray-400 uppercase">License Tier Status: <strong className="text-[#00FFA3]">{user.plan} Access</strong></span>
                  </div>
                  <button onClick={() => setActiveTab('settings')} className="text-[#00FFA3] font-bold uppercase hover:underline cursor-pointer">
                    Upgrade License Plan ➜
                  </button>
                </div>
              </div>

              {/* Compounding Yield Vault component */}
              <YieldVault
                onStake={handleStake}
                vaults={investedPools}
                onClaimInterest={handleClaimInterest}
                triggerFlash={triggerFlash}
                lang={lang}
                balance={user.balance}
                tonBalance={tonBalance}
              />

              {/* Refer and earn 7 days free promotion */}
              <div className="bg-gradient-to-r from-purple-950/20 to-indigo-950/20 border border-purple-900/20 rounded-2xl p-5 relative overflow-hidden flex flex-col md:flex-row items-center gap-4">
                <Award className="text-[#00FFA3] flex-shrink-0" size={32} />
                <div>
                  <h4 className="text-white text-xs font-black uppercase tracking-wider">Advanced Partner Referral Reward</h4>
                  <p className="text-[9.5px] text-gray-400 mt-1 leading-relaxed">
                    Extend your strategic Pro license by <strong className="text-[#00FFA3]">7 days completely free</strong> for every referee signup! Share Telegram command variables instantly.
                  </p>
                  <div className="mt-3 bg-black/60 border border-purple-900/30 px-3 py-1.5 rounded-xl font-mono text-[9px] text-purple-300 select-all max-w-sm truncate">
                    https://tradenexa.com/register?ref={user.email}
                  </div>
                </div>
              </div>
            </div>
          )}

          {/* TAB 2: MARKETS TICKERS PAGE */}
          {activeTab === 'markets' && (
            <div className="space-y-6 animate-fade-in">
              <div className="flex items-center gap-2 bg-[#14151a] border border-[#222] px-3.5 h-11 rounded-xl">
                <Search size={14} className="text-gray-500" />
                <input
                  type="text"
                  placeholder="Search perpetuals markets... (e.g. BTC, ETH)"
                  value={searchQuery}
                  onChange={(e) => setSearchQuery(e.target.value)}
                  className="w-full bg-transparent border-none focus:outline-none text-xs text-white placeholder-gray-500 font-mono uppercase"
                />
              </div>

              {/* Tickers list */}
              <div className="grid grid-cols-1 md:grid-cols-2 gap-3">
                {getFilteredTickers().map(sym => {
                  const tick = tickerPrices[sym as keyof typeof tickerPrices];
                  const isUp = tick.change >= 0;
                  return (
                    <div
                      key={sym}
                      onClick={() => {
                        setSelectedSymbol(sym as any);
                        setActiveTab('trade');
                      }}
                      className="bg-[#14151a] hover:bg-[#1c1d24] border border-[#222] rounded-2xl p-4 cursor-pointer transition-all duration-200 flex justify-between items-center relative overflow-hidden"
                    >
                      <div className="flex items-center gap-2.5">
                        <span className={`w-2 h-2 rounded-full ${isUp ? 'bg-[#00FFA3] animate-pulse' : 'bg-[#FF4D4D]'}`} />
                        <div>
                          <h4 className="font-mono text-xs font-black text-white">{sym}</h4>
                          <span className="text-[8.5px] text-gray-500 font-mono">Bybit Perpetual</span>
                        </div>
                      </div>

                      {/* Sparkline graphics simulation */}
                      <div className="w-14 h-7 flex gap-0.5 items-end opacity-40">
                        {Array.from({ length: 6 }).map((_, i) => (
                          <span
                            key={i}
                            style={{ height: `${25 + Math.random() * 70}%` }}
                            className={`w-1 rounded-sm ${isUp ? 'bg-[#00FFA3]' : 'bg-[#FF4D4D]'}`}
                          />
                        ))}
                      </div>

                      <div className="text-right">
                        <p className="font-mono text-xs font-extrabold text-white">${tick.price.toLocaleString()}</p>
                        <span className={`font-mono text-[9px] font-black ${isUp ? 'text-[#00FFA3]' : 'text-[#FF4D4D]'}`}>
                          {isUp ? '+' : ''}{tick.change}%
                        </span>
                      </div>
                    </div>
                  );
                })}
              </div>

              {/* Set custom price alarms widget */}
              <div className="bg-[#14151a] border border-[#222] rounded-2xl p-5 space-y-4">
                <span className="text-[9px] font-extrabold text-[#F0B90B] uppercase tracking-widest font-mono flex items-center gap-1.5">
                  <span className="w-1.5 h-1.5 bg-[#F0B90B] rounded-full animate-ping" />
                  Define Price Alarm Gateways
                </span>
                <p className="text-[9.5px] text-gray-500 leading-relaxed font-sans mt-1">
                  Armed price alarms instantly push desktop notifications and trigger simulated server-sent data events on target matches.
                </p>
                
                <form onSubmit={(e) => {
                  e.preventDefault();
                  const data = new FormData(e.currentTarget);
                  const sym = String(data.get('alert_sym') || 'BTCUSDT');
                  const price = Number(data.get('alert_price') || 0);
                  const dir = String(data.get('alert_dir') || 'above') as 'above' | 'below';

                  if (price <= 0) {
                    triggerFlash('error', 'Enter a valid target price threshold.');
                    return;
                  }

                  const alert: PriceAlert = {
                    id: Date.now(),
                    symbol: sym,
                    targetPrice: price,
                    direction: dir,
                    active: true
                  };
                  setPriceAlerts(prev => [alert, ...prev]);
                  triggerFlash('success', `Armed Alert! Triggered when ${sym} goes ${dir} $${price}`);
                  e.currentTarget.reset();
                }} className="grid grid-cols-1 sm:grid-cols-4 gap-2 text-xs font-mono">
                  <select name="alert_sym" className="bg-[#0b0c10] border border-[#222] text-white rounded-xl px-2 py-2 text-[10px] focus:outline-none">
                    <option value="BTCUSDT">BTCUSDT</option>
                    <option value="ETHUSDT">ETHUSDT</option>
                    <option value="SOLUSDT">SOLUSDT</option>
                    <option value="ADAUSDT">ADAUSDT</option>
                  </select>

                  <select name="alert_dir" className="bg-[#0b0c10] border border-[#222] text-white rounded-xl px-2 py-2 text-[10px] focus:outline-none">
                    <option value="above">ABOVE (📈)</option>
                    <option value="below">BELOW (📉)</option>
                  </select>

                  <input
                    type="number"
                    name="alert_price"
                    step="any"
                    placeholder="Trigger level (68450)"
                    className="bg-[#0b0c10] border border-[#222] text-white rounded-xl px-3 py-2 text-[10px] focus:outline-none focus:border-[#00FFA3]"
                    required
                  />

                  <button type="submit" className="bg-[#F0B90B] hover:opacity-95 text-black font-black px-4 py-2 rounded-xl text-[9px] uppercase font-sans cursor-pointer select-none">
                    Arm Alert
                  </button>
                </form>

                {/* Armed price alarms lists */}
                {priceAlerts.length > 0 && (
                  <div className="border-t border-[#222] pt-3.5 space-y-2 font-mono text-[9px]">
                    <span className="text-gray-500 uppercase font-black text-[8px] tracking-widest">Currently Armed Alarms ({priceAlerts.filter(p => p.active).length})</span>
                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-2">
                      {priceAlerts.map(alert => (
                        <div key={alert.id} className="flex justify-between items-center bg-[#0b0c10]/40 border border-[#222] p-2.5 rounded-xl">
                          <span className={alert.active ? 'text-zinc-300 font-extrabold' : 'text-gray-600 line-through'}>
                            {alert.symbol} {alert.direction === 'above' ? '≥' : '≤'} ${alert.targetPrice}
                          </span>
                          <span className={`px-2 py-0.5 rounded text-[7.5px] font-black uppercase border
                            ${alert.active ? 'bg-amber-950/20 text-amber-500 border-amber-900/30' : 'bg-zinc-900/50 text-gray-600 border-zinc-800'}`}>
                            {alert.active ? 'Armed' : 'Triggered'}
                          </span>
                        </div>
                      ))}
                    </div>
                  </div>
                )}
              </div>
            </div>
          )}

          {/* TAB 3: TRADING INTERFACE PAGE */}
          {activeTab === 'trade' && (
            <div className="space-y-6 animate-fade-in">
              <TradingViewChart
                symbol={selectedSymbol}
                timeframe={timeframe}
                onTimeframeChange={handleTimeframeChange}
                plan={user.plan}
                indicatorsEnabled={indicatorsEnabled}
                setIndicatorsEnabled={setIndicatorsEnabled}
                drawings={drawings}
                setDrawings={setDrawings}
                selectedTool={selectedTool}
                setSelectedTool={setSelectedTool}
                selectedDrawingColor={selectedDrawingColor}
                setSelectedDrawingColor={setSelectedDrawingColor}
                channelHeight={channelHeight}
                setChannelHeight={setChannelHeight}
                triggerFlash={triggerFlash}
                candleData={candleData}
                isChangingTimeframe={isChangingTimeframe}
                performanceMode={performanceMode}
              />

              {/* Order Execution Form with orderbook depth summary */}
              <div className="grid grid-cols-1 md:grid-cols-2 gap-5 text-xs font-mono">
                {/* 1. Buy/Sell Execution Form */}
                <div className="bg-[#14151a] border border-[#222] rounded-2xl p-5">
                  <h4 className="text-white text-[10px] font-black uppercase tracking-wider mb-3.5 flex items-center gap-1.5 border-b border-[#222] pb-2">
                    <Sliders size={12} className="text-[#00FFA3]" /> Place Contract Order
                  </h4>
                  <form onSubmit={handlePlaceTrade} className="space-y-3.5">
                    <div>
                      <label className="block text-gray-500 text-[8px] font-bold uppercase mb-1">Contract Action</label>
                      <select name="trade_side" className="w-full bg-[#0b0c10] border border-[#222] text-white rounded-xl px-2.5 py-1.5 font-bold focus:outline-none">
                        <option value="BUY">BUY / LONG (📈)</option>
                        <option value="SELL">SELL / SHORT (📉)</option>
                      </select>
                    </div>

                    <div className="grid grid-cols-2 gap-2">
                      <div>
                        <label className="block text-gray-500 text-[8px] font-bold uppercase mb-1">Margin Allocation ($)</label>
                        <input
                          type="number"
                          name="trade_margin"
                          defaultValue="50"
                          min="1"
                          max="200"
                          className="w-full bg-[#0b0c10] border border-[#222] text-white rounded-xl px-2.5 py-1 text-xs focus:outline-none text-sans font-bold"
                          required
                        />
                      </div>
                      <div>
                        <label className="block text-gray-500 text-[8px] font-bold uppercase mb-1">Leverage multiplier</label>
                        <select name="trade_lev" className="w-full bg-[#0b0c10] border border-[#222] text-white rounded-xl px-2.5 py-1.5 focus:outline-none">
                          <option value="10">10x Leverage</option>
                          <option value="20" selected>20x Leverage</option>
                          <option value="50">50x Leverage</option>
                        </select>
                      </div>
                    </div>

                    <button
                      type="submit"
                      className="w-full bg-[#00FFA3] hover:opacity-95 text-black font-black py-2.5 rounded-xl uppercase transition select-none cursor-pointer text-center text-[10px] font-sans tracking-wider"
                    >
                      Process Transaction Trade
                    </button>
                  </form>
                </div>

                {/* 2. Live Orderbook Bid-Ask Depth summary */}
                <div className="bg-[#14151a] border border-[#222] rounded-2xl p-5">
                  <h4 className="text-white text-[10px] font-black uppercase tracking-wider mb-3 flex items-center gap-1.5 border-b border-[#222] pb-2">
                    <ListFilter size={12} className="text-[#00FFA3]" /> Bybit Live Orderbook
                  </h4>
                  <div className="space-y-1.5 text-[9px]">
                    <div className="flex justify-between items-center text-[#FF4D4D] font-extrabold">
                      <span>ASK: $68,480.50</span>
                      <span>Vol: 1.542 BTC</span>
                    </div>
                    <div className="flex justify-between items-center text-[#FF4D4D]/80">
                      <span>ASK: $68,465.10</span>
                      <span>Vol: 0.814 BTC</span>
                    </div>
                    <div className="border-t border-[#1f1f1f] py-1 text-center text-[10px] text-white font-extrabold animate-pulse">
                      Settle Index: ${tickerPrices[selectedSymbol]?.price.toFixed(2)}
                    </div>
                    <div className="flex justify-between items-center text-[#00FFA3]/80">
                      <span>BID: $68,410.00</span>
                      <span>Vol: 2.112 BTC</span>
                    </div>
                    <div className="flex justify-between items-center text-[#00FFA3] font-extrabold">
                      <span>BID: $68,395.20</span>
                      <span>Vol: 4.815 BTC</span>
                    </div>
                  </div>
                </div>
              </div>

              {/* Active positions long/short panels tracker */}
              {openPositions.length > 0 && (
                <div className="bg-[#14151a] border border-[#222] rounded-2xl p-5 space-y-3 font-mono text-xs">
                  <h4 className="text-white text-[10px] font-black uppercase tracking-wider border-b border-[#222] pb-2">
                    Active Open Positions ({openPositions.length})
                  </h4>
                  <div className="space-y-2.5">
                    {openPositions.map((pos) => {
                      const isProfit = pos.pnl >= 0;
                      return (
                        <div key={pos.id} className="bg-[#0b0c10] border border-[#222] p-3.5 rounded-xl flex flex-col sm:flex-row justify-between sm:items-center gap-3">
                          <div>
                            <div className="flex items-center gap-2">
                              <span className={`text-[8px] font-black uppercase border px-1.5 py-0.5 rounded
                                ${pos.type === 'LONG' ? 'bg-emerald-950/20 text-[#00FFA3] border-emerald-920' : 'bg-rose-950/20 text-[#FF4D4D] border-rose-910'}`}>
                                {pos.type} {pos.lev}x
                              </span>
                              <strong className="text-white font-sans text-xs">{pos.symbol}</strong>
                            </div>
                            <div className="grid grid-cols-2 gap-4 mt-2 text-[9px] text-gray-500">
                              <span>Entry Price: <strong className="text-gray-300 font-sans">${pos.entryPrice}</strong></span>
                              <span>Contract Size: <strong className="text-gray-300 font-sans">{pos.size} BTC</strong></span>
                            </div>
                          </div>

                          <div className="text-right flex items-center justify-between sm:justify-end gap-4 border-t sm:border-0 border-zinc-900 pt-2.5 sm:pt-0">
                            <div>
                              <span className="text-gray-500 text-[8px] block uppercase">Live profit / loss</span>
                              <p className={`font-black text-xs ${isProfit ? 'text-[#00FFA3]' : 'text-[#FF4D4D]'}`}>
                                {isProfit ? '+' : ''}${isProfit ? pos.pnl : Math.abs(pos.pnl)} USDT
                              </p>
                            </div>
                            <button
                              onClick={() => handleClosePosition(pos.id)}
                              className="bg-red-950/20 hover:bg-red-950/35 border border-red-900/30 hover:border-red-900 text-red-400 font-extrabold px-3 py-1.5 rounded-xl text-[9px] uppercase transition cursor-pointer select-none font-sans"
                            >
                              Settle Close
                            </button>
                          </div>
                        </div>
                      );
                    })}
                  </div>
                </div>
              )}
            </div>
          )}

          {/* TAB 4: WALLET DEPOSIT PAYSTACK & SWAPS PAGE */}
          {activeTab === 'wallet' && (
            <div className="space-y-6 animate-fade-in">
              <PaystackSim
                onSuccess={handleClaimCredits}
                triggerFlash={triggerFlash}
                lang={lang}
              />

              {/* Inbuilt Buy/Sell Coins Swap Matrix */}
              <div className="bg-[#14151a] border border-[#222] rounded-2xl p-5">
                <div className="flex items-center justify-between border-b border-[#222] pb-3 mb-4">
                  <div className="flex items-center gap-2">
                    <ArrowRightLeft className="text-[#00FFA3]" size={15} />
                    <h3 className="font-extrabold text-white text-xs uppercase tracking-wider leading-none">
                      Inbuilt TON/USDT Currency Swaps
                    </h3>
                  </div>
                  <span className="text-[9px] font-mono text-[#F0B90B] font-bold">1 TON ≈ $7.50 USD</span>
                </div>

                <p className="text-[10px] text-gray-500 mb-4 font-sans leading-relaxed">
                  Swap assets instantly within our secure clearing framework. Direct swaps are logged automatically onto central account hashes.
                </p>

                <div className="grid grid-cols-2 gap-3.5">
                  <button
                    onClick={() => {
                      if (user.balance < 75) {
                        triggerFlash('error', 'Buy swaps require minimum $75.00 USDT reserves.');
                        return;
                      }
                      debitLedger(75, 'Bought 10 TON via inbuilt swaps matrix');
                      setTonBalance(prev => prev + 10);
                      triggerFlash('success', 'Buy Swap Authorized! Exchanged 75 USDT for 10 TON.');
                    }}
                    className="bg-[#0b0c10] hover:bg-zinc-900/80 border border-[#222] hover:border-[#00FFA3]/30 rounded-2xl p-3.5 text-center transition cursor-pointer flex flex-col items-center justify-center"
                  >
                    <span className="text-[8px] uppercase text-gray-500 block font-bold">Buy 10 TON</span>
                    <span className="text-[#00FFA3] font-mono font-bold text-[10.5px] mt-1">-75 USDT / +10 TON</span>
                  </button>

                  <button
                    onClick={() => {
                      if (tonBalance < 10) {
                        triggerFlash('error', 'Sell swaps require minimum 10 TON balance.');
                        return;
                      }
                      setTonBalance(prev => prev - 10);
                      creditLedger(75, 'Inbuilt Convert: Sold 10 TON contracts');
                      triggerFlash('success', 'Sell Swap Verified! Exchanged 10 TON for 75 USDT.');
                    }}
                    className="bg-[#0b0c10] hover:bg-zinc-900/80 border border-[#222] hover:border-[#FF4D4D]/30 rounded-2xl p-3.5 text-center transition cursor-pointer flex flex-col items-center justify-center"
                  >
                    <span className="text-[8px] uppercase text-gray-500 block font-bold">Sell 10 TON</span>
                    <span className="text-[#FF4D4D] font-mono font-bold text-[10.5px] mt-1">-10 TON / +75 USDT</span>
                  </button>
                </div>
              </div>

              {/* Web3 Wallet Authorized connection gateways */}
              <div className="bg-[#14151a] border border-[#222] rounded-2xl p-5">
                <div className="flex items-center justify-between mb-3.5">
                  <h4 className="text-white text-xs font-black uppercase leading-none">Web3 Wallet Authorization</h4>
                  <span className={`text-[8px] font-mono font-bold px-2 py-0.5 rounded border uppercase
                    ${connectedWallet ? 'bg-emerald-950/20 text-[#00FFA3] border-[#00FFA3]/20 animate-pulse' : 'bg-zinc-900 text-gray-500 border-[#222]'}`}>
                    {connectedWallet ? 'AUTHORISED' : 'DISCONNECTED'}
                  </span>
                </div>

                {connectedWallet ? (
                  <div className="bg-[#0b0c10] border border-[#222] p-3 rounded-xl flex items-center justify-between">
                    <div>
                      <span className="text-[8px] text-[#00FFA3] uppercase font-bold block">Active Connected Wallet address</span>
                      <p className="text-[10px] text-gray-300 font-mono mt-1 select-all">{connectedWallet}</p>
                    </div>
                    <button
                      onClick={() => {
                        setConnectedWallet(null);
                        triggerFlash('error', 'Web3 Wallet Session terminated!');
                      }}
                      className="text-red-400 hover:underline text-[9px] font-bold font-sans cursor-pointer uppercase border border-red-950 px-2 py-1 rounded"
                    >
                      Disconnect
                    </button>
                  </div>
                ) : (
                  <div>
                    <p className="text-[9.5px] text-gray-500 mb-3.5 leading-relaxed font-sans">
                      Connect your secure cold-storage TON keeper, phantom or trust wallets to synchronize cryptographic signatures:
                    </p>
                    <div className="grid grid-cols-3 gap-2 text-center text-[10px] font-bold">
                      {['tonkeeper', 'trust', 'phantom'].map(p => (
                        <button
                          key={p}
                          onClick={() => {
                            const mockAddress = 'EQ' + Math.random().toString(36).substring(2, 10).toUpperCase() + '...' + Math.random().toString(36).substring(2, 6).toUpperCase();
                            setConnectedWallet(mockAddress);
                            triggerFlash('success', `Web3 Provider: ${p.toUpperCase()} authorized details!`);
                          }}
                          className="bg-[#0b0c10] border border-[#222] hover:border-[#F0B90B] p-2.5 rounded-xl transition cursor-pointer uppercase flex flex-col items-center justify-center gap-1.5"
                        >
                          <span className="text-base">{p === 'tonkeeper' ? '💎' : p === 'trust' ? '🛡️' : '👻'}</span>
                          <span className="text-[8px] text-zinc-300 tracking-tight block">{p}</span>
                        </button>
                      ))}
                    </div>
                  </div>
                )}
              </div>
            </div>
          )}

          {/* TAB 5: AUDITED LEDGER HISTORIES PAGE */}
          {activeTab === 'history' && (
            <div className="space-y-6 animate-fade-in font-mono text-[10px]">
              <div className="flex justify-between items-center bg-[#14151a] p-3.5 rounded-xl border border-[#222] leading-none mb-1">
                <span className="text-gray-400 uppercase font-black text-[9px]">Double-Entry Hashes records</span>
                <span className="text-[#00FFA3] font-bold text-[9px]">Page {ledgerPage} of {Math.ceil(ledgers.length / ledgerLimit)}</span>
              </div>

              <div className="space-y-2.5">
                {ledgers.slice((ledgerPage - 1) * ledgerLimit, ledgerPage * ledgerLimit).map(l => (
                  <div key={l.id} className="bg-[#14151a] border border-[#222] p-3.5 rounded-xl flex flex-col gap-2 relative">
                    <div className="flex justify-between items-start gap-3">
                      <div>
                        <span className={`text-[7.5px] font-black uppercase text-sans border px-1.5 py-0.5 rounded
                          ${l.type === 'credit' ? 'bg-emerald-950/20 text-[#00FFA3] border-emerald-910' : 'bg-zinc-900 text-gray-400 border-zinc-800'}`}>
                          {l.type}
                        </span>
                        <h4 className="text-white text-[10px] font-extrabold mt-1.5 leading-snug">{l.reason}</h4>
                      </div>
                      <span className={`font-black tracking-tight text-xs flex-shrink-0
                        ${l.type === 'credit' ? 'text-[#00FFA3]' : 'text-gray-300'}`}>
                        {l.type === 'credit' ? '+' : '–'}${l.amount.toFixed(2)}
                      </span>
                    </div>

                    <div className="flex justify-between items-center text-[8.5px] border-t border-zinc-900 pt-2 text-gray-500">
                      <span>Maturity stamp: <strong className="text-gray-400">{l.timestamp}</strong></span>
                      <span>Reserves: <strong className="text-gray-400">${l.balanceAfter.toFixed(2)}</strong></span>
                    </div>
                  </div>
                ))}

                {/* Ledger hashes paginator */}
                {ledgers.length > ledgerLimit && (
                  <div className="grid grid-cols-2 gap-2 pt-2.5">
                    <button
                      disabled={ledgerPage <= 1}
                      onClick={() => setLedgerPage(prev => Math.max(1, prev - 1))}
                      className="bg-[#14151a] border border-[#222] hover:border-[#00FFA3]/40 text-gray-400 text-[9.5px] font-extrabold py-1.5 rounded-xl cursor-pointer disabled:opacity-30 transition"
                    >
                      ◀ PREVIOUS LOGS
                    </button>
                    <button
                      disabled={ledgerPage >= Math.ceil(ledgers.length / ledgerLimit)}
                      onClick={() => setLedgerPage(prev => prev + 1)}
                      className="bg-[#14151a] border border-[#222] hover:border-[#00FFA3]/40 text-gray-400 text-[9.5px] font-extrabold py-1.5 rounded-xl cursor-pointer disabled:opacity-30 transition"
                    >
                      NEXT LOGS PAGE ▶
                    </button>
                  </div>
                )}
              </div>

              {/* Price flags alarm logs */}
              {triggeredAlerts.length > 0 && (
                <div className="bg-[#14151a] border border-[#222] rounded-2xl p-5 space-y-2.5">
                  <h4 className="text-white text-[10px] font-black uppercase text-zinc-300">Triggered Price Alarms logs</h4>
                  <div className="space-y-1.5 max-h-24 overflow-y-auto custom-scrollbar">
                    {triggeredAlerts.map((log, k) => (
                      <p key={k} className="text-[9.2px] text-[#F0B90B] font-bold border-b border-zinc-900 pb-1 font-mono">
                        🚩 {log}
                      </p>
                    ))}
                  </div>
                </div>
              )}
            </div>
          )}

          {/* TAB 6: SETTINGS & ADMINISTRATIVE CALIBRATION CORE PAGE */}
          {activeTab === 'settings' && (
            <div className="space-y-6 animate-fade-in font-mono text-xs">
              
              {/* Performance Mode Switches */}
              <div className="bg-[#14151a] border border-[#222] rounded-2xl p-5 space-y-3">
                <div className="flex items-center justify-between border-b border-[#222] pb-2.5 mb-1">
                  <h5 className="font-extrabold uppercase text-white tracking-widest text-[10px] flex items-center gap-1.5">
                    <Activity size={12} className="text-[#00FFA3]" />
                    {t('performanceMode')}
                  </h5>
                  <button
                    onClick={() => {
                      setPerformanceMode(!performanceMode);
                      triggerFlash('success', `Performance efficiency mode turned ${!performanceMode ? 'ACTIVE' : 'DEACTIVATED'} globally.`);
                    }}
                    className={`text-[8.5px] font-bold uppercase px-2 py-0.5 rounded transition cursor-pointer select-none border font-mono
                      ${performanceMode ? 'bg-[#00FFA3]/10 text-[#00FFA3] border-[#00FFA3]/20' : 'bg-[#0b0c10] text-gray-550 border-[#222]'}`}
                  >
                    {performanceMode ? 'ACTIVE' : 'DEACTIVATED'}
                  </button>
                </div>
                <p className="text-[9.5px] text-gray-500 leading-relaxed font-sans mt-1">
                  {t('performanceModeDesc')}
                </p>
              </div>

              {/* Package pricing engine custom settings */}
              <div className="bg-[#14151a] border border-[#222] rounded-2xl p-5 space-y-4">
                <div className="flex items-center justify-between border-b border-[#222] pb-2 text-[10px] text-white font-extrabold">
                  <span className="flex items-center gap-1"><Coins size={12} className="text-[#00FFA3]" /> ADJUST COCKPIT PRICING MODES</span>
                  <span className="text-[8px] bg-amber-500/10 text-amber-500 border border-amber-500/20 px-1.5 py-0.5 rounded tracking-widest">LIVE DATA</span>
                </div>
                
                <p className="text-[9.5px] text-gray-500 leading-relaxed mt-1 font-sans">
                  Change pricing levels inside our cockpit sandbox database. These updates carry over into the packages upgrade sheet dynamically.
                </p>

                <div className="grid grid-cols-2 gap-3.5">
                  <div>
                    <label className="block text-gray-500 text-[8px] font-bold uppercase mb-1">Pro Strategy cost ($/m)</label>
                    <input
                      type="number"
                      step="0.01"
                      value={proTierPrice}
                      onChange={(e) => setProTierPrice(Number(e.target.value))}
                      className="w-full bg-[#0b0c10] border border-[#222] px-2.5 py-1.5 rounded-xl text-white text-[11px] font-bold focus:outline-none"
                    />
                  </div>
                  <div>
                    <label className="block text-gray-500 text-[8px] font-bold uppercase mb-1">VIP Premium cost ($/m)</label>
                    <input
                      type="number"
                      step="0.01"
                      value={vipTierPrice}
                      onChange={(e) => setVipTierPrice(Number(e.target.value))}
                      className="w-full bg-[#0b0c10] border border-[#222] px-2.5 py-1.5 rounded-xl text-white text-[11px] font-bold focus:outline-none"
                    />
                  </div>
                </div>

                <div className="border border-[#222] rounded-2xl p-4 space-y-4">
                  <h4 className="text-zinc-300 font-extrabold uppercase text-[10px] tracking-wide border-b border-[#222] pb-1.5 flex items-center gap-1">🛡️ Upgrades License plans</h4>
                  {[
                    { key: 'pro' as const, name: 'Pro Strategy License', price: proTierPrice, benefits: 'Confidences numbers unlocked + EMA crossover indicators curves.' },
                    { key: 'vip' as const, name: 'VIP Premium Sovereign', price: vipTierPrice, benefits: 'Zero latency indices metrics + immediate Bybit API feeds & Zero sponsored Ads.' }
                  ].map(plan => (
                    <div key={plan.key} className={`border p-3.5 rounded-xl text-[9px] relative transition-all duration-300
                      ${user.plan === plan.key ? 'bg-[#00FFA3]/5 border-[#00FFA3]/40' : 'bg-transparent border-zinc-900'}`}>
                      {user.plan === plan.key && (
                        <span className="absolute top-3.5 right-3.5 text-[7px] font-bold text-[#00FFA3] uppercase border border-[#00FFA3]/20 bg-[#00FFA3]/5 px-2 py-0.5 rounded-full select-none">License active</span>
                      )}
                      <h4 className="text-white text-[11px] font-extrabold leading-none">{plan.name}</h4>
                      <p className="text-gray-500 font-mono text-[9px] mt-1">$ {plan.price.toFixed(2)} / monthly</p>
                      <p className="text-gray-400 mt-2 font-sans py-1 leading-relaxed">{plan.benefits}</p>
                      <div className="mt-3">
                        {user.plan === plan.key ? (
                          <button disabled className="w-full text-center py-2 bg-zinc-900 border border-zinc-800 text-gray-600 rounded-xl select-none text-[9.5px] uppercase font-bold cursor-not-allowed">Active</button>
                        ) : (
                          <button onClick={() => handleUpgradeSubscription(plan.key)} className="w-full text-center py-2 bg-[#00FFA3] hover:opacity-95 text-black font-black uppercase text-[9.5px] rounded-xl transition cursor-pointer font-sans select-none tracking-wider">Buy License Upgrade</button>
                        )}
                      </div>
                    </div>
                  ))}
                </div>
              </div>

              {/* Administrative sensitive parameters, alerts writing, ads creator */}
              <div className="bg-[#14151a] border border-[#222] rounded-2xl p-5 space-y-4">
                <div className="flex items-center justify-between border-b border-[#222] pb-2 text-[10px] text-white font-extrabold font-mono">
                  <span className="flex items-center gap-1"><Shield size={12} className="text-[#00FFA3]" /> ADMIN HARDWARE CONFIG COCKPIT</span>
                  <span className="text-[8px] bg-red-950/25 text-red-500 border border-red-950/35 px-1.5 py-0.5 rounded tracking-widest font-black uppercase">Root</span>
                </div>

                {/* Signals Sensitivities globally toggler */}
                <div>
                  <label className="block text-gray-500 text-[8px] font-bold uppercase mb-1">Global Signal Sensitivity Filter</label>
                  <div className="grid grid-cols-3 gap-2">
                    {(['LOW', 'MEDIUM', 'HIGH'] as const).map(lvl => (
                      <button
                        key={lvl}
                        onClick={() => {
                          setSignalSensitivity(lvl);
                          triggerFlash('success', `Altered global calibration index status: ${lvl}`);
                        }}
                        className={`py-1.5 rounded-lg text-[9px] font-extrabold uppercase border cursor-pointer select-none text-center transition
                          ${signalSensitivity === lvl ? 'bg-[#00FFA3]/10 text-[#00FFA3] border-[#00FFA3]/30' : 'bg-[#0b0c10] text-gray-500 border-[#222]'}`}
                      >
                        {lvl} Mode
                      </button>
                    ))}
                  </div>
                </div>

                {/* Maintenance writing widget */}
                <div className="bg-[#0b0c10] border border-[#222] p-3.5 rounded-xl space-y-3">
                  <div className="flex justify-between items-center text-[10px] font-extrabold text-white">
                    <span>GLOBAL MAINTENANCE MESSAGES</span>
                    <button
                      onClick={() => {
                        setMaintenanceAlertActive(!maintenanceAlertActive);
                        triggerFlash('success', `Maintenance status flags modified: ${!maintenanceAlertActive}`);
                      }}
                      className={`text-[8px] font-bold border px-2 py-0.5 rounded transition uppercase
                        ${maintenanceAlertActive ? 'bg-amber-950/20 text-amber-500 border-amber-900/30' : 'bg-transparent text-gray-500 border-[#222]'}`}
                    >
                      {maintenanceAlertActive ? 'Warning online' : 'offline'}
                    </button>
                  </div>
                  <textarea
                    value={maintenanceAlertMsg}
                    onChange={(e) => setMaintenanceAlertMsg(e.target.value)}
                    className="w-full bg-[#14151a] border border-[#222] rounded-xl p-2 text-[10px] text-white focus:outline-none focus:border-[#00FFA3]"
                    rows={2}
                    placeholder="Provide alert descriptions..."
                  />
                </div>

                {/* Ads placements deployments */}
                <div className="bg-[#0b0c10] border border-[#222] p-3.5 rounded-xl space-y-3">
                  <h5 className="text-[10px] font-extrabold text-[#00FFA3]">DEPLOY SPONSORED PROMOTION CAMPAIGNS</h5>
                  <form onSubmit={(e) => {
                    e.preventDefault();
                    const formdata = new FormData(e.currentTarget);
                    const title = String(formdata.get('ad_title') || '');
                    const imageUrl = String(formdata.get('ad_image') || '');
                    if (!title || !imageUrl) return;

                    const newAd: AdCampaigns = {
                      id: campaigns.length + 1,
                      placement: 'market',
                      title,
                      imageUrl,
                      linkUrl: '#',
                      active: true
                    };
                    setCampaigns(prev => [newAd, ...prev]);
                    triggerFlash('success', `Ad Campaign '${title}' deployed successfully!`);
                    e.currentTarget.reset();
                  }} className="space-y-2.5 text-[9px]">
                    <input
                      type="text"
                      name="ad_title"
                      placeholder="Ad Headlines header (e.g. BTC cashback promos...)"
                      className="w-full bg-[#14151a] border border-[#222] rounded-lg px-2.5 py-1.5 focus:outline-none"
                      required
                    />
                    <input
                      type="url"
                      name="ad_image"
                      defaultValue="https://images.unsplash.com/photo-1621510456681-23a23cfb5f57?auto=format&fit=crop&w=600&q=80"
                      className="w-full bg-[#14151a] border border-[#222] rounded-lg px-2.5 py-1.5 focus:outline-none"
                      required
                    />
                    <button type="submit" className="w-full bg-indigo-950/20 hover:bg-indigo-950/30 border border-indigo-900/40 text-indigo-400 py-1.5 rounded-lg uppercase cursor-pointer text-center font-bold">
                      Deploy ad placement
                    </button>
                  </form>
                </div>
              </div>
            </div>
          )}

        </main>

        {/* TAB 7: SPECIFIC DEVELOPER COCKPIT SPLIT/RIGHT HALF PANEL */}
        {splitView && (
          <section className="w-full lg:w-[45%] bg-[#0c0d10] border-t lg:border-t-0 lg:border-l border-[#1f1f1f] p-4 md:p-6 overflow-y-auto max-h-[calc(100vh-64px)] space-y-6">
            <div className="flex items-center justify-between border-b border-[#222] pb-3 mb-4">
              <div className="flex items-center gap-2">
                <FileCode className="text-[#00FFA3]" size={16} />
                <h3 className="font-extrabold text-white text-xs uppercase tracking-wider font-sans">
                  PHP SaaS Systems Code Explorer
                </h3>
              </div>
              <span className="text-[7.5px] text-gray-500 font-extrabold uppercase">
                MySQLi Direct Engine
              </span>
            </div>

            <p className="text-[9.5px] text-gray-500 leading-relaxed font-sans mt-1">
              Analyze fully compliant enterprise backends written in pure PHP 7.4+ compatible with typical apache shared host systems:
            </p>

            {/* Custom file index selectors */}
            <div className="grid grid-cols-2 gap-2">
              {PHP_SOURCES.map((src, idx) => (
                <button
                  key={src.path}
                  onClick={() => setSelectedFileIndex(idx)}
                  className={`text-left p-2.5 rounded-xl border text-[10px] font-mono transition cursor-pointer select-none leading-none
                    ${selectedFileIndex === idx
                      ? 'bg-[#00FFA3]/15 border-[#00FFA3]/35 text-[#00FFA3]'
                      : 'bg-[#14151a] border-[#222] text-gray-500 hover:text-zinc-300'}`}
                >
                  <p className="font-black truncate block">{src.path}</p>
                  <span className="text-[8px] text-gray-600 block mt-1 truncate">{src.title}</span>
                </button>
              ))}
            </div>

            {/* Code presentation output box */}
            <div className="bg-[#14151a] border border-[#222] rounded-2xl p-4.5 space-y-3 relative overflow-hidden font-mono">
              <div className="flex justify-between items-center bg-[#0b0c10] p-2.5 rounded-xl border border-[#222] text-[9.5px]">
                <span className="text-[#00FFA3] font-black truncate max-w-xs">{currentPhpSource.path}</span>
                <button
                  onClick={() => handleCopyCode(currentPhpSource.code)}
                  className="text-[#00FFA3] font-bold hover:underline cursor-pointer"
                >
                  {isCopied ? 'Copied!' : 'Copy Code'}
                </button>
              </div>

              <p className="text-[9.5px] text-gray-400 font-sans leading-relaxed">
                💡 <strong className="text-white">Feature Action:</strong> {currentPhpSource.description}
              </p>

              <pre className="text-[8.5px] leading-relaxed text-zinc-300 p-3.5 bg-[#0b0c10] border border-[#222] rounded-xl overflow-x-auto max-h-96 custom-scrollbar select-text selection:bg-[#00FFA3]/30">
                {currentPhpSource.code}
              </pre>
            </div>

            {/* SSE pushed webhook triggers logs panel inside Code Cockpit */}
            <div className="bg-[#14151a] border border-[#222] rounded-2xl p-4">
              <div className="flex items-center justify-between border-b border-[#222] pb-2 mb-3">
                <span className="text-[8.5px] uppercase font-mono font-black text-gray-400 flex items-center gap-1.5">
                  <span className="w-1.5 h-1.5 rounded-full bg-[#00FFA3] animate-pulse" />
                  Live SSE Webhooks Emulator Log
                </span>
                <button
                  onClick={() => {
                    const demoMsg = {
                      id: Date.now(),
                      event: 'SSE_MOCK_HEARTBEAT',
                      title: 'API Ticker Ping',
                      message: `Bybit Perpetual API status: success. Latency 12ms.`,
                      time: new Date().toLocaleTimeString()
                    };
                    setSseEventsLog(prev => [demoMsg, ...prev]);
                    triggerFlash('success', 'Injected simulated SSE event webhook alert!');
                  }}
                  className="text-[8.5px] font-black text-[#00FFA3] hover:underline cursor-pointer uppercase font-mono"
                >
                  + emit sse webhook
                </button>
              </div>
              
              <div className="space-y-1.5 max-h-36 overflow-y-auto no-scrollbar font-mono text-[8px]">
                {sseEventsLog.map(log => (
                  <div key={log.id} className="bg-[#0b0c10] border border-[#222] p-2.5 rounded-xl flex justify-between gap-3 items-start">
                    <div>
                      <p className="text-[#00FFA3] font-black uppercase text-[7.5px]">{log.event}</p>
                      <p className="text-gray-300 font-bold mt-0.5">{log.title}</p>
                      <p className="text-gray-500 mt-0.5 leading-snug">{log.message}</p>
                    </div>
                    <span className="text-gray-600 font-extrabold whitespace-nowrap">{log.time}</span>
                  </div>
                ))}
              </div>
            </div>
          </section>
        )}

      </div>

    </div>
  );
}
