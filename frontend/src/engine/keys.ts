import { type InjectionKey } from 'vue';
import type { FileManager } from '@/modules/Core/Infra/composables/useFileManager';

export const FileManagerKey: InjectionKey<FileManager> = Symbol('FileManager');
