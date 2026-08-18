/**
 * Optional remote ESM blocks.js (gated server-side via manifest blocks_url).
 */
import { logger } from '@/shared/utils/logger';

const loaded = new Set<string>();

export async function loadRemotePluginBlocks(blocksUrl: string, pluginSlug: string): Promise<void> {
  if (!blocksUrl || loaded.has(blocksUrl)) {
    return;
  }

  try {
    await import(/* @vite-ignore */ blocksUrl);
    loaded.add(blocksUrl);
    logger.info('[pluginBootstrap] Remote theme blocks loaded', { pluginSlug, blocksUrl });
  } catch (error) {
    logger.error(`[pluginBootstrap] Remote blocks.js failed for "${pluginSlug}"`, error);
  }
}
