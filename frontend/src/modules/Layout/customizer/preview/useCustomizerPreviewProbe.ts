import { onMounted, onUnmounted } from 'vue';
import { isCustomizerPreviewQuery, MSG_PREVIEW_READY } from '@/modules/Layout/customizer/preview/protocol';
import {
  ATTR_TARGET,
  ROOT_CLASS,
  STYLE_ID,
  PREVIEW_PROBE_CSS,
  resolveCustomizerClickTarget,
} from '@/modules/Layout/customizer/preview/customizerPreviewProbe';

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

  function postSelect(target: string, mode?: 'design' | 'bindings') {
    if (!window.parent || window.parent === window) return;
    window.parent.postMessage(
      {
        type: 'JA_CUSTOMIZER_SELECT_TARGET',
        target,
        ...(mode ? { mode } : {}),
      },
      window.location.origin,
    );
  }

  function onClick(event: MouseEvent) {
    const resolved = resolveCustomizerClickTarget(event);
    if (!resolved) return;

    event.preventDefault();
    event.stopPropagation();

    lastTarget = resolved.target;
    document.querySelectorAll(`[${ATTR_TARGET}].is-selected`).forEach((node) => {
      node.classList.remove('is-selected');
    });
    const el = (event.target instanceof Element ? event.target : null)?.closest?.(`[${ATTR_TARGET}]`) as HTMLElement | null;
    el?.classList.add('is-selected');

    postSelect(resolved.target, resolved.mode);
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
