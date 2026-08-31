<template>
  <footer
    data-ja-customizer-target="footer"
    class="border-t border-border/80 bg-slate-950 text-slate-300 transition-colors relative z-10 font-sans"
  >
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14 sm:py-16">
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-10 lg:gap-8">
        <div class="lg:col-span-2 space-y-4">
          <div class="flex items-center gap-3.5">
            <div
              v-if="brandingDisplay !== 'text_only'"
              class="w-11 h-11 rounded-2xl bg-gradient-to-br from-orange-500 to-amber-600 text-white flex items-center justify-center font-black text-xl shadow-md shadow-orange-500/20 overflow-hidden"
              aria-hidden="true"
            >
              <img
                v-if="siteLogo"
                :src="siteLogo"
                :alt="displayCompanyName"
                class="h-full w-full object-contain p-1"
              >
              <Activity
                v-else
                class="w-6 h-6"
              />
            </div>
            <div v-if="brandingDisplay !== 'logo_only'">
              <span class="text-xl font-extrabold tracking-tight text-white font-heading block">
                {{ displayCompanyName }}
              </span>
              <span class="text-xs text-orange-400 font-bold font-mono">
                {{ displayAsn }}
              </span>
            </div>
          </div>

          <p class="text-sm text-slate-400 leading-relaxed max-w-md">
            {{ footerAboutText }}
          </p>

          <div
            v-if="socialLinks.length"
            class="flex flex-wrap gap-3 pt-1"
          >
            <a
              v-for="(link, idx) in socialLinks"
              :key="idx"
              :href="resolveSocialHref(link)"
              :target="getSocialTarget(link)"
              :rel="getSocialRel(link)"
              class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-700 text-slate-400 hover:text-orange-400 hover:border-orange-500/40 transition-colors"
              :aria-label="getSocialAriaLabel(link)"
            >
              <component
                :is="getSocialIcon(link.icon)"
                class="w-4 h-4"
              />
            </a>
          </div>

          <div class="flex flex-wrap gap-2 pt-1 font-mono">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-500/15 text-emerald-400 border border-emerald-500/30">
              <ShieldCheck class="w-3.5 h-3.5" />
              99.999% SLA Uptime Guarantee
            </span>
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-cyan-500/15 text-cyan-400 border border-cyan-500/30">
              <Server class="w-3.5 h-3.5" />
              Tier-3 Data Center Interconnect
            </span>
          </div>
        </div>

        <div class="space-y-4">
          <h4 class="text-sm font-bold uppercase tracking-wider text-white font-heading">
            {{ col1Title }}
          </h4>
          <ul class="space-y-2.5 text-sm text-slate-400">
            <li
              v-for="item in footerCol1Items"
              :key="String(item.id || item.title)"
            >
              <a
                v-if="isExternalLink(item.url)"
                :href="item.url || '#'"
                target="_blank"
                rel="noopener noreferrer"
                class="hover:text-orange-400 transition-colors"
              >
                {{ item.title }}
              </a>
              <router-link
                v-else
                :to="item.url || '/'"
                class="hover:text-orange-400 transition-colors"
              >
                {{ item.title }}
              </router-link>
            </li>
          </ul>
        </div>

        <div class="space-y-4">
          <h4 class="text-sm font-bold uppercase tracking-wider text-white font-heading">
            {{ col2Title }}
          </h4>
          <ul class="space-y-2.5 text-sm text-slate-400">
            <li
              v-for="item in footerCol2Items"
              :key="String(item.id || item.title)"
            >
              <a
                v-if="isExternalLink(item.url)"
                :href="item.url || '#'"
                target="_blank"
                rel="noopener noreferrer"
                class="hover:text-orange-400 transition-colors"
              >
                {{ item.title }}
              </a>
              <router-link
                v-else
                :to="item.url || '/'"
                class="hover:text-orange-400 transition-colors"
              >
                {{ item.title }}
              </router-link>
            </li>
          </ul>
        </div>

        <div class="space-y-4">
          <h4 class="text-sm font-bold uppercase tracking-wider text-white font-heading">
            {{ tt('footer.contact', 'Pusat Bantuan 24/7') }}
          </h4>
          <div class="space-y-2 text-xs text-slate-400 leading-relaxed">
            <p class="flex items-start gap-2">
              <MapPin class="w-4 h-4 text-orange-400 shrink-0 mt-0.5" />
              <span>{{ displayAddress }}</span>
            </p>
            <p class="flex items-center gap-2">
              <Phone class="w-4 h-4 text-orange-400 shrink-0" />
              <a
                :href="nocDialHref"
                class="hover:text-white font-semibold font-mono"
              >{{ displayNocPhone }} (NOC 24/7)</a>
            </p>
            <p class="flex items-center gap-2">
              <Mail class="w-4 h-4 text-orange-400 shrink-0" />
              <a
                :href="`mailto:${displayEmail}`"
                class="hover:text-white"
              >{{ displayEmail }}</a>
            </p>
            <p
              v-if="nocWhatsAppUrl"
              class="pt-1.5"
            >
              <a
                :href="nocWhatsAppUrl"
                target="_blank"
                rel="noopener noreferrer"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-[var(--layung-radius-sm)] bg-emerald-600 text-white font-bold text-xs hover:bg-emerald-700 transition-colors shadow-sm"
              >
                <MessageCircle class="w-3.5 h-3.5" />
                WhatsApp NOC & Sales
              </a>
            </p>
          </div>
        </div>
      </div>

      <div class="mt-12 pt-6 border-t border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-500">
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
                :to="item.url || '/'"
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
              {{ tt('header.about', 'Profil') }}
            </router-link>
            <span>·</span>
            <router-link
              to="/contact"
              class="hover:text-slate-300 transition-colors"
            >
              {{ tt('header.contact', 'Kontak NOC') }}
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
  </footer>
</template>

<script setup lang="ts">
import { computed, onMounted } from 'vue';
import {
  Activity, ShieldCheck, Server, MapPin, Phone, Mail, MessageCircle,
  Twitter, Instagram, Facebook, Youtube, Linkedin, Github, Music2, Globe,
} from 'lucide-vue-next';
import { useTheme } from '@/modules/Layout/composables/useTheme';
import { useMenu } from '@/modules/Layout/composables/useMenu';
import { useThemeI18n } from '@/modules/Layout/composables/useThemeI18n';
import { useSystemStore } from '@/modules/Core/System/stores/system';
import { useLayungIdentity } from '@/modules/Layout/views/themes/layung/composables/useLayungIdentity';
import type { MenuItem } from '@/modules/Layout/types/menu';

const { t: tt } = useThemeI18n('layung');
const { getSetting } = useTheme();
const { menus, fetchMenuByIdentifier } = useMenu();
const systemStore = useSystemStore();
const {
  displayCompanyName,
  displayAsn,
  displayAddress,
  displayNocPhone,
  displayEmail,
  nocDialHref,
  nocWhatsAppUrl,
} = useLayungIdentity();

const brandingDisplay = computed(() => String(getSetting('branding_display', 'both') || 'both'));

const siteLogo = computed(() => {
  const custom = getSetting('brand_logo', '');
  if (custom && typeof custom === 'string') return custom;
  return (systemStore.settings as { site_logo?: string } | undefined)?.site_logo || '';
});

const socialLinks = computed(() => (getSetting('social_links') as Array<{ icon?: string; url?: string }>) || []);

const footerAboutText = computed(() => {
  const fromTheme = getSetting('footer_about_text', '');
  if (fromTheme && typeof fromTheme === 'string') return fromTheme;
  return tt('footer.description', 'Layung Network adalah penyedia infrastruktur serat optik berkecepatan tinggi, koneksi dedicated 1:1, dan Managed IT Services dengan jaminan SLA 99.999% bagi korporasi dan institusi.');
});

const footerCopyrightText = computed(() => {
  const fromTheme = getSetting('footer_copyright', '');
  if (fromTheme && typeof fromTheme === 'string') return fromTheme;
  return `© ${new Date().getFullYear()} ${displayCompanyName.value}. ${tt('footer.copyright', 'Hak cipta dilindungi undang-undang.')}`;
});

const col1Title = computed(() => {
  const raw = getSetting('footer_col_1_title', '');
  if (typeof raw === 'string' && raw.trim()) return raw.trim();
  return tt('footer.services', 'Layanan Fiber Optik');
});

const col2Title = computed(() => {
  const raw = getSetting('footer_col_2_title', '');
  if (typeof raw === 'string' && raw.trim()) return raw.trim();
  return tt('footer.managed', 'Managed IT & SOC');
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
  { title: 'Dedicated Internet (DIA 1:1)', url: '/services#dia' },
  { title: 'Dark Fiber & Metro-E', url: '/services#dark-fiber' },
  { title: 'Data Center Colocation', url: '/about#colocation' },
  { title: tt('header.pricing', 'Paket Bandwidth'), url: '/pricing#packages' },
]);

const defaultCol2Items = computed((): Partial<MenuItem>[] => [
  { title: '24/7 Cyber Security SOC', url: '/solusi#soc' },
  { title: 'Managed SD-WAN Cloud', url: '/solusi#sdwan' },
  { title: tt('header.achievement', 'Sertifikasi ISO 27001'), url: '/achievement' },
  { title: tt('header.career', 'Pusat Karir Engineer'), url: '/career-center' },
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

onMounted(() => {
  fetchMenuByIdentifier(normalizeMenuSetting(getSetting('menu_location_footer_col_1', 'footer_col_1'), 'footer_col_1'), 'footer_col_1');
  fetchMenuByIdentifier(normalizeMenuSetting(getSetting('menu_location_footer_col_2', 'footer_col_2'), 'footer_col_2'), 'footer_col_2');
  fetchMenuByIdentifier(normalizeMenuSetting(getSetting('menu_location_footer', 'footer'), 'footer'), 'footer');
});
</script>
