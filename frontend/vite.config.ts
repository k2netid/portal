import { defineConfig } from 'vitest/config'
import vue from '@vitejs/plugin-vue'
import tailwindcss from '@tailwindcss/vite'
import { fileURLToPath, URL } from 'node:url'
import { resolve, dirname } from 'node:path'
import { visualizer } from 'rollup-plugin-visualizer'
import sri from 'vite-plugin-sri'
import { spaFallbackPlugin } from './vite/spaFallback'

const __dirname = dirname(fileURLToPath(import.meta.url))
const appBuildId = process.env.VITE_BUILD_ID ?? `${Date.now()}`

/** Laravel origin for /api and /sanctum during vite dev (E2E sets VITE_DEV_API_PROXY). */
const devApiProxyTarget =
  process.env.VITE_DEV_API_PROXY?.trim() ||
  process.env.E2E_API_PROXY_TARGET?.trim() ||
  'http://127.0.0.1:8081'

const devServerPort = Number(process.env.VITE_DEV_PORT || 5173)

// https://vite.dev/config/
export default defineConfig({
  define: {
    __APP_BUILD_ID__: JSON.stringify(appBuildId),
  },
  envDir: '../backend',
  test: {
    environment: 'happy-dom',
    globals: true,
    setupFiles: ['./tests/setup/vitest.setup.ts'],
    exclude: ['node_modules', 'tests/e2e/**'],
    coverage: {
      provider: 'v8',
      reporter: ['text', 'json', 'html'],
      exclude: ['src/lib/utils.ts', 'src/main-console.ts', 'src/main-public.ts', 'src/main-shared.ts', 'src/router/**', 'src/types/**', 'src/vite-env.d.ts'],
    },
  },
  plugins: [
    vue(),
    tailwindcss(),
    spaFallbackPlugin(),
    sri(),
    visualizer({
      filename: './dist/stats.html',
      open: false,
      gzipSize: true,
      brotliSize: true,
    }),
  ],
  resolve: {
    alias: {
      '@/components/builder': fileURLToPath(new URL('./src/modules/Content/Layout/components/builder', import.meta.url)),
      '@/components/content-renderer': fileURLToPath(new URL('./src/modules/Content/Layout/components/content-renderer', import.meta.url)),
      '@/composables': fileURLToPath(new URL('./src/shared/composables', import.meta.url)),
      '@': fileURLToPath(new URL('./src', import.meta.url))
    }
  },
  build: {
    modulePreload: false,
    // Use esbuild CSS minifier to keep Tailwind v4 directives in scoped blocks
    // from being reported as unknown by lightningcss during production builds.
    cssMinify: 'esbuild',
    rollupOptions: {
      input: {
        index: resolve(__dirname, 'index.html'),
        console: resolve(__dirname, 'console.html'),
      },
      output: {
        chunkFileNames: 'assets/[hash].js',
        entryFileNames: 'assets/[name]-[hash].js',
        assetFileNames: 'assets/[name]-[hash].[ext]',
        manualChunks: (id) => {
          if (id.includes('/src/modules/Crm/')) {
            return 'mod-crm';
          }
          if (id.includes('/src/modules/Operational/Accounting/')) {
            return 'mod-accounting';
          }
          if (id.includes('/src/modules/Core/Security/')) {
            return 'mod-security';
          }
          if (id.includes('/src/modules/Content/Publishing/')) {
            return 'mod-content-publishing';
          }
          if (id.includes('/src/modules/Content/Media/')) {
            return 'mod-content-media';
          }
          if (id.includes('/src/modules/Content/Forms/')) {
            return 'mod-content-forms';
          }
          if (id.includes('/src/modules/Content/Layout/')) {
            return 'mod-content-layout';
          }
          if (id.includes('/src/modules/Content/Library/')) {
            return 'mod-content-library';
          }
          if (id.includes('/src/modules/Content/Studio/')) {
            return 'mod-content-studio';
          }
          if (id.includes('/src/modules/Content/')) {
            return 'mod-content-shared';
          }
          if (id.includes('/src/modules/Intelligence/')) {
            return 'mod-intelligence';
          }
          if (!id.includes('node_modules')) {
            return;
          }

          // Individual heavy icons to avoid one massive icons chunk
          if (id.includes('lucide-vue-next')) {
            return 'vendor-icons';
          }

          // Core Vue & Router
          if (
            id.includes('node_modules/vue/') ||
            id.includes('node_modules/vue-router/') ||
            id.includes('node_modules/@vue/runtime')
          ) {
            return 'vendor-vue-core';
          }

          // State management
          if (id.includes('pinia')) {
            return 'vendor-pinia';
          }

          // Split heavy rich-text stack
          if (id.includes('@tiptap') || id.includes('prosemirror')) {
            return 'vendor-tiptap';
          }

          if (id.includes('highlight.js') || id.includes('lowlight')) {
            return 'vendor-syntax-highlighter';
          }

          // UI Frameworks
          if (id.includes('radix-vue') || id.includes('reka-ui')) {
            return 'vendor-ui-core';
          }

          // Form & Validation
          if (id.includes('zod') || id.includes('vee-validate')) {
            return 'vendor-forms';
          }

          // Utilities
          if (id.includes('axios') || id.includes('dayjs') || id.includes('lodash')) {
            return 'vendor-utils-base';
          }

          if (id.includes('gsap')) {
            return 'vendor-animation';
          }

          if (id.includes('@fullcalendar')) {
            return 'vendor-ui-calendar';
          }

          if (id.includes('chart.js') || id.includes('vue-chartjs')) {
            return 'vendor-ui-charts';
          }

          // Fallback vendor chunk
          return 'vendor-misc';
        },
      },
    },
    // Current largest generated chunk is around ~1.65MB; keep warning threshold
    // above that so build output only flags newly larger regressions.
    chunkSizeWarningLimit: 1800,
    sourcemap: false,
  },
  server: {
    host: process.env.VITE_DEV_HOST || '0.0.0.0',
    port: devServerPort,
    strictPort: process.env.VITE_DEV_PORT != null && process.env.VITE_DEV_PORT !== '',
    proxy: {
      '/api': {
        target: devApiProxyTarget,
        changeOrigin: true,
        secure: false,
      },
      '/sanctum': {
        target: devApiProxyTarget,
        changeOrigin: true,
        secure: false,
      },
      '/passkeys': {
        target: devApiProxyTarget,
        changeOrigin: true,
        secure: false,
      },
      '/user/passkeys': {
        target: devApiProxyTarget,
        changeOrigin: true,
        secure: false,
      },
    },
  }
})
