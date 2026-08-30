<template>
  <Dialog
    :open="true"
    @update:open="$emit('close')"
  >
    <DialogContent class="console-dialog-sm">
      <DialogHeader>
        <DialogTitle>{{ t('layout.menus.form.createTitle') }}</DialogTitle>
      </DialogHeader>

      <form
        class="space-y-4 py-4"
        @submit.prevent="handleSubmit"
      >
        <div class="space-y-2">
          <Label>
            {{ t('layout.menus.form.name') }} <span class="text-red-500">*</span>
          </Label>
          <Input
            v-model="form.name"
            type="text"
            required
            :placeholder="t('layout.menus.form.placeholders.name')"
          />
        </div>
        <div class="space-y-2">
          <Label>
            {{ t('layout.menus.form.location') }}
          </Label>
          <Select v-model="form.location">
            <SelectTrigger>
              <SelectValue :placeholder="t('layout.menus.form.placeholders.location')" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem 
                v-for="loc in locationOptions" 
                :key="loc.value" 
                :value="loc.value"
              >
                {{ loc.label }}
              </SelectItem>
            </SelectContent>
          </Select>
        </div>
      </form>

      <DialogFooter>
        <Button
          variant="outline"
          size="sm"
          @click="$emit('close')"
        >
          {{ t('layout.menus.actions.cancel') }}
        </Button>
        <Button
          size="sm"
          class="inline-flex items-center gap-1.5"
          :disabled="saving || !isValid"
          @click="handleSubmit"
        >
          <Loader2
            v-if="saving"
            data-icon="inline-start" class="size-4 shrink-0 animate-spin"
          />
          {{ saving ? t('layout.menus.actions.creating') : t('layout.menus.actions.createAction') }}
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { logger } from '@/shared/utils/logger';
import { ref, computed, onMounted } from 'vue';

import { useI18n } from 'vue-i18n';
import { LayoutService } from '@/modules/Layout/services/layoutService';
import Dialog from '@/shared/components/ui/Dialog.vue';
import DialogContent from '@/shared/components/ui/DialogContent.vue';
import DialogHeader from '@/shared/components/ui/DialogHeader.vue';
import DialogTitle from '@/shared/components/ui/DialogTitle.vue';
import DialogFooter from '@/shared/components/ui/DialogFooter.vue';
import Button from '@/shared/components/ui/Button.vue';
import Input from '@/shared/components/ui/Input.vue';
import Label from '@/shared/components/ui/Label.vue';
import Select from '@/shared/components/ui/Select.vue';
import SelectTrigger from '@/shared/components/ui/SelectTrigger.vue';
import SelectValue from '@/shared/components/ui/SelectValue.vue';
import SelectContent from '@/shared/components/ui/SelectContent.vue';
import SelectItem from '@/shared/components/ui/SelectItem.vue';
import {
  Loader2,
} from 'lucide-vue-next';
import { useToast } from '@/shared/composables/useToast';
import { useFormValidation } from '@/shared/composables/useFormValidation';
import { menuSchema } from '@/shared/schemas';

const { t } = useI18n();
const toast = useToast();
const { validateWithZod, setErrors, clearErrors } = useFormValidation(menuSchema);

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'saved', menu: { id?: string }): void;
}>();

const saving = ref(false);
const form = ref({
    name: '',
    location: '',
});

interface LocationOption {
    value: string;
    label: string;
}

const locationOptions = ref<LocationOption[]>([]);
const loadingLocations = ref(false);

const fetchLocations = async () => {
    loadingLocations.value = true;
    try {
        const response = await LayoutService.themeLocations();
        const data = response.data || {};
        
        // Transform { key: label } to [{ value: key, label: label }]
        locationOptions.value = Object.entries(data).map(([key, label]) => ({
            value: key,
            label: label as string
        }));
    } catch (error) {
        logger.error('Failed to fetch menu locations:', error);
        toast.error.load(error);
    } finally {
        loadingLocations.value = false;
    }
};

onMounted(() => {
    fetchLocations();
});

const isValid = computed(() => {
    return !!form.value.name?.trim();
});

const handleSubmit = async () => {
    if (!validateWithZod(form.value)) return;

    saving.value = true;
    clearErrors();
    try {
        const response = await LayoutService.createMenu(form.value);
        const menu = response.data;
        toast.success.create(t('layout.menus.title'));
        emit('saved', menu);
        emit('close');
    } catch (error: unknown) {
        if (error && typeof error === 'object' && 'response' in error) {
            const err = error as { response?: { status?: number, data?: { errors?: Record<string, string[]>, message?: string } } };
            if (err.response?.status === 422) {
                setErrors((err.response?.data?.errors as Record<string, string[]>) || {});
            } else {
                toast.error.fromResponse(error);
            }
        } else {
            toast.error.action(t('layout.menus.messages.saveFailed'));
        }
    } finally {
        saving.value = false;
    }
};
</script>
