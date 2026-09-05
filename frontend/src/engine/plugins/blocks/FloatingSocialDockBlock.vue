<template>
  <aside
    v-if="socialLinks.length && isEnabled"
    ref="dockRef"
    data-plugin="floating-social-dock"
    data-ja-customizer-target="social_links"
    class="ja-plugin-block ja-floating-social-dock plugin-slot-isolate"
    :class="[
      `ja-floating-social-dock--${dockPosition}`,
      `ja-floating-social-dock--style-${dockStyle}`,
      `ja-floating-social-dock--${effectiveOrientation}`,
      {
        'ja-floating-social-dock--collapsed': isCollapsed,
        'ja-floating-social-dock--show-mobile': showOnMobile,
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
        class="ja-floating-social-dock__toggle-btn"
        :aria-label="t('footer.socialExpand', 'Buka Media Sosial & Hotline')"
        @click="handleToggle(false)"
      >
        <Share2 class="w-4 h-4 transition-transform group-hover:scale-110" />
      </button>
      <span class="ja-floating-social-dock__tooltip">
        {{ t('footer.socialMedia', 'Media Sosial & Hotline') }}
      </span>
    </div>

    <!-- Expanded Dock Body -->
    <div
      v-else
      ref="bodyRef"
      class="ja-floating-social-dock__body"
    >
      <!-- Mini Header / Collapse Button -->
      <button
        type="button"
        class="ja-floating-social-dock__collapse-btn"
        :aria-label="t('footer.socialCollapse', 'Sembunyikan')"
        @click="handleToggle(true)"
      >
        <ChevronRight
          class="w-3.5 h-3.5 transition-transform"
          :class="chevronRotationClass"
        />
      </button>

      <div class="ja-floating-social-dock__divider" />

      <!-- Social Items List -->
      <div class="ja-floating-social-dock__list">
        <a
          v-for="(link, idx) in socialLinks"
          :key="idx"
          :href="resolveSocialHref(link)"
          :target="getSocialTarget(link)"
          :rel="getSocialRel(link)"
          class="ja-floating-social-dock__item group"
          :aria-label="getSocialAriaLabel(link)"
        >
          <component
            :is="getSocialIcon(link.icon)"
            class="w-4 h-4 transition-transform group-hover:scale-115 duration-200"
          />

          <!-- Flyout Tooltip Label -->
          <span class="ja-floating-social-dock__tooltip">
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
import { useI18n } from 'vue-i18n';
import { useTheme } from '@/modules/Layout/composables/useTheme';
import { useThemeMotion } from '@/modules/Layout/composables/useThemeMotion';

const { t } = useI18n();
const { getSetting } = useTheme();
const { isAnimationEnabled } = useThemeMotion();

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
    const items = bodyRef.value.querySelectorAll('.ja-floating-social-dock__item');
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
    gsap.fromTo(
      dockRef.value,
      { opacity: 0, scale: 0.8 },
      { opacity: 1, scale: 1, duration: 0.5, delay: 0.6, ease: 'back.out(1.4)' }
    );
  }
});

interface SocialLinkItem {
  icon?: string;
  url?: string;
  label?: string;
}

const parseSocialLinks = (raw: unknown): SocialLinkItem[] => {
  if (!raw) return [];
  if (Array.isArray(raw)) return raw;
  if (typeof raw === 'string') {
    try {
      const parsed = JSON.parse(raw);
      return Array.isArray(parsed) ? parsed : [];
    } catch {
      return [];
    }
  }
  return [];
};

const socialLinks = computed<SocialLinkItem[]>(() => {
  const fromTheme = parseSocialLinks(getSetting('social_links', []));
  if (fromTheme.length > 0) return fromTheme;

  // Fallback defaults
  return [
    { icon: 'Instagram', url: 'https://instagram.com' },
    { icon: 'Youtube', url: 'https://youtube.com' },
    { icon: 'MessageCircle', url: 'https://wa.me/628123456789' },
  ];
});

const trimStr = (v: unknown): string => (typeof v === 'string' ? v.trim() : '');

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

<style scoped>
.ja-floating-social-dock {
  position: fixed;
  z-index: 9995;
  display: flex;
  pointer-events: auto;
  transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.3s ease;
}

/* ─── Position Variants ─── */
.ja-floating-social-dock--right {
  right: 1.25rem;
  top: 50%;
  transform: translateY(-50%);
}

.ja-floating-social-dock--left {
  left: 1.25rem;
  top: 50%;
  transform: translateY(-50%);
}

.ja-floating-social-dock--bottom_right {
  right: 1.5rem;
  bottom: 5.5rem;
}

.ja-floating-social-dock--bottom_left {
  left: 1.5rem;
  bottom: 5.5rem;
}

.ja-floating-social-dock--bottom_center {
  left: 50%;
  bottom: 1.5rem;
  transform: translateX(-50%);
}

.ja-floating-social-dock--top_right {
  right: 1.5rem;
  top: 5.5rem;
}

.ja-floating-social-dock--top_left {
  left: 1.5rem;
  top: 5.5rem;
}

/* ─── Orientation: Vertical ─── */
.ja-floating-social-dock--vertical {
  flex-direction: column;
  align-items: center;
}

.ja-floating-social-dock--vertical .ja-floating-social-dock__body {
  flex-direction: column;
  align-items: center;
}

.ja-floating-social-dock--vertical .ja-floating-social-dock__list {
  flex-direction: column;
}

.ja-floating-social-dock--vertical .ja-floating-social-dock__divider {
  width: 1.25rem;
  height: 1px;
  margin: 0.35rem 0;
}

/* ─── Orientation: Horizontal ─── */
.ja-floating-social-dock--horizontal {
  flex-direction: row;
  align-items: center;
}

.ja-floating-social-dock--horizontal .ja-floating-social-dock__body {
  flex-direction: row;
  align-items: center;
}

.ja-floating-social-dock--horizontal .ja-floating-social-dock__list {
  flex-direction: row;
}

.ja-floating-social-dock--horizontal .ja-floating-social-dock__divider {
  height: 1.25rem;
  width: 1px;
  margin: 0 0.35rem;
}

/* ─── Mobile Visibility & Spacing ─── */
@media (max-width: 768px) {
  .ja-floating-social-dock {
    display: none;
  }
  .ja-floating-social-dock--show-mobile {
    display: flex;
  }
  .ja-floating-social-dock--show-mobile.ja-floating-social-dock--bottom_center {
    bottom: 1rem;
    max-width: calc(100vw - 2rem);
  }
  .ja-floating-social-dock--show-mobile.ja-floating-social-dock--bottom_right {
    right: 0.75rem;
    bottom: 4.5rem;
  }
  .ja-floating-social-dock--show-mobile.ja-floating-social-dock--bottom_left {
    left: 0.75rem;
    bottom: 4.5rem;
  }
  .ja-floating-social-dock--show-mobile.ja-floating-social-dock--right {
    right: 0.5rem;
  }
  .ja-floating-social-dock--show-mobile.ja-floating-social-dock--left {
    left: 0.5rem;
  }
}

/* ─── Body Container & Styles ─── */
.ja-floating-social-dock__body {
  display: flex;
  padding: 0.35rem;
  border-radius: 9999px;
  box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.25), 0 8px 10px -6px rgba(0, 0, 0, 0.2);
}

.ja-floating-social-dock--style-glass .ja-floating-social-dock__body {
  background: rgba(15, 23, 42, 0.78);
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
  border: 1px solid rgba(255, 255, 255, 0.15);
  box-shadow: 0 12px 30px -4px rgba(0, 0, 0, 0.35), inset 0 1px 0 rgba(255, 255, 255, 0.2);
}

.ja-floating-social-dock--style-solid .ja-floating-social-dock__body {
  background: #0f172a;
  border: 1px solid #334155;
  box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.4);
}

.ja-floating-social-dock--style-glow .ja-floating-social-dock__body {
  background: rgba(15, 23, 42, 0.9);
  backdrop-filter: blur(14px);
  border: 1px solid rgba(245, 158, 11, 0.4);
  box-shadow: 0 0 25px rgba(245, 158, 11, 0.3), 0 12px 30px rgba(0, 0, 0, 0.4);
}

/* ─── Buttons & Items ─── */
.ja-floating-social-dock__toggle-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 2.5rem;
  height: 2.5rem;
  border-radius: 9999px;
  background: rgba(15, 23, 42, 0.85);
  backdrop-filter: blur(14px);
  border: 1px solid rgba(255, 255, 255, 0.2);
  color: #f8fafc;
  cursor: pointer;
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
  transition: all 0.25s ease;
}

.ja-floating-social-dock__toggle-btn:hover {
  transform: scale(1.08);
  background: #f59e0b;
  color: #0f172a;
}

.ja-floating-social-dock__collapse-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 2rem;
  height: 2rem;
  border-radius: 9999px;
  background: transparent;
  border: none;
  color: #94a3b8;
  cursor: pointer;
  transition: all 0.2s ease;
}

.ja-floating-social-dock__collapse-btn:hover {
  background: rgba(255, 255, 255, 0.1);
  color: #f8fafc;
}

.ja-floating-social-dock__divider {
  background: rgba(255, 255, 255, 0.15);
}

.ja-floating-social-dock__list {
  display: flex;
  gap: 0.25rem;
}

.ja-floating-social-dock__item {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 2.25rem;
  height: 2.25rem;
  border-radius: 9999px;
  color: #cbd5e1;
  text-decoration: none;
  transition: all 0.2s ease;
}

.ja-floating-social-dock__item:hover {
  background: rgba(245, 158, 11, 0.2);
  color: #fbbf24;
  transform: translateY(-1px);
}

/* ─── Tooltip ─── */
.ja-floating-social-dock__tooltip {
  position: absolute;
  pointer-events: none;
  opacity: 0;
  white-space: nowrap;
  padding: 0.25rem 0.5rem;
  font-size: 0.75rem;
  font-weight: 500;
  border-radius: 0.375rem;
  background: #0f172a;
  color: #f8fafc;
  border: 1px solid rgba(255, 255, 255, 0.1);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
  transition: opacity 0.2s ease, transform 0.2s ease;
  z-index: 60;
}

.ja-floating-social-dock--right .ja-floating-social-dock__tooltip,
.ja-floating-social-dock--bottom_right .ja-floating-social-dock__tooltip,
.ja-floating-social-dock--top_right .ja-floating-social-dock__tooltip {
  right: calc(100% + 0.5rem);
  top: 50%;
  transform: translateY(-50%) translateX(4px);
}

.ja-floating-social-dock--left .ja-floating-social-dock__tooltip,
.ja-floating-social-dock--bottom_left .ja-floating-social-dock__tooltip,
.ja-floating-social-dock--top_left .ja-floating-social-dock__tooltip {
  left: calc(100% + 0.5rem);
  top: 50%;
  transform: translateY(-50%) translateX(-4px);
}

.ja-floating-social-dock--bottom_center .ja-floating-social-dock__tooltip {
  bottom: calc(100% + 0.5rem);
  left: 50%;
  transform: translateX(-50%) translateY(4px);
}

.group:hover .ja-floating-social-dock__tooltip {
  opacity: 1;
  transform: translateY(-50%) translateX(0);
}

.ja-floating-social-dock--bottom_center .group:hover .ja-floating-social-dock__tooltip {
  transform: translateX(-50%) translateY(0);
}
</style>
