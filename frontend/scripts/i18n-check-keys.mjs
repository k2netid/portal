#!/usr/bin/env node
/**
 * Validates locale keys against merged runtime bundles (en + id).
 *
 * - `npm run i18n:check` — gate: JSON syntax + nav/breadcrumbs/labelKey (CI-safe)
 * - `npm run i18n:check:full` — also scans all t()/$t() without fallback in src/
 */
import { readFileSync, readdirSync, statSync } from 'node:fs';
import { dirname, join, relative } from 'node:path';
import { fileURLToPath } from 'node:url';

const root = join(dirname(fileURLToPath(import.meta.url)), '..');
const localesRoot = join(root, 'src/locales');
const modulesRoot = join(root, 'src/modules');
const srcRoot = join(root, 'src');
const fullScan = process.argv.includes('--full');

const LOCALE_PREFIXES =
    'library|publishing|forms|media|layout|theme|newsletter|search|ai|system|infra|security|operational|member|sharedConsole|common|crm|builder';

const KEY_IN_STRING = new RegExp(
    `((?:${LOCALE_PREFIXES})\\.(?:[a-zA-Z0-9_]+|\\.[a-zA-Z0-9_]+)+)`,
    'g',
);

const STRICT_T_PATTERNS = [
    /\$t\s*\(\s*['"]([^'"]+)['"]\s*\)/g,
    /\bt\s*\(\s*['"]([^'"]+)['"]\s*\)/g,
];

const REQUIRED_PATTERNS = [
    /labelKey:\s*['"]([^'"]+)['"]/g,
    /breadcrumb:\s*['"]([^'"]+)['"]/g,
    /i18nKey:\s*['"]([^'"]+)['"]/g,
    /titleKey:\s*['"]([^'"]+)['"]/g,
];

function flatten(obj, prefix = '') {
    const out = new Set();
    for (const [key, value] of Object.entries(obj)) {
        const path = prefix ? `${prefix}.${key}` : key;
        if (value && typeof value === 'object' && !Array.isArray(value)) {
            for (const nested of flatten(value, path)) out.add(nested);
        } else {
            out.add(path);
        }
    }
    return out;
}

function loadJsonKeys(filePath, prefix) {
    const json = JSON.parse(readFileSync(filePath, 'utf8'));
    const keys = new Set();
    for (const k of flatten(json, prefix)) keys.add(k);
    return keys;
}

function namespaceFromPath(relPath) {
    const parts = relPath.split('/');
    const flatModuleMap = {
        Layout: 'layout',
        Library: 'library',
        Publishing: 'publishing',
        Media: 'media',
        Forms: 'forms',
        Newsletter: 'newsletter',
        Search: 'search',
        CmsAi: 'ai',
        Analytics: 'system.analytics',
        Member: 'member',
        Site: 'site',
    };

    if (parts[1] === 'locales' && flatModuleMap[parts[0]]) {
        if (parts[2]) {
            return parts[2].toLowerCase();
        }
        return flatModuleMap[parts[0]];
    }

    if (parts[0] === 'Layout' && parts[1] === 'views' && parts[2] === 'themes' && parts[3] && parts[4] === 'locales') {
        return `theme.${parts[3]}`;
    }

    if (parts[0] === 'Content' && parts[1]) {
        const map = { Library: 'library', Publishing: 'publishing', Media: 'media', Forms: 'forms', Layout: 'layout' };
        return map[parts[1]] ?? parts[1].toLowerCase();
    }
    if (parts[0] === 'Core' && parts[1]) {
        const map = { System: 'system', Infra: 'infra' };
        return map[parts[1]] ?? parts[1].toLowerCase();
    }
    if (parts[0] === 'Intelligence' && parts[1]) {
        const map = { Newsletter: 'newsletter', Search: 'search', Ai: 'ai' };
        return map[parts[1]] ?? parts[1].toLowerCase();
    }
    if (parts[0] === 'Operational' && parts[1] === 'Member') {
        return 'member';
    }
    if (parts[0] === 'Crm') {
        return 'crm';
    }
    if (parts[0] === 'Operational' && parts[1]) {
        return 'operational';
    }
    return parts[parts.length - 1]?.toLowerCase() ?? 'module';
}

function collectModuleLocaleDirs(dir, acc = []) {
    for (const name of readdirSync(dir)) {
        const full = join(dir, name);
        if (!statSync(full).isDirectory()) continue;
        if (full.includes('/views/themes/')) {
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

/** themes/<slug>/locales → theme.<slug>.* */
function collectThemeLocaleDirs(themesRoot, acc = []) {
    let themesDir;
    try {
        themesDir = join(themesRoot, 'Layout/views/themes');
        if (!statSync(themesDir).isDirectory()) return acc;
    } catch {
        return acc;
    }
    for (const slug of readdirSync(themesDir)) {
        const localesPath = join(themesDir, slug, 'locales');
        try {
            if (
                statSync(localesPath).isDirectory() &&
                readdirSync(localesPath).some((f) => f.endsWith('.json'))
            ) {
                acc.push({ localesPath, slug });
            }
        } catch {
            /* no locales/ */
        }
    }
    return acc;
}

function loadMergedLocaleKeys(lang) {
    const keys = new Set();
    const langDir = join(localesRoot, lang);

    for (const base of [
        'actions',
        'labels',
        'navigation',
        'messages',
        'errors',
        'validation',
        'placeholders',
        'pagination',
        'status',
        'time',
        'genders',
        'auth',
    ]) {
        for (const k of loadJsonKeys(join(langDir, `${base}.json`), `common.${base}`)) keys.add(k);
    }

    for (const k of loadJsonKeys(join(langDir, 'console.json'), 'sharedConsole')) keys.add(k);
    for (const k of loadJsonKeys(join(langDir, 'media.json'), 'media')) keys.add(k);
    for (const k of loadJsonKeys(join(langDir, 'ai.json'), 'ai')) keys.add(k);

    for (const k of loadJsonKeys(join(langDir, 'editor.json'), 'editor')) {
        keys.add(k);
        // Publishing alias compatibility
        keys.add(k.replace(/^editor\./, 'publishing.editor.'));
    }
    keys.add('publishing.content.form.maxSizeHint');
    keys.add('publishing.content.form.minHint');

    const layoutBuilderLocale = join(modulesRoot, 'Layout/locales/builder', `${lang}.json`);
    try {
        for (const k of loadJsonKeys(layoutBuilderLocale, 'builder')) keys.add(k);
    } catch {
        /* optional */
    }

    collectModuleLocaleDirs(modulesRoot).forEach((localesDir) => {
        const rel = localesDir.split('/src/modules/')[1] ?? '';
        if (rel === 'Core/Security/locales') {
            for (const k of loadJsonKeys(join(localesDir, `${lang}.json`), 'system.security')) keys.add(k);
            return;
        }
        if (rel === 'Intelligence/Analytics/locales' || rel === 'Analytics/locales') {
            for (const k of loadJsonKeys(join(localesDir, `${lang}.json`), 'system.analytics')) keys.add(k);
            return;
        }
        if (rel === 'Mail/locales') {
            const mailJson = JSON.parse(readFileSync(join(localesDir, `${lang}.json`), 'utf8'));
            for (const k of flatten(mailJson.mail ?? {}, 'system.mail')) keys.add(k);
            if (typeof mailJson.navigationMenuMail === 'string') {
                keys.add('system.navigation.menu.mail');
            }
            return;
        }
        const namespace = namespaceFromPath(rel);
        for (const k of loadJsonKeys(join(localesDir, `${lang}.json`), namespace)) keys.add(k);
    });

    return keys;
}

function isLocaleKey(key) {
    if (!key || key.includes('${') || key.endsWith('.')) return false;
    const parts = key.split('.');
    if (parts.length < 3) return false;
    return new RegExp(`^(?:${LOCALE_PREFIXES})$`).test(parts[0]);
}

function collectSourceFiles(dir, acc = []) {
    for (const name of readdirSync(dir)) {
        const full = join(dir, name);
        if (!statSync(full).isDirectory()) {
            if (/\.(vue|ts|tsx)$/.test(name)) acc.push(full);
            continue;
        }
        if (['node_modules', 'dist'].includes(name)) continue;
        collectSourceFiles(full, acc);
    }
    return acc;
}

function collectNavigationFiles(dir, acc = []) {
    for (const name of readdirSync(dir)) {
        const full = join(dir, name);
        if (!statSync(full).isDirectory()) continue;
        if (name === 'node_modules' || name === 'dist' || name === 'locales') continue;
        if (name === 'navigation.ts') acc.push(full);
        else collectNavigationFiles(full, acc);
    }
    return acc;
}

function extractFromLine(line, patterns, keys) {
    for (const re of patterns) {
        re.lastIndex = 0;
        let m;
        while ((m = re.exec(line)) !== null) {
            if (isLocaleKey(m[1])) keys.add(m[1]);
        }
    }
}

function extractReferencedKeys(filePath, { full }) {
    const text = readFileSync(filePath, 'utf8');
    const keys = new Set();
    const isCritical =
        filePath.endsWith('navigation.ts') ||
        filePath.endsWith('useBreadcrumbs.ts') ||
        filePath.includes('/router');

    if (!full && !isCritical) return keys;

    for (const line of text.split('\n')) {
        if (line.includes('${')) continue;

        if (full || isCritical) {
            extractFromLine(line, REQUIRED_PATTERNS, keys);
        }
        if (full) {
            extractFromLine(line, STRICT_T_PATTERNS, keys);
        }
    }

    if (filePath.endsWith('useBreadcrumbs.ts')) {
        for (const line of text.split('\n')) {
            if (line.includes('${')) continue;
            KEY_IN_STRING.lastIndex = 0;
            let m;
            while ((m = KEY_IN_STRING.exec(line)) !== null) {
                if (isLocaleKey(m[1])) keys.add(m[1]);
            }
        }
    }

    if (filePath.endsWith('navigation.ts')) {
        for (const line of text.split('\n')) {
            if (line.includes('${')) continue;
            KEY_IN_STRING.lastIndex = 0;
            let m;
            while ((m = KEY_IN_STRING.exec(line)) !== null) {
                if (isLocaleKey(m[1])) keys.add(m[1]);
            }
        }
    }

    return keys;
}

function validateJsonSyntax() {
    const errors = [];

    function walkJson(dir) {
        for (const name of readdirSync(dir)) {
            const full = join(dir, name);
            if (statSync(full).isDirectory()) {
                walkJson(full);
                continue;
            }
            if (!name.endsWith('.json')) continue;
            try {
                const data = JSON.parse(readFileSync(full, 'utf8'));
                validateBraceBalance(full, data, '');
            } catch (e) {
                errors.push(`${relative(root, full)}: ${e.message}`);
            }
        }
    }

    function validateBraceBalance(file, obj, path) {
        for (const [key, value] of Object.entries(obj)) {
            const p = path ? `${path}.${key}` : key;
            if (value && typeof value === 'object' && !Array.isArray(value)) {
                validateBraceBalance(file, value, p);
            } else if (typeof value === 'string') {
                const open = (value.match(/\{/g) || []).length;
                const close = (value.match(/\}/g) || []).length;
                if (open !== close) {
                    errors.push(`${relative(root, file)} @ ${p}: unmatched braces`);
                }
            }
        }
    }

    walkJson(join(localesRoot, 'en'));
    walkJson(join(localesRoot, 'id'));
    walkJson(join(localesRoot, 'su'));
    collectModuleLocaleDirs(modulesRoot).forEach(walkJson);
    return errors;
}

const jsonErrors = validateJsonSyntax();
if (jsonErrors.length) {
    console.error('\nInvalid locale JSON:');
    for (const e of jsonErrors) console.error(`  - ${e}`);
    process.exit(1);
}

const referenced = new Set();
const criticalFiles = [
    join(srcRoot, 'shared/composables/useBreadcrumbs.ts'),
    ...collectNavigationFiles(modulesRoot),
];

for (const file of fullScan ? collectSourceFiles(srcRoot) : criticalFiles) {
    if (file.includes('/locales/')) continue;
    for (const key of extractReferencedKeys(file, { full: fullScan })) referenced.add(key);
}

const enKeys = loadMergedLocaleKeys('en');
const idKeys = loadMergedLocaleKeys('id');
const suKeys = loadMergedLocaleKeys('su');

const onlyInEn = [...enKeys].filter((k) => !idKeys.has(k)).sort();
const onlyInId = [...idKeys].filter((k) => !enKeys.has(k)).sort();
const onlyInEnvsSu = [...enKeys].filter((k) => !suKeys.has(k)).sort();
const onlyInSu = [...suKeys].filter((k) => !enKeys.has(k)).sort();

const missingEn = [...referenced].filter((k) => !enKeys.has(k)).sort();
const missingId = [...referenced].filter((k) => !idKeys.has(k)).sort();
const missingSu = [...referenced].filter((k) => !suKeys.has(k)).sort();

if (onlyInEn.length || onlyInId.length || onlyInEnvsSu.length || onlyInSu.length) {
    console.error(`\nLocale parity: ${enKeys.size} en vs ${idKeys.size} id vs ${suKeys.size} su (must match).`);
    if (onlyInEn.length) {
        console.error(`\nOnly in en vs id (${onlyInEn.length}, show first 40):`);
        for (const k of onlyInEn.slice(0, 40)) console.error(`  - ${k}`);
        if (onlyInEn.length > 40) console.error(`  ... and ${onlyInEn.length - 40} more`);
    }
    if (onlyInId.length) {
        console.error(`\nOnly in id vs en (${onlyInId.length}, show first 40):`);
        for (const k of onlyInId.slice(0, 40)) console.error(`  - ${k}`);
        if (onlyInId.length > 40) console.error(`  ... and ${onlyInId.length - 40} more`);
    }
    if (onlyInEnvsSu.length) {
        console.error(`\nOnly in en vs su (${onlyInEnvsSu.length}, show first 40):`);
        for (const k of onlyInEnvsSu.slice(0, 40)) console.error(`  - ${k}`);
        if (onlyInEnvsSu.length > 40) console.error(`  ... and ${onlyInEnvsSu.length - 40} more`);
    }
    if (onlyInSu.length) {
        console.error(`\nOnly in su vs en (${onlyInSu.length}, show first 40):`);
        for (const k of onlyInSu.slice(0, 40)) console.error(`  - ${k}`);
        if (onlyInSu.length > 40) console.error(`  ... and ${onlyInSu.length - 40} more`);
    }
    process.exit(1);
}

if (missingEn.length || missingId.length || missingSu.length) {
    const mode = fullScan ? 'full' : 'gate';
    if (missingEn.length) {
        console.error(`\nMissing in en (${mode}, ${missingEn.length}):`);
        for (const k of missingEn) console.error(`  - ${k}`);
    }
    if (missingId.length) {
        console.error(`\nMissing in id (${mode}, ${missingId.length}):`);
        for (const k of missingId) console.error(`  - ${k}`);
    }
    if (missingSu.length) {
        console.error(`\nMissing in su (${mode}, ${missingSu.length}):`);
        for (const k of missingSu) console.error(`  - ${k}`);
    }
    process.exit(1);
}

const mode = fullScan ? 'full scan' : 'gate';
console.log(
    `OK [${mode}]: ${referenced.size} gate keys, ${enKeys.size} en + ${idKeys.size} id + ${suKeys.size} su definitions (symmetric), JSON valid.`,
);
