import { Node, mergeAttributes } from '@tiptap/core'

export const Column = Node.create({
    name: 'column',
    content: 'block+',
    defining: true,

    parseHTML() {
        return [
            {
                tag: 'div[data-type="column"]',
            },
        ]
    },

    renderHTML({ HTMLAttributes }) {
        return ['div', mergeAttributes(HTMLAttributes, { 'data-type': 'column', class: 'column-item' }), 0]
    },
})
