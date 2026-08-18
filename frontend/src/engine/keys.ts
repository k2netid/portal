import { type InjectionKey } from 'vue';
import type { FileManager } from '@/modules/Content/Media/composables/useFileManager';
import type { MediaManager } from '@/engine/composables/useMediaManager';

export const FileManagerKey: InjectionKey<FileManager> = Symbol('FileManager');
export const MediaManagerKey: InjectionKey<MediaManager> = Symbol('MediaManager');
