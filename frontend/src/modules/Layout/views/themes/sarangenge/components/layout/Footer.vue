<template>
  <footer
    data-ja-customizer-target="footer"
    class="border-t border-border/80 bg-card/95 backdrop-blur-md text-foreground transition-colors relative z-10"
  >
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-12">
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-8 lg:gap-6">
        <!-- Col 1: School Identity -->
        <div class="lg:col-span-2 space-y-4">
          <div class="flex items-center gap-3.5">
            <div
              v-if="brandingDisplay !== 'text_only'"
              class="w-11 h-11 rounded-2xl bg-[#0f172a] text-amber-400 flex items-center justify-center font-black text-xl shadow-md border border-slate-700/60 overflow-hidden"
              aria-hidden="true"
            >
              <img
                v-if="siteLogo"
                :src="siteLogo"
                :alt="displaySchoolName"
                class="h-full w-full object-contain p-1"
              >
              <template v-else>
                {{ displaySchoolName.charAt(0).toUpperCase() }}
              </template>
            </div>
            <div v-if="brandingDisplay !== 'logo_only'">
              <span class="text-xl font-extrabold tracking-tight text-foreground font-heading block">
                {{ displaySchoolName }}
              </span>
              <span class="text-xs text-primary font-bold">
                {{ displayAccreditation }} · {{ displayNpsn }}
              </span>
            </div>
          </div>

          <p class="text-sm text-muted-foreground leading-relaxed max-w-md">
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
              class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-border/70 text-muted-foreground hover:text-primary hover:border-primary/40 transition-colors"
              :aria-label="getSocialAriaLabel(link)"
            >
              <component
                :is="getSocialIcon(link.icon)"
                class="w-4 h-4"
              />
            </a>
          </div>

          <div class="flex flex-wrap gap-2 pt-1">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-primary/10 text-primary border border-primary/20">
              <GraduationCap class="w-3.5 h-3.5" />
              {{ tt('footer.badgeCurriculum', 'Kurikulum Merdeka & Bilingual') }}
            </span>
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-500/15 text-amber-800 dark:text-amber-300 border border-amber-500/30">
              <Award class="w-3.5 h-3.5" />
              {{ tt('footer.badgeChildFriendly', 'Sekolah Ramah Anak 2026') }}
            </span>
          </div>
        </div>

        <!-- Col 2: from Menu Builder footer_col_1 -->
        <div class="space-y-4">
          <h4 class="text-sm font-bold uppercase tracking-wider text-foreground font-heading">
            {{ col1Title }}
          </h4>
          <ul class="space-y-2.5 text-sm text-muted-foreground">
            <li
              v-for="item in footerCol1Items"
              :key="String(item.id || item.title)"
            >
              <a
                v-if="isExternalLink(item.url)"
                :href="item.url || '#'"
                target="_blank"
                rel="noopener noreferrer"
                class="hover:text-primary transition-colors"
              >
                {{ item.title }}
              </a>
              <router-link
                v-else
                :to="item.url || '/'"
                class="hover:text-primary transition-colors"
              >
                {{ item.title }}
              </router-link>
            </li>
          </ul>
        </div>

        <!-- Col 3: from Menu Builder footer_col_2 -->
        <div class="space-y-4">
          <h4 class="text-sm font-bold uppercase tracking-wider text-foreground font-heading">
            {{ col2Title }}
          </h4>
          <ul class="space-y-2.5 text-sm text-muted-foreground">
            <li
              v-for="item in footerCol2Items"
              :key="String(item.id || item.title)"
            >
              <a
                v-if="isExternalLink(item.url)"
                :href="item.url || '#'"
                target="_blank"
                rel="noopener noreferrer"
                class="hover:text-primary transition-colors"
              >
                {{ item.title }}
              </a>
              <router-link
                v-else
                :to="item.url || '/'"
                class="hover:text-primary transition-colors"
              >
                {{ item.title }}
              </router-link>
            </li>
          </ul>
        </div>

        <!-- Col 4: Contact & Hotline -->
        <div class="space-y-4">
          <h4 class="text-sm font-bold uppercase tracking-wider text-foreground font-heading">
            {{ tt('footer.contact', 'Kontak & Hotline') }}
          </h4>
          <div class="space-y-2 text-xs text-muted-foreground leading-relaxed">
            <p class="flex items-start gap-2">
              <MapPin class="w-4 h-4 text-primary shrink-0 mt-0.5" />
              <span>{{ displayAddress }}</span>
            </p>
            <p class="flex items-center gap-2">
              <Phone class="w-4 h-4 text-primary shrink-0" />
              <a
                :href="phoneDialHref"
                class="hover:text-foreground font-semibold"
              >{{ displayPhone }}</a>
            </p>
            <p class="flex items-center gap-2">
              <Mail class="w-4 h-4 text-primary shrink-0" />
              <a
                :href="`mailto:${displayEmail}`"
                class="hover:text-foreground"
              >{{ displayEmail }}</a>
            </p>
            <p
              v-if="whatsAppUrl"
              class="pt-1.5"
            >
              <a
                :href="whatsAppUrl"
                target="_blank"
                rel="noopener noreferrer"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-[var(--sarangenge-radius-sm)] bg-emerald-600 text-white font-bold text-xs hover:bg-emerald-700 transition-colors shadow-sm"
              >
                <MessageCircle class="w-3.5 h-3.5" />
                WhatsApp Hotline PPDB
              </a>
            </p>
          </div>
        </div>
      </div>

      <!-- Bottom Bar -->
      <div class="mt-12 pt-6 border-t border-border/60 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-muted-foreground">
        <p>
          {{ footerCopyrightText }}
        </p>
        <div class="flex items-center gap-4 flex-wrap justify-center">
          <template v-if="footerItems.length">
            <template
              v-for="(item, idx) in footerItems"
              :key="String(item.id || item.title)"
            >
              <span
                v-if="idx > 0"
                class="text-border"
              >·</span>
              <a
                v-if="isExternalLink(item.url)"
                :href="item.url || '#'"
                target="_blank"
                rel="noopener noreferrer"
                class="hover:text-foreground transition-colors"
              >
                {{ item.title }}
              </a>
              <router-link
                v-else
                :to="item.url || '/'"
                class="hover:text-foreground transition-colors"
              >
                {{ item.title }}
              </router-link>
            </template>
          </template>
          <template v-else>
            <router-link
              to="/about"
              class="hover:text-foreground transition-colors"
            >
              {{ tt('header.about', 'Profil') }}
            </router-link>
            <span>·</span>
            <router-link
              to="/contact"
              class="hover:text-foreground transition-colors"
            >
              {{ tt('header.contact', 'Kontak') }}
            </router-link>
            <span>·</span>
            <router-link
              to="/search"
              class="hover:text-foreground transition-colors"
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
  MapPin, Phone, Mail, GraduationCap, Award, MessageCircle,
  Twitter, Instagram, Facebook, Youtube, Linkedin, Github, Music2, Globe,
} from 'lucide-vue-next';
import { useTheme } from '@/modules/Layout/composables/useTheme';
import { useMenu } from '@/modules/Layout/composables/useMenu';
import { useThemeI18n } from '@/modules/Layout/composables/useThemeI18n';
import { useSystemStore } from '@/modules/Core/System/stores/system';
import { useSarangengeIdentity } from '@/modules/Layout/views/themes/sarangenge/composables/useSarangengeIdentity';
import type { MenuItem } from '@/modules/Layout/types/menu';

const { t: tt } = useThemeI18n('sarangenge');
const { getSetting } = useTheme();
const { menus, fetchMenuByIdentifier } = useMenu();
const systemStore = useSystemStore();
const {
  displaySchoolName,
  displayAddress,
  displayPhone,
  displayEmail,
  displayAccreditation,
  displayNpsn,
  phoneDialHref,
  whatsAppUrl,
} = useSarangengeIdentity();

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
  return tt(
    'footer.description',
    { school: displaySchoolName.value },
    `${displaySchoolName.value} berkomitmen menyelenggarakan pendidikan berkualitas, berintegritas, berstandar tinggi, dan berlandaskan kearifan lokal.`
  );
});

const footerCopyrightText = computed(() => {
  const fromTheme = getSetting('footer_copyright', '');
  if (fromTheme && typeof fromTheme === 'string') return fromTheme;
  return `© ${new Date().getFullYear()} ${displaySchoolName.value}. ${tt('footer.copyright', 'Hak cipta dilindungi undang-undang.')}`;
});

const col1Title = computed(() => {
  const raw = getSetting('footer_col_1_title', '');
  if (typeof raw === 'string' && raw.trim()) return raw.trim();
  return tt('footer.programs', 'Program & Kurikulum');
});

const col2Title = computed(() => {
  const raw = getSetting('footer_col_2_title', '');
  if (typeof raw === 'string' && raw.trim()) return raw.trim();
  return tt('footer.admissions', 'Informasi PPDB');
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
  { title: tt('header.solusi', 'Program Unggulan'), url: '/solusi#programs' },
  { title: tt('header.services', 'Fasilitas Kampus'), url: '/services#smart-class' },
  { title: tt('header.achievement', 'Prestasi Siswa'), url: '/achievement' },
  { title: tt('header.tim', 'Direktori Guru'), url: '/tim' },
]);

const defaultCol2Items = computed((): Partial<MenuItem>[] => [
  { title: tt('header.pricing', 'Biaya & Beasiswa'), url: '/pricing#beasiswa' },
  { title: tt('header.career', 'Jejaring Alumni'), url: '/career-center' },
  { title: tt('header.blog', 'Berita & Pengumuman'), url: '/blog' },
  { title: tt('header.getStarted', 'Pendaftaran PPDB'), url: '/contact#ppdb' },
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
