import { defineConfig } from '@playwright/test';

const procEnv = (globalThis as { process?: { env?: Record<string, string | undefined> } }).process?.env;

const useFullStack = procEnv?.PLAYWRIGHT_USE_FULL_STACK === '1';

const baseURL =
    procEnv?.PLAYWRIGHT_BASE_URL ||
    (useFullStack ? 'http://127.0.0.1:8081' : 'http://127.0.0.1:4173');

const enableWebServer = procEnv?.PLAYWRIGHT_WEB_SERVER === '1' && !useFullStack;

const apiProxyTarget = procEnv?.VITE_DEV_API_PROXY || procEnv?.E2E_API_PROXY_TARGET || 'http://127.0.0.1:8081';

export default defineConfig({
    testDir: './tests/e2e',
    timeout: 30_000,
    fullyParallel: true,
    workers: procEnv?.PLAYWRIGHT_WORKERS
        ? Number(procEnv.PLAYWRIGHT_WORKERS)
        : (procEnv?.CI ? 2 : undefined),
    expect: {
        timeout: 10_000,
    },
    use: {
        baseURL,
        trace: 'on-first-retry',
        extraHTTPHeaders: {
            'X-E2E-Captcha-Bypass': procEnv?.E2E_CAPTCHA_BYPASS_TOKEN || 'local-e2e',
        },
    },
    reporter: [['list']],
    ...(enableWebServer
        ? {
              webServer: {
                  command: `VITE_DEV_API_PROXY=${apiProxyTarget} VITE_DEV_PORT=4173 npm run dev -- --host 127.0.0.1 --port 4173`,
                  url: baseURL,
                  reuseExistingServer: !procEnv?.CI,
                  timeout: 120_000,
                  env: {
                      ...(procEnv as Record<string, string>),
                      VITE_DEV_API_PROXY: apiProxyTarget,
                      VITE_DEV_PORT: '4173',
                  },
              },
          }
        : {}),
});
