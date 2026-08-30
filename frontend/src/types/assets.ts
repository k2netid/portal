/**
 * Shared Asset Interfaces
 */

export interface IconCategory {
    id: string;
    label: string;
    icons: string[];
}

export interface ShapeDivider {
    id: string;
    label: string;
    svg: {
        top: string;
        bottom: string;
    };
    viewBox: string;
}

export interface BackgroundMask {
    id: string;
    label: string;
    svg: {
        default?: {
            landscape: string;
            portrait: string;
            square: string;
        };
        regular?: {
            default: {
                landscape: string;
                portrait: string;
                square: string;
            };
            rotated: {
                landscape: string;
                portrait: string;
                square: string;
            };
        };
        inverted?: {
            default: {
                landscape: string;
                portrait: string;
                square: string;
            };
            rotated?: {
                landscape: string;
                portrait: string;
                square: string;
            };
        };
    };
    viewBox: {
        landscape: string;
        portrait: string;
        square: string;
    };
}

export interface BackgroundPattern {
    id: string;
    label: string;
    width: number;
    height: number;
    svg: {
        default?: string;
        rotated?: string;
        inverted?: string | {
            default: string;
            rotated: string;
        };
        regular?: {
            default: string;
            rotated: string;
        };
        inverted_variant?: {
            default: string;
            rotated: string;
        };
    };
}
