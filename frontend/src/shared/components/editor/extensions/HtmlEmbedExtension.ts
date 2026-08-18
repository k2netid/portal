import { Node, mergeAttributes } from '@tiptap/core'
import { VueNodeViewRenderer } from '@tiptap/vue-3'
import HtmlEmbedComponent from '../nodes/HtmlEmbedComponent.vue'

declare module '@tiptap/core' {
    interface Commands<ReturnType> {
        htmlEmbed: {
            insertHtmlEmbed: (html: string) => ReturnType,
        }
    }
}

import { type Component } from 'vue'

export const HtmlEmbed = Node.create({
    name: 'htmlEmbed',

    group: 'block',

    atom: true,

    addAttributes() {
        return {
            html: {
                default: '',
            },
            width: {
                default: '100%',
            },
            height: {
                default: 'auto',
            },
            displayMode: {
                default: 'block',
            },
            align: {
                default: 'center',
            },
            borderRadius: {
                default: '0',
            },
            borderWidth: {
                default: '0',
            },
            borderColor: {
                default: null,
            },
            margin: {
                default: '16',
            },
        }
    },

    parseHTML() {
        return [
            {
                tag: 'div[data-html-embed]',
            },
        ]
    },

    renderHTML({ HTMLAttributes }) {
        return ['div', mergeAttributes({ 'data-html-embed': '' }, HTMLAttributes)]
    },

    addNodeView() {
        return VueNodeViewRenderer(HtmlEmbedComponent as unknown as Component)
    },

    addCommands() {
        return {
            insertHtmlEmbed: (html) => ({ commands }) => {
                return commands.insertContent({
                    type: this.name,
                    attrs: { html },
                })
            },
        }
    },
})
