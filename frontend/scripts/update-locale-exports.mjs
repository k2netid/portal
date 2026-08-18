#!/usr/bin/env node
import { readFileSync, writeFileSync, readdirSync, statSync } from 'node:fs';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';

const root = join(dirname(fileURLToPath(import.meta.url)), '..');
const modulesRoot = join(root, 'src/modules');

function collectModuleLocaleDirs(dir, acc = []) {
    for (const name of readdirSync(dir)) {
        const full = join(dir, name);
        if (!statSync(full).isDirectory()) continue;
        if (full.includes('/views/themes/')) {
            collectModuleLocaleDirs(full, acc);
            continue;
        }
        const localesPath = join(full, 'locales');
        try {
            if (
                statSync(localesPath).isDirectory() &&
                readdirSync(localesPath).some((f) => f.endsWith('.json'))
            ) {
                acc.push(localesPath);
            }
        } catch {
            /* no locales/ */
        }
        collectModuleLocaleDirs(full, acc);
    }
    return acc;
}

collectModuleLocaleDirs(modulesRoot).forEach((localesDir) => {
    const indexPath = join(localesDir, 'index.ts');
    try {
        if (statSync(indexPath).isFile()) {
            const content = `import en from './en.json';
import id from './id.json';
import su from './su.json';

export default { en, id, su } as const;
`;
            writeFileSync(indexPath, content, 'utf8');
            console.log(`Updated: ${indexPath}`);
        }
    } catch (e) {
        console.error(`Failed to update ${indexPath}:`, e.message);
    }
});

console.log('OK: All module locale indexes updated to export Basa Sunda (su)!');
