import type { Component } from 'vue';

/**
 * Sarangenge theme bundle entry.
 * Resolves theme pages dynamically for uploaded / standalone packages.
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
    throw new Error(`[sarangenge] theme bundle: no page "${pageBaseName}"`);
  }
  const mod = await loader();
  return (mod.default ?? mod) as Component;
}

export default { resolveComponent };
