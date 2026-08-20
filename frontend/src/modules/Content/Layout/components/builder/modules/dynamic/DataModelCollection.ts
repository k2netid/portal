import type { ModuleDefinition } from '@/types/builder';
import {
    backgroundSettings,
    spacingSettings,
    borderSettings,
    boxShadowSettings,
    sizingSettings,
    filterSettings,
    transformSettings,
    animationSettings,
    visibilitySettings,
    positionSettings,
    transitionSettings,
    cssSettings,
    typographySettings,
    conditionsSettings,
    interactionsSettings,
    scrollEffectsSettings,
    attributesSettings,
    adminLabelSettings,
    layoutSettings
} from '@/components/builder/modules/commonSettings';

/**
 * Data Model Collection Module Definition
 * Allows visual rendering of any Data Model Studio collection (e.g. portfolio, doctors, testimonials, products).
 */
const DataModelCollectionModule: ModuleDefinition = {
    name: 'datamodel_collection',
    title: 'Data Model Collection',
    icon: 'Database',
    category: 'dynamic',

    children: null,

    defaults: {
        modelSlug: '',
        title: 'Data Model Collection',
        itemsPerPage: 6,
        columns: 3,
        layout: 'grid',
        gap: 24,

        // Field mapping
        titleField: 'title',
        descriptionField: 'description',
        imageField: 'image',
        badgeField: 'category',
        linkField: 'url',

        // Display Toggles
        showTitle: true,
        showImage: true,
        showBadge: true,
        showDescription: true,
        showLink: true,
        buttonText: 'View Details',

        // Image
        imageAspectRatio: '16:9',

        // Background
        background: { color: '', image: '', repeat: 'no-repeat', position: 'center', size: 'cover' },

        // Spacing
        padding: { top: 0, bottom: 0, left: 0, right: 0, unit: 'px' },
        margin: { top: 0, bottom: 0, left: 0, right: 0, unit: 'px' },

        // Border & Shadow
        border: {
            radius: { tl: 12, tr: 12, bl: 12, br: 12, linked: true },
            styles: {
                all: { width: 1, color: 'hsl(var(--border))', style: 'solid' },
                top: { width: 1, color: 'hsl(var(--border))', style: 'solid' },
                right: { width: 1, color: 'hsl(var(--border))', style: 'solid' },
                bottom: { width: 1, color: 'hsl(var(--border))', style: 'solid' },
                left: { width: 1, color: 'hsl(var(--border))', style: 'solid' }
            }
        },
        boxShadow: { preset: 'none', horizontal: 0, vertical: 0, blur: 0, spread: 0, color: 'rgba(0,0,0,0)', inset: false },

        hover_scale: 1,
        hover_brightness: 100,

        aria_label: '',
        html_id: '',
        animation_effect: '',
        animation_duration: 1000,
        animation_delay: 0,
        animation_repeat: '1'
    },

    settings: {
        content: [
            {
                id: 'model_source',
                label: 'Data Model Source',
                fields: [
                    {
                        name: 'modelSlug',
                        type: 'select',
                        label: 'Select Data Model',
                        placeholder: 'Choose a Data Model...',
                        options: 'dynamic:models',
                        searchable: true,
                        help: 'Select the dynamic collection created in Data Model Studio.'
                    },
                    {
                        name: 'title',
                        type: 'text',
                        label: 'Section Heading',
                        placeholder: 'Our Collection',
                        responsive: true
                    },
                    {
                        name: 'itemsPerPage',
                        type: 'range',
                        label: 'Items Limit',
                        min: 1,
                        max: 24,
                        step: 1,
                        responsive: true
                    },
                    {
                        name: 'columns',
                        type: 'select',
                        label: 'Grid Columns',
                        options: [
                            { value: 1, label: '1 Column (List)' },
                            { value: 2, label: '2 Columns' },
                            { value: 3, label: '3 Columns' },
                            { value: 4, label: '4 Columns' }
                        ],
                        responsive: true
                    }
                ]
            },
            {
                id: 'field_mappings',
                label: 'Field Mappings',
                fields: [
                    {
                        name: 'titleField',
                        type: 'text',
                        label: 'Title Field Slug',
                        placeholder: 'title, name, headline'
                    },
                    {
                        name: 'imageField',
                        type: 'text',
                        label: 'Image Field Slug',
                        placeholder: 'image, photo, thumbnail'
                    },
                    {
                        name: 'badgeField',
                        type: 'text',
                        label: 'Badge / Category Field Slug',
                        placeholder: 'category, role, type'
                    },
                    {
                        name: 'descriptionField',
                        type: 'text',
                        label: 'Description Field Slug',
                        placeholder: 'description, bio, excerpt'
                    },
                    {
                        name: 'linkField',
                        type: 'text',
                        label: 'Link / URL Field Slug',
                        placeholder: 'url, link, website'
                    }
                ]
            },
            {
                id: 'display_elements',
                label: 'Display Elements',
                fields: [
                    { name: 'showImage', type: 'toggle', label: 'Show Card Image', responsive: true },
                    { name: 'showBadge', type: 'toggle', label: 'Show Category Badge', responsive: true },
                    { name: 'showDescription', type: 'toggle', label: 'Show Description', responsive: true },
                    { name: 'showLink', type: 'toggle', label: 'Show Action Button / Link', responsive: true },
                    { name: 'buttonText', type: 'text', label: 'Button Label', placeholder: 'View Details' }
                ]
            },
            backgroundSettings,
            adminLabelSettings('Data Model Collection')
        ],
        design: [
            layoutSettings,
            typographySettings,
            spacingSettings,
            borderSettings,
            boxShadowSettings,
            sizingSettings,
            filterSettings,
            transformSettings,
            animationSettings,
            visibilitySettings,
            positionSettings,
            transitionSettings
        ],
        advanced: [
            cssSettings,
            conditionsSettings,
            interactionsSettings,
            scrollEffectsSettings,
            attributesSettings
        ]
    }
};

export default DataModelCollectionModule;
