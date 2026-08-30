import { type InjectionKey } from 'vue';
import type { FileManager } from '@/modules/Core/Infra/composables/useFileManager';
import type { MediaManager } from '@/modules/Media/composables/useMediaManager';

export const FileManagerKey: InjectionKey<FileManager> = Symbol('FileManager');
export const MediaManagerKey: InjectionKey<MediaManager> = Symbol('MediaManager');
