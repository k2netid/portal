<template>
  <div
    data-testid="console-sidebar-brand"
    class="flex items-center gap-2 select-none group"
    :class="[minimized ? 'flex-col justify-center' : 'flex-row']"
  >
    <div 
      class="relative flex items-center justify-center overflow-hidden"
      :class="[ minimized ? 'w-9 h-9' : 'w-auto h-9 max-w-[120px]' ]"
    >
      <img
        v-if="showLogoImage"
        :src="appLogo"
        :alt="displayTitle"
        width="36"
        height="36"
        decoding="async"
        class="w-full h-full object-contain rounded-md"
        @error="logoLoadFailed = true"
      >
      <div 
        v-else
        class="w-full h-full flex flex-col items-center justify-center border-2 border-primary rounded-lg shadow-sm"
      >
        <span class="text-[13px] leading-none font-[900] text-primary tracking-tighter">JA</span>
        <span class="text-[7.5px] leading-tight font-bold text-primary/80 tracking-widest -mt-0.5">CORE</span>
      </div>
    </div>

    <div
      v-if="!minimized"
      class="flex flex-col ml-1"
    >
      <span class="text-sm font-black tracking-tight text-foreground leading-none">{{ displayTitle }}</span>
      <div class="flex items-center mt-0.5">
        <span 
          class="text-[9px] px-1.5 py-0.5 rounded-full font-bold uppercase tracking-wider leading-none"
          :class="licenseBadgeClasses"
        >
          {{ displaySubtitle }}
        </span>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { useSystemStore } from '@/modules/Core/System/stores/system';
import { useConsoleTheme } from '@/modules/Core/System/composables/useConsoleTheme';
import { useDarkMode } from '@/shared/composables/useDarkMode';
import { resolveConsoleSidebarLogo } from '@/modules/Core/System/utils/resolveConsoleSidebarLogo';

const props = withDefaults(defineProps<{
  minimized?: boolean;
  title?: string | null;
  subtitle?: string | null;
}>(), {
  minimized: false,
  title: null,
  subtitle: null,
});

const systemStore = useSystemStore();
const { settings: consoleThemeSettings } = useConsoleTheme();
const { isDark } = useDarkMode();
const logoLoadFailed = ref(false);

const displayTitle = computed(() => props.title || systemStore.appIdentity?.app_name || 'Jejakawan');

const displaySubtitle = computed(() => {
  if (props.subtitle) return props.subtitle;
  const tier = systemStore.appIdentity?.app_license_tier || 'basic';
  const tierNames: Record<string, string> = {
    community: 'Community',
    basic: 'Basic',
    starter: 'Starter',
    pro: 'Pro',
    pro_plus: 'Pro+',
    enterprise: 'Enterprise',
    white_label: 'Enterprise',
  };
  return tierNames[tier] || tier;
});

const licenseBadgeClasses = computed(() => {
  const tier = systemStore.appIdentity?.app_license_tier || 'basic';
  switch (tier) {
    case 'starter':
      return 'bg-sky-500/10 text-sky-600 border border-sky-500/20';
    case 'pro':
      return 'bg-blue-500/10 text-blue-600 border border-blue-500/20';
    case 'pro_plus':
      return 'bg-indigo-500/10 text-indigo-600 border border-indigo-500/20';
    case 'enterprise':
    case 'white_label':
      return 'bg-purple-500/10 text-purple-600 border border-purple-500/20';
    default:
      return 'bg-muted text-muted-foreground border border-border';
  }
});

const appLogo = computed(() =>
  resolveConsoleSidebarLogo(consoleThemeSettings.value, {
    minimized: props.minimized,
    isDark: isDark.value,
    legacyLogo: systemStore.appIdentity?.app_logo || '',
  }),
);

const showLogoImage = computed(() => Boolean(appLogo.value) && !logoLoadFailed.value);

watch(appLogo, () => {
  logoLoadFailed.value = false;
});
</script>

<style scoped>
.group:hover .bg-primary {
  filter: brightness(1.1);
}
</style>
