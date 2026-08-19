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

      <!-- Navigation Menu Block -->
      <nav
        v-else-if="block.type === 'menu' || block.type === 'fullwidth_menu'"
        :id="getSettingStr(block, 'html_id') || undefined"
        :aria-label="getSettingStr(block, 'aria_label', 'Navigation Menu')"
        class="builder-menu-block w-full py-2"
        :class="[
          getTextAlignClass(getSettingStr(block, 'alignment')),
          getSettingStr(block, 'css_class')
        ]"
        :style="resolveBlockStyles(block)"
      >
        <div class="flex flex-wrap items-center gap-3">
          <template v-for="(item, itemIdx) in getMenuItems(getSettingStr(block, 'menuId'))" :key="itemIdx">
            <a
              :href="item.url || '#'"
              class="text-sm font-semibold text-foreground/80 hover:text-primary transition-colors py-1.5 px-3 rounded-lg hover:bg-primary/10"
              :target="item.open_in_new_tab ? '_blank' : '_self'"
            >
              {{ item.title }}
            </a>
          </template>
        </div>
      </nav>

      <!-- Dynamic Blog / Posts Query Loop Block -->
      <div
        v-else-if="block.type === 'blog' || block.type === 'posts' || block.type === 'query_loop'"
        :id="getSettingStr(block, 'html_id') || undefined"
        class="builder-blog-block w-full py-4"
        :class="getSettingStr(block, 'css_class')"
        :style="resolveBlockStyles(block)"
      >
        <div v-if="getSettingStr(block, 'title')" class="mb-6">
          <h3 class="text-2xl font-bold text-foreground">
            {{ getSettingStr(block, 'title') }}
          </h3>
        </div>

        <div
          class="grid gap-6"
          :class="getSettingNum(block, 'columns', 3) === 2 ? 'grid-cols-1 md:grid-cols-2' : (getSettingNum(block, 'columns', 3) === 4 ? 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-4' : 'grid-cols-1 md:grid-cols-2 lg:grid-cols-3')"
        >
          <template v-for="(post, pIdx) in getSamplePosts(getSettingNum(block, 'itemsPerPage', 3))" :key="pIdx">
            <article class="group rounded-2xl border border-border bg-card/60 p-5 shadow-sm transition-all hover:shadow-md hover:border-primary/40 flex flex-col">
              <figure v-if="getSettingBool(block, 'showImage', true)" class="overflow-hidden rounded-xl bg-muted aspect-video mb-4">
                <img :src="post.image" :alt="post.title" class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105" loading="lazy">
              </figure>
              <div v-if="getSettingBool(block, 'showCategory', true)" class="mb-2">
                <span class="text-[10px] font-bold uppercase tracking-wider text-primary bg-primary/10 px-2 py-0.5 rounded-md">
                  {{ post.category }}
                </span>
              </div>
              <h4 class="text-base font-bold text-foreground line-clamp-2 mb-2 group-hover:text-primary transition-colors">
                <a :href="post.url">{{ post.title }}</a>
              </h4>
              <p v-if="getSettingBool(block, 'showExcerpt', true)" class="text-xs text-muted-foreground line-clamp-3 mb-4 leading-relaxed flex-1">
                {{ post.excerpt }}
              </p>
              <div v-if="getSettingBool(block, 'showDate', true) || getSettingBool(block, 'showAuthor', true)" class="pt-3 border-t border-border/50 flex items-center justify-between text-[11px] text-muted-foreground mt-auto">
                <span v-if="getSettingBool(block, 'showAuthor', true)">{{ post.author }}</span>
                <span v-if="getSettingBool(block, 'showDate', true)">{{ post.date }}</span>
              </div>
            </article>
          </template>
        </div>
      </div>

      <!-- Form Picker / Contact Form Block -->
      <div
        v-else-if="block.type === 'form_picker' || block.type === 'contact_form'"
        :id="getSettingStr(block, 'html_id') || undefined"
        class="builder-form-block w-full max-w-2xl mx-auto rounded-2xl border border-border bg-card p-6 md:p-8 shadow-sm"
        :class="getSettingStr(block, 'css_class')"
        :style="resolveBlockStyles(block)"
      >
        <div v-if="getSettingBool(block, 'show_title', true)" class="mb-4">
          <h3 class="text-xl font-bold text-foreground">
            {{ getSettingStr(block, 'title', 'Hubungi Kami') }}
          </h3>
          <p v-if="getSettingBool(block, 'show_description', true) && getSettingStr(block, 'description')" class="text-sm text-muted-foreground mt-1">
            {{ getSettingStr(block, 'description') }}
          </p>
        </div>

        <form class="space-y-4" @submit.prevent="handleFormSubmit">
          <div class="space-y-1.5">
            <label class="text-xs font-semibold text-foreground">Nama Lengkap</label>
            <input type="text" required placeholder="Masukkan nama..." class="w-full h-10 px-3 text-sm rounded-xl border border-border bg-background focus:outline-none focus:ring-2 focus:ring-primary/20">
          </div>
          <div class="space-y-1.5">
            <label class="text-xs font-semibold text-foreground">Email</label>
            <input type="email" required placeholder="name@example.com" class="w-full h-10 px-3 text-sm rounded-xl border border-border bg-background focus:outline-none focus:ring-2 focus:ring-primary/20">
          </div>
          <div class="space-y-1.5">
            <label class="text-xs font-semibold text-foreground">Pesan</label>
            <textarea rows="4" required placeholder="Tuliskan pesan Anda..." class="w-full p-3 text-sm rounded-xl border border-border bg-background focus:outline-none focus:ring-2 focus:ring-primary/20"></textarea>
          </div>
          <button type="submit" class="w-full h-10 rounded-xl bg-primary text-primary-foreground font-semibold text-sm hover:bg-primary/90 transition-colors shadow-sm">
            {{ getSettingStr(block, 'button_text', 'Kirim Pesan') }}
          </button>
        </form>
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
import { useToast } from '@/shared/composables/useToast';
import type { BlockInstance } from '@/types/builder';

const props = defineProps<{
  blocks?: BlockInstance[];
  block?: BlockInstance | null;
  context?: Record<string, any>;
  isPreview?: boolean;
  mode?: 'view' | 'edit';
}>();

const toast = useToast();

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

const getMenuItems = (_menuId?: string): Array<{ title: string; url: string; open_in_new_tab?: boolean }> => {
  return [
    { title: 'Beranda', url: '/' },
    { title: 'Tentang Kami', url: '/about' },
    { title: 'Layanan', url: '/services' },
    { title: 'Kontak', url: '/contact' }
  ];
};

const getSamplePosts = (count = 3): Array<{ title: string; excerpt: string; date: string; author: string; category: string; image: string; url: string }> => {
  const samples = [
    {
      title: 'Inovasi Teknologi Terkini dalam Pengembangan Website Modern',
      excerpt: 'Mengenal pendekatan modular dan visual builder yang mempercepat produktivitas tim pengembang.',
      date: '19 Agu 2026',
      author: 'Redaksi',
      category: 'Teknologi',
      image: '/assets/themes/janari/news-placeholder.png',
      url: '/blog'
    },
    {
      title: 'Strategi Optimasi SEO & Metadata untuk Meningkatkan Trafik',
      excerpt: 'Panduan lengkap mengatur OpenGraph, Schema JSON-LD, dan struktur konten yang ramah mesin pencari.',
      date: '18 Agu 2026',
      author: 'Admin',
      category: 'Insight',
      image: '/assets/themes/janari/hero-placeholder.png',
      url: '/blog'
    },
    {
      title: 'Penerapan Design Tokens dan Tema Dinamis pada Web Skala Besar',
      excerpt: 'Bagaimana CSS Custom Properties menyatukan Theme Customizer dengan kanvas Visual Builder.',
      date: '17 Agu 2026',
      author: 'Tim Desain',
      category: 'Design System',
      image: '/assets/themes/janari/avatar-placeholder.png',
      url: '/blog'
    }
  ];
  return samples.slice(0, Math.max(1, count));
};

const handleFormSubmit = () => {
  toast.success.default('Formulir berhasil dikirim!');
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
