import { describe, expect, it } from 'vitest';
import type { Content } from '@/modules/Content/Publishing/types/content';
import {
    hasSubstantivePublicContent,
    isPublicContentRecord,
} from '@/modules/Content/Publishing/utils/publicContent';

describe('publicContent utils', () => {
    it('rejects non-object API payloads', () => {
        expect(isPublicContentRecord(null)).toBe(false);
        expect(isPublicContentRecord('<!DOCTYPE html>')).toBe(false);
        expect(isPublicContentRecord({ title: 'x' })).toBe(false);
    });

    it('accepts records with a slug', () => {
        expect(isPublicContentRecord({ id: '1', slug: 'about', title: 'About' })).toBe(true);
    });

    it('treats empty published shells as non-substantive', () => {
        expect(
            hasSubstantivePublicContent({
                id: '1',
                slug: 'asalsaja',
                title: 'asalsaja',
                type: 'page',
                status: 'published',
            } as Content),
        ).toBe(false);
    });

    it('accepts pages with body, intro, or featured image', () => {
        expect(
            hasSubstantivePublicContent({
                id: '1',
                slug: 'about',
                title: 'About',
                type: 'page',
                status: 'published',
                body: '<p>Hi</p>',
            } as Content),
        ).toBe(true);
    });
});
