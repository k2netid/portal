<template>
  <footer class="bg-card/70 border-t border-border/50 relative z-20 py-12 md:py-16">
    <!-- Ambient Top Line Accent -->
    <div class="absolute top-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-primary/50 to-transparent" />

    <div class="container mx-auto px-4 md:px-8">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-10 pb-12 border-b border-border/40">
        <!-- Brand & Mission Column -->
        <div class="md:col-span-2 flex flex-col pr-4">
          <router-link to="/" class="inline-flex items-center gap-3 mb-4">
            <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-primary to-secondary flex items-center justify-center text-primary-foreground font-black text-sm shadow-md shadow-primary/25">
              {{ siteName.substring(0, 1).toUpperCase() }}
            </div>
            <span class="text-xl font-heading font-black tracking-tight uppercase text-foreground">
              {{ siteName }}
            </span>
          </router-link>

          <p class="text-sm text-muted-foreground leading-relaxed max-w-md mb-6">
            {{ footerDescription }}
          </p>

          <!-- Live System Status Badge -->
          <div class="inline-flex items-center gap-2.5 px-3 py-1.5 rounded-full bg-emerald-500/10 border border-emerald-500/30 text-emerald-600 dark:text-emerald-400 text-xs font-mono w-fit">
            <span class="relative flex h-2 w-2">
              <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75" />
              <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500" />
            </span>
            <span>{{ t('common.statusOnline') }}</span>
          </div>
        </div>

        <!-- Solutions Links -->
        <div class="flex flex-col gap-3">
          <span class="text-xs font-mono uppercase tracking-widest text-foreground font-bold">
            {{ t('bento.badge') }}
          </span>
          <router-link to="/solusi" class="text-sm text-muted-foreground hover:text-primary transition-colors">
            Core Engine Architecture
          </router-link>
          <router-link to="/solusi" class="text-sm text-muted-foreground hover:text-primary transition-colors">
            Edge Deployment
          </router-link>
          <router-link to="/solusi" class="text-sm text-muted-foreground hover:text-primary transition-colors">
            Visual Canvas Studio
          </router-link>
          <router-link to="/pricing" class="text-sm text-muted-foreground hover:text-primary transition-colors">
            {{ t('header.pricing') }}
          </router-link>
        </div>

        <!-- Ecosystem Links -->
        <div class="flex flex-col gap-3">
          <span class="text-xs font-mono uppercase tracking-widest text-foreground font-bold">
            Developer
          </span>
          <router-link to="/blog" class="text-sm text-muted-foreground hover:text-primary transition-colors">
            {{ t('header.blog') }}
          </router-link>
          <router-link to="/contact" class="text-sm text-muted-foreground hover:text-primary transition-colors">
            {{ t('header.contact') }}
          </router-link>
          <a
            href="https://github.com/jejak-awan"
            target="_blank"
            rel="noopener noreferrer"
            class="text-sm text-muted-foreground hover:text-primary transition-colors"
          >
            GitHub Repository
          </a>
          <router-link to="/search" class="text-sm text-muted-foreground hover:text-primary transition-colors">
            {{ t('header.search') }}
          </router-link>
        </div>
      </div>

      <!-- Bottom Bar -->
      <div class="pt-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-muted-foreground">
        <p>© {{ currentYear }} {{ siteName }}. {{ t('footer.copyright') }}</p>
        <p class="font-mono text-[11px] text-foreground/50">
          {{ t('footer.tagline') }}
        </p>
      </div>
    </div>
  </footer>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useTheme } from '@/modules/Layout/composables/useTheme';
import { useLocalizedThemeSetting } from '@/modules/Layout/composables/useLocalizedThemeSetting';
import { useThemeI18n } from '@/modules/Layout/composables/useThemeI18n';

const { getSetting } = useTheme();
const { localizedString } = useLocalizedThemeSetting();
const { t } = useThemeI18n('sareupna');

const siteName = computed(() => (getSetting('site_title') as string) || 'PT Jejak Awan Digital');
const footerDescription = computed(
  () => localizedString('site_description') || t('footer.description')
);
const currentYear = new Date().getFullYear();
</script>
