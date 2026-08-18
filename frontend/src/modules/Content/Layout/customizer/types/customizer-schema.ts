import type { ThemeSetting } from '@/modules/Content/Layout/types/theme';
import type { CustomizerSettingScope } from '@/modules/Content/Layout/customizer/types/extension';

/** Single field in platform or theme settings_schema. */
export interface CustomizerSettingDefinition extends ThemeSetting {
    /** platform = host global; theme = active theme package only. */
    scope?: CustomizerSettingScope;
    /** Sidebar grouping id (host or theme extension). */
    group?: string;
    hidden?: boolean;
}

export type CustomizerSettingsSchema = Record<string, CustomizerSettingDefinition>;
