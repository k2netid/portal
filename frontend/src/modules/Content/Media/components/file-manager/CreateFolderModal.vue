<template>
  <Dialog
    :open="true"
    @update:open="$emit('close')"
  >
    <DialogContent class="console-dialog-sm">
      <DialogHeader>
        <DialogTitle>{{ $t('media.file_manager.modals.createFolder.title') }}</DialogTitle>
        <DialogDescription>
          {{ $t('media.file_manager.modals.createFolder.placeholder') }}
        </DialogDescription>
      </DialogHeader>

      <form
        class="grid gap-4 py-4"
        @submit.prevent="handleSubmit"
      >
        <div class="grid gap-2">
          <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
            {{ $t('media.file_manager.modals.createFolder.name') }} <span class="text-destructive">*</span>
          </label>
          <Input
            v-model="folderName"
            type="text"
            required
            :placeholder="$t('media.file_manager.modals.createFolder.placeholder')"
            class="col-span-3"
          />
        </div>
      </form>

      <DialogFooter>
        <Button
          variant="outline"
          type="button"
          class="rounded-xl h-10 px-5 border-border/60 hover:bg-accent/10 text-foreground font-bold transition-colors" size="sm" @click="$emit('close')"
        >
          {{ $t('media.file_manager.modals.createFolder.cancel') }}
        </Button>
        <Button
          :disabled="creating || !isValid"
          type="submit"
          class="rounded-xl h-10 px-5 bg-primary hover:bg-primary/90 text-primary-foreground font-bold shadow-lg shadow-primary/20 transition-colors active:scale-[0.98]"
          @click="handleSubmit"
        >
          <Loader2
            v-if="creating"
            class="w-4 h-4 mr-2 animate-spin"
          />
          {{ creating ? $t('media.file_manager.modals.createFolder.creating') : $t('media.file_manager.modals.createFolder.create') }}
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '@/engine/api/client';
import {
    Button,
    Input,
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogDescription,
    DialogFooter
} from '@/shared/components/ui';
import { useToast } from '@/shared/composables/useToast';
import { useFormValidation } from '@/shared/composables/useFormValidation';
import { folderSchema } from '@/shared/schemas';
import {
  Loader2,
} from 'lucide-vue-next';

useI18n();
const toast = useToast();
const { validateWithZod, setErrors, clearErrors } = useFormValidation(folderSchema);

const props = withDefaults(defineProps<{
    path?: string;
}>(), {
    path: '/'
});

const emit = defineEmits<{
    'close': [];
    'created': [];
}>();

const folderName = ref('');
const creating = ref(false);

const isValid = computed(() => {
    return !!folderName.value?.trim();
});

const handleSubmit = async () => {
    if (!validateWithZod({ name: folderName.value })) return;

    creating.value = true;
    clearErrors();
    try {
        await api.post('/manage/infra/file-manager/folder', {
            name: folderName.value,
            path: props.path,
        });
        toast.success.create('Folder');
        emit('created');
        emit('close');
    } catch (error: unknown) {
        const err = error as import('axios').AxiosError<{ errors?: Record<string, string[]> }>;
        if (err.response?.status === 422) {
            setErrors(err.response.data.errors || {});
        } else {
            toast.error.fromResponse(err);
        }
    } finally {
        creating.value = false;
    }
};
</script>

