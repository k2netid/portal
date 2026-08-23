/**
 * Builder Constants
 */

// Module Categories
export const MODULE_CATEGORIES = {
    structure: {
        id: 'structure',
        label: 'Structure',
        icon: 'LayoutGrid',
        color: '#2059ea'
    },
    basic: {
        id: 'basic',
        label: 'Basic',
        icon: 'Type',
        color: '#18b793'
    },
    media: {
        id: 'media',
        label: 'Media',
        icon: 'Image',
        color: '#9b59b6'
    },
    forms: {
        id: 'forms',
        label: 'Forms',
        icon: 'FormInput',
        color: '#e67e22'
    }
}

// Element Colors (Wireframe View)
export const ELEMENT_COLORS = {
    section: '#2059ea',
    row: '#18b793',
    column: '#b3c3d6',
    module: '#b3c3d6'
}

// Device Modes
export const DEVICE_MODES = {
    desktop: {
        id: 'desktop',
        label: 'Desktop',
        icon: 'Monitor',
        width: null // Full width
    },
    tablet: {
        id: 'tablet',
        label: 'Tablet',
        icon: 'Tablet',
        width: 768
    },
    mobile: {
        id: 'mobile',
        label: 'Mobile',
        icon: 'Smartphone',
        width: 375
    }
}

// Settings Tabs
export const SETTINGS_TABS = {
    content: {
        id: 'content',
        label: 'Content',
        icon: 'FileText'
    },
    design: {
        id: 'design',
        label: 'Design',
        icon: 'Palette'
    },
    advanced: {
        id: 'advanced',
        label: 'Advanced',
        icon: 'Settings'
    }
}

// Left Sidebar Panels
export const SIDEBAR_PANELS = [
    { id: 'pages', label: 'Pages', icon: 'FileText' },
    { id: 'templates', label: 'Templates', icon: 'Layout' },
    { id: 'theme', label: 'Theme', icon: 'Palette' },
    { id: 'settings', label: 'Settings', icon: 'Settings' },
    { id: 'layers', label: 'Layers', icon: 'Layers' },
    { id: 'layouts', label: 'Layouts', icon: 'Grid' },
    { id: 'presets', label: 'Presets', icon: 'Sparkles' },
    { id: 'history', label: 'History', icon: 'Clock' },
    { id: 'global_variables', label: 'Global Variables', icon: 'Database' },
    { id: 'portability', label: 'Import/Export', icon: 'Share2' },
    { id: 'help', label: 'Help', icon: 'HelpCircle' }
]

// Field Types
export const FIELD_TYPES = {
    text: 'TextField',
    textarea: 'TextareaField',
    richtext: 'RichtextField',
    number: 'NumberField',
    range: 'RangeField',
    select: 'SelectField',
    toggle: 'ToggleField',
    color: 'ColorField',
    upload: 'UploadField',
    buttonGroup: 'ButtonGroupField',
    spacing: 'SpacingField',
    border: 'BorderField',
    shadow: 'ShadowField'
}

// CSS Units
export const CSS_UNITS = ['px', '%', 'em', 'rem', 'vw', 'vh']

// Font Weights
export const FONT_WEIGHTS = [
    { value: '100', label: 'Thin' },
    { value: '200', label: 'Extra Light' },
    { value: '300', label: 'Light' },
    { value: '400', label: 'Regular' },
    { value: '500', label: 'Medium' },
    { value: '600', label: 'Semi Bold' },
    { value: '700', label: 'Bold' },
    { value: '800', label: 'Extra Bold' },
    { value: '900', label: 'Black' }
]

// Text Alignments
export const TEXT_ALIGNMENTS = [
    { value: 'left', label: 'Left', icon: 'AlignLeft' },
    { value: 'center', label: 'Center', icon: 'AlignCenter' },
    { value: 'right', label: 'Right', icon: 'AlignRight' },
    { value: 'justify', label: 'Justify', icon: 'AlignJustify' }
]
