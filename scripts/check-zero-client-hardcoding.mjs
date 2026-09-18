#!/usr/bin/env node
/**
 * Automated CI Gate: Zero Client Hardcoding Enforcement
 *
 * Ensures that upstream (ja-core_engine) theme packages and demo seeders
 * remain 100% generic and platform-agnostic, with ZERO hardcoded client-specific
 * entities (e.g. PT Kirana Karina Network / K2NET, SMK Negeri 6 Bandung,
 * client-specific domains, hotlines, or emails).
 *
 * Scanned directories:
 * - frontend/src/modules/Layout/views/themes/
 * - backend/Modules/Layout/app/Database/Seeders/Themes/
 * - backend/theme-packs/
 *
 * Usage: node scripts/check-zero-client-hardcoding.mjs
 */

import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const ROOT = path.resolve(__dirname, '..');

const SCAN_DIRS = [
  'frontend/src/modules/Layout/views/themes',
  'backend/Modules/Layout/app/Database/Seeders/Themes',
  'backend/theme-packs',
];

const FORBIDDEN_RULES = [
  {
    name: 'PT. Kirana Karina Network (K2NET legal entity)',
    pattern: /PT\.?\s*Kirana\s*Karina\s*Network/i,
  },
  {
    name: 'K2NET brand identifier',
    pattern: /\bK2NET\b/,
  },
  {
    name: 'SMK Negeri 6 Bandung school entity',
    pattern: /SMK\s*Negeri\s*6\b/i,
  },
  {
    name: 'SMKN 6 school slug',
    pattern: /\bsmkn6\b/i,
  },
  {
    name: 'Client domain: smkn6.sch.id',
    pattern: /\bsmkn6\.sch\.id\b/i,
  },
  {
    name: 'Client domain: k2net.id',
    pattern: /\bk2net\.id\b/i,
  },
  {
    name: 'Client contact email: halo@jejakawan.com',
    pattern: /\bhalo@jejakawan\.com\b/i,
  },
  {
    name: 'Client hotline WhatsApp: 6281122334455',
    pattern: /\b6281122334455\b/,
  },
];

const TEXT_EXTENSIONS = new Set([
  '.json',
  '.vue',
  '.ts',
  '.js',
  '.mjs',
  '.php',
  '.md',
  '.html',
  '.css',
  '.scss',
]);

function walkDirectory(dir, fileList = []) {
  if (!fs.existsSync(dir)) return fileList;

  const entries = fs.readdirSync(dir, { withFileTypes: true });
  for (const entry of entries) {
    const fullPath = path.join(dir, entry.name);
    if (entry.isDirectory()) {
      if (entry.name !== 'node_modules' && entry.name !== '.git') {
        walkDirectory(fullPath, fileList);
      }
    } else if (entry.isFile()) {
      const ext = path.extname(entry.name).toLowerCase();
      if (TEXT_EXTENSIONS.has(ext)) {
        fileList.push(fullPath);
      }
    }
  }
  return fileList;
}

let violations = 0;

for (const relDir of SCAN_DIRS) {
  const fullDir = path.join(ROOT, relDir);
  const files = walkDirectory(fullDir);

  for (const filePath of files) {
    const relativePath = path.relative(ROOT, filePath).replace(/\\/g, '/');
    const content = fs.readFileSync(filePath, 'utf8');
    const lines = content.split('\n');

    for (let i = 0; i < lines.length; i++) {
      const line = lines[i];
      for (const rule of FORBIDDEN_RULES) {
        if (rule.pattern.test(line)) {
          console.error(
            `\x1b[31m[ZERO-CLIENT-HARDCODING VIOLATION]\x1b[0m ${relativePath}:${i + 1}`,
          );
          console.error(`  Rule: ${rule.name}`);
          console.error(`  Line: ${line.trim()}`);
          violations++;
        }
      }
    }
  }
}

if (violations > 0) {
  console.error(
    `\n\x1b[31mFAILED:\x1b[0m ${violations} client-specific hardcoding violation(s) detected in upstream codebase.`,
  );
  console.error(
    'Upstream (ja-core_engine) must remain 100% generic. Client identities belong exclusively in downstream deployment seeders.\n',
  );
  process.exit(1);
}

console.log(
  '\x1b[32mcheck-zero-client-hardcoding:\x1b[0m OK — 0 client-specific entities found across upstream theme packages and demo seeders.',
);
