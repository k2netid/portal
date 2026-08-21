<template>
  <Dialog
    :open="true"
    @update:open="$emit('close')"
  >
    <DialogContent class="console-dialog-md sm:max-w-[600px]">
      <DialogHeader>
        <DialogTitle>
          {{ task ? t('system.system.scheduled_tasks.modal.title_edit') : t('system.system.scheduled_tasks.modal.title_create') }}
        </DialogTitle>
        <DialogDescription class="sr-only">
          {{ task ? t('system.system.scheduled_tasks.modal.title_edit') : t('system.system.scheduled_tasks.modal.title_create') }}
        </DialogDescription>
      </DialogHeader>

      <form
        class="p-6 space-y-4"
        @submit.prevent="handleSubmit"
      >
        <div>
          <label class="block text-sm font-medium text-foreground mb-1">
            {{ t('system.system.scheduled_tasks.modal.name_label') }} <span class="text-red-500">*</span>
          </label>
          <input
            v-model="form.name"
            type="text"
            required
            class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
            :placeholder="t('system.system.scheduled_tasks.modal.name_placeholder')"
          >
        </div>

        <div>
          <label class="block text-sm font-medium text-foreground mb-1">
            {{ t('system.system.scheduled_tasks.modal.command_label') }} <span class="text-red-500">*</span>
          </label>
          <input
            v-model="form.command"
            type="text"
            required
            class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-2 focus:ring-primary font-mono text-sm"
            :placeholder="t('system.system.scheduled_tasks.modal.command_placeholder')"
          >
          <p class="mt-1 text-xs text-muted-foreground">
            {{ t('system.system.scheduled_tasks.modal.command_help') }}
          </p>
        </div>

        <div>
          <label class="block text-sm font-medium text-foreground mb-1">
            {{ t('system.system.scheduled_tasks.modal.schedule_label') }} <span class="text-red-500">*</span>
          </label>
          <input
            v-model="form.schedule"
            type="text"
            required
            class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-2 focus:ring-primary font-mono text-sm"
            :placeholder="t('system.system.scheduled_tasks.modal.schedule_placeholder')"
          >
          <p class="mt-1 text-xs text-muted-foreground">
            {{ t('system.system.scheduled_tasks.modal.schedule_help') }}
          </p>
        </div>

        <div>
          <label class="block text-sm font-medium text-foreground mb-1">
            {{ t('system.system.scheduled_tasks.modal.description_label') }}
          </label>
          <textarea
            v-model="form.description"
            rows="3"
            class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
            :placeholder="t('system.system.scheduled_tasks.modal.description_placeholder')"
          />
        </div>

        <div class="flex items-center">
          <input
            id="is_active"
            v-model="form.is_active"
            type="checkbox"
            class="h-4 w-4 text-primary focus:ring-primary border-input rounded"
          >
          <label
            for="is_active"
            class="ml-2 block text-sm text-foreground"
          >
            {{ t('system.system.scheduled_tasks.modal.active_label') }}
          </label>
        </div>
      </form>

      <DialogFooter>
        <Button
          variant="outline"
          size="sm" class="h-10" @click="$emit('close')"
        >
          {{ t('system.system.scheduled_tasks.modal.cancel') }}
        </Button>
        <Button
          :disabled="saving"
          @click="handleSubmit"
        >
          <Loader2
            v-if="saving"
            class="w-4 h-4 mr-2 animate-spin"
          />
          {{ saving ? t('system.system.scheduled_tasks.modal.saving') : (task ? t('system.system.scheduled_tasks.modal.update') : t('system.system.scheduled_tasks.modal.create_action')) }}
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '@/engine/api/client';
import { useToast } from '@/shared/composables/useToast';
import { useFormValidation } from '@/shared/composables/useFormValidation';
import { taskSchema } from '@/shared/schemas';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogDescription,
    DialogFooter,
    Button
} from '@/shared/components/ui';
import {
  Loader2,
} from 'lucide-vue-next';

interface Task {
    id: string;
    name: string;
    command: string;
    schedule: string;
    description: string;
    is_active: boolean;
}

interface TaskForm {
    name: string;
    command: string;
    schedule: string;
    description: string;
    is_active: boolean;
}

const { t } = useI18n();
const toast = useToast();
const { validateWithZod, setErrors, clearErrors } = useFormValidation(taskSchema);

const props = defineProps<{
    task?: Task | null;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'saved'): void;
}>();

const saving = ref(false);

const form = ref<TaskForm>({
    name: '',
    command: '',
    schedule: '',
    description: '',
    is_active: true,
});

const loadTask = () => {
    if (props.task) {
        form.value = {
            name: props.task.name || '',
            command: props.task.command || '',
            schedule: props.task.schedule || '',
            description: props.task.description || '',
            is_active: props.task.is_active !== undefined ? props.task.is_active : true,
        };
    }
};

const handleSubmit = async () => {
    if (!validateWithZod(form.value)) return;

    saving.value = true;
    clearErrors();
    try {
        if (props.task) {
            await api.put(`/manage/system/scheduled-tasks/${props.task.id}`, form.value);
            toast.success.update('Scheduled Task');
        } else {
            await api.post('/manage/system/scheduled-tasks', form.value);
            toast.success.create('Scheduled Task');
        }
        emit('saved');
    } catch (error: unknown) {
        const resp = (error as { response?: { status?: number, data?: { errors?: Record<string, string[]> } } }).response;
        if (resp?.status === 422) {
            setErrors(resp.data?.errors || {});
        } else {
            toast.error.fromResponse(error);
        }
    } finally {
        saving.value = false;
    }
};

onMounted(() => {
    loadTask();
});
</script>

