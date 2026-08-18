import type { BlockDefinition } from '@/types/builder';
import Code from 'lucide-vue-next/dist/esm/icons/code.js';
import { defineAsyncComponent } from 'vue';

export default {
    name: 'code',
    label: 'Code Block',
    icon: Code,
    description: 'Display code with syntax highlighting.',
    component: defineAsyncComponent(() => import('@/shared/blocks/CodeBlock.vue')),
    settings: [
        { key: 'code', type: 'textarea', label: 'Code', default: '// Your code here\nconst hello = "world";' },
        {
            key: 'language',
            type: 'select',
            label: 'Language',
            options: [
                { label: 'JavaScript', value: 'javascript' },
                { label: 'TypeScript', value: 'typescript' },
                { label: 'Python', value: 'python' },
                { label: 'PHP', value: 'php' },
                { label: 'HTML', value: 'html' },
                { label: 'CSS', value: 'css' },
                { label: 'JSON', value: 'json' },
                { label: 'Bash', value: 'bash' },
                { label: 'SQL', value: 'sql' }
            ],
            default: 'javascript'
        },
        { key: 'show_line_numbers', type: 'boolean', label: 'Show Line Numbers', default: true },
        { key: 'show_copy_button', type: 'boolean', label: 'Show Copy Button', default: true },
        { key: 'window_chrome', type: 'boolean', label: 'Window Chrome (Mac Style)', default: false },
        {
            key: 'theme',
            type: 'select',
            label: 'Theme',
            options: [
                { label: 'Dark', value: 'dark' },
                { label: 'Light', value: 'light' },
                { label: 'GitHub', value: 'github' }
            ],
            default: 'dark'
        },
        {
            key: 'max_height',
            type: 'select',
            label: 'Max Height',
            options: [
                { label: 'Auto', value: '' },
                { label: '200px', value: '200px' },
                { label: '300px', value: '300px' },
                { label: '500px', value: '500px' }
            ],
            default: ''
        },
        {
            key: 'padding',
            type: 'select',
            label: 'Padding',
            options: [
                { label: 'None', value: '' },
                { label: 'Small', value: 'py-4' },
                { label: 'Medium', value: 'py-6' },
                { label: 'Large', value: 'py-10' }
            ],
            default: 'py-6'
        }
    ],
    defaultSettings: {
        code: '// Your code here\nconst hello = "world";',
        language: 'javascript',
        show_line_numbers: true,
        show_copy_button: true,
        theme: 'dark',
        max_height: '',
        padding: 'py-6',
        visibility: { mobile: true, tablet: true, desktop: true }
    }
} as BlockDefinition;
