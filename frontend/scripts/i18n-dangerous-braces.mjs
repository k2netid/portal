#!/usr/bin/env node
/**
 * CI guard: locale strings with raw `{` / `}` that break vue-i18n (not ICU placeholders).
 * Allowed: `{name}`, `{count, plural, ...}`, `{{` escaped, or no braces.
 */
import { readFileSync, readdirSync, statSync } from 'node:fs';
import { dirname, join, relative } from 'node:path';
import { fileURLToPath } from 'node:url';

const root = join(dirname(fileURLToPath(import.meta.url)), '..');
const localesRoots = [
  join(root, 'src/locales'),
  join(root, 'src/modules'),
];

const PLACEHOLDER = /^\{[a-zA-Z_][a-zA-Z0-9_]*(?:,[^}]*)?\}$/;
const LITERAL = /^\{'[^']*'\}$/;
const findings = [];

function flatten(obj, prefix = '') {
  const out = [];
  for (const [key, value] of Object.entries(obj)) {
    const path = prefix ? `${prefix}.${key}` : key;
    if (value && typeof value === 'object' && !Array.isArray(value)) {
      out.push(...flatten(value, path));
    } else if (typeof value === 'string') {
      out.push({ path, value });
    }
  }
  return out;
}

function scanFile(filePath) {
  let json;
  try {
    json = JSON.parse(readFileSync(filePath, 'utf8'));
  } catch (e) {
    findings.push({ file: filePath, path: '(parse)', value: String(e.message) });
    return;
  }
  for (const { path, value } of flatten(json)) {
    if (path === 'jsonObject' && filePath.includes('placeholders.json')) continue;
    if (!value.includes('{') && !value.includes('}')) continue;
    if (value.includes('{{')) {
      findings.push({ file: filePath, path, value, token: '{{' });
      continue;
    }
    const parts = value.split(/(\{[^}]*\})/);
    for (const part of parts) {
      if (!part.includes('{')) continue;
      const matches = part.match(/\{[^}]*\}/g) ?? [];
      for (const m of matches) {
        if (!PLACEHOLDER.test(m) && !LITERAL.test(m)) {
          findings.push({ file: filePath, path, value, token: m });
          break;
        }
      }
    }
  }
}

function walk(dir) {
  for (const name of readdirSync(dir)) {
    const full = join(dir, name);
    const st = statSync(full);
    if (st.isDirectory()) {
      if (name === 'node_modules' || name === 'dist') continue;
      walk(full);
      continue;
    }
    if (name.endsWith('.json') && (full.includes('/locales/') || full.endsWith('/en.json') || full.endsWith('/id.json'))) {
      scanFile(full);
    }
  }
}

for (const base of localesRoots) {
  if (statSync(base).isDirectory()) walk(base);
}

if (findings.length > 0) {
  console.error(`FAIL [i18n-dangerous-braces]: ${findings.length} suspicious locale string(s)`);
  for (const f of findings.slice(0, 30)) {
    console.error(`  - ${relative(root, f.file)} :: ${f.path} :: token ${f.token ?? '?'} :: ${f.value.slice(0, 80)}`);
  }
  if (findings.length > 30) console.error(`  ... and ${findings.length - 30} more`);
  process.exit(1);
}

console.log('OK [i18n-dangerous-braces]: no dangerous raw braces in locale JSON');
