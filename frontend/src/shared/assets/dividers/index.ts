import type { ShapeDivider } from '@/types/assets';

export const ShapeDividers: ShapeDivider[] = [
    {
        id: 'arrow',
        label: 'Arrow',
        svg: {
            top: '<path d="M640 140L1280 0H0z"/>',
            bottom: '<path d="M640 139L0 0v140h1280V0L640 139z"/>'
        },
        viewBox: '0 0 1280 140'
    },
    {
        id: 'curve',
        label: 'Curve',
        svg: {
            top: '<path d="M0 0c320 140 960 140 1280 0v140H0V0z"/>',
            bottom: '<path d="M0 140c320-140 960-140 1280 0V0H0v140z"/>'
        },
        viewBox: '0 0 1280 140'
    },
    {
        id: 'slant',
        label: 'Slant',
        svg: {
            top: '<path d="M0 0l1280 140V0H0z"/>',
            bottom: '<path d="M0 140L1280 0v140H0z"/>'
        },
        viewBox: '0 0 1280 140'
    },
    {
        id: 'waves',
        label: 'Waves',
        svg: {
            top: '<path d="M0 0c320 0 426.667 140 640 140s320-140 640-140v140H0V0z"/>',
            bottom: '<path d="M0 140c320 0 426.667-140 640-140s320 140 640 140V0H0v140z"/>'
        },
        viewBox: '0 0 1280 140'
    },
    {
        id: 'clouds',
        label: 'Clouds',
        svg: {
            top: '<path d="M0 140c106.667 0 160-40 213.333-40s106.667 40 213.334 40c106.666 0 160-80 213.333-80s106.667 80 213.333 80c106.667 0 160-60 213.334-60s106.666 60 213.333 60v-140H0v140z"/>',
            bottom: '<path d="M0 0c106.667 0 160 40 213.333 40s106.667-40 213.334-40c106.666 0 160 80 213.333 80s106.667-80 213.333-80c106.667 0 160 60 213.334 60s106.666-60 213.333-60v140H0V0z"/>'
        },
        viewBox: '0 0 1280 140'
    }
];
