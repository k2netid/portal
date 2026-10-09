/**
 * Bootstrap active plugins' theme App Blocks (public shell only).
 */
import api from '@/engine/api/client';
import { getThemeBlockLoaders } from '@/engine/plugins/loaders';
import {
  registerAppBlock,
  resetAppBlocksRegistry,
  unregisterAppBlocksForPlugin,
} from '@/engine/plugins/pluginRegistry';
import type { PluginBlocksApiResponse } from '@/engine/plugins/types';
import { loadRemotePluginBlocks } from '@/engine/plugins/loadRemotePluginBlocks';
import { logger } from '@/shared/utils/logger';

let bootstrapped = false;

export async function bootstrapPluginThemeBlocks(force = false): Promise<void> {
  if (bootstrapped && !force) {
    return;
  }

  resetAppBlocksRegistry();

  let manifest: PluginBlocksApiResponse['plugins'] = [];

  let apiFailed = false;

  try {
    const response = await api.get('/public/layout/plugin-blocks');
    const data = (response.data ?? response) as PluginBlocksApiResponse;
    manifest = Array.isArray(data.plugins) ? data.plugins : [];
  } catch (error) {
    apiFailed = true;
    logger.warning('[pluginBootstrap] Could not load plugin-blocks manifest; using bundled loaders only in dev', error);
  }

  const activeSlugs = new Set(manifest.map((p) => p.slug));

  const themeBlockLoaders = await getThemeBlockLoaders();

  for (const entry of manifest) {
    const loader = themeBlockLoaders[entry.slug];
    if (!loader) {
      logger.debug(`[pluginBootstrap] No frontend loader for plugin "${entry.slug}"`);
      continue;
    }

    try {
      const mod = await loader();
      mod.registerThemeBlocks((slotName, block) => {
        if (!entry.slots.includes(slotName)) {
          return;
        }
        registerAppBlock(slotName, {
          ...block,
          pluginSlug: block.pluginSlug || entry.slug,
          priority: block.priority ?? entry.priority,
        });
      });

      if (entry.blocks_url) {
        await loadRemotePluginBlocks(entry.blocks_url, entry.slug);
      }
    } catch (error) {
      logger.error(`[pluginBootstrap] Failed to load blocks for "${entry.slug}"`, error);
    }
  }

  // Dev fallback: ONLY run if API request actually failed/errored (e.g. offline dev without backend)
  // NEVER run when API succeeded and authoritatively returned 0 active plugins!
  if (apiFailed && import.meta.env.DEV) {
    for (const [slug, loader] of Object.entries(themeBlockLoaders)) {
      try {
        const mod = await loader();
        mod.registerThemeBlocks((slotName, block) => {
          registerAppBlock(slotName, { ...block, pluginSlug: block.pluginSlug || slug });
        });
      } catch (error) {
        logger.error(`[pluginBootstrap] Fallback loader failed for "${slug}"`, error);
      }
    }
  }

  bootstrapped = true;
  logger.info('[pluginBootstrap] Theme app blocks ready', { plugins: [...activeSlugs] });
}

export function teardownPluginThemeBlocks(slug: string): void {
  unregisterAppBlocksForPlugin(slug);
}
