import type { ComposerTranslation } from 'vue-i18n';

export function securityEventTypeKey(eventType: string): string {
    return `system.security.logs.eventTypes.${eventType}`;
}

export function formatSecurityEventLabel(
    t: ComposerTranslation,
    eventType?: string | null,
): string {
    if (!eventType) return '-';
    const key = securityEventTypeKey(eventType);
    const translated = t(key);
    if (translated !== key) return translated;
    return eventType
        .replace(/_/g, ' ')
        .replace(/\b\w/g, (c) => c.toUpperCase());
}
