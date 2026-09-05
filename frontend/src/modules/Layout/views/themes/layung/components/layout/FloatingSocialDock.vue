<template>
  <aside
    v-if="socialLinks.length && isEnabled"
    ref="dockRef"
    data-ja-customizer-target="social_links"
    class="layung-social-dock"
    :class="[
      `layung-social-dock--${dockPosition}`,
      {
        'layung-social-dock--collapsed': isCollapsed,
        'layung-social-dock--show-mobile': showOnMobile,
      }
    ]"
    :aria-label="tt('footer.floatingSocialDockAria', 'Tautan Media Sosial Melayang')"
  >
    <!-- Collapsed Trigger Button -->
    <div
      v-if="isCollapsed"
      class="relative group"
    >
      <button
        ref="toggleBtnRef"
        type="button"
        class="layung-social-dock__toggle-btn"
        :aria-label="tt('footer.socialExpand', 'Buka Media Sosial')"
        @click="handleToggle(false)"
      >
        <Share2 class="w-4 h-4 transition-transform group-hover:scale-110" />
      </button>
      <span class="layung-social-dock__tooltip">
        {{ tt('footer.socialMedia', 'Media Sosial') }}
      </span>
    </div>

    <!-- Expanded Dock Body -->
    <div
      v-else
      ref="bodyRef"
      class="layung-social-dock__body"
    >
      <!-- Mini Header / Collapse Button -->
      <button
        type="button"
        class="layung-social-dock__collapse-btn"
        :aria-label="tt('footer.socialCollapse', 'Sembunyikan')"
        @click="handleToggle(true)"
      >
        <ChevronRight
          class="w-3.5 h-3.5 transition-transform"
          :class="chevronRotationClass"
        />
      </button>

      <div class="layung-social-dock__divider" />

      <!-- Social Items List -->
      <div class="layung-social-dock__list">
        <a
          v-for="(link, idx) in socialLinks"
          :key="idx"
          :href="resolveSocialHref(link)"
          :target="getSocialTarget(link)"
          :rel="getSocialRel(link)"
          class="layung-social-dock__item group"
          :aria-label="getSocialAriaLabel(link)"
        >
          <component
            :is="getSocialIcon(link.icon)"
            class="w-4 h-4 transition-transform group-hover:scale-115 duration-200"
          />

          <!-- Flyout Tooltip Label -->
          <span class="layung-social-dock__tooltip">
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

const { t: tt } = useThemeI18n('layung');
const { getSetting } = useTheme();
const { isAnimationEnabled } = useThemeMotion();

const isEnabled = computed(() => getSetting('enable_floating_social', true) !== false);
const dockPosition = computed(() => String(getSetting('floating_social_position', 'right') || 'right'));
const defaultCollapsed = computed(() => Boolean(getSetting('floating_social_default_collapsed', false)));
const showOnMobile = computed(() => Boolean(getSetting('floating_social_show_on_mobile', false)));

const isCollapsed = ref(defaultCollapsed.value);

watch(defaultCollapsed, (val) => {
  isCollapsed.value = val;
});

const chevronRotationClass = computed(() => {
  if (dockPosition.value === 'left' || dockPosition.value === 'bottom_left') {
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
    const slideX = (dockPosition.value === 'left' || dockPosition.value === 'bottom_left') ? -15 : 15;
    gsap.fromTo(
      bodyRef.value,
      { scale: 0.8, opacity: 0, x: slideX },
      { scale: 1, opacity: 1, x: 0, duration: 0.35, ease: 'back.out(1.5)', clearProps: 'all' }
    );
    const items = bodyRef.value.querySelectorAll('.layung-social-dock__item');
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
    const enterX = (dockPosition.value === 'left' || dockPosition.value === 'bottom_left') ? -36 : 36;
    gsap.fromTo(
      dockRef.value,
      { x: enterX, opacity: 0 },
      { x: 0, opacity: 1, duration: 0.55, delay: 0.3, ease: 'back.out(1.4)', clearProps: 'all' }
    );
  }
});

const rawSocialLinks = computed(() => {
  return (getSetting('social_links') as Array<{ icon?: string; url?: string; label?: string }>) || [];
});

const defaultSocialLinks = [
  { icon: 'WhatsApp', url: '6285136290851' },
  { icon: 'Linkedin', url: 'https://linkedin.com/company/isp-provider' },
  { icon: 'Instagram', url: 'https://instagram.com/portal.net' },
  { icon: 'Mail', url: 'info@portal.net' },
];

const socialLinks = computed(() => {
  if (rawSocialLinks.value.length > 0) {
    return rawSocialLinks.value;
  }
  return defaultSocialLinks;
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
  if (!clean) return tt('common.link', 'Tautan');
  if (clean === 'MessageCircle' || clean === 'WhatsApp') return 'WhatsApp CS';
  if (clean === 'Linkedin') return 'LinkedIn Kami';
  if (clean === 'Mail' || clean === 'Email') return 'Kirim Email';
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
  return tt('footer.visitSocial', { platform: icon }, `Kunjungi ${icon}`);
};
</script>

