import { describe, it, expect } from 'vitest';
import {
    normalizeAgainstAvailable,
    readBrowserLanguages,
    resolvePreferredLocale,
} from '@/engine/i18n/resolvePreferredLocale';

describe('resolvePreferredLocale', () => {
    const available = ['id', 'en', 'su'] as const;

    describe('normalizeAgainstAvailable', () => {
        it('returns exact match', () => {
            expect(normalizeAgainstAvailable('en', available, 'id')).toBe('en');
        });

        it('extracts base language from locale tag (e.g. en-US -> en)', () => {
            expect(normalizeAgainstAvailable('en-US', available, 'id')).toBe('en');
            expect(normalizeAgainstAvailable('id-ID', available, 'en')).toBe('id');
        });

        it('falls back when not available or empty', () => {
            expect(normalizeAgainstAvailable('ja-JP', available, 'id')).toBe('id');
            expect(normalizeAgainstAvailable('', available, 'id')).toBe('id');
        });
    });

    describe('readBrowserLanguages', () => {
        it('returns array of languages from navigator', () => {
            const list = readBrowserLanguages();
            expect(Array.isArray(list)).toBe(true);
        });
    });

    describe('resolvePreferredLocale', () => {
        it('prefers stored locale if valid', () => {
            expect(resolvePreferredLocale(available, { stored: 'su', fallback: 'id' })).toBe('su');
        });

        it('falls back to browser languages if stored is missing', () => {
            expect(
                resolvePreferredLocale(available, {
                    stored: null,
                    browserLanguages: ['en-US', 'id'],
                    fallback: 'id',
                }),
            ).toBe('en');
        });

        it('uses fallback when browser languages do not match', () => {
            expect(
                resolvePreferredLocale(available, {
                    stored: null,
                    browserLanguages: ['zh-CN', 'de-DE'],
                    fallback: 'id',
                }),
            ).toBe('id');
        });
    });
});
