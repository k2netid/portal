import '@/styles/shell/console-tailwind.css';
import { createApp } from 'vue';
import { setAppShell } from '@/config/shell';
import landingI18n from '@/engine/i18n/landing';
import LandingApp from './LandingApp.vue';

setAppShell('landing');

createApp(LandingApp).use(landingI18n).mount('#app');
