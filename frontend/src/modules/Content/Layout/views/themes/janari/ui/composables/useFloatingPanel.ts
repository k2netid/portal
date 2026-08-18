import { nextTick, type Ref } from 'vue';
import type { JanariPanelAlign, JanariPanelSide } from './popoverContext';

export function useFloatingPanelPosition(
    triggerRef: Ref<HTMLElement | null>,
    panelRef: Ref<HTMLElement | null>,
    options: {
        side: JanariPanelSide;
        align: JanariPanelAlign;
        sideOffset: number;
    },
) {
    const apply = async () => {
        await nextTick();
        const trigger = triggerRef.value;
        const panel = panelRef.value;
        if (!trigger || !panel) return;

        const rect = trigger.getBoundingClientRect();
        const panelRect = panel.getBoundingClientRect();
        let top: number;
        let left = 0;

        if (options.side === 'bottom') {
            top = rect.bottom + options.sideOffset;
        } else if (options.side === 'top') {
            top = rect.top - panelRect.height - options.sideOffset;
        } else if (options.side === 'right') {
            left = rect.right + options.sideOffset;
            top = rect.top;
        } else {
            left = rect.left - panelRect.width - options.sideOffset;
            top = rect.top;
        }

        if (options.side === 'top' || options.side === 'bottom') {
            if (options.align === 'start') {
                left = rect.left;
            } else if (options.align === 'end') {
                left = rect.right - panelRect.width;
            } else {
                left = rect.left + rect.width / 2 - panelRect.width / 2;
            }
        }

        const padding = 8;
        left = Math.max(padding, Math.min(left, window.innerWidth - panelRect.width - padding));
        top = Math.max(padding, Math.min(top, window.innerHeight - panelRect.height - padding));

        panel.style.position = 'fixed';
        panel.style.top = `${top}px`;
        panel.style.left = `${left}px`;
        panel.style.zIndex = '200';
    };

    return { apply };
}
