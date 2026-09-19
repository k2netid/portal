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

    it('inherits platform header_menu_alignment with default center across themes', () => {
        const layungSchema = mergeThemeSettingsSchema('layung');
        expect(layungSchema.header_menu_alignment).toBeDefined();
        expect(layungSchema.header_menu_alignment?.default).toBe('center');
        expect(layungSchema.header_menu_alignment?.category).toBe('Layout');

        const janariSchema = mergeThemeSettingsSchema('janari');
        expect(janariSchema.header_menu_alignment).toBeDefined();
        expect(janariSchema.header_menu_alignment?.default).toBe('center');

        const options = (layungSchema.header_menu_alignment?.options || []) as Array<{ value: string }>;
        expect(options.map((o) => o.value)).toEqual(['center', 'left', 'right']);
    });

    it('assigns order attributes to platform layout settings deterministically', () => {
        const schema = mergeThemeSettingsSchema('sarangenge');
        expect(schema.layout_style?.order).toBe(10);
        expect(schema.container_max_width?.order).toBe(20);
        expect(schema.header_style?.order).toBe(60);
        expect(schema.header_sticky?.order).toBe(70);
        expect(schema.header_menu_alignment?.order).toBe(80);
        expect(schema.nav_style?.order).toBe(90);
        expect(schema.home_sections?.order).toBe(130);
    });

    it('includes contact map settings and order in sarangenge contact page', () => {
        const schema = mergeThemeSettingsSchema('sarangenge');
        expect(schema.enable_contact?.order).toBe(10);
        expect(schema.contact_form_slug?.order).toBe(20);
        expect(schema.contact_address?.order).toBe(22);
        expect(schema.contact_phone?.order).toBe(25);
        expect(schema.contact_whatsapp?.order).toBe(30);
        expect(schema.contact_admission_hotline?.order).toBe(40);
        expect(schema.contact_operating_hours?.order).toBe(50);
        expect(schema.contact_map_enabled?.order).toBe(60);
        expect(schema.contact_map_source?.order).toBe(70);
        expect(schema.contact_map_link?.order).toBe(80);
        expect(schema.contact_map_zoom?.order).toBe(90);
        expect(schema.contact_maps_embed_url?.order).toBe(100);
    });
});
