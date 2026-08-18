import { computed, inject, provide, reactive, ref, watch, type ComputedRef, type InjectionKey, type Ref, type WritableComputedRef } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '@/engine/api/client';
import { useToast } from '@/shared/composables/useToast';
import { useConsoleTheme } from '@/modules/Core/System/composables/useConsoleTheme';
import { useSystemStore } from '@/modules/Core/System/stores/system';
import { logger } from '@/shared/utils/logger';
import { calculateContrastRatio } from '@/shared/utils/color';
import {
    CONSOLE_COLOR_PRESET_CUSTOM,
    CONSOLE_SURFACE_GLASS,
    normalizeConsoleColorPreset,
    normalizeConsoleSurfaceStyle,
    type ConsoleColorPresetId,
    type ConsoleSurfaceStyle,
} from '@/modules/Core/System/constants/consoleThemePresets';
import {
    CONSOLE_GLASS_GRADIENT_DEFAULT,
    normalizeConsoleGlassGradientPreset,
    clampGlassAngle,
    clampGlassIntensity,
    type ConsoleGlassGradientPresetId,
} from '@/modules/Core/System/constants/consoleGlassGradient';
import {
    CONSOLE_THEME_MODE_DEFAULT,
    CONSOLE_THEME_MODE_GLOBAL,
    normalizeConsoleThemeMode,
    type ConsoleThemeMode,
} from '@/modules/Core/System/constants/consoleThemeMode';

export type ConsoleAppearanceTabId = 'colors' | 'shell' | 'logos';

export type { ConsoleThemeMode };

export interface ConsoleAppearanceContext {
    loading: Ref<boolean>;
    saving: Ref<boolean>;
    activeTab: Ref<ConsoleAppearanceTabId>;
    form: Record<string, string | number>;
    hasWhiteLabel: ComputedRef<boolean>;
    colorPreset: WritableComputedRef<ConsoleColorPresetId>;
    surfaceStyle: WritableComputedRef<ConsoleSurfaceStyle>;
    brandColor: WritableComputedRef<string>;
    brandColorDark: WritableComputedRef<string>;
    glassGradientPreset: WritableComputedRef<ConsoleGlassGradientPresetId>;
    glassGradientColor: WritableComputedRef<string>;
    primaryHsl: ComputedRef<string>;
    lightModeContrast: ComputedRef<{ ratio: number; passAA: boolean; passAAA: boolean }>;
    darkModeContrast: ComputedRef<{ ratio: number; passAA: boolean; passAAA: boolean }>;
    exportedThemeJson: ComputedRef<string>;
    importTarget: Ref<string>;
    showLogoLightPicker: Ref<boolean>;
    showLogoDarkPicker: Ref<boolean>;
    showLogoCompactPicker: Ref<boolean>;
    showFaviconPicker: Ref<boolean>;
    clampGlassIntensity: typeof clampGlassIntensity;
    clampGlassAngle: typeof clampGlassAngle;
    syncDraft: () => void;
    load: () => Promise<void>;
    save: () => Promise<void>;
    copyThemeConfig: () => void;
    importThemeConfig: () => void;
    CONSOLE_SURFACE_GLASS: typeof CONSOLE_SURFACE_GLASS;
    CONSOLE_SURFACE_FLAT: ConsoleSurfaceStyle;
    themeMode: WritableComputedRef<ConsoleThemeMode>;
    isGlobalMode: ComputedRef<boolean>;
    isAdvancedMode: ComputedRef<boolean>;
}

const CONSOLE_APPEARANCE_KEY: InjectionKey<ConsoleAppearanceContext> = Symbol('consoleAppearance');

export function useConsoleAppearancePage(): ConsoleAppearanceContext {
    const { t } = useI18n();
    const toast = useToast();
    const systemStore = useSystemStore();
    const { load: reloadConsoleTheme, applyDraft, cssVars } = useConsoleTheme();

    const loading = ref(true);
    const saving = ref(false);
    const activeTab = ref<ConsoleAppearanceTabId>('colors');
    const importTarget = ref('');

    const showLogoLightPicker = ref(false);
    const showLogoDarkPicker = ref(false);
    const showLogoCompactPicker = ref(false);
    const showFaviconPicker = ref(false);

    const form = reactive<Record<string, string | number>>({
        console_theme_mode: CONSOLE_THEME_MODE_DEFAULT,
        console_color_preset: CONSOLE_COLOR_PRESET_CUSTOM,
        console_brand_primary: '#4f46e5',
        console_brand_primary_dark: '#818cf8',
        console_button_radius: 8,
        console_surface_style: CONSOLE_SURFACE_GLASS,
        console_sidebar_style: 'glass',
        console_sidebar_accent: '#0f172a',
        console_navbar_style: 'glass',
        console_popper_opacity: 85,
        console_glass_gradient_preset: CONSOLE_GLASS_GRADIENT_DEFAULT,
        console_glass_gradient_color: '#4f46e5',
        console_glass_gradient_intensity: 55,
        console_glass_gradient_angle: 135,
        app_logo_light: '',
        app_logo_dark: '',
        app_logo_compact: '',
        app_favicon: '',
        console_font_primary: 'system',
        console_font_mono: 'system',
        console_shadow_elevation: 'soft',
        console_border_style: 'subtle',
        console_button_style: 'solid',
        console_card_style: 'soft',
        console_modal_backdrop_opacity: 50,
        console_dropdown_style: 'standard',
        console_icon_weight: 'regular',
    });

    const themeMode = computed({
        get: () => normalizeConsoleThemeMode(form.console_theme_mode),
        set: (mode: ConsoleThemeMode) => { form.console_theme_mode = mode; },
    });

    const isGlobalMode = computed(() => themeMode.value === CONSOLE_THEME_MODE_GLOBAL);
    const isAdvancedMode = computed(() => !isGlobalMode.value);

    watch(themeMode, (mode) => {
        if (mode === CONSOLE_THEME_MODE_GLOBAL && activeTab.value === 'shell') {
            activeTab.value = 'colors';
        } else if (mode !== CONSOLE_THEME_MODE_GLOBAL && activeTab.value === 'colors') {
            activeTab.value = 'shell';
        }
    });

    const hasWhiteLabel = computed(() => systemStore.appIdentity?.has_white_label ?? false);

    const getContrastAnalysis = (colorHex: string, bgHex: string) => {
        try {
            const ratio = calculateContrastRatio(colorHex, bgHex);
            return { ratio, passAA: ratio >= 4.5, passAAA: ratio >= 7.0 };
        } catch {
            return { ratio: 1, passAA: false, passAAA: false };
        }
    };

    const brandColor = computed({
        get: () => String(form.console_brand_primary || '#4f46e5'),
        set: (value: string) => { form.console_brand_primary = value; },
    });

    const brandColorDark = computed({
        get: () => String(form.console_brand_primary_dark || '#818cf8'),
        set: (value: string) => { form.console_brand_primary_dark = value; },
    });

    const lightModeContrast = computed(() => getContrastAnalysis(brandColor.value, '#ffffff'));
    const darkModeContrast = computed(() => getContrastAnalysis(brandColorDark.value, '#000000'));

    const colorPreset = computed({
        get: () => normalizeConsoleColorPreset(form.console_color_preset),
        set: (id: ConsoleColorPresetId) => { form.console_color_preset = id; },
    });

    const surfaceStyle = computed({
        get: () => normalizeConsoleSurfaceStyle(form.console_surface_style),
        set: (style: ConsoleSurfaceStyle) => { form.console_surface_style = style; },
    });

    const glassGradientPreset = computed({
        get: () => normalizeConsoleGlassGradientPreset(form.console_glass_gradient_preset),
        set: (id: ConsoleGlassGradientPresetId) => { form.console_glass_gradient_preset = id; },
    });

    const glassGradientColor = computed({
        get: () => String(form.console_glass_gradient_color || brandColor.value),
        set: (v: string) => { form.console_glass_gradient_color = v; },
    });

    const primaryHsl = computed(() => cssVars.value['--console-primary-hsl'] ?? '238.9 77.1% 60.6%');

    const exportedThemeJson = computed(() => JSON.stringify({
        mode: form.console_theme_mode,
        preset: form.console_color_preset,
        primary: form.console_brand_primary,
        primary_dark: form.console_brand_primary_dark,
        radius: form.console_button_radius,
        surface: form.console_surface_style,
        sidebar: form.console_sidebar_style,
        sidebar_accent: form.console_sidebar_accent,
        navbar: form.console_navbar_style,
        popper_opacity: form.console_popper_opacity,
        glass_preset: form.console_glass_gradient_preset,
        glass_color: form.console_glass_gradient_color,
        intensity: form.console_glass_gradient_intensity,
        angle: form.console_glass_gradient_angle,
        font_primary: form.console_font_primary,
        font_mono: form.console_font_mono,
        shadow_elevation: form.console_shadow_elevation,
        border_style: form.console_border_style,
        icon_weight: form.console_icon_weight,
        dropdown_style: form.console_dropdown_style,
        modal_backdrop: form.console_modal_backdrop_opacity,
        card_style: form.console_card_style,
        button_style: form.console_button_style,
    }));

    function syncDraft() {
        applyDraft({
            console_theme_mode: String(form.console_theme_mode),
            console_color_preset: String(form.console_color_preset),
            console_brand_primary: String(form.console_brand_primary),
            console_brand_primary_dark: String(form.console_brand_primary_dark),
            console_button_radius: form.console_button_radius,
            console_surface_style: String(form.console_surface_style),
            console_sidebar_style: String(form.console_sidebar_style),
            console_sidebar_accent: String(form.console_sidebar_accent),
            console_navbar_style: String(form.console_navbar_style),
            console_popper_opacity: form.console_popper_opacity,
            console_glass_gradient_preset: String(form.console_glass_gradient_preset),
            console_glass_gradient_color: String(form.console_glass_gradient_color),
            console_glass_gradient_intensity: form.console_glass_gradient_intensity,
            console_glass_gradient_angle: form.console_glass_gradient_angle,
            app_logo_light: String(form.app_logo_light),
            app_logo_dark: String(form.app_logo_dark),
            app_logo_compact: String(form.app_logo_compact),
            app_favicon: String(form.app_favicon),
            console_font_primary: String(form.console_font_primary),
            console_font_mono: String(form.console_font_mono),
            console_shadow_elevation: String(form.console_shadow_elevation),
            console_border_style: String(form.console_border_style),
            console_icon_weight: String(form.console_icon_weight),
            console_dropdown_style: String(form.console_dropdown_style),
            console_modal_backdrop_opacity: form.console_modal_backdrop_opacity,
            console_card_style: String(form.console_card_style),
            console_button_style: String(form.console_button_style),
        });
    }

    function copyThemeConfig() {
        navigator.clipboard.writeText(exportedThemeJson.value);
        toast.success.default(t('system.settings.consoleAppearance.themeCopied'));
    }

    function importThemeConfig() {
        try {
            const parsed = JSON.parse(importTarget.value) as Record<string, unknown>;
            if (parsed.mode) form.console_theme_mode = String(parsed.mode);
            if (parsed.preset) form.console_color_preset = String(parsed.preset);
            if (parsed.primary) form.console_brand_primary = String(parsed.primary);
            if (parsed.primary_dark) form.console_brand_primary_dark = String(parsed.primary_dark);
            if (parsed.radius) form.console_button_radius = Number(parsed.radius);
            if (parsed.surface) form.console_surface_style = String(parsed.surface);
            if (parsed.sidebar) form.console_sidebar_style = String(parsed.sidebar);
            if (parsed.sidebar_accent) form.console_sidebar_accent = String(parsed.sidebar_accent);
            if (parsed.navbar) form.console_navbar_style = String(parsed.navbar);
            if (parsed.popper_opacity) form.console_popper_opacity = Number(parsed.popper_opacity);
            if (parsed.glass_preset) form.console_glass_gradient_preset = String(parsed.glass_preset);
            if (parsed.glass_color) form.console_glass_gradient_color = String(parsed.glass_color);
            if (parsed.intensity) form.console_glass_gradient_intensity = Number(parsed.intensity);
            if (parsed.angle) form.console_glass_gradient_angle = Number(parsed.angle);
            if (parsed.font_primary) form.console_font_primary = String(parsed.font_primary);
            if (parsed.font_mono) form.console_font_mono = String(parsed.font_mono);
            if (parsed.shadow_elevation) form.console_shadow_elevation = String(parsed.shadow_elevation);
            if (parsed.border_style) form.console_border_style = String(parsed.border_style);
            if (parsed.icon_weight) form.console_icon_weight = String(parsed.icon_weight);
            if (parsed.dropdown_style) form.console_dropdown_style = String(parsed.dropdown_style);
            if (parsed.modal_backdrop) form.console_modal_backdrop_opacity = Number(parsed.modal_backdrop);
            if (parsed.card_style) form.console_card_style = String(parsed.card_style);
            if (parsed.button_style) form.console_button_style = String(parsed.button_style);
            syncDraft();
            importTarget.value = '';
            toast.success.default(t('system.settings.consoleAppearance.themeImported'));
        } catch {
            toast.error.default(t('system.settings.consoleAppearance.themeImportInvalid'));
        }
    }

    const FORM_KEYS = [
        'console_theme_mode', 'console_color_preset', 'console_brand_primary', 'console_brand_primary_dark',
        'console_button_radius', 'console_surface_style', 'console_sidebar_style', 'console_sidebar_accent',
        'console_navbar_style', 'console_popper_opacity', 'console_glass_gradient_preset',
        'console_glass_gradient_color', 'console_glass_gradient_intensity', 'console_glass_gradient_angle',
        'app_logo_light', 'app_logo_dark', 'app_logo_compact', 'app_favicon',
        'console_font_primary', 'console_font_mono', 'console_shadow_elevation', 'console_button_style', 'console_card_style', 'console_modal_backdrop_opacity', 'console_dropdown_style', 'console_icon_weight', 'console_border_style',
    ] as const;

    const load = async () => {
        loading.value = true;
        try {
            const res = await api.get('/manage/system/console-theme');
            const data = res.data ?? res;
            const schema = (data.schema ?? {}) as Record<string, { default?: unknown; type?: string }>;
            const settings = (data.settings ?? {}) as Record<string, unknown>;
            for (const key of FORM_KEYS) {
                const def = schema[key];
                const raw = settings[key] ?? def?.default ?? form[key];
                const isNumeric = def?.type === 'range' || key.includes('intensity') || key.includes('angle')
                    || key.includes('opacity') || key === 'console_button_radius';
                form[key] = isNumeric ? Number(raw) : String(raw);
            }
            if (!form.console_color_preset) form.console_color_preset = CONSOLE_COLOR_PRESET_CUSTOM;
            syncDraft();
        } catch (e) {
            logger.error('[ConsoleAppearance] load failed', e);
            toast.error.default(t('system.settings.consoleAppearance.loadError'));
        } finally {
            loading.value = false;
        }
    };

    const save = async () => {
        saving.value = true;
        try {
            const payload = [
                { key: 'console_theme_mode', value: form.console_theme_mode, group: 'console_branding', type: 'string' },
                { key: 'console_color_preset', value: form.console_color_preset, group: 'console_branding', type: 'string' },
                { key: 'console_brand_primary', value: form.console_brand_primary, group: 'console_branding', type: 'string' },
                { key: 'console_brand_primary_dark', value: form.console_brand_primary_dark, group: 'console_branding', type: 'string' },
                { key: 'console_button_radius', value: String(form.console_button_radius), group: 'console_branding', type: 'integer' },
                { key: 'console_surface_style', value: form.console_surface_style, group: 'console_branding', type: 'string' },
                { key: 'console_sidebar_style', value: form.console_sidebar_style, group: 'console_branding', type: 'string' },
                { key: 'console_sidebar_accent', value: form.console_sidebar_accent, group: 'console_branding', type: 'string' },
                { key: 'console_navbar_style', value: form.console_navbar_style, group: 'console_branding', type: 'string' },
                { key: 'console_popper_opacity', value: String(form.console_popper_opacity), group: 'console_branding', type: 'integer' },
                { key: 'console_glass_gradient_preset', value: form.console_glass_gradient_preset, group: 'console_branding', type: 'string' },
                { key: 'console_glass_gradient_color', value: form.console_glass_gradient_color, group: 'console_branding', type: 'string' },
                { key: 'console_glass_gradient_intensity', value: String(form.console_glass_gradient_intensity), group: 'console_branding', type: 'integer' },
                { key: 'console_glass_gradient_angle', value: String(form.console_glass_gradient_angle), group: 'console_branding', type: 'integer' },
                { key: 'app_logo_light', value: form.app_logo_light, group: 'brand', type: 'image' },
                { key: 'app_logo_dark', value: form.app_logo_dark, group: 'brand', type: 'image' },
                { key: 'app_logo_compact', value: form.app_logo_compact, group: 'brand', type: 'image' },
                { key: 'app_favicon', value: form.app_favicon, group: 'brand', type: 'image' },
                { key: 'console_font_primary', value: String(form.console_font_primary), group: 'console_branding', type: 'string' },
                { key: 'console_font_mono', value: String(form.console_font_mono), group: 'console_branding', type: 'string' },
                { key: 'console_shadow_elevation', value: String(form.console_shadow_elevation), group: 'console_branding', type: 'string' },
                { key: 'console_border_style', value: String(form.console_border_style), group: 'console_branding', type: 'string' },
                { key: 'console_button_style', value: String(form.console_button_style), group: 'console_branding', type: 'string' },
                { key: 'console_card_style', value: String(form.console_card_style), group: 'console_branding', type: 'string' },
                { key: 'console_modal_backdrop_opacity', value: String(form.console_modal_backdrop_opacity), group: 'console_branding', type: 'integer' },
                { key: 'console_dropdown_style', value: String(form.console_dropdown_style), group: 'console_branding', type: 'string' },
                { key: 'console_icon_weight', value: String(form.console_icon_weight), group: 'console_branding', type: 'string' },
            ];
            await api.post('/manage/system/settings/bulk-update', { settings: payload });
            await systemStore.fetchPublicSettings({ force: true });
            await systemStore.fetchAppIdentity();
            await reloadConsoleTheme(true);
            syncDraft();
            toast.success.action(t('system.settings.consoleAppearance.saved'));
        } catch (e) {
            logger.error('[ConsoleAppearance] save failed', e);
            toast.error.default(t('system.settings.consoleAppearance.saveError'));
        } finally {
            saving.value = false;
        }
    };

    watch(form, syncDraft, { deep: true });

    const ctx: ConsoleAppearanceContext = {
        loading,
        saving,
        activeTab,
        form,
        hasWhiteLabel,
        colorPreset,
        surfaceStyle,
        brandColor,
        brandColorDark,
        glassGradientPreset,
        glassGradientColor,
        primaryHsl,
        lightModeContrast,
        darkModeContrast,
        exportedThemeJson,
        importTarget,
        showLogoLightPicker,
        showLogoDarkPicker,
        showLogoCompactPicker,
        showFaviconPicker,
        clampGlassIntensity,
        clampGlassAngle,
        syncDraft,
        load,
        save,
        copyThemeConfig,
        importThemeConfig,
        CONSOLE_SURFACE_GLASS,
        CONSOLE_SURFACE_FLAT: 'flat',
        themeMode,
        isGlobalMode,
        isAdvancedMode,
    };

    return ctx;
}

export function provideConsoleAppearance(ctx: ConsoleAppearanceContext) {
    provide(CONSOLE_APPEARANCE_KEY, ctx);
}

export function useConsoleAppearanceContext(): ConsoleAppearanceContext {
    const ctx = inject(CONSOLE_APPEARANCE_KEY);
    if (!ctx) throw new Error('useConsoleAppearanceContext must be used within console appearance page');
    return ctx;
}
