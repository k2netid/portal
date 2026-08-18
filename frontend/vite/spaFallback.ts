import type { Connect, Plugin } from 'vite';
import { resolveIsConsoleEntrypoint } from '../src/engine/router/entrypoint';

/** SPA history fallback: serve the correct HTML shell (console vs public). */
const shouldRewriteToShell = (pathname: string): boolean => {
    if (pathname === '/' || pathname === '') {
        return true;
    }

    if (
        pathname.startsWith('/@')
        || pathname.startsWith('/node_modules/')
        || pathname.startsWith('/assets/')
        || pathname.startsWith('/api')
        || pathname.startsWith('/sanctum')
    ) {
        return false;
    }

    if (/\.[a-z0-9]+$/i.test(pathname)) {
        return false;
    }

    return true;
};

const shellHtmlForPath = (pathname: string): string => (
    resolveIsConsoleEntrypoint(pathname) ? '/console.html' : '/index.html'
);

const installSpaFallback = (middlewares: Connect.Server): void => {
    middlewares.use((req, _res, next) => {
        const rawUrl = req.url ?? '/';
        const [pathname, search = ''] = rawUrl.split('?');
        const cleanPath = pathname === '/' ? '/' : pathname.replace(/\/$/, '') || '/';

        if (shouldRewriteToShell(cleanPath)) {
            const html = shellHtmlForPath(cleanPath);
            req.url = search ? `${html}?${search}` : html;
        }

        next();
    });
};

export function spaFallbackPlugin(): Plugin {
    return {
        name: 'ja-spa-fallback',
        configureServer(server) {
            installSpaFallback(server.middlewares);
        },
        configurePreviewServer(server) {
            installSpaFallback(server.middlewares);
        },
    };
}
