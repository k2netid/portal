<template>
  <footer class="mt-auto border-t border-border/80 bg-slate-950 text-slate-400 shrink-0">
    <div class="console-content-wrap px-4 sm:px-6 lg:px-8 py-5 text-xs">
      <p>{{ copyrightLine }}</p>
    </div>
  </footer>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useTheme } from '@/modules/Layout/composables/useTheme';
import { useThemeI18n } from '@/modules/Layout/composables/useThemeI18n';
import { useLayungIdentity } from '@/modules/Layout/views/themes/layung/composables/useLayungIdentity';

const { t: tt } = useThemeI18n('layung');
const { getSetting } = useTheme();
const { displayCompanyName } = useLayungIdentity();

const copyrightLine = computed(() => {
  const fromTheme = getSetting('footer_copyright', '');
  if (typeof fromTheme === 'string' && fromTheme.trim()) return fromTheme.trim();
  return `© ${new Date().getFullYear()} ${displayCompanyName.value}. ${tt('footer.copyright', 'Hak cipta dilindungi undang-undang.')}`;
});
</script>
