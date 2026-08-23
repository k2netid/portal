<template>
  <div class="richtext-toolbar">
    <!-- Row 1: Primary Formatting -->
    <div class="toolbar-row">
      <!-- Headings / Paragraph -->
      <BaseDropdown title="Format">
        <template #trigger="{ open }">
          <button class="toolbar-btn text-label" :class="{ active: open }">
             {{ activeNodeName }}
             <ChevronDown :size="12" />
          </button>
        </template>
        <template #default="{ close }">
          <div class="format-list">
             <button @click="editor.chain().focus().setParagraph().run(); close()" :class="{ active: editor.isActive('paragraph') }">Paragraph</button>
             <button @click="editor.chain().focus().toggleHeading({ level: 1 }).run(); close()" :class="{ active: editor.isActive('heading', { level: 1 }) }">Heading 1</button>
             <button @click="editor.chain().focus().toggleHeading({ level: 2 }).run(); close()" :class="{ active: editor.isActive('heading', { level: 2 }) }">Heading 2</button>
             <button @click="editor.chain().focus().toggleHeading({ level: 3 }).run(); close()" :class="{ active: editor.isActive('heading', { level: 3 }) }">Heading 3</button>
             <button @click="editor.chain().focus().toggleHeading({ level: 4 }).run(); close()" :class="{ active: editor.isActive('heading', { level: 4 }) }">Heading 4</button>
             <button @click="editor.chain().focus().toggleHeading({ level: 5 }).run(); close()" :class="{ active: editor.isActive('heading', { level: 5 }) }">Heading 5</button>
             <button @click="editor.chain().focus().toggleHeading({ level: 6 }).run(); close()" :class="{ active: editor.isActive('heading', { level: 6 }) }">Heading 6</button>
          </div>
        </template>
      </BaseDropdown>

      <div class="divider"></div>

      <button class="toolbar-btn" @click="editor.chain().focus().toggleBold().run()" :class="{ active: editor.isActive('bold') }" title="Bold">
        <Bold :size="14" />
      </button>
      <button class="toolbar-btn" @click="editor.chain().focus().toggleItalic().run()" :class="{ active: editor.isActive('italic') }" title="Italic">
        <Italic :size="14" />
      </button>
      <button class="toolbar-btn" @click="editor.chain().focus().toggleUnderline().run()" :class="{ active: editor.isActive('underline') }" title="Underline">
        <UnderlineIcon :size="14" />
      </button>
      <button class="toolbar-btn" @click="editor.chain().focus().toggleStrike().run()" :class="{ active: editor.isActive('strike') }" title="Strikethrough">
        <Strikethrough :size="14" />
      </button>
      
      <div class="divider"></div>

      <BaseDropdown title="Text Color">
        <template #trigger>
           <button class="toolbar-btn" :class="{ active: editor.isActive('textStyle') }" title="Text Color">
             <div class="color-indicator" :style="{ backgroundColor: activeColor }">A</div>
           </button>
        </template>
        <template #default="{ close }">
           <div class="color-picker-simple">
              <button v-for="color in colors" :key="color" :style="{ backgroundColor: color }" @click="editor.chain().focus().setColor(color).run(); close()"></button>
              <button class="reset-color" @click="editor.chain().focus().unsetColor().run(); close()">Reset</button>
           </div>
        </template>
      </BaseDropdown>

      <button class="toolbar-btn" @click="editor.chain().focus().unsetAllMarks().run()" title="Clear Formatting">
        <RemoveFormatting :size="14" />
      </button>

      <div class="divider"></div>

      <button class="toolbar-btn" @click="editor.chain().focus().toggleBulletList().run()" :class="{ active: editor.isActive('bulletList') }" title="Bullet List">
        <List :size="14" />
      </button>
      <button class="toolbar-btn" @click="editor.chain().focus().toggleOrderedList().run()" :class="{ active: editor.isActive('orderedList') }" title="Numbered List">
        <ListOrdered :size="14" />
      </button>

      <div class="divider"></div>
      
      <button class="toolbar-btn" @click="editor.chain().focus().setTextAlign('left').run()" :class="{ active: editor.isActive({ textAlign: 'left' }) }" title="Align Left">
        <AlignLeft :size="14" />
      </button>
      <button class="toolbar-btn" @click="editor.chain().focus().setTextAlign('center').run()" :class="{ active: editor.isActive({ textAlign: 'center' }) }" title="Align Center">
        <AlignCenter :size="14" />
      </button>
      <button class="toolbar-btn" @click="editor.chain().focus().setTextAlign('right').run()" :class="{ active: editor.isActive({ textAlign: 'right' }) }" title="Align Right">
        <AlignRight :size="14" />
      </button>
      <button class="toolbar-btn" @click="editor.chain().focus().setTextAlign('justify').run()" :class="{ active: editor.isActive({ textAlign: 'justify' }) }" title="Justify">
        <AlignJustify :size="14" />
      </button>
    </div>

    <!-- Row 2: Secondary / Insert -->
    <div class="toolbar-row">
       <div class="group-wrapper">
         <button class="toolbar-btn" @click="setLink" :class="{ active: editor.isActive('link') }" title="Link">
            <LinkIcon :size="14" />
         </button>
         <button class="toolbar-btn" @click="editor.chain().focus().unsetLink().run()" :disabled="!editor.isActive('link')" title="Unlink">
            <Unlink :size="14" />
         </button>
       </div>
       
       <div class="divider"></div>

       <button class="toolbar-btn" @click="editor.chain().focus().toggleCodeBlock().run()" :class="{ active: editor.isActive('codeBlock') }" title="Code Block">
          <Code :size="14" />
       </button>
       <button class="toolbar-btn" @click="editor.chain().focus().toggleBlockquote().run()" :class="{ active: editor.isActive('blockquote') }" title="Quote">
          <Quote :size="14" />
       </button>
       <button class="toolbar-btn" @click="editor.chain().focus().setHorizontalRule().run()" title="Horizontal Line">
          <Minus :size="14" />
       </button>

       <div class="divider"></div>

       <button class="toolbar-btn" @click="editor.chain().focus().undo().run()" :disabled="!editor.can().undo()" title="Undo">
         <Undo :size="14" />
       </button>
       <button class="toolbar-btn" @click="editor.chain().focus().redo().run()" :disabled="!editor.can().redo()" title="Redo">
         <Redo :size="14" />
       </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import type { Editor } from '@tiptap/vue-3'
import Bold from 'lucide-vue-next/dist/esm/icons/bold.js';
import Italic from 'lucide-vue-next/dist/esm/icons/italic.js';
import UnderlineIcon from 'lucide-vue-next/dist/esm/icons/underline.js';
import Strikethrough from 'lucide-vue-next/dist/esm/icons/strikethrough.js';
import RemoveFormatting from 'lucide-vue-next/dist/esm/icons/remove-formatting.js';
import List from 'lucide-vue-next/dist/esm/icons/list.js';
import AlignLeft from 'lucide-vue-next/dist/esm/icons/align-start-horizontal.js';
import AlignCenter from 'lucide-vue-next/dist/esm/icons/align-center-horizontal.js';
import AlignRight from 'lucide-vue-next/dist/esm/icons/align-end-horizontal.js';
import AlignJustify from 'lucide-vue-next/dist/esm/icons/align-horizontal-justify-center.js';
import LinkIcon from 'lucide-vue-next/dist/esm/icons/link.js';
import Unlink from 'lucide-vue-next/dist/esm/icons/unlink.js';
import Code from 'lucide-vue-next/dist/esm/icons/code.js';
import Quote from 'lucide-vue-next/dist/esm/icons/quote.js';
import Minus from 'lucide-vue-next/dist/esm/icons/minus.js';
import Undo from 'lucide-vue-next/dist/esm/icons/undo.js';
import Redo from 'lucide-vue-next/dist/esm/icons/redo.js';
import ChevronDown from 'lucide-vue-next/dist/esm/icons/chevron-down.js';
import { BaseDropdown } from '@/components/builder/ui'

const props = defineProps<{
  editor: Editor;
}>()

const activeNodeName = computed(() => {
  if (props.editor.isActive('heading', { level: 1 })) return 'H1'
  if (props.editor.isActive('heading', { level: 2 })) return 'H2'
  if (props.editor.isActive('heading', { level: 3 })) return 'H3'
  if (props.editor.isActive('heading', { level: 4 })) return 'H4'
  if (props.editor.isActive('heading', { level: 5 })) return 'H5'
  if (props.editor.isActive('heading', { level: 6 })) return 'H6'
  return 'Paragraph'
})

const activeColor = computed(() => {
    return props.editor.getAttributes('textStyle').color || 'currentColor'
})

const colors = [
    '#000000', '#444444', '#666666', '#999999', '#cccccc', '#eeeeee', '#ffffff',
    '#ff0000', '#ff9900', '#ffff00', '#00ff00', '#00ffff', '#0000ff', '#9900ff', '#ff00ff',
    '#f44336', '#e91e63', '#9c27b0', '#673ab7', '#3f51b5', '#2196f3', '#03a9f4', '#00bcd4', 
    '#009688', '#4caf50', '#8bc34a', '#cddc39', '#ffeb3b', '#ffc107', '#ff9800', '#ff5722'
]

const setLink = () => {
  const previousUrl = props.editor.getAttributes('link').href
  const url = window.prompt('URL', previousUrl)

  // cancelled
  if (url === null) {
    return
  }

  // empty
  if (url === '') {
    props.editor.chain().focus().extendMarkRange('link').unsetLink().run()
    return
  }

  // update link
  props.editor.chain().focus().extendMarkRange('link').setLink({ href: url }).run()
}
</script>

<style scoped>
.richtext-toolbar {
  display: flex;
  flex-direction: column;
  gap: 4px;
  padding: 6px;
  background: var(--builder-bg-tertiary);
  border-bottom: 1px solid var(--builder-border);
}

.toolbar-row {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 2px;
}

.toolbar-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 24px;
  height: 24px;
  border: 1px solid transparent;
  background: transparent;
  color: var(--builder-text-secondary);
  border-radius: 3px;
  cursor: pointer;
  padding: 0;
  transition: all 0.2s;
}

.toolbar-btn:hover {
  background: var(--builder-bg-secondary);
  color: var(--builder-text-primary);
}

.toolbar-btn.active {
  background: var(--builder-accent);
  color: white;
}

.toolbar-btn.text-label {
    width: auto;
    padding: 0 4px;
    font-size: 11px;
    font-weight: 600;
    gap: 4px;
}

.toolbar-btn:disabled {
    opacity: 0.3;
    cursor: not-allowed;
}

.divider {
  width: 1px;
  height: 16px;
  background: var(--builder-border);
  margin: 0 4px;
}

/* Format List (Dropdown) */
.format-list {
    display: flex;
    flex-direction: column;
    padding: 4px;
    min-width: 120px;
}

.format-list button {
    text-align: left;
    background: none;
    border: none;
    padding: 6px 8px;
    color: var(--builder-text-secondary);
    font-size: 13px;
    cursor: pointer;
    border-radius: 4px;
}

.format-list button:hover {
    background: var(--builder-bg-tertiary);
    color: var(--builder-text-primary);
}

.format-list button.active {
    background: var(--builder-accent);
    color: white;
}

/* Color Picker */
.color-indicator {
    font-weight: bold;
    font-family: serif;
    font-size: 14px;
    border-bottom: 2px solid;
    line-height: 1;
}

.color-picker-simple {
    display: flex;
    flex-wrap: wrap;
    width: 160px;
    gap: 2px;
    padding: 6px;
}

.color-picker-simple button {
    width: 18px;
    height: 18px;
    border: 1px solid var(--builder-border);
    cursor: pointer;
    border-radius: 2px;
    padding: 0;
}

.color-picker-simple button:hover {
    transform: scale(1.1);
    z-index: 2;
    border-color: white;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

.color-picker-simple .reset-color {
    width: 100%;
    height: auto;
    margin-top: 4px;
    background: transparent;
    border: 1px solid var(--builder-border);
    font-size: 11px;
    color: var(--builder-text-secondary);
}
</style>
