<template>
  <div 
    v-if="show"
    class="fixed z-[100] min-w-[160px] bg-background border border-border rounded-md shadow-xl p-1 animate-in fade-in zoom-in-95 duration-100"
    :style="{ top: `${adjustedPosition.y}px`, left: `${adjustedPosition.x}px` }"
    @mousedown.stop
    @contextmenu.prevent
  >
    <template v-for="(item, index) in items">
      <div
        v-if="item.type === 'separator'"
        :key="'sep-' + index"
        class="my-1 border-t border-border/50"
      />
      <button
        v-else
        :key="item.label"
        class="w-full flex items-center gap-2 px-2 py-1.5 text-xs text-foreground hover:bg-accent hover:text-accent-foreground rounded-sm transition-colors text-left"
        @click="item.action && $emit('action', item.action)"
      >
        <component
          :is="item.icon"
          v-if="item.icon"
          class="w-3.5 h-3.5 opacity-70"
        />
        {{ item.label }}
      </button>
    </template>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted, onBeforeUnmount, type Component } from 'vue';

interface ContextMenuItem {
    label?: string;
    icon?: Component | string;
    action?: string;
    type?: 'separator' | string;
}

const props = withDefaults(defineProps<{
    show: boolean;
    position?: { x: number; y: number };
    items?: ContextMenuItem[];
}>(), {
    position: () => ({ x: 0, y: 0 }),
    items: () => []
});

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'action', action: string): void;
}>();

const adjustedPosition = computed(() => {
    // Basic adjustment to keep menu on screen
    const menuWidth = 160;
    const menuHeight = props.items.length * 32;
    
    let x = props.position.x;
    let y = props.position.y;
    
    if (x + menuWidth > window.innerWidth) x -= menuWidth;
    if (y + menuHeight > window.innerHeight) y -= menuHeight;
    
    return { x, y };
});

const handleGlobalClick = () => {
    if (props.show) emit('close');
};

onMounted(() => {
    document.addEventListener('mousedown', handleGlobalClick);
});

onBeforeUnmount(() => {
    document.removeEventListener('mousedown', handleGlobalClick);
});
</script>
