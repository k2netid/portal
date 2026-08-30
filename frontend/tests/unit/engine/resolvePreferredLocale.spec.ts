import { describe, expect, it } from 'vitest';
import {
    normalizeAgainstAvailable,
    resolvePreferredLocale,
} from '@/engine/i18n/resolvePreferredLocale';

const available = ['id', 'en', 'su'] as const;

describe('resolvePreferredLocale', () => {
    it('prefers stored locale over browser', () => {
        expect(resolvePreferredLocale(available, {
            stored: 'su',
            browserLanguages: ['en-US', 'en'],
            fallback: 'id',
        })).toBe('su');
    });

    it('maps browser en-US to en', () => {
        expect(resolvePreferredLocale(available, {
            stored: null,
            browserLanguages: ['en-US', 'en'],
            fallback: 'id',
        })).toBe('en');
    });

    it('keeps Indonesian when browser prefers id', () => {
        expect(resolvePreferredLocale(available, {
            stored: null,
            browserLanguages: ['id-ID'],
            fallback: 'id',
        })).toBe('id');
    });

    it('falls back when browser language is unsupported', () => {
        expect(resolvePreferredLocale(available, {
            stored: null,
            browserLanguages: ['fr-FR', 'de'],
            fallback: 'id',
        })).toBe('id');
    });

    it('normalizes region tags', () => {
        expect(normalizeAgainstAvailable('su-ID', available, 'id')).toBe('su');
        expect(normalizeAgainstAvailable('xx-YY', available, 'id')).toBe('id');
    });
});
