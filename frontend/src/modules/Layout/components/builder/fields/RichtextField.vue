<template>
  <div class="richtext-field">
    <!-- Header Labels -->
    <div class="field-header">
      <label v-if="label" class="builder-label">{{ label }}</label>
      
      <!-- Mode Switcher -->
      <div class="mode-switch">
        <button 
          class="mode-btn" 
          :class="{ active: mode === 'visual' }" 
          @click="setMode('visual')"
        >
          Visual
        </button>
        <button 
          class="mode-btn" 
          :class="{ active: mode === 'text' }" 
          @click="setMode('text')"
        >
          Text
        </button>
      </div>
    </div>

    <!-- Extra Actions (Media / AI) -->
    <div class="extra-actions">
        <button class="action-btn" @click="$emit('add-media')">
            <LucideImage :size="12" /> Add Media
        </button>
        <button class="action-btn ai-btn">
             <Sparkles :size="12" /> Generate with AI
        </button>
    </div>

    <!-- Editor Container -->
    <div class="editor-container" :class="{ focused: isFocused }">
      <!-- Visual Mode -->
      <div v-show="mode === 'visual'" class="editor-visual">
         <RichtextToolbar v-if="editor" :editor="editor" />
         <editor-content :editor="editor" class="tiptap-content" />
      </div>

      <!-- Text Mode (Raw HTML) -->
      <div v-show="mode === 'text'" class="editor-text">
         <textarea 
            v-model="rawHtml" 
            @input="updateFromRaw" 
            placeholder="Write raw HTML here..."
            class="raw-textarea"
         ></textarea>
      </div>
    </div>
</div>
</template>

<script setup lang="ts">
import { ref, watch, onBeforeUnmount, onMounted } from 'vue'
import LucideImage from 'lucide-vue-next/dist/esm/icons/image.js';
import Sparkles from 'lucide-vue-next/dist/esm/icons/sparkles.js';

import { Editor, EditorContent } from '@tiptap/vue-3'
import StarterKit from '@tiptap/starter-kit'
import { Underline } from '@tiptap/extension-underline'
import { TextStyle } from '@tiptap/extension-text-style'
import { Color } from '@tiptap/extension-color'
import { TextAlign } from '@tiptap/extension-text-align'
import { Link } from '@tiptap/extension-link'
import { Image as ImageExt } from '@tiptap/extension-image'

import RichtextToolbar from '@/components/builder/fields/RichtextToolbar.vue'
import type { SettingDefinition } from '@/types/builder'

const props = defineProps<{
  field?: SettingDefinition;
  value?: string;
  label?: string;
}>()

const emit = defineEmits(['update:value', 'add-media'])

const mode = ref('visual') // visual | text
const isFocused = ref(false)
const rawHtml = ref(props.value || '')
const editor = ref<Editor | undefined>()

// Initialize Tiptap
onMounted(() => {
    editor.value = new Editor({
        content: props.value || '',
        extensions: [
            StarterKit,
            Underline,
            TextStyle,
            Color,
            Link.configure({
                openOnClick: false
            }),
            ImageExt,
            TextAlign.configure({
                types: ['heading', 'paragraph'],
            }),
        ],
        onUpdate: ({ editor }) => {
            const html = editor.getHTML()
            rawHtml.value = html
            emit('update:value', html)
        },
        onFocus: () => isFocused.value = true,
        onBlur: () => isFocused.value = false,
        editorProps: {
            attributes: {
                class: 'prose prose-sm dark:prose-invert focus:outline-none min-h-[150px] px-3 py-2'
            }
        }
    })
})

onBeforeUnmount(() => {
    if (editor.value) {
        editor.value.destroy()
    }
})

// Watch model value for external changes
watch(() => props.value, (newVal) => {
    // Only update if content is different to avoid cursor jumps
    if (editor.value && newVal !== editor.value.getHTML()) {
        editor.value.commands.setContent(newVal || '', { emitUpdate: false })
        rawHtml.value = newVal || ''
    }
})

const setMode = (m: string) => {
    mode.value = m
    if (m === 'visual' && editor.value) {
        // Sync raw html back to visual editor
        editor.value.commands.setContent(rawHtml.value || '', { emitUpdate: false })
    }
}

const updateFromRaw = () => {
    emit('update:value', rawHtml.value)
}
</script>

<style scoped>
.richtext-field {
    width: 100%;
}

.field-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
}

.mode-switch {
    display: flex;
    background: var(--builder-bg-tertiary);
    border-radius: 4px;
    padding: 2px;
}

.mode-btn {
    background: transparent;
    border: none;
    color: var(--builder-text-secondary);
    font-size: 11px;
    padding: 2px 8px;
    cursor: pointer;
    border-radius: 3px;
}

.mode-btn.active {
    background: var(--builder-bg-primary);
    color: var(--builder-text-primary);
    box-shadow: 0 1px 2px rgba(0,0,0,0.1);
}

.extra-actions {
    display: flex;
    gap: 8px;
    margin-bottom: 8px;
}

.action-btn {
    display: flex;
    align-items: center;
    gap: 4px;
    font-size: 11px;
    padding: 4px 8px;
    border: 1px solid var(--builder-border);
    border-radius: 4px;
    background: var(--builder-bg-secondary);
    color: var(--builder-text-primary);
    cursor: pointer;
}

.ai-btn {
    background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
    border: none;
    color: white;
}

.editor-container {
    border: 1px solid var(--builder-border);
    border-radius: var(--border-radius-md);
    overflow: hidden;
    background: var(--builder-bg-primary);
    transition: var(--transition-fast);
}

.editor-container.focused {
    border-color: var(--builder-accent);
    box-shadow: 0 0 0 2px rgba(32, 89, 234, 0.1);
}

/* Tiptap Internal Styling */
:deep(.tiptap-content) {
    max-height: 400px;
    overflow-y: auto;
}

:deep(.tiptap-content p) {
    margin: 0.5em 0;
}

/* Raw Text Area */
.raw-textarea {
    width: 100%;
    min-height: 200px;
    background: #1e1e1e;
    color: #d4d4d4;
    font-family: monospace;
    font-size: 12px;
    padding: 10px;
    border: none;
    resize: vertical;
    outline: none;
}
</style>
