import type { RouteRecordRaw } from 'vue-router';

const routes: RouteRecordRaw[] = [
  {
    path: 'forms',
    name: 'forms',
    component: () => import('../views/Index.vue'),
    meta: {
      extension: 'forms',
      permission: 'use forms',
      title: 'Forms',
    },
  },
];

export default routes;
