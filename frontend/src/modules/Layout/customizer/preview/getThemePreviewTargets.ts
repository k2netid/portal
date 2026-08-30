import type { CustomizerPreviewTargetConfig } from '@/modules/Layout/customizer/preview/protocol';
import janariTargets from '@/modules/Layout/views/themes/janari/customizer/preview.targets.json';

const bySlug: Record<string, Record<string, CustomizerPreviewTargetConfig>> = {
  janari: janariTargets.targets as Record<string, CustomizerPreviewTargetConfig>,
};

export function getThemePreviewTargets(slug: string): Record<string, CustomizerPreviewTargetConfig> {
  const key = String(slug || '').toLowerCase();
  if (bySlug[key]) return bySlug[key];
  if (key.startsWith('janari')) return bySlug.janari;
  return {};
}

export function resolvePreviewNavItemId(
  targets: Record<string, CustomizerPreviewTargetConfig>,
  targetKey: string,
  mode?: 'design' | 'bindings',
): string | null {
  const cfg = targets[targetKey];
  if (!cfg) return null;
  if ((mode === 'bindings' || (!mode && cfg.mode === 'bindings')) && cfg.bindingsId) {
    return `comp-${cfg.bindingsId}`;
  }
  return cfg.navItemId;
}
