import { onMounted, onUnmounted } from 'vue';
import {
  isCustomizerPreviewQuery,
  MSG_PREVIEW_READY,
  MSG_SELECT_TARGET,
  type CustomizerPreviewMode,
} from '@/modules/Layout/customizer/preview/protocol';

const ATTR_TARGET = 'data-ja-customizer-target';
const ATTR_MODE = 'data-ja-customizer-mode';
const ROOT_CLASS = 'ja-customizer-preview';
const STYLE_ID = 'ja-customizer-preview-probe-styles';

/** Inline so public shell always gets badges even if Vite CSS chunk is delayed. */
const PREVIEW_PROBE_CSS = `
html.ja-customizer-preview [data-ja-customizer-target] {
  position: relative !important;
  cursor: pointer !important;
  outline: 2px dashed rgba(37, 99, 235, 0.55) !important;
  outline-offset: 3px !important;
  box-shadow: inset 0 0 0 9999px rgba(37, 99, 235, 0.04) !important;
  transition: outline-color 0.15s ease, box-shadow 0.15s ease !important;
  min-height: 2.5rem;
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

/**
 * Public-site probe: when loaded inside Theme Customizer iframe, outline
 * annotated sections and post SELECT_TARGET on click (capture phase).
 */
export function useCustomizerPreviewProbe() {
  if (typeof window === 'undefined') return;

  const inIframe = (() => {
    try {
      return window.parent !== window;
    } catch {
      return true;
    }
  })();

  const enabled =
    isCustomizerPreviewQuery(window.location.search)
    || (inIframe && typeof sessionStorage !== 'undefined' && sessionStorage.getItem('ja_customizer_preview') === '1');

  if (!enabled) return;

  try {
    sessionStorage.setItem('ja_customizer_preview', '1');
  } catch { /* ignore */ }

  let lastTarget: string | null = null;

  function ensureStyles() {
    if (document.getElementById(STYLE_ID)) return;
    const style = document.createElement('style');
    style.id = STYLE_ID;
    style.textContent = PREVIEW_PROBE_CSS;
    document.head.appendChild(style);
  }

  function postSelect(target: string, mode?: CustomizerPreviewMode) {
    if (!window.parent || window.parent === window) return;
    window.parent.postMessage(
      {
        type: MSG_SELECT_TARGET,
        target,
        ...(mode ? { mode } : {}),
      },
      window.location.origin,
    );
  }

  function onClick(event: MouseEvent) {
    const el = (event.target as HTMLElement | null)?.closest?.(`[${ATTR_TARGET}]`) as HTMLElement | null;
    if (!el) return;

    const target = el.getAttribute(ATTR_TARGET);
    if (!target) return;

    event.preventDefault();
    event.stopPropagation();

    const modeAttr = el.getAttribute(ATTR_MODE) as CustomizerPreviewMode | null;
    const mode = modeAttr === 'bindings' || modeAttr === 'design' ? modeAttr : undefined;
    const effectiveMode: CustomizerPreviewMode | undefined =
      event.altKey ? 'bindings' : mode;

    lastTarget = target;
    document.querySelectorAll(`[${ATTR_TARGET}].is-selected`).forEach((node) => {
      node.classList.remove('is-selected');
    });
    el.classList.add('is-selected');

    postSelect(target, effectiveMode);
  }

  function onKeydown(event: KeyboardEvent) {
    if (event.key !== 'Escape' || !lastTarget) return;
    document.querySelectorAll(`[${ATTR_TARGET}].is-selected`).forEach((node) => {
      node.classList.remove('is-selected');
    });
    lastTarget = null;
  }

  function onParentMessage(event: MessageEvent) {
    if (event.origin !== window.location.origin) return;
    if (event.data?.type !== 'JA_CUSTOMIZER_FOCUS_TARGET') return;
    const target = typeof event.data.target === 'string' ? event.data.target : '';
    if (!target) return;
    const el = document.querySelector(`[${ATTR_TARGET}="${CSS.escape(target)}"]`) as HTMLElement | null;
    if (!el) return;
    document.querySelectorAll(`[${ATTR_TARGET}].is-selected`).forEach((node) => {
      node.classList.remove('is-selected');
    });
    el.classList.add('is-selected');
    lastTarget = target;
    el.scrollIntoView({ behavior: 'smooth', block: 'center' });
  }

  onMounted(() => {
    document.documentElement.classList.add(ROOT_CLASS);
    ensureStyles();
    document.addEventListener('click', onClick, true);
    document.addEventListener('keydown', onKeydown);
    window.addEventListener('message', onParentMessage);

    if (window.parent && window.parent !== window) {
      window.parent.postMessage({ type: MSG_PREVIEW_READY }, window.location.origin);
    }
  });

  onUnmounted(() => {
    document.documentElement.classList.remove(ROOT_CLASS);
    document.getElementById(STYLE_ID)?.remove();
    document.removeEventListener('click', onClick, true);
    document.removeEventListener('keydown', onKeydown);
    window.removeEventListener('message', onParentMessage);
  });
}
