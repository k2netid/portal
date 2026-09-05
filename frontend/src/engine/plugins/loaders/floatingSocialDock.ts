import type { RegisterThemeBlocksFn } from '@/engine/plugins/types';
import FloatingSocialDockBlock from '@/engine/plugins/blocks/FloatingSocialDockBlock.vue';

export const registerThemeBlocks: RegisterThemeBlocksFn = (register) => {
  register('floating_overlay', {
    pluginSlug: 'floating-social-dock',
    component: FloatingSocialDockBlock,
    priority: 10,
  });
};
