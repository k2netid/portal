<template>
  <Dialog
    :open="true"
    @update:open="$emit('close')"
  >
    <DialogContent class="console-dialog-xl">
      <DialogHeader>
        <DialogTitle>
          {{ fieldGroup ? t('library.customFields.groups.modal.title_edit') : t('library.customFields.groups.modal.title_create') }}
        </DialogTitle>
      </DialogHeader>

      <form
        class="space-y-4"
        @submit.prevent="handleSubmit"
      >
        <div class="space-y-2">
          <Label for="name">
            {{ t('library.customFields.groups.modal.name_label') }} <span class="text-destructive">*</span>
          </Label>
          <Input
            id="name"
            v-model="form.name"
            required
            :placeholder="t('library.customFields.groups.modal.name_placeholder')"
          />
        </div>

        <div class="space-y-2">
          <Label for="description">
            {{ t('library.customFields.groups.modal.description_label') }}
          </Label>
          <Textarea
            id="description"
            v-model="form.description"
            rows="3"
            :placeholder="t('library.customFields.groups.modal.description_placeholder')"
          />
        </div>

        <div class="space-y-2">
          <Label for="attachable_type">
            {{ t('library.customFields.groups.modal.attach_label') }}
          </Label>
          <Select v-model="form.attachable_type">
            <SelectTrigger id="attachable_type">
              <SelectValue :placeholder="t('library.customFields.groups.modal.attach_options.none')" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="none">
                {{ t('library.customFields.groups.modal.attach_options.none') }}
              </SelectItem>
              <SelectItem value="App\\Models\\Content">
                {{ t('library.customFields.groups.modal.attach_options.content') }}
              </SelectItem>
              <SelectItem value="App\\Models\\Category">
                {{ t('library.customFields.groups.modal.attach_options.category') }}
              </SelectItem>
              <SelectItem value="App\\Models\\Media">
                {{ t('library.customFields.groups.modal.attach_options.media') }}
              </SelectItem>
            </SelectContent>
          </Select>
        </div>

        <DialogFooter>
          <Button
            variant="outline"
            type="button"
            size="sm"
            class="h-10"
            @click="$emit('close')"
          >
            {{ t('library.customFields.groups.modal.cancel') }}
          </Button>
          <Button
            type="submit"
            size="sm"
            class="h-10 inline-flex items-center gap-2"
            :disabled="saving || !isValid || (fieldGroup && !isDirty)"
          >
            <Loader2
              v-if="saving"
              data-icon="inline-start"
              class="size-4 shrink-0 animate-spin"
            />
            {{ saving ? t('library.customFields.groups.modal.saving') : (fieldGroup ? t('library.customFields.groups.modal.update') : t('library.customFields.groups.modal.create')) }}
          </Button>
        </DialogFooter>
      </form>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { logger } from '@/shared/utils/logger';
import { ref, onMounted, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '@/engine/api/client';
import { useToast } from '@/shared/composables/useToast';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogFooter,
    Button,
    Input,
    Label,
    Textarea,
    Select,
    SelectTrigger,
    SelectValue,
    SelectContent,
    SelectItem
} from '@/shared/components/ui';
import {
  Loader2,
} from 'lucide-vue-next';

interface FieldGroup {
    id: string | string;
    name: string;
    description: string | null;
    attachable_type: string | null;
}

const { t } = useI18n();

const props = defineProps<{
    fieldGroup?: FieldGroup | null;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'saved'): void;
}>();

const saving = ref(false);

interface FieldGroupForm {
    name: string;
    description: string;
    attachable_type: string | 'none';
}

const form = ref<FieldGroupForm>({
    name: '',
    description: '',
    attachable_type: 'none',
});

const initialForm = ref<FieldGroupForm | null>(null);

const isValid = computed(() => {
    return !!form.value.name?.trim();
});

const isDirty = computed(() => {
    if (!initialForm.value) return true;
    return JSON.stringify(form.value) !== JSON.stringify(initialForm.value);
});

const loadFieldGroup = () => {
    if (props.fieldGroup) {
        form.value = {
            name: props.fieldGroup.name || '',
            description: props.fieldGroup.description || '',
            attachable_type: props.fieldGroup.attachable_type || 'none',
        };
    } else {
        form.value = {
            name: '',
            description: '',
            attachable_type: 'none',
        };
    }
    initialForm.value = JSON.parse(JSON.stringify(form.value));
};

const toast = useToast();

const handleSubmit = async () => {
    saving.value = true;
    try {
        const payload = {
            ...form.value,
            attachable_type: form.value.attachable_type === 'none' ? null : form.value.attachable_type
        };
        if (props.fieldGroup) {
            await api.put(`/manage/library/field-groups/${props.fieldGroup.id}`, payload);
            toast.success.update(t('library.customFields.tabs.groups'));
        } else {
            await api.post('/manage/library/field-groups', form.value);
            toast.success.create(t('library.customFields.tabs.groups'));
        }
        emit('saved');
    } catch (error: unknown) {
        logger.error('Failed to save field group:', error);
        toast.error.fromResponse(error);
    } finally {
        saving.value = false;
    }
};

onMounted(() => {
    loadFieldGroup();
});
</script>

