export interface LayoutPreset {
    name?: string;
    widths?: number[]; // Simple column widths (e.g. [1, 1])
    specialty?: boolean;
    rows?: LayoutPreset[]; // For multi-row flex layouts
    cols?: {
        width: number;
        rows?: LayoutPreset[];
    }[]; // For specialty/masonry layouts
}

export const equalLayouts: LayoutPreset[] = [
    { name: '1 Column', widths: [1] },
    { name: '2 Columns', widths: [1, 1] },
    { name: '3 Columns', widths: [1, 1, 1] },
    { name: '4 Columns', widths: [1, 1, 1, 1] },
    { name: '5 Columns', widths: [1, 1, 1, 1, 1] },
    { name: '6 Columns', widths: [1, 1, 1, 1, 1, 1] },
    { name: '7 Columns', widths: [1, 1, 1, 1, 1, 1, 1] },
    { name: '8 Columns', widths: [1, 1, 1, 1, 1, 1, 1, 1] },
]

export const offsetLayouts: LayoutPreset[] = [
    // Row 1
    { name: '2/3 1/3', widths: [2, 1] },
    { name: '1/3 2/3', widths: [1, 2] },
    { name: '3/4 1/4', widths: [3, 1] },
    { name: '1/4 3/4', widths: [1, 3] },
    // Row 2
    { name: '4/5 1/5', widths: [4, 1] },
    { name: '1/5 4/5', widths: [1, 4] },
    { name: '1/4 1/2 1/4', widths: [1, 2, 1] },
    { name: '1/2 1/4 1/4', widths: [2, 1, 1] },
    // Row 3
    { name: '1/4 1/4 1/2', widths: [1, 1, 2] },
    { name: '1/5 3/5 1/5', widths: [1, 3, 1] },
    { name: '3/5 1/5 1/5', widths: [3, 1, 1] },
    { name: '1/5 1/5 3/5', widths: [1, 1, 3] },
]

export const flexMultiRowPresets: LayoutPreset[] = [
    // Row 1
    { name: '2 Rows (Full)', rows: [{ widths: [1] }, { widths: [1] }] },
    { name: '2 Rows (1/2)', rows: [{ widths: [1, 1] }, { widths: [1, 1] }] },
    { name: '2 Rows (1/3)', rows: [{ widths: [1, 1, 1] }, { widths: [1, 1, 1] }] },
    { name: '2 Rows (1/4)', rows: [{ widths: [1, 1, 1, 1] }, { widths: [1, 1, 1, 1] }] },
    // Row 2
    { name: '3 Rows (Full)', rows: [{ widths: [1] }, { widths: [1] }, { widths: [1] }] },
    { name: '3 Rows (1/2)', rows: [{ widths: [1, 1] }, { widths: [1, 1] }, { widths: [1, 1] }] },
    { name: '3 Rows (1/3)', rows: [{ widths: [1, 1, 1] }, { widths: [1, 1, 1] }, { widths: [1, 1, 1] }] },
    { name: '3 Rows (1/4)', rows: [{ widths: [1, 1, 1, 1] }, { widths: [1, 1, 1, 1] }, { widths: [1, 1, 1, 1] }] },
    // Row 3
    { name: '2 Cols Top, 1 Bottom', rows: [{ widths: [1, 1] }, { widths: [1] }] },
    { name: '1 Col Top, 2 Bottom', rows: [{ widths: [1] }, { widths: [1, 1] }] },
    { name: '3 Cols Top, 1 Bottom', rows: [{ widths: [1, 1, 1] }, { widths: [1] }] },
    { name: '1 Col Top, 3 Bottom', rows: [{ widths: [1] }, { widths: [1, 1, 1] }] },
]

export const flexMultiColumnPresets: LayoutPreset[] = [
    // Specialty split layouts
    { name: '1/2, Split Right', specialty: true, cols: [{ width: 2 }, { width: 2, rows: [{ widths: [1] }, { widths: [1] }] }] },
    { name: 'Split Left, 1/2', specialty: true, cols: [{ width: 2, rows: [{ widths: [1] }, { widths: [1] }] }, { width: 2 }] },
    { name: 'Split Top-Left, 1/2', specialty: true, cols: [{ width: 2, rows: [{ widths: [1] }, { widths: [1, 1] }] }, { width: 2 }] },
    { name: 'Split Top-Right, 1/2', specialty: true, cols: [{ width: 2 }, { width: 2, rows: [{ widths: [1] }, { widths: [1, 1] }] }] },

    { name: '1/4 1/2 1/4', widths: [1, 2, 1] },
    { name: '1/2 1/4 1/4', widths: [2, 1, 1] },
    { name: '1/4 1/4 1/2', widths: [1, 1, 2] },
    { name: 'Split x2', specialty: true, cols: [{ width: 1, rows: [{ widths: [1] }, { widths: [1] }] }, { width: 1, rows: [{ widths: [1] }, { widths: [1] }] }] },

    { name: '1/3 2/3-Split', specialty: true, cols: [{ width: 1 }, { width: 2, rows: [{ widths: [1] }, { widths: [1] }] }] },
    { name: '2/3-Split 1/3', specialty: true, cols: [{ width: 2, rows: [{ widths: [1] }, { widths: [1] }] }, { width: 1 }] },
    { name: 'Center Split', specialty: true, cols: [{ width: 1 }, { width: 2, rows: [{ widths: [1] }, { widths: [1] }] }, { width: 1 }] },
    { name: 'Split x3', specialty: true, cols: [{ width: 1, rows: [{ widths: [1] }, { widths: [1] }] }, { width: 1, rows: [{ widths: [1] }, { widths: [1] }] }, { width: 1, rows: [{ widths: [1] }, { widths: [1] }] }] },
]

export const gridMultiRowPresets: LayoutPreset[] = [...flexMultiRowPresets]

export const masonryPresets: LayoutPreset[] = [
    // Row 1 (from image)
    { name: 'Masonry 1', specialty: true, cols: [{ width: 1, rows: [{ widths: [1] }, { widths: [1] }] }, { width: 1, rows: [{ widths: [1] }, { widths: [1] }] }] },
    { name: 'Masonry 2', specialty: true, cols: [{ width: 1 }, { width: 1, rows: [{ widths: [1] }, { widths: [1] }, { widths: [1] }] }] },
    { name: 'Masonry 3', specialty: true, cols: [{ width: 2, rows: [{ widths: [1] }, { widths: [1] }] }, { width: 1, rows: [{ widths: [1] }, { widths: [1] }, { widths: [1] }] }] },
    { name: 'Masonry 4', specialty: true, cols: [{ width: 1 }, { width: 1, rows: [{ widths: [1] }, { widths: [1] }] }, { width: 1 }] },
    // Row 2
    { name: 'Masonry 5', specialty: true, cols: [{ width: 1, rows: [{ widths: [1] }, { widths: [1] }] }, { width: 1 }, { width: 1, rows: [{ widths: [1] }, { widths: [1] }] }] },
    { name: 'Masonry 6', specialty: true, cols: [{ width: 2, rows: [{ widths: [1] }, { widths: [1] }] }, { width: 1 }, { width: 1, rows: [{ widths: [1] }, { widths: [1] }] }] },
    { name: 'Masonry 7', specialty: true, cols: [{ width: 1, rows: [{ widths: [1] }, { widths: [1] }] }, { width: 2, rows: [{ widths: [1] }, { widths: [1, 1] }] }, { width: 1 }] },
    { name: 'Masonry 8', specialty: true, cols: [{ width: 3, rows: [{ widths: [1] }, { widths: [1, 1] }] }, { width: 1, rows: [{ widths: [1] }, { widths: [1] }] }] },
    // Row 3
    { name: 'Masonry 9', specialty: true, cols: [{ width: 1, rows: [{ widths: [1] }, { widths: [1] }, { widths: [1] }] }, { width: 3, rows: [{ widths: [1] }, { widths: [1, 1] }] }] },
    { name: 'Masonry 10', specialty: true, cols: [{ width: 2, rows: [{ widths: [1] }, { widths: [1] }] }, { width: 1, rows: [{ widths: [1] }, { widths: [1] }, { widths: [1] }] }] },
    { name: 'Masonry 11', specialty: true, cols: [{ width: 2, rows: [{ widths: [1] }, { widths: [1] }] }, { width: 2, rows: [{ widths: [1] }, { widths: [1] }, { widths: [1] }] }] },
    { name: 'Masonry 12', specialty: true, cols: [{ width: 1, rows: [{ widths: [1] }, { widths: [1] }, { widths: [1] }] }, { width: 1, rows: [{ widths: [1] }, { widths: [1] }] }, { width: 2, rows: [{ widths: [1] }] }] },
    // Row 4
    { name: 'Masonry 13', specialty: true, cols: [{ width: 1, rows: [{ widths: [1] }, { widths: [1] }, { widths: [1] }] }, { width: 1, rows: [{ widths: [1] }, { widths: [2, 1] }, { widths: [1] }] }, { width: 1 }] },
    { name: 'Masonry 14', specialty: true, cols: [{ width: 1 }, { width: 1, rows: [{ widths: [1, 1] }, { widths: [1] }, { widths: [1, 1] }] }, { width: 1 }] },
    { name: 'Masonry 15', specialty: true, cols: [{ width: 1, rows: [{ widths: [1] }, { widths: [1] }, { widths: [1] }] }, { width: 1, rows: [{ widths: [1] }, { widths: [1, 1] }, { widths: [1] }] }, { width: 1, rows: [{ widths: [1] }, { widths: [1] }] }] },
    { name: 'Masonry 16', specialty: true, cols: [{ width: 1, rows: [{ widths: [1] }, { widths: [1] }] }, { width: 1, rows: [{ widths: [1] }, { widths: [1] }, { widths: [1] }] }, { width: 1, rows: [{ widths: [1] }, { widths: [1] }] }] },
]

export const sidebarPresets: LayoutPreset[] = [
    // From image
    { name: 'Top-Bottom Full, Middle 2/3-1/3', rows: [{ widths: [1] }, { widths: [2, 1] }, { widths: [1] }] },
    { name: 'Top-Bottom Full, Middle 1/3-2/3', rows: [{ widths: [1] }, { widths: [1, 2] }, { widths: [1] }] },
    { name: 'Top-Bottom Full, Middle 1/4-1/2-1/4', rows: [{ widths: [1] }, { widths: [1, 2, 1] }, { widths: [1] }] },
    { name: 'Top-Bottom Full, Middle 1/4-1/4-1/2', rows: [{ widths: [1] }, { widths: [1, 1, 2] }, { widths: [1] }] },
]
