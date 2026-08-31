<template>
  <div class="min-h-[50vh] flex flex-col items-center justify-center p-8 text-center space-y-4">
    <div class="w-16 h-16 rounded-2xl bg-orange-500/10 text-orange-500 flex items-center justify-center">
      <AlertTriangle class="w-8 h-8" />
    </div>
    <h2 class="text-2xl font-bold font-heading text-foreground">
      {{ title || 'Halaman Dinonaktifkan' }}
    </h2>
    <p class="text-sm text-muted-foreground max-w-md">
      {{ message || 'Halaman ini sedang tidak dapat diakses atau dinonaktifkan oleh administrator jaringan.' }}
    </p>
    <Button
      as="router-link"
      to="/"
      variant="outline"
      size="sm"
    >
      Kembali ke Beranda
    </Button>
  </div>
</template>

<script setup lang="ts">
import { AlertTriangle } from 'lucide-vue-next';
import { Button } from '@/modules/Layout/views/themes/layung/ui';

defineProps<{
  title?: string;
  message?: string;
}>();
</script>
