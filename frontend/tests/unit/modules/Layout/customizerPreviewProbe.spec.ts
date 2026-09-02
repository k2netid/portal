import { describe, expect, it } from 'vitest';
import {
    PREVIEW_PROBE_CSS,
    isCustomizerInteractiveTarget,
    resolveCustomizerClickTarget,
    shouldCaptureCustomizerClick,
} from '@/modules/Layout/customizer/preview/customizerPreviewProbe';

function clickOn(_el: Element, extras: Partial<MouseEventInit> = {}): MouseEvent {
    return new MouseEvent('click', { bubbles: true, cancelable: true, ...extras });
}

describe('customizerPreviewProbe', () => {
    it('does not force position:relative !important (that kills sticky headers)', () => {
        expect(PREVIEW_PROBE_CSS).not.toMatch(/position:\s*relative\s*!important/);
        expect(PREVIEW_PROBE_CSS).toMatch(/:not\(\.sticky\):not\(\.fixed\)/);
    });

    it('lets header/nav links and buttons work without modifier keys', () => {
        document.body.innerHTML = `
          <header data-ja-customizer-target="header" class="sticky">
            <nav data-ja-customizer-target="nav">
              <a href="/services">Fiber</a>
              <button type="button">Bahasa</button>
            </nav>
          </header>
        `;
        const link = document.querySelector('a')!;
        const button = document.querySelector('button')!;
        const linkEvent = clickOn(link);
        Object.defineProperty(linkEvent, 'target', { value: link });
        const buttonEvent = clickOn(button);
        Object.defineProperty(buttonEvent, 'target', { value: button });

        expect(isCustomizerInteractiveTarget(link)).toBe(true);
        expect(isCustomizerInteractiveTarget(button)).toBe(true);
        expect(shouldCaptureCustomizerClick(linkEvent)).toBe(false);
        expect(shouldCaptureCustomizerClick(buttonEvent)).toBe(false);
        expect(resolveCustomizerClickTarget(linkEvent)).toBeNull();
    });

    it('still selects the header chrome when clicking empty padding', () => {
        document.body.innerHTML = `
          <header data-ja-customizer-target="header" class="sticky">
            <div class="pad"></div>
          </header>
        `;
        const pad = document.querySelector('.pad')!;
        const event = clickOn(pad);
        Object.defineProperty(event, 'target', { value: pad });
        const resolved = resolveCustomizerClickTarget(event);
        expect(resolved?.target).toBe('header');
    });

    it('captures interactive clicks when Ctrl/Cmd/Alt is held', () => {
        document.body.innerHTML = `<header data-ja-customizer-target="header"><a href="/">Home</a></header>`;
        const link = document.querySelector('a')!;
        const event = clickOn(link, { ctrlKey: true });
        Object.defineProperty(event, 'target', { value: link });
        expect(shouldCaptureCustomizerClick(event)).toBe(true);
        expect(resolveCustomizerClickTarget(event)?.target).toBe('header');
    });
});
