#!/usr/bin/env node
/**
 * Assert product SemVer alignment across package manifests + README.
 * SoT: root package.json "version"
 * Usage: node scripts/check-versions.mjs
 */
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const root = path.resolve(__dirname, '..');

const SEMVER =
  /^(0|[1-9]\d*)\.(0|[1-9]\d*)\.(0|[1-9]\d*)(?:-((?:0|[1-9]\d*|\d*[a-zA-Z-][0-9a-zA-Z-]*)(?:\.(?:0|[1-9]\d*|\d*[a-zA-Z-][0-9a-zA-Z-]*))*))?(?:\+([0-9a-zA-Z-]+(?:\.[0-9a-zA-Z-]+)*))?$/;

function readJson(rel) {
  return JSON.parse(fs.readFileSync(path.join(root, rel), 'utf8'));
}

const errors = [];
const rootPkg = readJson('package.json');
const expected = rootPkg.version;

if (typeof expected !== 'string' || !SEMVER.test(expected)) {
  errors.push(`root package.json version is not SemVer: ${JSON.stringify(expected)}`);
}

const pairs = [
  ['frontend/package.json', readJson('frontend/package.json').version],
  ['backend/composer.json', readJson('backend/composer.json').version],
];

for (const [label, ver] of pairs) {
  if (ver !== expected) {
    errors.push(`${label} version ${JSON.stringify(ver)} !== root ${JSON.stringify(expected)}`);
  }
}

const readme = fs.readFileSync(path.join(root, 'README.md'), 'utf8');
const readmeHit = readme.match(/\|\s*\*\*Versi\*\*\s*\|\s*`([^`]+)`/);
if (!readmeHit) {
  errors.push('README.md missing | **Versi** | `…` | table cell');
} else if (readmeHit[1] !== expected) {
  errors.push(`README.md Versi ${JSON.stringify(readmeHit[1])} !== root ${JSON.stringify(expected)}`);
}

const changelog = fs.readFileSync(path.join(root, 'CHANGELOG.md'), 'utf8');
const section = `## [${expected}]`;
if (!changelog.includes(section)) {
  errors.push(`CHANGELOG.md missing section ${section} (cut release or bump packages to match latest cut)`);
}

// Theme packages should use SemVer in theme.json (spot-check first-party themes)
const themesDir = path.join(root, 'frontend/src/modules/Layout/views/themes');
if (fs.existsSync(themesDir)) {
  for (const slug of fs.readdirSync(themesDir)) {
    const themeJson = path.join(themesDir, slug, 'theme.json');
    if (!fs.existsSync(themeJson)) continue;
    const ver = JSON.parse(fs.readFileSync(themeJson, 'utf8')).version;
    if (typeof ver !== 'string' || !SEMVER.test(ver)) {
      errors.push(`theme ${slug} theme.json version is not SemVer: ${JSON.stringify(ver)}`);
    }
  }
}

if (errors.length) {
  console.error('versions:check FAILED');
  for (const e of errors) console.error(' -', e);
  process.exit(1);
}

console.log(`versions:check OK — product ${expected}`);
