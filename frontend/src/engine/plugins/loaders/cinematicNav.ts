import type { RegisterThemeBlocksFn } from '@/engine/plugins/types';
import CinematicNavBlock from '@/engine/plugins/blocks/CinematicNavBlock.vue';

export const registerThemeBlocks: RegisterThemeBlocksFn = (register) => {
  register('floating_overlay', {
    pluginSlug: 'cinematic-nav',
    component: CinematicNavBlock,
    priority: 5,
  });
};
