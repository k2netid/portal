<template>
  <bubble-menu 
    v-if="editor" 
    :editor="editor" 
    :tippy-options="{ duration: 100 }" 
    :should-show="shouldShow"
    class="flex items-center gap-1 p-1 bg-background border border-border rounded-md shadow-md"
  >
    <Button
      variant="ghost"
      size="sm"
      :class="{ 'bg-muted': editor.isActive('bold') }"
      @click="editor.chain().focus().toggleBold().run()"
    >
      <Bold class="w-3 h-3" />
    </Button>
    <Button
      variant="ghost"
      size="sm"
      :class="{ 'bg-muted': editor.isActive('italic') }"
      @click="editor.chain().focus().toggleItalic().run()"
    >
      <Italic class="w-3 h-3" />
    </Button>
    <Button
      variant="ghost"
      size="sm"
      :class="{ 'bg-muted': editor.isActive('underline') }"
      @click="editor.chain().focus().toggleUnderline().run()"
    >
      <UnderlineIcon class="w-3 h-3" />
    </Button>
    <Button
      variant="ghost"
      size="sm"
      :class="{ 'bg-muted': editor.isActive('strike') }"
      @click="editor.chain().focus().toggleStrike().run()"
    >
      <Strikethrough class="w-3 h-3" />
    </Button>
  </bubble-menu>
</template>

<script setup lang="ts">
import { BubbleMenu } from '@tiptap/vue-3/menus';
import { Button } from '@/shared/components/ui';
import {
  Bold,
  Italic,
  Strikethrough,
  UnderlineIcon,
} from 'lucide-vue-next';
import type { Editor } from '@tiptap/vue-3';

defineProps<{
    editor: Editor | undefined;
}>();

 
const shouldShow = (props: any) => {
    const { editor } = props;
    // Don't show if image or video is selected (we will have a different bubble for that)
    if (editor.isActive('image') || editor.isActive('video') || editor.isActive('htmlEmbed') || editor.isActive('icon')) {
        return false;
    }
    return editor.isEditable && !editor.state.selection.empty;
};
</script>
