<template>
  <div class="block-renderer w-full space-y-6">
    <template v-for="(block, index) in internalBlocks" :key="block.id || index">
      <!-- Section Block -->
      <section
        v-if="block.type === 'section' || block.type === 'fullwidth_section'"
        :id="getSettingStr(block, 'html_id') || undefined"
        class="builder-section w-full"
        :class="[
          getSettingStr(block, 'css_class'),
          getSettingBool(block, 'fullwidth') ? 'w-full' : 'container mx-auto px-4'
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
        :id="getSettingStr(block, 'html_id') || undefined"
        class="builder-row grid gap-6"
        :class="[
          getRowGridClass(block),
          getSettingStr(block, 'css_class')
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
        :id="getSettingStr(block, 'html_id') || undefined"
        class="builder-column flex flex-col space-y-4"
        :class="getSettingStr(block, 'css_class')"
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
        :is="getSettingStr(block, 'tag', 'h2')"
        v-else-if="block.type === 'heading'"
        :id="getSettingStr(block, 'html_id') || undefined"
        class="builder-heading font-black tracking-tight text-foreground"
        :class="[
          getHeadingSizeClass(getSettingStr(block, 'size') || getSettingStr(block, 'tag')),
          getTextAlignClass(getSettingStr(block, 'alignment')),
          getSettingStr(block, 'css_class')
        ]"
        :style="resolveBlockStyles(block)"
      >
        {{ resolveDynamicText(getSettingStr(block, 'text') || getSettingStr(block, 'title')) }}
      </component>

      <!-- Text / RichText Block -->
      <div
        v-else-if="block.type === 'text' || block.type === 'rich_text'"
        :id="getSettingStr(block, 'html_id') || undefined"
        class="builder-text prose prose-slate dark:prose-invert max-w-none leading-relaxed"
        :class="[
          getTextAlignClass(getSettingStr(block, 'alignment')),
          getSettingStr(block, 'css_class')
        ]"
        :style="resolveBlockStyles(block)"
        v-html="resolveDynamicText(getSettingStr(block, 'content') || getSettingStr(block, 'text') || getSettingStr(block, 'body'))"
      />

      <!-- Image Block -->
      <figure
        v-else-if="block.type === 'image'"
        :id="getSettingStr(block, 'html_id') || undefined"
        class="builder-image overflow-hidden rounded-2xl"
        :class="[
          getTextAlignClass(getSettingStr(block, 'alignment')),
          getSettingStr(block, 'css_class')
        ]"
        :style="resolveBlockStyles(block)"
      >
        <img
          :src="getSettingStr(block, 'url') || getSettingStr(block, 'src') || getSettingStr(block, 'image')"
          :alt="getSettingStr(block, 'alt') || getSettingStr(block, 'title')"
          class="w-full h-auto object-cover rounded-2xl shadow-sm transition-transform duration-300"
          loading="lazy"
        >
        <figcaption
          v-if="getSettingStr(block, 'caption')"
          class="mt-2 text-xs text-center text-muted-foreground"
        >
          {{ getSettingStr(block, 'caption') }}
        </figcaption>
      </figure>

      <!-- Button Block -->
      <div
        v-else-if="block.type === 'button'"
        class="builder-button-wrapper"
        :class="getTextAlignClass(getSettingStr(block, 'alignment'))"
      >
        <a
          :href="getSettingStr(block, 'url') || getSettingStr(block, 'link') || '#'"
          :target="getSettingBool(block, 'open_in_new_tab') ? '_blank' : '_self'"
          :rel="getSettingBool(block, 'open_in_new_tab') ? 'noopener noreferrer' : undefined"
          class="inline-flex items-center justify-center font-bold px-6 py-3 rounded-xl transition-all shadow-sm hover:shadow hover:scale-[1.02] active:scale-[0.98]"
          :class="[
            getButtonVariantClass(getSettingStr(block, 'variant') || getSettingStr(block, 'style')),
            getSettingStr(block, 'css_class')
          ]"
          :style="resolveBlockStyles(block)"
        >
          {{ getSettingStr(block, 'text') || getSettingStr(block, 'label', 'Click Here') }}
        </a>
      </div>

      <!-- Divider / Spacer Block -->
      <div
        v-else-if="block.type === 'divider' || block.type === 'spacer'"
        class="builder-divider w-full"
        :style="{ height: `${getSettingNum(block, 'height', 24)}px` }"
      >
        <hr
          v-if="block.type === 'divider'"
          class="border-border w-full my-auto"
          :style="getSettingStr(block, 'color') ? { borderColor: getSettingStr(block, 'color') } : {}"
        >
      </div>

      <!-- HTML / Embed Block -->
      <div
        v-else-if="block.type === 'html' || block.type === 'code' || block.type === 'embed'"
        class="builder-raw-html w-full overflow-hidden"
        v-html="getSettingStr(block, 'code') || getSettingStr(block, 'html')"
      />

      <!-- Custom Component Fallback / Container -->
      <div
        v-else
        class="builder-generic-block w-full"
        :class="getSettingStr(block, 'css_class')"
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

const getSettingStr = (block: BlockInstance, key: string, fallback = ''): string => {
  const val = block.settings?.[key];
  if (typeof val === 'string') return val;
  if (typeof val === 'number') return String(val);
  return fallback;
};

const getSettingBool = (block: BlockInstance, key: string, fallback = false): boolean => {
  const val = block.settings?.[key];
  if (typeof val === 'boolean') return val;
  return fallback;
};

const getSettingNum = (block: BlockInstance, key: string, fallback = 0): number => {
  const val = block.settings?.[key];
  if (typeof val === 'number') return val;
  if (typeof val === 'string' && !isNaN(Number(val))) return Number(val);
  return fallback;
};

const resolveDynamicText = (text: string): string => {
  if (!text || typeof text !== 'string') return '';
  return text;
};

const resolveBlockStyles = (block: BlockInstance): Record<string, string> => {
  const styles: Record<string, string> = {};

  const bgColor = getSettingStr(block, 'background_color');
  if (bgColor) styles.backgroundColor = bgColor;

  const txtColor = getSettingStr(block, 'text_color');
  if (txtColor) styles.color = txtColor;

  const padTop = getSettingNum(block, 'padding_top');
  if (padTop) styles.paddingTop = `${padTop}px`;

  const padBot = getSettingNum(block, 'padding_bottom');
  if (padBot) styles.paddingBottom = `${padBot}px`;

  const marTop = getSettingNum(block, 'margin_top');
  if (marTop) styles.marginTop = `${marTop}px`;

  const marBot = getSettingNum(block, 'margin_bottom');
  if (marBot) styles.marginBottom = `${marBot}px`;

  return styles;
};

const getRowGridClass = (block: BlockInstance): string => {
  const layout = getSettingStr(block, 'layout') || String(getSettingNum(block, 'columns', 1));
  if (layout === '1/2_1/2' || layout === '2') return 'grid-cols-1 md:grid-cols-2';
  if (layout === '1/3_1/3_1/3' || layout === '3') return 'grid-cols-1 md:grid-cols-3';
  if (layout === '1/4_1/4_1/4_1/4' || layout === '4') return 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-4';
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
