<template>
  <Select
    :model-value="modelValue"
    @update:model-value="onUpdate"
  >
    <SelectTrigger
      class="h-auto min-h-11 w-full py-2.5 px-3 text-left [&>svg]:shrink-0"
      :aria-label="ariaLabel"
    >
      <div
        v-if="selected"
        class="flex min-w-0 flex-1 items-center gap-2.5 pr-2"
      >
        <span
          class="shrink-0 rounded-md border border-border/60 shadow-sm"
          :class="swatchClass"
          :style="selected.swatchStyle"
          aria-hidden="true"
        />
        <span class="min-w-0 flex-1">
          <span class="block text-sm font-medium leading-tight text-foreground">
            {{ selected.label }}
          </span>
          <span
            v-if="selected.description"
            class="mt-0.5 block text-xs leading-snug text-foreground/80 line-clamp-1"
          >
            {{ selected.description }}
          </span>
        </span>
      </div>
      <span
        v-else
        class="text-sm text-muted-foreground"
      >{{ placeholder }}</span>
    </SelectTrigger>

    <SelectContent
      class="console-rich-select-content w-[var(--radix-select-trigger-width)] min-w-[18rem] max-w-[min(100vw-2rem,28rem)]"
      :side-offset="4"
    >
      <SelectItem
        v-for="opt in options"
        :key="opt.value"
        :value="opt.value"
        class="console-rich-select-item"
      >
        <div class="flex items-center gap-2.5 py-0.5">
          <span
            class="shrink-0 rounded-md border border-border/60 shadow-sm"
            :class="swatchClass"
            :style="opt.swatchStyle"
            aria-hidden="true"
          />
          <span class="min-w-0 flex-1">
            <span class="block text-sm font-medium leading-tight text-foreground">
              {{ opt.label }}
            </span>
            <span
              v-if="opt.description"
              class="mt-0.5 block text-xs leading-snug text-foreground/80"
            >
              {{ opt.description }}
            </span>
          </span>
        </div>
      </SelectItem>
    </SelectContent>
  </Select>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
} from '@/shared/components/ui';

export interface ConsoleRichSelectOption {
    value: string;
    label: string;
    description?: string;
    swatchStyle?: Record<string, string>;
}

const props = withDefaults(
    defineProps<{
        modelValue: string;
        options: ConsoleRichSelectOption[];
        placeholder?: string;
        ariaLabel?: string;
        swatchClass?: string;
    }>(),
    {
        placeholder: '',
        ariaLabel: '',
        swatchClass: 'h-8 w-8',
    },
);

const emit = defineEmits<{
    'update:modelValue': [value: string];
}>();

const selected = computed(() =>
    props.options.find((o) => o.value === props.modelValue),
);

function onUpdate(value: string) {
    emit('update:modelValue', value);
}
</script>
