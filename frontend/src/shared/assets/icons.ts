import type { IconCategory } from '@/types/assets';

// Navigation & Direction
export const commonIcons: string[] = [
    'ChevronUp', 'ChevronDown', 'ChevronLeft', 'ChevronRight',
    'ChevronsUp', 'ChevronsDown', 'ChevronsLeft', 'ChevronsRight',
    'ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight',
    'CircleArrowUp', 'CircleArrowDown', 'CircleArrowLeft', 'CircleArrowRight',
    'MoveUp', 'MoveDown', 'MoveLeft', 'MoveRight',
    'Plus', 'Minus', 'X', 'Check', 'Search', 'Settings', 'Home', 'User',
    'Bell', 'Calendar', 'Camera', 'Video', 'Mail', 'Trash2', 'PenTool',
    'ExternalLink', 'Link', 'Share2', 'Download', 'Upload', 'RefreshCw',
    'Circle', 'Square', 'Triangle', 'Star', 'Heart', 'Shield', 'CircleHelp',
    'CircleAlert', 'Info', 'CircleCheckBig', 'CirclePlus', 'CircleX',
    'Menu', 'Grid2X2', 'LayoutDashboard', 'Columns', 'Rows', 'Maximize2', 'Minimize2',
    'Eye', 'EyeOff', 'Lock', 'LockOpen', 'Key', 'Flag', 'MapPin', 'Clock'
]

export const categories: IconCategory[] = [
    {
        id: 'navigation',
        label: 'Navigation',
        icons: ['ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight', 'ChevronUp', 'ChevronDown', 'ChevronLeft', 'ChevronRight', 'ChevronsUp', 'ChevronsDown', 'ChevronsLeft', 'ChevronsRight', 'MoveUp', 'MoveDown', 'MoveLeft', 'MoveRight', 'ArrowBigUp', 'ArrowBigDown', 'CircleArrowUp', 'CircleArrowDown']
    },
    {
        id: 'communication',
        label: 'Communication',
        icons: ['Mail', 'MessageSquare', 'MessageCircle', 'Phone', 'Send', 'Share2', 'AtSign', 'Paperclip', 'Bell', 'Languages']
    },
    {
        id: 'media',
        label: 'Media',
        icons: ['Image', 'Video', 'Music', 'Play', 'Pause', 'Square', 'Film', 'Camera', 'Mic', 'Headphones', 'Speaker', 'Volume2']
    },
    {
        id: 'interface',
        label: 'Interface',
        icons: ['Menu', 'Grid2X2', 'LayoutDashboard', 'Settings', 'Search', 'ListFilter', 'Maximize2', 'Minimize2', 'Layers', 'GripHorizontal', 'Ellipsis', 'SlidersHorizontal']
    },
    {
        id: 'actions',
        label: 'Actions',
        icons: ['Plus', 'Minus', 'X', 'Check', 'Trash2', 'PenTool', 'Save', 'Copy', 'RefreshCw', 'Download', 'Upload', 'Power', 'LogOut', 'ExternalLink']
    },
    {
        id: 'status',
        label: 'Status & Help',
        icons: ['CircleHelp', 'Info', 'CircleAlert', 'TriangleAlert', 'Shield', 'ShieldCheck', 'ShieldAlert', 'CircleCheckBig', 'CirclePlus', 'CircleX', 'Circle', 'Lock', 'LockOpen']
    }
]

// Extract all unique icons from categories for the "All Icons" view
export const allIcons: string[] = Array.from(new Set(
    categories.flatMap(cat => cat.icons)
)).sort();
