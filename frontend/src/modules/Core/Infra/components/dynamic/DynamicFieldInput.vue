<template>
  <div class="space-y-1">
    <Label :for="inputId">
      {{ field.name }}
      <span
        v-if="field.is_required"
        class="text-destructive"
      >*</span>
    </Label>

    <textarea
      v-if="field.type === 'longtext'"
      :id="inputId"
      :value="stringValue"
      class="mt-1 w-full min-h-[96px] rounded-md border border-input bg-background px-3 py-2 text-sm"
      :required="field.is_required"
      @input="onInput(($event.target as HTMLTextAreaElement).value)"
    />

    <select
      v-else-if="field.type === 'select'"
      :id="inputId"
      :value="stringValue"
      class="mt-1 w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
      :required="field.is_required"
      @change="onInput(($event.target as HTMLSelectElement).value)"
    >
      <option
        value=""
        disabled
      >
        Select…
      </option>
      <option
        v-for="opt in field.options ?? []"
        :key="opt"
        :value="opt"
      >
        {{ opt }}
      </option>
    </select>

    <div
      v-else-if="field.type === 'boolean'"
      class="flex items-center gap-2 pt-1"
    >
      <input
        :id="inputId"
        type="checkbox"
        class="h-4 w-4 rounded border-input"
        :checked="booleanValue"
        @change="onInput(($event.target as HTMLInputElement).checked)"
      >
      <span class="text-sm text-muted-foreground">Yes</span>
    </div>

    <Input
      v-else
      :id="inputId"
      :type="inputType"
      :model-value="stringValue"
      :required="field.is_required"
      class="mt-1"
      @update:model-value="onInput"
    />
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { Input, Label } from '@/shared/components/ui';
import type { CckFieldDefinition } from '../../services/cckService';

const props = defineProps<{
    field: CckFieldDefinition;
    modelValue: unknown;
}>();

const emit = defineEmits<{
    'update:modelValue': [value: unknown];
}>();

const inputId = computed(() => `dyn-${props.field.slug}`);

const stringValue = computed(() => {
    if (props.modelValue === null || props.modelValue === undefined) {
        return '';
    }
    return String(props.modelValue);
});

const booleanValue = computed(() => Boolean(props.modelValue));

const inputType = computed(() => {
    switch (props.field.type) {
        case 'email':
            return 'email';
        case 'number':
            return 'number';
        case 'date':
            return 'date';
        default:
            return 'text';
    }
});

function onInput(value: unknown): void {
    if (props.field.type === 'number' && typeof value === 'string' && value !== '') {
        emit('update:modelValue', Number(value));
        return;
    }
    emit('update:modelValue', value);
}
</script>
