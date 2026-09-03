import { describe, it, expect, beforeEach } from 'vitest';
import {
    isCustomizerPreviewQuery,
    readParentOriginFromQuery,
    isTrustedCustomizerParentOrigin,
    resolveAllowedCustomizerOrigins,
    readThemeSlugFromQuery,
    withCustomizerPreviewParams,
    isCustomizerSelectTargetMessage,
    isCustomizerThemeBootMessage,
    readStoredPreviewTheme,
    storePreviewTheme,
    MSG_SELECT_TARGET,
    MSG_THEME_BOOT,
} from '@/modules/Layout/customizer/preview/protocol';

describe('customizer preview protocol', () => {
    beforeEach(() => {
        sessionStorage.clear();
    });

    describe('query readers', () => {
        it('detects preview query parameter', () => {
            expect(isCustomizerPreviewQuery('?ja_customizer_preview=1')).toBe(true);
            expect(isCustomizerPreviewQuery('?ja_customizer_preview=0')).toBe(false);
            expect(isCustomizerPreviewQuery('?other=1')).toBe(false);
            expect(isCustomizerPreviewQuery('')).toBe(false);
        });

        it('reads parent origin safely', () => {
            expect(readParentOriginFromQuery('?ja_parent_origin=http%3A%2F%2Flocalhost%3A8081')).toBe('http://localhost:8081');
            expect(readParentOriginFromQuery('?ja_parent_origin=javascript:alert(1)')).toBeNull();
            expect(readParentOriginFromQuery('')).toBeNull();
        });

        it('validates trusted parent origin (same hostname, http/https only, no credentials)', () => {
            const self = 'http://staging.k2net.id:8083';
            expect(isTrustedCustomizerParentOrigin('http://staging.k2net.id:8083', self)).toBe(true);
            expect(isTrustedCustomizerParentOrigin('https://staging.k2net.id', self)).toBe(true);
            expect(isTrustedCustomizerParentOrigin('https://malicious.k2net.id', self)).toBe(false);
            expect(isTrustedCustomizerParentOrigin('http://attacker.com', self)).toBe(false);
            expect(isTrustedCustomizerParentOrigin('https://user:pass@staging.k2net.id', self)).toBe(false);
            expect(isTrustedCustomizerParentOrigin('invalid-url', self)).toBe(false);
        });

        it('resolves allowed customizer origins', () => {
            const self = 'http://staging.k2net.id:8083';
            // Not in preview mode
            const normalVisits = resolveAllowedCustomizerOrigins('?ja_parent_origin=http%3A%2F%2Fstaging.k2net.id', self);
            expect(normalVisits.has(self)).toBe(true);
            expect(normalVisits.size).toBe(1);

            // In preview mode with trusted parent
            const previewTrusted = resolveAllowedCustomizerOrigins('?ja_customizer_preview=1&ja_parent_origin=https%3A%2F%2Fstaging.k2net.id', self);
            expect(previewTrusted.has(self)).toBe(true);
            expect(previewTrusted.has('https://staging.k2net.id')).toBe(true);

            // In preview mode with untrusted parent
            const previewUntrusted = resolveAllowedCustomizerOrigins('?ja_customizer_preview=1&ja_parent_origin=https%3A%2F%2Fevil.com', self);
            expect(previewUntrusted.has(self)).toBe(true);
            expect(previewUntrusted.has('https://evil.com')).toBe(false);
        });

        it('reads valid theme slug from query', () => {
            expect(readThemeSlugFromQuery('?ja_theme_slug=layung')).toBe('layung');
            expect(readThemeSlugFromQuery('?ja_theme_slug=Layung_Theme-1')).toBe('layung_theme-1');
            expect(readThemeSlugFromQuery('?ja_theme_slug=invalid%20slug!')).toBeNull();
            expect(readThemeSlugFromQuery('')).toBeNull();
        });

        it('constructs url with preview params', () => {
            const result = withCustomizerPreviewParams('/pricing/isp', 'http://127.0.0.1:8083', 'layung');
            expect(result).toContain('ja_customizer_preview=1');
            expect(result).toContain('ja_parent_origin=http%3A%2F%2F127.0.0.1%3A8083');
            expect(result).toContain('ja_theme_slug=layung');
        });
    });

    describe('message type guards', () => {
        it('validates select target message', () => {
            expect(
                isCustomizerSelectTargetMessage({
                    type: MSG_SELECT_TARGET,
                    target: 'hero',
                }),
            ).toBe(true);

            expect(
                isCustomizerSelectTargetMessage({
                    type: MSG_SELECT_TARGET,
                    target: 123,
                }),
            ).toBe(false);

            expect(
                isCustomizerSelectTargetMessage({
                    type: 'OTHER_MSG',
                }),
            ).toBe(false);

            expect(isCustomizerSelectTargetMessage(null)).toBe(false);
            expect(isCustomizerSelectTargetMessage(undefined)).toBe(false);
        });

        it('validates theme boot message', () => {
            expect(
                isCustomizerThemeBootMessage({
                    type: MSG_THEME_BOOT,
                    theme: { slug: 'layung' },
                }),
            ).toBe(true);

            expect(
                isCustomizerThemeBootMessage({
                    type: MSG_THEME_BOOT,
                    theme: 'invalid',
                }),
            ).toBe(false);

            expect(
                isCustomizerThemeBootMessage({
                    type: 'OTHER_MSG',
                }),
            ).toBe(false);
        });
    });

    describe('session storage preview theme persistence', () => {
        it('stores and reads preview theme payload', () => {
            expect(readStoredPreviewTheme()).toBeNull();

            const mockTheme = {
                slug: 'layung',
                name: 'Layung K2NET',
                version: '1.0.0',
            };

            storePreviewTheme(mockTheme as any);
            const read = readStoredPreviewTheme();
            expect(read).toEqual(mockTheme);
        });

        it('handles corrupted sessionStorage gracefully', () => {
            sessionStorage.setItem('ja_customizer_preview_theme', 'invalid-json{{{');
            expect(readStoredPreviewTheme()).toBeNull();
        });
    });
});
