<template>
  <div class="bg-card rounded-lg border border-border overflow-hidden">
    <!-- Group Header (Clickable for collapse) -->
    <button 
      type="button"
      class="w-full flex items-center gap-3 px-6 py-4 text-left transition-colors hover:bg-muted/50"
      @click="toggle"
    >
      <div
        class="p-2 rounded-lg"
        :class="iconColorClass"
      >
        <component
          :is="icon"
          class="w-5 h-5"
        />
      </div>
      <div class="flex-1">
        <h2 class="text-sm font-semibold text-foreground">
          {{ title }}
        </h2>
        <p class="text-xs text-muted-foreground">
          {{ description }}
        </p>
      </div>
      <div class="flex items-center gap-3">
        <slot name="badge" />
        <ChevronDown 
          class="w-5 h-5 text-muted-foreground transition-transform duration-200" 
          :class="{ 'rotate-180': isExpanded }"
        />
      </div>
    </button>
        
    <!-- Collapsible Content -->
    <div 
      v-show="isExpanded" 
      class="border-t border-border p-6"
    >
      <!-- Group Settings Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <slot />
      </div>

      <!-- Group Footer Action -->
      <div
        v-if="$slots.footer"
        class="mt-4 pt-4 border-t border-border"
      >
        <slot name="footer" />
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, type Component } from 'vue'
import { ChevronDown } from 'lucide-vue-next';

const props = withDefaults(defineProps<{
    title: string;
    description: string;
    icon: string | Component;
    defaultExpanded?: boolean;
    color?: 'primary' | 'blue' | 'emerald' | 'amber' | 'red' | 'purple' | 'indigo' | 'orange' | 'pink';
}>(), {
    defaultExpanded: true,
    color: 'primary',
});

const isExpanded = ref(props.defaultExpanded)

const toggle = () => {
    isExpanded.value = !isExpanded.value
}

const iconColorClass = computed(() => {
    const colorMap: Record<string, string> = {
        primary: 'bg-primary/10 text-primary',
        blue: 'bg-blue-500/10 text-blue-500',
        emerald: 'bg-emerald-500/10 text-emerald-500',
        amber: 'bg-amber-500/10 text-amber-500',
        red: 'bg-red-500/10 text-red-500',
        purple: 'bg-purple-500/10 text-purple-500',
        indigo: 'bg-indigo-500/10 text-indigo-500',
        orange: 'bg-orange-500/10 text-orange-500',
        pink: 'bg-pink-500/10 text-pink-500',
    }
    return colorMap[props.color] || colorMap.primary
})
</script>
