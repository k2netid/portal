import type { ModuleDefinition } from '@/types/builder';
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
    conditionsSettings,
    interactionsSettings,
    scrollEffectsSettings,
    attributesSettings
} from '@/components/builder/modules/commonSettings';

/**
 * Share Network Module Definition
 */
const ShareNetworkModule: ModuleDefinition = {
    name: 'share_network',
    title: 'Network',
    icon: 'Share2',
    category: 'internal',

    children: null,

    defaults: {
        network: 'facebook',
        label: 'Share',
        customLabel: '',
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
                id: 'main',
                label: 'Network',
                fields: [
                    {
                        name: 'network',
                        type: 'select',
                        label: 'Network',
                        options: [
                            { value: 'facebook', label: 'Facebook', icon: 'Facebook' },
                            { value: 'twitter', label: 'Twitter / X', icon: 'Twitter' },
                            { value: 'linkedin', label: 'LinkedIn', icon: 'Linkedin' },
                            { value: 'pinterest', label: 'Pinterest' },
                            { value: 'reddit', label: 'Reddit' },
                            { value: 'whatsapp', label: 'WhatsApp' },
                            { value: 'email', label: 'Email', icon: 'Mail' }
                        ]
                    },
                    { name: 'customLabel', type: 'text', label: 'Custom Label', description: 'Overrides default network name.' },
                    { name: 'aria_label', type: 'text', label: 'ARIA Label' },
                    { name: 'html_id', type: 'text', label: 'HTML ID' }
                ]
            },
            backgroundSettings,
            adminLabelSettings('Network')
        ],
        design: [
            spacingSettings,
            borderSettings,
            boxShadowSettings,
            sizingSettings,
            filterSettings,
            transformSettings,
            animationSettings
        ],
        advanced: [
            conditionsSettings,
            interactionsSettings,
            scrollEffectsSettings,
            attributesSettings
        ]
    }
};

export default ShareNetworkModule;
