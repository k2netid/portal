<template>
  <footer class="mt-auto border-t border-border/80 bg-muted/30 shrink-0">
    <div class="console-content-wrap px-4 sm:px-6 lg:px-8 py-5 text-xs text-muted-foreground">
      <p>{{ copyrightLine }}</p>
    </div>
  </footer>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useTheme } from '@/modules/Layout/composables/useTheme';
import { useThemeI18n } from '@/modules/Layout/composables/useThemeI18n';
import { useSarangengeIdentity } from '@/modules/Layout/views/themes/sarangenge/composables/useSarangengeIdentity';

const { t: tt } = useThemeI18n('sarangenge');
const { getSetting } = useTheme();
const { displaySchoolName } = useSarangengeIdentity();

const copyrightLine = computed(() => {
  const fromTheme = getSetting('footer_copyright', '');
  if (typeof fromTheme === 'string' && fromTheme.trim()) return fromTheme.trim();
  return `© ${new Date().getFullYear()} ${displaySchoolName.value}. ${tt('footer.copyright', 'Hak cipta dilindungi undang-undang.')}`;
});
</script>
