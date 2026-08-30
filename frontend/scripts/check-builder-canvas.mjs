import { chromium } from 'playwright';

const baseURL = process.env.PLAYWRIGHT_BASE_URL || 'http://127.0.0.1:5173';
const email = process.env.E2E_LOGIN_EMAIL || 'super@jejakawan.com';
const password = process.env.E2E_LOGIN_PASSWORD || 'password';
const token = process.env.E2E_CAPTCHA_BYPASS_TOKEN || 'local-e2e';

const browser = await chromium.launch({ headless: true });
const context = await browser.newContext({
    extraHTTPHeaders: { 'X-E2E-Captcha-Bypass': token },
});

const page = await context.newPage();
await page.goto(`${baseURL}/auth/console-sign-in`, { waitUntil: 'domcontentloaded' });
await page.fill('#email', email);
await page.fill('#password', password);
await page.locator('button[type="submit"]').click();
await page.waitForURL(/ja-dash|\/dash/i, { timeout: 25_000 });
await page.waitForSelector('[data-testid="console-sidebar-brand"]', { timeout: 20_000 });

const dump = await page.evaluate(() => {
    const app = document.querySelector('#app');
    const vueApp = app && app.__vue_app__;
    const router = vueApp?.config?.globalProperties?.$router;
    const routes = (router?.getRoutes?.() || []).map((r) => ({
        name: String(r.name ?? ''),
        path: r.path,
        recordPath: r.path,
    }));
    const hrefs = [...document.querySelectorAll('a[href]')]
        .map((a) => a.getAttribute('href'))
        .filter(Boolean)
        .filter((h) => /editor|theme|layout|site|menu/i.test(h));
    return {
        url: location.href,
        routeCount: routes.length,
        builder: routes.filter((r) => /builder|site-editor|themes|menus/i.test(`${r.name} ${r.path}`)),
        hrefs,
    };
});

console.log(JSON.stringify(dump, null, 2));

await page.getByRole('button', { name: /Editorial/i }).first().click();
await page.locator('a[href="/ja-dash/site-editor"]').first().click();
await page.waitForURL(/site-editor/, { timeout: 20_000 });
await page.waitForTimeout(5000);

const canvas = await page.locator('.ja-builder, .ja-builder-main, .canvas').count();
await page.screenshot({ path: 'test-results/builder-canvas-check.png', fullPage: true });
console.log(JSON.stringify({
    afterNav: page.url(),
    canvasCount: canvas,
    title: await page.title(),
}, null, 2));

await browser.close();
