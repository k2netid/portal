<template>
  <Dialog
    :open="true"
    @update:open="$emit('close')"
  >
    <DialogContent class="console-dialog-lg">
      <DialogHeader>
        <DialogTitle>
          {{ redirect ? $t('layout.redirects.modals.redirect.titleEdit') : $t('layout.redirects.modals.redirect.titleCreate') }}
        </DialogTitle>
      </DialogHeader>

      <form
        class="space-y-4 py-4"
        @submit.prevent="handleSubmit"
      >
        <div class="space-y-2">
          <Label>
            {{ $t('layout.redirects.modals.redirect.from') }} <span class="text-destructive">*</span>
          </Label>
          <Input
            v-model="form.from_url"
            required
            :placeholder="$t('layout.redirects.modals.redirect.fromPlaceholder')"
          />
          <span
            v-if="errors.from_url"
            class="text-xs text-destructive"
          >{{ errors.from_url[0] }}</span>
          <p class="text-xs text-muted-foreground">
            {{ $t('layout.redirects.modals.redirect.fromHint') }}
          </p>
        </div>

        <div class="space-y-2">
          <Label>
            {{ $t('layout.redirects.modals.redirect.to') }} <span class="text-destructive">*</span>
          </Label>
          <Input
            v-model="form.to_url"
            required
            :placeholder="$t('layout.redirects.modals.redirect.toPlaceholder')"
          />
          <span
            v-if="errors.to_url"
            class="text-xs text-destructive"
          >{{ errors.to_url[0] }}</span>
          <p class="text-xs text-muted-foreground">
            {{ $t('layout.redirects.modals.redirect.toHint') }}
          </p>
        </div>

        <div class="space-y-2">
          <Label>
            {{ $t('layout.redirects.modals.redirect.code') }} <span class="text-destructive">*</span>
          </Label>
          <Select v-model="form.status_code">
            <SelectTrigger>
              <SelectValue />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="301">
                {{ $t('layout.redirects.modals.redirect.codes.p301') }}
              </SelectItem>
              <SelectItem value="302">
                {{ $t('layout.redirects.modals.redirect.codes.t302') }}
              </SelectItem>
              <SelectItem value="307">
                {{ $t('layout.redirects.modals.redirect.codes.t307') }}
              </SelectItem>
              <SelectItem value="308">
                {{ $t('layout.redirects.modals.redirect.codes.p308') }}
              </SelectItem>
            </SelectContent>
          </Select>
        </div>

        <div class="flex items-center space-x-2 pt-2">
          <Checkbox
            id="is_active"
            v-model:checked="form.is_active"
          />
          <Label
            for="is_active"
            class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
          >
            {{ $t('layout.redirects.modals.redirect.active') }}
          </Label>
        </div>
      </form>

      <DialogFooter>
        <Button
          variant="outline"
          size="sm" class="h-10" @click="$emit('close')"
        >
          {{ $t('common.actions.cancel') }}
        </Button>
        <Button
          :disabled="isSubmitting || !isValid || (redirect && !isDirty)"
          @click="handleSubmit"
        >
          <Loader2
            v-if="isSubmitting"
            class="w-4 h-4 mr-2 animate-spin"
          />
          {{ isSubmitting ? $t('layout.redirects.modals.redirect.saving') : (redirect ? $t('common.actions.update') : $t('common.actions.create')) }}
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '@/engine/api/client';
import { layoutPaths } from '@/engine/api/paths';
import { toApiRedirectPayload, type RedirectRow } from '@/modules/Layout/utils/redirect';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogFooter,
    Button,
    Input,
    Label,
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
import { redirectSchema } from '@/shared/schemas/common';

const props = defineProps<{
    redirect?: RedirectRow | null;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'saved'): void;
}>();

const { t } = useI18n();
const toast = useToast();
const isSubmitting = ref(false);

const { errors, validateWithZod, setErrors } = useFormValidation(redirectSchema);

interface RedirectForm {
    from_url: string;
    to_url: string;
    status_code: string;
    is_active: boolean;
}

const form = ref<RedirectForm>({
    from_url: '',
    to_url: '',
    status_code: '301',
    is_active: true,
});

const initialForm = ref<RedirectForm | null>(null);

const isDirty = computed(() => {
    if (!props.redirect || !initialForm.value) return true; // Always true for create or if init not set
    return JSON.stringify(form.value) !== JSON.stringify(initialForm.value);
});

const isValid = computed(() => {
    return !!form.value.from_url?.trim() && !!form.value.to_url?.trim() && !!form.value.status_code;
});

const loadRedirect = () => {
    if (props.redirect) {
        form.value = {
            from_url: props.redirect.from_url || '',
            to_url: props.redirect.to_url || '',
            status_code: String(props.redirect.status_code) || '301',
            is_active: props.redirect.is_active !== undefined ? props.redirect.is_active : true,
        };
        initialForm.value = JSON.parse(JSON.stringify(form.value));
    }
};

const handleSubmit = async () => {
    if (!validateWithZod(form.value)) return;

    isSubmitting.value = true;
    try {
        const payload = toApiRedirectPayload({
            from_url: form.value.from_url,
            to_url: form.value.to_url,
            status_code: Number(form.value.status_code),
            is_active: form.value.is_active,
        });
        if (props.redirect) {
            await api.put(layoutPaths.redirect(String(props.redirect.id)), payload);
            toast.success.update(t('layout.redirects.title'));
        } else {
            await api.post(layoutPaths.redirects, payload);
            toast.success.create(t('layout.redirects.title'));
        }
        emit('saved');
    } catch (error: unknown) {
        const err = error as { response?: { status?: number; data?: { errors?: Record<string, string[]> } } };
        if (err.response?.status === 422) {
            setErrors(((err.response?.data?.errors) || {}) as Record<string, string[]>);
        } else {
            toast.error.fromResponse(error);
        }
    } finally {
        isSubmitting.value = false;
    }
};

onMounted(() => {
    loadRedirect();
});
</script>

