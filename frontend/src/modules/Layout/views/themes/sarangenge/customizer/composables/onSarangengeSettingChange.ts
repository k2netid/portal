/**
 * Handle customizer setting changes for Sarangenge theme.
 */
export function onSarangengeSettingChange(
    key: string,
    val: unknown,
    formValues: Record<string, unknown>,
): void {
    if (key === 'ppdb_is_open' && val === true) {
        if (!formValues.ppdb_year) {
            formValues.ppdb_year = '2026/2027';
        }
    }
}
