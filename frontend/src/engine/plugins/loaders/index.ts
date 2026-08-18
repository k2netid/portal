import type { RegisterThemeBlocksFn } from '@/engine/plugins/types';
import { resolveDiscoveredThemeBlockLoaders } from './discoverModuleThemeBlockLoaders';

/** Engine-bundled loaders (always available). */
export const engineThemeBlockLoaders: Record<
  string,
  () => Promise<{ registerThemeBlocks: RegisterThemeBlocksFn }>
> = {
  'content-share-bar': () => import('./contentShareBar'),
  'before-footer-promo': () => import('./beforeFooterPromo'),
};

let mergedLoaders: Record<string, () => Promise<{ registerThemeBlocks: RegisterThemeBlocksFn }>> | null =
  null;

export async function getThemeBlockLoaders(): Promise<
  Record<string, () => Promise<{ registerThemeBlocks: RegisterThemeBlocksFn }>>
> {
  if (mergedLoaders) return mergedLoaders;
  const discovered = await resolveDiscoveredThemeBlockLoaders();
  mergedLoaders = { ...engineThemeBlockLoaders, ...discovered };
  return mergedLoaders;
}
