/**
 * Binding components with CMS slots open Content mode.
 * Components that only own theme settings (empty slots) stay in Design
 * so the controls pane shows the actual fields instead of an empty
 * “content source” inspector.
 */
export function resolveBindingComponentMode(
  component: { slots?: unknown[] } | null | undefined,
  preferred?: 'design' | 'bindings' | 'advanced' | null,
): 'design' | 'bindings' {
  const hasSlots = Boolean(component?.slots && component.slots.length > 0);
  if (preferred === 'bindings' && hasSlots) return 'bindings';
  if (preferred === 'design' || !hasSlots) return 'design';
  return 'bindings';
}

export function bindingComponentAllowsDesignMode(item: {
  bindingComponent?: { slots?: unknown[] } | null;
  manifestSections?: { settings?: unknown[] }[] | null;
}): boolean {
  if (!item.bindingComponent) return true;
  const hasSlots = Boolean(item.bindingComponent.slots && item.bindingComponent.slots.length > 0);
  const hasSettings = Boolean(item.manifestSections?.some((section) => (section.settings?.length ?? 0) > 0));
  return !hasSlots || hasSettings;
}

export function componentManifestCategories(component: {
  manifestCategory?: string;
  manifestCategories?: string[];
}): string[] {
  if (Array.isArray(component.manifestCategories) && component.manifestCategories.length > 0) {
    return component.manifestCategories.filter((cat) => typeof cat === 'string' && cat.trim() !== '');
  }
  if (typeof component.manifestCategory === 'string' && component.manifestCategory.trim()) {
    return [component.manifestCategory.trim()];
  }
  return [];
}
