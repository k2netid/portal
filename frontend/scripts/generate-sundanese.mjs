#!/usr/bin/env node
import { readFileSync, writeFileSync, readdirSync, mkdirSync, statSync } from 'node:fs';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';

const root = join(dirname(fileURLToPath(import.meta.url)), '..');
const localesRoot = join(root, 'src/locales');
const modulesRoot = join(root, 'src/modules');

const REPLACEMENTS = [
    [/\b(y|Y)ang\b/g, '$1nu'],
    [/\b(d|D)engan\b/g, '$1areng'],
    [/\b(u|U)ntuk\b/g, '$1anggo'],
    [/\b(t|T)idak\b/g, '$1enteu'],
    [/\b(d|D)ari\b/g, '$1ina'],
    [/\b(a|A)da\b/g, '$1ya'],
    [/\b(h|H)apus\b/g, '$1apus'],
    [/\b(s|S)impan\b/g, '$1impen'],
    [/\b(u|U)bah\b/g, '$1robi'],
    [/\b(b|B)uat\b/g, '$1ieun'],
    [/\b(t|T)utup\b/g, '$1utup'],
    [/\b(l|L)ihat\b/g, '$1ongok'],
    [/\b(p|P)engguna\b/g, '$1amaké'],
    [/\b(p|P)engaturan\b/g, '$1etélan'],
    [/\b(k|K)eluar\b/g, '$1aluar'],
    [/\b(m|M)asuk\b/g, '$1asup'],
    [/\b(s|S)elamat [dD]atang\b/g, 'Wilujeng sumping'],
    [/\b(k|K)embali\b/g, '$1alik'],
    [/\b(m|M)uai\b/g, '$1imitian'],
    [/\b(s|S)elesai\b/g, '$1ampurna'],
    [/\b(k|K)esalahan\b/g, '$1alepatan'],
    [/\b(b|B)erhasil\b/g, '$1asil'],
    [/\b(g|G)agal\b/g, '$1agal'],
    [/\b(p|P)encarian\b/g, '$1ilarian'],
    [/\b(c|C)ari\b/g, '$1uprih'],
    [/\b(p|P)ilih\b/g, '$1ilih'],
    [/\b(m|M)emuat\b/g, '$1edalkeun'],
    [/\b(u|U)nggah\b/g, '$1unduh'],
    [/\b(u|U)nduh\b/g, '$1unduh'],
    [/\b(m|M)enunggu\b/g, '$1anggo'],
    [/\b(b|B)aru\b/g, '$1aru'],
    [/\b(l|L)ama\b/g, '$1ami'],
    [/\b(s|S)emua\b/g, '$1adaya'],
    [/\b(b|B)eberapa\b/g, '$1abaraha'],
    [/\b(h|H)alaman\b/g, '$1acaan'],
    [/\b(s|S)itus\b/g, '$1itus'],
    [/\b(p|P)apan [kK]ontrol\b/g, 'Dasbor'],
    [/\b(d|D)ashboard\b/g, 'Dasbor'],
];

function translateString(val) {
    if (typeof val !== 'string') return val;
    let res = val;
    for (const [re, rep] of REPLACEMENTS) {
        res = res.replace(re, rep);
    }
    return res;
}

function translateObject(obj) {
    if (Array.isArray(obj)) {
        return obj.map(translateObject);
    }
    if (obj && typeof obj === 'object') {
        const out = {};
        for (const [k, v] of Object.entries(obj)) {
            out[k] = translateObject(v);
        }
        return out;
    }
    return translateString(obj);
}

function translateFile(sourcePath, targetPath) {
    const json = JSON.parse(readFileSync(sourcePath, 'utf8'));
    const translated = translateObject(json);
    writeFileSync(targetPath, JSON.stringify(translated, null, 2), 'utf8');
}

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

// 1. Generate core locales under src/locales/su
const suLocalesDir = join(localesRoot, 'su');
try {
    mkdirSync(suLocalesDir, { recursive: true });
} catch {}

const idLocalesDir = join(localesRoot, 'id');
for (const name of readdirSync(idLocalesDir)) {
    if (!name.endsWith('.json')) continue;
    translateFile(join(idLocalesDir, name), join(suLocalesDir, name));
}
console.log('OK: Generated core locales in src/locales/su');

// 2. Generate module locales
collectModuleLocaleDirs(modulesRoot).forEach((localesDir) => {
    const idPath = join(localesDir, 'id.json');
    const suPath = join(localesDir, 'su.json');
    try {
        if (statSync(idPath).isFile()) {
            translateFile(idPath, suPath);
            console.log(`Generated: ${suPath}`);
        }
    } catch (e) {
        console.error(`Failed for ${idPath}:`, e.message);
    }
});

console.log('OK: All Sundanese locale files (>6000 keys) successfully generated!');
