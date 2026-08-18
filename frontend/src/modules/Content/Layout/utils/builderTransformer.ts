/**
 * Builder Content Transformer
 * Handles conversion between Classic HTML and Builder Blocks
 */

import type { BlockInstance } from '../types/builder';

const generateId = (): string => `module-${Date.now()}-${Math.random().toString(36).substr(2, 9)}`;

/**
 * Convert Classic HTML to Builder structure
 * @param {string} html 
 * @returns {Array} Array of blocks
 */
export const htmlToBuilder = (html: string): BlockInstance[] => {
    if (!html || html.trim() === '' || html === '<p></p>') return [];

    return [
        {
            id: generateId(),
            type: 'section',
            settings: {
                fullWidth: false,
                padding: { top: 60, bottom: 60, left: 0, right: 0, unit: 'px' },
                background: { color: '', image: '', repeat: 'no-repeat', position: 'center', size: 'cover' }
            },
            children: [
                {
                    id: generateId(),
                    type: 'row',
                    settings: {
                        columns: '1',
                        gutterWidth: 30,
                        padding: { top: 30, bottom: 30, left: 0, right: 0, unit: 'px' }
                    },
                    children: [
                        {
                            id: generateId(),
                            type: 'column',
                            settings: {
                                flexGrow: 1,
                                verticalAlignment: 'top'
                            },
                            children: [
                                {
                                    id: generateId(),
                                    type: 'text',
                                    settings: {
                                        content: html,
                                        padding: { top: 0, bottom: 0, left: 0, right: 0, unit: 'px' }
                                    }
                                }
                            ]
                        }
                    ]
                }
            ]
        }
    ];
};

/**
 * Flatten Builder Blocks into Classic HTML
 * @param {Array} blocks 
 * @returns {string} HTML string
 */
export const builderToHtml = (blocks: BlockInstance[]): string => {
    if (!blocks || !Array.isArray(blocks)) return '';

    let html = '';

    const processModules = (modules: BlockInstance[]) => {
        for (const module of modules) {
            // If it's a text-based module, extract content
            if (module.type === 'text' || module.type === 'heading') {
                const content = module.settings?.content || '';
                if (content) {
                    if (module.type === 'heading') {
                        const level = module.settings?.level || 'h2';
                        html += `<${level}>${content}</${level}>`;
                    } else {
                        html += content;
                    }
                }
            }

            // Recurse into children
            if (module.children && Array.isArray(module.children)) {
                processModules(module.children);
            }
        }
    };

    processModules(blocks);

    return html;
};
