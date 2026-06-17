import React, { useState, useEffect } from 'react';
import { InvestmentVault } from '../types';
import { Coins, HelpCircle, ArrowUpRight, Percent, Calendar, CheckCircle2 } from 'lucide-react';

interface YieldVaultProps {
  onStake: (amount: number, token: 'USDT' | 'TON', apy: number, days: number) => void;
  vaults: InvestmentVault[];
  onClaimInterest: (vaultId: number) => void;
  triggerFlash: (type: 'success' | 'error', msg: string) => void;
  lang?: 'en' | 'es' | 'zh';
  balance: number;
  tonBalance: number;
}

export default function YieldVault({
  onStake,
  vaults,
  onClaimInterest,
  triggerFlash,
  lang = 'en',
  balance,
  tonBalance
}: YieldVaultProps) {
  const [stakeAmount, setStakeAmount] = useState<string>('100');
  const [stakeToken, setStakeToken] = useState<'USDT' | 'TON'>('USDT');
  const [stakeDays, setStakeDays] = useState<number>(30);

  // Derive APY based on selected duration and currency
  const getApy = () => {
    let base = stakeToken === 'USDT' ? 12.5 : 22.0; // Higher APY for TON due to volatility risks
    if (stakeDays === 7) return base;
    if (stakeDays === 15) return base + 3.5;
    return base + 8.0; // 30 days
  };

  const handleCreateStake = (e: React.FormEvent) => {
    e.preventDefault();
    const amt = Number(stakeAmount);
    if (isNaN(amt) || amt <= 0) {
      triggerFlash('error', 'Stake amount must be higher than zero.');
      return;
    }

    if (stakeToken === 'USDT' && balance < amt) {
      triggerFlash('error', 'Insufficient USDT balance to lock stake.');
      return;
    }
    if (stakeToken === 'TON' && tonBalance < amt) {
      triggerFlash('error', 'Insufficient TON reserves to lock stake.');
      return;
    }

    const activeApy = getApy();
    onStake(amt, stakeToken, activeApy, stakeDays);
    triggerFlash('success', `Locked $${amt} ${stakeToken} at ${activeApy}% APY for ${stakeDays} days!`);
    setStakeAmount('100');
  };

  return (
    <div className="bg-[#14151a] border border-[#222] rounded-2xl p-5 shadow-xl font-mono text-zinc-300">
      <div className="flex items-center justify-between border-b border-[#222] pb-3 mb-4">
        <div className="flex items-center gap-2">
          <Percent className="text-[#00FFA3]" size={16} />
          <h3 className="font-extrabold text-white text-xs uppercase tracking-wider">
            Smart Yield Vault & Staking Pools
          </h3>
        </div>
        <span className="text-[7.5px] text-[#F0B90B] font-extrabold bg-[#F0B90B]/5 border border-[#F0B90B]/15 px-2 py-0.5 rounded uppercase">
          Compounding Active
        </span>
      </div>

      <p className="text-[9.5px] text-gray-500 mb-4 leading-relaxed font-sans">
        Lock idle cryptos to back TradeNexa liquidity pools. Earn stable daily micro-yields compounded under automated smart custody.
      </p>

      {/* Stake Form */}
      <form onSubmit={handleCreateStake} className="space-y-3.5 bg-[#0b0c10]/40 border border-[#222]/80 p-3.5 rounded-xl">
        <div className="grid grid-cols-2 gap-2">
          <div>
            <label className="block text-gray-500 text-[8px] font-bold uppercase mb-1">Staking Coin</label>
            <div className="grid grid-cols-2 gap-1.5 p-0.5 bg-[#0b0c10] border border-[#222] rounded-lg">
              {(['USDT', 'TON'] as const).map(tok => (
                <button
                  type="button"
                  key={tok}
                  onClick={() => setStakeToken(tok)}
                  className={`py-1 text-[9px] font-bold rounded uppercase transition cursor-pointer text-center
                    ${stakeToken === tok ? 'bg-[#00FFA3] text-black' : 'text-gray-500 hover:text-gray-300'}`}
                >
                  {tok}
                </button>
              ))}
            </div>
          </div>

          <div>
            <label className="block text-gray-500 text-[8px] font-bold uppercase mb-1">Duration Term</label>
            <select
              value={stakeDays}
              onChange={(e) => setStakeDays(Number(e.target.value))}
              className="w-full bg-[#0b0c10] border border-[#222] text-white text-[10px] rounded-lg px-2 py-1 focus:outline-none font-bold"
            >
              <option value={7}>7 Days (Starter)</option>
              <option value={15}>15 Days (Plus)</option>
              <option value={30}>30 Days (Sovereign)</option>
            </select>
          </div>
        </div>

        <div className="grid grid-cols-2 gap-2.5 items-end">
          <div>
            <label className="block text-gray-500 text-[8px] font-bold uppercase mb-1">
              Lock principal ({stakeToken})
            </label>
            <input
              type="number"
              value={stakeAmount}
              onChange={(e) => setStakeAmount(e.target.value)}
              className="w-full bg-[#0b0c10] border border-[#222] rounded-lg px-2.5 py-1 text-xs text-white focus:border-[#00FFA3] focus:outline-none text-sans font-bold"
              required
            />
          </div>

          <div className="bg-[#0b0c10] border border-[#222] p-1.5 rounded-lg flex items-center justify-between text-[8px]">
            <div>
              <span className="text-gray-500">ESTIMATED YIELD APY:</span>
              <p className="text-[#00FFA3] text-[10px] font-bold">{getApy()}% APY</p>
            </div>
            <Coins size={12} className="text-gray-600" />
          </div>
        </div>

        <button
          type="submit"
          className="w-full bg-[#00FFA3] hover:opacity-95 text-black font-black py-2 rounded-xl text-[10px] uppercase transition cursor-pointer tracking-widest font-sans"
        >
          ✓ Lock Stake Vault
        </button>
      </form>

      {/* Active Stakes List */}
      <div className="mt-5 space-y-3.5">
        <h4 className="text-[9px] font-extrabold text-gray-400 uppercase tracking-widest border-l-2 border-[#00FFA3] pl-2 leading-none">
          Active Staked Pools ({vaults.length})
        </h4>

        {vaults.length === 0 ? (
          <div className="text-center py-5 bg-[#0b0c10]/40 border border-[#222]/50 rounded-xl text-[9px] text-gray-500">
            No active vaults locked. Lock capital above to start earning.
          </div>
        ) : (
          <div className="space-y-2.5">
            {vaults.map((vault) => {
              const progressRatio = ((30 - vault.daysRemaining) / 30) * 100;
              return (
                <div key={vault.id} className="bg-[#0b0c10]/80 p-3 rounded-xl border border-[#222] relative overflow-hidden transition hover:border-[#333]">
                  {/* Status indicators */}
                  <div className="flex justify-between items-center mb-1.5">
                    <div className="flex items-center gap-1.5">
                      <span className={`w-1.5 h-1.5 rounded-full ${vault.status === 'active' ? 'bg-[#00FFA3] animate-pulse' : 'bg-gray-500'}`} />
                      <span className="text-[10px] font-bold text-white uppercase">
                        {vault.amount} {vault.token} Staked
                      </span>
                    </div>
                    <span className="text-[8px] font-black text-[#00FFA3] bg-[#00FFA3]/5 border border-[#00FFA3]/15 px-1.5 py-0.5 rounded uppercase">
                      {vault.apy}% APY
                    </span>
                  </div>

                  {/* Profit summaries */}
                  <div className="grid grid-cols-2 gap-2 text-[9px] bg-[#14151a] p-2 rounded-lg border border-[#222]/50 mb-2">
                    <div>
                      <span className="text-gray-500 uppercase tracking-tight block">Accrued Interest:</span>
                      <p className="text-[#00FFA3] text-[10px] font-bold">
                        +{vault.interestAccrued.toFixed(6)} {vault.token === 'TON' ? 'TON' : 'USDT'}
                      </p>
                    </div>
                    <div className="text-right">
                      <span className="text-gray-500 uppercase tracking-tight block">Maturity Remaining:</span>
                      <p className="text-gray-300 font-bold">{vault.daysRemaining} Days left</p>
                    </div>
                  </div>

                  {/* Progress tracker bar */}
                  <div className="w-full bg-[#14151a] h-1 rounded-full overflow-hidden mb-2">
                    <div
                      className="bg-gradient-to-r from-[#00FFA3] to-[#00E5FF] h-full"
                      style={{ width: `${Math.min(100, Math.max(10, progressRatio))}%` }}
                    />
                  </div>

                  {/* Claim Button */}
                  {vault.interestAccrued > 0 && (
                    <button
                      onClick={() => onClaimInterest(vault.id)}
                      className="w-full text-center py-1.5 bg-[#14151a] hover:bg-[#1c1d24] border border-[#222] text-[#00FFA3] text-[8.5px] font-bold rounded-lg uppercase cursor-pointer select-none transition"
                    >
                      🎁 Claim Accrued Yield
                    </button>
                  )}
                </div>
              );
            })}
          </div>
        )}
      </div>
    </div>
  );
}
