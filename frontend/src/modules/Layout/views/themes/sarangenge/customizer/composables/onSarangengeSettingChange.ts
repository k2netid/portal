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

    if (key === 'school_name' && typeof val === 'string') {
        formValues.site_name = val;
        formValues.site_title = val;
    } else if ((key === 'site_title' || key === 'site_name') && typeof val === 'string') {
        formValues.school_name = val;
        formValues.site_title = val;
        formValues.site_name = val;
    }

    if (key === 'school_tagline' && typeof val === 'string') {
        formValues.site_tagline = val;
    } else if (key === 'site_tagline' && typeof val === 'string') {
        formValues.school_tagline = val;
    }
}
