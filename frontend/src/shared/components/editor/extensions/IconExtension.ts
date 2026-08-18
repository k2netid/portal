import { Node, mergeAttributes } from '@tiptap/core';
import { VueNodeViewRenderer } from '@tiptap/vue-3';
import IconComponent from '../nodes/IconComponent.vue';

import { type Component } from 'vue';

export const Icon = Node.create({
    name: 'icon',

    group: 'inline',

    inline: true,

    selectable: true,

    draggable: true,

    atom: true,

    addAttributes() {
        return {
            name: {
                default: 'Circle',
            },
            size: {
                default: '1em',
            },
            color: {
                default: 'currentColor',
            },
            strokeWidth: {
                default: 2,
            },
            rotate: {
                default: 0,
            },
            backgroundColor: {
                default: null,
            },
            borderRadius: {
                default: '0px',
            },
            padding: {
                default: '0px',
            },
            opacity: {
                default: 1,
            }
        };
    },

    parseHTML() {
        return [
            {
                tag: 'span[data-type="icon"]',
            },
        ];
    },

    renderHTML({ HTMLAttributes }) {
        return ['span', mergeAttributes(HTMLAttributes, { 'data-type': 'icon' })];
    },

    addNodeView() {
        return VueNodeViewRenderer(IconComponent as unknown as Component);
    },
});
