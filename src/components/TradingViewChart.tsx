import React, { useState, useRef, useEffect } from 'react';
import { Drawing } from '../types';
import { Activity, Sliders, Trash2, RotateCcw, TrendingUp, Cpu } from 'lucide-react';

interface TradingViewChartProps {
  symbol: string;
  timeframe: string;
  onTimeframeChange: (tf: '1m' | '5m' | '15m' | '1h' | '1d') => void;
  plan: 'free' | 'pro' | 'vip';
  indicatorsEnabled: boolean;
  setIndicatorsEnabled: (val: boolean) => void;
  drawings: Drawing[];
  setDrawings: React.Dispatch<React.SetStateAction<Drawing[]>>;
  selectedTool: 'none' | 'line' | 'channel';
  setSelectedTool: (tool: 'none' | 'line' | 'channel') => void;
  selectedDrawingColor: string;
  setSelectedDrawingColor: (col: string) => void;
  channelHeight: number;
  setChannelHeight: (val: number) => void;
  triggerFlash: (type: 'success' | 'error', msg: string) => void;
  candleData: any[];
  isChangingTimeframe: boolean;
  performanceMode?: boolean;
}

export default function TradingViewChart({
  symbol,
  timeframe,
  onTimeframeChange,
  plan,
  indicatorsEnabled,
  setIndicatorsEnabled,
  drawings,
  setDrawings,
  selectedTool,
  setSelectedTool,
  selectedDrawingColor,
  setSelectedDrawingColor,
  channelHeight,
  setChannelHeight,
  triggerFlash,
  candleData,
  isChangingTimeframe,
  performanceMode = false,
}: TradingViewChartProps) {
  const [chartType, setChartType] = useState<'candle' | 'area' | 'depth'>('candle');
  const [hoveredCandle, setHoveredCandle] = useState<any | null>(null);
  const chartSvgRef = useRef<SVGSVGElement | null>(null);
  const [drawingStart, setDrawingStart] = useState<{ x: number; y: number } | null>(null);
  const [currentDrawing, setCurrentDrawing] = useState<Drawing | null>(null);

  const getSvgCoordinates = (
    e: React.MouseEvent<SVGSVGElement> | React.TouchEvent<SVGSVGElement>
  ) => {
    if (!chartSvgRef.current) return null;
    const rect = chartSvgRef.current.getBoundingClientRect();
    
    let clientX, clientY;
    if ('touches' in e) {
      if (e.touches.length === 0) return null;
      clientX = e.touches[0].clientX;
      clientY = e.touches[0].clientY;
    } else {
      clientX = e.clientX;
      clientY = e.clientY;
    }
    
    return {
      x: clientX - rect.left,
      y: clientY - rect.top,
    };
  };

  const handleStartDrawing = (
    e: React.MouseEvent<SVGSVGElement> | React.TouchEvent<SVGSVGElement>
  ) => {
    if (selectedTool === 'none') return;
    if (e.cancelable) e.preventDefault();

    const coords = getSvgCoordinates(e);
    if (!coords) return;

    setDrawingStart(coords);
    setCurrentDrawing({
      id: 'preview',
      type: selectedTool === 'channel' ? 'channel' : 'line',
      x1: coords.x,
      y1: coords.y,
      x2: coords.x,
      y2: coords.y,
      color: selectedDrawingColor,
      channelHeight: channelHeight,
    });
  };

  const handleDragDrawing = (
    e: React.MouseEvent<SVGSVGElement> | React.TouchEvent<SVGSVGElement>
  ) => {
    if (selectedTool === 'none' || !drawingStart || !currentDrawing) return;
    if (e.cancelable) e.preventDefault();

    const coords = getSvgCoordinates(e);
    if (!coords) return;

    setCurrentDrawing(prev => {
      if (!prev) return null;
      return {
        ...prev,
        x2: coords.x,
        y2: coords.y,
      };
    });
  };

  const handleEndDrawing = () => {
    if (selectedTool === 'none' || !drawingStart || !currentDrawing) return;

    const dx = currentDrawing.x2 - currentDrawing.x1;
    const dy = currentDrawing.y2 - currentDrawing.y1;
    const distance = Math.sqrt(dx * dx + dy * dy);

    if (distance > 5) {
      const newDrawing: Drawing = {
        ...currentDrawing,
        id: 'drawing-' + Date.now(),
        channelHeight: channelHeight,
      };
      setDrawings(prev => [...prev, newDrawing]);
      triggerFlash('success', `Added ${currentDrawing.type === 'channel' ? 'trend channel' : 'trendline'} annotation!`);
    }

    setDrawingStart(null);
    setCurrentDrawing(null);
  };

  // Generated path for area chart
  const getAreaPath = () => {
    if (candleData.length === 0) return '';
    const step = 280 / 30;
    let path = `M 15 ${140 - (candleData[10].close % 100)}`;
    for (let i = 11; i < 40; i++) {
      const cx = (i - 10) * step + 15;
      const cy = 140 - (candleData[i].close % 100);
      path += ` L ${cx} ${cy}`;
    }
    // close path
    path += ` L ${(39 - 10) * step + 15} 180 L 15 180 Z`;
    return path;
  };

  const getAreaLinePath = () => {
    if (candleData.length === 0) return '';
    const step = 280 / 30;
    let path = `M 15 ${140 - (candleData[10].close % 100)}`;
    for (let i = 11; i < 40; i++) {
      const cx = (i - 10) * step + 15;
      const cy = 140 - (candleData[i].close % 100);
      path += ` L ${cx} ${cy}`;
    }
    return path;
  };

  // Generate curves for EMA indicator lines
  const getEmaPath = (type: 'ema9' | 'ema21') => {
    if (candleData.length === 0) return '';
    const step = 280 / 30;
    let path = '';
    for (let i = 10; i < 40; i++) {
      const cx = (i - 10) * step + 15;
      const val = type === 'ema9' ? candleData[i].ema9 : candleData[i].ema21;
      const cy = 140 - (val % 100);
      if (i === 10) {
        path += `M ${cx} ${cy}`;
      } else {
        path += ` L ${cx} ${cy}`;
      }
    }
    return path;
  };

  return (
    <div className="bg-[#14151a] border border-[#222] rounded-2xl p-4.5 shadow-xl select-none">
      {/* Header Info */}
      <div className="flex flex-wrap items-center justify-between gap-3 mb-4">
        <div className="flex items-center gap-3">
          <div className="bg-[#1c1d24] px-3 py-1.5 rounded-xl border border-[#2a2b34] flex items-center gap-2">
            <TrendingUp size={14} className="text-[#00FFA3]" />
            <span className="font-mono text-xs font-bold text-white uppercase">{symbol} Perpetual</span>
          </div>

          <span className="text-[9px] font-bold text-gray-500 uppercase tracking-widest hidden sm:inline">
            {timeframe} Interval Data
          </span>
        </div>

        <div className="flex items-center gap-2">
          {/* Chart Types selector */}
          <div className="bg-[#0b0c10] border border-[#222] p-0.5 rounded-lg flex">
            {(['candle', 'area', 'depth'] as const).map(type => (
              <button
                key={type}
                onClick={() => setChartType(type)}
                className={`px-2.5 py-1 text-[9px] font-mono font-bold uppercase rounded transition cursor-pointer
                  ${chartType === type ? 'bg-[#00FFA3]/10 text-[#00FFA3] border border-[#00FFA3]/20' : 'text-gray-500 hover:text-gray-300'}`}
              >
                {type}
              </button>
            ))}
          </div>

          <span className="text-[8px] font-extrabold tracking-wider font-mono text-[#00FFA3] bg-[#00FFA3]/10 border border-[#00FFA3]/20 px-2 py-1 rounded-full uppercase">
            {plan === 'free' ? 'Basic Mode' : `${plan.toUpperCase()} Core`}
          </span>
        </div>
      </div>

      {/* Timeframe selector toolbar */}
      <div className="flex items-center justify-between gap-2 bg-[#0b0c10] p-1.5 rounded-xl border border-[#222] mb-3">
        <div className="flex flex-wrap gap-1">
          {(['1m', '5m', '15m', '1h', '1d'] as const).map(tf => (
            <button
              key={tf}
              onClick={() => onTimeframeChange(tf)}
              className={`px-3 py-1 text-[10px] font-mono rounded font-black transition-all cursor-pointer uppercase
                ${timeframe === tf ? 'bg-[#00FFA3] text-black' : 'text-gray-400 hover:text-gray-200'}`}
            >
              {tf}
            </button>
          ))}
        </div>

        {performanceMode && (
          <div className="flex items-center gap-1.5 text-amber-500 text-[8.5px] font-mono leading-none bg-amber-500/10 border border-amber-500/20 px-2 py-1 rounded-lg">
            <Cpu size={10} className="animate-pulse" />
            <span>LIGHT MODE</span>
          </div>
        )}
      </div>

      {/* SVG Canvas drawing block */}
      <div className="relative">
        {/* Floating Ticker overlay details metadata */}
        <div className="absolute top-2.5 left-2.5 z-10 font-mono text-[9px] pointer-events-none space-y-0.5 select-none md:text-[10px]">
          <h5 className="font-extrabold text-[#00FFA3]">
            {symbol} <span className="text-gray-500">({timeframe})</span>
          </h5>
          {candleData.length > 0 && hoveredCandle ? (
            <div className="flex gap-2.5 text-[8.5px] text-gray-300">
              <span>O:<strong className="text-white">${hoveredCandle.open?.toFixed(1)}</strong></span>
              <span>H:<strong className="text-white">${hoveredCandle.high?.toFixed(1)}</strong></span>
              <span>L:<strong className="text-[#FF4D4D]">${hoveredCandle.low?.toFixed(1)}</strong></span>
              <span>C:<strong className="text-[#00FFA3]">${hoveredCandle.close?.toFixed(1)}</strong></span>
            </div>
          ) : candleData.length > 0 ? (
            <div className="flex gap-2.5 text-[8.5px] text-gray-400">
              <span>O:<strong className="text-gray-300">${candleData[candleData.length - 1].open?.toFixed(1)}</strong></span>
              <span>C:<strong className="text-gray-300">${candleData[candleData.length - 1].close?.toFixed(1)}</strong></span>
              <span>Vol:<strong className="text-gray-300">{candleData[candleData.length - 1].vol}</strong></span>
            </div>
          ) : null}

          {plan !== 'free' && indicatorsEnabled && chartType !== 'depth' && candleData.length > 0 && (
            <div className="flex gap-2.5 text-[8.5px] pt-1">
              <span className="text-emerald-400 font-bold">EMA(9): {candleData[candleData.length - 1].ema9?.toFixed(1)}</span>
              <span className="text-purple-400 font-bold">EMA(21): {candleData[candleData.length - 1].ema21?.toFixed(1)}</span>
            </div>
          )}
        </div>

        {/* Transition Loading overlays */}
        {isChangingTimeframe && (
          <div className="absolute inset-0 bg-[#0a0a0b]/80 z-20 flex flex-col items-center justify-center rounded-2xl gap-2 font-mono text-[10px] text-gray-400 border border-[#222]">
            <Activity className="text-[#00FFA3] animate-spin" size={18} />
            <span>Synchronizing candlestick blocks...</span>
          </div>
        )}

        <div className={`w-full h-52 bg-[#0b0c10] rounded-2xl relative border border-[#222] overflow-hidden flex items-end transition-all ${isChangingTimeframe ? 'opacity-20 blur-sm' : 'opacity-100 blur-none'}`}>
          <svg
            ref={chartSvgRef}
            onMouseDown={handleStartDrawing}
            onMouseMove={handleDragDrawing}
            onMouseUp={handleEndDrawing}
            onMouseLeave={handleEndDrawing}
            onTouchStart={handleStartDrawing}
            onTouchMove={handleDragDrawing}
            onTouchEnd={handleEndDrawing}
            className={`absolute inset-0 w-full h-full ${selectedTool !== 'none' ? 'cursor-crosshair touch-none select-none' : 'chart-transition'}`}
          >
            {/* Gridlines */}
            {[0.25, 0.5, 0.75].map((p, k) => (
              <line key={k} x1="0" y1={`${208 * p}`} x2="100%" y2={`${208 * p}`} stroke="#14151a" strokeDasharray="3,3" />
            ))}

            {/* AREA CHART MODE */}
            {chartType === 'area' && candleData.length > 0 && (
              <g>
                <defs>
                  <linearGradient id="areaGlow" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0%" stopColor="#00FFA3" stopOpacity="0.25" />
                    <stop offset="100%" stopColor="#00FFA3" stopOpacity="0" />
                  </linearGradient>
                </defs>
                <path d={getAreaPath()} fill="url(#areaGlow)" />
                <path d={getAreaLinePath()} fill="none" stroke="#00FFA3" strokeWidth="2.5" />
              </g>
            )}

            {/* CHART TYPE DEPTH BOOK */}
            {chartType === 'depth' && (
              <g>
                <path d="M 0 160 Q 60 150 140 180 Q 200 180 350 20 L 350 208 L 0 208 Z" fill="#FF4D4D" fillOpacity="0.1" />
                <path d="M 0 208 L 0 160 Q 60 150 140 180" fill="none" stroke="#FF4D4D" strokeWidth="2.5" />
                <path d="M 140 180 Q 200 180 350 20 L 350 208 Z" fill="#00FFA3" fillOpacity="0.1" />
                <path d="M 140 180 Q 200 180 350 20" fill="none" stroke="#00FFA3" strokeWidth="2.5" />
                <line x1="140" y1="0" x2="140" y2="208" stroke="#333" strokeDasharray="2,2" />
                <text x="145" y="195" fill="#888" className="font-mono text-[8px] font-bold">MID PRICE</text>
              </g>
            )}

            {/* CANDLESTICK PLOT MODE */}
            {chartType === 'candle' && candleData.length > 0 && (
              candleData.slice(10, 40).map((d, i) => {
                const step = 310 / 30;
                const cx = i * step + 15;
                const isBull = d.close >= d.open;
                const color = isBull ? '#00FFA3' : '#FF4D4D';
                
                // Scale values inside SVG (height 208)
                const mappedRsi = d.rsi;
                const oY = 160 - (d.open % 100);
                const cY = 160 - (d.close % 100);
                const hY = 160 - (d.high % 100);
                const lY = 160 - (d.low % 100);

                return (
                  <g 
                    key={i} 
                    onMouseEnter={() => setHoveredCandle(d)}
                    onMouseLeave={() => setHoveredCandle(null)}
                    className="cursor-pointer group"
                  >
                    {/* Wick */}
                    <line x1={cx} y1={hY} x2={cx} y2={lY} stroke={color} strokeWidth="1.2" />
                    {/* Shadow highlight on hover */}
                    <rect
                      x={cx - 6}
                      y={0}
                      width={12}
                      height={208}
                      fill="white"
                      fillOpacity="0"
                      className="group-hover:fill-opacity-[0.03]"
                    />
                    {/* Candle Body */}
                    <rect
                      x={cx - 3.5}
                      y={Math.min(oY, cY)}
                      width={7}
                      height={Math.max(2.5, Math.abs(cY - oY))}
                      fill={color}
                      rx={performanceMode ? 0 : 0.8}
                    />
                  </g>
                );
              })
            )}

            {/* RENDERING INDICATORS */}
            {plan !== 'free' && indicatorsEnabled && chartType !== 'depth' && candleData.length > 0 && (
              <g>
                <path d={getEmaPath('ema9')} fill="none" stroke="#10b981" strokeWidth="1.5" strokeOpacity="0.8" />
                <path d={getEmaPath('ema21')} fill="none" stroke="#7c3aed" strokeWidth="1.5" strokeOpacity="0.8" />
              </g>
            )}

            {/* Render Drawings */}
            {drawings.map((drawing) => {
              const h = drawing.channelHeight ?? -24;
              const isChannel = drawing.type === 'channel';
              return (
                <g key={drawing.id}>
                  {isChannel && (
                    <polygon
                      points={`${drawing.x1},${drawing.y1} ${drawing.x2},${drawing.y2} ${drawing.x2},${drawing.y2 + h} ${drawing.x1},${drawing.y1 + h}`}
                      fill={drawing.color}
                      fillOpacity="0.12"
                    />
                  )}
                  <line
                    x1={drawing.x1}
                    y1={drawing.y1}
                    x2={drawing.x2}
                    y2={drawing.y2}
                    stroke={drawing.color}
                    strokeWidth="1.8"
                    strokeDasharray={isChannel ? '2,2' : 'none'}
                  />
                  {isChannel && (
                    <line
                      x1={drawing.x1}
                      y1={drawing.y1 + h}
                      x2={drawing.x2}
                      y2={drawing.y2 + h}
                      stroke={drawing.color}
                      strokeWidth="1.8"
                    />
                  )}
                  <circle cx={drawing.x1} cy={drawing.y1} r="3" fill="#FFFFFF" stroke={drawing.color} strokeWidth="1" />
                  <circle cx={drawing.x2} cy={drawing.y2} r="3" fill="#FFFFFF" stroke={drawing.color} strokeWidth="1" />
                  {isChannel && (
                    <>
                      <circle cx={drawing.x1} cy={drawing.y1 + h} r="3" fill="#FFFFFF" stroke={drawing.color} strokeWidth="1" />
                      <circle cx={drawing.x2} cy={drawing.y2 + h} r="3" fill="#FFFFFF" stroke={drawing.color} strokeWidth="1" />
                    </>
                  )}
                </g>
              );
            })}

            {/* Active Drawing (While Dragging) */}
            {currentDrawing && (
              <g>
                {currentDrawing.type === 'channel' && (
                  <>
                    <polygon
                      points={`${currentDrawing.x1},${currentDrawing.y1} ${currentDrawing.x2},${currentDrawing.y2} ${currentDrawing.x2},${currentDrawing.y2 + channelHeight} ${currentDrawing.x1},${currentDrawing.y1 + channelHeight}`}
                      fill={currentDrawing.color}
                      fillOpacity="0.22"
                    />
                    <line
                      x1={currentDrawing.x1}
                      y1={currentDrawing.y1 + channelHeight}
                      x2={currentDrawing.x2}
                      y2={currentDrawing.y2 + channelHeight}
                      stroke={currentDrawing.color}
                      strokeWidth="2"
                    />
                  </>
                )}
                <line
                  x1={currentDrawing.x1}
                  y1={currentDrawing.y1}
                  x2={currentDrawing.x2}
                  y2={currentDrawing.y2}
                  stroke={currentDrawing.color}
                  strokeWidth="2"
                  strokeDasharray={currentDrawing.type === 'channel' ? '2,2' : 'none'}
                />
                <circle cx={currentDrawing.x1} cy={currentDrawing.y1} r="4" fill="#FFFFFF" stroke={currentDrawing.color} strokeWidth="1.5" />
                <circle cx={currentDrawing.x2} cy={currentDrawing.y2} r="4" fill="#FFFFFF" stroke={currentDrawing.color} strokeWidth="1.5" />
              </g>
            )}
          </svg>
        </div>
      </div>

      {/* DRAWING TOOLBAR AND ANNOTATION PARAMETERS */}
      <div className="mt-3.5 bg-[#0b0c10] border border-[#222] p-3 rounded-xl flex flex-col gap-2.5">
        <div className="flex items-center justify-between">
          <span className="text-[9px] font-extrabold text-[#00FFA3] uppercase tracking-widest font-mono flex items-center gap-1.5">
            <Sliders size={11} className="text-[#00FFA3]" />
            Technical Drawing Palette Tools
          </span>
          <div className="flex gap-1">
            <button
              onClick={() => {
                if (drawings.length > 0) {
                  setDrawings(prev => prev.slice(0, -1));
                  triggerFlash('success', 'Removed last trend drawing.');
                }
              }}
              disabled={drawings.length === 0}
              className="px-2 py-1 text-[8px] bg-[#1c1d24] disabled:opacity-30 text-gray-300 font-mono font-bold rounded-lg border border-[#2d2e38] transition flex items-center gap-1 cursor-pointer"
            >
              <RotateCcw size={8} /> Undo
            </button>
            <button
              onClick={() => {
                if (drawings.length > 0) {
                  setDrawings([]);
                  triggerFlash('success', 'Cleared chart canvas!');
                }
              }}
              disabled={drawings.length === 0}
              className="px-2 py-1 text-[8px] bg-red-950/20 hover:bg-red-950/30 disabled:opacity-30 text-red-400 font-mono font-bold rounded-lg border border-red-900/30 transition flex items-center gap-1 cursor-pointer"
            >
              <Trash2 size={8} /> Clear All
            </button>
          </div>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-2 gap-3 text-[10px]">
          {/* Tool Selector Buttons */}
          <div className="flex bg-[#14151a] border border-[#222] p-0.5 rounded-lg">
            {(['none', 'line', 'channel'] as const).map(tool => (
              <button
                key={tool}
                onClick={() => {
                  setSelectedTool(tool);
                  triggerFlash('success', tool === 'none' ? 'Restored standard zoom/pan touch interaction.' : `Active Annotation: ${tool.toUpperCase()}`);
                }}
                className={`flex-1 py-1 px-2 text-[8px] font-mono font-extrabold uppercase rounded transition text-center cursor-pointer
                  ${selectedTool === tool ? 'bg-[#00FFA3] text-black font-black' : 'text-gray-500 hover:text-gray-300'}`}
              >
                {tool === 'none' ? 'None' : tool === 'line' ? 'Trendline' : 'Channel'}
              </button>
            ))}
          </div>

          {/* Color selects dots */}
          <div className="flex justify-around items-center bg-[#14151a] border border-[#222] p-1 rounded-lg">
            {['#00FFA3', '#FF4D4D', '#F0B90B', '#7047EB', '#00E5FF'].map(col => (
              <button
                key={col}
                onClick={() => setSelectedDrawingColor(col)}
                style={{ backgroundColor: col }}
                className={`w-3.5 h-3.5 rounded-full border transition-all cursor-pointer
                  ${selectedDrawingColor === col ? 'scale-125 border-white ring-1 ring-[#00FFA3]' : 'border-black/50 hover:scale-110'}`}
              />
            ))}
          </div>
        </div>

        {/* Channel slider width adjuster */}
        {selectedTool === 'channel' && (
          <div className="flex items-center gap-2.5 bg-[#14151a] p-1.5 rounded-lg border border-[#222]/60 animate-fade-in">
            <span className="text-[7.5px] font-mono text-gray-400 uppercase tracking-tight font-black">
              Channel Distance: <strong>{Math.abs(channelHeight)}px</strong>
            </span>
            <input
              type="range"
              min="10"
              max="60"
              value={Math.abs(channelHeight)}
              onChange={(e) => setChannelHeight(-Number(e.target.value))}
              className="flex-1 h-1 bg-zinc-900 rounded-lg appearance-none cursor-pointer accent-[#00FFA3]"
            />
          </div>
        )}
      </div>

      {/* Advanced Telemetry overlays */}
      {plan !== 'free' ? (
        <div className="mt-3 border-t border-[#222] pt-3 flex items-center justify-between text-[9px]">
          <span className="text-gray-500 font-mono uppercase tracking-widest font-black">Indicators layer overlay</span>
          <button
            onClick={() => setIndicatorsEnabled(!indicatorsEnabled)}
            className={`px-3 py-1 text-[8.5px] font-bold font-mono rounded border transition cursor-pointer
              ${indicatorsEnabled ? 'bg-[#00FFA3]/10 text-[#00FFA3] border-[#00FFA3]/20' : 'bg-[#0b0c10] text-gray-500 border-[#222]'}`}
          >
            {indicatorsEnabled ? '✓ EMA 9/21 curves enabled' : 'Disabled'}
          </button>
        </div>
      ) : (
        <div className="mt-3 border-t border-[#222] pt-3 flex items-center justify-between text-[9px] font-mono">
          <span className="text-gray-500">🛡️ Indicator curves locked under basic tier</span>
          <span className="text-[#00FFA3] font-bold animate-pulse hover:underline cursor-pointer">Upgrade license plan</span>
        </div>
      )}
    </div>
  );
}
