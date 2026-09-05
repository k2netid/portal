<template>
  <div
    class="sarangenge-hero-visual relative w-full max-w-[540px] aspect-[4/3] sm:aspect-[16/11] flex items-center justify-center select-none"
    :class="`sarangenge-hero-visual--${currentType}`"
  >
    <!-- Ambient Warm Dawn & Teal Backdrop Glow -->
    <div class="absolute inset-0 -z-10 flex items-center justify-center pointer-events-none">
      <div class="w-3/4 h-3/4 rounded-full bg-amber-500/15 blur-3xl animate-pulse duration-1000" />
      <div class="w-1/2 h-1/2 rounded-full bg-teal-500/15 blur-2xl delay-300" />
    </div>

    <!-- PRESET 1: ENGINEERING & PRECISION CNC (TEACHING FACTORY) -->
    <div
      v-if="currentType === 'engineering'"
      class="w-full h-full relative flex items-center justify-center"
    >
      <svg
        viewBox="0 0 500 380"
        class="w-full h-full drop-shadow-[0_0_30px_rgba(245,158,11,0.25)]"
      >
        <defs>
          <linearGradient id="sarangengeEngGrad" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" stop-color="#f59e0b" stop-opacity="0.9" />
            <stop offset="50%" stop-color="#0f766e" stop-opacity="0.8" />
            <stop offset="100%" stop-color="#38bdf8" stop-opacity="0.8" />
          </linearGradient>
          <filter id="sarangengeGlow" x="-20%" y="-20%" width="140%" height="140%">
            <feGaussianBlur stdDeviation="3.5" result="blur" />
            <feMerge>
              <feMergeNode in="blur" />
              <feMergeNode in="SourceGraphic" />
            </feMerge>
          </filter>
        </defs>

        <!-- Blueprint Coordinate Grid -->
        <g class="stroke-white/10" stroke-width="1" stroke-dasharray="3 3">
          <line x1="50" y1="190" x2="450" y2="190" />
          <line x1="250" y1="40" x2="250" y2="340" />
          <circle cx="250" cy="190" r="140" fill="none" class="stroke-white/5" />
          <circle cx="250" cy="190" r="100" fill="none" class="stroke-amber-500/20" stroke-dasharray="4 4" />
        </g>

        <!-- Laser Axis Lines & Precision Machining Toolpaths -->
        <g stroke="url(#sarangengeEngGrad)" stroke-width="2" fill="none">
          <path d="M90 120 L250 190 L410 100" class="sarangenge-pulse-line" />
          <path d="M100 280 L250 190 L390 270" class="sarangenge-pulse-line" style="animation-delay: 1.1s;" />
          <path d="M90 120 L100 280" class="sarangenge-pulse-line" style="animation-delay: 0.5s;" />
          <path d="M410 100 L390 270" class="sarangenge-pulse-line" style="animation-delay: 1.6s;" />
        </g>

        <!-- Central High-Precision CNC Spindle & Planetary Gear System -->
        <g transform="translate(250, 190)">
          <!-- Concentric Pulse Radar Ring -->
          <circle r="52" fill="none" stroke="#f59e0b" stroke-width="1" class="animate-ping opacity-20" />
          <circle r="40" fill="#0a1128" stroke="#f59e0b" stroke-width="2" filter="url(#sarangengeGlow)" />
          <circle r="28" fill="#0f172a" stroke="#0f766e" stroke-width="1.5" />
          <circle r="10" fill="#f59e0b" class="animate-pulse" />

          <!-- Rotating Outer Gear Teeth Ring -->
          <g class="animate-spin duration-3000 origin-center">
            <circle r="36" fill="none" stroke="#f59e0b" stroke-width="1.5" stroke-dasharray="8 6" />
            <line x1="-36" y1="0" x2="36" y2="0" stroke="#f59e0b" stroke-width="1.5" />
            <line x1="0" y1="-36" x2="0" y2="36" stroke="#f59e0b" stroke-width="1.5" />
          </g>

          <!-- Counter-rotating Inner Gear -->
          <g class="animate-spin-reverse duration-2000 origin-center">
            <circle r="22" fill="none" stroke="#38bdf8" stroke-width="1" stroke-dasharray="4 4" />
          </g>
        </g>

        <!-- Node 1: CNC Milling 5-Axis (Top Left) -->
        <g transform="translate(90, 120)">
          <circle r="24" fill="#0a1128" stroke="#f59e0b" stroke-width="1.5" />
          <circle r="7" fill="#fbbf24" />
          <text x="0" y="36" text-anchor="middle" fill="#fde68a" font-size="9" font-family="monospace" font-weight="bold">CNC 5-AXIS</text>
        </g>

        <!-- Node 2: Robotic Laser Welding (Top Right) -->
        <g transform="translate(410, 100)">
          <circle r="24" fill="#0a1128" stroke="#0f766e" stroke-width="1.5" />
          <circle r="7" fill="#14b8a6" />
          <text x="0" y="36" text-anchor="middle" fill="#99f6e4" font-size="9" font-family="monospace" font-weight="bold">ROBOT WELDING</text>
        </g>

        <!-- Node 3: 3D CAD/CAM SolidWorks (Bottom Left) -->
        <g transform="translate(100, 280)">
          <circle r="22" fill="#0a1128" stroke="#38bdf8" stroke-width="1.5" />
          <circle r="6" fill="#38bdf8" />
          <text x="0" y="34" text-anchor="middle" fill="#bae6fd" font-size="9" font-family="monospace" font-weight="bold">CAD · CAM</text>
        </g>

        <!-- Node 4: Quality Inspection CMM (Bottom Right) -->
        <g transform="translate(390, 270)">
          <circle r="22" fill="#0a1128" stroke="#10b981" stroke-width="1.5" />
          <circle r="6" fill="#10b981" />
          <text x="0" y="34" text-anchor="middle" fill="#a7f3d0" font-size="9" font-family="monospace" font-weight="bold">CMM INSPECT</text>
        </g>
      </svg>

      <!-- Glass Telemetry Card -->
      <div class="absolute bottom-2 right-2 sm:bottom-4 sm:right-4 bg-slate-950/85 backdrop-blur-md border border-amber-500/30 rounded-xl px-3.5 py-2.5 shadow-xl flex items-center gap-3">
        <div class="w-2.5 h-2.5 rounded-full bg-amber-400 animate-ping" />
        <div class="text-[11px] font-mono leading-tight">
          <span class="text-white font-bold block">TEFA MESIN & CNC ACTIVE</span>
          <span class="text-amber-300/80 text-[10px]">Toleransi: ±0.005mm · ISO 9001:2015</span>
        </div>
      </div>
    </div>

    <!-- PRESET 2: ARCHITECTURE & BIM 4.0 (DESAIN PEMODELAN BANGUNAN) -->
    <div
      v-else-if="currentType === 'architecture'"
      class="w-full h-full relative flex items-center justify-center"
    >
      <svg
        viewBox="0 0 500 380"
        class="w-full h-full drop-shadow-[0_0_30px_rgba(15,118,110,0.3)]"
      >
        <defs>
          <linearGradient id="sarangengeBimGrad" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" stop-color="#0f766e" stop-opacity="0.9" />
            <stop offset="50%" stop-color="#38bdf8" stop-opacity="0.8" />
            <stop offset="100%" stop-color="#f59e0b" stop-opacity="0.8" />
          </linearGradient>
        </defs>

        <!-- 3D Isometric Ground Grid -->
        <g class="stroke-white/10" stroke-width="1">
          <line x1="100" y1="310" x2="250" y2="240" />
          <line x1="250" y1="240" x2="400" y2="310" />
          <line x1="400" y1="310" x2="250" y2="370" />
          <line x1="250" y1="370" x2="100" y2="310" />
        </g>

        <!-- Isometric Wireframe Structural Building (BIM Model) -->
        <g stroke="url(#sarangengeBimGrad)" stroke-width="1.8" fill="none">
          <!-- Building Tower 1 (Center Left) -->
          <polygon points="180,240 250,205 250,90 180,125" class="fill-teal-950/40" />
          <polygon points="250,205 320,240 320,125 250,90" class="fill-slate-900/60" />
          <polygon points="250,90 320,55 250,20 180,55" class="fill-amber-500/20" />
          
          <!-- Tower 2 Secondary Block -->
          <polygon points="130,265 180,240 180,150 130,175" class="fill-teal-950/30" />
          <polygon points="320,240 370,265 370,175 320,150" class="fill-slate-900/40" />

          <!-- Floor Division Wireframes -->
          <line x1="180" y1="160" x2="250" y2="125" stroke="#38bdf8" stroke-dasharray="3 3" />
          <line x1="250" y1="125" x2="320" y2="160" stroke="#38bdf8" stroke-dasharray="3 3" />
          <line x1="180" y1="200" x2="250" y2="165" stroke="#38bdf8" stroke-dasharray="3 3" />
          <line x1="250" y1="165" x2="320" y2="200" stroke="#38bdf8" stroke-dasharray="3 3" />
        </g>

        <!-- Laser Leveling Horizontal Scan Beam -->
        <g>
          <line x1="70" y1="125" x2="430" y2="125" stroke="#f59e0b" stroke-width="1.5" stroke-dasharray="6 4" class="sarangenge-scan-beam" />
          <circle cx="250" cy="125" r="4" fill="#f59e0b" class="animate-ping" />
        </g>

        <!-- Dimensional Elevation Callouts -->
        <g font-family="monospace" font-size="9" fill="#94a3b8">
          <text x="110" y="115">EL +24.50m (ROOF)</text>
          <line x1="100" y1="120" x2="175" y2="125" stroke="#38bdf8" stroke-width="1" />
          <text x="350" y="225">BIM LOD-400</text>
          <line x1="325" y1="230" x2="345" y2="225" stroke="#0f766e" stroke-width="1" />
        </g>
      </svg>

      <!-- Glass Telemetry Card -->
      <div class="absolute bottom-2 right-2 sm:bottom-4 sm:right-4 bg-slate-950/85 backdrop-blur-md border border-teal-500/30 rounded-xl px-3.5 py-2.5 shadow-xl flex items-center gap-3">
        <div class="w-2.5 h-2.5 rounded-full bg-teal-400 animate-ping" />
        <div class="text-[11px] font-mono leading-tight">
          <span class="text-white font-bold block">DPIB · STUDIO BIM 4.0</span>
          <span class="text-teal-300/80 text-[10px]">Autodesk Revit & Lumion Certified</span>
        </div>
      </div>
    </div>

    <!-- PRESET 3: AUTOMOTIVE & EV POWERTRAIN (TEKNIK OTOMOTIF) -->
    <div
      v-else-if="currentType === 'automotive'"
      class="w-full h-full relative flex items-center justify-center"
    >
      <div class="relative w-[340px] sm:w-[390px] bg-slate-950/90 backdrop-blur-md border border-amber-500/30 rounded-2xl p-4 sm:p-5 shadow-2xl space-y-3.5">
        <!-- Header HUD -->
        <div class="flex items-center justify-between border-b border-slate-800 pb-2.5">
          <div class="flex items-center gap-2">
            <span class="w-2.5 h-2.5 rounded-full bg-amber-400 animate-pulse" />
            <span class="text-xs font-mono font-bold text-white tracking-wider">EV POWERTRAIN & OBD-II</span>
          </div>
          <span class="text-[10px] font-mono text-amber-300 bg-amber-500/10 px-2 py-0.5 rounded border border-amber-500/25">
            READY · SOC 96%
          </span>
        </div>

        <!-- Gauge Clusters -->
        <div class="grid grid-cols-2 gap-3">
          <!-- RPM / Power Gauge -->
          <div class="bg-slate-900/80 border border-slate-800 rounded-xl p-3 flex flex-col items-center justify-center text-center">
            <div class="text-2xl font-bold font-mono text-amber-400 tracking-tight">
              6,850 <span class="text-xs text-slate-400">RPM</span>
            </div>
            <div class="text-[9px] uppercase font-mono text-slate-400 mt-0.5">Dual Inverter Motor</div>
            <div class="w-full bg-slate-800 h-1.5 rounded-full mt-2 overflow-hidden">
              <div class="bg-gradient-to-r from-teal-400 to-amber-400 h-full rounded-full w-[78%]" />
            </div>
          </div>

          <!-- Torque & Battery Voltage -->
          <div class="bg-slate-900/80 border border-slate-800 rounded-xl p-3 flex flex-col items-center justify-center text-center">
            <div class="text-2xl font-bold font-mono text-teal-400 tracking-tight">
              340 <span class="text-xs text-slate-400">Nm</span>
            </div>
            <div class="text-[9px] uppercase font-mono text-slate-400 mt-0.5">High-Voltage: 380V</div>
            <div class="w-full bg-slate-800 h-1.5 rounded-full mt-2 overflow-hidden">
              <div class="bg-gradient-to-r from-amber-400 to-teal-400 h-full rounded-full w-[85%]" />
            </div>
          </div>
        </div>

        <!-- Diagnostic Battery Cells -->
        <div class="bg-slate-900/60 border border-slate-800/80 rounded-xl p-2.5 flex items-center justify-between text-[10px] font-mono">
          <div class="flex items-center gap-1.5 text-slate-300">
            <span class="w-2 h-2 rounded-full bg-emerald-400" />
            <span>CELL BALANCING</span>
          </div>
          <span class="text-slate-400">TEMP: 26.4°C · OPTIMAL</span>
        </div>
      </div>
    </div>

    <!-- PRESET 4: TECH NETWORK & IOT (TEKNIK JARINGAN & IT) -->
    <div
      v-else
      class="w-full h-full relative flex items-center justify-center"
    >
      <svg
        viewBox="0 0 500 380"
        class="w-full h-full drop-shadow-[0_0_25px_rgba(56,189,248,0.25)]"
      >
        <defs>
          <linearGradient id="sarangengeNetGrad" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" stop-color="#38bdf8" stop-opacity="0.8" />
            <stop offset="100%" stop-color="#f59e0b" stop-opacity="0.8" />
          </linearGradient>
        </defs>

        <!-- Connecting Topology Lines -->
        <g class="stroke-slate-800" stroke-width="1.5" stroke-dasharray="4 4">
          <line x1="250" y1="190" x2="110" y2="100" />
          <line x1="250" y1="190" x2="390" y2="90" />
          <line x1="250" y1="190" x2="100" y2="280" />
          <line x1="250" y1="190" x2="400" y2="280" />
        </g>

        <!-- Active Packet Pulse Lines -->
        <g stroke="url(#sarangengeNetGrad)" stroke-width="2" fill="none">
          <path d="M110 100 L250 190 L390 90" class="sarangenge-pulse-line" />
          <path d="M100 280 L250 190 L400 280" class="sarangenge-pulse-line" style="animation-delay: 1.2s;" />
        </g>

        <!-- Core NOC Router Node -->
        <g transform="translate(250, 190)">
          <circle r="44" fill="none" stroke="#38bdf8" stroke-width="1" class="animate-ping opacity-25" />
          <circle r="34" fill="#0a1128" stroke="#38bdf8" stroke-width="2" />
          <circle r="22" fill="#0f172a" stroke="#f59e0b" stroke-width="1.5" />
          <circle r="8" fill="#38bdf8" class="animate-pulse" />
        </g>

        <!-- Edge Nodes -->
        <g transform="translate(110, 100)">
          <circle r="20" fill="#0a1128" stroke="#38bdf8" stroke-width="1.5" />
          <circle r="5" fill="#38bdf8" />
          <text x="0" y="32" text-anchor="middle" fill="#94a3b8" font-size="9" font-family="monospace">CLOUD CORE</text>
        </g>

        <g transform="translate(390, 90)">
          <circle r="20" fill="#0a1128" stroke="#f59e0b" stroke-width="1.5" />
          <circle r="5" fill="#f59e0b" />
          <text x="0" y="32" text-anchor="middle" fill="#94a3b8" font-size="9" font-family="monospace">CAMPUS FIBER</text>
        </g>
      </svg>

      <!-- Glass Telemetry Card -->
      <div class="absolute bottom-2 right-2 sm:bottom-4 sm:right-4 bg-slate-950/85 backdrop-blur-md border border-cyan-500/30 rounded-xl px-3.5 py-2.5 shadow-xl flex items-center gap-3">
        <div class="w-2.5 h-2.5 rounded-full bg-cyan-400 animate-ping" />
        <div class="text-[11px] font-mono leading-tight">
          <span class="text-white font-bold block">CAMPUS BACKBONE 10G</span>
          <span class="text-cyan-300/80 text-[10px]">MikroTik & Cisco Networking Academy</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';

const props = withDefaults(
  defineProps<{
    type?: string;
  }>(),
  {
    type: 'engineering',
  }
);

const currentType = computed(() => {
  const t = String(props.type || 'engineering').toLowerCase();
  if (['engineering', 'tefa', 'cnc', 'manufacturing'].includes(t)) return 'engineering';
  if (['architecture', 'bim', 'dpib', 'building'].includes(t)) return 'architecture';
  if (['automotive', 'ev', 'tkro', 'tflm'].includes(t)) return 'automotive';
  return 'tech_network';
});
</script>
