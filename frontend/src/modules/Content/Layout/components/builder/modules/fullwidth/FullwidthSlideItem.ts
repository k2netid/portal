import type { ModuleDefinition, SettingDefinition } from '@/types/builder';
import {
    adminLabelSettings,
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
    attributesSettings
} from '@/components/builder/modules/commonSettings';

/**
 * Fullwidth Slide Item Module Definition
 */
const FullwidthSlideItemModule: ModuleDefinition = {
    name: 'fullwidthslide_item',
    title: 'Fullwidth Slide',
    icon: 'Image',
    category: 'internal',

    children: null,

    defaults: {
        title: 'New Slide',
        subtitle: 'Slide Subtitle',
        content: '',
        image: '',
        buttonText: 'Click Here',
        buttonUrl: '#',
        // Background
        background: { color: '', image: '', repeat: 'no-repeat', position: 'center', size: 'cover' },
        // Spacing
        padding: { top: 0, bottom: 0, left: 0, right: 0, unit: 'px' },
        margin: { top: 0, bottom: 0, left: 0, right: 0, unit: 'px' },
        border: { radius: { tl: 0, tr: 0, bl: 0, br: 0, linked: true }, styles: { all: { width: 0, color: '#333333', style: 'solid' }, top: { width: 0, color: '#333333', style: 'solid' }, right: { width: 0, color: '#333333', style: 'solid' }, bottom: { width: 0, color: '#333333', style: 'solid' }, left: { width: 0, color: '#333333', style: 'solid' } } },
        boxShadow: { preset: 'none', horizontal: 0, vertical: 0, blur: 0, spread: 0, color: 'rgba(0,0,0,0)', inset: false },
        aria_label: '',
        html_id: '',
        animation_effect: '', animation_duration: 1000, animation_delay: 0, animation_repeat: '1'
    },

    settings: {
        content: [
            {
                id: 'main',
                label: 'Slide Content',
                fields: [
                    { name: 'title', type: 'text', label: 'Title' },
                    { name: 'subtitle', type: 'text', label: 'Subtitle' },
                    { name: 'content', type: 'richtext', label: 'Body Content' },
                    { name: 'buttonText', type: 'text', label: 'Button Text' },
                    { name: 'buttonUrl', type: 'text', label: 'Button URL' }
                ]
            },
            backgroundSettings,
            adminLabelSettings('Slide')
        ],
        design: [
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

export default FullwidthSlideItemModule;
