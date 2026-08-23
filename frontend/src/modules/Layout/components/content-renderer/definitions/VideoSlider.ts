import type { BlockDefinition } from '@/types/builder';
export default {
    type: 'videoslider',
    name: 'Video Slider',
    label: 'Video Slider',
    component: 'VideoSliderBlock',
    settings: [
        { name: 'items', label: 'Items', type: 'repeater' },
        { name: 'showArrows', label: 'Show Arrows', type: 'boolean' },
        { name: 'showDots', label: 'Show Dots', type: 'boolean' },
        { name: 'showThumbnails', label: 'Show Thumbnails', type: 'boolean' },
        { name: 'thumbnailPosition', label: 'Thumbnail Position', type: 'string' },
        { name: 'autoplay', label: 'Autoplay', type: 'boolean' },
        { name: 'autoplaySpeed', label: 'Autoplay Speed', type: 'number' },
        { name: 'aspectRatio', label: 'Aspect Ratio', type: 'string' },
        { name: 'slidesPerView', label: 'Slides Per View', type: 'number' },
        { name: 'gap', label: 'Gap', type: 'number' },
        { name: 'videoAutoplay', label: 'Video Autoplay', type: 'boolean' },
        { name: 'videoMuted', label: 'Video Muted', type: 'boolean' },
        { name: 'videoLoop', label: 'Video Loop', type: 'boolean' },
        { name: 'showControls', label: 'Show Controls', type: 'boolean' },
        { name: 'showPlayButton', label: 'Show Play Button', type: 'boolean' },
        { name: 'playButtonSize', label: 'Play Button Size', type: 'number' },
        { name: 'playButtonColor', label: 'Play Button Color', type: 'string' },
        { name: 'overlayColor', label: 'Overlay Color', type: 'string' }
    ]
} as BlockDefinition;
