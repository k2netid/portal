<template>
  <div
    class="layung-hero-visual relative w-full max-w-[540px] aspect-[4/3] sm:aspect-[16/11] flex items-center justify-center select-none"
    :class="`layung-hero-visual--${type}`"
  >
    <!-- Ambient Backdrop Glow -->
    <div class="absolute inset-0 -z-10 flex items-center justify-center pointer-events-none">
      <div class="w-3/4 h-3/4 rounded-full bg-sky-500/10 blur-3xl animate-pulse duration-1000" />
      <div class="w-1/2 h-1/2 rounded-full bg-emerald-500/10 blur-2xl delay-300" />
    </div>

    <!-- PRESET 1: NETWORK TOPOLOGY -->
    <div
      v-if="type === 'network'"
      class="w-full h-full relative flex items-center justify-center"
    >
      <svg
        viewBox="0 0 500 380"
        class="w-full h-full drop-shadow-[0_0_25px_rgba(0,174,239,0.25)]"
      >
        <defs>
          <linearGradient id="netLineGrad" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" stop-color="#00aeef" stop-opacity="0.8" />
            <stop offset="100%" stop-color="#10b981" stop-opacity="0.8" />
          </linearGradient>
          <filter id="glow" x="-20%" y="-20%" width="140%" height="140%">
            <feGaussianBlur stdDeviation="3" result="blur" />
            <feMerge>
              <feMergeNode in="blur" />
              <feMergeNode in="SourceGraphic" />
            </feMerge>
          </filter>
        </defs>

        <!-- Connecting Fiber Lines -->
        <g class="stroke-slate-800" stroke-width="1.5" stroke-dasharray="4 4">
          <line x1="250" y1="190" x2="110" y2="100" />
          <line x1="250" y1="190" x2="390" y2="90" />
          <line x1="250" y1="190" x2="100" y2="280" />
          <line x1="250" y1="190" x2="400" y2="280" />
          <line x1="110" y1="100" x2="390" y2="90" />
          <line x1="100" y1="280" x2="400" y2="280" />
        </g>

        <!-- Animated Active Fiber Paths with Traveling Pulses -->
        <g stroke="url(#netLineGrad)" stroke-width="2" fill="none">
          <path d="M110 100 L250 190 L390 90" class="layung-pulse-line" />
          <path d="M100 280 L250 190 L400 280" class="layung-pulse-line" style="animation-delay: 1.2s;" />
          <path d="M110 100 L100 280" class="layung-pulse-line" style="animation-delay: 0.6s;" />
          <path d="M390 90 L400 280" class="layung-pulse-line" style="animation-delay: 1.8s;" />
        </g>

        <!-- Central Core Node: AS153992 Core Router -->
        <g transform="translate(250, 190)">
          <!-- Concentric Radar Wave -->
          <circle r="48" fill="none" stroke="#00aeef" stroke-width="1" class="animate-ping opacity-25" />
          <circle r="36" fill="#020617" stroke="#00aeef" stroke-width="2" filter="url(#glow)" />
          <circle r="26" fill="#0b1220" stroke="#10b981" stroke-width="1.5" />
          <circle r="8" fill="#00aeef" class="animate-pulse" />
          <!-- Orbiting Core Ring -->
          <circle r="32" fill="none" stroke="#00aeef" stroke-width="1" stroke-dasharray="6 6" class="animate-spin duration-3000" />
        </g>

        <!-- Node 1: Metro Fiber Ring (Top Left) -->
        <g transform="translate(110, 100)">
          <circle r="22" fill="#020617" stroke="#00aeef" stroke-width="1.5" />
          <circle r="6" fill="#38bdf8" />
          <text x="0" y="34" text-anchor="middle" fill="#94a3b8" font-size="9" font-family="monospace">METRO RING</text>
        </g>

        <!-- Node 2: Tier-1 IXP / OpenIXP (Top Right) -->
        <g transform="translate(390, 90)">
          <circle r="24" fill="#020617" stroke="#10b981" stroke-width="1.5" />
          <circle r="6" fill="#10b981" />
          <text x="0" y="36" text-anchor="middle" fill="#94a3b8" font-size="9" font-family="monospace">JKT-IX · OIXP</text>
        </g>

        <!-- Node 3: OLT / GPON Hub (Bottom Left) -->
        <g transform="translate(100, 280)">
          <circle r="20" fill="#020617" stroke="#00aeef" stroke-width="1.5" />
          <circle r="5" fill="#38bdf8" />
          <text x="0" y="32" text-anchor="middle" fill="#94a3b8" font-size="9" font-family="monospace">OLT GPON</text>
        </g>

        <!-- Node 4: Enterprise Direct Connect (Bottom Right) -->
        <g transform="translate(400, 280)">
          <circle r="22" fill="#020617" stroke="#38bdf8" stroke-width="1.5" />
          <circle r="6" fill="#38bdf8" />
          <text x="0" y="34" text-anchor="middle" fill="#94a3b8" font-size="9" font-family="monospace">CPE DIRECT</text>
        </g>
      </svg>

      <!-- Glass Telemetry Card -->
      <div class="absolute bottom-1 right-2 sm:bottom-3 sm:right-4 bg-slate-950/80 backdrop-blur-md border border-slate-800/90 rounded-xl px-3 py-2 shadow-xl flex items-center gap-3">
        <div class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-ping" />
        <div class="text-[11px] font-mono leading-tight">
          <span class="text-white font-bold block">AS153992 · BGP UP</span>
          <span class="text-slate-400 text-[10px]">Latency: 1.8ms | SLA 99.98%</span>
        </div>
      </div>
    </div>

    <!-- PRESET 2: DATA CENTER & SERVER RACK -->
    <div
      v-else-if="type === 'datacenter'"
      class="w-full h-full relative flex items-center justify-center"
    >
      <div class="relative w-[340px] sm:w-[380px] bg-slate-950/90 backdrop-blur-md border border-slate-800/90 rounded-2xl p-4 shadow-2xl space-y-3">
        <!-- Rack Top Header -->
        <div class="flex items-center justify-between border-b border-slate-800/80 pb-2.5">
          <div class="flex items-center gap-2">
            <span class="w-2.5 h-2.5 rounded-full bg-emerald-400" />
            <span class="text-xs font-mono font-bold text-white tracking-wider">TIER-3 SERVER ENCLOSURE</span>
          </div>
          <span class="text-[10px] font-mono text-sky-400 bg-sky-500/10 px-2 py-0.5 rounded border border-sky-500/20">
            20.8°C OPTIMAL
          </span>
        </div>

        <!-- Server Blade 1: Core Router -->
        <div class="bg-slate-900/90 border border-slate-800 rounded-lg p-2.5 flex items-center justify-between">
          <div class="space-y-1">
            <span class="text-[10px] font-mono text-slate-400 block font-bold">1U CORE BGP ROUTER</span>
            <div class="flex gap-1.5">
              <span v-for="i in 8" :key="i" class="w-1.5 h-3 rounded-xs bg-emerald-500/80 animate-pulse" :style="{ animationDelay: `${i * 120}ms` }" />
            </div>
          </div>
          <div class="text-right font-mono text-[10px] text-slate-500">
            <span class="text-emerald-400 block font-semibold">10G SFP+</span>
            ACTIVE
          </div>
        </div>

        <!-- Server Blade 2: SOC / Managed Services -->
        <div class="bg-slate-900/90 border border-slate-800 rounded-lg p-2.5 flex items-center justify-between">
          <div class="space-y-1">
            <span class="text-[10px] font-mono text-slate-400 block font-bold">2U SOC SECURITY & FIREWALL</span>
            <div class="flex gap-1.5">
              <span v-for="i in 8" :key="i" class="w-1.5 h-3 rounded-xs bg-sky-400/80 animate-pulse" :style="{ animationDelay: `${i * 180}ms` }" />
            </div>
          </div>
          <div class="text-right font-mono text-[10px] text-slate-500">
            <span class="text-sky-400 block font-semibold">DDoS DEF</span>
            PROTECTED
          </div>
        </div>

        <!-- Server Blade 3: Storage & Compute -->
        <div class="bg-slate-900/90 border border-slate-800 rounded-lg p-2.5 flex items-center justify-between">
          <div class="space-y-1">
            <span class="text-[10px] font-mono text-slate-400 block font-bold">4U NVMe SAN & HYPERVISOR</span>
            <div class="flex gap-1.5">
              <span v-for="i in 8" :key="i" class="w-1.5 h-3 rounded-xs bg-indigo-400/80 animate-pulse" :style="{ animationDelay: `${i * 240}ms` }" />
            </div>
          </div>
          <div class="text-right font-mono text-[10px] text-slate-500">
            <span class="text-indigo-400 block font-semibold">REDUNDANT</span>
            GRID A+B
          </div>
        </div>
      </div>
    </div>

    <!-- PRESET 3: ENTERPRISE WIRELESS ACCESS POINT (WI-FI 6/7 AP) -->
    <div
      v-else-if="type === 'wireless'"
      class="w-full h-full relative flex items-center justify-center"
    >
      <svg
        viewBox="0 0 500 380"
        class="w-full h-full drop-shadow-[0_0_25px_rgba(16,185,129,0.2)]"
      >
        <defs>
          <linearGradient id="apBeamGrad" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" stop-color="#10b981" stop-opacity="0.9" />
            <stop offset="100%" stop-color="#00aeef" stop-opacity="0.2" />
          </linearGradient>
        </defs>

        <!-- Wireless RF Waves Expanding Outwards from Center AP -->
        <g transform="translate(250, 150)">
          <!-- Wave 1 -->
          <circle r="70" fill="none" stroke="#10b981" stroke-width="1.5" class="layung-rf-wave" style="animation-delay: 0s;" />
          <!-- Wave 2 -->
          <circle r="110" fill="none" stroke="#00aeef" stroke-width="1.2" class="layung-rf-wave" style="animation-delay: 0.8s;" />
          <!-- Wave 3 -->
          <circle r="150" fill="none" stroke="#10b981" stroke-width="1" class="layung-rf-wave" style="animation-delay: 1.6s;" />
        </g>

        <!-- Connection Beams to Client Devices -->
        <g stroke="url(#apBeamGrad)" stroke-width="1.5" stroke-dasharray="3 3">
          <line x1="250" y1="150" x2="90" y2="280" class="layung-beam" />
          <line x1="250" y1="150" x2="250" y2="310" class="layung-beam" />
          <line x1="250" y1="150" x2="410" y2="280" class="layung-beam" />
        </g>

        <!-- Central Enterprise Access Point Disc (Ceiling/Wall Mounted AP) -->
        <g transform="translate(250, 150)">
          <!-- Outer Mounting Ring -->
          <circle r="44" fill="#020617" stroke="#334155" stroke-width="2" />
          <circle r="38" fill="#0f172a" stroke="#00aeef" stroke-width="1.5" />
          <!-- Central Glowing LED Ring -->
          <circle r="26" fill="#020617" stroke="#10b981" stroke-width="2.5" class="animate-pulse" />
          <circle r="10" fill="#10b981" class="animate-ping opacity-30" />
          <circle r="6" fill="#10b981" />
          <text x="0" y="4" text-anchor="middle" fill="#ffffff" font-size="8" font-family="monospace" font-weight="bold">6/7</text>
        </g>

        <!-- Client 1: Enterprise Laptop (Left) -->
        <g transform="translate(90, 280)">
          <rect x="-24" y="-18" width="48" height="36" rx="8" fill="#020617" stroke="#00aeef" stroke-width="1.5" />
          <circle r="4" fill="#00aeef" class="animate-pulse" />
          <text x="0" y="30" text-anchor="middle" fill="#94a3b8" font-size="9" font-family="monospace">LAPTOP · 1.2G</text>
        </g>

        <!-- Client 2: Smartphone / Executive Device (Center Bottom) -->
        <g transform="translate(250, 310)">
          <rect x="-18" y="-22" width="36" height="44" rx="8" fill="#020617" stroke="#10b981" stroke-width="1.5" />
          <circle r="4" fill="#10b981" class="animate-pulse" />
          <text x="0" y="32" text-anchor="middle" fill="#94a3b8" font-size="9" font-family="monospace">MOBILE · 5GHz</text>
        </g>

        <!-- Client 3: Smart Office / IoT Node (Right) -->
        <g transform="translate(410, 280)">
          <rect x="-24" y="-18" width="48" height="36" rx="8" fill="#020617" stroke="#00aeef" stroke-width="1.5" />
          <circle r="4" fill="#00aeef" class="animate-pulse" />
          <text x="0" y="30" text-anchor="middle" fill="#94a3b8" font-size="9" font-family="monospace">IOT · MESH</text>
        </g>
      </svg>

      <!-- Glass Telemetry Card -->
      <div class="absolute top-2 right-2 sm:top-4 sm:right-4 bg-slate-950/80 backdrop-blur-md border border-slate-800/90 rounded-xl px-3 py-2 shadow-xl flex items-center gap-2.5">
        <div class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse" />
        <div class="text-[11px] font-mono leading-tight">
          <span class="text-white font-bold block">ENTERPRISE WI-FI 6/7</span>
          <span class="text-slate-400 text-[10px]">128 Users | 2.4 Gbps | Roaming</span>
        </div>
      </div>
    </div>

    <!-- PRESET 4: CYBER MATRIX & DIGITAL GLOBE -->
    <div
      v-else-if="type === 'cyber_globe'"
      class="w-full h-full relative flex items-center justify-center"
    >
      <svg
        viewBox="0 0 500 380"
        class="w-full h-full drop-shadow-[0_0_25px_rgba(0,174,239,0.3)]"
      >
        <defs>
          <linearGradient id="globeGrad" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" stop-color="#00aeef" stop-opacity="0.8" />
            <stop offset="50%" stop-color="#10b981" stop-opacity="0.6" />
            <stop offset="100%" stop-color="#020617" stop-opacity="0.9" />
          </linearGradient>
        </defs>

        <!-- Outer Rotating Rings -->
        <g transform="translate(250, 190)">
          <!-- Equator and Latitude Ellipses -->
          <ellipse rx="130" ry="40" fill="none" stroke="#00aeef" stroke-width="1.5" stroke-dasharray="8 6" class="animate-spin duration-8000" />
          <ellipse rx="130" ry="85" fill="none" stroke="#10b981" stroke-width="1" stroke-dasharray="4 4" class="animate-spin duration-7000" style="animation-direction: reverse;" />
          <ellipse rx="130" ry="130" fill="none" stroke="#334155" stroke-width="1" />
          <circle r="120" fill="none" stroke="url(#globeGrad)" stroke-width="1.5" />

          <!-- Inner Core Pulse -->
          <circle r="30" fill="#00aeef" class="animate-ping opacity-20" />
          <circle r="18" fill="#020617" stroke="#00aeef" stroke-width="2" />
          <circle r="8" fill="#10b981" />

          <!-- International Route Arcs -->
          <path d="M-90 -40 Q 0 -110 90 -30" fill="none" stroke="#38bdf8" stroke-width="2" class="layung-pulse-line" />
          <path d="M-80 50 Q 0 100 80 40" fill="none" stroke="#10b981" stroke-width="2" class="layung-pulse-line" style="animation-delay: 1s;" />

          <!-- Point: Bandung HQ -->
          <g transform="translate(-40, 25)">
            <circle r="4" fill="#00aeef" class="animate-ping" />
            <circle r="3" fill="#ffffff" />
            <text x="6" y="3" fill="#38bdf8" font-size="8" font-family="monospace" font-weight="bold">BDG HQ</text>
          </g>

          <!-- Point: Jakarta IXP -->
          <g transform="translate(-20, -10)">
            <circle r="3" fill="#10b981" />
            <text x="6" y="3" fill="#a7f3d0" font-size="8" font-family="monospace">JKT IX</text>
          </g>

          <!-- Point: Global Transit -->
          <g transform="translate(70, -40)">
            <circle r="4" fill="#38bdf8" class="animate-pulse" />
            <text x="6" y="3" fill="#94a3b8" font-size="8" font-family="monospace">GLOBAL T1</text>
          </g>
        </g>
      </svg>

      <!-- Glass Telemetry Card -->
      <div class="absolute bottom-2 left-2 sm:bottom-4 sm:left-4 bg-slate-950/80 backdrop-blur-md border border-slate-800/90 rounded-xl px-3 py-2 shadow-xl flex items-center gap-2.5">
        <div class="w-2.5 h-2.5 rounded-full bg-sky-400 animate-ping" />
        <div class="text-[11px] font-mono leading-tight">
          <span class="text-white font-bold block">GLOBAL BGP PEERING</span>
          <span class="text-slate-400 text-[10px]">APJII · IDNIC · Anycast Routing</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
withDefaults(
  defineProps<{
    type?: 'network' | 'datacenter' | 'wireless' | 'cyber_globe' | string;
  }>(),
  {
    type: 'network',
  },
);
</script>

<style scoped>
@keyframes layungDash {
  to {
    stroke-dashoffset: -40;
  }
}

@keyframes layungRfPulse {
  0% {
    transform: scale(0.6);
    opacity: 0.9;
  }
  100% {
    transform: scale(1.6);
    opacity: 0;
  }
}

.layung-pulse-line {
  stroke-dasharray: 8 6;
  animation: layungDash 2.5s linear infinite;
}

.layung-rf-wave {
  transform-origin: center;
  animation: layungRfPulse 3.2s cubic-bezier(0.1, 0.5, 0.4, 1) infinite;
}

.duration-3000 {
  animation-duration: 3s;
}
.duration-7000 {
  animation-duration: 7s;
}
.duration-8000 {
  animation-duration: 8s;
}
</style>
