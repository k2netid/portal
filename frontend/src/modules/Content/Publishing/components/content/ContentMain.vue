<template>
  <div class="space-y-6">
    <!-- Title Input (Focus Style) -->
    <div class="space-y-4">
      <input
        :value="modelValue.title"
        type="text"
        :placeholder="$t('publishing.content.form.titlePlaceholder')"
        class="w-full bg-transparent text-4xl font-bold tracking-tight border-none outline-none placeholder:text-muted-foreground/40"
        autofocus
        @input="updateTitle(($event.target as HTMLInputElement).value)"
      >
    </div>

    <!-- Visual Builder Quick Banner -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-4 rounded-2xl border border-border bg-muted/20 transition-all hover:border-primary/30">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-primary/10 text-primary border border-primary/20 flex items-center justify-center shrink-0 shadow-inner">
          <LayoutTemplate class="w-5 h-5" />
        </div>
        <div>
          <div class="flex items-center gap-2">
            <h4 class="text-sm font-bold text-foreground">
              {{ t('publishing.content.builder.bannerTitle', 'Visual Page & Block Builder') }}
            </h4>
            <Badge
              v-if="hasBuilderBlocks"
              variant="secondary"
              class="text-[10px] font-bold bg-primary/10 text-primary border border-primary/20 px-2 py-0.5 rounded-full"
            >
              {{ t('publishing.content.builder.blocksCount', { count: builderBlocksCount }) }}
            </Badge>
          </div>
          <p class="text-xs text-muted-foreground mt-0.5">
            {{ hasBuilderBlocks ? t('publishing.content.builder.hasBlocksDesc', 'Halaman ini dirancang dengan blok visual builder interaktif.') : t('publishing.content.builder.noBlocksDesc', 'Rancang halaman dengan drag & drop block responsif (Grid, Hero, Forms, Carousel).') }}
          </p>
        </div>
      </div>

      <div class="flex items-center gap-2 shrink-0">
        <Button
          type="button"
          class="h-9 px-4 rounded-xl font-semibold text-xs gap-1.5 shadow-sm bg-primary hover:bg-primary/90 text-primary-foreground"
          @click="emit('open-builder')"
        >
          <Palette class="w-4 h-4" />
          <span>{{ hasBuilderBlocks ? t('publishing.content.builder.editVisual', 'Buka di Visual Builder') : t('publishing.content.builder.launchVisual', 'Rancang dengan Visual Builder') }}</span>
        </Button>
      </div>
    </div>

    <!-- Classic Editor (Tiptap) -->
    <div class="animate-in fade-in slide-in-from-top-2 duration-300">
      <div class="flex items-center justify-between pb-2">
        <span class="text-xs font-bold uppercase tracking-wider text-muted-foreground">
          {{ hasBuilderBlocks ? t('publishing.content.builder.fallbackContent', 'Fallback Text / SEO Content (Tiptap)') : t('publishing.content.form.mainContent', 'Konten Artikel / Halaman') }}
        </span>
      </div>
      <TiptapEditor
        :model-value="modelValue.body || ''"
        class="min-h-[500px]"
        @update:model-value="updateField('body', $event)"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import TiptapEditor from '@/shared/components/editor/TiptapEditor.vue';
import { Button, Badge } from '@/shared/components/ui';
import { LayoutTemplate, Palette } from 'lucide-vue-next';
import type { ContentForm } from '@/modules/Content/Publishing/types/content';

const props = defineProps<{
  modelValue: ContentForm;
}>();

const emit = defineEmits<{
  'update:modelValue': [value: ContentForm];
  'save': [status?: string];
  'mode-selected': [mode: string | null];
  'toggle-auto-save': [value: boolean];
  'cancel': [];
  'open-builder': [];
}>();

const { t } = useI18n();

const builderBlocks = computed(() => {
  return props.modelValue.meta?.builder_blocks || [];
});

const hasBuilderBlocks = computed(() => {
  return Array.isArray(builderBlocks.value) && builderBlocks.value.length > 0;
});

const builderBlocksCount = computed(() => {
  return Array.isArray(builderBlocks.value) ? builderBlocks.value.length : 0;
});

const updateField = (field: string, value: unknown) => {
  emit('update:modelValue', { ...props.modelValue, [field]: value });
};

const updateTitle = (newTitle: string) => {
  emit('update:modelValue', { ...props.modelValue, title: newTitle });
};
</script>
