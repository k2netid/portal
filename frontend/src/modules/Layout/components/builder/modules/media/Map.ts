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
    layoutSettings,
    adminLabelSettings,
} from '@/components/builder/modules/commonSettings';

/**
 * Map Module Definition
 */
const MapModule: ModuleDefinition = {
    name: 'map',
    title: 'Map',
    icon: 'MapPin',
    category: 'media',

    children: null,

    defaults: {
        address: 'New York, NY, USA',
        embedUrl: '',
        zoom: 14,
        mapType: 'roadmap',
        // Size
        height: 400,
        // Marker
        showMarker: true,
        markerTitle: 'Our Location',
        // Controls
        showZoomControl: true,
        showStreetView: false,
        draggable: true,
        // Styling
        grayscale: false,
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
        hover_scale: 1,
        hover_brightness: 100,

        animation_effect: '',
        animation_duration: 1000,
        animation_delay: 0,
        animation_repeat: '1'
    },

    settings: {
        content: [
            {
                id: 'location',
                label: 'Location',
                fields: [
                    {
                        name: 'address',
                        type: 'text',
                        label: 'Address (City, State, or Zip)'
                    },
                    {
                        name: 'embedUrl',
                        type: 'text',
                        label: 'Custom Embed URL (Optional override)'
                    }
                ]
            },
            {
                id: 'marker',
                label: 'Marker',
                fields: [
                    {
                        name: 'showMarker',
                        type: 'toggle',
                        label: 'Show Marker',
                        responsive: true
                    },
                    {
                        name: 'markerTitle',
                        type: 'text',
                        label: 'Marker Title',
                        responsive: true,
                        show_if: { field: 'showMarker', value: true }
                    }
                ]
            },
            backgroundSettings,
            layoutSettings,
            adminLabelSettings('Map')
        ],
        design: [
            {
                id: 'mapSettings',
                label: 'Map Settings',
                fields: [
                    {
                        name: 'zoom',
                        type: 'range',
                        label: 'Zoom Level',
                        min: 1,
                        max: 20,
                        step: 1,
                        responsive: true
                    },
                    {
                        name: 'mapType',
                        type: 'select',
                        label: 'Map Type',
                        options: [
                            { value: 'roadmap', label: 'Roadmap' },
                            { value: 'satellite', label: 'Satellite' },
                            { value: 'hybrid', label: 'Hybrid' },
                            { value: 'terrain', label: 'Terrain' }
                        ]
                    },
                    {
                        name: 'height',
                        type: 'range',
                        label: 'Height',
                        min: 200,
                        max: 800,
                        step: 50,
                        unit: 'px',
                        responsive: true
                    }
                ]
            },
            {
                id: 'controls',
                label: 'Controls',
                fields: [
                    {
                        name: 'showZoomControl',
                        type: 'toggle',
                        label: 'Zoom Control',
                        responsive: true
                    },
                    {
                        name: 'showStreetView',
                        type: 'toggle',
                        label: 'Street View',
                        responsive: true
                    },
                    {
                        name: 'draggable',
                        type: 'toggle',
                        label: 'Draggable',
                        responsive: true
                    },
                    {
                        name: 'grayscale',
                        type: 'toggle',
                        label: 'Grayscale Style',
                        responsive: true
                    }
                ]
            },
            spacingSettings,
            borderSettings,
            boxShadowSettings,
            sizingSettings,
            {
                id: 'premium_interactive',
                label: 'Interactive States',
                fields: [
                    { name: 'hover_scale', type: 'range', label: 'Hover Scale', min: 0.8, max: 1.5, step: 0.05, default: 1 },
                    { name: 'hover_brightness', type: 'range', label: 'Hover Brightness', min: 50, max: 150, step: 10, unit: '%', default: 100 }
                ]
            },
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

export default MapModule;
