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
 * Hero Module Definition
 */
const HeroModule: ModuleDefinition = {
    name: 'hero',
    title: 'Hero / Header',
    icon: 'Layout',
    category: 'content',

    children: null,

    defaults: {
        eyebrow: 'New Era of Design',
        title: 'Build the Future of the Web\nwith Infinite Precision.',
        subtitle: 'The world\'s most flexible visual builder for creators who demand pixel-perfect execution.',
        content: '',
        image: '', // For split layout
        // Buttons (Deprecated but kept for compat)
        buttonText: 'Get Started for Free',
        buttonUrl: '#',
        button2Text: 'Learn More',
        button2Url: '#',
        showButton1: true,
        showButton2: true,
        // Layout
        layout: 'centered',
        useGlass: false,
        verticalAlign: 'center',
        minHeight: 700,
        alignment: 'center',
        contentMaxWidth: 1200,
        layout_type: 'block',
        gap_x: '0px',
        gap_y: '20px',
        aria_label: '',
        html_id: '',
        hover_scale: 1,
        hover_brightness: 100,
        // Background - Modified to support gradient keys
        gradientStart: '#4f46e5',
        gradientEnd: '#7c3aed',
        gradientDirection: 'to bottom right',
        overlayColor: 'rgba(0, 0, 0, 0.4)',
        overlayOpacity: 40,
        background: { color: 'transparent', image: '', repeat: 'no-repeat', position: 'center', size: 'cover' },
        // Spacing
        padding: { top: 120, bottom: 120, left: 24, right: 24, unit: 'px' },
        margin: { top: 0, bottom: 0, left: 0, right: 0, unit: 'px' },

        // Title Typography Defaults
        title_font_size: 72,
        title_font_weight: '900',
        title_color: '#ffffff',
        title_text_shadow: { horizontal: 0, vertical: 4, blur: 20, color: 'rgba(0,0,0,0.2)' },
        title_tag: 'h1',

        // Subtitle Typography Defaults
        subtitle_font_size: 22,
        subtitle_color: 'rgba(255, 255, 255, 0.9)',
        subtitle_max_width: 800,

        // Border
        border: {
            radius: { tl: 0, tr: 0, bl: 0, br: 0, linked: true },
            styles: {
                all: { width: 0, color: '#333333', style: 'solid' }
            }
        },
        animation_effect: 'animate-fade-up',
        animation_duration: 1000,
        animation_delay: 0,
        animation_repeat: '1'
    },

    settings: {
        content: [
            {
                id: 'text',
                label: 'Text',
                fields: [
                    { name: 'eyebrow', type: 'text', label: 'Eyebrow / Badge' },
                    { name: 'title', type: 'text', label: 'Title' },
                    { name: 'subtitle', type: 'text', label: 'Subtitle' },
                    { name: 'content', type: 'richtext', label: 'Content' },
                    { name: 'aria_label', type: 'text', label: 'ARIA Label' },
                    { name: 'html_id', type: 'text', label: 'HTML ID' }
                ]
            },
            {
                id: 'layout_content',
                label: 'Layout & Media',
                fields: [
                    {
                        name: 'layout',
                        type: 'select',
                        label: 'Layout Variant',
                        options: [
                            { value: 'centered', label: 'Centered' },
                            { value: 'split', label: 'Split (Image Right)' }
                        ]
                    },
                    { name: 'image', type: 'upload', label: 'Split Image', show_if: { field: 'layout', value: 'split' } },
                    { name: 'useGlass', type: 'toggle', label: 'Use Glassmorphism' }
                ]
            },
            {
                id: 'buttons',
                label: 'Legacy Buttons',
                fields: [
                    { name: 'showButton1', type: 'toggle', label: 'Show Primary Button' },
                    { name: 'buttonText', type: 'text', label: 'Primary Text', show_if: { field: 'showButton1', value: true } },
                    { name: 'buttonUrl', type: 'text', label: 'Primary URL', show_if: { field: 'showButton1', value: true } },
                    { name: 'showButton2', type: 'toggle', label: 'Show Secondary Button' },
                    { name: 'button2Text', type: 'text', label: 'Secondary Text', show_if: { field: 'showButton2', value: true } },
                    { name: 'button2Url', type: 'text', label: 'Secondary URL', show_if: { field: 'showButton2', value: true } }
                ]
            },
            backgroundSettings,
            adminLabelSettings('Hero / Header')
        ],
        design: [
            {
                ...layoutSettings,
                fields: [
                    ...layoutSettings.fields!,
                    { name: 'minHeight', type: 'range', label: 'Min Height', min: 400, max: 1000, step: 50, unit: 'px', responsive: true },
                    { name: 'contentMaxWidth', type: 'range', label: 'Content Max Width', min: 400, max: 1600, step: 50, unit: 'px', responsive: true },
                    {
                        name: 'verticalAlign',
                        type: 'select',
                        label: 'Vertical Alignment',
                        options: [
                            { value: 'start', label: 'Top' },
                            { value: 'center', label: 'Center' },
                            { value: 'end', label: 'Bottom' }
                        ]
                    },
                    {
                        name: 'alignment',
                        type: 'buttonGroup',
                        label: 'Text Alignment',
                        options: [
                            { value: 'left', label: 'Left', icon: 'AlignLeft' },
                            { value: 'center', label: 'Center', icon: 'AlignCenter' },
                            { value: 'right', label: 'Right', icon: 'AlignRight' }
                        ],
                        responsive: true
                    }
                ]
            },
            {
                id: 'premium_interactive',
                label: 'Interactive States',
                fields: [
                    { name: 'hover_scale', type: 'range', label: 'Hover Scale', min: 0.8, max: 1.5, step: 0.05, default: 1 },
                    { name: 'hover_brightness', type: 'range', label: 'Hover Brightness', min: 50, max: 150, step: 10, unit: '%', default: 100 }
                ]
            },
            {
                id: 'titleTypography',
                label: 'Title Typography',
                fields: (typographySettings.fields as SettingDefinition[]).map(f => ({
                    ...f,
                    name: `title_${f.name}`,
                    label: `Title ${f.label}`
                }))
            },
            {
                id: 'subtitleTypography',
                label: 'Subtitle Typography',
                fields: (typographySettings.fields as SettingDefinition[]).map(f => ({
                    ...f,
                    name: `subtitle_${f.name}`,
                    label: `Subtitle ${f.label}`
                }))
            },
            {
                id: 'bodyTypography',
                label: 'Body Typography',
                fields: (typographySettings.fields as SettingDefinition[]).map(f => ({
                    ...f,
                    name: `body_${f.name}`,
                    label: `Body ${f.label}`
                }))
            },
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
            cssSettings,
            conditionsSettings,
            interactionsSettings,
            scrollEffectsSettings,
            attributesSettings
        ]
    }
};

export default HeroModule;
