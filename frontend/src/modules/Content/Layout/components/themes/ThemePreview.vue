<template>
  <div
    class="w-full h-full bg-background"
    :class="{'opacity-50': loading, 'pointer-events-none': loading}"
  >
    <iframe
      ref="previewFrame"
      :src="previewUrl"
      class="w-full h-full border-0"
      @load="onPreviewLoad"
    />

    <div
      v-if="loading"
      class="absolute inset-0 flex items-center justify-center bg-background/50 backdrop-blur-sm z-10"
    >
      <div class="flex flex-col items-center gap-2">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary" />
        <!-- <p class="text-xs text-muted-foreground font-medium">Loading Preview...</p> -->
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { logger } from '@/shared/utils/logger';
import { ref, watch, toRaw } from 'vue';
import type { Theme } from '@/modules/Content/Layout/types/theme';
import { themeUsesJanariCanvas } from '@/modules/Content/Layout/utils/themeManifest';

const props = defineProps<{
    theme: Theme;
    previewUrl?: string;
}>();

defineEmits<{
    (e: 'close'): void;
}>();

const previewFrame = ref<HTMLIFrameElement | null>(null);
const loading = ref(true);

// Inject CSS variables and custom CSS into iframe
const injectThemeStyles = () => {
    if (!previewFrame.value || !props.theme) return;

    try {
        const iframeDoc = previewFrame.value.contentDocument || previewFrame.value.contentWindow?.document;
        if (!iframeDoc) return;

        // Remove existing theme styles
        const existingStyle = iframeDoc.getElementById('theme-customizer-styles');
        if (existingStyle) {
            existingStyle.remove();
        }

        // Create new style element
        const style = iframeDoc.createElement('style');
        style.id = 'theme-customizer-styles';
        
        let css = '';

        // Add CSS variables
        if (props.theme.css_variables) {
            css += props.theme.css_variables + '\n\n';
        }

        // Apply settings as CSS variables (Matching useTheme.js logic)
        if (props.theme.settings) {
            const settings = props.theme.settings;
            const variables: string[] = [];

            // Helper for conversion (simplified for injection)
            const hexToHsl = (hex: string) => {
                if (!hex || typeof hex !== 'string' || !hex.startsWith('#')) return null;
                let r = 0, g = 0, b = 0;
                if (hex.length === 4) {
                    r = parseInt('0x' + hex[1] + hex[1]);
                    g = parseInt('0x' + hex[2] + hex[2]);
                    b = parseInt('0x' + hex[3] + hex[3]);
                } else if (hex.length === 7) {
                    r = parseInt('0x' + hex[1] + hex[2]);
                    g = parseInt('0x' + hex[3] + hex[4]);
                    b = parseInt('0x' + hex[5] + hex[6]);
                }
                r /= 255; g /= 255; b /= 255;
                const cmin = Math.min(r, g, b), cmax = Math.max(r, g, b), delta = cmax - cmin;
                let h: number, s: number, l: number;
                if (delta === 0) h = 0;
                else if (cmax === r) h = ((g - b) / delta) % 6;
                else if (cmax === g) h = (b - r) / delta + 2;
                else h = (r - g) / delta + 4;
                h = Math.round(h * 60); if (h < 0) h += 360;
                l = (cmax + cmin) / 2; s = delta === 0 ? 0 : delta / (1 - Math.abs(2 * l - 1));
                return `${h} ${(s * 100).toFixed(1)}% ${(l * 100).toFixed(1)}%`;
            };

            Object.keys(settings).forEach(key => {
                const value = settings[key];
                if (!value) return;

                const cssKey = '--theme-' + key.replace(/_/g, '-');
                
                if (typeof value === 'string' && value.startsWith('#')) {
                    variables.push(`${cssKey}: ${value};`);
                    const hsl = hexToHsl(value);
                    if (hsl) variables.push(`${cssKey}-hsl: ${hsl};`);
                } else {
                    variables.push(`${cssKey}: ${value};`);
                }
            });

            if (variables.length > 0) {
                css += `:root {\n  ${variables.join('\n  ')}\n}\n`;
            }

            // Preview: heading/body font-family from theme settings
            if (settings.font_heading) {
                css += `h1, h2, h3, h4, h5, h6 { font-family: '${settings.font_heading}', sans-serif; }\n`;
            }
            if (settings.font_body) {
                css += `body { font-family: '${settings.font_body}', sans-serif; }\n`;
            }

            // Buttons Override Logic
            css += `
                :root {
                    --btn-radius: ${settings.button_radius || '8px'};
                    --btn-border-width: ${settings.button_border_width || '1'}px;
                }
                button, .btn, .button, [class*="bg-primary"] {
                    border-radius: var(--btn-radius) !important;
                    border-width: var(--btn-border-width) !important;
                }
            `;
        }

        // Add custom CSS
        if (props.theme.custom_css) {
            css += '\n' + props.theme.custom_css;
        }

        // Inject theme class into body
        const slug = props.theme.slug || '';
        if (slug) {
            iframeDoc.body.classList.add(`theme-${slug}`);
            if (themeUsesJanariCanvas(props.theme)) {
                iframeDoc.body.classList.add('theme-janari');
            }
        }

        style.textContent = css;
        iframeDoc.head.appendChild(style);
    } catch (error) {
        logger.warning('Failed to inject theme styles into preview:', error);
    }
};

const onPreviewLoad = () => {
    loading.value = false;
    
    // Inject theme CSS assets if available
    if (previewFrame.value && previewFrame.value.contentDocument) {
        const iframeDoc = previewFrame.value.contentDocument;
        const iframeHead = iframeDoc.head;
        
        // Inject theme CSS if available
        if (props.theme.assets?.css) {
            props.theme.assets.css.forEach(cssFile => {
                const link = iframeDoc.createElement('link');
                link.rel = 'stylesheet';
                link.href = cssFile.startsWith('http') ? cssFile : `/${cssFile}`;
                iframeHead.appendChild(link);
            });
        }
    }
    
    // Inject theme styles (CSS variables, settings, custom CSS)
    injectThemeStyles();
};

const refreshPreview = () => {
    if (previewFrame.value) {
        loading.value = true;
        previewFrame.value.src = props.previewUrl || '/';
    }
};

defineExpose({ refreshPreview });

// Watch for theme changes and re-inject styles
watch(() => props.theme, () => {
    if (previewFrame.value && previewFrame.value.contentWindow) {
        // Send postMessage for reactive updates (JS-based)
        const themeRaw = JSON.parse(JSON.stringify(toRaw(props.theme)));
        previewFrame.value.contentWindow.postMessage({
            type: 'JA_THEME_CUSTOMIZER_SYNC',
            theme: themeRaw,
            settings: themeRaw.settings,
            custom_css: themeRaw.custom_css,
        }, '*');

        previewFrame.value.contentWindow.postMessage({
            type: 'THEME_UPDATE',
            settings: themeRaw.settings,
            custom_css: themeRaw.custom_css,
        }, '*');

        // Still inject styles for color/CSS variables
        if (previewFrame.value.contentDocument) {
            injectThemeStyles();
        }
    }
}, { deep: true });
</script>

<style scoped>
iframe {
    transition: opacity 0.3s ease;
}
</style>

