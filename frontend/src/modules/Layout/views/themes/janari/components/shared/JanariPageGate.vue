<template>
  <div
    v-if="!isEnabled"
    class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16"
  >
    <PageDisabled
      :title="title"
      :message="disabledMessage"
    />
  </div>
  <slot v-else />
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useTheme } from '@/modules/Layout/composables/useTheme';
import { useThemeI18n } from '@/modules/Layout/composables/useThemeI18n';
import PageDisabled from './PageDisabled.vue';

const props = defineProps<{
  settingKey: string;
  title: string;
}>();

const { getSetting } = useTheme();
const { t } = useThemeI18n('janari');

const isEnabled = computed(() => getSetting(props.settingKey, true) !== false);

const disabledMessage = computed(() => {
  const raw = getSetting('disabled_page_message', '');
  if (typeof raw === 'string' && raw.trim() !== '') {
    return raw;
  }
  return t('pages.disabled.defaultMessage', 'Halaman ini sedang tidak dapat diakses.');
});
</script>
