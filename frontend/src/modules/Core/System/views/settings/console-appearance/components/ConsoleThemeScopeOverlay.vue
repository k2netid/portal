<template>
  <div
    v-if="disabled"
    class="absolute inset-0 z-10 flex flex-col items-center justify-center bg-background/90 p-6 text-center backdrop-blur-sm"
    role="status"
  >
    <component
      :is="icon"
      class="mb-3 h-8 w-8 text-muted-foreground"
      aria-hidden="true"
    />
    <p class="text-sm font-medium text-foreground">
      {{ title }}
    </p>
    <p class="mt-1 max-w-md text-xs text-muted-foreground">
      {{ description }}
    </p>
    <Button
      v-if="switchTabLabel"
      type="button"
      variant="secondary"
      size="sm"
      class="mt-4"
      @click="emit('switch-tab')"
    >
      {{ switchTabLabel }}
    </Button>
  </div>
</template>

<script setup lang="ts">
import type { Component } from 'vue';
import { Button } from '@/shared/components/ui';

defineProps<{
    disabled: boolean;
    title: string;
    description: string;
    switchTabLabel?: string;
    icon?: Component;
}>();

const emit = defineEmits<{
    'switch-tab': [];
}>();
</script>
