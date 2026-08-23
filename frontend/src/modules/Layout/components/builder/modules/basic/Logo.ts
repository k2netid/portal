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
    conditionsSettings,
    interactionsSettings,
    scrollEffectsSettings,
    attributesSettings,
    adminLabelSettings,
    layoutSettings,
    linkSettings
} from '@/components/builder/modules/commonSettings';

/**
 * Logo Module Definition
 */
const LogoModule: ModuleDefinition = {
    name: 'logo',
    title: 'Logo',
    icon: 'Image',
    category: 'basic',

    children: null,

    defaults: {
        image: '',
        altText: 'Logo',
        link: '/',
        openInNewTab: false,
        maxWidth: 200,
        height: 'auto',
        alignment: 'left',
        layout_type: 'block',
        gap_x: '0px',
        gap_y: '0px',
        aria_label: '',
        html_id: '',
        hover_scale: 1.05,
        hover_opacity: 1,
        background: { color: '', image: '', repeat: 'no-repeat', position: 'center', size: 'cover' },
        padding: { top: 0, bottom: 0, left: 0, right: 0, unit: 'px' },
        margin: { top: 0, bottom: 0, left: 0, right: 0, unit: 'px' },
        border: { radius: { tl: 0, tr: 0, bl: 0, br: 0, linked: true }, styles: { all: { width: 0, color: '#333333', style: 'solid' } } },
        boxShadow: { preset: 'none', horizontal: 0, vertical: 0, blur: 0, spread: 0, color: 'rgba(0,0,0,0)', inset: false }
    },

    settings: {
        content: [
            {
                id: 'logo',
                label: 'Logo',
                fields: [
                    { name: 'image', type: 'upload', label: 'Logo Image' },
                    { name: 'altText', type: 'text', label: 'Alt Text' },
                    { name: 'aria_label', type: 'text', label: 'ARIA Label' },
                    { name: 'html_id', type: 'text', label: 'HTML ID' }
                ]
            },
            linkSettings,
            backgroundSettings,
            adminLabelSettings('Logo')
        ],
        design: [
            layoutSettings,
            {
                id: 'size',
                label: 'Size & Interactive',
                fields: [
                    { name: 'maxWidth', type: 'range', label: 'Max Width', min: 50, max: 400, step: 10, unit: 'px', responsive: true },
                    {
                        name: 'alignment', type: 'buttonGroup', label: 'Alignment', responsive: true, options: [
                            { value: 'left', label: 'Left', icon: 'AlignLeft' },
                            { value: 'center', label: 'Center', icon: 'AlignCenter' },
                            { value: 'right', label: 'Right', icon: 'AlignRight' }
                        ]
                    },
                    {
                        name: 'hover_effects',
                        type: 'group',
                        label: 'Hover Effects',
                        fields: [
                            { name: 'hover_scale', type: 'range', label: 'Hover Scale', min: 0.8, max: 1.5, step: 0.05, default: 1.05 },
                            { name: 'hover_opacity', type: 'range', label: 'Hover Opacity', min: 0, max: 1, step: 0.1, default: 1 }
                        ]
                    }
                ]
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
}

export default LogoModule;
