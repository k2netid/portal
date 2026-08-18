import type { ModuleDefinition, SettingDefinition, ModuleField } from '@/types/builder';
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
    adminLabelSettings,
    cssSettings,
    typographySettings,
    conditionsSettings,
    interactionsSettings,
    scrollEffectsSettings,
    attributesSettings,
    linkSettings,
    layoutSettings
} from '@/components/builder/modules/commonSettings';

/**
 * Heading Module Definition
 */
const HeadingModule: ModuleDefinition = {
    name: 'heading',
    title: 'Heading',
    icon: 'Heading',
    category: 'basic',

    children: null,

    defaults: {
        text: 'Your Heading Here',
        subtitle: '',
        subtitle_tag: 'div',
        tag: 'h2',
        html_id: '',
        aria_label: '',
        size: 'large',
        alignment: 'left',
        layout_type: 'block',
        gap_x: '0px',
        gap_y: '0px',
        use_gradient: false,
        background_clip_text: false,
        use_stroke: false,
        stroke_width: 1,
        stroke_color: '#000000',
        shadow_preset: 'none', // none, soft, hard, glow
        background: { color: '', image: '', repeat: 'no-repeat', position: 'center', size: 'cover' },
        padding: { top: 0, bottom: 0, left: 0, right: 0, unit: 'px' },
        margin: { top: 0, bottom: 10, left: 0, right: 0, unit: 'px' },
        border: { radius: { tl: 0, tr: 0, bl: 0, br: 0, linked: true }, styles: { all: { width: 0, color: '#333333', style: 'solid' } } },
        boxShadow: { preset: 'none', horizontal: 0, vertical: 0, blur: 0, spread: 0, color: 'rgba(0,0,0,0)', inset: false },
        width: '100%'
    },

    settings: {
        content: [
            {
                id: 'elements',
                label: 'Elements',
                fields: [
                    { name: 'text', type: 'text', label: 'Heading Text', responsive: true },
                    { name: 'subtitle', type: 'text', label: 'Subtitle', responsive: true },
                    {
                        name: 'tag', type: 'select', label: 'Heading Tag', options: [
                            { value: 'h1', label: 'H1' },
                            { value: 'h2', label: 'H2' },
                            { value: 'h3', label: 'H3' },
                            { value: 'h4', label: 'H4' },
                            { value: 'h5', label: 'H5' },
                            { value: 'h6', label: 'H6' },
                            { value: 'div', label: 'DIV' },
                            { value: 'p', label: 'P' },
                            { value: 'span', label: 'SPAN' }
                        ],
                        default: 'h2'
                    },
                    {
                        name: 'subtitle_tag', type: 'select', label: 'Subtitle Tag', options: [
                            { value: 'h3', label: 'H3' },
                            { value: 'h4', label: 'H4' },
                            { value: 'h5', label: 'H5' },
                            { value: 'h6', label: 'H6' },
                            { value: 'div', label: 'DIV' },
                            { value: 'p', label: 'P' },
                            { value: 'span', label: 'SPAN' }
                        ],
                        default: 'div'
                    },
                    { name: 'html_id', type: 'text', label: 'HTML ID / Anchor', placeholder: 'e.g. section-anchor' },
                    { name: 'aria_label', type: 'text', label: 'ARIA Label', placeholder: 'Description for screen readers' }
                ]
            },
            {
                id: 'link',
                label: 'Link',
                fields: [
                    {
                        name: 'use_link',
                        type: 'toggle',
                        label: 'Wrap Heading with Link'
                    },
                    {
                        ...(linkSettings.fields as ModuleField[])[0],
                        show_if: { field: 'use_link', value: true }
                    },
                    {
                        ...(linkSettings.fields as ModuleField[])[1],
                        show_if: { field: 'use_link', value: true }
                    },
                    {
                        name: 'link_rel',
                        type: 'select',
                        label: 'Rel',
                        multiple: true,
                        options: [
                            { label: 'NoFollow', value: 'nofollow' },
                            { label: 'NoReferrer', value: 'noreferrer' },
                            { label: 'Sponsored', value: 'sponsored' }
                        ],
                        show_if: { field: 'use_link', value: true }
                    }
                ]
            },
            adminLabelSettings('Heading')
        ],
        design: [
            layoutSettings,
            {
                id: 'heading_styling',
                label: 'Premium Styling',
                fields: [
                    {
                        name: 'size', type: 'select', label: 'Heading Size', responsive: true, options: [
                            { value: 'small', label: 'Small' },
                            { value: 'medium', label: 'Medium' },
                            { value: 'large', label: 'Large' },
                            { value: 'xlarge', label: 'Extra Large' },
                            { value: 'display', label: 'Display' }
                        ]
                    },
                    { name: 'background_clip_text', type: 'toggle', label: 'Background Clip Text', description: 'Uses module background as text fill' },
                    {
                        name: 'text_effects',
                        type: 'group',
                        label: 'Text Effects',
                        fields: [
                            { name: 'use_gradient', type: 'toggle', label: 'Enable Text Gradient' },
                            { name: 'gradient', type: 'gradient', label: 'Text Gradient', show_if: { field: 'use_gradient', value: true } },
                            { name: 'use_stroke', type: 'toggle', label: 'Enable Text Stroke' },
                            { name: 'stroke_width', type: 'range', label: 'Stroke Width', min: 0, max: 10, unit: 'px', show_if: { field: 'use_stroke', value: true } },
                            { name: 'stroke_color', type: 'color', label: 'Stroke Color', show_if: { field: 'use_stroke', value: true } },
                            {
                                name: 'shadow_preset', type: 'select', label: 'Shadow Preset', options: [
                                    { value: 'none', label: 'None' },
                                    { value: 'soft', label: 'Soft Depth' },
                                    { value: 'hard', label: 'Hard Shadow' },
                                    { value: 'glow', label: 'Neon Glow' }
                                ]
                            }
                        ]
                    },
                    {
                        name: 'hover_states',
                        type: 'group',
                        label: 'Hover States',
                        fields: [
                            { name: 'hover_text_color', type: 'color', label: 'Hover Text Color', responsive: true },
                            { name: 'hover_use_gradient', type: 'toggle', label: 'Hover Gradient' },
                            { name: 'hover_gradient', type: 'gradient', label: 'Hover Gradient', show_if: { field: 'hover_use_gradient', value: true } }
                        ]
                    }
                ]
            },
            {
                id: 'typography',
                label: 'Typography',
                fields: (typographySettings.fields as SettingDefinition[]).map(f => ({
                    ...f,
                    name: f.name === 'text_align' ? 'alignment' : f.name
                }))
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

export default HeadingModule;
