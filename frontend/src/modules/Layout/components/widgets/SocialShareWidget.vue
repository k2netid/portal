<template>
  <div class="universal-widget social-share-widget rounded-2xl border border-border/70 bg-card p-5 shadow-sm space-y-3.5">
    <div class="flex items-center justify-between border-b border-border/60 pb-3">
      <div class="flex items-center gap-2">
        <span class="w-1 h-4 bg-primary rounded-full" />
        <h3 class="text-sm font-bold text-foreground font-heading tracking-tight">
          {{ widgetTitle }}
        </h3>
      </div>
      <Share2 class="w-4 h-4 text-muted-foreground" />
    </div>

    <!-- Share Buttons Grid -->
    <div class="grid grid-cols-2 gap-2">
      <!-- WhatsApp -->
      <a
        :href="whatsappUrl"
        target="_blank"
        rel="noopener noreferrer"
        class="flex items-center gap-2.5 p-2.5 rounded-xl border border-border/60 bg-muted/30 hover:bg-emerald-500/10 hover:border-emerald-500/30 hover:text-emerald-600 dark:hover:text-emerald-400 transition-all group"
      >
        <div class="w-7 h-7 rounded-lg bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
          <MessageCircle class="w-4 h-4" />
        </div>
        <span class="text-xs font-medium truncate text-foreground group-hover:text-emerald-600 dark:group-hover:text-emerald-400">WhatsApp</span>
      </a>

      <!-- Telegram -->
      <a
        :href="telegramUrl"
        target="_blank"
        rel="noopener noreferrer"
        class="flex items-center gap-2.5 p-2.5 rounded-xl border border-border/60 bg-muted/30 hover:bg-sky-500/10 hover:border-sky-500/30 hover:text-sky-600 dark:hover:text-sky-400 transition-all group"
      >
        <div class="w-7 h-7 rounded-lg bg-sky-500/15 text-sky-600 dark:text-sky-400 flex items-center justify-center shrink-0">
          <Send class="w-3.5 h-3.5" />
        </div>
        <span class="text-xs font-medium truncate text-foreground group-hover:text-sky-600 dark:group-hover:text-sky-400">Telegram</span>
      </a>

      <!-- X / Twitter -->
      <a
        :href="twitterUrl"
        target="_blank"
        rel="noopener noreferrer"
        class="flex items-center gap-2.5 p-2.5 rounded-xl border border-border/60 bg-muted/30 hover:bg-foreground/10 hover:border-foreground/20 hover:text-foreground transition-all group"
      >
        <div class="w-7 h-7 rounded-lg bg-foreground/10 text-foreground flex items-center justify-center shrink-0">
          <Twitter class="w-3.5 h-3.5" />
        </div>
        <span class="text-xs font-medium truncate text-foreground">X (Twitter)</span>
      </a>

      <!-- Facebook -->
      <a
        :href="facebookUrl"
        target="_blank"
        rel="noopener noreferrer"
        class="flex items-center gap-2.5 p-2.5 rounded-xl border border-border/60 bg-muted/30 hover:bg-blue-600/10 hover:border-blue-600/30 hover:text-blue-600 dark:hover:text-blue-400 transition-all group"
      >
        <div class="w-7 h-7 rounded-lg bg-blue-600/15 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0">
          <Facebook class="w-3.5 h-3.5" />
        </div>
        <span class="text-xs font-medium truncate text-foreground group-hover:text-blue-600 dark:group-hover:text-blue-400">Facebook</span>
      </a>
    </div>

    <!-- Copy Link Bar -->
    <div class="pt-1">
      <button
        type="button"
        class="w-full flex items-center justify-between p-2.5 rounded-xl border transition-all text-xs"
        :class="copied ? 'border-emerald-500/40 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 font-semibold' : 'border-border/60 bg-muted/40 hover:bg-muted text-muted-foreground hover:text-foreground'"
        @click="handleCopyLink"
      >
        <div class="flex items-center gap-2 truncate pr-2">
          <Check v-if="copied" class="w-4 h-4 text-emerald-600 dark:text-emerald-400 shrink-0" />
          <Link2 v-else class="w-4 h-4 shrink-0" />
          <span class="truncate">{{ copied ? linkCopiedText : targetUrl }}</span>
        </div>
        <span class="shrink-0 text-[11px] font-medium px-2 py-0.5 rounded-md" :class="copied ? 'bg-emerald-500/20 text-emerald-700 dark:text-emerald-300' : 'bg-background text-foreground border border-border/50'">
          {{ copied ? 'Tersalin' : copyLinkText }}
        </span>
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { Share2, MessageCircle, Send, Twitter, Facebook, Link2, Check } from 'lucide-vue-next';

const props = defineProps<{
  widget?: Record<string, any>;
  title?: string;
  post?: { title?: string; slug?: string };
  shareUrl?: string;
}>();

const { t, te } = useI18n();

const widgetTitle = computed(() => {
  if (props.title) return props.title;
  if (props.widget?.title) return props.widget.title;
  return te('layout.widgets.universal.socialShare.title')
    ? t('layout.widgets.universal.socialShare.title')
    : 'Bagikan Warta';
});

const copyLinkText = computed(() => {
  return te('layout.widgets.universal.socialShare.copyLink')
    ? t('layout.widgets.universal.socialShare.copyLink')
    : 'Salin Tautan';
});

const linkCopiedText = computed(() => {
  return te('layout.widgets.universal.socialShare.linkCopied')
    ? t('layout.widgets.universal.socialShare.linkCopied')
    : 'Tautan disalin ke papan klip!';
});

const targetUrl = computed(() => {
  if (props.shareUrl) return props.shareUrl;
  if (typeof window !== 'undefined') return window.location.href;
  return '';
});

const shareText = computed(() => {
  if (props.post?.title) return props.post.title;
  if (typeof document !== 'undefined') return document.title;
  return 'Warta & Informasi';
});

const whatsappUrl = computed(() => {
  return `https://api.whatsapp.com/send?text=${encodeURIComponent(shareText.value + ' ' + targetUrl.value)}`;
});

const telegramUrl = computed(() => {
  return `https://t.me/share/url?url=${encodeURIComponent(targetUrl.value)}&text=${encodeURIComponent(shareText.value)}`;
});

const twitterUrl = computed(() => {
  return `https://twitter.com/intent/tweet?text=${encodeURIComponent(shareText.value)}&url=${encodeURIComponent(targetUrl.value)}`;
});

const facebookUrl = computed(() => {
  return `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(targetUrl.value)}`;
});

const copied = ref(false);
let copyResetTimer: ReturnType<typeof setTimeout> | null = null;

const handleCopyLink = async () => {
  if (typeof navigator !== 'undefined' && navigator.clipboard) {
    try {
      await navigator.clipboard.writeText(targetUrl.value);
      copied.value = true;
      if (copyResetTimer) clearTimeout(copyResetTimer);
      copyResetTimer = setTimeout(() => {
        copied.value = false;
      }, 3000);
    } catch {
      // Fallback
    }
  }
};
</script>
