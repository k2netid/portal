import type { RegisterThemeBlocksFn } from '@/engine/plugins/types';
import BeforeFooterPromo from '@/engine/plugins/blocks/BeforeFooterPromo.vue';

export const registerThemeBlocks: RegisterThemeBlocksFn = (register) => {
  register('before_footer', {
    pluginSlug: 'before-footer-promo',
    component: BeforeFooterPromo,
    priority: 30,
  });
};
