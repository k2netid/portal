import CodeBlockLowlight from '@tiptap/extension-code-block-lowlight'
import { VueNodeViewRenderer } from '@tiptap/vue-3'
import CodeBlockWithCopy from '../nodes/CodeBlockWithCopy.vue'

import { type Component } from 'vue'

export const CodeBlockWithCopyExtension = CodeBlockLowlight.extend({
    addNodeView() {
        return VueNodeViewRenderer(CodeBlockWithCopy as unknown as Component)
    },
})
