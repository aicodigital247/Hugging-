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
  ArrowRightLeft
} from 'lucide-react';
import { PHP_SOURCES } from './php_sources';
import { SimulatorUser, Ledgers, Signals, Bulletins, AdCampaigns } from './types';

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
    alerts: "Dynamic Price Alerts",
    onboarding: "Onboarding Tour",
    telemetry: "Telemetry overlays",
    auditBook: "Audit Book Ledger",
    bybitMkt: "Bybit Linear Market Feed"
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
    alerts: "Alertas de Precios Activas",
    onboarding: "Tour de Bienvenida",
    telemetry: "Superposiciones de telemetría",
    auditBook: "Libro Contable Auditado",
    bybitMkt: "Feed de Mercado Bybit"
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
    bybitMkt: "Bybit 永续合约官方行情源"
  }
};

export interface PriceAlert {
  id: number;
  symbol: string;
  targetPrice: number;
  direction: 'above' | 'below';
  active: boolean;
}

export interface InvestmentVault {
  id: number;
  amount: number;
  token: 'USDT' | 'TON';
  apy: number;
  interestAccrued: number;
  daysRemaining: number;
  date: string;
}

export interface SseEvent {
  id: number;
  event: string;
  title: string;
  message: string;
  time: string;
}

export default function App() {
  // Multilingual active locale state
  const [lang, setLang] = useState<'en' | 'es' | 'zh'>('en');

  // Simulator state configurations
  const [user, setUser] = useState<SimulatorUser>({
    id: 1,
    email: 'admin@saas.com',
    plan: 'free',
    status: 'active',
    balance: 500.00,
    createdAt: '2026-06-16'
  });

  const [activeTab, setActiveTab] = useState<'markets' | 'signals' | 'charts' | 'ledger' | 'profile' | 'admin'>('markets');
  const [selectedSymbol, setSelectedSymbol] = useState<'BTCUSDT' | 'ETHUSDT' | 'SOLUSDT' | 'ADAUSDT'>('BTCUSDT');
  const [timeframe, setTimeframe] = useState<'1m' | '5m' | '15m' | '1h' | '1d'>('1h');
  const [isChangingTimeframe, setIsChangingTimeframe] = useState<boolean>(false);
  
  // Indicator Toggles State
  const [indicatorsEnabled, setIndicatorsEnabled] = useState<boolean>(true);
  
  // Custom interactive mock lists
  const [ledgers, setLedgers] = useState<Ledgers[]>([
    { id: 1, type: 'credit', amount: 500.00, reason: 'Sandbox Environment Capitalization', balanceAfter: 500.00, timestamp: '2026-06-16 12:00:00' }
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

  // Dynamic values state for Markets Tickers
  const [tickerPrices, setTickerPrices] = useState({
    BTCUSDT: { price: 68425.40, change: 2.15, vol: 8145.2 },
    ETHUSDT: { price: 3512.80, change: -1.04, vol: 924.5 },
    SOLUSDT: { price: 146.55, change: 4.88, vol: 2450.8 },
    ADAUSDT: { price: 0.442, change: -0.12, vol: 152.0 }
  });

  // Web3 wallets states
  const [connectedWallet, setConnectedWallet] = useState<string | null>(null);
  const [showWalletModal, setShowWalletModal] = useState<boolean>(false);
  const [tonBalance, setTonBalance] = useState<number>(0.0);

  // Price alarms list state (Custom price alerts)
  const [priceAlerts, setPriceAlerts] = useState<PriceAlert[]>([
    { id: 1, symbol: 'BTCUSDT', targetPrice: 68500, direction: 'above', active: true },
    { id: 2, symbol: 'SOLUSDT', targetPrice: 142, direction: 'below', active: true }
  ]);
  const [triggeredAlerts, setTriggeredAlerts] = useState<string[]>([]);

  // Staking investment vaults
  const [investedPools, setInvestedPools] = useState<InvestmentVault[]>([
    { id: 1, amount: 200, token: 'USDT', apy: 15.5, interestAccrued: 1.45, daysRemaining: 30, date: '2026-06-16' }
  ]);

  // Step-by-step Onboarding flow
  const [showOnboarding, setShowOnboarding] = useState<boolean>(true);
  const [onboardingStep, setOnboardingStep] = useState<number>(1);

  // Admin Module Control States (Globally toggle signals, Bybit API endpoints, Tier prices, and alerts)
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

  // Code Viewer state
  const [selectedFileIndex, setSelectedFileIndex] = useState<number>(0);
  const [isCopied, setIsCopied] = useState<boolean>(false);

  // Pagination states
  const [ledgerPage, setLedgerPage] = useState<number>(1);
  const ledgerLimit = 3;

  // Signal detail breakdown / explanation modal
  const [selectedSignalDetail, setSelectedSignalDetail] = useState<Signals | null>(null);

  // Translations fetch shortener
  const t = (key: keyof typeof TRANSLATIONS['en']): string => {
    return TRANSLATIONS[lang][key] || TRANSLATIONS['en'][key] || String(key);
  };

  // Triggering smooth timeframe changes transitions
  const handleTimeframeChange = (tf: typeof timeframe) => {
    setIsChangingTimeframe(true);
    setTimeframe(tf);
    setTimeout(() => setIsChangingTimeframe(false), 220);
  };

  // Simulated live ticker updater logic + Alerts checker
  useEffect(() => {
    const timer = setInterval(() => {
      setTickerPrices(prev => {
        const updateVal = (val: number, multiplier = 0.0005) => {
          const delta = val * multiplier * (Math.random() - 0.48);
          return Number((val + delta).toFixed(3));
        };
        const next = {
          BTCUSDT: { price: updateVal(prev.BTCUSDT.price), change: Number((prev.BTCUSDT.change + (Math.random() - 0.5) * 0.1).toFixed(2)), vol: prev.BTCUSDT.vol },
          ETHUSDT: { price: updateVal(prev.ETHUSDT.price), change: Number((prev.ETHUSDT.change + (Math.random() - 0.5) * 0.1).toFixed(2)), vol: prev.ETHUSDT.vol },
          SOLUSDT: { price: updateVal(prev.SOLUSDT.price), change: Number((prev.SOLUSDT.change + (Math.random() - 0.5) * 0.15).toFixed(2)), vol: prev.SOLUSDT.vol },
          ADAUSDT: { price: updateVal(prev.ADAUSDT.price), change: Number((prev.ADAUSDT.change + (Math.random() - 0.5) * 0.08).toFixed(3)), vol: prev.ADAUSDT.vol }
        };

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
                  message: `${alert.symbol} touched ${alert.targetPrice} standard.`,
                  time: timestamp
                },
                ...prevSse
              ]);
            }
          }
        });

        return next;
      });
    }, 3000);
    return () => clearInterval(timer);
  }, [priceAlerts]);


  const triggerFlash = (type: 'success' | 'error', message: string) => {
    setFlash({ type, message });
    setTimeout(() => setFlash(null), 3500);
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

  // 3. Simulated deposit form submit
  const handleClaimCredits = (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault();
    const data = new FormData(e.currentTarget);
    const amt = Number(data.get('amount') || 250);
    if (amt <= 0 || amt > 5000) {
      triggerFlash('error', 'Invalid amount. Minimum $10 up to $5,000 permitted per transfer.');
      return;
    }
    creditLedger(amt, 'Simulated Deposit - Sandbox Wallet Injection');
    triggerFlash('success', `Simulated deposit confirmed. Credited \$${amt.toFixed(2)} USD to Ledger.`);
    e.currentTarget.reset();
  };

  // 4. Admin broadcast publishers
  const handleAdminPublishNotice = (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault();
    const data = new FormData(e.currentTarget);
    const title = String(data.get('title') || '');
    const content = String(data.get('content') || '');
    const category = String(data.get('category') || 'broadcast') as 'broadcast' | 'alert' | 'signal';

    if (!title || !content) {
      triggerFlash('error', 'Bulletin headings and descriptions are mandatory.');
      return;
    }

    const item: Bulletins = {
      id: bulletins.length + 1,
      title,
      content,
      type: category,
      createdAt: new Date().toISOString().replace('T', ' ').substring(0, 19)
    };
    setBulletins(prev => [item, ...prev]);
    triggerFlash('success', 'Global system bulletin published to users cockpit.');
    e.currentTarget.reset();
  };

  // 5. Admin launch custom campaign
  const handleAdminCreateAd = (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault();
    const data = new FormData(e.currentTarget);
    const heading = String(data.get('heading') || '');
    const placementSlot = String(data.get('slot') || 'in_feed') as 'banner' | 'in_feed' | 'market';
    const graphic = String(data.get('url') || '');

    if (!heading || !graphic) {
      triggerFlash('error', 'Ensure banner headlines and illustrative graphic files are filled.');
      return;
    }

    const ad: AdCampaigns = {
      id: campaigns.length + 1,
      placement: placementSlot,
      title: heading,
      imageUrl: graphic,
      linkUrl: '#',
      active: true
    };
    setCampaigns(prev => [ad, ...prev]);
    triggerFlash('success', `Ad campaign '${heading}' deployed securely.`);
    e.currentTarget.reset();
  };

  // Copy code helper
  const handleCopyCode = (txt: string) => {
    navigator.clipboard.writeText(txt);
    setIsCopied(true);
    setTimeout(() => setIsCopied(false), 2000);
  };

  // Interactive Candlesticks Generator for SVG canvas
  const getCandlesticks = () => {
    const basePrices: { [key: string]: number } = {
      BTCUSDT: 68425,
      ETHUSDT: 3512,
      SOLUSDT: 146,
      ADAUSDT: 0.44
    };
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

      // indicators calculations elements
      const ema9 = center + wave * 0.9 + (idxOffset * (center * 0.0004));
      const ema21 = center + wave * 0.75 + (idxOffset * (center * 0.0003));
      
      rsiVal = Number((50 + Math.sin(i * 0.5) * 22 + (Math.random() - 0.5) * 8).toFixed(1));

      items.push({
        open,
        close,
        high,
        low,
        vol,
        ema9,
        ema21,
        rsi: rsiVal
      });
    }
    return items;
  };

  const candleData = getCandlesticks();

  // Selected PHP Source
  const currentPhpSource = PHP_SOURCES[selectedFileIndex];

  return (
    <div className="min-h-screen bg-[#050505] text-gray-200 flex flex-col md:flex-row antialiased select-none selection:bg-[#00FFA3]/20">
      
      {/* 1. DEVELOPER WORKBENCH SIDEBAR (DESKTOP MODE WRAPPER) */}
      <div className="w-full md:w-3/5 p-6 border-b md:border-b-0 md:border-r border-[#1f1f1f] bg-[#0A0A0B] flex flex-col justify-between overflow-y-auto max-h-screen">
        
        <div>
          {/* Logo Brand Header & Language Switcher */}
          <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-5 border-b border-[#1f1f1f] pb-4">
            <div className="flex items-center gap-2.5">
              <span className="w-9 h-9 rounded-xl bg-gradient-to-br from-[#00FFA3]/20 to-[#7047EB]/20 border border-[#00FFA3]/30 flex items-center justify-center font-black text-[#00FFA3] text-base shadow-lg shadow-[#7047EB]/10 font-display">
                TN
              </span>
              <div>
                <h1 className="text-sm font-black tracking-tighter text-white uppercase leading-none font-display">
                  {t('title')}
                </h1>
                <p className="text-[10px] text-gray-500 font-mono mt-1">{t('desc')}</p>
              </div>
            </div>

            {/* Language Switch Selector widget */}
            <div className="flex items-center gap-2 self-start sm:self-auto bg-[#151619] border border-[#222] p-1 rounded-xl">
              <Globe size={11} className="text-gray-400 ml-1.5" />
              {(['en', 'es', 'zh'] as const).map((l) => (
                <button
                  key={l}
                  onClick={() => {
                    setLang(l);
                    triggerFlash('success', `Language changed to ${l.toUpperCase()}`);
                  }}
                  className={`px-2 py-1 text-[9px] font-mono font-black rounded-lg transition uppercase cursor-pointer
                    ${lang === l ? 'bg-[#00FFA3]/10 text-[#00FFA3] border border-[#00FFA3]/20' : 'text-gray-500 hover:text-gray-300'}`}
                >
                  {l === 'en' ? 'EN' : l === 'es' ? 'ES' : '中文'}
                </button>
              ))}
            </div>
          </div>

          <div className="text-xs text-gray-400 leading-relaxed mb-6 bg-[#151619]/40 p-3.5 rounded-2xl border border-[#222] space-y-2">
            <p>
              💡 <strong>System Mode:</strong> You are exploring the <strong className="text-[#00FFA3]">TradeNexa PHP SaaS Cockpit</strong> designed for Bybit & Binance-style microsecond-latency simulation.
            </p>
            <div className="flex gap-2 pt-1">
              <button 
                onClick={() => {
                  setShowOnboarding(true);
                  setOnboardingStep(1);
                  triggerFlash('success', 'Onboarding guide restarted!');
                }}
                className="bg-[#00FFA3]/10 hover:bg-[#00FFA3]/25 border border-[#00FFA3]/25 px-2.5 py-1 rounded-lg text-[9px] text-[#00FFA3] font-bold transition font-mono uppercase cursor-pointer"
              >
                🚀 Reset Guided Tour
              </button>
              <button 
                onClick={() => {
                  setShowWalletModal(true);
                }}
                className="bg-[#7047EB]/15 hover:bg-[#7047EB]/30 border border-[#7047EB]/25 px-2.5 py-1 rounded-lg text-[9px] text-[#9167FF] font-bold transition font-mono uppercase cursor-pointer flex items-center gap-1"
              >
                <Wallet size={9} /> Connect Web3 Webhook
              </button>
            </div>
          </div>

          {/* Clean SSE live stream log feed inside workbench */}
          <div className="mb-6 bg-[#151619]/70 border border-[#222] rounded-2xl p-4">
            <div className="flex items-center justify-between mb-3 border-b border-[#222] pb-2">
              <span className="text-[10px] uppercase font-mono font-bold tracking-widest text-gray-400 flex items-center gap-1.5">
                <span className="w-2 h-2 rounded-full bg-[#00FFA3] animate-pulse"></span>
                Server-Sent Events Gateway (app/api/sse_notifications.php)
              </span>
              <button
                onClick={() => {
                  const items = [
                    { id: Date.now(), event: 'SSE_RISK_INSIGHT', title: 'Bybit Premium Pulse', message: 'Liquidity index spike on ADAUSDT linear contracts detected.', time: new Date().toLocaleTimeString() },
                    { id: Date.now() + 1, event: 'SSE_ADMIN_ACTION', title: 'Ad Campaign Deployed', message: 'Binance Cashback Banner modified in global view.', time: new Date().toLocaleTimeString() }
                  ];
                  setSseEventsLog(prev => [items[0], ...prev]);
                  triggerFlash('success', 'Pushed live SSE event data from PHP backend!');
                }}
                className="text-[9px] font-black text-[#00FFA3] hover:underline cursor-pointer uppercase font-mono"
              >
                + Push Demo SSE
              </button>
            </div>
            <div className="space-y-1.5 max-h-32 overflow-y-auto custom-scrollbar pr-1">
              {sseEventsLog.map((log) => (
                <div key={log.id} className="text-[10px] font-mono bg-[#0A0A0B] border border-[#222] rounded-xl p-2 flex justify-between items-start gap-3">
                  <div>
                    <span className="text-[8px] px-1.5 py-0.5 rounded bg-blue-500/10 text-blue-400 font-bold tracking-tight uppercase">
                      {log.event}
                    </span>
                    <h5 className="font-bold text-gray-200 mt-1">{log.title}</h5>
                    <p className="text-gray-500 text-[9px] mt-0.5">{log.message}</p>
                  </div>
                  <span className="text-gray-600 text-[8.5px] whitespace-nowrap">{log.time}</span>
                </div>
              ))}
            </div>
          </div>

          {/* Credentials Info */}
          <div className="grid grid-cols-2 gap-3.5 mb-6 text-xs font-mono">
            <div className="bg-[#151619] p-3.5 rounded-2xl border border-[#222]">
              <span className="text-[9px] text-gray-500 uppercase tracking-widest font-bold">Admin Login Username</span>
              <p className="font-mono text-xs font-bold text-gray-300 mt-1">admin@saas.com</p>
            </div>
            <div className="bg-[#151619] p-3.5 rounded-2xl border border-[#222]">
              <span className="text-[9px] text-gray-500 uppercase tracking-widest font-bold">Admin Code</span>
              <p className="font-mono text-xs font-bold text-[#00FFA3] mt-1">admin123</p>
            </div>
          </div>

          {/* File Explorer tab line selector */}
          <h3 className="text-[11px] font-bold text-[#00FFA3] uppercase tracking-widest mb-3 flex items-center gap-2 font-mono">
            <FileCode size={12} className="text-[#00FFA3]" />
            PHP Production files examiner (mysqli compatible)
          </h3>

          <div className="grid grid-cols-2 gap-2 mb-4">
            {PHP_SOURCES.map((src, idx) => (
              <button
                key={src.path}
                onClick={() => setSelectedFileIndex(idx)}
                className={`text-left p-3 rounded-xl border text-[11px] font-mono transition cursor-pointer select-none
                  ${selectedFileIndex === idx
                    ? 'bg-[#00FFA3]/10 border-[#00FFA3]/40 text-[#00FFA3]' 
                    : 'bg-[#151619] border-[#222] hover:border-[#2A2A2A] text-gray-400 hover:text-gray-200'}`}
              >
                <p className="font-bold truncate">{src.path}</p>
                <span className="text-[9px] text-gray-500 block mt-1 truncate">{src.title}</span>
              </button>
            ))}
          </div>

          <div className="bg-[#151619] border border-[#222] rounded-3xl p-5 relative overflow-hidden mb-6">
            <div className="flex justify-between items-center mb-3">
              <span className="text-[10px] font-mono bg-[#0A0A0B] border border-[#222] text-[#00FFA3] px-2.5 py-1 rounded-lg">
                📁 {currentPhpSource.path}
              </span>
              <button
                onClick={() => handleCopyCode(currentPhpSource.code)}
                className="text-[10px] text-[#00FFA3] font-bold hover:underline select-none cursor-pointer"
              >
                {isCopied ? 'Copied code!' : 'Copy Code'}
              </button>
            </div>
            
            <p className="text-xs text-gray-400 mb-3 leading-relaxed font-sans">
              💡 <span className="font-bold text-gray-300">Feature Description</span>: {currentPhpSource.description}
            </p>

            <pre className="text-[10px] font-mono leading-relaxed text-gray-300 p-4 bg-[#0A0A0B] rounded-2xl overflow-x-auto border border-[#222] max-h-72 custom-scrollbar">
              {currentPhpSource.code}
            </pre>
          </div>
        </div>

        {/* Quick Footer Checklist */}
        <div className="border-t border-[#1f1f1f] pt-5 text-[10px] text-gray-500 font-mono space-y-1">
          <p>⚖ TradeNexa.com - Strict PHP 7.4+ MySQLi SaaS Framework</p>
          <p>⚡ Tested compatible with cPanel shared hosting plans</p>
        </div>
      </div>

      {/* 2. THE FLOATING SMARTPHONE VISUALIZER VIEW */}
      <div className="flex-1 flex justify-center items-center p-4 md:p-8 bg-[#050505]">
        
        {/* Smartphone Shell Frame */}
        <div className="w-full max-w-[395px] h-[820px] bg-[#0A0A0B] border-[12px] border-[#1f1f1f] rounded-[48px] shadow-2xl relative flex flex-col overflow-hidden">
          
          {/* Internal Notch Element */}
          <div className="absolute top-0 inset-x-0 h-6 flex justify-center z-50">
            <div className="w-32 h-4 bg-[#1f1f1f] rounded-b-2xl flex justify-around items-center px-4">
              <span className="w-1.5 h-1.5 rounded-full bg-[#0A0A0B]"></span>
              <span className="w-8 h-1 bg-[#0A0A0B] rounded"></span>
            </div>
          </div>

          {/* Phone Screen Status Line */}
          <div className="pt-7 px-6 h-12 flex justify-between items-center text-[10px] text-gray-500 bg-[#0A0A0B] border-b border-[#1f1f1f] select-none z-25">
            <span className="font-bold text-gray-400">9:41 AM</span>
            <div className="flex gap-1.5 font-bold text-gray-400">
              <span>📶</span>
              <span>🔋</span>
            </div>
          </div>

          {/* FLASH NOTIFICATION DISPLAY */}
          {flash && (
            <div className={`absolute top-14 inset-x-4 z-40 p-3 rounded-2xl text-[10px] font-bold shadow-lg animate-bounce
              ${flash.type === 'success' ? 'bg-[#0A0A0B] border border-[#00FFA3]/40 text-[#00FFA3]' : 'bg-[#0A0A0B] border border-[#FF4D4D]/40 text-[#FF4D4D]'}`}>
              {flash.message}
            </div>
          )}

          {/* PHONE BODY SCROLL CONTAINER */}
          <div className="flex-1 overflow-y-auto no-scrollbar bg-[#0A0A0B] p-4.5 pb-24 text-gray-200">
            
            {/* System-wide maintenance warning alerts banner */}
            {maintenanceAlertActive && maintenanceAlertMsg && (
              <div className="mb-4 bg-[#F0B90B]/10 border border-[#F0B90B]/25 rounded-2xl p-3 text-[10.5px] leading-relaxed text-[#F0B90B] font-mono flex items-start gap-2 relative overflow-hidden shadow-md">
                <span className="text-[#F0B90B] font-bold mt-0.5">⚠️</span>
                <div className="flex-1">
                  <p className="font-extrabold uppercase text-[8px] tracking-wider text-yellow-400">System maintenance bulletin</p>
                  <p className="text-gray-300 mt-0.5">{maintenanceAlertMsg}</p>
                </div>
              </div>
            )}
            
            {/* Tab: MARKETS (Flagship Market Ticker) */}
            {activeTab === 'markets' && (
              <div>
                {/* Header Profile display */}
                <div className="flex justify-between items-center mb-5 mt-2">
                  <div className="flex items-center gap-2">
                    <div className="w-7 h-7 rounded-lg bg-gradient-to-br from-[#7047EB] to-[#9167FF] flex items-center justify-center font-black text-white text-xs select-none">
                      TN
                    </div>
                    <div>
                      <h4 className="text-xs font-black tracking-tight text-white uppercase">TRADENEXA<span className="text-[#00FFA3] font-bold">.AI</span></h4>
                      <p className="text-[9px] text-gray-500 font-mono uppercase tracking-widest leading-none mt-0.5">Bybit Linear Market feed</p>
                    </div>
                  </div>
                  <button onClick={() => { setActiveTab('profile'); }} className="w-6 h-6 rounded-full bg-[#151619] border border-[#222] flex items-center justify-center text-xs text-[#00FFA3] select-none cursor-pointer">
                    👤
                  </button>
                </div>

                {/* Display Ad Slot depending on placement (for Free users) */}
                {user.plan === 'free' && campaigns.find(c => c.placement === 'market') && (
                  <div className="mb-4 bg-gradient-to-r from-[#7047EB] to-[#9167FF] rounded-2xl p-4 relative overflow-hidden shadow-sm">
                    <div className="absolute -right-4 -bottom-4 opacity-20">
                      <svg width="100" height="100" viewBox="0 0 24 24" fill="white"><path d="M13 10V3L4 14H11V21L20 10H13Z"/></svg>
                    </div>
                    <div className="relative z-10">
                      <span className="text-[8px] bg-white/20 text-white font-bold px-2 py-0.5 rounded uppercase tracking-wider">PREMIUM SPONSORED</span>
                      <h3 className="text-xs font-bold text-white mt-1.5">{campaigns.find(c => c.placement === 'market')?.title}</h3>
                      <button onClick={() => setActiveTab('profile')} className="mt-2.5 bg-white text-[#7047EB] text-[9px] font-black px-3 py-1 rounded-full uppercase hover:bg-white/95 transition select-none">
                        Upgrade Pro
                      </button>
                    </div>
                  </div>
                )}

                {/* Hot system bulletins notices */}
                <div className="mb-4 bg-[#151619] border border-[#222] rounded-2xl p-3.5 space-y-2">
                  <span className="text-[9px] font-bold text-[#00FFA3] uppercase tracking-widest font-mono">Bulletins Channel</span>
                  <div className="space-y-1.5 pt-1.5 border-t border-[#222]">
                    {bulletins.map(b => (
                      <div key={b.id} className="text-[10px]">
                        <h5 className="font-bold text-gray-200 leading-snug">📣 {b.title}</h5>
                        <p className="text-[9px] text-gray-500 leading-relaxed truncate mt-0.5">{b.content}</p>
                      </div>
                    ))}
                  </div>
                </div>

                {/* Custom price alerts dynamic setter panel */}
                <div className="mb-4 bg-[#151619] border border-[#222] rounded-3xl p-4.5 space-y-3">
                  <span className="text-[9px] font-bold text-[#F0B90B] uppercase tracking-widest font-mono flex items-center gap-1.5">
                    <span className="w-1.5 h-1.5 bg-[#F0B90B] rounded-full animate-ping"></span>
                    Set Real-Time Bybit Price Alarm
                  </span>
                  <form onSubmit={(e) => {
                    e.preventDefault();
                    const formdata = new FormData(e.currentTarget);
                    const sym = String(formdata.get('alert_sym') || 'BTCUSDT');
                    const target = Number(formdata.get('alert_price') || 0);
                    const direction = String(formdata.get('alert_direction') || 'above') as 'above' | 'below';
                    
                    if (!target || target <= 0) {
                      triggerFlash('error', 'Select valid trigger target index.');
                      return;
                    }
                    const newAlert = {
                      id: Date.now(),
                      symbol: sym,
                      targetPrice: target,
                      direction,
                      active: true
                    };
                    setPriceAlerts(prev => [newAlert, ...prev]);
                    triggerFlash('success', `Alert armed! Trigger when ${sym} goes ${direction} $${target}`);
                    e.currentTarget.reset();
                  }} className="space-y-2 text-xs text-gray-300 font-mono">
                    <div className="grid grid-cols-2 gap-2">
                      <select name="alert_sym" className="bg-[#0A0A0B] border border-[#222] text-white rounded-xl px-2 py-1 focus:outline-none text-[10px] font-bold">
                        <option value="BTCUSDT">BTCUSDT</option>
                        <option value="ETHUSDT">ETHUSDT</option>
                        <option value="SOLUSDT">SOLUSDT</option>
                        <option value="ADAUSDT">ADAUSDT</option>
                      </select>
                      <select name="alert_direction" className="bg-[#0A0A0B] border border-[#222] text-white rounded-xl px-2 py-1 focus:outline-none text-[10px] font-bold">
                        <option value="above">ABOVE (📈)</option>
                        <option value="below">BELOW (📉)</option>
                      </select>
                    </div>
                    <div className="flex gap-2">
                      <input
                        type="number"
                        name="alert_price"
                        step="any"
                        placeholder="Alarm Level (68450)"
                        className="flex-1 bg-[#0A0A0B] border border-[#222] text-white rounded-xl px-2.5 py-1 focus:outline-none text-[10px]"
                      />
                      <button type="submit" className="bg-[#F0B90B] hover:bg-[#F0B90B]/90 text-black font-black px-3 py-1 rounded-xl transition cursor-pointer select-none text-[9px] uppercase font-sans">
                        Arm Alert
                      </button>
                    </div>
                  </form>

                  {/* Armed notification logs lists */}
                  {priceAlerts.length > 0 && (
                    <div className="border-t border-[#222] pt-2 space-y-1 max-h-24 overflow-y-auto no-scrollbar font-mono text-[8px]">
                      <p className="text-gray-500 uppercase tracking-tight text-[7px] font-bold">Armed Alarms ({priceAlerts.filter(a => a.active).length})</p>
                      <div className="grid grid-cols-1 gap-1">
                        {priceAlerts.map(a => (
                          <div key={a.id} className="flex justify-between items-center bg-[#0a0a0b]/40 border border-[#222] p-1 rounded-lg">
                            <span className={`${a.active ? 'text-gray-300' : 'text-gray-500 line-through'}`}>
                              {a.symbol} {a.direction === 'above' ? '≥' : '≤'} ${a.targetPrice}
                            </span>
                            <span className={`px-1 py-0.5 rounded text-[6.5px] font-bold ${a.active ? 'bg-amber-950/30 text-amber-500' : 'bg-gray-900 text-gray-500'}`}>
                              {a.active ? 'Armed' : 'Crossed'}
                            </span>
                          </div>
                        ))}
                      </div>
                    </div>
                  )}
                </div>

                {/* Asset Table Header */}
                <h3 className="text-[10px] font-bold text-gray-500 uppercase tracking-widest mb-2.5 font-mono">Hot Contracts Tickers</h3>
                
                <div className="space-y-2.5">
                  {(Object.keys(tickerPrices) as Array<keyof typeof tickerPrices>).map(sym => {
                    const tick = tickerPrices[sym];
                    return (
                      <div
                        key={sym}
                        onClick={() => {
                          setSelectedSymbol(sym as any);
                          setActiveTab('charts');
                        }}
                        className="bg-[#151619] hover:bg-[#151619]/80 border border-[#222] rounded-2xl p-3 cursor-pointer transition flex justify-between items-center relative overflow-hidden active:scale-[0.98]"
                      >
                        <div className="flex items-center gap-2.5">
                          <span className={`w-1.5 h-1.5 rounded-full ${tick.change >= 0 ? 'bg-[#00FFA3]' : 'bg-[#FF4D4D]'}`}></span>
                          <div>
                            <h4 className="font-mono text-xs font-bold text-white">{sym}</h4>
                            <span className="text-[8px] text-gray-500 font-mono">Bybit Perpetual</span>
                          </div>
                        </div>

                        {/* Mid sparkline representation */}
                        <div className="w-12 h-6 flex gap-0.5 items-end opacity-40">
                          {Array.from({ length: 6 }).map((_, i) => (
                            <span
                              key={i}
                              style={{ height: `${20 + Math.random() * 80}%` }}
                              className={`w-1 rounded-sm ${tick.change >= 0 ? 'bg-[#00FFA3]' : 'bg-[#FF4D4D]'}`}
                            ></span>
                          ))}
                        </div>

                        <div className="text-right">
                          <p className="font-mono text-xs font-bold text-white">${tick.price.toLocaleString()}</p>
                          <span className={`font-mono text-[9px] font-bold ${tick.change >= 0 ? 'text-[#00FFA3]' : 'text-[#FF4D4D]'}`}>
                            {tick.change >= 0 ? '+' : ''}{tick.change}%
                          </span>
                        </div>
                      </div>
                    );
                  })}
                </div>

                {/* Ad banner in-feed targeting free level */}
                {user.plan === 'free' && campaigns.find(c => c.placement === 'in_feed') && (
                  <div className="mt-4 bg-[#151619] border border-[#222] rounded-2xl overflow-hidden shadow-sm">
                    <img src={campaigns.find(c => c.placement === 'in_feed')?.imageUrl} alt="Ad" className="w-full h-16 object-cover opacity-50" />
                    <div className="p-2.5 bg-[#151619] text-gray-300 text-[9px] flex justify-between items-center">
                      <span className="font-bold text-gray-200">🎯 {campaigns.find(c => c.placement === 'in_feed')?.title}</span>
                      <span className="text-[7.5px] text-[#00FFA3] font-mono uppercase font-black border border-[#00FFA3]/20 px-1.5 py-0.5 rounded bg-[#00FFA3]/5">Ad</span>
                    </div>
                  </div>
                )}
              </div>
            )}

            {/* Tab: CHARTS (Technical charting stage) */}
            {activeTab === 'charts' && (
              <div>
                <div className="mb-4 bg-[#151619] border border-[#222] rounded-3xl p-3.5 mt-2">
                  <div className="flex items-center justify-between mb-3">
                    <select
                      value={selectedSymbol}
                      onChange={(e) => setSelectedSymbol(e.target.value as any)}
                      className="bg-[#0A0A0B] border border-[#222] text-white rounded-xl px-2.5 py-1 text-xs font-extrabold font-mono focus:outline-none"
                    >
                      <option value="BTCUSDT">BTC/USDT Linear</option>
                      <option value="ETHUSDT">ETH/USDT Linear</option>
                      <option value="SOLUSDT">SOL/USDT Linear</option>
                      <option value="ADAUSDT">ADA/USDT Linear</option>
                    </select>

                    <span className="text-[8px] font-extrabold tracking-wider font-mono text-[#00FFA3] bg-[#00FFA3]/10 border border-[#00FFA3]/20 px-2 py-0.5 rounded-full uppercase font-display">
                      {user.plan === 'free' ? 'Basic Mode' : `${user.plan.toUpperCase()} Core`}
                    </span>
                  </div>

                  {/* Timeframes select line switcher */}
                  <div className="flex gap-1 border-t border-[#0A0A0B] pt-3">
                    {['1m', '5m', '15m', '1h', '1d'].map((tf) => (
                      <button
                        key={tf}
                        onClick={() => handleTimeframeChange(tf as any)}
                        className={`px-2.5 py-1 text-[10px] font-mono rounded font-bold transition-all duration-200 select-none cursor-pointer
                          ${timeframe === tf ? 'bg-[#00FFA3] text-black font-black' : 'bg-[#0A0A0B] border border-[#222] text-gray-400 hover:text-gray-200 hover:border-[#333]'}`}
                      >
                        {tf}
                      </button>
                    ))}
                  </div>
                </div>

                {/* SVG CANDLESTICK TERMINAL PLOT */}
                <div className="bg-[#151619] border border-[#222] rounded-3xl p-4.5 mb-4 relative overflow-hidden select-none">
                  
                  {/* Floating labels overlay */}
                  <div className="absolute top-4 left-4 z-10">
                    <h5 className="text-[10px] font-mono font-black text-gray-300 leading-none">
                      {selectedSymbol} <span className="text-gray-500">({timeframe})</span>
                    </h5>
                    {user.plan !== 'free' && indicatorsEnabled ? (
                      <div className="flex gap-2.5 mt-1.5 text-[8px] font-mono">
                        <span className="text-[#00FFA3] font-bold">EMA9: {candleData[candleData.length-1].ema9?.toFixed(1)}</span>
                        <span className="text-purple-400 font-bold">EMA21: {candleData[candleData.length-1].ema21?.toFixed(1)}</span>
                      </div>
                    ) : (
                      <span className="text-[8px] text-gray-500 block mt-1">Status: basic candle rendering only</span>
                    )}
                  </div>

                  <div className={`w-full h-44 bg-[#0A0A0B] rounded-2xl relative border border-[#222] mt-4.5 overflow-hidden flex items-end transition-all duration-300 ${isChangingTimeframe ? 'opacity-30 scale-[0.98] blur-[0.5px]' : 'opacity-100 scale-100 blur-0'}`}>
                    
                    {/* SVG canvas with key for retriggering css animation */}
                    <svg key={timeframe} className="absolute inset-0 w-full h-full chart-transition">
                      {/* Gridline guides */}
                      {[0.25, 0.5, 0.75].map((p, k) => (
                        <line key={k} x1="0" y1={`${176 * p}`} x2="350" y2={`${176 * p}`} stroke="#151619" strokeDasharray="3,3" />
                      ))}

                      {/* Render candlestick bodies & wicks */}
                      {candleData.slice(10, 40).map((d, i) => {
                        const step = 280 / 30;
                        const cx = i * step + 15;
                        const isBull = d.close >= d.open;
                        const color = isBull ? '#00FFA3' : '#FF4D4D';
                        
                        // Price mapping bounds
                        const loBound = d.rsi * 0.4;
                        const hiBound = d.rsi * 1.5;

                        return (
                          <g key={i}>
                            {/* Wick line */}
                            <line x1={cx} y1={50 + d.rsi * 0.5} x2={cx} y2={120 + d.rsi * 0.5} stroke={color} strokeWidth="1" />
                            {/* Body block */}
                            <rect
                              x={cx - 3}
                              y={Math.min(70 + d.rsi * 0.5, 90 + d.rsi * 0.5)}
                              width={6}
                              height={Math.max(2, Math.abs(d.close - d.open) * 0.05)}
                              fill={color}
                              rx="0.5"
                            />
                          </g>
                        );
                      })}
                    </svg>
                  </div>

                   {/* Indicators Controls Switch for paid levels */}
                  {user.plan !== 'free' ? (
                    <div className="mt-3.5 border-t border-[#222] pt-3 flex items-center justify-between">
                      <span className="text-[9px] text-gray-500 uppercase tracking-widest font-semibold font-mono">Telemetry overlays</span>
                      <button
                        onClick={() => setIndicatorsEnabled(!indicatorsEnabled)}
                        className={`text-[9px] font-bold px-2 py-1 rounded transition select-none cursor-pointer
                          ${indicatorsEnabled ? 'bg-[#00FFA3]/10 text-[#00FFA3] border border-[#00FFA3]/20' : 'bg-[#0A0A0B] text-gray-500 border border-[#222]'}`}
                      >
                        {indicatorsEnabled ? '✓ EMA 9/21 curves enabled' : 'indicators disabled'}
                      </button>
                    </div>
                  ) : (
                    <div className="mt-3.5 border-t border-[#222] pt-3 flex items-center justify-between text-[10px]">
                      <span className="text-gray-500">🚫 Advanced indicators disabled</span>
                      <button onClick={() => setActiveTab('profile')} className="text-[#00FFA3] font-bold hover:underline select-none cursor-pointer">
                        Upgrade
                      </button>
                    </div>
                  )}

                </div>

                {/* Oscillators data block */}
                <div className="bg-[#151619] border border-[#222] rounded-3xl p-4.5 text-xs text-gray-300">
                  <h4 className="font-bold text-white mb-3 uppercase tracking-wider text-[10px]">Technical Alarms Signals</h4>
                  <div className="space-y-2 text-[11px]">
                    <div className="flex justify-between items-center p-2.5 rounded-xl bg-[#0A0A0B] border border-[#222]">
                      <span>EMA9 / EMA21 Dynamic crossover</span>
                      <span className="font-bold text-[#00FFA3]">📈 BULLISH TRAIL</span>
                    </div>
                    <div className="flex justify-between items-center p-2.5 rounded-xl bg-[#0A0A0B] border border-[#222]">
                      <span>Relative Strength Index (RSI)</span>
                      <span className="font-mono font-bold text-gray-400">⚖ BALANCED index (52.4)</span>
                    </div>
                  </div>
                </div>
              </div>
            )}

            {/* Tab: AI SIGNALS FEED (Expert algorithm signals) */}
            {activeTab === 'signals' && (
              <div>
                <div className="mb-4 bg-[#151619] border border-[#222] rounded-3xl p-4.5 mt-2">
                  <div className="flex items-center justify-between mb-2">
                    <div className="flex items-center gap-1.5">
                      <span className="w-2 h-2 rounded-full bg-[#00FFA3] animate-pulse"></span>
                      <h3 className="text-xs font-black uppercase text-white font-mono">{t('signalHeader')}</h3>
                    </div>
                    <span className="text-[8px] font-bold bg-[#00FFA3]/1s text-[#00FFA3] border border-[#00FFA3]/20 px-1.5 py-0.5 rounded font-mono">
                      SSE ACTIVE
                    </span>
                  </div>
                  <p className="text-[10px] text-gray-500 leading-relaxed font-mono">
                    Linear trend indicators are parsed and evaluated. <strong className="text-gray-300">Click any card</strong> to view professional indicator factor breakdowns, Stop Loss justifications, and confidence ratings details.
                  </p>
                  <div className="mt-2.5 bg-[#0A0A0B]/60 border border-[#222]/80 px-2.5 py-1.5 rounded-xl flex justify-between items-center text-[9px] font-mono">
                    <span className="text-gray-400">System Signal Sensitivity:</span>
                    <span className={`font-black uppercase px-2 py-0.5 rounded text-[7.5px] tracking-widest border
                      ${signalSensitivity === 'LOW' 
                        ? 'bg-blue-950/40 text-blue-400 border-blue-900/40' 
                        : signalSensitivity === 'HIGH' 
                          ? 'bg-amber-950/40 text-amber-400 border-amber-900/40' 
                          : 'bg-emerald-950/40 text-[#00FFA3] border-emerald-900/40'}`}>
                      ⚡ {signalSensitivity} FILTER
                    </span>
                  </div>
                </div>

                <div className="space-y-4 font-mono">
                  {[
                    { sym: 'BTCUSDT', sig: 'BUY' as const, rate: 68420.5, confidence: signalSensitivity === 'LOW' ? 76 : signalSensitivity === 'HIGH' ? 98 : 91, strategy: 'EMA Golden Cross', tp: 70500.0, sl: 67200.0, notes: 'EMA9 is hovering above EMA21 with massive historical support. RSI is oversold. (Filtered under admin parameter settings).', ema9: 68512.4, ema21: 68290.8, rsi: 29.5 },
                    { sym: 'ETHUSDT', sig: 'SELL' as const, rate: 3512.4, confidence: signalSensitivity === 'LOW' ? 68 : signalSensitivity === 'HIGH' ? 93 : 84, strategy: 'RSI Overbought alarm', tp: 3380.0, sl: 3590.0, notes: 'The 1-hour candle relative strength index crossed the critical 70 bar, highlighting near-term bearish flags. (Filtered under admin parameter settings).', ema9: 3491.2, ema21: 3540.5, rsi: 74.2 },
                    { sym: 'SOLUSDT', sig: 'BUY' as const, rate: 146.4, confidence: signalSensitivity === 'LOW' ? 62 : signalSensitivity === 'HIGH' ? 89 : 78, strategy: 'Volume EMA validation', tp: 155.0, sl: 141.2, notes: 'Order book liquidity blocks show heavy support. Relative volatility suggests rapid TP target attainment. (Filtered under admin parameter settings).', ema9: 147.2, ema21: 144.1, rsi: 44.0 }
                  ].map((s) => (
                    <div
                      key={s.sym}
                      onClick={() => setSelectedSignalDetail(s as any)}
                      className="bg-[#151619] hover:bg-[#1C1D22] border border-[#222] rounded-3xl p-4.5 relative overflow-hidden transition cursor-pointer select-none active:scale-[0.99] group"
                    >
                      {/* Glow backdrop */}
                      <span className={`absolute top-0 right-0 w-24 h-24 rounded-full blur-2xl opacity-15 transition group-hover:opacity-25
                        ${s.sig === 'BUY' ? 'bg-[#00FFA3]' : 'bg-[#FF4D4D]'}`}></span>
                      
                      <div className="flex justify-between items-center mb-3">
                        <div>
                          <span className="font-mono text-xs font-extrabold text-white">{s.sym}</span>
                          <span className="text-[8px] text-gray-500 block">Click for breakdown ➜</span>
                        </div>
                        <span className={`text-[9px] font-black uppercase px-2 py-0.5 rounded-lg border font-mono
                          ${s.sig === 'BUY' 
                            ? 'bg-[#00FFA3]/1s text-[#00FFA3] border-[#00FFA3]/20 bg-[#00FFA3]/5' 
                            : 'bg-[#FF4D4D]/10 text-[#FF4D4D] border-[#FF4D4D]/20 bg-[#FF4D4D]/5'}`}>
                          {s.sig}
                        </span>
                      </div>

                      {/* Gated statistics parameters */}
                      <div className="grid grid-cols-3 gap-2 bg-[#0A0A0B] p-2.5 rounded-xl border border-[#222]/60 mb-3 text-center font-mono">
                        <div>
                          <span className="text-[7.5px] text-gray-500 uppercase font-black">Entry range</span>
                          <p className="text-[10px] font-bold text-gray-300 mt-0.5">${s.rate}</p>
                        </div>
                        <div>
                          <span className="text-[7.5px] text-gray-500 uppercase font-black">Take Profit</span>
                          <p className="text-[10px] font-bold text-[#00FFA3] mt-0.5">${s.tp}</p>
                        </div>
                        <div>
                          <span className="text-[7.5px] text-gray-500 uppercase font-black">Stop Loss</span>
                          <p className="text-[10px] font-bold text-[#FF4D4D] mt-0.5">${s.sl}</p>
                        </div>
                      </div>

                      {/* Decouple subscription permissions gating */}
                      <div className="border-t border-[#222] pt-2.5 space-y-1 text-[10px]">
                        <div className="flex justify-between">
                          <span className="text-gray-500">Confidence ratio:</span>
                          <span className="font-bold font-mono text-[#00FFA3]">
                            {user.plan === 'free' ? '🔒 Locked (Pro/VIP)' : `${s.confidence}% Accuracy`}
                          </span>
                        </div>
                        <div className="flex justify-between">
                          <span className="text-gray-500">Method Strategy:</span>
                          <span className="font-semibold text-gray-400 text-[9.5px]">
                            {user.plan === 'free' ? '🔒 Locked parameter' : s.strategy}
                          </span>
                        </div>
                      </div>

                    </div>
                  ))}
                </div>
              </div>
            )}

            {/* Tab: LEDGER STALKER BOOK (Wallet Statement & claim) */}
            {activeTab === 'ledger' && (
              <div>
                <div className="mb-4 bg-gradient-to-br from-[#151619] to-[#0A0A0B] border border-[#222] rounded-3xl p-4.5 mt-2 shadow-sm relative overflow-hidden">
                  <span className="text-[8px] tracking-widest font-mono text-[#00FFA3] font-extrabold uppercase">{t('auditBook')}</span>
                  
                  <div className="mt-2 grid grid-cols-2 gap-3 pb-2 border-b border-[#222]">
                    <div>
                      <span className="text-[8px] text-gray-400 uppercase font-bold tracking-tight">Main USDT Balance</span>
                      <h3 className="font-mono text-base font-black text-[#00FFA3] mt-0.5">${user.balance.toFixed(2)}</h3>
                    </div>
                    <div>
                      <span className="text-[8px] text-gray-400 uppercase font-bold tracking-tight">Cold TON Balance</span>
                      <h3 className="font-mono text-base font-black text-[#F0B90B] mt-0.5">{tonBalance.toFixed(2)} ? TON</h3>
                    </div>
                  </div>

                  {/* Connected wallet panel check */}
                  <div className="pt-3 text-[9px] font-mono flex items-center justify-between">
                    {connectedWallet ? (
                      <div className="flex items-center gap-1 text-[#00FFA3]">
                        <span className="w-1.5 h-1.5 bg-[#00FFA3] rounded-full animate-ping"></span>
                        <span>Wallet connected: {connectedWallet.substring(0,8)}...{connectedWallet.substring(connectedWallet.length - 6)}</span>
                      </div>
                    ) : (
                      <button
                        onClick={() => setShowWalletModal(true)}
                        className="bg-[#00FFA3]/10 hover:bg-[#00FFA3]/20 border border-[#00FFA3]/20 rounded-lg px-2 py-1 text-[#00FFA3] text-[8.5px] uppercase font-bold transition flex items-center gap-1 cursor-pointer"
                      >
                        <Wallet size={9} /> Connect wallet
                      </button>
                    )}
                    {connectedWallet && (
                      <button
                        onClick={() => {
                          setConnectedWallet(null);
                          triggerFlash('error', 'Wallet disconnected.');
                        }}
                        className="text-gray-500 hover:text-gray-300 transition underline font-sans cursor-pointer"
                      >
                        Disconnect
                      </button>
                    )}
                  </div>
                </div>

                {/* Instant In-App Convert Swap TON/USDT */}
                <div className="bg-[#151619] border border-[#222] rounded-3xl p-4.5 mb-4 text-xs">
                  <div className="flex items-center justify-between mb-2.5">
                    <h4 className="font-bold text-white uppercase tracking-wide text-[10px] flex items-center gap-1 font-mono">
                      <ArrowRightLeft size={10} className="text-[#00FFA3]" /> Inbuilt Sell & Buy TON/USDT
                    </h4>
                    <span className="text-[8px] text-gray-500 font-mono">1 TON ≈ $7.50 USD</span>
                  </div>
                  <div className="grid grid-cols-2 gap-2">
                    <button
                      onClick={() => {
                        if (user.balance < 75) {
                          triggerFlash('error', 'Requires min $75.00 USDT to buy.');
                          return;
                        }
                        debitLedger(75, 'Bought 10 TON via inbuilt converter');
                        setTonBalance(prev => prev + 10);
                        triggerFlash('success', 'Exchange complete! Swapped 75 USDT for 10 TON.');
                      }}
                      className="bg-[#0A0A0B] hover:bg-[#111] border border-[#222] hover:border-[#00FFA3]/40 rounded-xl p-2 text-center transition cursor-pointer"
                    >
                      <span className="text-[8px] uppercase text-gray-500 block font-bold">Buy 10 TON</span>
                      <span className="text-[#00FFA3] font-mono font-bold text-[10px]">-75 USDT / +10 TON</span>
                    </button>

                    <button
                      onClick={() => {
                        if (tonBalance < 10) {
                          triggerFlash('error', 'Insufficient TON balance (Requires min 10 TON).');
                          return;
                        }
                        setTonBalance(prev => prev - 10);
                        creditLedger(75, 'Inbuilt Convert: Sold 10 TON');
                        triggerFlash('success', 'Exchange success! Swapped 10 TON for 75 USDT.');
                      }}
                      className="bg-[#0A0A0B] hover:bg-[#111] border border-[#222] hover:border-[#FF4D4D]/40 rounded-xl p-2 text-center transition cursor-pointer"
                    >
                      <span className="text-[8px] uppercase text-gray-500 block font-bold">Sell 10 TON</span>
                      <span className="text-[#FF4D4D] font-mono font-bold text-[10px]">-10 TON / +75 USDT</span>
                    </button>
                  </div>
                </div>

                {/* Claim credits input */}
                <div className="bg-[#151619] border border-[#222] rounded-3xl p-4.5 mb-4 text-xs">
                  <h4 className="font-bold text-white uppercase tracking-wide mb-3 text-[10px] font-mono">{t('claimHeader')}</h4>
                  <form onSubmit={handleClaimCredits} className="flex gap-2.5">
                    <input
                      type="number"
                      name="amount"
                      defaultValue="500"
                      min="10"
                      max="5000"
                      className="flex-1 bg-[#0A0A0B] border border-[#222] focus:border-[#00FFA3] text-xs text-white rounded-xl px-2.5 py-2 font-mono"
                    />
                    <button type="submit" className="bg-[#00FFA3] hover:bg-[#00FFA3]/90 text-black font-black py-2 px-3.5 rounded-xl text-xs select-none transition cursor-pointer">
                      Deposit
                    </button>
                  </form>
                </div>

                {/* Ledger trail histories with Pagination */}
                <div className="flex justify-between items-center mb-2.5">
                  <h4 className="text-[10px] font-bold text-gray-500 uppercase tracking-widest font-mono">Ledger hashes statement</h4>
                  <span className="text-[9px] text-gray-500 font-mono">Page {ledgerPage} / {Math.ceil(ledgers.length / ledgerLimit)}</span>
                </div>
                
                <div className="space-y-2.5">
                  {ledgers.slice((ledgerPage - 1) * ledgerLimit, ledgerPage * ledgerLimit).map((l) => (
                    <div key={l.id} className="bg-[#151619] border border-[#222] rounded-2xl p-3 text-[11px] font-mono">
                      <div className="flex justify-between items-start mb-2">
                        <div>
                          <span className={`text-[8px] uppercase tracking-wider py-0.5 px-1 rounded border font-semibold
                            ${l.type === 'credit' 
                              ? 'bg-emerald-950/60 text-emerald-400 border-emerald-900/40' 
                              : 'bg-rose-950/60 text-rose-400 border-rose-900/40'}`}>
                            {l.type.toUpperCase()}
                          </span>
                          <h5 className="font-bold text-slate-200 mt-1 rounded leading-snug">{l.reason}</h5>
                        </div>
                        <span className={`font-black tracking-tighter ${l.type === 'credit' ? 'text-emerald-400' : 'text-slate-300'}`}>
                          {l.type === 'credit' ? '+' : '–'}${l.amount.toFixed(2)}
                        </span>
                      </div>
                      <div className="flex justify-between items-center text-[9px] text-zinc-500 border-t border-slate-950/50 pt-2 font-mono">
                        <span className="text-gray-500">{l.timestamp}</span>
                        <span className="text-gray-600 truncate max-w-28 font-semibold">Bal: ${l.balanceAfter.toFixed(2)}</span>
                      </div>
                    </div>
                  ))}
                  
                  {/* Ledger Pagination Controls */}
                  <div className="grid grid-cols-2 gap-2 mt-4 pt-2">
                    <button
                      disabled={ledgerPage <= 1}
                      onClick={() => setLedgerPage(prev => Math.max(1, prev - 1))}
                      className="bg-[#151619] border border-[#222] rounded-xl py-1 px-3 text-[10px] text-gray-400 hover:text-white disabled:opacity-30 disabled:cursor-not-allowed cursor-pointer transition font-mono font-bold"
                    >
                      ◀ Previous
                    </button>
                    <button
                      disabled={ledgerPage >= Math.ceil(ledgers.length / ledgerLimit)}
                      onClick={() => setLedgerPage(prev => prev + 1)}
                      className="bg-[#151619] border border-[#222] rounded-xl py-1 px-3 text-[10px] text-gray-400 hover:text-white disabled:opacity-30 disabled:cursor-not-allowed cursor-pointer transition font-mono font-bold"
                    >
                      Next Page ▶
                    </button>
                  </div>
                </div>
              </div>
            )}

            {/* Tab: MEMBERSHIPS & LEVEL GATING */}
            {activeTab === 'profile' && (
              <div>
                <div className="mb-4 bg-[#151619] border border-[#222] rounded-3xl p-4.5 mt-2 flex items-center gap-3">
                  <div className="w-9 h-9 rounded-xl bg-[#00FFA3]/10 border border-[#00FFA3]/20 flex items-center justify-center font-bold text-[#00FFA3] text-sm font-display">
                    {user.email.substring(0,1).toUpperCase()}
                  </div>
                  <div className="truncate">
                    <h4 className="font-black text-white leading-none truncate">{user.email}</h4>
                    <span className="text-[9px] text-gray-500 mt-1 font-mono">Membership Level: <strong className="text-[#00FFA3] uppercase">{user.plan}</strong></span>
                  </div>
                </div>

                <h3 className="text-[10px] font-bold text-gray-500 uppercase tracking-widest mb-3 font-mono">Buy subscription packages</h3>
                <div className="space-y-4">
                  {[
                    { key: 'pro' as const, name: 'Pro Strategy License', price: proTierPrice, benefits: 'Confidences numbers unlocked + 10s latency indicators.' },
                    { key: 'vip' as const, name: 'VIP Real-Time Sovereign', price: vipTierPrice, benefits: 'Zero latency metrics + immediate Bybit API feeds & zero Ads.' }
                  ].map((p) => (
                    <div key={p.key} className={`border rounded-3xl p-4.5 relative overflow-hidden transition
                      ${user.plan === p.key ? 'bg-[#151619] border-[#00FFA3]' : 'bg-[#151619]/60 border-[#222]'}`}>
                      
                      {user.plan === p.key && (
                        <span className="absolute top-3.5 right-3.5 text-[8px] font-bold text-[#00FFA3] uppercase bg-[#00FFA3]/10 border border-[#00FFA3]/20 px-2 py-0.5 rounded-full select-none font-mono">Active</span>
                      )}

                      <h4 className="font-black text-xs text-white leading-none">{p.name}</h4>
                      <p className="text-[10px] text-gray-500 font-mono mt-1">$ {p.price} / monthly (demo credits)</p>
                      
                      <p className="text-[10px] text-gray-400 leading-relaxed mt-3.5 bg-[#0A0A0B] p-2.5 rounded-xl border border-[#222] font-mono">{p.benefits}</p>

                      <div className="mt-4">
                        {user.plan === p.key ? (
                          <button disabled className="w-full text-center text-[10px] font-black py-2 rounded-xl bg-[#0A0A0B] text-gray-600 border border-[#222] select-none cursor-not-allowed">
                            License Active
                          </button>
                        ) : (
                          <button
                            onClick={() => handleUpgradeSubscription(p.key)}
                            className="w-full text-center text-[10px] font-black py-2 rounded-xl bg-gradient-to-r from-[#7047EB] to-[#9167FF] hover:opacity-95 text-white transition select-none cursor-pointer uppercase"
                          >
                            Buy / Upgrade package
                          </button>
                        )}
                      </div>

                    </div>
                  ))}
                </div>
              </div>
            )}

            {/* Tab: ADMIN STRATEGY CONTROL */}
            {activeTab === 'admin' && (
              <div className="space-y-5">
                
                {/* Header Information Dashboard Card */}
                <div className="bg-[#151619] border border-[#222] rounded-3xl p-4.5 mt-2 shadow-sm relative overflow-hidden">
                  <div className="absolute top-0 right-0 w-24 h-24 rounded-full bg-[#00FFA3]/5 blur-2xl pointer-events-none"></div>
                  <span className="text-[8px] uppercase tracking-widest font-mono text-[#00FFA3] font-black flex items-center gap-1.5">
                    <Shield size={10} className="text-[#00FFA3]" /> Administrative Core Cockpit
                  </span>
                  <h4 className="text-sm font-extrabold text-white mt-1">SaaS Global Variables Panel</h4>
                  <p className="text-[9.5px] text-gray-500 mt-1 leading-relaxed font-mono">
                    Configure real-time filter gains, adjust subscription billing levels, audit connection gateway nodes, and broadcast system-wide warnings immediately.
                  </p>
                </div>

                {/* Section 1: Signals Sensitivity globally switch */}
                <div className="bg-[#151619] border border-[#222] rounded-3xl p-4.5 text-xs font-mono">
                  <div className="flex items-center justify-between mb-3 border-b border-[#0A0A0B] pb-2">
                    <h5 className="font-bold text-white uppercase tracking-wider text-[10px] flex items-center gap-1">
                      <Sliders size={10} className="text-[#00FFA3]" /> Core Signal Sensitivity
                    </h5>
                    <span className="text-[7.5px] text-gray-500 font-mono">Active: {signalSensitivity}</span>
                  </div>
                  
                  <p className="text-[9.5px] text-gray-400 mb-3.5 leading-relaxed">
                    Adjusting the algorithm sensitivity dynamically calibrates confidence metrics and accuracy margins shown to subscribers in real-time.
                  </p>

                  <div className="grid grid-cols-3 gap-2">
                    {(['LOW', 'MEDIUM', 'HIGH'] as const).map((lvl) => (
                      <button
                        key={lvl}
                        onClick={() => {
                          setSignalSensitivity(lvl);
                          triggerFlash('success', `Global filter sensitivity switched to ${lvl} mode!`);
                        }}
                        className={`py-2 px-1 rounded-xl text-[9px] font-bold border transition cursor-pointer text-center uppercase
                          ${signalSensitivity === lvl 
                            ? 'bg-[#00FFA3]/10 text-[#00FFA3] border-[#00FFA3]/30 shadow-md shadow-[#00FFA3]/5' 
                            : 'bg-[#0A0A0B] text-gray-400 border-[#222] hover:text-gray-200'}`}
                      >
                        {lvl === 'LOW' ? '📉 LOW' : lvl === 'HIGH' ? '📈 HIGH (MAX)' : '⚖ MEDIUM'}
                      </button>
                    ))}
                  </div>

                  <div className="mt-3 bg-[#0A0A0B] p-2.5 rounded-xl border border-[#222]/60 text-[8.5px] text-gray-500 space-y-1">
                    <p className="font-semibold text-gray-400">⚡ Sensitivity Weighting Breakdown:</p>
                    {signalSensitivity === 'LOW' && <p>• Conservative Mode. Clamps signal confidence estimates (e.g. BTCUSDT ~76%) to insulate retail traders from high-volatility false breakouts.</p>}
                    {signalSensitivity === 'MEDIUM' && <p>• Balanced Mode. Standard algorithmic weighting optimized for trend continuity (EMA 9/21 cross-verification parameters).</p>}
                    {signalSensitivity === 'HIGH' && <p>• Aggressive Mode. Expands indicator tolerances to trigger micro-trend spikes (BTCUSDT confidences scaled up to 98% accuracy thresholds!).</p>}
                  </div>
                </div>

                {/* Section 2: Bybit Connection Gateway API */}
                <div className="bg-[#151619] border border-[#222] rounded-3xl p-4.5 text-xs font-mono">
                  <div className="flex items-center justify-between mb-3 border-b border-[#0A0A0B] pb-2">
                    <h5 className="font-bold text-white uppercase tracking-wider text-[10px] flex items-center gap-1">
                      <RefreshCw size={10} className="text-[#00FFA3]" /> Bybit API Connection Endpoint
                    </h5>
                    
                    <div className="flex items-center gap-1">
                      <span className={`w-1.5 h-1.5 rounded-full ${bybitApiConnectStatus === 'CONNECTED' ? 'bg-[#00FFA3] animate-pulse' : bybitApiConnectStatus === 'TESTING' ? 'bg-yellow-400 animate-ping' : 'bg-red-500'}`}></span>
                      <span className="text-[7.5px] text-gray-500 uppercase tracking-tight font-black">{bybitApiConnectStatus}</span>
                    </div>
                  </div>

                  <form onSubmit={(e) => {
                    e.preventDefault();
                    setBybitApiConnectStatus('TESTING');
                    triggerFlash('success', 'Pinging selected REST gateway network node...');
                    setTimeout(() => {
                      setBybitApiConnectStatus('CONNECTED');
                      triggerFlash('success', 'REST Integration verified successfully! Latency: 16ms.');
                    }, 800);
                  }} className="space-y-3">
                    <div>
                      <label className="block text-gray-500 text-[8px] font-bold uppercase mb-1">Endpoints REST Gateway URL</label>
                      <input
                        type="text"
                        value={bybitApiEndpoint}
                        onChange={(e) => setBybitApiEndpoint(e.target.value)}
                        className="w-full bg-[#0A0A0B] border border-[#222] px-2.5 py-1.5 rounded-xl text-white text-[10px] uppercase tracking-wide focus:border-[#00FFA3] focus:outline-none"
                      />
                    </div>

                    <div className="grid grid-cols-2 gap-2">
                      <div>
                        <label className="block text-gray-500 text-[8px] font-bold uppercase mb-1">API Key Identifier</label>
                        <input
                          type="text"
                          value={bybitApiKey}
                          onChange={(e) => setBybitApiKey(e.target.value)}
                          className="w-full bg-[#0A0A0B] border border-[#222] px-2.5 py-1.5 rounded-xl text-white text-[10px] focus:border-[#00FFA3] focus:outline-none font-mono"
                        />
                      </div>
                      <div>
                        <label className="block text-gray-500 text-[8px] font-bold uppercase mb-1">API Private Secret</label>
                        <input
                          type="password"
                          value={bybitApiSecret}
                          onChange={(e) => setBybitApiSecret(e.target.value)}
                          className="w-full bg-[#0A0A0B] border border-[#222] px-2.5 py-1.5 rounded-xl text-white text-[10px] focus:border-[#00FFA3] focus:outline-none font-mono"
                        />
                      </div>
                    </div>

                    <div className="flex gap-2">
                      <button
                        type="button"
                        onClick={() => {
                          setBybitApiConnectStatus('DISCONNECTED');
                          triggerFlash('error', 'Bybit API gateway connection credentials cleared/offline!');
                        }}
                        className="bg-rose-950/40 hover:bg-rose-950/60 border border-rose-900/40 hover:border-rose-900/60 text-rose-400 py-2 px-3 rounded-xl text-[9px] font-bold uppercase transition cursor-pointer"
                      >
                        Disconnect Gateway
                      </button>
                      <button
                        type="submit"
                        className="flex-1 bg-[#00FFA3] hover:opacity-95 text-black py-2 rounded-xl text-[9.5px] font-black transition select-none cursor-pointer uppercase font-sans"
                      >
                        Save & Test Connection Node
                      </button>
                    </div>
                  </form>
                </div>

                {/* Section 3: Subscription plans pricing engine adjustment */}
                <div className="bg-[#151619] border border-[#222] rounded-3xl p-4.5 text-xs font-mono">
                  <div className="flex items-center justify-between mb-3 border-b border-[#0A0A0B] pb-2">
                    <h5 className="font-bold text-white uppercase tracking-wider text-[10px] flex items-center gap-1">
                      <Coins size={10} className="text-[#00FFA3]" /> Subscription Cost Engine
                    </h5>
                    <span className="text-[7.5px] text-gray-500 font-mono">Live update</span>
                  </div>

                  <p className="text-[9.5px] text-gray-400 mb-3.5 leading-relaxed">
                    Alter demo license pricing for strategy level upgrades. Rates seamlessly carry over into user upgrade blocks.
                  </p>

                  <form onSubmit={(e) => {
                    e.preventDefault();
                    triggerFlash('success', 'Subscription billing prices updated inside cockpit database!');
                  }} className="space-y-3">
                    <div className="grid grid-cols-2 gap-3.5">
                      <div>
                        <label className="block text-gray-500 text-[8px] font-bold uppercase mb-1">PRO Strategic Level ($)</label>
                        <input
                          type="number"
                          step="0.01"
                          min="1"
                          value={proTierPrice}
                          onChange={(e) => setProTierPrice(Number(e.target.value))}
                          className="w-full bg-[#0A0A0B] border border-[#222] px-2.5 py-1.5 rounded-xl text-white text-[11px] font-bold focus:border-[#00FFA3] focus:outline-none"
                        />
                      </div>
                      <div>
                        <label className="block text-gray-500 text-[8px] font-bold uppercase mb-1">VIP Real-Time Level ($)</label>
                        <input
                          type="number"
                          step="0.01"
                          min="1"
                          value={vipTierPrice}
                          onChange={(e) => setVipTierPrice(Number(e.target.value))}
                          className="w-full bg-[#0A0A0B] border border-[#222] px-2.5 py-1.5 rounded-xl text-white text-[11px] font-bold focus:border-[#00FFA3] focus:outline-none"
                        />
                      </div>
                    </div>

                    <button
                      type="submit"
                      className="w-full bg-gradient-to-r from-[#7047EB] to-[#9167FF] hover:opacity-95 text-white py-2 rounded-xl text-[9.5px] font-bold transition select-none cursor-pointer uppercase"
                    >
                      ✓ Update License billing Rates
                    </button>
                  </form>
                </div>

                {/* Section 4: System maintenance warnings editor */}
                <div className="bg-[#151619] border border-[#222] rounded-3xl p-4.5 text-xs font-mono">
                  <div className="flex items-center justify-between mb-3 border-b border-[#0A0A0B] pb-2">
                    <h5 className="font-bold text-white uppercase tracking-wider text-[10px] flex items-center gap-1">
                      <Bell size={10} className="text-[#00FFA3]" /> System Maintenance Warning
                    </h5>
                    
                    {/* Toggle Indicator Button */}
                    <button
                      type="button"
                      onClick={() => {
                        setMaintenanceAlertActive(!maintenanceAlertActive);
                        triggerFlash('success', `Maintenance system alerts turned ${!maintenanceAlertActive ? 'ACTIVE' : 'DEACTIVATED'} globally.`);
                      }}
                      className={`text-[8px] font-bold uppercase px-2 py-0.5 rounded transition cursor-pointer select-none border font-mono
                        ${maintenanceAlertActive 
                          ? 'bg-[#F0B90B]/10 text-[#F0B90B] border-[#F0B90B]/20 bg-[#F0B90B]/5' 
                          : 'bg-[#0A0A0B] text-gray-500 border-[#222]'}`}
                    >
                      {maintenanceAlertActive ? '✓ Active warning' : 'downtime offline'}
                    </button>
                  </div>

                  <p className="text-[9.5px] text-gray-400 mb-3.5 leading-relaxed">
                    Broadcast a prominent alert banner regarding hardware backup operations or trading engine system maintenance.
                  </p>

                  <div className="space-y-3">
                    <div>
                      <label className="block text-gray-500 text-[8px] font-bold uppercase mb-1">Bulletin warning message</label>
                      <textarea
                        value={maintenanceAlertMsg}
                        onChange={(e) => setMaintenanceAlertMsg(e.target.value)}
                        className="w-full bg-[#0A0A0B] border border-[#222] px-2.5 py-1.5 rounded-xl text-white text-[10px] focus:border-[#00FFA3] focus:outline-none font-mono"
                        rows={2}
                      ></textarea>
                    </div>

                    <button
                      type="button"
                      onClick={() => {
                        triggerFlash('success', 'Global system maintenance message synchronized!');
                      }}
                      className="w-full bg-[#F0B90B] hover:opacity-95 text-black font-black py-2 rounded-xl text-[9.5px] transition select-none cursor-pointer uppercase font-sans"
                    >
                      Publish Alert Bulletin
                    </button>
                  </div>
                </div>

                {/* Section 5: Original bulletins and campaign ads (fully preserved) */}
                <div className="bg-[#151619] border border-[#222] rounded-3xl p-4.5 text-xs font-mono">
                  <h4 className="font-bold text-white border-b border-[#0A0A0B] pb-2 mb-3 uppercase tracking-wider text-[10px] flex items-center gap-1">
                    <Send size={10} className="text-[#00FFA3]" /> Publish Global bulletin list
                  </h4>
                  <form onSubmit={handleAdminPublishNotice} className="space-y-3">
                    <div>
                      <label className="block text-gray-500 text-[8px] font-bold uppercase mb-1">Headline</label>
                      <input type="text" name="title" defaultValue="EMA Index Cross Spikes Real-time" className="w-full bg-[#0A0A0B] border border-[#222] px-2.5 py-1.5 rounded-xl text-white focus:border-[#00FFA3] focus:outline-none text-[10px]" />
                    </div>
                    <div>
                      <label className="block text-gray-500 text-[8px] font-bold uppercase mb-1">Detailed text notice</label>
                      <textarea name="content" defaultValue="The RSI 14 oscillates at support baseline bounds. Check your alerts feed." className="w-full bg-[#0A0A0B] border border-[#222] px-2.5 py-1.5 rounded-xl text-white focus:border-[#00FFA3] focus:outline-none text-[10px]" rows={2}></textarea>
                    </div>
                    <button type="submit" className="w-full bg-[#0A0A0B] hover:bg-[#111] border border-[#222] hover:border-[#00FFA3]/40 text-gray-300 hover:text-white py-2 rounded-xl text-[9.5px] font-bold transition select-none cursor-pointer uppercase">
                      Publish glob notices
                    </button>
                  </form>
                </div>

                {/* Register dynamic Ad */}
                <div className="bg-[#151619] border border-[#222] rounded-3xl p-4.5 text-xs font-mono">
                  <h4 className="font-bold text-white border-b border-[#0A0A0B] pb-2 mb-3 uppercase tracking-wider text-[10px] flex items-center gap-1">
                    <Zap size={10} className="text-[#00FFA3]" /> Create Marketing Ad campaigns
                  </h4>
                  <form onSubmit={handleAdminCreateAd} className="space-y-3">
                    <div>
                      <label className="block text-gray-500 text-[8px] font-bold uppercase mb-1">Campaign Slogan Header</label>
                      <input type="text" name="heading" defaultValue="Binance VIP Integration Complete" className="w-full bg-[#0A0A0B] border border-[#222] px-2.5 py-1.5 rounded-xl text-white focus:border-[#00FFA3] focus:outline-none text-[10px]" />
                    </div>
                    <div>
                      <label className="block text-gray-500 text-[8px] font-bold uppercase mb-1">Graphic Image URL address</label>
                      <input type="url" name="url" defaultValue="https://images.unsplash.com/photo-1621510456681-23a23cfb5f57?auto=format&fit=crop&w=600&q=80" className="w-full bg-[#0A0A0B] border border-[#222] px-2.5 py-1.5 rounded-xl text-white focus:border-[#00FFA3] focus:outline-none text-[10px]" />
                    </div>
                    <button type="submit" className="w-full bg-[#0A0A0B] hover:bg-[#111] border border-[#222] hover:border-[#00FFA3]/40 text-gray-300 hover:text-white py-2 rounded-xl text-[9.5px] font-bold transition select-none cursor-pointer uppercase">
                      Deploy Advertising Banner
                    </button>
                  </form>
                </div>

              </div>
            )}

          </div>

          {/* PERSISTENT BOTTOM NAVIGATION TAB TOUCH BAR */}
          <div className="absolute bottom-0 inset-x-0 h-[68px] bg-[#0A0A0B] border-t border-[#1f1f1f] flex justify-around items-center px-2 z-30 select-none">
            {[
              { id: 'markets' as const, label: 'Markets', icon: <TrendingUp size={16} /> },
              { id: 'charts' as const, label: 'Charts', icon: <Activity size={16} /> },
              { id: 'signals' as const, label: 'AI Signals', icon: <Radio size={16} /> },
              { id: 'ledger' as const, label: 'Ledger', icon: <Coins size={16} /> },
              { id: 'profile' as const, label: 'Billing', icon: <User size={16} /> },
              { id: 'admin' as const, label: 'Admin', icon: <Shield size={16} /> }
            ].map((cfg) => (
              <button
                key={cfg.id}
                onClick={() => setActiveTab(cfg.id)}
                className={`flex flex-col items-center justify-center w-12 h-12 rounded-xl transition select-none cursor-pointer
                  ${activeTab === cfg.id ? 'text-[#00FFA3] bg-[#00FFA3]/5' : 'text-gray-500 hover:text-gray-300'}`}
              >
                {cfg.icon}
                <span className="text-[8px] tracking-tight font-black mt-1 uppercase">{cfg.label}</span>
              </button>
            ))}
          </div>

        </div>

      </div>

    </div>
  );
}
