import { readdirSync, statSync } from 'node:fs';
import { join } from 'node:path';

const distAssetsDir = join(process.cwd(), 'dist', 'assets');

const parseBudget = (envName, fallbackKb) => {
  const raw = process.env[envName];
  if (!raw) return fallbackKb * 1024;
  const parsed = Number(raw);
  if (!Number.isFinite(parsed) || parsed <= 0) return fallbackKb * 1024;
  return parsed * 1024;
};

const totalJsBudget = parseBudget('PERF_BUDGET_TOTAL_JS_KB', 26000);
const entryJsBudget = parseBudget('PERF_BUDGET_ENTRY_JS_KB', 650);

const files = readdirSync(distAssetsDir).filter((file) => file.endsWith('.js'));
const fileStats = files.map((file) => ({
  file,
  size: statSync(join(distAssetsDir, file)).size,
}));

const totalJsSize = fileStats.reduce((sum, item) => sum + item.size, 0);
const entryCandidates = fileStats
  .filter((item) => item.file.startsWith('index-'))
  .sort((a, b) => b.size - a.size);
const largestEntrySize = entryCandidates[0]?.size ?? 0;

const toKb = (bytes) => Math.round(bytes / 1024);

if (totalJsSize > totalJsBudget || largestEntrySize > entryJsBudget) {
  console.error('[perf-budget] Build exceeds budget');
  console.error(`[perf-budget] total JS: ${toKb(totalJsSize)} KB (budget ${toKb(totalJsBudget)} KB)`);
  console.error(`[perf-budget] largest entry: ${toKb(largestEntrySize)} KB (budget ${toKb(entryJsBudget)} KB)`);
  process.exit(1);
}

console.log(`[perf-budget] OK total JS ${toKb(totalJsSize)} KB / ${toKb(totalJsBudget)} KB`);
console.log(`[perf-budget] OK largest entry ${toKb(largestEntrySize)} KB / ${toKb(entryJsBudget)} KB`);
