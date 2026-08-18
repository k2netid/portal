<template>
  <Accordion 
    type="single" 
    collapsible 
    :default-value="defaultOpen ? 'item-1' : ''"
    :model-value="modelValue !== undefined ? (modelValue ? 'item-1' : '') : undefined"
    @update:model-value="handleUpdate"
    class="w-full border-none"
  >
    <AccordionItem value="item-1" class="border-none">
      <div class="flex items-center justify-between group">
        <AccordionTrigger 
            :class="cn(
                'flex-1 py-2 px-3 text-[11px] font-bold tracking-wider text-slate-500 hover:text-slate-900 transition-colors uppercase hover:no-underline',
                { 'flex-row-reverse justify-between': iconPosition === 'right' }
            )"
        >
          <slot name="title">
            <span>{{ title }}</span>
          </slot>
        </AccordionTrigger>
        <div class="flex items-center gap-2 pr-3">
            <slot name="actions" />
        </div>
      </div>
      <AccordionContent class="px-3 pb-4 pt-1">
        <slot />
      </AccordionContent>
    </AccordionItem>
  </Accordion>
</template>

<script setup lang="ts">
import {
  Accordion,
  AccordionItem,
  AccordionTrigger,
  AccordionContent
} from '@/components/ui';
import { cn } from '@/lib/utils';

interface Props {
  modelValue?: boolean;
  title?: string;
  defaultOpen?: boolean;
  iconPosition?: string;
}

withDefaults(defineProps<Props>(), {
  modelValue: undefined,
  title: '',
  defaultOpen: false,
  iconPosition: 'left'
});

const emit = defineEmits<{
  (e: 'update:modelValue', value: boolean): void;
}>();

const handleUpdate = (val: string | string[] | undefined) => {
    // Accordion type="single" emits string or undefined.
    // val is string value of the item ('item-1') if open, or undefined/empty if closed.
    emit('update:modelValue', val === 'item-1');
};
</script>
```
