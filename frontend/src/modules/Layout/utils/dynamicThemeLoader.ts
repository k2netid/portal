import { defineAsyncComponent, type Component } from 'vue';
import { themePageBaseName } from '@/modules/Layout/utils/themeViewResolver';
import { logger } from '@/shared/utils/logger';

const dynamicComponentCache = new Map<string, Component>();

export interface DynamicThemeContext {
  slug: string;
  bundleUrl: string;
  page: string;
  /** SHA-256 hex from theme.json bundle_checksum (optional integrity check). */
  bundleChecksum?: string | null;
}

/**
 * Load a page component from a prebuilt theme.esm.js bundle (uploaded themes, Fase 3).
 * Bundle must export: resolveComponent(pageBaseName: string) => Component | Promise<Component>
 */
export function loadDynamicThemeComponent(ctx: DynamicThemeContext): Component {
  const cacheKey = `${ctx.slug}|${ctx.page}|${ctx.bundleUrl}|${ctx.bundleChecksum ?? ''}`;
  const cached = dynamicComponentCache.get(cacheKey);
  if (cached) {
    return cached;
  }

  const pageBase = themePageBaseName(ctx.page);

  const asyncComp = defineAsyncComponent({
    loader: async () => {
      if (ctx.bundleChecksum) {
        const ok = await verifyThemeBundleChecksum(ctx.bundleUrl, ctx.bundleChecksum);
        if (!ok) {
          throw new Error(`Theme bundle checksum mismatch for ${ctx.slug}`);
        }
      }
      const mod = await import(/* @vite-ignore */ ctx.bundleUrl) as {
        resolveComponent?: (name: string) => Component | Promise<Component>;
        default?: { resolveComponent?: (name: string) => Component | Promise<Component> };
      };
      const resolve = mod.resolveComponent ?? mod.default?.resolveComponent;
      if (typeof resolve !== 'function') {
        throw new Error(`Theme bundle ${ctx.slug} missing resolveComponent()`);
      }
      const component = await resolve(pageBase);
      if (!component) {
        throw new Error(`Theme bundle ${ctx.slug} has no component for page "${pageBase}"`);
      }
      return component;
    },
    timeout: 15000,
    onError: (error, _retry, fail) => {
      logger.error('[dynamicThemeLoader] Failed to load uploaded theme page', {
        slug: ctx.slug,
        page: ctx.page,
        bundleUrl: ctx.bundleUrl,
        error,
      });
      fail();
    },
  });

  dynamicComponentCache.set(cacheKey, asyncComp);
  return asyncComp;
}

export function isUploadedThemeActive(
  theme: { source?: string; bundle_url?: string | null } | null | undefined,
): boolean {
  if (!theme) return false;
  if (!filterUploadedThemesEnabled()) return false;
  return theme.source === 'uploaded' && typeof theme.bundle_url === 'string' && theme.bundle_url.length > 0;
}

/** Mirrors backend FEATURE_UPLOADED_THEMES — enable via VITE_FEATURE_UPLOADED_THEMES=true */
export function filterUploadedThemesEnabled(): boolean {
  return import.meta.env.VITE_FEATURE_UPLOADED_THEMES === 'true'
    || import.meta.env.VITE_FEATURE_UPLOADED_THEMES === true;
}

/** Verify SHA-256 bundle_checksum from theme manifest (optional). */
export async function verifyThemeBundleChecksum(
  bundleUrl: string,
  expectedChecksum: string,
): Promise<boolean> {
  const expected = expectedChecksum.trim().toLowerCase();
  if (!expected) return true;

  try {
    const res = await fetch(bundleUrl, { credentials: 'same-origin' });
    if (!res.ok) return false;
    const buf = await res.arrayBuffer();
    const hash = await crypto.subtle.digest('SHA-256', buf);
    const hex = Array.from(new Uint8Array(hash))
      .map((b) => b.toString(16).padStart(2, '0'))
      .join('');
    return hex === expected;
  } catch {
    return false;
  }
}
