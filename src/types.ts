/**
 * TradeNexa.com - Simulator Type Declarations
 */

export interface SimulatorUser {
  id: number;
  email: string;
  plan: 'free' | 'pro' | 'vip';
  status: 'active' | 'banned';
  balance: number;
  createdAt: string;
}

export interface Ledgers {
  id: number;
  type: 'credit' | 'debit';
  amount: number;
  reason: string;
  balanceAfter: number;
  timestamp: string;
}

export interface Signals {
  symbol: string;
  signal_type: 'BUY' | 'SELL' | 'HOLD';
  confidence: number;
  entry: number;
  tp: number;
  sl: number;
  rsi: number;
  ema9: number;
  ema21: number;
  strategy: string;
  notes: string;
}

export interface Bulletins {
  id: number;
  title: string;
  content: string;
  type: 'broadcast' | 'alert' | 'signal';
  createdAt: string;
}

export interface AdCampaigns {
  id: number;
  placement: 'banner' | 'in_feed' | 'market';
  title: string;
  imageUrl: string;
  linkUrl: string;
  active: boolean;
}

export interface FileCodeData {
  path: string;
  title: string;
  description: string;
  code: string;
}

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
  status: 'active' | 'completed';
}

export interface SseEvent {
  id: number;
  event: string;
  title: string;
  message: string;
  time: string;
}

export interface Drawing {
  id: string;
  type: 'line' | 'channel';
  x1: number;
  y1: number;
  x2: number;
  y2: number;
  color: string;
  channelHeight?: number;
}
