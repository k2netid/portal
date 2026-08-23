export const animationSettings = {
    id: 'animation',
    label: 'Animation',
    group: 'Animation', // Legacy property kept for compatibility if needed
    tab: 'advanced',    // Legacy property
    fields: [
        {
            name: 'animation_effect',
            type: 'select',
            label: 'Animation Effect',
            options: [
                { label: 'None', value: '' },
                { label: 'Fade In', value: 'animate-fade-in' },
                { label: 'Fade In Up', value: 'animate-fade-in-up' },
                { label: 'Fade In Down', value: 'animate-fade-in-down' },
                { label: 'Fade In Left', value: 'animate-fade-in-left' },
                { label: 'Fade In Right', value: 'animate-fade-in-right' },
                { label: 'Zoom In', value: 'animate-zoom-in' },
                { label: 'Bounce', value: 'animate-bounce' },
                { label: 'Pulse', value: 'animate-pulse' }
            ],
            default: ''
        },
        {
            name: 'animation_duration',
            type: 'range',
            label: 'Duration (ms)',
            min: 0,
            max: 3000,
            step: 100,
            default: 1000,
            showInput: true,
            condition: (s: Record<string, unknown>) => !!s.animation_effect
        },
        {
            name: 'animation_delay',
            type: 'range',
            label: 'Delay (ms)',
            min: 0,
            max: 2000,
            step: 50,
            default: 0,
            showInput: true,
            condition: (s: Record<string, unknown>) => !!s.animation_effect
        },
        {
            name: 'animation_repeat',
            type: 'select',
            label: 'Repeat',
            options: [
                { label: 'Once', value: '1' },
                { label: 'Infinite', value: 'infinite' }
            ],
            default: '1',
            condition: (s: Record<string, unknown>) => !!s.animation_effect
        }
    ]
}
