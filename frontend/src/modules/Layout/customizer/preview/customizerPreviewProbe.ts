import { MSG_SELECT_TARGET, type CustomizerPreviewMode } from '@/modules/Layout/customizer/preview/protocol';

export const ATTR_TARGET = 'data-ja-customizer-target';
export const ATTR_MODE = 'data-ja-customizer-mode';
export const ROOT_CLASS = 'ja-customizer-preview';
export const STYLE_ID = 'ja-customizer-preview-probe-styles';

/**
 * Do not force `position: relative` on sticky/fixed chrome.
 * The previous `position: relative !important` rule killed sticky headers
 * (`<header class="sticky" data-ja-customizer-target="header">`).
 */
export const PREVIEW_PROBE_CSS = `
html.ja-customizer-preview [data-ja-customizer-target] {
  cursor: pointer !important;
  outline: 2px dashed rgba(37, 99, 235, 0.55) !important;
  outline-offset: 3px !important;
  box-shadow: inset 0 0 0 9999px rgba(37, 99, 235, 0.04) !important;
  transition: outline-color 0.15s ease, box-shadow 0.15s ease !important;
  min-height: 2.5rem;
}
html.ja-customizer-preview [data-ja-customizer-target]:not(.sticky):not(.fixed) {
  position: relative;
}
html.ja-customizer-preview [data-ja-customizer-target]:hover {
  outline-color: rgba(37, 99, 235, 0.95) !important;
  outline-style: solid !important;
  box-shadow: inset 0 0 0 9999px rgba(37, 99, 235, 0.1) !important;
}
html.ja-customizer-preview [data-ja-customizer-target].is-selected {
  outline: 3px solid #2563eb !important;
  outline-offset: 2px !important;
  box-shadow: inset 0 0 0 9999px rgba(37, 99, 235, 0.14) !important;
}
html.ja-customizer-preview [data-ja-customizer-target]::after {
  content: "✎ " attr(data-ja-customizer-target) !important;
  position: absolute !important;
  top: 10px !important;
  right: 10px !important;
  z-index: 2147483000 !important;
  display: inline-flex !important;
  align-items: center !important;
  gap: 4px !important;
  padding: 3px 9px !important;
  border-radius: 999px !important;
  font-family: ui-sans-serif, system-ui, sans-serif !important;
  font-size: 10px !important;
  font-weight: 800 !important;
  letter-spacing: 0.04em !important;
  text-transform: uppercase !important;
  line-height: 1.2 !important;
  color: #fff !important;
  background: #2563eb !important;
  box-shadow: 0 4px 14px rgba(37, 99, 235, 0.35) !important;
  opacity: 1 !important;
  pointer-events: none !important;
  white-space: nowrap !important;
}
html.ja-customizer-preview [data-ja-customizer-target].is-selected::after {
  background: #1d4ed8 !important;
  content: "✓ " attr(data-ja-customizer-target) !important;
}
`;

const INTERACTIVE_SELECTOR = [
  'a',
  'button',
  'input',
  'select',
  'textarea',
  'summary',
  'label',
  '[role="button"]',
  '[role="menuitem"]',
  '[role="option"]',
  '[role="link"]',
  '[data-radix-collection-item]',
  '[data-state]',
].join(',');

export function isCustomizerInteractiveTarget(target: EventTarget | null): boolean {
  if (!(target instanceof Element)) return false;
  return Boolean(target.closest(INTERACTIVE_SELECTOR));
}

/**
 * Capture-phase click-to-select must not swallow header/nav interactions.
 * Modifier click (Ctrl/Cmd/Alt) still selects the section.
 */
export function shouldCaptureCustomizerClick(event: MouseEvent): boolean {
  if (event.metaKey || event.ctrlKey || event.altKey) return true;
  return !isCustomizerInteractiveTarget(event.target);
}

export function resolveCustomizerClickTarget(event: MouseEvent): {
  target: string;
  mode?: CustomizerPreviewMode;
} | null {
  const el = (event.target instanceof Element ? event.target : null)?.closest?.(`[${ATTR_TARGET}]`) as HTMLElement | null;
  if (!el) return null;
  const target = el.getAttribute(ATTR_TARGET);
  if (!target) return null;
  if (!shouldCaptureCustomizerClick(event)) return null;

  const modeAttr = el.getAttribute(ATTR_MODE) as CustomizerPreviewMode | null;
  const mode = modeAttr === 'bindings' || modeAttr === 'design' ? modeAttr : undefined;
  const effectiveMode: CustomizerPreviewMode | undefined = event.altKey ? 'bindings' : mode;

  return { target, mode: effectiveMode };
}

export { MSG_SELECT_TARGET };
