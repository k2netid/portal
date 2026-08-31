import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const ROOT = path.resolve(__dirname, '..');

const janariJsonPath = path.join(ROOT, 'src/modules/Layout/views/themes/janari/theme.json');
const layungSchemaPath = path.join(ROOT, 'src/modules/Layout/views/themes/layung/customizer/schema.settings.json');
const layungThemeJsonPath = path.join(ROOT, 'src/modules/Layout/views/themes/layung/theme.json');

const janari = JSON.parse(fs.readFileSync(janariJsonPath, 'utf8'));
const layungCustomizer = JSON.parse(fs.readFileSync(layungSchemaPath, 'utf8'));

// Extract platform-scoped settings from janari
const platformSettings = {};
for (const [k, v] of Object.entries(janari.settings_schema || {})) {
  if (v && v.scope === 'platform') {
    platformSettings[k] = v;
  }
}

// Extract layung theme settings (excluding _meta)
const themeSettings = {};
for (const [k, v] of Object.entries(layungCustomizer)) {
  if (k === '_meta') continue;
  themeSettings[k] = {
    ...v,
    scope: 'theme',
  };
}

const mergedSchema = {
  ...platformSettings,
  ...themeSettings,
};

const layungManifest = {
  name: 'Layung',
  slug: 'layung',
  version: '1.0.0',
  type: 'frontend',
  description: 'Layung — tema portal penyedia layanan internet (ISP) dan Managed Service Provider (MSP) terdepan. Arsitektur serat optik, jaminan SLA 99.999%, dan layanan NOC 24/7.',
  author: 'Jejakawan Team',
  supports: {
    layung_canvas: true,
  },
  settings_schema: mergedSchema,
};

fs.writeFileSync(layungThemeJsonPath, JSON.stringify(layungManifest, null, 4), 'utf8');
console.log(`merged ${Object.keys(mergedSchema).length} keys → layung theme.json (platform ${Object.keys(platformSettings).length}, theme ${Object.keys(themeSettings).length})`);
