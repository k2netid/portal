import { gzipSync } from 'node:zlib';
import { readFileSync } from 'node:fs';
import { join, relative, resolve, sep } from 'node:path';

const distDir = resolve(process.cwd(), 'dist');
const manifestPath = join(distDir, '.vite', 'manifest.json');
const manifest = JSON.parse(readFileSync(manifestPath, 'utf8'));
const manifestEntriesByFile = new Map(
  Object.entries(manifest).map(([key, entry]) => [entry.file, [key, entry]])
);

const parseBudget = (envName, fallbackKb) => {
  const raw = process.env[envName];
  if (!raw) return fallbackKb * 1024;
  const parsed = Number(raw);
  if (!Number.isFinite(parsed) || parsed <= 0) {
    throw new Error(`[perf-budget] ${envName} must be a positive number of KB`);
  }
  return parsed * 1024;
};

const jsGzipBudget = parseBudget('PERF_BUDGET_INITIAL_JS_GZIP_KB', 200);
const landingJsGzipBudget = parseBudget('PERF_BUDGET_LANDING_JS_GZIP_KB', 69);
const cssGzipBudget = parseBudget('PERF_BUDGET_INITIAL_CSS_GZIP_KB', 100);
const shells = ['index.html', 'public.html', 'landing.html'];
const toKb = (bytes) => Math.round(bytes / 1024);

const resolveBuildFile = (file) => {
  const absolutePath = resolve(distDir, file);
  const relativePath = relative(distDir, absolutePath);
  if (relativePath.startsWith(`..${sep}`) || relativePath === '..') {
    throw new Error(`[perf-budget] Build file escapes dist directory: ${file}`);
  }
  return absolutePath;
};

const collectInitialResources = (entryKey) => {
  const visited = new Set();
  const jsFiles = new Set();
  const cssFiles = new Set();

  const visit = (key) => {
    if (visited.has(key)) return;
    visited.add(key);

    const entry = manifest[key];
    if (!entry) throw new Error(`[perf-budget] Missing manifest entry: ${key}`);
    if (entry.file.endsWith('.js')) jsFiles.add(entry.file);
    for (const cssFile of entry.css ?? []) cssFiles.add(cssFile);
    for (const importedKey of entry.imports ?? []) visit(importedKey);
  };

  visit(entryKey);
  return { jsFiles, cssFiles };
};

const measure = (files) => {
  let rawBytes = 0;
  let gzipBytes = 0;
  for (const file of files) {
    const contents = readFileSync(resolveBuildFile(file));
    rawBytes += contents.byteLength;
    gzipBytes += gzipSync(contents).byteLength;
  }
  return { rawBytes, gzipBytes };
};

const results = shells.map((shell) => {
  const html = readFileSync(join(distDir, shell), 'utf8');
  const scriptPath = html.match(/<script\b[^>]*\btype=["']module["'][^>]*\bsrc=["']([^"']+)["']/i)?.[1];
  if (!scriptPath) throw new Error(`[perf-budget] No module entry found in ${shell}`);

  const scriptFile = new URL(scriptPath, 'http://localhost').pathname.replace(/^\/+/, '');
  const entry = manifestEntriesByFile.get(scriptFile);
  if (!entry) throw new Error(`[perf-budget] Module entry is missing from the manifest: ${scriptFile}`);

  const resources = collectInitialResources(entry[0]);
  const js = measure(resources.jsFiles);
  const css = measure(resources.cssFiles);

  return { shell, js, css };
});

let failed = false;
for (const { shell, js, css } of results) {
  const shellJsGzipBudget = shell === 'landing.html' ? landingJsGzipBudget : jsGzipBudget;
  const jsOverBudget = js.gzipBytes > shellJsGzipBudget;
  const cssOverBudget = css.gzipBytes > cssGzipBudget;
  failed ||= jsOverBudget || cssOverBudget;

  console.log(
    `[perf-budget] ${shell} JS: ${toKb(js.rawBytes)} KB raw / ${toKb(js.gzipBytes)} KB gzip ` +
    `(gzip budget ${toKb(shellJsGzipBudget)} KB)`,
  );
  console.log(
    `[perf-budget] ${shell} CSS: ${toKb(css.rawBytes)} KB raw / ${toKb(css.gzipBytes)} KB gzip ` +
    `(gzip budget ${toKb(cssGzipBudget)} KB)`,
  );
}

if (failed) {
  console.error('[perf-budget] One or more initial-load gzip budgets were exceeded');
  process.exit(1);
}
