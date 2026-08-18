import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import tailwindcss from '@tailwindcss/vite';
import { dirname, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const __dirname = dirname(fileURLToPath(import.meta.url));
const themeSlug = process.env.THEME_SLUG ?? 'janari';
const outDir =
  process.env.THEME_BUILD_OUT ??
  resolve(__dirname, `../backend/storage/app/public/themes/${themeSlug}`);
const entry = resolve(
  __dirname,
  `src/modules/Content/Layout/views/themes/${themeSlug}/theme.bundle-entry.ts`,
);

export default defineConfig({
  plugins: [vue(), tailwindcss()],
  envDir: resolve(__dirname, '../backend'),
  resolve: {
    alias: {
      '@': resolve(__dirname, 'src'),
    },
  },
  build: {
    outDir,
    emptyOutDir: false,
    cssCodeSplit: false,
    sourcemap: true,
    lib: {
      entry,
      formats: ['es'],
      fileName: () => 'theme.esm.js',
    },
  },
});
