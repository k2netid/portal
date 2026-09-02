import { describe, expect, it } from 'vitest';
import {
  bindingComponentAllowsDesignMode,
  componentManifestCategories,
  resolveBindingComponentMode,
} from '@/modules/Layout/customizer/shell/resolveBindingComponentMode';
import { resolvePreviewNavItemId } from '@/modules/Layout/customizer/preview/getThemePreviewTargets';
import layungTargets from '@/modules/Layout/views/themes/layung/customizer/preview.targets.json';

describe('resolveBindingComponentMode', () => {
  it('opens Design for Layung-style components with settings but no CMS slots', () => {
    expect(resolveBindingComponentMode({ slots: [] })).toBe('design');
    expect(resolveBindingComponentMode({ slots: [] }, 'bindings')).toBe('design');
  });

  it('opens Design when preview requests design even if CMS slots exist', () => {
    expect(resolveBindingComponentMode({ slots: [{ id: 'news' }] }, 'design')).toBe('design');
  });

  it('opens Content when the component has data slots', () => {
    expect(resolveBindingComponentMode({ slots: [{ id: 'news' }] })).toBe('bindings');
    expect(resolveBindingComponentMode({ slots: [{ id: 'news' }] }, 'design')).toBe('design');
    expect(resolveBindingComponentMode({ slots: [{ id: 'news' }] }, 'bindings')).toBe('bindings');
  });

  it('keeps Design compatible when settings exist', () => {
    expect(
      bindingComponentAllowsDesignMode({
        bindingComponent: { slots: [] },
        manifestSections: [{ settings: [{ key: 'hero_title' }] }],
      }),
    ).toBe(true);
  });
});

describe('componentManifestCategories', () => {
  it('prefers the categories array and falls back to a single category', () => {
    expect(componentManifestCategories({ manifestCategories: ['Hero Section', 'ISP Info'] })).toEqual([
      'Hero Section',
      'ISP Info',
    ]);
    expect(componentManifestCategories({ manifestCategory: 'Footer' })).toEqual(['Footer']);
  });
});

describe('Layung preview click targets', () => {
  it('maps the HERO badge to the hero component nav item', () => {
    const targets = layungTargets.targets;
    expect(resolvePreviewNavItemId(targets, 'hero', 'design')).toBe('comp-hero');
    expect(resolvePreviewNavItemId(targets, 'footer', 'design')).toBe('ux-footer');
  });
});
