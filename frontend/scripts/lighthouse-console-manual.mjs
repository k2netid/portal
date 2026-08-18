#!/usr/bin/env node
/**
 * Manual Lighthouse runs for the admin console (no CI gate).
 * Requires a reachable app, e.g. full stack on http://127.0.0.1:8081
 *
 * Usage:
 *   npm run lighthouse:console
 *   npm run lighthouse:console:mobile
 */
import { mkdirSync } from 'node:fs';
import { spawnSync } from 'node:child_process';
import { join } from 'node:path';

const baseUrl = (process.env.LIGHTHOUSE_BASE_URL ?? 'http://127.0.0.1:8081').replace(/\/$/, '');
const outDir = join(process.cwd(), 'tmp');
const target = process.argv[2] ?? 'sign-in';

const presets = {
    'sign-in': {
        path: '/auth/console-sign-in',
        outfile: 'lighthouse-console-sign-in.html',
        args: ['--preset=desktop', '--only-categories=performance,accessibility,best-practices'],
    },
    'sign-in-mobile': {
        path: '/auth/console-sign-in',
        outfile: 'lighthouse-console-sign-in-mobile.html',
        args: ['--preset=perf', '--form-factor=mobile', '--only-categories=performance,accessibility'],
    },
};

const config = presets[target];
if (!config) {
    console.error(`[lighthouse:console] Unknown target "${target}". Valid: ${Object.keys(presets).join(', ')}`);
    process.exit(1);
}

mkdirSync(outDir, { recursive: true });
const url = `${baseUrl}${config.path}`;
const outputPath = join(outDir, config.outfile);

console.log(`[lighthouse:console] ${url} → ${outputPath}`);

const result = spawnSync(
    'npx',
    [
        'lighthouse',
        url,
        ...config.args,
        '--output=html',
        `--output-path=${outputPath}`,
        '--quiet',
    ],
    { stdio: 'inherit' },
);

if (result.status !== 0) {
    console.error('[lighthouse:console] Lighthouse failed. Is the stack up? Set LIGHTHOUSE_BASE_URL if not on 8081.');
    process.exit(result.status ?? 1);
}

console.log(`[lighthouse:console] Done. Open file://${outputPath}`);
