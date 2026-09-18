#!/usr/bin/env node
/**
 * Fail when runtime paths under a module/theme/extension change without bumping
 * SemVer SoT + touching that pack's CHANGELOG (hard allowlist).
 *
 * Base ref: MODULES_VERSIONS_BASE | GITHUB_BASE_REF | origin/main | main
 * Usage: node scripts/check-module-versions.mjs
 *
 * @see docs/guides/release-and-versioning.md
 */
import { execFileSync } from 'node:child_process';
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const root = path.resolve(__dirname, '..');

const SEMVER =
  /^(0|[1-9]\d*)\.(0|[1-9]\d*)\.(0|[1-9]\d*)(?:-((?:0|[1-9]\d*|\d*[a-zA-Z-][0-9a-zA-Z-]*)(?:\.(?:0|[1-9]\d*|\d*[a-zA-Z-][0-9a-zA-Z-]*))*))?(?:\+([0-9a-zA-Z-]+(?:\.[0-9a-zA-Z-]+)*))?$/;

function git(args, { allowFail = false } = {}) {
  try {
    return execFileSync('git', args, {
      cwd: root,
      encoding: 'utf8',
      stdio: ['ignore', 'pipe', 'pipe'],
    }).trim();
  } catch (e) {
    if (allowFail) return '';
    throw e;
  }
}

function resolveBase() {
  if (process.env.MODULES_VERSIONS_BASE) return process.env.MODULES_VERSIONS_BASE;
  if (process.env.GITHUB_BASE_REF) return `origin/${process.env.GITHUB_BASE_REF}`;
  const currentBranch = git(['rev-parse', '--abbrev-ref', 'HEAD'], { allowFail: true });
  if (currentBranch && currentBranch !== 'HEAD' && currentBranch !== 'main') {
    const tracking = `origin/${currentBranch}`;
    if (git(['rev-parse', '--verify', tracking], { allowFail: true })) {
      return tracking;
    }
  }
  for (const cand of ['origin/main', 'main']) {
    const ok = git(['rev-parse', '--verify', cand], { allowFail: true });
    if (ok) return cand;
  }
  return 'HEAD~1';
}

function listChangedFiles(base) {
  const sets = [
    git(['diff', '--name-only', `${base}...HEAD`], { allowFail: true }),
    git(['diff', '--name-only'], { allowFail: true }),
    git(['diff', '--name-only', '--cached'], { allowFail: true }),
    git(['ls-files', '--others', '--exclude-standard'], { allowFail: true }),
  ];
  const out = new Set();
  for (const chunk of sets) {
    for (const line of chunk.split('\n')) {
      const f = line.trim();
      if (f) out.add(f.replace(/\\/g, '/'));
    }
  }
  return [...out];
}

function isAllowlisted(rel) {
  if (rel.startsWith('docs/')) return true;
  const base = path.posix.basename(rel);
  if (base === 'CHANGELOG.md' || base === 'README.md') return true;
  return false;
}

function isCoreChangelog(rel) {
  return (
    /^backend\/Modules\/Core\/(CHANGELOG\.md|(System|Infra|Security)\/CHANGELOG\.md)$/.test(rel) ||
    /^frontend\/src\/modules\/Core\/(CHANGELOG\.md|(System|Infra|Security)\/CHANGELOG\.md)$/.test(rel)
  );
}

/**
 * @returns {{ kind: 'module'|'theme'|'extension', id: string, versionFile: string, changelogMatcher: (f: string) => boolean } | null}
 */
function classify(rel) {
  const extDoc = rel.match(/^docs\/extensions\/([^/]+)\.md$/);
  if (extDoc) {
    const slug = extDoc[1];
    return {
      kind: 'extension',
      id: slug,
      versionFile: `backend/extensions/${slug}/manifest.json`,
      changelogMatcher: () => true,
      docsOnlyCredit: true,
    };
  }

  if (rel.startsWith('docs/')) return null;

  const theme = rel.match(
    /^frontend\/src\/modules\/Layout\/views\/themes\/([^/]+)\/(.+)$/,
  );
  if (theme) {
    const slug = theme[1];
    return {
      kind: 'theme',
      id: slug,
      versionFile: `frontend/src/modules/Layout/views/themes/${slug}/theme.json`,
      changelogMatcher: (f) =>
        f === `frontend/src/modules/Layout/views/themes/${slug}/CHANGELOG.md`,
    };
  }

  const ext = rel.match(/^backend\/extensions\/([^/]+)\/(.+)$/);
  if (ext) {
    const slug = ext[1];
    return {
      kind: 'extension',
      id: slug,
      versionFile: `backend/extensions/${slug}/manifest.json`,
      changelogMatcher: (f) =>
        f === `backend/extensions/${slug}/CHANGELOG.md` ||
        f === `docs/extensions/${slug}.md`,
    };
  }

  const be = rel.match(/^backend\/Modules\/([^/]+)\/(.+)$/);
  if (be) {
    const name = be[1];
    return {
      kind: 'module',
      id: name,
      versionFile: `backend/Modules/${name}/manifest.json`,
      changelogMatcher: (f) => {
        if (name === 'Core') return isCoreChangelog(f);
        return (
          f === `backend/Modules/${name}/CHANGELOG.md` ||
          f === `frontend/src/modules/${name}/CHANGELOG.md`
        );
      },
    };
  }

  const fe = rel.match(/^frontend\/src\/modules\/([^/]+)\/(.+)$/);
  if (fe) {
    const name = fe[1];
    // Theme paths already handled; Layout theme host still Layout module
    return {
      kind: 'module',
      id: name,
      versionFile: `backend/Modules/${name}/manifest.json`,
      changelogMatcher: (f) => {
        if (name === 'Core') return isCoreChangelog(f);
        return (
          f === `backend/Modules/${name}/CHANGELOG.md` ||
          f === `frontend/src/modules/${name}/CHANGELOG.md`
        );
      },
    };
  }

  return null;
}

function readJsonAt(rel, ref) {
  if (ref === 'WORKTREE') {
    const full = path.join(root, rel);
    if (!fs.existsSync(full)) return null;
    try {
      return JSON.parse(fs.readFileSync(full, 'utf8'));
    } catch {
      return null;
    }
  }
  const raw = git(['show', `${ref}:${rel}`], { allowFail: true });
  if (!raw) return null;
  try {
    return JSON.parse(raw);
  } catch {
    return null;
  }
}

function versionOf(json) {
  if (!json || typeof json.version !== 'string') return null;
  return json.version;
}

const base = resolveBase();
const files = listChangedFiles(base);

if (files.length === 0) {
  console.log(`modules:versions:check OK — no changes vs ${base}`);
  process.exit(0);
}

/** @type {Map<string, { meta: ReturnType<typeof classify>, runtime: boolean, changelog: boolean }>} */
const packs = new Map();

function touch(key, meta, { runtime = false, changelog = false } = {}) {
  let row = packs.get(key);
  if (!row) {
    row = { meta, runtime: false, changelog: false };
    packs.set(key, row);
  }
  if (runtime) row.runtime = true;
  if (changelog) row.changelog = true;
}

for (const rel of files) {
  const meta = classify(rel);
  if (!meta) continue;
  const key = `${meta.kind}:${meta.id}`;

  if (meta.docsOnlyCredit) {
    touch(key, meta, { changelog: true });
    continue;
  }

  if (meta.changelogMatcher(rel) || (isAllowlisted(rel) && path.posix.basename(rel) === 'CHANGELOG.md')) {
    touch(key, meta, { changelog: true });
  }

  if (isAllowlisted(rel)) {
    // allowlist never forces runtime bump by itself
    continue;
  }

  // Changing version file alone is not "runtime feature" but still a version touch —
  // only force checks when other runtime files also change.
  if (rel === meta.versionFile) {
    continue;
  }

  touch(key, meta, { runtime: true });
}

const errors = [];

for (const [key, row] of packs) {
  if (!row.runtime) continue;

  const { meta } = row;
  const current = versionOf(readJsonAt(meta.versionFile, 'WORKTREE'));
  const previous = versionOf(readJsonAt(meta.versionFile, base));

  if (!current || !SEMVER.test(current)) {
    errors.push(
      `${key}: missing/invalid SemVer in ${meta.versionFile} (got ${JSON.stringify(current)})`,
    );
    continue;
  }

  if (previous !== null) {
    if (!SEMVER.test(previous)) {
      errors.push(`${key}: base ${meta.versionFile} version not SemVer: ${JSON.stringify(previous)}`);
    } else if (current === previous) {
      errors.push(
        `${key}: runtime paths changed but ${meta.versionFile} still ${current} (bump SemVer vs ${base})`,
      );
    }
  }

  if (!row.changelog) {
    errors.push(
      `${key}: runtime paths changed but no CHANGELOG update for this pack/theme (touch [Unreleased])`,
    );
  }
}

if (errors.length) {
  console.error(`modules:versions:check FAILED (base=${base})`);
  for (const e of errors) console.error(' -', e);
  console.error('See docs/guides/release-and-versioning.md (Module / theme SemVer).');
  process.exit(1);
}

const runtimePacks = [...packs.entries()].filter(([, r]) => r.runtime).map(([k]) => k);
console.log(
  `modules:versions:check OK — base=${base}` +
    (runtimePacks.length ? `; runtime packs checked: ${runtimePacks.join(', ')}` : '; no runtime pack diffs'),
);
