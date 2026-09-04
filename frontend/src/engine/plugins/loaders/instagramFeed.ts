import type { RegisterThemeBlocksFn } from '@/engine/plugins/types';
import InstagramFeedBlock from '@/engine/plugins/blocks/InstagramFeedBlock.vue';

export const registerThemeBlocks: RegisterThemeBlocksFn = (register) => {
  register('after_hero', {
    pluginSlug: 'instagram-feed',
    component: InstagramFeedBlock,
    priority: 15,
  });
  register('before_footer', {
    pluginSlug: 'instagram-feed',
    component: InstagramFeedBlock,
    priority: 15,
  });
};
