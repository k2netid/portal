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
 * Star Rating Module Definition
 */
const StarRatingModule: ModuleDefinition = {
    name: 'starrating',
    title: 'Star Rating',
    icon: 'Star',
    category: 'content',

    children: null,

    defaults: {
        rating: 4.5,
        maxRating: 5,
        showNumber: true,
        showReviewCount: true,
        reviewCount: 128,
        reviewText: 'reviews',
        // Styling
        starColor: '#f59e0b',
        emptyStarColor: '#e0e0e0',
        starSize: 24,
        // Background
        background: { color: '', image: '', repeat: 'no-repeat', position: 'center', size: 'cover' },
        // Spacing
        padding: { top: 0, bottom: 0, left: 0, right: 0, unit: 'px' },
        margin: { top: 0, bottom: 0, left: 0, right: 0, unit: 'px' },
        // Border
        border: {
            radius: { tl: 0, tr: 0, bl: 0, br: 0, linked: true },
            styles: {
                all: { width: 0, color: '#333333', style: 'solid' },
                top: { width: 0, color: '#333333', style: 'solid' },
                right: { width: 0, color: '#333333', style: 'solid' },
                bottom: { width: 0, color: '#333333', style: 'solid' },
                left: { width: 0, color: '#333333', style: 'solid' }
            }
        },
        boxShadow: { preset: 'none', horizontal: 0, vertical: 0, blur: 0, spread: 0, color: 'rgba(0,0,0,0)', inset: false },
        aria_label: '',
        html_id: '',
        animation_effect: '', animation_duration: 1000, animation_delay: 0, animation_repeat: '1'
    },

    settings: {
        content: [
            {
                id: 'rating',
                label: 'Rating',
                fields: [
                    { name: 'rating', type: 'range', label: 'Rating', min: 0, max: 5, step: 0.5, responsive: true },
                    { name: 'maxRating', type: 'range', label: 'Max Stars', min: 3, max: 10, step: 1, responsive: true },
                    { name: 'showNumber', type: 'toggle', label: 'Show Rating Number', responsive: true },
                    { name: 'showReviewCount', type: 'toggle', label: 'Show Review Count', responsive: true },
                    { name: 'reviewCount', type: 'text', label: 'Review Count', responsive: true },
                    { name: 'reviewText', type: 'text', label: 'Review Text', responsive: true },
                    { name: 'aria_label', type: 'text', label: 'ARIA Label' },
                    { name: 'html_id', type: 'text', label: 'HTML ID' }
                ]
            },
            backgroundSettings,
            adminLabelSettings('Star Rating')
        ],
        design: [
            {
                ...layoutSettings,
                fields: [
                    ...layoutSettings.fields!,
                    { name: 'starSize', type: 'range', label: 'Star Size', min: 16, max: 80, step: 2, unit: 'px', responsive: true },
                    { name: 'starColor', type: 'color', label: 'Star Color', responsive: true },
                    { name: 'emptyStarColor', type: 'color', label: 'Empty Star Color', responsive: true },
                    {
                        name: 'alignment',
                        type: 'buttonGroup',
                        label: 'Alignment',
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
                id: 'textTypography',
                label: 'Text Typography',
                fields: (typographySettings.fields as SettingDefinition[]).map(f => ({
                    ...f,
                    name: `text_${f.name}`,
                    label: `Text ${f.label}`
                }))
            },
            {
                id: 'reviewTypography',
                label: 'Review Info Typography',
                fields: (typographySettings.fields as SettingDefinition[]).map(f => ({
                    ...f,
                    name: `review_${f.name}`,
                    label: `Review Info ${f.label}`
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

export default StarRatingModule;
