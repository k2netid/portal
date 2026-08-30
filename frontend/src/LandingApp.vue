<template>
  <div class="landing-root relative min-h-screen flex flex-col font-sans text-foreground overflow-hidden">
    <div
      class="pointer-events-none absolute inset-0 -z-10 landing-atmosphere"
      aria-hidden="true"
    />

    <header class="landing-enter landing-enter-delay-0 relative z-10 px-6 sm:px-10 pt-8">
      <a
        href="/"
        class="inline-flex items-center gap-3 min-w-0 group"
      >
        <span
          class="landing-logo-slot h-10 w-10 shrink-0"
          aria-hidden="true"
        >
          <img
            src="/logo.png"
            alt=""
            width="40"
            height="40"
            decoding="async"
            fetchpriority="high"
            class="landing-logo h-10 w-10 object-contain"
            :class="{ 'is-ready': logoReady }"
            @load="logoReady = true"
            @error="logoReady = true"
          >
        </span>
        <p class="text-xl sm:text-2xl font-semibold tracking-tight text-foreground truncate group-hover:text-primary transition-colors">
          {{ t('landing.brand') }}
        </p>
      </a>
    </header>

    <main class="relative z-10 flex-1 flex flex-col justify-center px-6 sm:px-10 py-12 lg:py-8">
      <div class="mx-auto w-full max-w-6xl grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-8 items-center">
        <div class="max-w-xl">
          <p class="landing-enter landing-enter-delay-1 text-sm font-semibold tracking-[0.14em] text-primary uppercase mb-4">
            {{ t('landing.eyebrow') }}
          </p>
          <h1 class="landing-enter landing-enter-delay-2 text-4xl sm:text-5xl font-bold tracking-tight leading-[1.1] text-foreground">
            {{ t('landing.headline') }}
          </h1>
          <i18n-t
            keypath="landing.body"
            tag="p"
            class="landing-enter landing-enter-delay-3 mt-5 text-lg text-muted-foreground leading-relaxed"
          >
            <template #site>
              <strong class="font-semibold text-foreground">{{ t('landing.sitePack') }}</strong>
            </template>
          </i18n-t>

          <i18n-t
            keypath="landing.docsHint"
            tag="p"
            class="landing-enter landing-enter-delay-4 mt-8 text-sm text-muted-foreground leading-relaxed max-w-lg"
          >
            <template #readme>
              <span class="font-medium text-foreground/85">{{ t('landing.readmeLabel') }}</span>
            </template>
            <template #docs>
              <a
                href="https://docs.jejakawan.com"
                target="_blank"
                rel="noopener noreferrer"
                class="font-medium text-primary underline-offset-4 hover:underline"
              >{{ t('landing.docsLabel') }}</a>
            </template>
          </i18n-t>
        </div>

        <!-- Visual stage only — no console/login CTAs (security: paths stay out of public HTML) -->
        <div
          class="landing-enter landing-enter-delay-3 relative mx-auto w-full max-w-md lg:max-w-none aspect-square lg:aspect-auto lg:min-h-[28rem]"
          aria-hidden="true"
        >
          <div class="landing-stage absolute inset-0 flex items-center justify-center">
            <div class="landing-orbit landing-orbit-a" />
            <div class="landing-orbit landing-orbit-b" />
            <div class="landing-orbit landing-orbit-c" />

            <div class="landing-node landing-node-1" />
            <div class="landing-node landing-node-2" />
            <div class="landing-node landing-node-3" />
            <div class="landing-node landing-node-4" />

            <div class="landing-grid" />

            <div class="landing-mark">
              <span class="landing-logo-slot landing-logo-slot-hero inline-flex">
                <img
                  src="/logo.png"
                  alt=""
                  width="112"
                  height="112"
                  decoding="async"
                  class="landing-logo landing-logo-hero h-24 w-24 sm:h-28 sm:w-28 object-contain"
                  :class="{ 'is-ready': logoReady }"
                  @load="logoReady = true"
                  @error="logoReady = true"
                >
              </span>
            </div>

            <div class="landing-beam landing-beam-1" />
            <div class="landing-beam landing-beam-2" />
          </div>
        </div>
      </div>
    </main>

    <footer class="landing-enter landing-enter-delay-5 relative z-10 border-t border-border/60 bg-background/40 backdrop-blur-sm px-6 sm:px-10 py-6">
      <div class="mx-auto max-w-6xl flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <p class="text-sm text-foreground/80">
          {{ t('landing.copyright', { year: currentYear, brand: t('landing.brand') }) }}
        </p>
        <span
          class="landing-logo-slot h-7 w-7 shrink-0"
          aria-hidden="true"
        >
          <img
            src="/logo.png"
            alt=""
            width="28"
            height="28"
            decoding="async"
            class="landing-logo h-7 w-7 object-contain"
            :class="{ 'is-ready': logoReady }"
            @load="logoReady = true"
            @error="logoReady = true"
          >
        </span>
      </div>
    </footer>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const currentYear = computed(() => new Date().getFullYear());
/** Hide broken/partial alt flash until the mark paints. */
const logoReady = ref(false);

onMounted(() => {
  document.title = t('landing.documentTitle');
  const probe = new Image();
  probe.onload = () => {
    logoReady.value = true;
  };
  probe.src = '/logo.png';
  if (probe.complete && probe.naturalWidth > 0) {
    logoReady.value = true;
  }
});
</script>

<style scoped>
.landing-root {
  --primary: var(--console-primary-hsl, 238.9 77.1% 60.6%);
  --primary-foreground: var(--console-primary-foreground-hsl, 0 0% 100%);
  --ring: var(--console-primary-hsl, 238.9 77.1% 60.6%);
  --radius: var(--console-radius, 0.75rem);
}

.landing-atmosphere {
  background:
    radial-gradient(ellipse 70% 55% at 8% -5%, hsl(var(--primary) / 0.14), transparent 55%),
    radial-gradient(ellipse 50% 45% at 88% 12%, hsl(var(--warning) / 0.1), transparent 50%),
    radial-gradient(ellipse 40% 35% at 70% 70%, hsl(var(--primary) / 0.06), transparent 60%),
    linear-gradient(165deg, hsl(var(--background)) 0%, hsl(var(--muted) / 0.55) 48%, hsl(var(--background)) 100%);
  animation: landing-atmosphere-shift 18s ease-in-out infinite alternate;
}

.landing-stage {
  isolation: isolate;
}

.landing-orbit {
  position: absolute;
  border-radius: 9999px;
  border: 1px solid hsl(var(--primary) / 0.18);
  box-shadow: inset 0 0 40px hsl(var(--primary) / 0.04);
}

.landing-orbit-a {
  width: min(78%, 22rem);
  height: min(78%, 22rem);
  animation: landing-spin 48s linear infinite;
}

.landing-orbit-b {
  width: min(58%, 16.5rem);
  height: min(58%, 16.5rem);
  border-style: dashed;
  border-color: hsl(var(--foreground) / 0.12);
  animation: landing-spin-rev 36s linear infinite;
}

.landing-orbit-c {
  width: min(38%, 11rem);
  height: min(38%, 11rem);
  border-color: hsl(var(--primary) / 0.28);
  animation: landing-pulse-ring 4.5s ease-in-out infinite;
}

.landing-grid {
  position: absolute;
  inset: 12%;
  border-radius: 2rem;
  background-image:
    linear-gradient(hsl(var(--foreground) / 0.045) 1px, transparent 1px),
    linear-gradient(90deg, hsl(var(--foreground) / 0.045) 1px, transparent 1px);
  background-size: 28px 28px;
  mask-image: radial-gradient(circle at center, black 20%, transparent 72%);
  opacity: 0.9;
  animation: landing-grid-drift 14s ease-in-out infinite alternate;
}

.landing-logo-slot {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
}

.landing-logo-slot-hero {
  width: 7rem;
  height: 7rem;
}

.landing-logo {
  filter: drop-shadow(0 2px 6px hsl(var(--foreground) / 0.12));
  opacity: 0;
  transition: opacity 0.25s ease;
}

.landing-logo.is-ready {
  opacity: 1;
}

.landing-logo-hero {
  filter: drop-shadow(0 6px 18px hsl(var(--foreground) / 0.14));
}

.landing-logo-slot .landing-logo.is-ready {
  opacity: 0.85;
}

.landing-logo-slot-hero .landing-logo.is-ready,
header .landing-logo.is-ready {
  opacity: 1;
}

.landing-mark {
  position: relative;
  z-index: 2;
  animation: landing-mark-float 5.5s ease-in-out infinite;
}

.landing-node {
  position: absolute;
  z-index: 1;
  width: 0.55rem;
  height: 0.55rem;
  border-radius: 9999px;
  background: hsl(var(--primary));
  box-shadow: 0 0 0 4px hsl(var(--primary) / 0.12);
}

.landing-node-1 {
  top: 18%;
  left: 22%;
  animation: landing-node-glow 3.2s ease-in-out infinite;
}

.landing-node-2 {
  top: 24%;
  right: 18%;
  width: 0.4rem;
  height: 0.4rem;
  background: hsl(var(--foreground) / 0.45);
  box-shadow: 0 0 0 3px hsl(var(--foreground) / 0.06);
  animation: landing-node-glow 4s ease-in-out 0.6s infinite;
}

.landing-node-3 {
  bottom: 22%;
  left: 20%;
  width: 0.45rem;
  height: 0.45rem;
  animation: landing-node-glow 3.8s ease-in-out 1.1s infinite;
}

.landing-node-4 {
  bottom: 28%;
  right: 24%;
  background: hsl(var(--warning));
  box-shadow: 0 0 0 4px hsl(var(--warning) / 0.14);
  animation: landing-node-glow 3.5s ease-in-out 0.3s infinite;
}

.landing-beam {
  position: absolute;
  z-index: 0;
  width: 42%;
  height: 1px;
  background: linear-gradient(90deg, transparent, hsl(var(--primary) / 0.35), transparent);
  transform-origin: center;
}

.landing-beam-1 {
  transform: rotate(28deg);
  animation: landing-beam-fade 5s ease-in-out infinite;
}

.landing-beam-2 {
  transform: rotate(-42deg);
  animation: landing-beam-fade 6.5s ease-in-out 1.2s infinite;
}

.landing-enter {
  animation: landing-fade-up 0.55s ease-out both;
}

.landing-enter-delay-0 { animation-delay: 0.02s; }
.landing-enter-delay-1 { animation-delay: 0.08s; }
.landing-enter-delay-2 { animation-delay: 0.14s; }
.landing-enter-delay-3 { animation-delay: 0.2s; }
.landing-enter-delay-4 { animation-delay: 0.28s; }
.landing-enter-delay-5 { animation-delay: 0.36s; }

@keyframes landing-fade-up {
  from {
    opacity: 0;
    transform: translateY(12px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes landing-atmosphere-shift {
  from {
    filter: hue-rotate(0deg);
    transform: scale(1);
  }
  to {
    filter: hue-rotate(8deg);
    transform: scale(1.03);
  }
}

@keyframes landing-spin {
  to { transform: rotate(360deg); }
}

@keyframes landing-spin-rev {
  to { transform: rotate(-360deg); }
}

@keyframes landing-pulse-ring {
  0%, 100% {
    opacity: 0.55;
    transform: scale(1);
  }
  50% {
    opacity: 1;
    transform: scale(1.04);
  }
}

@keyframes landing-mark-float {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-8px); }
}

@keyframes landing-node-glow {
  0%, 100% {
    opacity: 0.55;
    transform: scale(1);
  }
  50% {
    opacity: 1;
    transform: scale(1.25);
  }
}

@keyframes landing-beam-fade {
  0%, 100% { opacity: 0.15; }
  50% { opacity: 0.7; }
}

@keyframes landing-grid-drift {
  from { transform: translateY(0) scale(1); }
  to { transform: translateY(-6px) scale(1.02); }
}

@media (prefers-reduced-motion: reduce) {
  .landing-atmosphere,
  .landing-enter,
  .landing-orbit,
  .landing-mark,
  .landing-node,
  .landing-beam,
  .landing-grid {
    animation: none !important;
  }
}
</style>
