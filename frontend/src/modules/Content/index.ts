import { PublishingModule } from './Publishing/module';
import { MediaModule } from './Media/module';
import { ContentStudioModule } from './Studio/module';

/** Layout, Forms, Library register as one studio module (R2026.15). */
export const contentModules = [
    PublishingModule,
    MediaModule,
    ContentStudioModule,
];

export { ContentStudioModule };
