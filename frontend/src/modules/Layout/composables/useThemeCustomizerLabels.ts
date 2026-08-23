import { computed, type MaybeRefOrGetter, toValue } from 'vue';
import { useI18n } from 'vue-i18n';

function slugifyLabel(label: string): string {
    return label
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, '_')
        .replace(/^_+|_+$/g, '');
}

/**
 * Resolves theme customizer labels: theme.<slug>.customizer.* first, then publishing.theme_customizer.items.*.
 */
export function useThemeCustomizerLabels(themeSlug: MaybeRefOrGetter<string | undefined>) {
    const { t, te } = useI18n();
    const slug = computed(() => {
        const s = toValue(themeSlug);
        return typeof s === 'string' && s.length > 0 ? s : '';
    });

    function themePath(segment: string, key: string): string {
        return `theme.${slug.value}.customizer.${segment}.${key}`;
    }

    function resolve(segment: 'items' | 'common_options' | 'manifest_categories', key: string): string | null {
        if (slug.value) {
            const tk = themePath(segment, key);
            if (te(tk)) return tk;
        }
        if (segment === 'items') {
            const pk = `publishing.theme_customizer.items.${key}`;
            if (te(pk)) return pk;
        } else {
            const pk = `publishing.theme_customizer.items.${segment}.${key}`;
            if (te(pk)) return pk;
        }
        return null;
    }

    function settingLabel(key: string | undefined, fallback: string): string {
        if (!key) return fallback;
        const resolved = resolve('items', key);
        return resolved ? t(resolved) : fallback;
    }

    function settingHint(key: string | undefined, fallback: string): string {
        if (!key) return fallback;
        const hintKey = `${key}_hint`;
        const resolved = resolve('items', hintKey);
        return resolved ? t(resolved) : fallback;
    }

    function optionLabel(label: string): string {
        if (!label) return '';
        const sk = slugifyLabel(label);
        const resolved = resolve('common_options', sk);
        return resolved ? t(resolved) : label;
    }

    function fieldLabel(label: string): string {
        return optionLabel(label);
    }

    function categoryLabel(category: string): string {
        const ck = slugifyLabel(category);
        const resolved = resolve('manifest_categories', ck);
        return resolved ? t(resolved) : category;
    }

    return {
        slug,
        slugifyLabel,
        settingLabel,
        settingHint,
        optionLabel,
        fieldLabel,
        categoryLabel,
    };
}
