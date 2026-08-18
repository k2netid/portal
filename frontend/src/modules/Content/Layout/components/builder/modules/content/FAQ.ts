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
 * FAQ Module Definition
 */
const FAQModule: ModuleDefinition = {
    name: 'faq',
    title: 'FAQ',
    icon: 'HelpCircle',
    category: 'content',

    children: null,

    defaults: {
        variant: 'boxed',
        allowMultiple: false,
        items: [
            { question: 'How quickly can I launch my site?', answer: 'With our pre-built modules and instant edge delivery, you can launch a production-ready site in minutes, not days.' },
            { question: 'Is there a limit on bandwidth?', answer: 'We offer unlimited global bandwidth on all our premium plans, ensuring your site stays fast no matter the traffic.' },
            { question: 'Can I export the source code?', answer: 'Absolutely. You have full access to export clean, production-grade Vue/React code at any time.' }
        ],
        accentColor: '#4f46e5',
        activeBgColor: 'rgba(79, 70, 229, 0.05)',
        // Background
        background: { color: 'transparent', image: '', repeat: 'no-repeat', position: 'center', size: 'cover' },
        // Spacing
        padding: { top: 20, bottom: 20, left: 0, right: 0, unit: 'px' },
        margin: { top: 0, bottom: 0, left: 0, right: 0, unit: 'px' },
        // Border
        border: {
            radius: { tl: 24, tr: 24, bl: 24, br: 24, linked: true },
            styles: {
                all: { width: 0, color: '#e0e0e0', style: 'solid' }
            }
        },
        boxShadow: { preset: 'medium', horizontal: 0, vertical: 10, blur: 30, spread: -5, color: 'rgba(0,0,0,0.05)', inset: false },
        aria_label: '',
        html_id: '',
        hover_scale: 1,
        hover_brightness: 100,
        animation_effect: 'animate-fade-up', animation_duration: 1000, animation_delay: 0, animation_repeat: '1'
    },

    settings: {
        content: [
            {
                id: 'items',
                label: 'FAQ Items',
                fields: [
                    {
                        name: 'items',
                        type: 'repeater',
                        label: 'FAQ Items',
                        itemLabel: 'Question',
                        fields: [
                            { name: 'question', type: 'text', label: 'Question' },
                            { name: 'answer', type: 'textarea', label: 'Answer' }
                        ]
                    }
                ]
            },
            {
                ...layoutSettings,
                fields: [
                    ...layoutSettings.fields!,
                    { name: 'layout', type: 'select', label: 'Display Mode', responsive: true, options: [{ value: 'accordion', label: 'Accordion' }, { value: 'list', label: 'List (Always Open)' }] },
                    { name: 'allowMultiple', type: 'toggle', label: 'Allow Multiple Open', responsive: true }
                ]
            },
            {
                id: 'premium_interactive',
                label: 'Interactive States',
                fields: [
                    { name: 'hover_scale', type: 'range', label: 'Item Hover Scale', min: 0.8, max: 1.5, step: 0.05, default: 1 },
                    { name: 'hover_brightness', type: 'range', label: 'Item Hover Brightness', min: 50, max: 150, step: 10, unit: '%', default: 100 }
                ]
            },
            backgroundSettings,
            adminLabelSettings('FAQ')
        ],
        design: [
            {
                id: 'questionTypography',
                label: 'Question Typography',
                fields: (typographySettings.fields as SettingDefinition[]).map(f => ({
                    ...f,
                    name: `question_${f.name}`,
                    label: `Question ${f.label}`
                }))
            },
            {
                id: 'answerTypography',
                label: 'Answer Typography',
                fields: (typographySettings.fields as SettingDefinition[]).map(f => ({
                    ...f,
                    name: `answer_${f.name}`,
                    label: `Answer ${f.label}`
                }))
            },
            {
                id: 'faqStyle',
                label: 'FAQ Style',
                fields: [
                    { name: 'accentColor', type: 'color', label: 'Icon Color', responsive: true },
                    { name: 'itemBorderColor', type: 'color', label: 'Item Border Color', responsive: true }
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
};

export default FAQModule;
