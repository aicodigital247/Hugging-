import React, { useState } from 'react';
import { CreditCard, ShieldCheck, CheckCircle2, Loader2, AlertCircle } from 'lucide-react';

interface PaystackSimProps {
  onSuccess: (amount: number) => void;
  triggerFlash: (type: 'success' | 'error', msg: string) => void;
  lang?: 'en' | 'es' | 'zh';
}

export default function PaystackSim({ onSuccess, triggerFlash, lang = 'en' }: PaystackSimProps) {
  const [amount, setAmount] = useState<string>('500');
  const [cardNumber, setCardNumber] = useState<string>('');
  const [expiry, setExpiry] = useState<string>('');
  const [cvv, setCvv] = useState<string>('');
  const [pin, setPin] = useState<string>('');
  const [step, setStep] = useState<'input' | 'payment-pipeline' | 'complete'>('input');
  const [pipelineIndex, setPipelineIndex] = useState<number>(0);

  const pipelineMilestones = [
    'Initializing secure Paystack session...',
    'Interfacing with Central Bank 3D secure network node...',
    'Verifying CVV checks at server gateway...',
    'Authorizing double-entry settlement balance keys...',
    'Completed! Syncing database indexes...'
  ];

  const handleCardFormat = (e: React.ChangeEvent<HTMLInputElement>) => {
    let value = e.target.value.replace(/\D/g, '');
    value = value.substring(0, 16);
    const matched = value.match(/.{1,4}/g);
    setCardNumber(matched ? matched.join(' ') : value);
  };

  const handleExpiryFormat = (e: React.ChangeEvent<HTMLInputElement>) => {
    let value = e.target.value.replace(/\D/g, '');
    value = value.substring(0, 4);
    if (value.length > 2) {
      setExpiry(value.substring(0, 2) + '/' + value.substring(2));
    } else {
      setExpiry(value);
    }
  };

  const executePaystackSimulation = (e: React.FormEvent) => {
    e.preventDefault();
    const amt = Number(amount);
    if (isNaN(amt) || amt <= 5 || amt > 5000) {
      triggerFlash('error', 'Paystack deposits restricted from $10 to $5000 USDT mock funds.');
      return;
    }
    if (cardNumber.replace(/\s/g, '').length < 16) {
      triggerFlash('error', 'Provide a valid 16-digit debit card number.');
      return;
    }
    if (expiry.length < 5) {
      triggerFlash('error', 'Provide expiry (MM/YY).');
      return;
    }
    if (cvv.length < 3) {
      triggerFlash('error', 'Provide CVV.');
      return;
    }
    if (pin.length < 4) {
      triggerFlash('error', 'Provide your 4-digit master card PIN.');
      return;
    }

    setStep('payment-pipeline');
    setPipelineIndex(0);

    // Timeline pipeline simulation stepper
    const timerArr = [1000, 2000, 3200, 4300, 5200];
    timerArr.forEach((time, index) => {
      setTimeout(() => {
        setPipelineIndex(index);
        if (index === timerArr.length - 1) {
          setTimeout(() => {
            setStep('complete');
            onSuccess(amt);
            triggerFlash('success', `Paystack deposit cleared! Account credited +$${amt.toFixed(2)} USDT.`);
          }, 800);
        }
      }, time);
    });
  };

  const resetForm = () => {
    setStep('input');
    setCardNumber('');
    setExpiry('');
    setCvv('');
    setPin('');
    setPipelineIndex(0);
  };

  return (
    <div className="bg-[#14151a] border border-[#222] rounded-2xl p-5 shadow-xl font-mono text-zinc-300">
      <div className="flex items-center justify-between border-b border-[#222] pb-3 mb-4">
        <div className="flex items-center gap-2">
          <CreditCard className="text-[#00FFA3]" size={16} />
          <h3 className="font-extrabold text-white text-xs uppercase tracking-wider">
            Paystack Settlement Gateway
          </h3>
        </div>
        <span className="text-[7.5px] text-gray-500 font-extrabold bg-[#00FFA3]/5 border border-[#00FFA3]/15 px-2 py-0.5 rounded uppercase">
          API v2 Live
        </span>
      </div>

      {step === 'input' && (
        <form onSubmit={executePaystackSimulation} className="space-y-3">
          <div className="bg-[#0b0c10] p-3 rounded-xl border border-[#222]/80 mb-1">
            <span className="text-[8px] text-gray-500 uppercase font-black block mb-1">
              Deposit Amount (USDT)
            </span>
            <div className="flex items-center">
              <span className="text-[#00FFA3] font-bold text-xs mr-1.5">$</span>
              <input
                type="number"
                value={amount}
                onChange={(e) => setAmount(e.target.value)}
                min="10"
                max="5000"
                className="w-full bg-transparent text-sm font-extrabold text-white text-sans focus:outline-none"
                placeholder="500"
                required
              />
            </div>
          </div>

          <div className="space-y-2.5">
            <div>
              <label className="block text-gray-500 text-[8px] font-bold uppercase mb-1">
                Debit Card Number
              </label>
              <input
                type="text"
                value={cardNumber}
                onChange={handleCardFormat}
                placeholder="4000 1234 5678 9010"
                className="w-full bg-[#0b0c10] border border-[#222] rounded-xl px-3 py-2 text-xs text-white uppercase focus:border-[#00FFA3] focus:outline-none"
                required
              />
            </div>

            <div className="grid grid-cols-2 gap-2">
              <div>
                <label className="block text-gray-500 text-[8px] font-bold uppercase mb-1">
                  Card Expiration
                </label>
                <input
                  type="text"
                  value={expiry}
                  onChange={handleExpiryFormat}
                  placeholder="MM/YY"
                  className="w-full bg-[#0b0c10] border border-[#222] rounded-xl px-3 py-2 text-xs text-white text-center focus:border-[#00FFA3] focus:outline-none"
                  required
                />
              </div>

              <div>
                <label className="block text-gray-500 text-[8px] font-bold uppercase mb-1">
                  Secure Code (CVV)
                </label>
                <input
                  type="password"
                  value={cvv}
                  onChange={(e) => setCvv(e.target.value.replace(/\D/g, '').substring(0, 3))}
                  placeholder="3-Digits"
                  className="w-full bg-[#0b0c10] border border-[#222] rounded-xl px-3 py-2 text-xs text-white text-center focus:border-[#00FFA3] focus:outline-none"
                  required
                />
              </div>
            </div>

            <div>
              <label className="block text-gray-500 text-[8px] font-bold uppercase mb-1">
                Pin Verification
              </label>
              <input
                type="password"
                value={pin}
                onChange={(e) => setPin(e.target.value.replace(/\D/g, '').substring(0, 4))}
                placeholder="4-Digit Secure PIN"
                className="w-full bg-[#0b0c10] border border-[#222] rounded-xl px-3 py-2 text-xs text-white tracking-widest text-center focus:border-[#00FFA3] focus:outline-none"
                required
              />
            </div>
          </div>

          <button
            type="submit"
            className="w-full bg-[#00FFA3] hover:bg-[#00FFA3]/90 text-black font-black py-2.5 rounded-xl text-xs transition uppercase select-none cursor-pointer tracking-wider font-sans mt-3"
          >
            Authorize Paystack Secure Checkout
          </button>
        </form>
      )}

      {step === 'payment-pipeline' && (
        <div className="py-6 flex flex-col items-center justify-center space-y-4">
          <Loader2 className="text-[#00FFA3] animate-spin" size={28} />
          <div className="text-center">
            <h4 className="text-white text-[11px] font-extrabold uppercase animate-pulse">
              SIMULATING TRANSACTION FLOW
            </h4>
            <div className="mt-4 max-w-sm space-y-2 text-left bg-[#0b0c10] border border-[#222] p-3 rounded-xl">
              {pipelineMilestones.map((milestone, idx) => {
                const isActive = idx === pipelineIndex;
                const isPassed = idx < pipelineIndex;
                return (
                  <div key={idx} className="flex items-center gap-2 text-[9px]">
                    <span className={`w-1.5 h-1.5 rounded-full
                      ${isActive ? 'bg-amber-400 animate-ping' : isPassed ? 'bg-[#00FFA3]' : 'bg-gray-800'}`}
                    />
                    <span className={isActive ? 'text-amber-400 font-bold' : isPassed ? 'text-gray-400 line-through' : 'text-gray-600'}>
                      {milestone}
                    </span>
                  </div>
                );
              })}
            </div>
          </div>
        </div>
      )}

      {step === 'complete' && (
        <div className="py-6 flex flex-col items-center justify-center space-y-3.5 text-center">
          <CheckCircle2 className="text-[#00FFA3] animate-bounce" size={32} />
          <div>
            <h4 className="text-white text-xs font-black uppercase">
              PAYMENT CLEARANCE APPROVED
            </h4>
            <p className="text-[10px] text-gray-500 mt-1 max-w-xs leading-relaxed">
              Reserves are synchronized with double-entry database accounts. Your transaction receipt has been printed on the ledger stream.
            </p>
          </div>
          <button
            onClick={resetForm}
            className="bg-[#1c1d24] border border-[#2d2e38] hover:text-white px-3.5 py-1.5 rounded-xl text-[10px] uppercase font-bold transition cursor-pointer"
          >
            Deposit Again
          </button>
        </div>
      )}

      <div className="mt-4 pt-3 border-t border-[#222]/60 flex items-center gap-1.5 text-[8px] text-gray-500 leading-none">
        <ShieldCheck className="text-emerald-500" size={10} />
        <span>Secured via PCI-DSS encryption layer standards.</span>
      </div>
    </div>
  );
}
