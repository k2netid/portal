import { Node, mergeAttributes } from '@tiptap/core'
import { VueNodeViewRenderer } from '@tiptap/vue-3'
import VideoNodeView from '../nodes/VideoNodeView.vue'

import { type Component } from 'vue'

declare module '@tiptap/core' {
    interface Commands<ReturnType> {
        video: {
            setVideo: (options: Record<string, unknown>) => ReturnType,
        }
    }
}

export const VideoExtension = Node.create({
    name: 'video',
    group: 'block',
    atom: true,

    draggable: true,
    selectable: true,

    addAttributes() {
        return {
            src: {
                default: null,
            },
            controls: {
                default: true,
            },
            autoplay: {
                default: false,
            },
            loop: {
                default: false,
            },
            width: {
                default: 'auto',
            },
            height: {
                default: 'auto',
            },
            borderRadius: {
                default: '0px',
            },
            align: {
                default: 'center',
            },
            displayMode: {
                default: 'block',
            },
            margin: {
                default: '1rem',
            }
        }
    },

    parseHTML() {
        return [
            {
                tag: 'video',
            },
        ]
    },

    renderHTML({ HTMLAttributes }) {
        return ['video', mergeAttributes(HTMLAttributes)]
    },

    addNodeView() {
        return VueNodeViewRenderer(VideoNodeView as unknown as Component)
    },

    addCommands() {
        return {
            setVideo: options => ({ commands }) => {
                return commands.insertContent({
                    type: this.name,
                    attrs: options,
                })
            },
        }
    },
})
