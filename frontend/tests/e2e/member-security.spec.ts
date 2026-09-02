import { execSync } from 'node:child_process';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import { expect, test } from '@playwright/test';

const E2E_HEADERS = {
    'X-E2E-Captcha-Bypass': process.env.E2E_CAPTCHA_BYPASS_TOKEN ?? 'local-e2e',
    Accept: 'application/json',
};

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const backendRoot = path.resolve(__dirname, '../../../backend');

function memberPassword(): string {
    return process.env.E2E_MEMBER_PASSWORD ?? 'Password12!';
}

function totpForSecret(secret: string): string {
    const escaped = secret.replace(/'/g, "'\\''");
    const out = execSync(
        `php -r 'require "${backendRoot}/vendor/autoload.php"; echo (new PragmaRX\\Google2FA\\Google2FA)->getCurrentOtp("${escaped}");'`,
        { encoding: 'utf8' },
    );

    return out.trim();
}

async function registerMember(
    request: import('@playwright/test').APIRequestContext,
    email: string,
): Promise<string> {
    const password = memberPassword();
    const response = await request.post('/api/v1/public/member/register', {
        headers: E2E_HEADERS,
        data: {
            name: 'E2E Reader',
            email,
            password,
            password_confirmation: password,
        },
    });

    expect(response.ok()).toBeTruthy();
    const body = await response.json();
    return String(body.data.token);
}

test.describe('Member security flows', () => {
    test('registers and signs in without console IAM', async ({ page }) => {
        const email = `e2e-member-${Date.now()}@example.com`;
        const password = memberPassword();

        await page.goto('/member/register');
        await page.locator('input[autocomplete="name"]').fill('E2E Reader');
        await page.locator('input[type="email"]').fill(email);
        await page.locator('input[autocomplete="new-password"]').first().fill(password);
        await page.locator('input[autocomplete="new-password"]').nth(1).fill(password);
        await page.locator('button[type="submit"]').click();

        await expect(page).toHaveURL(/\/member(?:\/dashboard)?/, { timeout: 20_000 });

        await page.goto('/member/login');
        await page.locator('input[type="email"]').fill(email);
        await page.locator('input[autocomplete="current-password"]').fill(password);
        await page.locator('button[type="submit"]').click();

        await expect(page).toHaveURL(/\/member(?:\/dashboard)?/, { timeout: 20_000 });
        await expect(page.getByText(/halaman tidak stabil/i)).toHaveCount(0);
    });

    test('shows error for invalid member credentials', async ({ page }) => {
        await page.goto('/member/login');
        await page.locator('input[type="email"]').fill('missing-reader@example.com');
        await page.locator('input[autocomplete="current-password"]').fill('wrong-password-xyz');
        await page.locator('button[type="submit"]').click();

        await expect(page.getByText(/invalid|credential|failed|salah/i).first()).toBeVisible();
        await expect(page).toHaveURL(/\/member\/login/);
    });

    test('completes login when two-factor is enabled', async ({ page, request }) => {
        test.setTimeout(60_000);

        execSync(
            `cd ${backendRoot} && php artisan tinker --execute="\\Modules\\Core\\System\\Models\\Setting::set('enable_2fa', true, 'boolean', 'security');"`,
            { stdio: 'ignore' },
        );

        const email = `e2e-2fa-${Date.now()}@example.com`;
        const password = memberPassword();
        const token = await registerMember(request, email);

        const generate = await request.post('/api/v1/member/2fa/generate', {
            headers: { ...E2E_HEADERS, Authorization: `Bearer ${token}` },
        });
        expect(generate.ok()).toBeTruthy();
        const generateBody = await generate.json();
        const secret = String(generateBody.data.secret);
        const otp = totpForSecret(secret);

        const verify = await request.post('/api/v1/member/2fa/verify', {
            headers: { ...E2E_HEADERS, Authorization: `Bearer ${token}` },
            data: { code: otp },
        });
        expect(verify.ok()).toBeTruthy();

        await page.goto('/member/login');
        await page.locator('input[type="email"]').fill(email);
        await page.locator('input[autocomplete="current-password"]').fill(password);
        await page.locator('button[type="submit"]').click();

        await expect(page.getByText(/two-factor|authentication code|autentikasi/i).first()).toBeVisible({ timeout: 15_000 });

        const loginOtp = totpForSecret(secret);
        await page.locator('input[autocomplete="one-time-code"]').fill(loginOtp);
        await page.locator('button[type="submit"]').click();

        await expect(page).toHaveURL(/\/member(?:\/dashboard)?/, { timeout: 20_000 });
    });

    test('security page loads for authenticated member', async ({ page, request }) => {
        const email = `e2e-security-${Date.now()}@example.com`;
        const token = await registerMember(request, email);

        await page.goto('/member/login');
        await page.evaluate((memberToken) => {
            localStorage.setItem('ja_member_token', memberToken);
        }, token);

        await page.goto('/member/security');
        await expect(page.getByRole('heading', { name: /security|keamanan/i }).first()).toBeVisible({ timeout: 15_000 });
        await expect(page.getByText(/change password|password|kata sandi/i).first()).toBeVisible();
    });
});
