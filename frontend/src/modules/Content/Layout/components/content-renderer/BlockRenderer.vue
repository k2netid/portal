<template>
  <div class="block-renderer w-full space-y-6">
    <template v-for="block in internalBlocks" :key="block.id || block._temp_id">
      <!-- Section Block -->
      <section
        v-if="block.type === 'section' || block.type === 'fullwidth_section'"
        :id="block.settings?.html_id || undefined"
        class="builder-section w-full"
        :class="[
          block.settings?.css_class || '',
          block.settings?.fullwidth ? 'w-full' : 'container mx-auto px-4'
        ]"
        :style="resolveBlockStyles(block)"
      >
        <BlockRenderer
          v-if="block.children && block.children.length > 0"
          :blocks="block.children"
          :context="context"
          :is-preview="isPreview"
          :mode="mode"
        />
      </section>

      <!-- Row Block -->
      <div
        v-else-if="block.type === 'row'"
        :id="block.settings?.html_id || undefined"
        class="builder-row grid gap-6"
        :class="[
          getRowGridClass(block),
          block.settings?.css_class || ''
        ]"
        :style="resolveBlockStyles(block)"
      >
        <BlockRenderer
          v-if="block.children && block.children.length > 0"
          :blocks="block.children"
          :context="context"
          :is-preview="isPreview"
          :mode="mode"
        />
      </div>

      <!-- Column Block -->
      <div
        v-else-if="block.type === 'column'"
        :id="block.settings?.html_id || undefined"
        class="builder-column flex flex-col space-y-4"
        :class="block.settings?.css_class || ''"
        :style="resolveBlockStyles(block)"
      >
        <BlockRenderer
          v-if="block.children && block.children.length > 0"
          :blocks="block.children"
          :context="context"
          :is-preview="isPreview"
          :mode="mode"
        />
      </div>

      <!-- Heading Block -->
      <component
        :is="block.settings?.tag || 'h2'"
        v-else-if="block.type === 'heading'"
        :id="block.settings?.html_id || undefined"
        class="builder-heading font-black tracking-tight text-foreground"
        :class="[
          getHeadingSizeClass(block.settings?.size || block.settings?.tag),
          getTextAlignClass(block.settings?.alignment),
          block.settings?.css_class || ''
        ]"
        :style="resolveBlockStyles(block)"
      >
        {{ resolveDynamicText(block.settings?.text || block.settings?.title || '') }}
      </component>

      <!-- Text / RichText Block -->
      <div
        v-else-if="block.type === 'text' || block.type === 'rich_text'"
        :id="block.settings?.html_id || undefined"
        class="builder-text prose prose-slate dark:prose-invert max-w-none leading-relaxed"
        :class="[
          getTextAlignClass(block.settings?.alignment),
          block.settings?.css_class || ''
        ]"
        :style="resolveBlockStyles(block)"
        v-html="resolveDynamicText(block.settings?.content || block.settings?.text || block.settings?.body || '')"
      />

      <!-- Image Block -->
      <figure
        v-else-if="block.type === 'image'"
        :id="block.settings?.html_id || undefined"
        class="builder-image overflow-hidden rounded-2xl"
        :class="[
          getTextAlignClass(block.settings?.alignment),
          block.settings?.css_class || ''
        ]"
        :style="resolveBlockStyles(block)"
      >
        <img
          :src="block.settings?.url || block.settings?.src || block.settings?.image || ''"
          :alt="block.settings?.alt || block.settings?.title || ''"
          class="w-full h-auto object-cover rounded-2xl shadow-sm transition-transform duration-300"
          loading="lazy"
        >
        <figcaption
          v-if="block.settings?.caption"
          class="mt-2 text-xs text-center text-muted-foreground"
        >
          {{ block.settings.caption }}
        </figcaption>
      </figure>

      <!-- Button Block -->
      <div
        v-else-if="block.type === 'button'"
        class="builder-button-wrapper"
        :class="getTextAlignClass(block.settings?.alignment)"
      >
        <a
          :href="block.settings?.url || block.settings?.link || '#'"
          :target="block.settings?.open_in_new_tab ? '_blank' : '_self'"
          :rel="block.settings?.open_in_new_tab ? 'noopener noreferrer' : undefined"
          class="inline-flex items-center justify-center font-bold px-6 py-3 rounded-xl transition-all shadow-sm hover:shadow hover:scale-[1.02] active:scale-[0.98]"
          :class="[
            getButtonVariantClass(block.settings?.variant || block.settings?.style),
            block.settings?.css_class || ''
          ]"
          :style="resolveBlockStyles(block)"
        >
          {{ block.settings?.text || block.settings?.label || 'Click Here' }}
        </a>
      </div>

      <!-- Divider / Spacer Block -->
      <div
        v-else-if="block.type === 'divider' || block.type === 'spacer'"
        class="builder-divider w-full"
        :style="{ height: (block.settings?.height || 24) + 'px' }"
      >
        <hr
          v-if="block.type === 'divider'"
          class="border-border w-full my-auto"
          :style="{ borderColor: block.settings?.color || undefined }"
        >
      </div>

      <!-- HTML / Embed Block -->
      <div
        v-else-if="block.type === 'html' || block.type === 'code' || block.type === 'embed'"
        class="builder-raw-html w-full overflow-hidden"
        v-html="block.settings?.code || block.settings?.html || ''"
      />

      <!-- Custom Component Fallback / Container -->
      <div
        v-else
        class="builder-generic-block w-full"
        :class="block.settings?.css_class || ''"
        :style="resolveBlockStyles(block)"
      >
        <BlockRenderer
          v-if="block.children && block.children.length > 0"
          :blocks="block.children"
          :context="context"
          :is-preview="isPreview"
          :mode="mode"
        />
      </div>
    </template>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import type { BlockInstance } from '@/types/builder';

const props = defineProps<{
  blocks?: BlockInstance[];
  block?: BlockInstance | null;
  context?: Record<string, any>;
  isPreview?: boolean;
  mode?: 'view' | 'edit';
}>();

const internalBlocks = computed<BlockInstance[]>(() => {
  if (props.block) return [props.block];
  return props.blocks || [];
});

const resolveDynamicText = (text: string): string => {
  if (!text || typeof text !== 'string') return '';
  return text;
};

const resolveBlockStyles = (block: BlockInstance): Record<string, string> => {
  const styles: Record<string, string> = {};
  const settings = block.settings || {};

  if (settings.background_color) {
    styles.backgroundColor = settings.background_color;
  }
  if (settings.text_color) {
    styles.color = settings.text_color;
  }
  if (settings.padding_top) {
    styles.paddingTop = `${settings.padding_top}px`;
  }
  if (settings.padding_bottom) {
    styles.paddingBottom = `${settings.padding_bottom}px`;
  }
  if (settings.margin_top) {
    styles.marginTop = `${settings.margin_top}px`;
  }
  if (settings.margin_bottom) {
    styles.marginBottom = `${settings.margin_bottom}px`;
  }

  return styles;
};

const getRowGridClass = (block: BlockInstance): string => {
  const layout = block.settings?.layout || block.settings?.columns;
  if (layout === '1/2_1/2' || layout === 2) return 'grid-cols-1 md:grid-cols-2';
  if (layout === '1/3_1/3_1/3' || layout === 3) return 'grid-cols-1 md:grid-cols-3';
  if (layout === '1/4_1/4_1/4_1/4' || layout === 4) return 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-4';
  if (layout === '1/3_2/3') return 'grid-cols-1 md:grid-cols-12 md:[&>*:first-child]:col-span-4 md:[&>*:last-child]:col-span-8';
  if (layout === '2/3_1/3') return 'grid-cols-1 md:grid-cols-12 md:[&>*:first-child]:col-span-8 md:[&>*:last-child]:col-span-4';
  return 'grid-cols-1';
};

const getHeadingSizeClass = (size?: string): string => {
  switch (size) {
    case 'h1':
    case 'xlarge':
      return 'text-4xl md:text-5xl lg:text-6xl';
    case 'h2':
    case 'large':
      return 'text-3xl md:text-4xl';
    case 'h3':
    case 'medium':
      return 'text-2xl md:text-3xl';
    case 'h4':
    case 'small':
      return 'text-xl md:text-2xl';
    case 'h5':
      return 'text-lg md:text-xl';
    default:
      return 'text-2xl md:text-3xl';
  }
};

const getTextAlignClass = (alignment?: string): string => {
  switch (alignment) {
    case 'center':
      return 'text-center justify-center';
    case 'right':
      return 'text-right justify-end';
    case 'justify':
      return 'text-justify';
    default:
      return 'text-left justify-start';
  }
};

const getButtonVariantClass = (variant?: string): string => {
  switch (variant) {
    case 'secondary':
      return 'bg-secondary text-secondary-foreground hover:bg-secondary/80';
    case 'outline':
      return 'border-2 border-primary text-primary hover:bg-primary hover:text-primary-foreground';
    case 'ghost':
      return 'bg-transparent text-primary hover:bg-primary/10';
    default:
      return 'bg-primary text-primary-foreground hover:bg-primary/90';
  }
};
</script>
