/**
 * Module-level theme block contract (Fase 5). Export slug + registerThemeBlocks.
 * Engine loaders in @/engine/plugins/loaders take precedence for the same slug.
 */
import type { RegisterThemeBlocksFn } from '@/engine/plugins/types';

export const themeBlockPluginSlug = 'publishing-inline-notice';

export const registerThemeBlocks: RegisterThemeBlocksFn = () => {
  // Add blocks when sys_plugins row exists and manifest includes this slug.
};
