<template>
  <Dialog
    :open="true"
    @update:open="$emit('close')"
  >
    <DialogContent class="console-dialog-lg">
      <DialogHeader>
        <DialogTitle>
          {{ widget ? $t('layout.widgets.modals.widget.titleEdit') : $t('layout.widgets.modals.widget.titleCreate') }}
        </DialogTitle>
      </DialogHeader>

      <form
        class="space-y-4 py-4 max-h-[70vh] overflow-y-auto pr-2"
        @submit.prevent="handleSubmit"
      >
        <div class="space-y-2">
          <Label>{{ $t('layout.widgets.modals.widget.title') }} <span class="text-red-500">*</span></Label>
          <Input
            v-model="form.title"
            type="text"
            required
          />
          <span
            v-if="errors.title"
            class="text-xs text-destructive"
          >{{ errors.title }}</span>
        </div>
        <div class="space-y-2">
          <Label>{{ $t('layout.widgets.modals.widget.type') }} <span class="text-red-500">*</span></Label>
          <Select v-model="form.type">
            <SelectTrigger>
              <SelectValue :placeholder="$t('layout.widgets.modals.widget.type')" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="text">
                {{ $t('layout.widgets.types.text') }}
              </SelectItem>
              <SelectItem value="html">
                {{ $t('layout.widgets.types.html') }}
              </SelectItem>
              <SelectItem value="recent_posts">
                {{ $t('layout.widgets.types.recent_posts') }}
              </SelectItem>
              <SelectItem value="categories">
                {{ $t('layout.widgets.types.categories') }}
              </SelectItem>
              <SelectItem value="custom">
                {{ $t('layout.widgets.types.custom') }}
              </SelectItem>
            </SelectContent>
          </Select>
          <span
            v-if="errors.type"
            class="text-xs text-destructive"
          >{{ errors.type }}</span>
        </div>
        <div class="space-y-2">
          <Label>{{ $t('layout.widgets.modals.widget.location') }}</Label>
          <Input
            v-model="form.location"
            type="text"
            :placeholder="$t('layout.widgets.modals.widget.positionPlaceholder')"
          />
          <span
            v-if="errors.location"
            class="text-xs text-destructive"
          >{{ errors.location }}</span>
        </div>
        <div class="space-y-2">
          <Label>{{ $t('layout.widgets.modals.widget.content') }}</Label>
          <Textarea
            v-model="form.content"
            :rows="6"
          />
          <span
            v-if="errors.content"
            class="text-xs text-destructive"
          >{{ errors.content }}</span>
        </div>
        <div class="flex items-center space-x-2 pt-2">
          <Checkbox
            id="is_active"
            v-model:checked="form.is_active"
          />
          <Label
            for="is_active"
            class="text-sm font-normal cursor-pointer"
          >
            {{ $t('layout.widgets.modals.widget.active') }}
          </Label>
        </div>
      </form>

      <DialogFooter>
        <Button
          variant="outline"
          size="sm"
          @click="$emit('close')"
        >
          {{ $t('common.actions.cancel') }}
        </Button>
        <Button
          :disabled="isSubmitting || !isValid || (widget && !isDirty)"
          @click="handleSubmit"
        >
          <Loader2
            v-if="isSubmitting"
            data-icon="inline-start" class="size-4 shrink-0 animate-spin"
          />
          {{ isSubmitting ? $t('layout.widgets.modals.widget.saving') : (widget ? $t('common.actions.update') : $t('common.actions.create')) }}
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { onMounted, ref, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '@/engine/api/client';
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
    Checkbox, 
    Select, 
    SelectTrigger, 
    SelectValue, 
    SelectContent, 
    SelectItem 
} from '@/shared/components/ui';
import {
  Loader2,
} from 'lucide-vue-next';
import { useToast } from '@/shared/composables/useToast';
import { useFormValidation } from '@/shared/composables/useFormValidation';
import { widgetSchema } from '@/shared/schemas/common';

type WidgetType = 'html' | 'text' | 'recent_posts' | 'categories' | 'custom';

interface Widget {
    id: string;
    title: string;
    type: string;
    location?: string;
    content?: string;
    is_active?: boolean | number;
}

interface WidgetForm {
    title: string;
    type: WidgetType;
    location: string;
    content: string;
    is_active: boolean;
}

const props = withDefaults(defineProps<{
    widget?: Widget | null;
}>(), {
    widget: null
});

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'saved'): void;
}>();

const { t } = useI18n();
const toast = useToast();
const isSubmitting = ref(false);

const { errors, validateWithZod, setErrors } = useFormValidation(widgetSchema);

const form = ref<WidgetForm>({
    title: '',
    type: 'text',
    location: '',
    content: '',
    is_active: true
});

const initialForm = ref<WidgetForm | null>(null);

const isDirty = computed(() => {
    if (!props.widget || !initialForm.value) return true;
    return JSON.stringify(form.value) !== JSON.stringify(initialForm.value);
});

const isValid = computed(() => {
    return !!form.value.title?.trim() && !!form.value.type;
});

const loadWidget = () => {
    if (props.widget) {
        form.value = { 
            title: props.widget.title || '',
            type: (props.widget.type as WidgetType) || 'text',
            location: props.widget.location || '',
            content: props.widget.content || '',
            is_active: !!props.widget.is_active 
        };
        initialForm.value = JSON.parse(JSON.stringify(form.value));
    }
};

const handleSubmit = async () => {
    if (!validateWithZod(form.value)) return;
    isSubmitting.value = true;

    try {
        if (props.widget) {
            await api.put(`/manage/layout/widgets/${props.widget.id}`, form.value);
            toast.success.update(t('layout.widgets.title'));
        } else {
            await api.post('/manage/layout/widgets', form.value);
            toast.success.create(t('layout.widgets.title'));
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
        isSubmitting.value = false;
    }
};

onMounted(() => {
    loadWidget();
});
</script>

