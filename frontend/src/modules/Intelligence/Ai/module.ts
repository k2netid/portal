import type { AppModule } from '@/engine/types/module';
import aiRoutes from './router';

export const AiModule: AppModule = {
    id: 'ai',
    name: 'AI Assistant', // display; nav uses ai.navigation.panel
    routes: aiRoutes,
    navigation: [],
};

export default AiModule;
