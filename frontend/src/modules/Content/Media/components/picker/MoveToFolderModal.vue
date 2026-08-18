<template>
  <Dialog
    :open="true"
    @update:open="$emit('close')"
  >
    <DialogContent class="console-dialog-sm">
      <DialogHeader>
        <DialogTitle>{{ t('media.modals.moveToFolder.title') }}</DialogTitle>
        <DialogDescription>
          {{ t('media.modals.moveToFolder.description') }}
        </DialogDescription>
      </DialogHeader>

      <div class="py-4">
        <label class="text-sm font-medium mb-2 block">{{ t('media.modals.moveToFolder.selectFolder') }}</label>
        <Select v-model="selectedFolderId">
          <SelectTrigger
            class="h-10 w-full"
            :aria-label="t('media.modals.moveToFolder.selectFolder')"
          >
            <SelectValue :placeholder="t('media.modals.moveToFolder.placeholder')" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="root">
              {{ t('media.modals.moveToFolder.root') }}
            </SelectItem>
            <SelectItem
              v-for="folder in folders"
              :key="folder.id"
              :value="String(folder.id)"
            >
              {{ folder.name }}
            </SelectItem>
          </SelectContent>
        </Select>
      </div>

      <DialogFooter>
        <Button
          variant="outline"
          size="sm"
          class="h-10"
          @click="$emit('close')"
        >
          {{ t('media.actions.cancel') }}
        </Button>
        <Button
          size="sm"
          class="h-10"
          @click="handleMove"
        >
          {{ t('media.modals.moveToFolder.move') }}
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import {
    Button,
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogDescription,
    DialogFooter,
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/shared/components/ui';
import type { MediaFolder } from '@/modules/Content/Media/types/media';

const { t } = useI18n();

defineProps<{
    folders?: MediaFolder[];
}>();

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'moved', folderId: string | null): void;
}>();

const selectedFolderId = ref('root');

const handleMove = () => {
    const folderId = selectedFolderId.value === 'root' ? null : selectedFolderId.value;
    emit('moved', folderId);
};
</script>
