<template>
  <footer class="mt-auto border-t border-border/50 bg-background shrink-0">
    <div class="console-content-wrap px-4 sm:px-6 lg:px-8 py-5 text-xs text-muted-foreground">
      <p>
        <span v-if="copyrightText">{{ copyrightText }}</span>
        <span v-else>&copy; {{ year }} {{ siteName }}</span>
      </p>
    </div>
  </footer>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useTheme } from '@/modules/Layout/composables/useTheme';
import { useLocalizedThemeSetting } from '@/modules/Layout/composables/useLocalizedThemeSetting';
import { useMemberPortalBranding } from '@/modules/Member/composables/useMemberPortalBranding';

const { getSetting } = useTheme();
const { localizedString } = useLocalizedThemeSetting();
const { siteName } = useMemberPortalBranding();

const year = new Date().getFullYear();
const copyrightText = computed(() => {
  const fromTheme = localizedString('footer_copyright');
  if (typeof fromTheme === 'string' && fromTheme.trim()) return fromTheme.trim();
  const raw = getSetting('footer_copyright', '');
  return typeof raw === 'string' && raw.trim() ? raw.trim() : '';
});
</script>
