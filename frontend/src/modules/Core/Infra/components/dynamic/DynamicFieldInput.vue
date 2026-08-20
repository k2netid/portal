<template>
  <div class="space-y-1.5">
    <div class="flex items-center justify-between">
      <Label :for="inputId" class="text-xs font-medium text-foreground">
        {{ field.name }}
        <span
          v-if="field.is_required"
          class="text-destructive font-bold ml-0.5"
        >*</span>
      </Label>
      <span class="text-[11px] font-mono text-muted-foreground">
        {{ field.slug }}
      </span>
    </div>

    <!-- Long Text / Textarea -->
    <Textarea
      v-if="field.type === 'longtext'"
      :id="inputId"
      :model-value="stringValue"
      rows="4"
      class="text-xs resize-y font-normal"
      :required="field.is_required"
      :placeholder="field.name"
      @update:model-value="onInput"
    />

    <!-- Select Dropdown -->
    <div v-else-if="field.type === 'select'" class="relative">
      <select
        :id="inputId"
        :value="stringValue"
        class="w-full h-9 rounded-md border border-input bg-background px-3 py-1.5 text-xs text-foreground focus:outline-none focus:ring-2 focus:ring-primary/20"
        :required="field.is_required"
        @change="onInput(($event.target as HTMLSelectElement).value)"
      >
        <option value="" disabled>
          {{ $t('infra.dynamic.record.selectPlaceholder') }}
        </option>
        <option
          v-for="opt in field.options ?? []"
          :key="opt"
          :value="opt"
        >
          {{ opt }}
        </option>
      </select>
    </div>

    <!-- Boolean Switch / Checkbox -->
    <div
      v-else-if="field.type === 'boolean'"
      class="flex items-center gap-2.5 pt-1"
    >
      <Checkbox
        :id="inputId"
        :checked="booleanValue"
        @update:checked="onInput($event === true)"
      />
      <label :for="inputId" class="text-xs text-foreground cursor-pointer select-none">
        {{ booleanValue ? $t('infra.dynamic.record.booleanYes') : $t('infra.dynamic.record.booleanNo') }}
      </label>
    </div>

    <!-- Image URL with Preview -->
    <div v-else-if="field.type === 'image'" class="space-y-2">
      <div class="flex gap-2">
        <Input
          :id="inputId"
          type="text"
          :model-value="stringValue"
          :required="field.is_required"
          class="h-9 text-xs"
          placeholder="https://example.com/image.jpg"
          @update:model-value="onInput"
        />
      </div>
      <div
        v-if="stringValue"
        class="flex items-center gap-3 p-2 rounded-lg border border-border/60 bg-muted/20"
      >
        <div class="w-12 h-12 rounded border bg-background overflow-hidden shrink-0 flex items-center justify-center">
          <img
            :src="stringValue"
            alt="Preview"
            class="w-full h-full object-cover"
            @error="imageLoadError = true"
            @load="imageLoadError = false"
          >
        </div>
        <div class="min-w-0 flex-1">
          <p class="text-[11px] font-medium text-foreground truncate">{{ stringValue }}</p>
          <p class="text-[10px] text-muted-foreground">
            {{ imageLoadError ? 'Image preview unavailable' : $t('infra.dynamic.record.imagePreview') }}
          </p>
        </div>
      </div>
    </div>

    <!-- Standard Text / Number / Email / Date Input -->
    <Input
      v-else
      :id="inputId"
      :type="inputType"
      :model-value="stringValue"
      :required="field.is_required"
      class="h-9 text-xs"
      :placeholder="field.name"
      @update:model-value="onInput"
    />
  </div>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue';
import { Input, Label, Textarea, Checkbox } from '@/shared/components/ui';
import type { CckFieldDefinition } from '../../services/cckService';

const props = defineProps<{
    field: CckFieldDefinition;
    modelValue: unknown;
}>();

const emit = defineEmits<{
    'update:modelValue': [value: unknown];
}>();

const imageLoadError = ref(false);

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
    if (props.field.type === 'number') {
        if (typeof value === 'string') {
            if (value === '') {
                emit('update:modelValue', null);
                return;
            }
            const num = Number(value);
            emit('update:modelValue', isNaN(num) ? value : num);
            return;
        }
    }
    emit('update:modelValue', value);
}
</script>
