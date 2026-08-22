<template>
  <div
    class="relative flex flex-col bg-muted/10 border border-border/60 rounded-xl shadow-none overflow-visible"
    @contextmenu="handleContextMenu"
  >
    <!-- Toolbar Component (Sticky) -->
    <div class="sticky top-0 z-30 bg-background/30 backdrop-blur-sm border-b border-border/40 overflow-visible">
      <Toolbar 
        v-if="editor" 
        :editor="editor" 
        :show-html-view="showHtmlView"
        @insert-table="showTableDialog = true"
        @open-media="showMediaPicker = true"
        @insert-html="insertHtmlEmbed"
        @toggle-html="toggleHtmlView"
        @insert-icon="insertIcon"
      />
    </div>

    <!-- Scrollable Content Area -->
    <div 
      class="relative overflow-auto border-b border-border/10 group/editor rounded-b-xl flex-1 cursor-text custom-scrollbar flex flex-col"
      :class="[
        compact ? 'min-h-[220px]' : 'min-h-[400px] max-h-[calc(100vh-16rem)]',
        resizable ? 'resize-y' : ''
      ]"
      style="display: flex; width: 100%;"
      @click="focusEditor"
    >
      <!-- Editor Content (WYSIWYG View) -->
      <editor-content 
        v-show="!showHtmlView"
        :editor="editor" 
        class="prose prose-sm sm:prose-base dark:prose-invert max-w-none focus:outline-none p-4 text-card-foreground flex-1 min-h-[200px]" 
      />

      <!-- HTML Source View -->
      <div
        v-show="showHtmlView"
        class="html-view flex-1 flex flex-col"
      >
        <textarea
          v-model="htmlContent"
          class="w-full flex-1 min-h-[220px] p-4 font-mono text-sm bg-card text-card-foreground border-none resize-none focus:outline-none focus:ring-0"
          :placeholder="t('publishing.editor.placeholder.htmlCode')"
          @blur="applyHtmlChanges"
        />
      </div>
    </div>
        
    <!-- Standard Bubble Menu -->
    <BubbleMenu :editor="editor" />

    <!-- Media Specific Bubble Menu -->
    <MediaBubbleMenu
      :editor="editor"
      @open-properties="openProperties"
    />

    <!-- Media Properties Popover -->
    <PropertiesPopover 
      v-model:open="showPropertiesModal"
      :node="selectedNodeForProperties"
      :anchor="propertiesAnchor"
      @save="saveMediaProperties"
    />

    <!-- Context Menu -->
    <ContextMenu 
      :show="contextMenu.show"
      :position="contextMenu.position"
      :items="contextMenu.items"
      @close="contextMenu.show = false"
      @action="handleContextMenuAction"
    />

    <!-- Media Picker Modal -->
    <MediaPicker
      v-model:open="showMediaPicker"
      @selected="handleMediaSelect"
    >
      <template #trigger>
        <div class="hidden" />
      </template>
    </MediaPicker>

    <!-- Table Insert Dialog -->
    <TableInsertDialog 
      v-model:open="showTableDialog"
      @insert="insertTable"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, watch, onBeforeUnmount, reactive, type Component } from 'vue';
import { useEditor, EditorContent } from '@tiptap/vue-3';
import { useI18n } from 'vue-i18n';
import StarterKit from '@tiptap/starter-kit';
import Placeholder from '@tiptap/extension-placeholder';
import BubbleMenuExtension from '@tiptap/extension-bubble-menu';
import Typography from '@tiptap/extension-typography';
import TextAlign from '@tiptap/extension-text-align';
import { TextStyle } from '@tiptap/extension-text-style';
import { FontFamily } from '@tiptap/extension-font-family';
import { Color } from '@tiptap/extension-color';
import Highlight from '@tiptap/extension-highlight';
import { Table } from '@tiptap/extension-table';
import { TableRow } from '@tiptap/extension-table-row';
import { TableCell } from '@tiptap/extension-table-cell';
import { TableHeader } from '@tiptap/extension-table-header';

// Modular Components
import Toolbar from '@/shared/components/editor/Toolbar.vue';
import BubbleMenu from '@/shared/components/editor/BubbleMenu.vue';
import MediaBubbleMenu from '@/shared/components/editor/MediaBubbleMenu.vue';
import ContextMenu from '@/shared/components/editor/ContextMenu.vue';
import PropertiesPopover from '@/shared/components/editor/PropertiesPopover.vue';
import TableInsertDialog from '@/shared/components/editor/TableInsertDialog.vue';
import MediaPicker from '@/shared/components/ui/MediaPicker.vue';

// Custom Extensions
import { CustomImage } from '@/shared/components/editor/extensions/CustomImage';
import { VideoExtension } from '@/shared/components/editor/extensions/Video';
import { Dropcap } from '@/shared/components/editor/extensions/Dropcap';
import { Columns } from '@/shared/components/editor/extensions/Columns';
import { Column } from '@/shared/components/editor/extensions/Column';
import { TextColumns } from '@/shared/components/editor/extensions/TextColumns';
import { CodeBlockWithCopyExtension } from '@/shared/components/editor/extensions/CodeBlockExtension';
import { HtmlEmbed } from '@/shared/components/editor/extensions/HtmlEmbedExtension';
import { Icon } from '@/shared/components/editor/extensions/IconExtension';

import type { Media } from '@/shared/types/media';

import { createLowlight } from 'lowlight';
import javascript from 'highlight.js/lib/languages/javascript';
import typescript from 'highlight.js/lib/languages/typescript';
import html from 'highlight.js/lib/languages/xml';
import css from 'highlight.js/lib/languages/css';
import php from 'highlight.js/lib/languages/php';
import python from 'highlight.js/lib/languages/python';
import json from 'highlight.js/lib/languages/json';
import bash from 'highlight.js/lib/languages/bash';
import sql from 'highlight.js/lib/languages/sql';
import markdown from 'highlight.js/lib/languages/markdown';

import {
  Bold,
  Italic,
  Redo,
  RemoveFormatting,
  SettingsIcon,
  Trash2,
  Undo,
} from 'lucide-vue-next';

interface Props {
    modelValue?: string;
    placeholder?: string;
    compact?: boolean;
    resizable?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    modelValue: '',
    placeholder: 'Start writing...',
    compact: false,
    resizable: false,
});

const emit = defineEmits<{
    (e: 'update:modelValue', value: string): void;
}>();

const focusEditor = () => {
    if (editor.value && !editor.value.isFocused) {
        editor.value.chain().focus().run();
    }
};

const showMediaPicker = ref(false);
const showTableDialog = ref(false);
const showHtmlView = ref(false);
const htmlContent = ref('');

// Properties Popover State
interface SelectedNode {
    type: string;
    pos: number;
    attrs: Record<string, unknown>;
}

const showPropertiesModal = ref(false);
const selectedNodeForProperties = ref<SelectedNode | null>(null);
const propertiesAnchor = ref<HTMLElement | null>(null);

// Context Menu State
interface ContextMenuItem {
    label?: string;
    icon?: Component;
    action?: string;
    type?: string;
}

const contextMenu = reactive({
    show: false,
    x: 0,
    y: 0,
    items: [] as ContextMenuItem[],
    position: { x: 0, y: 0 }
});

// Create lowlight instance
const { t } = useI18n()
const lowlight = createLowlight()
lowlight.register('javascript', javascript)
lowlight.register('typescript', typescript)
lowlight.register('html', html)
lowlight.register('css', css)
lowlight.register('php', php)
lowlight.register('python', python)
lowlight.register('json', json)
lowlight.register('bash', bash)
lowlight.register('sql', sql)
lowlight.register('markdown', markdown)

const editor = useEditor({
    content: props.modelValue,
    extensions: [
        StarterKit.configure({
            codeBlock: false,
            paragraph: false,
        }),
        Placeholder.configure({
            placeholder: ({ node: _node }) => {
                if (props.placeholder === 'Start writing...') {
                    return t('publishing.editor.placeholder.text')
                }
                return props.placeholder
            },
        }),
        CustomImage,
        VideoExtension,
        Dropcap,
        Columns,
        Column,
        TextColumns,
        Typography,
        BubbleMenuExtension,
        TextAlign.configure({
            types: ['heading', 'paragraph', 'image', 'video'],
        }),
        TextStyle,
        FontFamily,
        Color,
        Highlight.configure({
            multicolor: true,
        }),
        Table.configure({
            resizable: true,
        }),
        TableRow,
        TableHeader,
        TableCell,
        CodeBlockWithCopyExtension.configure({
            lowlight,
        }),
        HtmlEmbed,
        Icon,
    ],
    editorProps: {
        attributes: {
            class: 'focus:outline-none min-h-full',
        },
        handleDOMEvents: {
            dblclick: (_view, _event) => {
                // Check if we clicked on a media node
                if (editor.value && (editor.value.isActive('image') || editor.value.isActive('video') || editor.value.isActive('htmlEmbed') || editor.value.isActive('icon'))) {
                    openProperties()
                    return true
                }
                return false
            }
        }
    },
    onUpdate: () => {
        if (editor.value) {
            emit('update:modelValue', editor.value.getHTML())
        }
    },
})

// Sync content
watch(() => props.modelValue, (newValue) => {
    const isSame = editor.value?.getHTML() === newValue;
    if (editor.value && !isSame) {
        editor.value.commands.setContent(newValue, { emitUpdate: false });
    }
});

function handleMediaSelect(media: Media) {
    const url = media?.url || media?.path;
    if (url && editor.value) {
        editor.value.chain().focus().setImage({ 
            src: url, 
            alt: media?.alt || media?.name || '' 
        }).run();
    }
    showMediaPicker.value = false;
}

function openProperties() {
    if (!editor.value) return;
    
    let type = 'image';
    if (editor.value.isActive('video')) type = 'video';
    else if (editor.value.isActive('htmlEmbed')) type = 'htmlEmbed';
    else if (editor.value.isActive('icon')) type = 'icon';

    // Get current selection position
    const { from } = editor.value.state.selection;

    selectedNodeForProperties.value = {
        type,
        pos: from,
        attrs: editor.value.getAttributes(type) as Record<string, unknown>
    };
    
    // Get DOM element for anchoring
    const dom = editor.value.view.nodeDOM(from);
    propertiesAnchor.value = dom instanceof HTMLElement ? dom : null;
    
    showPropertiesModal.value = true;
}

function saveMediaProperties(properties: Record<string, unknown>) {
    if (!editor.value || !selectedNodeForProperties.value) return;
    
    const { pos } = selectedNodeForProperties.value;
    // Use setNodeMarkup to update specific node by position, keeping focus in popover
    editor.value.chain().command(({ tr }) => {
        tr.setNodeMarkup(pos, undefined, properties as Record<string, unknown>);
        return true;
    }).run();
}

// Table Handlers
function insertTable(config: { rows: number, cols: number }) {
    if (editor.value) {
        editor.value.chain().focus().insertTable({ 
            rows: config.rows, 
            cols: config.cols, 
            withHeaderRow: true 
        }).run()
    }
}

// HTML View Handlers
function toggleHtmlView() {
    if (!editor.value) return;
    
    if (!showHtmlView.value) {
        htmlContent.value = editor.value.getHTML()
    } else {
        applyHtmlChanges()
    }
    showHtmlView.value = !showHtmlView.value
}

function applyHtmlChanges() {
    if (editor.value && htmlContent.value !== editor.value.getHTML()) {
        editor.value.commands.setContent(htmlContent.value, { emitUpdate: false })
        emit('update:modelValue', htmlContent.value)
    }
}

function insertHtmlEmbed() {
    if (editor.value) {
        editor.value.chain().focus().insertHtmlEmbed('').run()
    }
}

function insertIcon(iconName: string) {
    if (iconName && editor.value) {
        editor.value.chain().focus().insertContent({
            type: 'icon',
            attrs: {
                name: iconName
            }
        }).insertContent(' ').run() // Add space after icon for better UX
    }
}

// Context Menu Logic
const handleContextMenu = (e: MouseEvent) => {
    if (!editor.value) return 
    
    // Allow default browser context menu inside the text area in HTML view
    if (showHtmlView.value) return

    e.preventDefault()

    const items: ContextMenuItem[] = []

    // Contextual actions based on selection
    if (editor.value.isActive('image') || editor.value.isActive('video') || editor.value.isActive('htmlEmbed') || editor.value.isActive('icon')) {
        items.push({ label: t('publishing.editor.contextMenu.properties'), icon: SettingsIcon, action: 'properties' })
        items.push({ label: t('publishing.editor.contextMenu.delete'), icon: Trash2, action: 'delete' })
    } else if (editor.value.isActive('table')) {
        items.push({ label: t('publishing.editor.contextMenu.deleteTable'), icon: Trash2, action: 'deleteTable' })
    } else {
         // General actions
         items.push({ label: t('publishing.editor.contextMenu.bold'), icon: Bold, action: 'bold' })
         items.push({ label: t('publishing.editor.contextMenu.italic'), icon: Italic, action: 'italic' })
         items.push({ label: t('publishing.editor.contextMenu.cleanFormatting'), icon: RemoveFormatting, action: 'clearFormatting' })
         items.push({ type: 'separator' })
         items.push({ label: t('publishing.editor.contextMenu.undo'), icon: Undo, action: 'undo' })
         items.push({ label: t('publishing.editor.contextMenu.redo'), icon: Redo, action: 'redo' })
    }

    if (items.length === 0) return

    contextMenu.position = { x: e.clientX, y: e.clientY }
    contextMenu.items = items
    contextMenu.show = true
}

const handleContextMenuAction = (action: string) => {
    contextMenu.show = false
    
    if (!editor.value) return;

    switch (action) {
        case 'properties':
            openProperties()
            break
        case 'delete':
            editor.value.chain().focus().deleteSelection().run()
            break
        case 'deleteTable':
            editor.value.chain().focus().deleteTable().run()
            break
        case 'clearFormatting':
            editor.value.chain().focus().unsetAllMarks().clearNodes().run()
            break
        case 'bold':
            editor.value.chain().focus().toggleBold().run()
            break
        case 'italic':
            editor.value.chain().focus().toggleItalic().run()
            break
        case 'undo':
            editor.value.chain().focus().undo().run()
            break
        case 'redo':
            editor.value.chain().focus().redo().run()
            break
    }
}

onBeforeUnmount(() => {
    editor.value?.destroy()
})
</script>

<style scoped>
.resize-y {
  resize: vertical;
  overflow: auto;
}

/* Custom resize handle visibility */
.resize-y::-webkit-resizer {
  background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-grip-horizontal"><circle cx="12" cy="9" r="1"/><circle cx="19" cy="9" r="1"/><circle cx="5" cy="9" r="1"/><circle cx="12" cy="15" r="1"/><circle cx="19" cy="15" r="1"/><circle cx="5" cy="15" r="1"/></svg>');
  background-repeat: no-repeat;
  background-position: bottom right;
  opacity: 0.5;
}

:deep(.ProseMirror) {
  min-height: 180px;
  height: 100%;
  outline: none !important;
}

:deep(.ProseMirror p.is-editor-empty:first-child::before) {
  color: var(--muted-foreground, #9ca3af);
  content: attr(data-placeholder);
  float: left;
  height: 0;
  pointer-events: none;
}
</style>
