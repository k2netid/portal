<template>
  <Dialog
    :open="isOpen"
    @update:open="v => { if(!v) $emit('close') }"
  >
    <DialogContent class="!p-0 !gap-0 max-w-lg flex flex-col overflow-hidden rounded-2xl border border-border/80 bg-card shadow-2xl [&>button[aria-label=Close]]:hidden">
      <!-- Header -->
      <div class="h-12 px-5 bg-muted/40 border-b border-border/40 flex items-center justify-between select-none shrink-0">
        <DialogTitle class="text-sm font-bold text-foreground flex items-center gap-2">
          <Keyboard class="w-4 h-4 text-primary" />
          <span>Keyboard Shortcuts Cheatsheet</span>
        </DialogTitle>

        <Button
          variant="ghost"
          size="icon"
          class="h-7 w-7 text-muted-foreground hover:text-foreground rounded-lg"
          @click="$emit('close')"
        >
          <X class="w-4 h-4" />
        </Button>
      </div>

      <!-- Shortcuts Grid -->
      <div class="p-5 space-y-4 max-h-[70vh] overflow-y-auto custom-scrollbar">
        <!-- Navigation -->
        <div class="space-y-2">
          <h4 class="text-[11px] font-bold uppercase tracking-wider text-muted-foreground">Navigation & Selection</h4>
          <div class="space-y-1.5">
            <div class="flex items-center justify-between text-xs py-1 px-2 rounded-lg bg-muted/20">
              <span class="text-foreground font-medium">Next email in list</span>
              <div class="flex items-center gap-1">
                <kbd class="px-1.5 py-0.5 rounded bg-muted border border-border/60 text-[10px] font-mono font-bold">J</kbd>
                <span class="text-muted-foreground text-[10px]">or</span>
                <kbd class="px-1.5 py-0.5 rounded bg-muted border border-border/60 text-[10px] font-mono font-bold">↓</kbd>
              </div>
            </div>

            <div class="flex items-center justify-between text-xs py-1 px-2 rounded-lg bg-muted/20">
              <span class="text-foreground font-medium">Previous email in list</span>
              <div class="flex items-center gap-1">
                <kbd class="px-1.5 py-0.5 rounded bg-muted border border-border/60 text-[10px] font-mono font-bold">K</kbd>
                <span class="text-muted-foreground text-[10px]">or</span>
                <kbd class="px-1.5 py-0.5 rounded bg-muted border border-border/60 text-[10px] font-mono font-bold">↑</kbd>
              </div>
            </div>

            <div class="flex items-center justify-between text-xs py-1 px-2 rounded-lg bg-muted/20">
              <span class="text-foreground font-medium">Focus search bar</span>
              <kbd class="px-2 py-0.5 rounded bg-muted border border-border/60 text-[10px] font-mono font-bold">/</kbd>
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div class="space-y-2">
          <h4 class="text-[11px] font-bold uppercase tracking-wider text-muted-foreground">Mail Actions</h4>
          <div class="space-y-1.5">
            <div class="flex items-center justify-between text-xs py-1 px-2 rounded-lg bg-muted/20">
              <span class="text-foreground font-medium">Compose new email</span>
              <kbd class="px-2 py-0.5 rounded bg-muted border border-border/60 text-[10px] font-mono font-bold">C</kbd>
            </div>

            <div class="flex items-center justify-between text-xs py-1 px-2 rounded-lg bg-muted/20">
              <span class="text-foreground font-medium">Reply to email</span>
              <kbd class="px-2 py-0.5 rounded bg-muted border border-border/60 text-[10px] font-mono font-bold">R</kbd>
            </div>

            <div class="flex items-center justify-between text-xs py-1 px-2 rounded-lg bg-muted/20">
              <span class="text-foreground font-medium">Forward email</span>
              <kbd class="px-2 py-0.5 rounded bg-muted border border-border/60 text-[10px] font-mono font-bold">F</kbd>
            </div>

            <div class="flex items-center justify-between text-xs py-1 px-2 rounded-lg bg-muted/20">
              <span class="text-foreground font-medium">Toggle star status</span>
              <kbd class="px-2 py-0.5 rounded bg-muted border border-border/60 text-[10px] font-mono font-bold">S</kbd>
            </div>

            <div class="flex items-center justify-between text-xs py-1 px-2 rounded-lg bg-muted/20">
              <span class="text-foreground font-medium">Move to Trash</span>
              <div class="flex items-center gap-1">
                <kbd class="px-1.5 py-0.5 rounded bg-muted border border-border/60 text-[10px] font-mono font-bold">#</kbd>
                <span class="text-muted-foreground text-[10px]">or</span>
                <kbd class="px-1.5 py-0.5 rounded bg-muted border border-border/60 text-[10px] font-mono font-bold">Del</kbd>
              </div>
            </div>
          </div>
        </div>

        <!-- System -->
        <div class="space-y-2">
          <h4 class="text-[11px] font-bold uppercase tracking-wider text-muted-foreground">General</h4>
          <div class="space-y-1.5">
            <div class="flex items-center justify-between text-xs py-1 px-2 rounded-lg bg-muted/20">
              <span class="text-foreground font-medium">Show keyboard shortcuts</span>
              <kbd class="px-2 py-0.5 rounded bg-muted border border-border/60 text-[10px] font-mono font-bold">?</kbd>
            </div>
            <div class="flex items-center justify-between text-xs py-1 px-2 rounded-lg bg-muted/20">
              <span class="text-foreground font-medium">Close active modal or detail</span>
              <kbd class="px-2 py-0.5 rounded bg-muted border border-border/60 text-[10px] font-mono font-bold">Esc</kbd>
            </div>
          </div>
        </div>
      </div>

      <!-- Footer -->
      <div class="h-11 px-5 bg-muted/30 border-t border-border/40 flex items-center justify-between text-[11px] text-muted-foreground shrink-0">
        <span>Press <kbd class="px-1 py-0.2 rounded bg-muted font-mono font-bold text-[10px]">Esc</kbd> anytime to dismiss.</span>
        <Button
          size="sm"
          class="h-7 text-xs px-3"
          @click="$emit('close')"
        >
          Got it
        </Button>
      </div>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { Keyboard, X } from 'lucide-vue-next';
import {
  Dialog,
  DialogContent,
  DialogTitle,
  Button,
} from '@/shared/components/ui';

defineProps<{
    isOpen: boolean;
}>();

defineEmits<{
    (e: 'close'): void;
}>();
</script>
