import type { Component } from 'vue';

/**
 * Fase 6 — uploaded theme bundle entry.
 * Built to storage/.../themes/janari/theme.esm.js via `npm run build:theme:janari`.
 */
const pageModules = import.meta.glob<{ default: Component }>('./pages/*.vue');

function pageLoader(pageBaseName: string): (() => Promise<{ default: Component }>) | undefined {
  const base = pageBaseName.trim().toLowerCase();
  const match = Object.entries(pageModules).find(([path]) => {
    const file = path.split('/').pop()?.replace(/\.vue$/i, '').toLowerCase();
    return file === base;
  });
  return match?.[1] as (() => Promise<{ default: Component }>) | undefined;
}

export async function resolveComponent(pageBaseName: string): Promise<Component> {
  const loader = pageLoader(pageBaseName);
  if (!loader) {
    throw new Error(`[janari] theme bundle: no page "${pageBaseName}"`);
  }
  const mod = await loader();
  return (mod.default ?? mod) as Component;
}

export default { resolveComponent };
