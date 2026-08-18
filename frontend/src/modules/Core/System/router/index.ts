import type { RouteRecordRaw } from 'vue-router';
import innerSystemRoutes from './system';
import teamRoutes from './team';
import developerRoutes from './developer';

/** Dashboard route is registered in `engine/router/console.ts` as `system.dashboard`. */
const systemRoutes: RouteRecordRaw[] = [
    ...innerSystemRoutes,
    ...teamRoutes,
    ...developerRoutes,
];

export default systemRoutes;
