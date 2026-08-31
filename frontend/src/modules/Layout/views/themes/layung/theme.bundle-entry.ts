import type { Component } from 'vue';

const pageLoaders = import.meta.glob('./pages/*.vue') as Record<string, () => Promise<{ default: Component }>>;

export async function loadPage(name: string): Promise<Component | null> {
  const normalized = name.replace(/^pages\//, '').replace(/\.vue$/, '');
  const targetKey = `./pages/${normalized}.vue`;
  const loader = pageLoaders[targetKey];
  if (!loader) {
    return null;
  }
  const mod = await loader();
  return mod.default ?? mod;
}

export function listPages(): string[] {
  return Object.keys(pageLoaders).map((key) =>
    key.replace('./pages/', '').replace('.vue', ''),
  );
}

export default {
  slug: 'layung',
  name: 'Layung',
  loadPage,
  listPages,
};
