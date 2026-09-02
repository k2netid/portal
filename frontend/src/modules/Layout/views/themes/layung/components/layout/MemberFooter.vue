<template>
  <footer class="mt-auto border-t border-border/80 bg-slate-950 text-slate-400 shrink-0">
    <div class="console-content-wrap px-4 sm:px-6 lg:px-8 py-5 text-xs">
      <p>{{ copyrightLine }}</p>
    </div>
  </footer>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { useTheme } from '@/modules/Layout/composables/useTheme';
import { useThemeI18n } from '@/modules/Layout/composables/useThemeI18n';
import { useLayungIdentity } from '@/modules/Layout/views/themes/layung/composables/useLayungIdentity';
import { resolveLayungLocalizedCopy } from '@/modules/Layout/views/themes/layung/composables/resolveLayungLocalizedCopy';

const { locale } = useI18n({ useScope: 'global' });
const { t: tt } = useThemeI18n('layung');
const { getSetting } = useTheme();
const { displayCompanyName } = useLayungIdentity();

const copyrightLine = computed(() => {
  const custom = resolveLayungLocalizedCopy({
    getSetting,
    locale: locale.value,
    key: 'footer_text',
    fallback: '',
  }) || resolveLayungLocalizedCopy({
    getSetting,
    locale: locale.value,
    key: 'footer_copyright',
    fallback: '',
  });

  if (custom && custom.trim()) return custom.trim();
  return `© ${new Date().getFullYear()} ${displayCompanyName.value}. ${tt('footer.copyright', 'Hak cipta dilindungi undang-undang.')}`;
});
</script>
