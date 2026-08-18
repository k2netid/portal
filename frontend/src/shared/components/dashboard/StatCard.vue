<template>
  <div
    class="rounded-xl bg-card px-5 py-4 border border-border/40 shadow-none"
    :class="[rootToneClass, variant === 'console' ? 'console-stat-card' : 'member-kpi']"
  >
    <div class="flex items-start justify-between gap-3">
      <div class="min-w-0 flex-1 space-y-2">
        <p class="text-[11px] font-semibold uppercase tracking-wider text-muted-foreground">
          {{ label }}
        </p>
        <p class="text-2xl sm:text-3xl font-bold tabular-nums tracking-tight text-foreground truncate">
          {{ value }}
        </p>
        <div
          v-if="hint"
          class="flex items-center gap-1.5 text-xs font-medium text-muted-foreground"
        >
          <component
            v-if="hintIcon"
            :is="hintIcon"
            class="h-3.5 w-3.5 shrink-0 opacity-70"
          />
          <span class="truncate">{{ hint }}</span>
        </div>
      </div>
      <div
        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg border border-border/40"
        :class="iconWrapClass"
      >
        <component
          :is="icon"
          class="h-5 w-5"
        />
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { Component } from 'vue';
import { computed } from 'vue';

const props = withDefaults(
    defineProps<{
        label: string;
        value: string;
        hint?: string;
        hintIcon?: Component;
        icon: Component;
        tone?: 'default' | 'primary' | 'success' | 'warning';
        variant?: 'console' | 'member';
    }>(),
    { hint: undefined, hintIcon: undefined, tone: 'default', variant: 'console' },
);

const rootToneClass = computed(() => {
    if (props.variant === 'member') {
        if (props.tone === 'primary') return 'member-kpi--primary';
        if (props.tone === 'success') return 'member-kpi--success';
        if (props.tone === 'warning') return 'member-kpi--warning';
        return 'member-kpi--default';
    }
    if (props.tone === 'primary') return 'console-stat-card--primary';
    if (props.tone === 'success') return 'console-stat-card--success';
    if (props.tone === 'warning') return 'console-stat-card--warning';
    return 'console-stat-card--default';
});

const iconWrapClass = computed(() => {
    if (props.tone === 'primary') return 'bg-primary/10 text-primary';
    if (props.tone === 'success') return 'bg-success/10 text-success';
    if (props.tone === 'warning') return 'bg-warning/10 text-warning';
    return 'bg-muted/50 text-muted-foreground';
});
</script>
