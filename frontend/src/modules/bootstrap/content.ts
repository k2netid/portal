export function consolePathNeedsContentModules(_pathname: string): boolean {
    return false;
}

export function consolePathNeedsDeferredModules(_pathname: string): boolean {
    return false;
}

export async function loadContentModules() {
    return [];
}

export async function loadBootstrapConsoleModules(_pathname: string) {
    const { loadCoreModules } = await import('./core');
    const core = await loadCoreModules();

    return [...core];
}
