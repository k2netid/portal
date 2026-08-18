import type { RegisterThemeBlocksFn } from '@/engine/plugins/types';

export type ThemeBlocksModule = {
  themeBlockPluginSlug: string;
  registerThemeBlocks: RegisterThemeBlocksFn;
};

const moduleThemeBlocks = import.meta.glob<ThemeBlocksModule>(
  '/src/modules/**/plugins/themeBlocks.ts',
);

/** Resolve module `plugins/themeBlocks.ts` files to slug-keyed loaders. */
export async function resolveDiscoveredThemeBlockLoaders(): Promise<
  Record<string, () => Promise<{ registerThemeBlocks: RegisterThemeBlocksFn }>>
> {
  const bySlug: Record<string, () => Promise<{ registerThemeBlocks: RegisterThemeBlocksFn }>> = {};

  for (const importFn of Object.values(moduleThemeBlocks)) {
    const mod = await importFn();
    const slug = mod.themeBlockPluginSlug;
    if (!slug) continue;
    bySlug[slug] = async () => ({ registerThemeBlocks: mod.registerThemeBlocks });
  }

  return bySlug;
}
