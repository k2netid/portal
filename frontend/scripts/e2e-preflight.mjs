#!/usr/bin/env node
const apiBase = (process.env.VITE_DEV_API_PROXY || process.env.E2E_API_PROXY_TARGET || 'http://127.0.0.1:8081').replace(/\/$/, '');
const email = process.env.E2E_LOGIN_EMAIL || 'super@jejakawan.com';
const password = process.env.E2E_LOGIN_PASSWORD || 'ChangeMeOnFirstLogin!';

async function main() {
    const health = await fetch(`${apiBase}/up`);
    if (!health.ok) {
        console.error(`Backend not reachable at ${apiBase}/up (${health.status})`);
        process.exit(1);
    }
    console.log(`OK ${apiBase}/up`);

    const jar = [];
    const csrf = await fetch(`${apiBase}/sanctum/csrf-cookie`, { redirect: 'manual' });
    if (!csrf.ok && csrf.status !== 204) {
        console.error('CSRF cookie failed', csrf.status);
        process.exit(1);
    }
    const setCookie = csrf.headers.getSetCookie?.() ?? [];
    jar.push(...setCookie);
    const xsrf = jar.map((c) => c.split(';')[0]).find((c) => c.startsWith('XSRF-TOKEN='));
    const cookieHeader = jar.map((c) => c.split(';')[0]).join('; ');

    const login = await fetch(`${apiBase}/api/v1/public/system/auth/login`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            Cookie: cookieHeader,
            ...(xsrf ? { 'X-XSRF-TOKEN': decodeURIComponent(xsrf.split('=')[1]) } : {}),
        },
        body: JSON.stringify({ email, password }),
    });
    const body = await login.json().catch(() => ({}));
    if (!login.ok || !body.success) {
        console.error('Seed login failed', login.status, body);
        process.exit(1);
    }
    console.log(`OK login ${email}`);
}

main().catch((e) => {
    console.error(e);
    process.exit(1);
});
