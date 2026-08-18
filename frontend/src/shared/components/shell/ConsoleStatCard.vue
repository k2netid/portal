<script setup lang="ts">
import type { Component } from 'vue';
import { Card, CardContent } from '@/shared/components/ui';
import { cn } from '@/shared/utils/lib-utils';

const props = withDefaults(
    defineProps<{
        label: string;
        value: string | number;
        icon?: Component;
        hint?: string;
        hintIcon?: Component;
        tone?: 'primary' | 'success' | 'destructive' | 'muted' | 'warning' | 'info';
        active?: boolean;
        clickable?: boolean;
    }>(),
    { tone: 'primary', active: false, clickable: false, icon: undefined, hint: undefined, hintIcon: undefined },
);

const emit = defineEmits<{ click: [] }>();

const iconWellClass: Record<string, string> = {
    primary: 'bg-primary/10 text-primary',
    success: 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400',
    destructive: 'bg-destructive/10 text-destructive',
    muted: 'bg-muted text-muted-foreground',
    warning: 'bg-amber-500/10 text-amber-600 dark:text-amber-400',
    info: 'bg-sky-500/10 text-sky-600 dark:text-sky-400',
};

const activeBorderClass: Record<string, string> = {
    primary: 'border-primary/40 ring-1 ring-primary/15',
    success: 'border-emerald-500/40 ring-1 ring-emerald-500/15',
    destructive: 'border-destructive/40 ring-1 ring-destructive/15',
    muted: 'border-border ring-1 ring-border/30',
    warning: 'border-amber-500/40 ring-1 ring-amber-500/15',
    info: 'border-sky-500/40 ring-1 ring-sky-500/15',
};

function onClick() {
    if (props.clickable) emit('click');
}

function onKeyActivate(event: KeyboardEvent) {
    if (!props.clickable) return;
    if (event.key === 'Enter' || event.key === ' ') {
        event.preventDefault();
        emit('click');
    }
}
</script>

<template>
  <Card
    :class="cn(
      'rounded-xl border border-border/60 bg-card shadow-none transition-colors',
      clickable && 'cursor-pointer hover:border-border focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2',
      active && activeBorderClass[tone],
    )"
    :role="clickable ? 'button' : undefined"
    :tabindex="clickable ? 0 : undefined"
    :aria-label="clickable ? label : undefined"
    :aria-pressed="clickable ? active : undefined"
    @click="onClick"
    @keydown="onKeyActivate"
  >
    <CardContent class="flex items-start justify-between p-5 gap-3">
      <div class="min-w-0 space-y-1.5 flex-1">
        <p class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground">
          {{ label }}
        </p>
        <p class="text-2xl font-bold tracking-tight text-foreground truncate tabular-nums">
          {{ value }}
        </p>
        <div
          v-if="hint"
          class="flex items-center gap-1.5 text-[11px] font-medium text-muted-foreground mt-1"
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
        v-if="icon"
        :class="cn('shrink-0 rounded-xl p-2.5 flex items-center justify-center', iconWellClass[tone])"
      >
        <component
          :is="icon"
          class="h-5 w-5"
        />
      </div>
    </CardContent>
  </Card>
</template>
