import dayjs from 'dayjs';

/**
 * Format number to Indonesian Rupiah (IDR)
 */
export function formatCurrency(amount: number | string): string {
    const value = typeof amount === 'string' ? parseFloat(amount) : amount;
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(value || 0);
}

/**
 * Format number with thousand separator
 */
export function formatNumber(num: number | string): string {
    const value = typeof num === 'string' ? parseFloat(num) : num;
    return new Intl.NumberFormat('id-ID').format(value || 0);
}

/**
 * Format date to standard Indonesian format
 */
export function formatDate(date: string | Date | null | undefined, format = 'DD MMM YYYY'): string {
    if (!date) return '-';
    return dayjs(date).format(format);
}

/**
 * Get status variant for Badges
 */
export function getStatusVariant(status: string): 'default' | 'success' | 'warning' | 'destructive' | 'secondary' | 'outline' {
    const s = status?.toLowerCase() || '';
    if (['active', 'paid', 'success', 'online'].includes(s)) return 'success';
    if (['pending', 'unpaid', 'warning'].includes(s)) return 'warning';
    if (['suspended', 'cancelled', 'failed', 'offline', 'destructive', 'isolated'].includes(s)) return 'destructive';
    if (['inactive', 'closed'].includes(s)) return 'secondary';
    return 'outline';
}
