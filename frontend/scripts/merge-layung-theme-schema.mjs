/**
 * Merge platform + layung customizer schema files → layung/theme.json settings_schema.
 * Source of truth: split JSON files (not theme.json).
 *
 * Usage: node scripts/merge-layung-theme-schema.mjs
 */
import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const root = path.join(path.dirname(fileURLToPath(import.meta.url)), '..');
const layungThemeJsonPath = path.join(root, 'src/modules/Layout/views/themes/layung/theme.json');
const platformPath = path.join(root, 'src/modules/Layout/customizer/platform/schema/global.settings.schema.json');
const layungSchemaPath = path.join(root, 'src/modules/Layout/views/themes/layung/customizer/schema.settings.json');

function omitMeta(schema) {
    const out = {};
    for (const [key, def] of Object.entries(schema)) {
        if (key === '_meta') continue;
        out[key] = def;
    }
    return out;
}

const themeDoc = JSON.parse(fs.readFileSync(layungThemeJsonPath, 'utf8'));
const platform = JSON.parse(fs.readFileSync(platformPath, 'utf8'));
const themeSchema = JSON.parse(fs.readFileSync(layungSchemaPath, 'utf8'));

const merged = {
    ...omitMeta(platform),
    ...omitMeta(themeSchema),
};

themeDoc.settings_schema = merged;
themeDoc.customizer = {
    platformSchema: 'Layout/customizer/platform/schema/global.settings.schema.json',
    themeSchema: 'customizer/schema.settings.json',
};

fs.writeFileSync(layungThemeJsonPath, `${JSON.stringify(themeDoc, null, 4)}\n`, 'utf8');

console.log(
    `merged ${Object.keys(merged).length} keys → layung theme.json (platform ${Object.keys(omitMeta(platform)).length}, theme ${Object.keys(omitMeta(themeSchema)).length})`,
);
