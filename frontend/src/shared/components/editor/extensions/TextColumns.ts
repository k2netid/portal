import { Node, mergeAttributes } from '@tiptap/core'

declare module '@tiptap/core' {
    interface Commands<ReturnType> {
        textColumns: {
            setTextColumns: (count: number) => ReturnType,
            toggleTextColumns: (count: number) => ReturnType,
        }
    }
}

export const TextColumns = Node.create({
    name: 'textColumns',
    group: 'block',
    content: 'block+',
    defining: true,

    addAttributes() {
        return {
            count: {
                default: 1,
                parseHTML: element => parseInt(element.style.columnCount) || 1,
                renderHTML: attributes => {
                    if (attributes.count <= 1) return {}
                    return { style: `column-count: ${attributes.count}` }
                }
            },
            gap: {
                default: '2rem',
                parseHTML: element => element.style.columnGap || '2rem',
                renderHTML: attributes => {
                    if (attributes.count <= 1) return {}
                    return { style: `column-gap: ${attributes.gap}` }
                }
            },
            rule: {
                default: true,
                parseHTML: element => element.style.columnRule !== 'none',
                renderHTML: attributes => {
                    if (attributes.count <= 1 || !attributes.rule) return {}
                    return { style: `column-rule: 1px solid hsl(var(--border) / 0.5)` }
                }
            }
        }
    },

    parseHTML() {
        return [
            {
                tag: 'div[data-type="text-columns"]',
            },
        ]
    },

    renderHTML({ HTMLAttributes }) {
        return ['div', mergeAttributes(HTMLAttributes, {
            'data-type': 'text-columns',
            class: 'text-columns-container'
        }), 0]
    },

    addCommands() {
        return {
            setTextColumns: (count) => ({ commands }) => {
                if (count <= 1) {
                    return commands.lift('textColumns')
                }
                return commands.wrapIn('textColumns', { count })
            },
            toggleTextColumns: (count) => ({ commands, editor }) => {
                const isActive = editor.isActive('textColumns', { count })
                if (isActive) {
                    return commands.lift('textColumns')
                }
                return commands.wrapIn('textColumns', { count })
            }
        }
    },
})
