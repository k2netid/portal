<template>
  <footer
    data-ja-customizer-target="footer"
    class="border-t-0 bg-slate-950 text-slate-300 transition-colors relative z-10 font-sans"
  >
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-12">
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-6 xl:gap-8">
        <!-- Col 1: Brand & Bandung Office Address -->
        <div class="lg:col-span-4 space-y-4">
          <div class="flex items-center gap-3.5">
            <BrandMark
              v-if="brandingDisplay !== 'text_only'"
              :src="siteLogo"
              :alt="displayCompanyName"
            />
            <div v-if="brandingDisplay !== 'logo_only'">
              <span class="text-xl font-extrabold tracking-tight text-white font-heading block">
                {{ displayCompanyName }}
              </span>
              <span class="text-xs text-sky-400 font-medium">
                {{ displayTagline }}
              </span>
            </div>
          </div>

          <p class="text-sm text-slate-400 leading-relaxed max-w-sm">
            {{ footerAboutText }}
          </p>

          <div
            v-if="displayAddress"
            class="pt-1 text-xs text-slate-500 leading-relaxed max-w-sm flex items-start gap-2 border-t border-slate-900 pt-3"
          >
            <MapPin class="w-4 h-4 text-sky-400/90 shrink-0 mt-0.5" />
            <span>{{ displayAddress }}</span>
          </div>
        </div>

        <!-- Col 2-5: Navigation & Social Grid (2-column on mobile, 4-column on tablet/desktop) -->
        <div class="lg:col-span-8 grid grid-cols-2 md:grid-cols-4 gap-x-6 sm:gap-x-8 gap-y-8 items-start">
          <!-- Col 2: ISP Services (Menu Col 1) -->
          <div class="space-y-3">
            <h4 class="text-[11px] font-bold uppercase tracking-[0.12em] text-white font-heading">
              {{ col1Title }}
            </h4>
            <ul class="space-y-2 text-[13px] text-slate-400">
              <li
                v-for="item in footerCol1Items"
                :key="String(item.id || item.title)"
              >
                <a
                  v-if="isExternalLink(item.url)"
                  :href="item.url || '#'"
                  target="_blank"
                  rel="noopener noreferrer"
                  class="hover:text-sky-400 transition-colors break-words"
                >
                  {{ item.title }}
                </a>
                <router-link
                  v-else
                  :to="resolvePublicMenuTo(item.url)"
                  class="hover:text-sky-400 transition-colors break-words"
                >
                  {{ item.title }}
                </router-link>
              </li>
            </ul>
          </div>

          <!-- Col 3: MSP Solutions (Menu Col 2) -->
          <div class="space-y-3">
            <h4 class="text-[11px] font-bold uppercase tracking-[0.12em] text-white font-heading">
              {{ col2Title }}
            </h4>
            <ul class="space-y-2 text-[13px] text-slate-400">
              <li
                v-for="item in footerCol2Items"
                :key="String(item.id || item.title)"
              >
                <a
                  v-if="isExternalLink(item.url)"
                  :href="item.url || '#'"
                  target="_blank"
                  rel="noopener noreferrer"
                  class="hover:text-sky-400 transition-colors break-words"
                >
                  {{ item.title }}
                </a>
                <router-link
                  v-else
                  :to="resolvePublicMenuTo(item.url)"
                  class="hover:text-sky-400 transition-colors break-words"
                >
                  {{ item.title }}
                </router-link>
              </li>
            </ul>
          </div>

          <!-- Col 4: IT Products (Menu Col 3) -->
          <div class="space-y-3">
            <h4 class="text-[11px] font-bold uppercase tracking-[0.12em] text-white font-heading">
              {{ col3Title }}
            </h4>
            <ul class="space-y-2 text-[13px] text-slate-400">
              <li
                v-for="item in footerCol3Items"
                :key="String(item.id || item.title)"
              >
                <a
                  v-if="isExternalLink(item.url)"
                  :href="item.url || '#'"
                  target="_blank"
                  rel="noopener noreferrer"
                  class="hover:text-sky-400 transition-colors break-words"
                >
                  {{ item.title }}
                </a>
                <router-link
                  v-else
                  :to="resolvePublicMenuTo(item.url)"
                  class="hover:text-sky-400 transition-colors break-words"
                >
                  {{ item.title }}
                </router-link>
              </li>
            </ul>
          </div>

          <!-- Col 5: Social Media / Ikuti Kami -->
          <div class="space-y-3">
            <h4 class="text-[11px] font-bold uppercase tracking-[0.12em] text-white font-heading">
              {{ tt('footer.social', 'Media Sosial') }}
            </h4>
            <p class="text-xs text-slate-400 leading-relaxed">
              {{ tt('footer.socialDesc', 'Ikuti perkembangan jaringan, promo produk, dan kabar terbaru kami.') }}
            </p>
            <div
              v-if="effectiveSocialLinks.length"
              class="flex flex-wrap gap-2 pt-1"
            >
              <a
                v-for="(link, idx) in effectiveSocialLinks"
                :key="idx"
                :href="resolveSocialHref(link)"
                :target="getSocialTarget(link)"
                :rel="getSocialRel(link)"
                class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-800 bg-slate-900/60 text-slate-400 hover:text-sky-400 hover:border-sky-500/40 hover:bg-sky-500/10 transition-all shadow-sm"
                :aria-label="getSocialAriaLabel(link)"
                :title="link.label || link.icon"
              >
                <component
                  :is="getSocialIcon(link.icon)"
                  class="w-4 h-4"
                />
              </a>
            </div>
          </div>
        </div>
      </div>

      <!-- Bottom Bar: Copyright & Quick Links -->
      <div class="mt-10 pt-6 border-t border-slate-800/80 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-500">
        <p>{{ footerCopyrightText }}</p>
        <div class="flex items-center gap-4 flex-wrap justify-center">
          <template v-if="footerItems.length">
            <template
              v-for="(item, idx) in footerItems"
              :key="String(item.id || item.title)"
            >
              <span
                v-if="idx > 0"
                class="text-slate-700"
              >·</span>
              <a
                v-if="isExternalLink(item.url)"
                :href="item.url || '#'"
                target="_blank"
                rel="noopener noreferrer"
                class="hover:text-slate-300 transition-colors"
              >
                {{ item.title }}
              </a>
              <router-link
                v-else
                :to="resolvePublicMenuTo(item.url)"
                class="hover:text-slate-300 transition-colors"
              >
                {{ item.title }}
              </router-link>
            </template>
          </template>
          <template v-else>
            <router-link
              to="/about"
              class="hover:text-slate-300 transition-colors"
            >
              {{ tt('header.about', 'Tentang Kami') }}
            </router-link>
            <span>·</span>
            <router-link
              to="/contact"
              class="hover:text-slate-300 transition-colors"
            >
              {{ tt('header.contact', 'Kontak') }}
            </router-link>
            <span>·</span>
            <router-link
              to="/search"
              class="hover:text-slate-300 transition-colors"
            >
              {{ tt('header.search', 'Pencarian') }}
            </router-link>
          </template>
        </div>
      </div>
    </div>

    <!-- Floating Social Dock -->
    <FloatingSocialDock />
  </footer>
</template>

<script setup lang="ts">
import { computed, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import {
  MessageCircle,
  Twitter, Instagram, Facebook, Youtube, Linkedin, Github, Music2, Globe, Mail, MapPin,
} from 'lucide-vue-next';
import BrandMark from '@/modules/Layout/views/themes/layung/components/layout/BrandMark.vue';
import FloatingSocialDock from '@/modules/Layout/views/themes/layung/components/layout/FloatingSocialDock.vue';
import { useTheme } from '@/modules/Layout/composables/useTheme';
import { useMenu } from '@/modules/Layout/composables/useMenu';
import { useThemeI18n } from '@/modules/Layout/composables/useThemeI18n';
import { useLayungIdentity } from '@/modules/Layout/views/themes/layung/composables/useLayungIdentity';
import {
  resolveLayungLocalizedCopy,
} from '@/modules/Layout/views/themes/layung/composables/resolveLayungLocalizedCopy';
import {
  LAYUNG_STORE_SHOPEE,
  LAYUNG_STORE_SIPLAH_BLIBLI,
  LAYUNG_STORE_SIPLAH_TOKOLADANG,
  LAYUNG_STORE_TOKOPEDIA,
} from '@/modules/Layout/views/themes/layung/composables/layungStoreUrls';
import type { MenuItem } from '@/modules/Layout/types/menu';
import { resolvePublicMenuTo } from '@/modules/Layout/utils/menuUrl';

const { locale } = useI18n({ useScope: 'global' });
const { t: tt } = useThemeI18n('layung');
const { getSetting } = useTheme();
const { menus, fetchMenuByIdentifier } = useMenu();
const {
  displayCompanyName,
  displayBrandLogo,
  displayAddress,
  displayTagline,
} = useLayungIdentity();

const brandingDisplay = computed(() => String(getSetting('branding_display', 'logo_only') || 'logo_only'));

const siteLogo = displayBrandLogo;

const socialLinks = computed(() => (getSetting('social_links') as Array<{ icon?: string; url?: string; label?: string }>) || []);

const defaultSocialLinks = [
  { icon: 'Instagram', url: 'https://instagram.com/jejakawan', label: 'Instagram' },
  { icon: 'Github', url: 'https://github.com/jejakawan/k2net', label: 'GitHub' },
  { icon: 'MessageCircle', url: 'https://wa.me/6285136290851', label: 'WhatsApp' },
];

const effectiveSocialLinks = computed(() => (socialLinks.value.length ? socialLinks.value : defaultSocialLinks));

const footerAboutText = computed(() => {
  const custom = resolveLayungLocalizedCopy({
    getSetting,
    locale: locale.value,
    key: 'footer_about_text',
    fallback: '',
  });

  if (custom && custom.trim() && !/AS153992|165\.99\.252/.test(custom)) {
    return custom.trim();
  }
  return tt(
    'footer.description',
    'K2NET (PT Kirana Karina Network) — ISP, managed services, dan penyedia produk IT dari koneksi internet hingga pengadaan perangkat.',
  );
});

const footerCopyrightText = computed(() => {
  const custom = resolveLayungLocalizedCopy({
    getSetting,
    locale: locale.value,
    key: 'footer_text',
    fallback: '',
  }) || resolveLayungLocalizedCopy({
    getSetting,
    locale: locale.value,
    key: 'footer_copyright',
    fallback: '',
  });

  if (custom && custom.trim()) return custom.trim();
  return `© ${new Date().getFullYear()} ${displayCompanyName.value}. ${tt('footer.copyright', 'Hak cipta dilindungi undang-undang.')}`;
});

const col1Title = computed(() => {
  const custom = resolveLayungLocalizedCopy({
    getSetting,
    locale: locale.value,
    key: 'footer_col_1_title',
    fallback: '',
  });
  if (custom && custom.trim()) return custom.trim();
  return tt('footer.isp', 'ISP — Konektivitas');
});

const col2Title = computed(() => {
  const custom = resolveLayungLocalizedCopy({
    getSetting,
    locale: locale.value,
    key: 'footer_col_2_title',
    fallback: '',
  });
  if (custom && custom.trim()) return custom.trim();
  return tt('footer.msp', 'MSP — Layanan IT');
});

const col3Title = computed(() => {
  const custom = resolveLayungLocalizedCopy({
    getSetting,
    locale: locale.value,
    key: 'footer_col_3_title',
    fallback: '',
  });
  if (custom && custom.trim()) return custom.trim();
  return tt('footer.products', 'Produk IT');
});

const normalizeMenuSetting = (value: unknown, fallback: string): string => {
  if (value === null || value === undefined || value === '' || value === 'none') return fallback;
  return String(value);
};

const trimStr = (v: unknown): string => {
  if (v == null) return '';
  if (typeof v !== 'string') return String(v).trim();
  return v.trim();
};

const toWhatsAppDialDigits = (input: string): string => {
  const d = input.replace(/\D/g, '');
  if (!d) return '';
  if (d.startsWith('62')) return d;
  if (d.startsWith('0')) return `62${d.slice(1)}`;
  if (d.startsWith('8')) return `62${d}`;
  return d;
};

const isExternalLink = (url?: string | null) => {
  if (!url) return false;
  return url.startsWith('http://') || url.startsWith('https://') || url.startsWith('mailto:') || url.startsWith('tel:');
};

const getSocialIcon = (key?: string) => {
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
    case 'Mail':
    case 'Email':
      return Mail;
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

const getSocialRel = (link: { icon?: string; url?: string }) =>
  (getSocialTarget(link) ? 'noopener noreferrer' : undefined);

const getSocialAriaLabel = (link: { icon?: string; url?: string }) => {
  const icon = trimStr(link?.icon) || 'social';
  return `Kunjungi ${icon}`;
};

const defaultCol1Items = computed((): Partial<MenuItem>[] => [
  { title: tt('header.services', 'Internet'), url: '/pricing/isp' },
  { title: tt('header.pricingIsp', 'Paket Internet'), url: '/pricing/isp' },
  { title: tt('header.about', 'Tentang Kami'), url: '/about' },
  { title: tt('header.achievement', 'SLA'), url: '/achievement' },
]);

const defaultCol2Items = computed((): Partial<MenuItem>[] => [
  { title: tt('header.solusi', 'Managed Services'), url: '/solusi' },
  { title: tt('header.pricingMsp', 'Paket MSP'), url: '/pricing/msp' },
  { title: tt('header.tim', 'Tim'), url: '/tim' },
  { title: tt('header.career', 'Karir'), url: '/career' },
]);

const defaultCol3Items = computed((): Partial<MenuItem>[] => [
  { title: tt('footer.storeOffline', 'Toko Offline'), url: '/contact' },
  { title: 'Tokopedia K2NET', url: LAYUNG_STORE_TOKOPEDIA },
  { title: 'Shopee K2NET', url: LAYUNG_STORE_SHOPEE },
  { title: 'SIPLah Blibli', url: LAYUNG_STORE_SIPLAH_BLIBLI },
  { title: 'SIPLah Toko Ladang', url: LAYUNG_STORE_SIPLAH_TOKOLADANG },
]);

const contactPageEnabled = computed(() => getSetting('enable_contact', true) !== false);

const isContactMenuPath = (url?: string | null): boolean => {
  if (!url) return false;
  const target = resolvePublicMenuTo(url);
  const withoutQuery = target.split('?')[0] ?? '';
  const path = withoutQuery.split('#')[0]?.replace(/\/+$/, '') || '/';
  return path === '/contact';
};

const filterFooterItems = (items: Partial<MenuItem>[]): Partial<MenuItem>[] => {
  if (contactPageEnabled.value) return items;
  return items.filter((item) => !isContactMenuPath(item.url));
};

const footerCol1Items = computed(() => filterFooterItems(
  menus.value['footer_col_1']?.items?.length ? menus.value['footer_col_1'].items : defaultCol1Items.value,
));

const footerCol2Items = computed(() => filterFooterItems(
  menus.value['footer_col_2']?.items?.length ? menus.value['footer_col_2'].items : defaultCol2Items.value,
));

const footerCol3Items = computed(() => filterFooterItems(
  menus.value['footer_col_3']?.items?.length ? menus.value['footer_col_3'].items : defaultCol3Items.value,
));

const footerItems = computed(() => filterFooterItems((menus.value['footer']?.items || []) as MenuItem[]));

onMounted(() => {
  fetchMenuByIdentifier(normalizeMenuSetting(getSetting('menu_location_footer_col_1', 'footer_col_1'), 'footer_col_1'), 'footer_col_1');
  fetchMenuByIdentifier(normalizeMenuSetting(getSetting('menu_location_footer_col_2', 'footer_col_2'), 'footer_col_2'), 'footer_col_2');
  fetchMenuByIdentifier(normalizeMenuSetting(getSetting('menu_location_footer_col_3', 'footer_col_3'), 'footer_col_3'), 'footer_col_3');
  fetchMenuByIdentifier(normalizeMenuSetting(getSetting('menu_location_footer', 'footer'), 'footer'), 'footer');
});
</script>
