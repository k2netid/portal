<template>
  <div class="space-y-4 min-w-0 overflow-hidden">
    <div class="space-y-3 border-b border-border pb-3 min-w-0">
      <div class="min-w-0">
        <h3 class="text-sm font-bold tracking-tight text-foreground flex items-center gap-2 min-w-0">
          <Share2 class="w-4 h-4 text-primary shrink-0" />
          <span class="truncate">{{ t('publishing.theme_customizer.seo.title', 'Social Share & SERP Preview') }}</span>
        </h3>
        <p class="text-xs text-muted-foreground mt-1 leading-relaxed break-words">
          {{ t('publishing.theme_customizer.seo.subtitle', 'Pratinjau visual bagaimana situs Anda tampil saat dicari di Google atau dibagikan di media sosial.') }}
        </p>
      </div>

      <!-- Full-width wrap tabs — never overflow the narrow controls rail -->
      <div
        class="grid grid-cols-5 gap-0.5 bg-muted/60 p-1 rounded-xl border border-border min-w-0"
        role="tablist"
        :aria-label="t('publishing.theme_customizer.seo.title', 'Social Share & SERP Preview')"
      >
        <button
          v-for="platform in platforms"
          :key="platform.id"
          type="button"
          role="tab"
          :aria-selected="activePlatform === platform.id"
          class="min-w-0 px-0.5 py-1.5 text-[9px] font-semibold rounded-lg transition-all flex flex-col items-center justify-center gap-0.5"
          :class="activePlatform === platform.id ? 'bg-background text-foreground shadow-sm' : 'text-muted-foreground hover:text-foreground'"
          :title="platform.name"
          @click="activePlatform = platform.id"
        >
          <component
            :is="platform.icon"
            class="w-3.5 h-3.5 shrink-0"
          />
          <span class="truncate max-w-full leading-tight">{{ platform.short }}</span>
        </button>
      </div>
    </div>

    <!-- Google Search SERP Preview -->
    <div
      v-if="activePlatform === 'google'"
      class="space-y-3 animate-in fade-in duration-200 min-w-0"
    >
      <div class="flex flex-col gap-1.5 min-w-0">
        <span class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground flex items-center gap-1.5">
          <Globe class="w-3.5 h-3.5 text-blue-500 shrink-0" />
          Google Search (SERP)
        </span>
        <div class="flex flex-wrap items-center gap-x-2 gap-y-0.5 text-[10px] text-muted-foreground">
          <span>Title: <strong :class="titleLength > 60 ? 'text-amber-500' : 'text-foreground'">{{ titleLength }}/60</strong></span>
          <span class="text-muted-foreground/40">•</span>
          <span>Desc: <strong :class="descLength > 160 ? 'text-amber-500' : 'text-foreground'">{{ descLength }}/160</strong></span>
        </div>
      </div>

      <div class="p-3.5 rounded-xl border border-border bg-card shadow-sm space-y-2 min-w-0 overflow-hidden">
        <div class="flex items-center gap-2 min-w-0">
          <div class="w-6 h-6 rounded-full bg-muted border border-border flex items-center justify-center overflow-hidden shrink-0">
            <img
              v-if="faviconUrl"
              :src="faviconUrl"
              class="w-4 h-4 object-contain"
              alt=""
            >
            <Globe
              v-else
              class="w-3.5 h-3.5 text-muted-foreground"
            />
          </div>
          <div class="leading-tight min-w-0 flex-1">
            <p class="text-xs font-medium text-foreground truncate">{{ siteTitle || 'Nama Situs' }}</p>
            <p class="text-[11px] text-muted-foreground truncate">{{ domainUrl }}</p>
          </div>
        </div>

        <h4 class="text-sm font-medium text-blue-600 dark:text-blue-400 leading-snug break-words">
          {{ displayTitle }}
        </h4>

        <p class="text-xs text-foreground/80 leading-relaxed line-clamp-3 break-words">
          {{ displayDescription }}
        </p>
      </div>
    </div>

    <!-- Facebook -->
    <div
      v-else-if="activePlatform === 'facebook'"
      class="space-y-3 animate-in fade-in duration-200 min-w-0"
    >
      <span class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground flex items-center gap-1.5">
        <Facebook class="w-3.5 h-3.5 text-blue-600 shrink-0" />
        Facebook OpenGraph
      </span>

      <div class="rounded-xl border border-border bg-card shadow-sm overflow-hidden min-w-0">
        <div class="w-full aspect-[1.91/1] max-h-40 bg-muted/40 flex items-center justify-center overflow-hidden border-b border-border/80">
          <img
            v-if="ogImageUrl"
            :src="ogImageUrl"
            class="w-full h-full object-cover"
            alt=""
          >
          <div
            v-else
            class="flex flex-col items-center gap-1 text-muted-foreground px-3 text-center"
          >
            <ImageIcon class="w-7 h-7 opacity-40" />
            <span class="text-[10px] font-medium">1200 × 630</span>
          </div>
        </div>

        <div class="p-3 bg-card space-y-1 min-w-0">
          <p class="text-[10px] uppercase tracking-wider font-semibold text-muted-foreground truncate">
            {{ domainHost }}
          </p>
          <h4 class="text-sm font-bold text-foreground leading-snug break-words line-clamp-2">
            {{ displayTitle }}
          </h4>
          <p class="text-xs text-muted-foreground line-clamp-2 leading-relaxed break-words">
            {{ displayDescription }}
          </p>
        </div>
      </div>
    </div>

    <!-- Twitter / X -->
    <div
      v-else-if="activePlatform === 'twitter'"
      class="space-y-3 animate-in fade-in duration-200 min-w-0"
    >
      <span class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground flex items-center gap-1.5">
        <Twitter class="w-3.5 h-3.5 text-foreground shrink-0" />
        Twitter / X Card
      </span>

      <div class="rounded-xl border border-border bg-card shadow-sm overflow-hidden min-w-0">
        <div class="w-full aspect-[1.91/1] max-h-40 bg-muted/40 flex items-center justify-center overflow-hidden border-b border-border/80">
          <img
            v-if="ogImageUrl"
            :src="ogImageUrl"
            class="w-full h-full object-cover"
            alt=""
          >
          <div
            v-else
            class="flex flex-col items-center gap-1 text-muted-foreground px-3 text-center"
          >
            <ImageIcon class="w-7 h-7 opacity-40" />
            <span class="text-[10px] font-medium">Summary large image</span>
          </div>
        </div>

        <div class="p-3 bg-card space-y-1 min-w-0">
          <p class="text-[10px] text-muted-foreground flex items-center gap-1 font-medium truncate">
            <Globe class="w-3 h-3 shrink-0" />
            {{ domainHost }}
          </p>
          <h4 class="text-sm font-bold text-foreground leading-snug break-words line-clamp-2">
            {{ displayTitle }}
          </h4>
          <p class="text-xs text-muted-foreground line-clamp-2 leading-relaxed break-words">
            {{ displayDescription }}
          </p>
        </div>
      </div>
    </div>

    <!-- WhatsApp -->
    <div
      v-else-if="activePlatform === 'whatsapp'"
      class="space-y-3 animate-in fade-in duration-200 min-w-0"
    >
      <span class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground flex items-center gap-1.5">
        <MessageCircle class="w-3.5 h-3.5 text-emerald-500 shrink-0" />
        WhatsApp
      </span>

      <div class="p-3 bg-muted/30 rounded-xl border border-border min-w-0">
        <div class="bg-card rounded-xl border border-border p-2.5 space-y-2 shadow-sm min-w-0">
          <div class="w-full aspect-video max-h-36 bg-muted/50 rounded-lg overflow-hidden flex items-center justify-center">
            <img
              v-if="ogImageUrl"
              :src="ogImageUrl"
              class="w-full h-full object-cover"
              alt=""
            >
            <ImageIcon
              v-else
              class="w-7 h-7 text-muted-foreground opacity-40"
            />
          </div>

          <div class="space-y-0.5 min-w-0">
            <h4 class="text-xs font-bold text-foreground leading-tight break-words line-clamp-2">
              {{ displayTitle }}
            </h4>
            <p class="text-[11px] text-muted-foreground line-clamp-2 leading-relaxed break-words">
              {{ displayDescription }}
            </p>
            <p class="text-[10px] text-primary truncate font-medium pt-0.5">
              {{ domainUrl }}
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Schema -->
    <div
      v-else-if="activePlatform === 'schema'"
      class="space-y-3 animate-in fade-in duration-200 min-w-0"
    >
      <div class="flex flex-col gap-2 min-w-0">
        <span class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground flex items-center gap-1.5">
          <Code2 class="w-3.5 h-3.5 text-primary shrink-0" />
          Schema.org JSON-LD
        </span>
        <Button
          type="button"
          variant="outline"
          size="sm"
          class="h-8 w-full px-2.5 text-[11px] rounded-lg gap-1 font-medium"
          @click="copySchema"
        >
          <Copy class="w-3 h-3" />
          <span>{{ copied ? 'Tersalin!' : 'Salin JSON-LD' }}</span>
        </Button>
      </div>

      <div class="border border-border rounded-xl bg-muted/20 p-3 font-mono text-[10px] text-foreground overflow-x-auto custom-scrollbar leading-relaxed max-h-64 overflow-y-auto">
        <pre class="min-w-0 whitespace-pre-wrap break-all">{{ jsonLdString }}</pre>
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
  { id: 'google' as const, name: 'Google', short: 'Google', icon: Globe },
  { id: 'facebook' as const, name: 'Facebook', short: 'FB', icon: Facebook },
  { id: 'twitter' as const, name: 'Twitter / X', short: 'X', icon: Twitter },
  { id: 'whatsapp' as const, name: 'WhatsApp', short: 'WA', icon: MessageCircle },
  { id: 'schema' as const, name: 'Schema JSON-LD', short: 'JSON', icon: Code2 },
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
