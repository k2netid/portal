<template>
  <Dialog
    :open="true"
    @update:open="$emit('close')"
  >
    <DialogContent class="console-dialog-xl max-h-[90vh] flex flex-col p-0">
      <DialogHeader class="p-6 pb-0">
        <DialogTitle>
          {{ field ? t('library.customFields.fields.modal.title_edit') : t('library.customFields.fields.modal.title_create') }}
        </DialogTitle>
      </DialogHeader>

      <form
        class="flex-1 overflow-y-auto p-6 pt-2 space-y-4"
        @submit.prevent="handleSubmit"
      >
        <div class="space-y-2">
          <Label for="label">
            {{ t('library.customFields.fields.modal.label_label') }} <span class="text-destructive">*</span>
          </Label>
          <Input
            id="label"
            v-model="form.label"
            required
            :placeholder="t('library.customFields.fields.modal.label_placeholder')"
            @input="generateName"
          />
        </div>

        <div class="space-y-2">
          <Label for="name">
            {{ t('library.customFields.fields.modal.name_label') }} <span class="text-destructive">*</span>
          </Label>
          <Input
            id="name"
            v-model="form.name"
            required
            class="font-mono text-xs"
            :placeholder="t('library.customFields.fields.modal.name_placeholder')"
          />
          <p class="text-[10px] text-muted-foreground">
            {{ t('library.customFields.fields.modal.name_help') }}
          </p>
        </div>

        <div class="space-y-2">
          <Label for="type">
            {{ t('library.customFields.fields.modal.type_label') }} <span class="text-destructive">*</span>
          </Label>
          <Select
            v-model="form.type"
            required
          >
            <SelectTrigger id="type">
              <SelectValue />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="text">
                Text
              </SelectItem>
              <SelectItem value="textarea">
                Textarea
              </SelectItem>
              <SelectItem value="number">
                Number
              </SelectItem>
              <SelectItem value="email">
                Email
              </SelectItem>
              <SelectItem value="url">
                URL
              </SelectItem>
              <SelectItem value="date">
                Date
              </SelectItem>
              <SelectItem value="datetime">
                DateTime
              </SelectItem>
              <SelectItem value="boolean">
                Boolean
              </SelectItem>
              <SelectItem value="select">
                Select
              </SelectItem>
              <SelectItem value="multiselect">
                Multi Select
              </SelectItem>
              <SelectItem value="radio">
                Radio
              </SelectItem>
              <SelectItem value="checkbox">
                Checkbox
              </SelectItem>
              <SelectItem value="file">
                File
              </SelectItem>
              <SelectItem value="image">
                Image
              </SelectItem>
              <SelectItem value="json">
                JSON
              </SelectItem>
            </SelectContent>
          </Select>
        </div>

        <div class="space-y-2">
          <Label for="field_group_id">
            {{ t('library.customFields.fields.modal.group_label') }}
          </Label>
          <Select 
            :model-value="form.field_group_id ? String(form.field_group_id) : 'none'"
            @update:model-value="(val) => form.field_group_id = val === 'none' ? null : val"
          >
            <SelectTrigger id="field_group_id">
              <SelectValue :placeholder="t('library.customFields.fields.modal.group_none')" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="none">
                {{ t('library.customFields.fields.modal.group_none') }}
              </SelectItem>
              <SelectItem
                v-for="group in fieldGroups"
                :key="group.id"
                :value="String(group.id)"
              >
                {{ group.name }}
              </SelectItem>
            </SelectContent>
          </Select>
        </div>

        <div class="space-y-2">
          <Label for="default_value">
            {{ t('library.customFields.fields.modal.default_label') }}
          </Label>
          <Input
            id="default_value"
            v-model="form.default_value"
            :placeholder="t('library.customFields.fields.modal.default_placeholder')"
          />
        </div>

        <div class="space-y-2">
          <Label for="options">
            {{ t('library.customFields.fields.modal.options_label') }}
          </Label>
          <Textarea
            id="options"
            v-model="form.options"
            rows="3"
            class="font-mono text-xs"
            :placeholder="t('library.customFields.fields.modal.options_placeholder')"
          />
          <p class="text-[10px] text-muted-foreground">
            {{ t('library.customFields.fields.modal.options_help') }}
          </p>
        </div>

        <div class="space-y-2">
          <Label for="instructions">
            {{ t('library.customFields.fields.modal.instructions_label') }}
          </Label>
          <Textarea
            id="instructions"
            v-model="form.instructions"
            rows="2"
            :placeholder="t('library.customFields.fields.modal.instructions_placeholder')"
          />
        </div>

        <div class="grid grid-cols-2 gap-4 pt-2">
          <div class="flex items-center space-x-2">
            <Checkbox
              id="is_required"
              :checked="form.is_required"
              @update:checked="v => form.is_required = v"
            />
            <Label
              for="is_required"
              class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
            >
              {{ t('library.customFields.fields.modal.required_label') }}
            </Label>
          </div>
          <div class="flex items-center space-x-2">
            <Checkbox
              id="is_searchable"
              :checked="form.is_searchable"
              @update:checked="v => form.is_searchable = v"
            />
            <Label
              for="is_searchable"
              class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
            >
              {{ t('library.customFields.fields.modal.searchable_label') }}
            </Label>
          </div>
        </div>
      </form>

      <DialogFooter class="p-6 pt-0">
        <Button
          variant="outline"
          type="button"
          size="sm"
          class="h-10"
          @click="$emit('close')"
        >
          {{ t('library.customFields.fields.modal.cancel') }}
        </Button>
        <Button
          type="submit"
          size="sm"
          class="h-10 inline-flex items-center gap-2"
          :disabled="saving || !isValid || (field && !isDirty)"
          @click="handleSubmit"
        >
          <Loader2
            v-if="saving"
            data-icon="inline-start"
            class="size-4 shrink-0 animate-spin"
          />
          {{ saving ? t('library.customFields.fields.modal.saving') : (field ? t('library.customFields.fields.modal.update') : t('library.customFields.fields.modal.create')) }}
        </Button>
      </DialogFooter>
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
    SelectItem,
    Checkbox
} from '@/shared/components/ui';
import {
  Loader2,
} from 'lucide-vue-next';

interface FieldGroup {
    id: string | number;
    name: string;
}

interface Field {
    id: string | number;
    label: string;
    name: string;
    type: string;
    field_group_id: string | number | null;
    default_value: unknown;
    options: string | string[] | (string | number | Record<string, unknown>)[];
    instructions: string | null;
    is_required: boolean | number;
    is_searchable: boolean | number;
}

interface FieldForm {
    label: string;
    name: string;
    type: string;
    field_group_id: string | number | null;
    default_value: string;
    options: string;
    instructions: string;
    is_required: boolean;
    is_searchable: boolean;
}

const { t } = useI18n();
const toast = useToast();

const props = withDefaults(defineProps<{
    field?: Field | null;
    fieldGroups?: FieldGroup[];
}>(), {
    field: null,
    fieldGroups: () => []
});

const emit = defineEmits<{
    'close': [];
    'saved': [];
}>();

const saving = ref(false);

const form = ref<FieldForm>({
    label: '',
    name: '',
    type: 'text',
    field_group_id: null,
    default_value: '',
    options: '',
    instructions: '',
    is_required: false,
    is_searchable: false,
});

const generateName = () => {
    if (!form.value.name || form.value.name === slugify(form.value.label)) {
        form.value.name = slugify(form.value.label);
    }
};

const slugify = (text: string) => {
    return text
        .toString()
        .toLowerCase()
        .trim()
        .replace(/\s+/g, '_')
        .replace(/[^\w-\s]+/g, '')
        .replace(/-+/, '_')
        .replace(/^_+/, '')
        .replace(/_+$/, '');
};

const initialForm = ref<FieldForm | null>(null);

const isValid = computed(() => {
    return !!form.value.label?.trim() && !!form.value.name?.trim() && !!form.value.type;
});

const isDirty = computed(() => {
    if (!initialForm.value) return true;
    return JSON.stringify(form.value) !== JSON.stringify(initialForm.value);
});

const loadField = () => {
    if (props.field) {
        form.value = {
            label: props.field.label || '',
            name: props.field.name || '',
            type: props.field.type || 'text',
            field_group_id: props.field.field_group_id || null,
            default_value: String(props.field.default_value ?? ''),
            options: Array.isArray(props.field.options) ? props.field.options.join(',') : (String(props.field.options || '')),
            instructions: props.field.instructions || '',
            is_required: !!props.field.is_required,
            is_searchable: !!props.field.is_searchable,
        };
    } else {
        form.value = {
            label: '',
            name: '',
            type: 'text',
            field_group_id: null,
            default_value: '',
            options: '',
            instructions: '',
            is_required: false,
            is_searchable: false,
        };
    }
    initialForm.value = JSON.parse(JSON.stringify(form.value));
};

const handleSubmit = async () => {
    saving.value = true;
    try {
        const payload = {
            ...form.value,
            options: form.value.options ? form.value.options.split(',').map(o => o.trim()) : [],
        };
        
        if (props.field) {
            await api.put(`/manage/library/custom-fields/${props.field.id}`, payload);
            toast.success.update(t('library.customFields.fields.title'));
        } else {
            await api.post('/manage/library/custom-fields', payload);
            toast.success.create(t('library.customFields.fields.title'));
        }
        emit('saved');
    } catch (error: unknown) {
        logger.error('Failed to save field:', error);
        toast.error.fromResponse(error);
    } finally {
        saving.value = false;
    }
};

onMounted(() => {
    loadField();
});
</script>

