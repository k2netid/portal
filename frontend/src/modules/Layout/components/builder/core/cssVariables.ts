export const themeVariables = [
    // Backgrounds
    '--builder-bg-primary',
    '--builder-bg-secondary',
    '--builder-bg-tertiary',
    '--builder-bg-background',
    '--builder-bg-overlay',

    // Text
    '--builder-text-primary',
    '--builder-text-secondary',
    '--builder-text-muted',
    '--builder-text-inverted',

    // Brand / Theme
    '--builder-primary',
    '--builder-secondary',
    '--builder-accent',

    // Status
    '--builder-success',
    '--builder-warning',
    '--builder-error',
    '--builder-info',

    // Borders
    '--builder-border',
    '--builder-border-hover',

    // Layout / Spacing (optional if used in color context)
    // '--container-padding',
    // '--header-height'
];

/**
 * Helper to converting a string name to a CSS variable slug
 * e.g. "Brand Blue" -> "--brand-blue"
 */
export const toCssVarName = (name: string): string => {
    if (!name) return '';
    const slug = name.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
    return `--${slug}`;
}
