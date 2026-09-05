<template>
  <footer
    data-ja-customizer-target="footer"
    class="bg-background text-foreground/90 mt-auto border-t border-border relative"
  >
    <!-- Signature Gradient Accent Line (Matching Header) -->
    <div class="absolute top-0 left-0 w-full h-[1px] bg-gradient-to-r from-transparent via-primary/60 to-transparent z-10" />
    <div class="absolute top-0 left-0 w-full h-[1px] bg-primary/10 blur-[2px] z-0" />

    <div class="container mx-auto px-6 py-10 md:py-12">
      <div
        ref="footerGridRef"
        :class="['grid gap-8 md:gap-10', gridClass]"
      >
        <!-- Brand / Masthead -->
        <div class="space-y-4">
          <div class="flex items-center gap-4">
            <div 
              v-if="brandingDisplay !== 'text_only'"
              class="relative flex items-center justify-center overflow-hidden"
            >
              <img 
                v-if="siteLogo" 
                :src="siteLogo" 
                class="h-7 w-auto object-contain brightness-100" 
                :alt="siteName"
                width="140"
                height="28"
                loading="lazy"
                decoding="async"
                sizes="140px"
              >
              <div 
                v-else
                class="w-8 h-8 rounded-none bg-foreground flex items-center justify-center text-background font-black text-lg"
              >
                {{ siteName.substring(0, 1).toUpperCase() }}
              </div>
            </div>

            <div 
              v-if="brandingDisplay !== 'logo_only'"
              class="flex flex-col"
            >
              <span class="text-xl font-heading font-black tracking-[-0.05em] uppercase text-foreground leading-none">{{ siteName }}</span>
              <span
                v-if="brandingDisplay === 'both'"
                class="text-[8px] font-bold tracking-[0.3em] uppercase text-foreground/70 mt-1"
              >
                {{ siteVersion }}
              </span>
            </div>
          </div>
          <p class="text-foreground/75 text-xs leading-relaxed max-w-xs font-medium">
            {{ brandDescription }}
          </p>
                    
          <!-- Social Links -->
          <div
            ref="socialLinksRef"
            class="flex gap-4"
          >
            <a
              v-for="(link, idx) in socialLinks"
              :key="idx"
              :href="resolveSocialHref(link)"
              :target="getSocialTarget(link)"
              :rel="getSocialRel(link)"
              class="motion-social text-foreground/70 hover:text-primary transform hover:scale-110 hover:-translate-y-1 transition-all duration-300 cubic-bezier(0.37, 0.01, 0, 0.98)"
              :aria-label="getSocialAriaLabel(link)"
            >
              <component
                :is="getSocialIcon(link.icon)"
                class="w-4 h-4"
              />
            </a>
          </div>
        </div>

        <!-- Footer Column 1 -->
        <div
          v-if="(footerCol1Items?.length || 0) > 0"
          class="space-y-4"
        >
          <h2 class="text-[10px] font-black uppercase tracking-[0.35em] text-foreground/75">
            {{ col1Title }}
          </h2>
          <ul class="space-y-2">
            <li
              v-for="item in footerCol1Items"
              :key="String(item.id || item.title)"
            >
              <router-link
                :to="item.url || '/'"
                class="text-foreground/75 hover:text-primary text-[11px] font-black uppercase tracking-[0.2em] transition-all duration-300 cubic-bezier(0.37, 0.01, 0, 0.98) group flex items-center gap-2"
              >
                <span class="w-1.5 h-[1px] bg-primary/0 group-hover:w-3 group-hover:bg-primary transition-all duration-300" />
                {{ item.title }}
              </router-link>
            </li>
          </ul>
        </div>

        <!-- Footer Column 2 -->
        <div
          v-if="(footerCol2Items?.length || 0) > 0"
          class="space-y-4"
        >
          <h2 class="text-[10px] font-black uppercase tracking-[0.35em] text-foreground/75">
            {{ col2Title }}
          </h2>
          <ul class="space-y-2">
            <li
              v-for="item in footerCol2Items"
              :key="String(item.id || item.title)"
            >
              <router-link
                :to="item.url || '/'"
                class="text-foreground/75 hover:text-primary text-[11px] font-black uppercase tracking-[0.2em] transition-all duration-300 cubic-bezier(0.37, 0.01, 0, 0.98) group flex items-center gap-2"
              >
                <span class="w-1.5 h-[1px] bg-primary/0 group-hover:w-3 group-hover:bg-primary transition-all duration-300" />
                {{ item.title }}
              </router-link>
            </li>
          </ul>
        </div>

        <!-- Newsletter (High-end Contrast) -->
        <div
          v-if="newsletterEnabled"
          class="space-y-4"
        >
          <h2 class="text-[10px] font-black uppercase tracking-[0.25em] text-foreground/90">
            {{ newsletterTitle }}
          </h2>
          <p class="text-foreground/75 text-xs leading-relaxed font-medium">
            {{ newsletterDescription }}
          </p>
          <form
            class="flex flex-col gap-2"
            @submit.prevent="submitNewsletter"
          >
            <div class="flex gap-2">
              <input 
                v-model="email"
                type="email" 
                :placeholder="newsletterPlaceholder" 
                class="flex-1 px-3 py-2.5 bg-muted/30 border border-border text-foreground placeholder-foreground/30 text-xs focus:border-primary/50 outline-none transition-colors"
              >
              <button 
                type="submit" 
                :disabled="loading"
                class="px-4 min-w-10 min-h-10 bg-foreground text-background hover:bg-primary hover:text-primary-foreground transition-colors disabled:opacity-50"
                :aria-label="loading ? tt('footer.newsletterSendingAria') : tt('footer.newsletterSubmitAria')"
              >
                <Loader2
                  v-if="loading"
                  class="w-4 h-4 animate-spin"
                />
                <ArrowRight
                  v-else
                  class="w-4 h-4"
                />
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Bottom Bar -->
      <div :class="['mt-8 md:mt-10 pt-6 border-t border-border flex justify-between items-center gap-4', isDesktop ? 'flex-row' : 'flex-col']">
        <p class="text-foreground/75 text-[9px] uppercase tracking-[0.2em] font-black flex flex-wrap gap-x-4 gap-y-1">
          <span v-if="copyrightText">{{ copyrightText }}</span>
          <template v-else>
            <span>&copy; {{ new Date().getFullYear() }} <a
              :href="brandUrl"
              target="_blank"
              class="hover:text-primary transition-colors"
            >{{ siteName }}</a></span>
            <span class="hidden md:inline text-foreground/5">/</span>
            <span>{{ developedByText }} <a
              :href="brandUrl"
              target="_blank"
              rel="noopener noreferrer"
              class="hover:text-primary transition-colors"
            >{{ brandLinkText }}</a></span>
          </template>
        </p>
                
        <div class="flex gap-6">
          <router-link 
            v-for="item in footerItems" 
            :key="String(item.id || item.title)" 
            :to="item.url || '/'"
            class="text-foreground/80 hover:text-foreground text-[9px] font-black uppercase tracking-[0.3em] transition-colors"
          >
            {{ item.title }}
          </router-link>
          <template v-if="footerItems.length === 0">
            <router-link
              :to="privacyUrl"
              class="text-foreground/80 hover:text-foreground text-[9px] font-black uppercase tracking-[0.3em] transition-colors"
            >
              {{ privacyLabel }}
            </router-link>
            <router-link
              :to="termsUrl"
              class="text-foreground/80 hover:text-foreground text-[9px] font-black uppercase tracking-[0.3em] transition-colors"
            >
              {{ termsLabel }}
            </router-link>
          </template>
        </div>
      </div>
    </div>
    <JanariFloatingSocialDock />
  </footer>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, nextTick } from 'vue'
import { useI18n } from 'vue-i18n'
import JanariFloatingSocialDock from '@/modules/Layout/views/themes/janari/components/layout/JanariFloatingSocialDock.vue'
import { useThemeI18n } from '@/modules/Layout/composables/useThemeI18n'
import { useTheme } from '@/modules/Layout/composables/useTheme'
import { useLocalizedThemeSetting } from '@/modules/Layout/composables/useLocalizedThemeSetting'
import { useMenu } from '@/modules/Layout/composables/useMenu'
import { useSystemStore } from '@/modules/Core/System/stores/system'
import { useToast } from '@/shared/composables/useToast'
import { useFormValidation } from '@/shared/composables/useFormValidation'
import { useResponsiveDevice } from '@/shared/composables/useResponsiveDevice';
import { useThemeMotion } from '@/modules/Layout/composables/useThemeMotion';
import { useJanariIdentity, trimStr, toWhatsAppDialDigits } from '@/modules/Layout/views/themes/janari/composables/useJanariIdentity';
import { newsletterSchema } from '@/shared/schemas'
import { NewsletterService } from '@/modules/Newsletter/services/newsletterService'
import type { MenuItem } from '@/modules/Layout/types/menu';
import {
  Twitter,
  Instagram,
  Facebook,
  Youtube,
  Linkedin,
  Github,
  Music2,
  Mail,
  MessageCircle,
  Globe,
  Loader2,
  ArrowRight,
} from 'lucide-vue-next';

const { t } = useI18n()
const { t: tt } = useThemeI18n('janari')
const { getSetting, themeSettings } = useTheme()
const { localizedString } = useLocalizedThemeSetting()
const { menus, fetchMenuByIdentifier } = useMenu()
const device = useResponsiveDevice();
const { staggerChildren, magneticHover } = useThemeMotion();
const toast = useToast()

const footerGridRef = ref<HTMLElement>()
const socialLinksRef = ref<HTMLElement>()
const { validateWithZod, clearErrors } = useFormValidation(newsletterSchema)

const isDesktop = computed(() => device.value === 'desktop');
const isTablet = computed(() => device.value === 'tablet');

const gridClass = computed(() => {
    if (isDesktop.value) return 'grid-cols-4';
    if (isTablet.value) return 'grid-cols-2';
    return 'grid-cols-1';
});

const loading = ref(false)
const email = ref('')

const brandingDisplay = computed(() => getSetting('branding_display', 'logo_only'));
const systemStore = useSystemStore();
const newsletterEnabled = computed(() => systemStore.activeExtensions.includes('newsletter'));
const siteSettings = computed(() => systemStore.settings);
const { displaySiteName, displaySiteDescription } = useJanariIdentity();

const siteName = computed(() => displaySiteName.value);
const siteLogo = computed((): string => {
    const fromSetting = getSetting('brand_logo');
    const fromSite = siteSettings.value?.site_logo;
    return (typeof fromSetting === 'string' ? fromSetting : '')
        || (typeof fromSite === 'string' ? fromSite : '');
});
const siteVersion = computed(() => siteSettings.value?.site_version || tt('footer.versionDefault'));

const brandDescription = computed(() =>
  displaySiteDescription.value
    || localizedString('footer_description')
    || tt('footer.descriptionDefault'),
)
const col1Title = computed(() =>
  menus.value['footer_col_1']?.name
    || localizedString('footer_col1_title')
    || tt('footer.browse'),
)
const col2Title = computed(() =>
  menus.value['footer_col_2']?.name
    || localizedString('footer_col2_title')
    || tt('footer.support'),
)
const newsletterTitle = computed(() =>
  localizedString('footer_newsletter_title') || t('publishing.frontend.newsletter.title'),
)
const newsletterDescription = computed(() =>
  localizedString('footer_newsletter_description') || t('publishing.frontend.newsletter.description'),
)
const newsletterPlaceholder = computed(() =>
  localizedString('footer_newsletter_placeholder') || t('publishing.frontend.newsletter.placeholder'),
)
const developedByText = computed(() =>
  localizedString('footer_developed_by') || tt('footer.developedBy'),
)
const brandLinkText = computed(() =>
  localizedString('footer_brand_link') || tt('footer.brandLink'),
)
const privacyLabel = computed(() =>
  localizedString('footer_privacy_label') || t('publishing.frontend.footer.privacy'),
)
const termsLabel = computed(() =>
  localizedString('footer_terms_label') || t('publishing.frontend.footer.terms'),
)
const copyrightText = computed(() => {
  const fromTheme = localizedString('footer_copyright')
  if (fromTheme.trim()) return fromTheme.trim()
  const saved = themeSettings.value?.footer_text
  if (typeof saved === 'string' && saved.trim()) return saved.trim()
  return ''
})
const privacyUrl = computed(() => {
  const raw = getSetting('footer_privacy_url', '/privacy')
  return typeof raw === 'string' && raw.trim() ? raw.trim() : '/privacy'
})
const termsUrl = computed(() => {
  const raw = getSetting('footer_terms_url', '/terms')
  return typeof raw === 'string' && raw.trim() ? raw.trim() : '/terms'
})
const brandUrl = computed(() => {
  const raw = getSetting('footer_brand_url', 'https://jejakawan.com')
  return typeof raw === 'string' && raw.trim() ? raw.trim() : 'https://jejakawan.com'
})

const socialLinks = computed(() => (getSetting('social_links') as any[]) || []);

const normalizeMenuSetting = (value: unknown, fallback: string): string => {
    if (value === null || value === undefined || value === '' || value === 'none') {
        return fallback;
    }
    return String(value);
};

const getSocialIcon = (key: string) => {
    switch (key) {
        case 'Twitter': return Twitter;
        case 'Instagram': return Instagram;
        case 'Facebook': return Facebook;
        case 'Youtube': return Youtube;
        case 'Linkedin': return Linkedin;
        case 'Github': return Github;
        case 'Music2': return Music2;
        case 'MessageCircle':
        case 'WhatsApp':
            return MessageCircle;
        case 'Mail': return Mail;
        case 'Email': return Mail;
        default: return Globe;
    }
};

const resolveSocialHref = (link: { icon?: string; url?: string }) => {
    const icon = trimStr(link?.icon);
    const raw = trimStr(link?.url);
    if (!raw) return '#';

    if (icon === 'Mail' || icon === 'Email') {
        if (raw.startsWith('mailto:')) return raw;
        if (/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(raw)) return `mailto:${raw}`;
        return raw;
    }

    if (icon === 'MessageCircle' || icon === 'WhatsApp') {
        if (raw.includes('wa.me/') || raw.includes('api.whatsapp.com/') || raw.includes('whatsapp.com/')) {
            return raw.startsWith('http') ? raw : `https://${raw.replace(/^\/+/, '')}`;
        }
        const digits = toWhatsAppDialDigits(raw);
        return digits ? `https://wa.me/${digits}` : '#';
    }

    return raw;
};

const getSocialTarget = (link: { icon?: string; url?: string }) => {
    const href = resolveSocialHref(link);
    if (href.startsWith('mailto:') || href.startsWith('tel:') || href === '#') return undefined;
    return '_blank';
};

const getSocialRel = (link: { icon?: string; url?: string }) => {
    return getSocialTarget(link) ? 'noopener noreferrer' : undefined;
};

const getSocialAriaLabel = (link: { icon?: string; url?: string }) => {
    const icon = trimStr(link?.icon) || 'social';
    const href = resolveSocialHref(link);
    if (href.startsWith('mailto:')) return tt('footer.socialAriaEmail', { icon });
    if (href.startsWith('tel:')) return tt('footer.socialAriaPhone', { icon });
    if (href === '#') return tt('footer.socialAriaEmpty', { icon });
    try {
        const parsed = new URL(href, window.location.origin);
        const path = parsed.pathname.replace(/^\/+/, '');
        const target = path ? `${parsed.hostname}/${path}` : parsed.hostname;
        return tt('footer.socialAriaVisit', { icon, target });
    } catch {
        return tt('footer.socialAriaVisit', { icon, target: icon });
    }
};

onMounted(async () => {
    fetchMenuByIdentifier(normalizeMenuSetting(getSetting('menu_location_footer_col_1', 'footer_col_1'), 'footer_col_1'), 'footer_col_1')
    fetchMenuByIdentifier(normalizeMenuSetting(getSetting('menu_location_footer_col_2', 'footer_col_2'), 'footer_col_2'), 'footer_col_2')
    fetchMenuByIdentifier(normalizeMenuSetting(getSetting('menu_location_footer', 'footer'), 'footer'), 'footer')

    await nextTick()

    if (footerGridRef.value) {
        staggerChildren(footerGridRef.value, ':scope > div', {
            distance: 30,
            stagger: 0.12,
            duration: 0.8,
            start: 'top 95%',
        })
    }

    if (socialLinksRef.value) {
        const socialIcons = socialLinksRef.value.querySelectorAll('.motion-social')
        socialIcons.forEach((icon) => {
            magneticHover(icon, 0.4)
        })
    }
})

const defaultCol1Items = computed((): Partial<MenuItem>[] => [
    { title: tt('footer.defaultAbout'), url: '/about' },
    { title: tt('footer.defaultNews'), url: '/blog' },
    { title: tt('footer.defaultContact'), url: '/contact' },
]);

const defaultCol2Items = computed((): Partial<MenuItem>[] => [
    { title: tt('footer.defaultAchievements'), url: '/achievement' },
    { title: tt('footer.defaultCareers'), url: '/career' },
]);

const footerCol1Items = computed(() => {
    const menu = menus.value['footer_col_1'];
    return (menu?.items && menu.items.length > 0) ? menu.items : defaultCol1Items.value;
});
const footerCol2Items = computed(() => {
    const menu = menus.value['footer_col_2'];
    return (menu?.items && menu.items.length > 0) ? menu.items : defaultCol2Items.value;
});
const footerItems = computed(() => (menus.value['footer']?.items || []) as MenuItem[]);

const submitNewsletter = async () => {
    if (!validateWithZod({ email: email.value })) return

    loading.value = true
    clearErrors()
    
    try {
        await NewsletterService.subscribe({ email: email.value })
        toast.success.action(t('publishing.frontend.newsletter.success'))
        email.value = ''
    } catch (error: unknown) {
        toast.error.action(error)
    } finally {
        loading.value = false
    }
}
</script>
