#!/usr/bin/env node
/**
 * Check relative markdown links under docs/ (and optional extra roots).
 * Usage: node scripts/check-docs-links.mjs [rootDir...]
 * Default root: docs/
 */
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const repoRoot = path.resolve(__dirname, '..');
const roots = process.argv.slice(2).length
  ? process.argv.slice(2).map((p) => path.resolve(repoRoot, p))
  : [path.join(repoRoot, 'docs')];

const LINK_RE = /\[([^\]]*)\]\(([^)]+)\)/g;
const SKIP_HREF = /^(https?:|mailto:|file:|#)/i;

function walkMarkdown(dir, out = []) {
  if (!fs.existsSync(dir)) return out;
  for (const ent of fs.readdirSync(dir, { withFileTypes: true })) {
    if (ent.name === 'node_modules' || ent.name === '.git') continue;
    const full = path.join(dir, ent.name);
    if (ent.isDirectory()) walkMarkdown(full, out);
    else if (ent.isFile() && ent.name.endsWith('.md')) out.push(full);
  }
  return out;
}

const broken = [];
let checked = 0;

for (const root of roots) {
  for (const file of walkMarkdown(root)) {
    const text = fs.readFileSync(file, 'utf8');
    let m;
    LINK_RE.lastIndex = 0;
    while ((m = LINK_RE.exec(text)) !== null) {
      const href = m[2].trim();
      if (SKIP_HREF.test(href)) continue;
      // ignore bare sibling-repo prose paths without ./
      if (!href.startsWith('.') && !href.startsWith('/')) continue;
      const bare = href.split('#')[0].split('?')[0];
      if (!bare) continue;
      checked += 1;
      const target = path.resolve(path.dirname(file), bare);
      if (!fs.existsSync(target)) {
        broken.push({
          file: path.relative(repoRoot, file),
          href,
          target: path.relative(repoRoot, target),
        });
      }
    }
  }
}

if (broken.length) {
  console.error(`docs link check FAILED: ${broken.length} broken / ${checked} relative links`);
  for (const b of broken) {
    console.error(`  ${b.file} → ${b.href} (missing ${b.target})`);
  }
  process.exit(1);
}

console.log(`docs link check OK: ${checked} relative links across ${roots.map((r) => path.relative(repoRoot, r) || '.').join(', ')}`);
