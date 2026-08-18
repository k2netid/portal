<template>
  <div class="min-h-screen bg-muted/30 flex items-center justify-center p-6 bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-background via-muted/20 to-muted/40">
    <div class="max-w-md w-full bg-card border border-border rounded-[2rem] shadow-2xl overflow-hidden animate-in zoom-in-95 duration-500">
      <div class="p-8 pb-6 flex flex-col items-center text-center">
        <!-- Icon Slot -->
        <div class="mb-6">
          <slot name="icon" />
        </div>

        <!-- Title Code -->
        <h1 class="text-5xl font-bold tracking-tighter mb-2 text-foreground">
          <slot name="title" />
        </h1>

        <!-- Message Header -->
        <h2 class="text-lg font-semibold mb-3 text-foreground">
          <slot name="message" />
        </h2>

        <!-- Description -->
        <p class="text-sm text-muted-foreground leading-relaxed px-4">
          <slot name="description" />
        </p>

        <!-- Actions -->
        <div class="w-full flex gap-3 mt-8">
          <slot name="actions" />
        </div>
      </div>

      <!-- Footer -->
      <div class="px-8 py-6 border-t border-border/50 bg-muted/30">
        <div class="flex items-center justify-center text-[10px] text-muted-foreground font-medium uppercase tracking-wider">
          <slot name="footer" />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
// Error Layout Component - Compact Design
</script>
