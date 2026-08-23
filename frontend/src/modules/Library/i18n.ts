/** Locale namespace for the Library module (tags, custom fields). */
export const LIBRARY_I18N_NS = 'library' as const;

export function libraryKey(suffix: string): `${typeof LIBRARY_I18N_NS}.${string}` {
    return `${LIBRARY_I18N_NS}.${suffix}`;
}
