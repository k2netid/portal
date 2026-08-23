import type { ModuleDefinition, SettingDefinition } from '@/types/builder';
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
    linkSettings,
    loopSettings,
    orderSettings,
    adminLabelSettings,
    cssSettings,
    typographySettings,
    conditionsSettings,
    interactionsSettings,
    scrollEffectsSettings,
    attributesSettings,
    layoutSettings
} from '@/components/builder/modules/commonSettings';

/**
 * Text Module Definition
 */
const TextModule: ModuleDefinition = {
    name: 'text',
    title: 'Text',
    icon: 'Type',
    category: 'basic',

    children: null,

    defaults: {
        content: `
<p>Your content goes here. Edit or remove this text inline or in the module Content settings. You can also style every aspect of this content in the module Design settings and even apply custom CSS to this text in the module Advanced settings.</p>
<p>Pro-level tip: Use the new Multi-Column settings to organize your layout without creating complex row/column structures. You can also enable Drop Caps to give your paragraphs a premium, editorial feel.</p>
<p>This text is designed to show you exactly how your content will flow across the page. Whether you're building a landing page, a blog post, or a technical document, our builder gives you the flexibility to present your ideas with stunning clarity and precision.</p>
`,
        showTitle: false,
        title: '',
        titleTag: 'h2',
        layout_type: 'block',
        gap_x: '0px',
        gap_y: '0px',
        text_column_count: 1,
        text_column_gap: '30px',
        text_column_rule_width: 0,
        text_column_rule_color: '#eeeeee',
        text_column_rule_style: 'solid',
        use_drop_cap: false,
        drop_cap_color: '',
        drop_cap_font_size: '',
        drop_cap_font_family: '',
        background: { color: '', image: '', repeat: 'no-repeat', position: 'center', size: 'cover' },
        padding: { top: 0, bottom: 0, left: 0, right: 0, unit: 'px' },
        margin: { top: 0, bottom: 0, left: 0, right: 0, unit: 'px' },
        border: { radius: { tl: 0, tr: 0, bl: 0, br: 0, linked: true }, styles: { all: { width: 0, color: '#333333', style: 'solid' } } },
        boxShadow: { preset: 'none', horizontal: 0, vertical: 0, blur: 0, spread: 0, color: 'rgba(0,0,0,0)', inset: false },
        width: '100%'
    },

    settings: {
        content: [
            {
                id: 'content',
                label: 'Content',
                fields: [
                    { name: 'showTitle', type: 'toggle', label: 'Show Title', default: false },
                    { name: 'title', type: 'text', label: 'Title', show_if: { field: 'showTitle', value: true } },
                    {
                        name: 'titleTag',
                        type: 'select',
                        label: 'Title Tag',
                        options: [
                            { value: 'h1', label: 'H1' },
                            { value: 'h2', label: 'H2' },
                            { value: 'h3', label: 'H3' },
                            { value: 'h4', label: 'H4' }
                        ],
                        default: 'h2',
                        show_if: { field: 'showTitle', value: true }
                    },
                    { name: 'content', type: 'richtext', label: 'Content' }
                ]
            },
            linkSettings,
            loopSettings,
            orderSettings,
            adminLabelSettings('Text')
        ],
        design: [
            layoutSettings,
            {
                id: 'text_columns',
                label: 'Text Columns',
                fields: [
                    {
                        name: 'text_column_count',
                        type: 'range',
                        label: 'Column Count',
                        min: 1,
                        max: 4,
                        step: 1,
                        default: 1,
                        responsive: true
                    },
                    {
                        name: 'text_column_gap',
                        type: 'dimension',
                        label: 'Column Gap',
                        default: '30px',
                        responsive: true,
                        show_if: { field: 'text_column_count', value: [2, 3, 4] }
                    },
                    {
                        name: 'text_column_rule',
                        type: 'group',
                        label: 'Column Rule / Line',
                        show_if: { field: 'text_column_count', value: [2, 3, 4] },
                        fields: [
                            { name: 'text_column_rule_width', type: 'range', label: 'Rule Width', min: 0, max: 10, unit: 'px' },
                            { name: 'text_column_rule_color', type: 'color', label: 'Rule Color' },
                            {
                                name: 'text_column_rule_style',
                                type: 'select',
                                label: 'Rule Style',
                                options: [
                                    { label: 'Solid', value: 'solid' },
                                    { label: 'Dashed', value: 'dashed' },
                                    { label: 'Dotted', value: 'dotted' },
                                    { label: 'Double', value: 'double' }
                                ]
                            }
                        ]
                    }
                ]
            },
            {
                id: 'text_styling',
                label: 'Text',
                fields: [
                    {
                        name: 'drop_cap',
                        type: 'group',
                        label: 'Drop Cap',
                        fields: [
                            { name: 'use_drop_cap', type: 'toggle', label: 'Enable Drop Cap' },
                            { name: 'drop_cap_color', type: 'color', label: 'Drop Cap Color', show_if: { field: 'use_drop_cap', value: true } },
                            { name: 'drop_cap_font_size', type: 'dimension', label: 'Drop Cap Font Size', show_if: { field: 'use_drop_cap', value: true } },
                            { name: 'drop_cap_font_family', type: 'font', label: 'Drop Cap Font Family', show_if: { field: 'use_drop_cap', value: true } }
                        ]
                    },
                    {
                        name: 'text_hover',
                        type: 'group',
                        label: 'Hover States',
                        fields: [
                            { name: 'hover_text_color', type: 'color', label: 'Hover Text Color', responsive: true }
                        ]
                    },
                    ...(typographySettings.fields as SettingDefinition[])
                ]
            },
            backgroundSettings,
            spacingSettings,
            borderSettings,
            boxShadowSettings,
            sizingSettings,
            filterSettings,
            transformSettings,
            animationSettings
        ],
        advanced: [
            visibilitySettings,
            positionSettings,
            transitionSettings,
            scrollEffectsSettings,
            interactionsSettings,
            conditionsSettings,
            attributesSettings,
            cssSettings
        ]
    }
};

export default TextModule;
