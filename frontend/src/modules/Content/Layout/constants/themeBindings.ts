/** JSON key in `Theme.settings` for component → slot data bindings. */
export const THEME_DATA_BINDINGS_KEY = 'theme_data_bindings' as const

export function isPlainSettingsObject(value: unknown): value is Record<string, unknown> {
    return value !== null && typeof value === 'object' && !Array.isArray(value)
}
