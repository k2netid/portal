<template>
  <footer class="w-full border-t border-border/40 bg-muted/20 mt-auto transition-colors">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-8 lg:gap-12">
        <!-- Brand Summary -->
        <div class="space-y-4 md:col-span-2">
          <router-link
            to="/"
            class="flex items-center gap-3"
          >
            <div class="w-8 h-8 rounded-xl bg-primary text-primary-foreground flex items-center justify-center font-bold text-base shadow-sm">
              {{ siteName.charAt(0).toUpperCase() }}
            </div>
            <span class="text-lg font-bold tracking-tight text-foreground font-heading">
              {{ siteName }}
            </span>
          </router-link>
          <p class="text-sm text-muted-foreground max-w-sm leading-relaxed">
            {{ t('theme.zenith.footer.description', 'Zenith — ultra-clean, modern minimalist theme for high-performance publishing.') }}
          </p>
        </div>

        <!-- Quick Links -->
        <div class="space-y-3">
          <h4 class="text-xs font-bold uppercase tracking-wider text-foreground">
            {{ t('theme.zenith.footer.links', 'Quick Links') }}
          </h4>
          <ul class="space-y-2 text-sm text-muted-foreground">
            <li>
              <router-link
                to="/about"
                class="hover:text-foreground transition-colors"
              >
                {{ t('theme.zenith.header.about', 'About') }}
              </router-link>
            </li>
            <li>
              <router-link
                to="/solusi"
                class="hover:text-foreground transition-colors"
              >
                {{ t('theme.zenith.header.solusi', 'Solutions') }}
              </router-link>
            </li>
            <li>
              <router-link
                to="/services"
                class="hover:text-foreground transition-colors"
              >
                {{ t('theme.zenith.header.services', 'Services') }}
              </router-link>
            </li>
            <li>
              <router-link
                to="/blog"
                class="hover:text-foreground transition-colors"
              >
                {{ t('theme.zenith.header.blog', 'Blog') }}
              </router-link>
            </li>
          </ul>
        </div>

        <!-- Resources -->
        <div class="space-y-3">
          <h4 class="text-xs font-bold uppercase tracking-wider text-foreground">
            {{ t('theme.zenith.footer.resources', 'Resources') }}
          </h4>
          <ul class="space-y-2 text-sm text-muted-foreground">
            <li>
              <router-link
                to="/pricing"
                class="hover:text-foreground transition-colors"
              >
                {{ t('theme.zenith.header.pricing', 'Pricing') }}
              </router-link>
            </li>
            <li>
              <router-link
                to="/contact"
                class="hover:text-foreground transition-colors"
              >
                {{ t('theme.zenith.header.contact', 'Contact') }}
              </router-link>
            </li>
          </ul>
        </div>
      </div>

      <div class="mt-12">
        <WidgetArea location="footer_bottom" />
      </div>

      <!-- Bottom Bar -->
      <div class="mt-12 pt-8 border-t border-border/40 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-muted-foreground">
        <p>{{ copyrightText }}</p>
        <p class="text-muted-foreground/60">
          Powered by <span class="font-semibold text-foreground">JA-CMS & Zenith Theme</span>
        </p>
      </div>
    </div>
  </footer>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { useTheme } from '@/modules/Layout/composables/useTheme';
import { useSystemStore } from '@/modules/Core/System/stores/system';
import WidgetArea from '@/modules/Layout/components/widgets/WidgetArea.vue';

const { t } = useI18n();
const { getSetting } = useTheme();
const systemStore = useSystemStore();

const siteName = computed(() => {
  return String(getSetting('site_title') || systemStore.siteSettings?.site_name || systemStore.appIdentity?.app_name || 'Zenith');
});

const copyrightText = computed(() => {
  return String(getSetting('footer_copyright') || `© ${new Date().getFullYear()} ${siteName.value}. ${t('theme.zenith.footer.copyright', 'All rights reserved.')}`);
});
</script>
