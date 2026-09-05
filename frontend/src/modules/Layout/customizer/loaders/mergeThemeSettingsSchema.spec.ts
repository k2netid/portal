import { describe, it, expect } from 'vitest';
import { mergeThemeSettingsSchema } from './mergeThemeSettingsSchema';

describe('mergeThemeSettingsSchema', () => {
    it('correctly loads and isolates schema for sarangenge', () => {
        const schema = mergeThemeSettingsSchema('sarangenge');
        expect(schema.home_sections).toBeDefined();
        const homeSections = schema.home_sections!;
        const options = (homeSections.options || []) as Array<{ value: string }>;
        expect(options).toHaveLength(11);
        expect(options.map((o) => o.value)).toEqual([
            'hero',
            'bento',
            'track_finder',
            'programs',
            'announcements',
            'achievements',
            'facilities',
            'extracurricular',
            'testimonials',
            'faq',
            'cta',
        ]);
        expect(schema.facilities_title?.category).toBe('Facilities Page');
        expect(schema.enable_facilities?.category).toBe('Facilities Page');
    });

    it('correctly loads and isolates schema for layung', () => {
        const schema = mergeThemeSettingsSchema('layung');
        expect(schema.home_sections).toBeDefined();
        const homeSections = schema.home_sections!;
        const options = (homeSections.options || []) as Array<{ value: string }>;
        expect(options).toHaveLength(8);
        expect(options.map((o) => o.value)).toEqual([
            'hero',
            'services',
            'calculator',
            'sla',
            'managed_services',
            'testimonials',
            'faq',
            'cta',
        ]);
    });

    it('correctly loads and isolates schema for janari', () => {
        const schema = mergeThemeSettingsSchema('janari');
        expect(schema.home_sections).toBeDefined();
        const homeSections = schema.home_sections!;
        const options = (homeSections.options || []) as Array<{ value: string }>;
        expect(options).toHaveLength(6);
        expect(options.map((o) => o.value)).toEqual([
            'hero',
            'products',
            'updates',
            'partners',
            'testimonials',
            'cta',
        ]);
    });
});
