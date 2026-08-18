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
    adminLabelSettings
} from '@/components/builder/modules/commonSettings';

/**
 * Pricing Tables Module Definition
 */
const PricingTablesModule: ModuleDefinition = {
    name: 'pricingtable', // Keep name for now to avoid breaking existing instances
    title: 'Pricing Tables',
    icon: 'CreditCard',
    category: 'content',

    children: null,

    defaults: {
        items: [
            {
                title: 'Starter',
                price: '29',
                currency: '$',
                period: '/mo',
                buttonText: 'Start Free',
                buttonUrl: '#',
                features: 'Standard Support\n1 Business Website\n10GB Storage',
                isFeatured: false
            },
            {
                title: 'Professional',
                price: '99',
                currency: '$',
                period: '/mo',
                buttonText: 'Go Pro',
                buttonUrl: '#',
                features: 'Priority Support\n5 Business Websites\n50GB Storage\nCustom Domains',
                isFeatured: true
            },
            {
                title: 'Enterprise',
                price: '299',
                currency: '$',
                period: '/mo',
                buttonText: 'Contact Us',
                buttonUrl: '#',
                features: '24/7 Support\nUnlimited Websites\n500GB Storage\nAdvanced Security',
                isFeatured: false
            }
        ],
        columns: 3,
        gap: 32,
        // Card Styling
        cardBackgroundColor: '#ffffff',
        featuredCardBackgroundColor: '#ffffff',
        accentColor: '#4f46e5',
        // Background
        background: { color: 'transparent', image: '', repeat: 'no-repeat', position: 'center', size: 'cover' },
        // Spacing
        padding: { top: 80, bottom: 80, left: 24, right: 24, unit: 'px' },
        margin: { top: 0, bottom: 0, left: 0, right: 0, unit: 'px' },
        border: { radius: { tl: 40, tr: 40, bl: 40, br: 40, linked: true }, styles: { all: { width: 0, color: '#e0e0e0', style: 'solid' } } },
        boxShadow: { preset: 'high', horizontal: 0, vertical: 20, blur: 40, spread: -5, color: 'rgba(0,0,0,0.1)', inset: false },
        aria_label: '',
        html_id: '',
        hover_scale: 1.02,
        hover_brightness: 100,
        animation_effect: 'animate-fade-up', animation_duration: 1000, animation_delay: 0, animation_repeat: '1'
    },

    settings: {
        content: [
            {
                id: 'items',
                label: 'Pricing Plans',
                fields: [
                    {
                        name: 'items',
                        type: 'repeater',
                        label: 'Plans',
                        itemLabel: 'title',
                        fields: [
                            { name: 'title', type: 'text', label: 'Plan Title' },
                            { name: 'price', type: 'text', label: 'Price' },
                            { name: 'currency', type: 'text', label: 'Currency Symbol' },
                            { name: 'period', type: 'text', label: 'Period' },
                            { name: 'buttonText', type: 'text', label: 'Button Text' },
                            { name: 'buttonUrl', type: 'text', label: 'Button URL' },
                            { name: 'features', type: 'textarea', label: 'Features (one per line)' },
                            { name: 'isFeatured', type: 'toggle', label: 'Featured Plan' }
                        ]
                    }
                ]
            },
            backgroundSettings,
            {
                id: 'premium_interactive',
                label: 'Interactive States',
                fields: [
                    { name: 'hover_scale', type: 'range', label: 'Card Hover Scale', min: 0.8, max: 1.2, step: 0.01, default: 1.02 },
                    { name: 'hover_brightness', type: 'range', label: 'Card Hover Brightness', min: 50, max: 150, step: 10, unit: '%', default: 100 }
                ]
            },
            adminLabelSettings('Pricing Tables')
        ],
        design: [
            {
                id: 'layout',
                label: 'Layout',
                fields: [
                    { name: 'columns', type: 'range', label: 'Columns', min: 1, max: 4, step: 1, responsive: true },
                    { name: 'gap', type: 'range', label: 'Gap', min: 0, max: 80, step: 4, unit: 'px', responsive: true }
                ]
            },
            {
                id: 'style',
                label: 'Card Style',
                fields: [
                    { name: 'accentColor', type: 'color', label: 'Accent Color', responsive: true },
                    { name: 'cardBackgroundColor', type: 'color', label: 'Card Background', responsive: true },
                    { name: 'featuredCardBackgroundColor', type: 'color', label: 'Featured Card Background', responsive: true }
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
                id: 'priceTypography',
                label: 'Price Typography',
                fields: (typographySettings.fields as SettingDefinition[]).map(f => ({
                    ...f,
                    name: `price_${f.name}`,
                    label: `Price ${f.label}`
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

export default PricingTablesModule;
