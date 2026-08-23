<template>
  <Card class="comment-form">
    <CardHeader>
      <CardTitle>{{ $t('publishing.comments.leaveComment') }}</CardTitle>
    </CardHeader>
    <CardContent>
      <form
        class="space-y-4"
        @submit.prevent="handleSubmit"
      >
        <div
          v-if="!authStore.isAuthenticated"
          class="grid grid-cols-1 md:grid-cols-2 gap-4"
        >
          <div class="space-y-2">
            <Label for="name">{{ $t('publishing.comments.name') }}</Label>
            <Input
              id="name"
              v-model="form.name"
              type="text"
              required
            />
          </div>
          <div class="space-y-2">
            <Label for="email">{{ $t('publishing.comments.email') }}</Label>
            <Input
              id="email"
              v-model="form.email"
              type="email"
              required
            />
          </div>
        </div>

        <div
          v-if="!authStore.isAuthenticated"
          class="space-y-2"
        >
          <Label>{{ $t('system.settings.labels.enable_captcha') }}</Label>
          <CaptchaWrapper 
            action="comment" 
            @verified="handleCaptchaVerified" 
          />
        </div>

        <div class="space-y-2">
          <Label for="body">{{ $t('publishing.comments.comment') }}</Label>
          <Textarea
            id="body"
            v-model="form.body"
            rows="4"
            required
          />
        </div>

        <Button
          type="submit"
          size="sm"
          :disabled="loading"
          class="w-full sm:w-auto inline-flex items-center gap-1.5"
        >
          <Loader2
            v-if="loading"
            data-icon="inline-start" class="size-4 shrink-0 animate-spin"
          />
          {{ loading ? $t('publishing.comments.submitting') : $t('publishing.comments.submit') }}
        </Button>
      </form>
    </CardContent>
  </Card>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '@/engine/api/client';
import { publishingPaths } from '@/engine/api/paths';
import { useAuthStore } from '@/modules/Core/System/stores/auth';
import {
  Loader2,
} from 'lucide-vue-next';
import CaptchaWrapper from '@/modules/Core/System/components/captcha/CaptchaWrapper.vue';
import {
    Card,
    CardHeader,
    CardTitle,
    CardContent,
    Input,
    Label,
    Textarea,
    Button
} from '@/shared/components/ui';
import { useToast } from '@/shared/composables/useToast';
import { useFormValidation } from '@/shared/composables/useFormValidation';
import { commentSchema } from '@/shared/schemas';

interface CommentForm {
    name: string;
    email: string;
    body: string;
    parent_id: string | null;
    captcha_token: string;
    captcha_input: string;
}

const props = defineProps<{
    contentId: string;
    parentId?: string | null;
}>();

const emit = defineEmits<{
    (e: 'submitted'): void;
}>();

useI18n();
const authStore = useAuthStore();
const toast = useToast();
const { validateWithZod, setErrors, clearErrors } = useFormValidation(commentSchema);

const form = ref<CommentForm>({
    name: '',
    email: '',
    body: '',
    parent_id: props.parentId ?? null,
    captcha_token: '',
    captcha_input: '',
});

const handleCaptchaVerified = (payload: { token: string; answer?: string; position?: string; code?: string }) => {
    form.value.captcha_token = payload.token;
    form.value.captcha_input = payload.answer || payload.position || payload.code || '';
};

const loading = ref(false);

const handleSubmit = async () => {
    if (!validateWithZod(form.value)) return;

    loading.value = true;
    clearErrors();
    try {
        await api.post(publishingPaths.publicContentComments(props.contentId), form.value);
        
        // Reset form
        form.value = {
            name: '',
            email: '',
            body: '',
            parent_id: props.parentId ?? null,
            captcha_token: '',
            captcha_input: '',
        };

        emit('submitted');
        toast.success.create('Comment');
    } catch (error: unknown) {
        const err = error as import('axios').AxiosError<{ errors?: Record<string, string[]> }>;
        if (err.response?.status === 422) {
            setErrors(err.response.data?.errors || {});
        } else {
            toast.error.fromResponse(err);
        }
    } finally {
        loading.value = false;
    }
};
</script>


