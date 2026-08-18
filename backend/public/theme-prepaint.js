(function () {
    try {
        const path = window.location.pathname;
        const isConsole = path.startsWith('/dash') || path.startsWith('/auth') || path.startsWith('/login') || path.startsWith('/setup');
        
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
            // Public/Janari Prepaint
            const savedDark = localStorage.getItem('frontend-dark-mode');
            const mq = window.matchMedia('(prefers-color-scheme: dark)');
            const isDark = savedDark === 'dark' || (savedDark === 'system' && mq.matches) || (!savedDark && mq.matches);
            
            if (isDark) {
                document.documentElement.classList.add('dark');
            } else if (savedDark === 'light') {
                document.documentElement.classList.remove('dark');
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
