import type { RegisterThemeBlocksFn } from '@/engine/plugins/types';
import ContentShareCta from '@/engine/plugins/blocks/ContentShareCta.vue';

export const registerThemeBlocks: RegisterThemeBlocksFn = (register) => {
  register('after_post_content', {
    pluginSlug: 'content-share-bar',
    component: ContentShareCta,
    priority: 20,
  });
};
