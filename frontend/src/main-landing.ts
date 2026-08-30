import '@/styles/shell/console-tailwind.css';
import { createApp } from 'vue';
import { setAppShell } from '@/config/shell';
import LandingApp from './LandingApp.vue';

setAppShell('landing');

createApp(LandingApp).mount('#app');
