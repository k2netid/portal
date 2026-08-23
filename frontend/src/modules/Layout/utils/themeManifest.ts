/**
 * Detect themes that use the Janari canvas stack (data-janari-* attrs, accent presets, themes/janari/assets/styles/janari.css).
 * Prefer manifest / DB `supports.janari_canvas`; slug prefix `janari` remains a backward-compatible fallback.
 */
function truthyFlag(value: unknown): boolean {
    return value === true || value === "1" || value === '1' || value === 'true';
}

function readJanariCanvasFromSupportsObject(supports: unknown): boolean {
    if (!supports || typeof supports !== 'object' || Array.isArray(supports)) {
        return false;
    }
    const raw = (supports as Record<string, unknown>).janari_canvas;

    return truthyFlag(raw);
}

export function readJanariCanvasSupport(manifest: unknown): boolean {
    if (!manifest || typeof manifest !== 'object' || Array.isArray(manifest)) {
        return false;
    }
    const m = manifest as { supports?: unknown };

    return readJanariCanvasFromSupportsObject(m.supports);
}

export function themeUsesJanariCanvas(
    theme: { slug?: string; manifest?: unknown; supports?: unknown } | null | undefined,
): boolean {
    if (!theme) {
        return false;
    }
    if (readJanariCanvasSupport(theme.manifest)) {
        return true;
    }
    if (readJanariCanvasFromSupportsObject(theme.supports)) {
        return true;
    }
    const slug = String(theme.slug ?? '');

    return slug.startsWith('janari');
}
