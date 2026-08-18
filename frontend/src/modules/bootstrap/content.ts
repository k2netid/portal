const CONTENT_PATH_RE =
    /^\/[^/]+\/(?:publishing|media|forms|layout|library|content-studio|themes|pages|contents|categories|content-templates|comments|seo|menus|widgets|redirects|tags|custom-fields)(?:[/?#]|$)/i;

const DEFERRED_PATH_RE =
    /^\/[^/]+\/(?:search|ai|analytics|newsletter|email-templates)(?:[/?#]|$)/i;

export function consolePathNeedsContentModules(pathname: string): boolean {
    return CONTENT_PATH_RE.test(pathname);
}

export function consolePathNeedsDeferredModules(pathname: string): boolean {
    return DEFERRED_PATH_RE.test(pathname);
}

export async function loadContentModules() {
    const { contentModules } = await import('../Content');
    return contentModules;
}

export async function loadBootstrapConsoleModules(pathname: string) {
    const { loadCoreModules } = await import('./core');
    const core = await loadCoreModules();

    const modules = [...core];

    if (consolePathNeedsContentModules(pathname)) {
        const content = await loadContentModules();
        modules.push(...content);
    }

    if (consolePathNeedsDeferredModules(pathname)) {
        const { loadDeferredConsoleModules } = await import('@/modules/deferred/console');
        const deferred = await loadDeferredConsoleModules();
        modules.push(...deferred);
    }

    return modules;
}
