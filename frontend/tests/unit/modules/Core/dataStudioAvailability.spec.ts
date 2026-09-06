import { describe, it, expect } from 'vitest';
import infraRoutes from '@/modules/Core/Infra/router';
import { infraNavigation } from '@/modules/Core/Infra/navigation';

describe('Data Model Studio Extension Gating Configuration', () => {
  it('assigns data-studio extension metadata to all Data Model Studio routes', () => {
    const dataStudioRouteNames = [
      'model-index',
      'model-create',
      'model-edit',
      'dynamic-records-index',
      'dynamic-records-create',
      'dynamic-records-edit',
    ];

    for (const name of dataStudioRouteNames) {
      const route = infraRoutes.find((r) => r.name === name);
      expect(route, `Route ${name} should exist in infraRoutes`).toBeDefined();
      expect(route?.meta?.extension, `Route ${name} should be gated by extension: data-studio`).toBe('data-studio');
    }
  });

  it('keeps pure infra routes ungated by data-studio extension', () => {
    const fileManagerRoute = infraRoutes.find((r) => r.name === 'file-manager');
    expect(fileManagerRoute).toBeDefined();
    expect(fileManagerRoute?.meta?.extension).toBeUndefined();
  });

  it('declares data-studio extension in infraNavigation fallback', () => {
    const studioNav = infraNavigation.find((item) => item.name === 'model-index');
    expect(studioNav).toBeDefined();
    expect(studioNav?.extension).toBe('data-studio');
  });

  it('filters navigation items when extension is inactive', () => {
    const activeExtensions = new Set(['core', 'publishing']);

    const isExtensionActive = (extSlug?: string): boolean => {
      if (!extSlug) return true;
      return activeExtensions.has(extSlug);
    };

    const studioGroup = {
      name: 'Data Model Studio',
      group: 'studio',
      extension: 'data-studio',
      children: [
        {
          name: 'model-index',
          extension: 'data-studio',
        },
      ],
    };

    // When data-studio is not active, the child is filtered out
    const filteredChildren = studioGroup.children.filter((child) =>
      isExtensionActive(child.extension)
    );
    expect(filteredChildren.length).toBe(0);

    // Root is also filtered out
    const isRootActive = isExtensionActive(studioGroup.extension);
    expect(isRootActive).toBe(false);

    // Now activate data-studio
    activeExtensions.add('data-studio');
    expect(isExtensionActive(studioGroup.extension)).toBe(true);
    expect(studioGroup.children.filter((c) => isExtensionActive(c.extension)).length).toBe(1);
  });
});
