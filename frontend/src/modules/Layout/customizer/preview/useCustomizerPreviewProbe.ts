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

/**
 * Public-site probe: when loaded inside Theme Customizer iframe, outline
 * annotated sections and post SELECT_TARGET on click (capture phase).
 */
export function useCustomizerPreviewProbe() {
  if (typeof window === 'undefined') return;

  const enabled = isCustomizerPreviewQuery(window.location.search);
  if (!enabled) return;

  let lastTarget: string | null = null;

  function postSelect(target: string, mode?: CustomizerPreviewMode) {
    if (!window.parent || window.parent === window) return;
    // Same-origin embed: always address parent by the iframe's own origin
    // (query parent_origin can mismatch localhost vs 127.0.0.1).
    const targetOrigin = window.location.origin;
    window.parent.postMessage(
      {
        type: MSG_SELECT_TARGET,
        target,
        ...(mode ? { mode } : {}),
      },
      targetOrigin,
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

    // Alt/Option → prefer Content bindings when host supports it
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

  onMounted(() => {
    document.documentElement.classList.add(ROOT_CLASS);
    document.addEventListener('click', onClick, true);
    document.addEventListener('keydown', onKeydown);

    if (window.parent && window.parent !== window) {
      window.parent.postMessage({ type: MSG_PREVIEW_READY }, window.location.origin);
    }
  });

  onUnmounted(() => {
    document.documentElement.classList.remove(ROOT_CLASS);
    document.removeEventListener('click', onClick, true);
    document.removeEventListener('keydown', onKeydown);
  });
}
