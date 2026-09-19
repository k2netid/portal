(function () {
    try {
        const path = window.location.pathname;
        const isConsole = path.startsWith('/dash') || path.startsWith('/ja-dash') || path.startsWith('/auth') || path.startsWith('/login') || path.startsWith('/setup') || path.startsWith('/manage');
        const cachedFavicon = isConsole
            ? localStorage.getItem('ja_console_favicon_href')
            : localStorage.getItem('ja_site_favicon_href');

        function isGenericFavicon(href) {
            if (!href) return true;
            try {
                var p = new URL(href, window.location.origin).pathname.toLowerCase();
                return p === '/favicon.ico' || p.endsWith('/favicon.ico');
            } catch (e) {
                return href === '/favicon.ico' || /\/favicon\.ico$/i.test(href);
            }
        }
        var current = document.querySelector('link[rel="icon"], link[rel="shortcut icon"]');
        var currentHref = current ? (current.getAttribute('href') || '') : '';
        if (cachedFavicon && !isGenericFavicon(cachedFavicon) && isGenericFavicon(currentHref)) {
            document.querySelectorAll('link[rel="icon"], link[rel="shortcut icon"], link[rel="apple-touch-icon"]').forEach(function (el) {
                el.parentNode && el.parentNode.removeChild(el);
            });
            var icon = document.createElement('link');
            icon.rel = 'icon';
            icon.href = cachedFavicon;
            document.head.appendChild(icon);
        }

        if (isConsole) {
            // Console Prepaint
            const savedDark = localStorage.getItem('console-dark-mode');
            const mq = window.matchMedia('(prefers-color-scheme: dark)');
            const isDark = savedDark === 'dark' || (savedDark === 'system' && mq.matches) || (!savedDark && mq.matches);
            
            if (isDark) {
                document.documentElement.classList.add('dark');
            } else if (savedDark === 'light') {
                document.documentElement.classList.remove('dark');
            }

            const snapshotStr = localStorage.getItem('console_theme_snapshot_v1');
            if (snapshotStr) {
                const snapshot = JSON.parse(snapshotStr);
                
                // Base tokens
                if (snapshot.mode) {
                    document.documentElement.setAttribute('data-console-theme-mode', snapshot.mode);
                }
                
                // Advanced/Global CSS Variables
                if (snapshot.cssVars) {
                    Object.entries(snapshot.cssVars).forEach(([key, value]) => {
                        document.documentElement.style.setProperty(key, value);
                    });
                }
                
                // Layout Attrs
                if (snapshot.layoutAttrs) {
                    Object.entries(snapshot.layoutAttrs).forEach(([key, value]) => {
                        document.documentElement.setAttribute(key, value);
                    });
                }
            }
        } else {
            // Public/Janari/Layung Prepaint
            const isPreview = window !== window.parent || window.location.search.includes('ja_customizer_preview=1') || (function() { try { return sessionStorage.getItem('ja_customizer_preview') === '1'; } catch(e) { return false; } })();
            const savedDark = isPreview ? null : localStorage.getItem('frontend-dark-mode');
            const defaultMode = localStorage.getItem('ja_theme_default_mode') || 'dark';
            const activeMode = savedDark || defaultMode;
            const mq = window.matchMedia('(prefers-color-scheme: dark)');
            const isDark = activeMode === 'dark' || (activeMode === 'system' && mq.matches);
            
            if (isDark) {
                document.documentElement.classList.add('dark');
            } else if (activeMode === 'light') {
                document.documentElement.classList.remove('dark');
            }

            const savedLocale = localStorage.getItem('locale');
            const defaultLocale = localStorage.getItem('ja_theme_default_locale') || 'id';
            const effectiveLocale = savedLocale || (defaultLocale !== 'auto' ? defaultLocale : 'id');
            if (effectiveLocale) {
                document.documentElement.lang = effectiveLocale;
            }

            const snapshotStr = localStorage.getItem('frontend_theme_snapshot_v1');
            if (snapshotStr) {
                const snapshot = JSON.parse(snapshotStr);
                Object.entries(snapshot).forEach(([key, value]) => {
                    if (key.startsWith('data-janari-')) {
                        document.documentElement.setAttribute(key, value);
                    }
                });
            }
        }
    } catch (e) {
        // Silently fail to not block rendering
    }
})();
