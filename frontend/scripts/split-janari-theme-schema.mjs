/**
 * Re-classify janari/theme.json settings_schema → platform + theme customizer schema files,
 * then merge back into theme.json. Use only when keys were added on theme.json directly.
 *
 * Day-to-day: edit split files + `npm run theme:schema:merge`
 *
 * Usage: node scripts/split-janari-theme-schema.mjs [--resplit]
 */
import { spawnSync } from 'child_process';
import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const root = path.join(path.dirname(fileURLToPath(import.meta.url)), '..');
const themeJsonPath = path.join(root, 'src/modules/Content/Layout/views/themes/janari/theme.json');
const platformOut = path.join(root, 'src/modules/Content/Layout/customizer/platform/schema/global.settings.schema.json');
const themeOut = path.join(root, 'src/modules/Content/Layout/views/themes/janari/customizer/schema.settings.json');

const PLATFORM_CATEGORIES = new Set([
    'General',
    'Site Info',
    'Organization Info',
    'Social Media',
    'Appearance',
    'Typography',
    'Fonts',
    'Layout',
    'Animations',
    'Footer',
    'Public Pages',
    'Buttons',
]);

/** Legacy school-demo keys — dropped from hub Janari schema (no Vue consumers). */
const REMOVED_KEYS = new Set([
    'majors_badge',
    'majors_title',
    'majors_button_text',
    'stats_title',
    'page_academic_title',
    'page_academic_subtitle',
    'page_academic_hero',
    'page_vocation_title',
    'page_vocation_subtitle',
    'page_vocation_hero',
]);

/** Janari canvas / preset fields (stay in theme package even if category is Colors). */
const THEME_ONLY_KEYS = new Set([
    'color_preset',
    'color_intensity',
    'monochrome_variant',
    'monochrome_texture',
    'monochrome_texture_strength',
]);

const themeDoc = JSON.parse(fs.readFileSync(themeJsonPath, 'utf8'));
const schema = themeDoc.settings_schema || {};
for (const key of REMOVED_KEYS) {
    delete schema[key];
}

const platform = {
    _meta: {
        scope: 'platform',
        description: 'Host-wide settings for any active public theme on the Jejakawan hub.',
    },
};
const themeSchema = {
    _meta: {
        scope: 'theme',
        slug: 'janari',
        description: 'Janari-specific customizer fields (sections, canvas, demo pages).',
    },
};

for (const [key, def] of Object.entries(schema)) {
    const entry = { ...def, scope: def.scope };
    const category = def.category || 'General';
    const isPlatform =
        !THEME_ONLY_KEYS.has(key) &&
        (PLATFORM_CATEGORIES.has(category) || entry.scope === 'platform');

    if (isPlatform) {
        entry.scope = 'platform';
        platform[key] = entry;
    } else {
        entry.scope = 'theme';
        themeSchema[key] = entry;
    }
}

fs.writeFileSync(platformOut, `${JSON.stringify(platform, null, 4)}\n`);
fs.writeFileSync(themeOut, `${JSON.stringify(themeSchema, null, 4)}\n`);

const pCount = Object.keys(platform).filter((k) => k !== '_meta').length;
const tCount = Object.keys(themeSchema).filter((k) => k !== '_meta').length;
console.log(`resplit: platform keys ${pCount}, theme keys ${tCount}`);

const mergeScript = path.join(path.dirname(fileURLToPath(import.meta.url)), 'merge-janari-theme-schema.mjs');
const merge = spawnSync(process.execPath, [mergeScript], { stdio: 'inherit', cwd: root });
if (merge.status !== 0) {
    process.exit(merge.status ?? 1);
}
