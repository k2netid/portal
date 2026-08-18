<template>
  <div
    v-if="open"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
    @click.self="$emit('update:open', false)"
  >
    <div class="bg-background border border-border rounded-lg shadow-xl p-6 w-[300px] space-y-4">
      <h3 class="text-lg font-semibold text-foreground">
        {{ t('publishing.editor.tableDialog.title') }}
      </h3>
      <div class="space-y-3">
        <div class="flex items-center justify-between">
          <label class="text-sm text-muted-foreground">{{ t('publishing.editor.tableDialog.rows') }}</label>
          <input 
            v-model.number="config.rows" 
            type="number" 
            min="1" 
            max="20"
            class="w-20 px-2 py-1 text-sm border border-border rounded-md bg-background text-foreground"
          >
        </div>
        <div class="flex items-center justify-between">
          <label class="text-sm text-muted-foreground">{{ t('publishing.editor.tableDialog.columns') }}</label>
          <input 
            v-model.number="config.cols" 
            type="number" 
            min="1" 
            max="10"
            class="w-20 px-2 py-1 text-sm border border-border rounded-md bg-background text-foreground"
          >
        </div>
      </div>
      <div class="flex justify-end gap-2 pt-2">
        <Button
          variant="outline"
          size="sm"
          @click="$emit('update:open', false)"
        >
          {{ t('common.actions.cancel') }}
        </Button>
        <Button
          size="sm"
          @click="insert"
        >
          {{ t('publishing.editor.tableDialog.insert') }}
        </Button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { Button } from '@/shared/components/ui';

const { t } = useI18n();

interface TableConfig {
    rows: number;
    cols: number;
}

defineProps<{
    open: boolean;
}>();

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void;
    (e: 'insert', config: TableConfig): void;
}>();

const config = ref<TableConfig>({ rows: 3, cols: 3 });

function insert() {
    emit('insert', { ...config.value });
    emit('update:open', false);
    config.value = { rows: 3, cols: 3 }; // Reset
}
</script>
