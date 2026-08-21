import { type InjectionKey } from 'vue';

export interface FileManagerContext {
    currentPath: string;
    refresh: () => Promise<void>;
}

export const FileManagerKey: InjectionKey<FileManagerContext> = Symbol('FileManager');
