<template>
  <div class="space-y-6">
    <!-- Header & Platform Selector -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-border pb-4">
      <div>
        <h3 class="text-base font-bold tracking-tight text-foreground flex items-center gap-2">
          <Share2 class="w-4 h-4 text-primary" />
          {{ t('publishing.theme_customizer.seo.title', 'Social Share & SERP Preview') }}
        </h3>
        <p class="text-xs text-muted-foreground mt-0.5">
          {{ t('publishing.theme_customizer.seo.subtitle', 'Pratinjau visual bagaimana situs Anda tampil saat dicari di Google atau dibagikan di media sosial.') }}
        </p>
      </div>

      <!-- Platform Tabs -->
      <div class="flex items-center gap-1 bg-muted/60 p-1 rounded-xl border border-border shrink-0">
        <button
          v-for="platform in platforms"
          :key="platform.id"
          type="button"
          class="px-2.5 py-1 text-xs font-semibold rounded-lg transition-all flex items-center gap-1.5"
          :class="activePlatform === platform.id ? 'bg-background text-foreground shadow-sm' : 'text-muted-foreground hover:text-foreground'"
          @click="activePlatform = platform.id"
        >
          <component :is="platform.icon" class="w-3.5 h-3.5" />
          <span class="hidden sm:inline">{{ platform.name }}</span>
        </button>
      </div>
    </div>

    <!-- 1. Google Search SERP Preview -->
    <div
      v-if="activePlatform === 'google'"
      class="space-y-4 animate-in fade-in duration-200"
    >
      <div class="flex items-center justify-between">
        <span class="text-xs font-bold uppercase tracking-wider text-muted-foreground flex items-center gap-2">
          <Globe class="w-3.5 h-3.5 text-blue-500" />
          Google Search (SERP)
        </span>
        <div class="flex items-center gap-2 text-[11px] text-muted-foreground">
          <span>Title: <strong :class="titleLength > 60 ? 'text-amber-500' : 'text-foreground'">{{ titleLength }}/60</strong></span>
          <span>•</span>
          <span>Desc: <strong :class="descLength > 160 ? 'text-amber-500' : 'text-foreground'">{{ descLength }}/160</strong></span>
        </div>
      </div>

      <!-- SERP Result Card -->
      <div class="p-5 rounded-2xl border border-border bg-card shadow-sm space-y-2 max-w-2xl">
        <div class="flex items-center gap-2.5">
          <div class="w-6 h-6 rounded-full bg-muted border border-border flex items-center justify-center overflow-hidden shrink-0">
            <img
              v-if="faviconUrl"
              :src="faviconUrl"
              class="w-4 h-4 object-contain"
              alt="Favicon"
            >
            <Globe
              v-else
              class="w-3.5 h-3.5 text-muted-foreground"
            />
          </div>
          <div class="leading-tight truncate">
            <p class="text-xs font-medium text-foreground truncate">{{ siteTitle || 'Nama Situs' }}</p>
            <p class="text-[11px] text-muted-foreground truncate">{{ domainUrl }}</p>
          </div>
        </div>

        <h4 class="text-base sm:text-lg font-medium text-blue-600 dark:text-blue-400 hover:underline cursor-pointer leading-snug">
          {{ displayTitle }}
        </h4>

        <p class="text-xs sm:text-sm text-foreground/80 leading-relaxed line-clamp-2">
          {{ displayDescription }}
        </p>
      </div>
    </div>

    <!-- 2. Facebook Share Preview -->
    <div
      v-else-if="activePlatform === 'facebook'"
      class="space-y-4 animate-in fade-in duration-200"
    >
      <span class="text-xs font-bold uppercase tracking-wider text-muted-foreground flex items-center gap-2">
        <Facebook class="w-3.5 h-3.5 text-blue-600" />
        Facebook OpenGraph Card
      </span>

      <!-- Facebook Card -->
      <div class="rounded-2xl border border-border bg-card shadow-sm overflow-hidden max-w-xl">
        <div class="w-full h-56 bg-muted/40 flex items-center justify-center overflow-hidden border-b border-border/80 relative">
          <img
            v-if="ogImageUrl"
            :src="ogImageUrl"
            class="w-full h-full object-cover"
            alt="OG Image Preview"
          >
          <div
            v-else
            class="flex flex-col items-center gap-2 text-muted-foreground"
          >
            <ImageIcon class="w-8 h-8 opacity-40" />
            <span class="text-xs font-medium">Default Share Image (1200 x 630px)</span>
          </div>
        </div>

        <div class="p-4 bg-card space-y-1">
          <p class="text-[11px] uppercase tracking-wider font-semibold text-muted-foreground truncate">
            {{ domainHost }}
          </p>
          <h4 class="text-sm font-bold text-foreground leading-snug truncate">
            {{ displayTitle }}
          </h4>
          <p class="text-xs text-muted-foreground line-clamp-2 leading-relaxed">
            {{ displayDescription }}
          </p>
        </div>
      </div>
    </div>

    <!-- 3. Twitter / X Card Preview -->
    <div
      v-else-if="activePlatform === 'twitter'"
      class="space-y-4 animate-in fade-in duration-200"
    >
      <span class="text-xs font-bold uppercase tracking-wider text-muted-foreground flex items-center gap-2">
        <Twitter class="w-3.5 h-3.5 text-foreground" />
        Twitter / X Large Image Card
      </span>

      <!-- Twitter Card -->
      <div class="rounded-2xl border border-border bg-card shadow-sm overflow-hidden max-w-xl">
        <div class="w-full h-56 bg-muted/40 flex items-center justify-center overflow-hidden border-b border-border/80">
          <img
            v-if="ogImageUrl"
            :src="ogImageUrl"
            class="w-full h-full object-cover"
            alt="Twitter Card Preview"
          >
          <div
            v-else
            class="flex flex-col items-center gap-2 text-muted-foreground"
          >
            <ImageIcon class="w-8 h-8 opacity-40" />
            <span class="text-xs font-medium">Twitter Summary Large Image</span>
          </div>
        </div>

        <div class="p-3.5 bg-card space-y-1">
          <p class="text-[11px] text-muted-foreground flex items-center gap-1 font-medium truncate">
            <Globe class="w-3 h-3" />
            {{ domainHost }}
          </p>
          <h4 class="text-sm font-bold text-foreground leading-snug truncate">
            {{ displayTitle }}
          </h4>
          <p class="text-xs text-muted-foreground line-clamp-2 leading-relaxed">
            {{ displayDescription }}
          </p>
        </div>
      </div>
    </div>

    <!-- 4. WhatsApp Chat Preview -->
    <div
      v-else-if="activePlatform === 'whatsapp'"
      class="space-y-4 animate-in fade-in duration-200"
    >
      <span class="text-xs font-bold uppercase tracking-wider text-muted-foreground flex items-center gap-2">
        <MessageCircle class="w-3.5 h-3.5 text-emerald-500" />
        WhatsApp Chat Bubble
      </span>

      <!-- WhatsApp Bubble -->
      <div class="p-4 bg-muted/30 rounded-2xl border border-border max-w-md">
        <div class="bg-card dark:bg-card/90 rounded-2xl border border-border p-3 space-y-2 shadow-sm">
          <div class="w-full h-44 bg-muted/50 rounded-xl overflow-hidden flex items-center justify-center">
            <img
              v-if="ogImageUrl"
              :src="ogImageUrl"
              class="w-full h-full object-cover"
              alt="WhatsApp Preview"
            >
            <ImageIcon
              v-else
              class="w-8 h-8 text-muted-foreground opacity-40"
            />
          </div>

          <div class="space-y-0.5">
            <h4 class="text-xs font-bold text-foreground leading-tight truncate">
              {{ displayTitle }}
            </h4>
            <p class="text-[11px] text-muted-foreground line-clamp-2 leading-relaxed">
              {{ displayDescription }}
            </p>
            <p class="text-[10px] text-primary truncate font-medium pt-0.5">
              {{ domainUrl }}
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- 5. Schema.org JSON-LD Structured Data -->
    <div
      v-else-if="activePlatform === 'schema'"
      class="space-y-4 animate-in fade-in duration-200"
    >
      <div class="flex items-center justify-between">
        <span class="text-xs font-bold uppercase tracking-wider text-muted-foreground flex items-center gap-2">
          <Code2 class="w-3.5 h-3.5 text-primary" />
          Schema.org JSON-LD (Rich Snippets)
        </span>
        <Button
          type="button"
          variant="outline"
          size="sm"
          class="h-7 px-2.5 text-[11px] rounded-lg gap-1 font-medium"
          @click="copySchema"
        >
          <Copy class="w-3 h-3" />
          <span>{{ copied ? 'Tersalin!' : 'Salin JSON-LD' }}</span>
        </Button>
      </div>

      <div class="border border-border rounded-xl bg-muted/20 p-4 font-mono text-xs text-foreground overflow-x-auto custom-scrollbar leading-relaxed">
        <pre>{{ jsonLdString }}</pre>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { Button } from '@/shared/components/ui';
import {
  Code2,
  Copy,
  Facebook,
  Globe,
  ImageIcon,
  MessageCircle,
  Share2,
  Twitter,
} from 'lucide-vue-next';

const props = defineProps<{
  siteTitle?: string;
  siteTagline?: string;
  brandLogo?: string;
  favicon?: string;
  socialLinks?: Array<{ icon: string; url: string }>;
}>();

const { t } = useI18n();

const activePlatform = ref<'google' | 'facebook' | 'twitter' | 'whatsapp' | 'schema'>('google');

const platforms = [
  { id: 'google' as const, name: 'Google', icon: Globe },
  { id: 'facebook' as const, name: 'Facebook', icon: Facebook },
  { id: 'twitter' as const, name: 'Twitter / X', icon: Twitter },
  { id: 'whatsapp' as const, name: 'WhatsApp', icon: MessageCircle },
  { id: 'schema' as const, name: 'Schema JSON-LD', icon: Code2 },
];

const domainUrl = computed(() => (typeof window !== 'undefined' ? window.location.origin : 'https://jejakawan.com'));
const domainHost = computed(() => {
  try {
    return new URL(domainUrl.value).hostname;
  } catch {
    return 'jejakawan.com';
  }
});

const displayTitle = computed(() => {
  const title = props.siteTitle || 'Jejakawan';
  const tagline = props.siteTagline || 'CMS & Digital Platform';
  return `${title} — ${tagline}`;
});

const displayDescription = computed(() => {
  return props.siteTagline || 'Platform digital terpadu untuk publikasi konten, edukasi, dan layanan interaktif modern.';
});

const titleLength = computed(() => displayTitle.value.length);
const descLength = computed(() => displayDescription.value.length);

const faviconUrl = computed(() => props.favicon || props.brandLogo || '');
const ogImageUrl = computed(() => props.brandLogo || '');

const jsonLdData = computed(() => ({
  '@context': 'https://schema.org',
  '@type': 'WebSite',
  name: props.siteTitle || 'Jejakawan',
  url: domainUrl.value,
  description: displayDescription.value,
  publisher: {
    '@type': 'Organization',
    name: props.siteTitle || 'Jejakawan',
    logo: {
      '@type': 'ImageObject',
      url: props.brandLogo || '',
    },
    sameAs: (props.socialLinks || []).map((s) => s.url).filter(Boolean),
  },
}));

const jsonLdString = computed(() => JSON.stringify(jsonLdData.value, null, 2));

const copied = ref(false);
function copySchema() {
  if (typeof navigator !== 'undefined' && navigator.clipboard) {
    navigator.clipboard.writeText(jsonLdString.value);
    copied.value = true;
    setTimeout(() => {
      copied.value = false;
    }, 2000);
  }
}
</script>
