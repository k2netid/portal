import type { ThemeCustomizerExtension } from '@/modules/Layout/customizer/types/extension';
import { janariCustomizerExtension } from '@/modules/Layout/views/themes/janari/customizer';

/** Bundled theme customizer extensions (add new themes here when introduced). */
const extensionBySlug: Record<string, ThemeCustomizerExtension> = {
    janari: janariCustomizerExtension,
};

export function resolveThemeCustomizerExtension(slug: string): ThemeCustomizerExtension | null {
    const normalized = (slug || '').trim().toLowerCase();
    if (!normalized) return null;
    return extensionBySlug[normalized] ?? null;
}

export function getThemeBindingRegistry(slug: string) {
    return resolveThemeCustomizerExtension(slug)?.bindings ?? [];
}

export function getReservedManifestCategories(slug: string): Set<string> {
    const list = resolveThemeCustomizerExtension(slug)?.reservedManifestCategories ?? [];
    return new Set(list);
}
