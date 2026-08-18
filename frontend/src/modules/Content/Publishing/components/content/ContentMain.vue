<template>
  <div class="space-y-6">
    <!-- Title Input (Focus Style) -->
    <div class="space-y-4">
      <input
        :value="modelValue.title"
        type="text"
        :placeholder="$t('publishing.content.form.titlePlaceholder')"
        class="w-full bg-transparent text-4xl font-bold tracking-tight border-none outline-none placeholder:text-muted-foreground/40"
        autofocus
        @input="updateTitle(($event.target as HTMLInputElement).value)"
      >
    </div>

    <!-- Classic Editor -->
    <div class="animate-in fade-in slide-in-from-top-2 duration-300">
      <TiptapEditor
        :model-value="modelValue.body || ''"
        class="min-h-[500px]"
        @update:model-value="updateField('body', $event)"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
import TiptapEditor from '@/shared/components/editor/TiptapEditor.vue';
import type { ContentForm } from '@/modules/Content/Publishing/types/content';

const props = defineProps<{
    modelValue: ContentForm;
}>();

const emit = defineEmits<{
    'update:modelValue': [value: ContentForm];
    'save': [status?: string];
    'mode-selected': [mode: string | null];
    'toggle-auto-save': [value: boolean];
    'cancel': [];
}>();




const updateField = (field: string, value: unknown) => {
    emit('update:modelValue', { ...props.modelValue, [field]: value });
};

const updateTitle = (newTitle: string) => {
    emit('update:modelValue', { ...props.modelValue, title: newTitle });
};
</script>
