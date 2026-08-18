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
    adminLabelSettings,
    conditionsSettings,
    interactionsSettings,
    scrollEffectsSettings,
    attributesSettings,
    layoutSettings
} from '@/components/builder/modules/commonSettings';

/**
 * Team Member Module Definition
 */
const TeamMemberModule: ModuleDefinition = {
    name: 'teammember',
    title: 'Team Member',
    icon: 'User',
    category: 'content',

    children: null,

    defaults: {
        name: 'John Doe',
        position: 'CEO & Founder',
        bio: 'A short bio about the team member goes here.',
        image: '',
        socialLinks: [
            { network: 'twitter', url: '#', useCustomColor: false },
            { network: 'linkedin', url: '#', useCustomColor: false }
        ],
        // Layout
        layout: 'stacked',
        alignment: 'center',
        // Background
        background: { color: '#ffffff', image: '', repeat: 'no-repeat', position: 'center', size: 'cover' },
        // Image
        imageSize: 150,
        imageBorderRadius: 50,
        // Spacing
        padding: { top: 24, bottom: 24, left: 24, right: 24, unit: 'px' },
        margin: { top: 0, bottom: 0, left: 0, right: 0, unit: 'px' },
        // Border
        border: {
            radius: { tl: 8, tr: 8, bl: 8, br: 8, linked: true },
            styles: {
                all: { width: 0, color: '#e0e0e0', style: 'solid' },
                top: { width: 0, color: '#e0e0e0', style: 'solid' },
                right: { width: 0, color: '#e0e0e0', style: 'solid' },
                bottom: { width: 0, color: '#e0e0e0', style: 'solid' },
                left: { width: 0, color: '#e0e0e0', style: 'solid' }
            }
        },
        boxShadow: { preset: 'none', horizontal: 0, vertical: 0, blur: 0, spread: 0, color: 'rgba(0,0,0,0)', inset: false },
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
                id: 'info',
                label: 'Information',
                fields: [
                    {
                        name: 'name',
                        type: 'text',
                        label: 'Name',
                        responsive: true
                    },
                    {
                        name: 'position',
                        type: 'text',
                        label: 'Position',
                        responsive: true
                    },
                    {
                        name: 'bio',
                        type: 'textarea',
                        label: 'Bio',
                        responsive: true
                    },
                    {
                        name: 'image',
                        type: 'upload',
                        label: 'Photo',
                        responsive: true
                    },
                    { name: 'aria_label', type: 'text', label: 'ARIA Label' },
                    { name: 'html_id', type: 'text', label: 'HTML ID' }
                ]
            },
            {
                id: 'social',
                label: 'Social Links',
                fields: [
                    {
                        name: 'socialLinks',
                        type: 'repeater',
                        label: 'Social Links',
                        itemLabel: 'network',
                        fields: [
                            {
                                name: 'network',
                                type: 'select',
                                label: 'Network',
                                options: [
                                    { value: 'facebook', label: 'Facebook', icon: 'Facebook' },
                                    { value: 'twitter', label: 'Twitter / X', icon: 'Twitter' },
                                    { value: 'instagram', label: 'Instagram', icon: 'Instagram' },
                                    { value: 'linkedin', label: 'LinkedIn', icon: 'Linkedin' },
                                    { value: 'youtube', label: 'YouTube', icon: 'Youtube' },
                                    { value: 'email', label: 'Email', icon: 'Mail' },
                                    { value: 'website', label: 'Website', icon: 'Globe' }
                                ]
                            },
                            { name: 'url', type: 'text', label: 'Link URL' },
                            { name: 'useCustomColor', type: 'toggle', label: 'Use Custom Colors' },
                            {
                                name: 'iconColor',
                                type: 'color',
                                label: 'Icon Color',
                                show_if: { field: 'useCustomColor', value: true }
                            },
                            {
                                name: 'backgroundColor',
                                type: 'color',
                                label: 'Background Color',
                                show_if: { field: 'useCustomColor', value: true }
                            }
                        ]
                    }
                ]
            },
            backgroundSettings,
            adminLabelSettings('Team Member')
        ],
        design: [
            {
                id: 'layout',
                label: 'Layout',
                fields: [
                    {
                        name: 'layout',
                        type: 'select',
                        label: 'Layout',
                        responsive: true,
                        options: [
                            { value: 'stacked', label: 'Stacked' },
                            { value: 'horizontal', label: 'Horizontal' }
                        ]
                    },
                    {
                        name: 'alignment',
                        type: 'buttonGroup',
                        label: 'Alignment',
                        responsive: true,
                        options: [
                            { value: 'left', label: 'Left', icon: 'AlignLeft' },
                            { value: 'center', label: 'Center', icon: 'AlignCenter' },
                            { value: 'right', label: 'Right', icon: 'AlignRight' }
                        ]
                    }
                ]
            },
            {
                id: 'image',
                label: 'Image',
                fields: [
                    {
                        name: 'imageSize',
                        type: 'range',
                        label: 'Image Size',
                        min: 80,
                        max: 250,
                        step: 10,
                        unit: 'px',
                        responsive: true
                    },
                    {
                        name: 'imageBorderRadius',
                        type: 'range',
                        label: 'Border Radius',
                        min: 0,
                        max: 50,
                        step: 5,
                        unit: '%',
                        responsive: true
                    }
                ]
            },
            {
                id: 'nameTypography',
                label: 'Name Typography',
                fields: (typographySettings.fields as SettingDefinition[]).map(f => ({
                    ...f,
                    name: `name_${f.name}`,
                    label: `Name ${f.label}`
                }))
            },
            {
                id: 'positionTypography',
                label: 'Position Typography',
                fields: (typographySettings.fields as SettingDefinition[]).map(f => ({
                    ...f,
                    name: `position_${f.name}`,
                    label: `Position ${f.label}`
                }))
            },
            {
                id: 'bioTypography',
                label: 'Bio Typography',
                fields: (typographySettings.fields as SettingDefinition[]).map(f => ({
                    ...f,
                    name: `bio_${f.name}`,
                    label: `Bio ${f.label}`
                }))
            },
            spacingSettings,
            borderSettings,
            boxShadowSettings,
            sizingSettings,
            filterSettings,
            transformSettings,
            animationSettings,
            layoutSettings
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

export default TeamMemberModule;
