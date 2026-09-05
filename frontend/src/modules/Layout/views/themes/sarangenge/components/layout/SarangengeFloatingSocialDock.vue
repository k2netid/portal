<template>
  <aside
    v-if="socialLinks.length && isEnabled"
    ref="dockRef"
    data-ja-customizer-target="social_links"
    class="sarangenge-social-dock"
    :class="[
      `sarangenge-social-dock--${dockPosition}`,
      `sarangenge-social-dock--style-${dockStyle}`,
      `sarangenge-social-dock--${effectiveOrientation}`,
      {
        'sarangenge-social-dock--collapsed': isCollapsed,
        'sarangenge-social-dock--show-mobile': showOnMobile,
      }
    ]"
    :aria-label="t('footer.floatingSocialDockAria', 'Tautan Media Sosial Melayang')"
  >
    <!-- Collapsed Trigger Button -->
    <div
      v-if="isCollapsed"
      class="relative group"
    >
      <button
        ref="toggleBtnRef"
        type="button"
        class="sarangenge-social-dock__toggle-btn"
        :aria-label="t('footer.socialExpand', 'Buka Media Sosial & Hotline')"
        @click="handleToggle(false)"
      >
        <Share2 class="w-4 h-4 transition-transform group-hover:scale-110" />
      </button>
      <span class="sarangenge-social-dock__tooltip">
        {{ t('footer.socialMedia', 'Media Sosial & Hotline') }}
      </span>
    </div>

    <!-- Expanded Dock Body -->
    <div
      v-else
      ref="bodyRef"
      class="sarangenge-social-dock__body"
    >
      <!-- Mini Header / Collapse Button -->
      <button
        type="button"
        class="sarangenge-social-dock__collapse-btn"
        :aria-label="t('footer.socialCollapse', 'Sembunyikan')"
        @click="handleToggle(true)"
      >
        <ChevronRight
          class="w-3.5 h-3.5 transition-transform"
          :class="chevronRotationClass"
        />
      </button>

      <div class="sarangenge-social-dock__divider" />

      <!-- Social Items List -->
      <div class="sarangenge-social-dock__list">
        <a
          v-for="(link, idx) in socialLinks"
          :key="idx"
          :href="resolveSocialHref(link)"
          :target="getSocialTarget(link)"
          :rel="getSocialRel(link)"
          class="sarangenge-social-dock__item group"
          :aria-label="getSocialAriaLabel(link)"
        >
          <component
            :is="getSocialIcon(link.icon)"
            class="w-4 h-4 transition-transform group-hover:scale-115 duration-200"
          />

          <!-- Flyout Tooltip Label -->
          <span class="sarangenge-social-dock__tooltip">
            {{ getSocialPlatformName(link) }}
          </span>
        </a>
      </div>
    </div>
  </aside>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, nextTick, watch } from 'vue';
import gsap from 'gsap';
import {
  Share2,
  ChevronRight,
  Globe,
  Instagram,
  Facebook,
  Twitter,
  Youtube,
  Linkedin,
  Github,
  MessageCircle,
  Mail,
  Music2,
} from 'lucide-vue-next';
import { useThemeI18n } from '@/modules/Layout/composables/useThemeI18n';
import { useTheme } from '@/modules/Layout/composables/useTheme';
import { useThemeMotion } from '@/modules/Layout/composables/useThemeMotion';
import { useSarangengeIdentity } from '@/modules/Layout/views/themes/sarangenge/composables/useSarangengeIdentity';

const { t } = useThemeI18n('sarangenge');
const { getSetting } = useTheme();
const { isAnimationEnabled } = useThemeMotion();
const { whatsAppUrl } = useSarangengeIdentity();

const isEnabled = computed(() => getSetting('enable_floating_social', true) !== false);
const dockPosition = computed(() => String(getSetting('floating_social_position', 'right') || 'right'));
const dockOrientation = computed(() => String(getSetting('floating_social_orientation', 'auto') || 'auto'));
const dockStyle = computed(() => String(getSetting('floating_social_style', 'glass') || 'glass'));
const defaultCollapsed = computed(() => Boolean(getSetting('floating_social_default_collapsed', false)));
const showOnMobile = computed(() => Boolean(getSetting('floating_social_show_on_mobile', false)));

const isCollapsed = ref(defaultCollapsed.value);

watch(defaultCollapsed, (val) => {
  isCollapsed.value = val;
});

const effectiveOrientation = computed<'vertical' | 'horizontal'>(() => {
  if (dockOrientation.value === 'horizontal') return 'horizontal';
  if (dockOrientation.value === 'vertical') return 'vertical';
  return dockPosition.value === 'bottom_center' ? 'horizontal' : 'vertical';
});

const chevronRotationClass = computed(() => {
  if (effectiveOrientation.value === 'horizontal') {
    return 'rotate-90';
  }
  if (dockPosition.value === 'left' || dockPosition.value === 'bottom_left' || dockPosition.value === 'top_left') {
    return 'rotate-180';
  }
  return '';
});

const dockRef = ref<HTMLElement | null>(null);
const bodyRef = ref<HTMLElement | null>(null);
const toggleBtnRef = ref<HTMLElement | null>(null);

const handleToggle = async (collapsed: boolean) => {
  isCollapsed.value = collapsed;
  await nextTick();
  if (!isAnimationEnabled()) return;

  if (collapsed && toggleBtnRef.value) {
    gsap.fromTo(
      toggleBtnRef.value,
      { scale: 0.6, opacity: 0, rotation: -45 },
      { scale: 1, opacity: 1, rotation: 0, duration: 0.35, ease: 'back.out(1.8)', clearProps: 'all' }
    );
  } else if (!collapsed && bodyRef.value) {
    let slideX = 0;
    let slideY = 0;
    if (effectiveOrientation.value === 'horizontal') {
      slideY = 15;
    } else {
      slideX = (dockPosition.value === 'left' || dockPosition.value === 'bottom_left' || dockPosition.value === 'top_left') ? -15 : 15;
    }
    gsap.fromTo(
      bodyRef.value,
      { scale: 0.8, opacity: 0, x: slideX, y: slideY },
      { scale: 1, opacity: 1, x: 0, y: 0, duration: 0.35, ease: 'back.out(1.5)', clearProps: 'all' }
    );
    const items = bodyRef.value.querySelectorAll('.sarangenge-social-dock__item');
    if (items.length) {
      gsap.fromTo(
        items,
        { scale: 0.7, opacity: 0 },
        { scale: 1, opacity: 1, stagger: 0.04, duration: 0.25, ease: 'power2.out', clearProps: 'all' }
      );
    }
  }
};

onMounted(() => {
  if (dockRef.value && isAnimationEnabled()) {
    let enterX = 0;
    let enterY = 0;
    if (dockPosition.value === 'bottom_center') {
      enterY = 36;
    } else if (dockPosition.value === 'left' || dockPosition.value === 'bottom_left' || dockPosition.value === 'top_left') {
      enterX = -36;
    } else {
      enterX = 36;
    }
    gsap.fromTo(
      dockRef.value,
      { x: enterX, y: enterY, opacity: 0 },
      { x: 0, y: 0, opacity: 1, duration: 0.55, delay: 0.3, ease: 'back.out(1.4)', clearProps: 'all' }
    );
  }
});

const rawSocialLinks = computed(() => {
  return (getSetting('social_links') as Array<{ icon?: string; url?: string; label?: string }>) || [];
});

const defaultSocialLinks = computed(() => [
  { icon: 'WhatsApp', url: whatsAppUrl.value || '', label: 'Hotline Informasi' },
  { icon: 'Instagram', url: '#', label: 'Instagram' },
  { icon: 'Youtube', url: '#', label: 'YouTube' },
  { icon: 'Mail', url: 'info@school.sch.id', label: 'Email Resmi' },
]);

const socialLinks = computed(() => {
  if (rawSocialLinks.value.length > 0) {
    return rawSocialLinks.value;
  }
  return defaultSocialLinks.value;
});

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

const getSocialPlatformName = (link: { icon?: string; label?: string }) => {
  const customLabel = trimStr(link?.label);
  if (customLabel) return customLabel;

  const clean = trimStr(link?.icon);
  if (!clean) return t('common.link', 'Tautan');
  if (clean === 'MessageCircle' || clean === 'WhatsApp') return 'WhatsApp';
  if (clean === 'Instagram') return 'Instagram';
  if (clean === 'Youtube') return 'YouTube';
  if (clean === 'Mail' || clean === 'Email') return 'Email';
  return clean;
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

const getSocialAriaLabel = (link: { icon?: string; url?: string; label?: string }) => {
  const icon = trimStr(link?.label) || trimStr(link?.icon) || 'social';
  return t('footer.visitSocial', { platform: icon }, `Kunjungi ${icon}`);
};
</script>
