<template>
  <div 
    class="inline-richtext" 
    :class="{ 'is-focused': isFocused }"
    @click.stop
  >
    <editor-content v-if="editor" :editor="editor as any" class="inline-editor-content" />
    
    <!-- Floating Toolbar -->
    <Teleport to="body">
      <div 
        v-if="editor && isFocused" 
        class="inline-richtext-toolbar"
        :style="toolbarStyle"
        @mousedown.prevent
      >
        <RichtextToolbar :editor="editor" />
      </div>
    </Teleport>
  </div>
</template>

<script setup lang="ts">
import { ref, shallowRef, watch, onBeforeUnmount, onMounted, computed, nextTick } from 'vue';
import { Editor, EditorContent } from '@tiptap/vue-3';
import StarterKit from '@tiptap/starter-kit';
import { Underline } from '@tiptap/extension-underline';
import { TextStyle } from '@tiptap/extension-text-style';
import { Color } from '@tiptap/extension-color';
import { TextAlign } from '@tiptap/extension-text-align';
import { Link } from '@tiptap/extension-link';
import { Image as ImageExt } from '@tiptap/extension-image';

import RichtextToolbar from '../fields/RichtextToolbar.vue';

interface Props {
  modelValue?: string;
  placeholder?: string;
}

const props = withDefaults(defineProps<Props>(), {
  modelValue: '',
  placeholder: 'Type content...'
});

const emit = defineEmits<{
  (e: 'update:modelValue', value: string): void;
  (e: 'focus'): void;
  (e: 'blur'): void;
}>();

const isFocused = ref(false);
const editor = shallowRef<Editor | null>(null);
const toolbarPos = ref({ top: 0, left: 0 });

const toolbarStyle = computed(() => ({
  top: `${toolbarPos.value.top}px`,
  left: `${toolbarPos.value.left}px`
}));

const updateToolbarPosition = () => {
    if (!editor.value) return;
    
    const { view } = editor.value;
    const { state } = view;
    const { selection } = state;
    
    // Get the coordinates of the selection
    const coords = view.coordsAtPos(selection.from);
    
    // Position toolbar above selection
    toolbarPos.value = {
        top: coords.top - 80, // Offset above the text
        left: Math.max(10, coords.left - 150) // Try to center roughly
    };
};

onMounted(() => {
  editor.value = new Editor({
    content: props.modelValue,
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
      emit('update:modelValue', editor.getHTML());
      nextTick(updateToolbarPosition);
    },
    onFocus: () => {
        isFocused.value = true;
        emit('focus');
        nextTick(updateToolbarPosition);
    },
    onBlur: () => {
        // Delay blur to allow clicking toolbar
        setTimeout(() => {
            const activeEl = document.activeElement;
            if (!activeEl || !activeEl.closest('.inline-richtext-toolbar')) {
                isFocused.value = false;
                emit('blur');
            }
        }, 200);
    },
    editorProps: {
      attributes: {
        class: 'prose-edit focus:outline-none'
      }
    }
  });
});

onBeforeUnmount(() => {
  if (editor.value) {
    editor.value.destroy();
  }
});

watch(() => props.modelValue, (newVal) => {
  if (editor.value && newVal !== editor.value.getHTML()) {
    editor.value.commands.setContent(newVal, { emitUpdate: false });
  }
});
</script>

<style scoped>
.inline-richtext {
  width: 100%;
  cursor: text;
}

.inline-editor-content :deep(.prose-edit) {
  min-height: 1em;
}

.inline-richtext-toolbar {
  position: fixed;
  z-index: 100001 !important;
  background: var(--builder-bg-primary);
  border: 1px solid var(--builder-border);
  border-radius: var(--border-radius-md);
  box-shadow: var(--shadow-lg);
  padding: 4px;
  pointer-events: auto;
  min-width: 300px;
}

/* Ensure the toolbar looks compact */
.inline-richtext-toolbar :deep(.richtext-toolbar) {
    border-bottom: none;
    padding: 2px;
}
</style>
