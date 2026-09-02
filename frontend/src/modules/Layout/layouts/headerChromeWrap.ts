/**
 * Sticky <header> cannot live inside a `position: relative` wrapper that is only
 * as tall as the header — the sticky containing block is that tiny box, so the
 * bar scrolls away. Janari uses `position: fixed` on <header> itself; Layung /
 * Sarangenge stick via this wrapper instead.
 */
export function headerChromeWrapClass(opts: {
  sticky: boolean;
  janariFixed: boolean;
}): string {
  if (opts.janariFixed) return 'relative z-50 overflow-visible';
  if (opts.sticky) return 'sticky top-0 z-[100] overflow-visible';
  return 'relative z-50 overflow-visible';
}

export function isHeaderStickySetting(value: unknown, defaultValue = true): boolean {
  if (value === undefined || value === null) return defaultValue;
  if (value === false || value === 0 || value === '0' || value === 'false' || value === 'off') {
    return false;
  }
  return true;
}
