import Image from '@tiptap/extension-image'
import { VueNodeViewRenderer } from '@tiptap/vue-3'
import ImageNodeView from '../nodes/ImageNodeView.vue'

import { type Component } from 'vue'

export const CustomImage = Image.extend({
    draggable: true,
    selectable: true,

    addAttributes() {
        return {
            ...this.parent?.(),
            width: {
                default: 'auto',
                renderHTML: attributes => ({
                    width: attributes.width,
                }),
            },
            height: {
                default: 'auto',
                renderHTML: attributes => ({
                    height: attributes.height,
                }),
            },
            borderRadius: {
                default: '0px',
                renderHTML: attributes => ({
                    style: `border-radius: ${attributes.borderRadius}`,
                }),
            },
            borderWidth: {
                default: '0px',
                renderHTML: attributes => ({
                    style: `border-width: ${attributes.borderWidth}`,
                }),
            },
            borderColor: {
                default: '#000000',
                renderHTML: attributes => ({
                    style: `border-color: ${attributes.borderColor}`,
                }),
            },
            align: {
                default: 'center', // left, center, right
            },
            displayMode: {
                default: 'block', // block, inline, float-left, float-right
                renderHTML: attributes => ({
                    'data-display': attributes.displayMode,
                }),
            },
            margin: {
                default: '1rem',
            }
        }
    },

    addNodeView() {
        return VueNodeViewRenderer(ImageNodeView as unknown as Component)
    },
});
