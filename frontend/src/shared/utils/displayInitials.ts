/**
 * Avatar fallback initials from a display name (max 2 characters).
 */
export function getDisplayInitials(name?: string | null): string {
    const trimmed = (name ?? '').trim();
    if (!trimmed) {
        return '?';
    }

    const parts = trimmed.split(/\s+/).filter(Boolean);
    if (parts.length === 0) {
        return '?';
    }

    const firstWord = parts[0] ?? '';
    if (parts.length === 1) {
        return firstWord.slice(0, 2).toUpperCase() || '?';
    }

    const first = firstWord[0] ?? '';
    const second = (parts[1] ?? '')[0] ?? '';
    const initials = `${first}${second}`.toUpperCase();

    return initials || '?';
}
