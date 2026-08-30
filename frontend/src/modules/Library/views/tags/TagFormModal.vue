<template>
  <Dialog
    :open="open"
    @update:open="$emit('update:open', $event)"
  >
    <DialogContent class="console-dialog-sm">
      <DialogHeader>
        <DialogTitle>{{ isEdit ? $t('library.tags.form.editTitle') : $t('library.tags.form.createTitle') }}</DialogTitle>
        <DialogDescription>
          {{ isEdit ? $t('library.tags.form.editDescription') : $t('library.tags.form.createDescription') }}
        </DialogDescription>
      </DialogHeader>

      <form
        class="space-y-4 py-4"
        @submit.prevent="handleSubmit"
      >
        <!-- Name -->
        <div class="space-y-2">
          <Label>
            {{ $t('library.tags.form.name') }} <span class="text-destructive">*</span>
          </Label>
          <Input
            v-model="form.name"
            required
            :class="errors.name ? 'border-destructive focus-visible:ring-destructive' : ''"
            :placeholder="$t('library.tags.form.namePlaceholder')"
            @input="generateSlug"
          />
          <p
            v-if="errors.name"
            class="text-sm text-destructive"
          >
            {{ Array.isArray(errors.name) ? errors.name[0] : errors.name }}
          </p>
        </div>

        <!-- Slug -->
        <div class="space-y-2">
          <Label>
            {{ $t('library.tags.form.slug') }} <span class="text-destructive">*</span>
          </Label>
          <Input
            v-model="form.slug"
            required
            :class="errors.slug ? 'border-destructive focus-visible:ring-destructive' : ''"
            :placeholder="$t('library.tags.form.slugPlaceholder')"
          />
          <p class="text-xs text-muted-foreground">
            {{ $t('library.tags.form.slugHelp') }}
          </p>
          <p
            v-if="errors.slug"
            class="text-sm text-destructive"
          >
            {{ Array.isArray(errors.slug) ? errors.slug[0] : errors.slug }}
          </p>
        </div>

        <!-- Description -->
        <div class="space-y-2">
          <Label>
            {{ $t('library.tags.form.description') }}
          </Label>
          <Textarea
            v-model="form.description"
            rows="3"
            :placeholder="$t('library.tags.form.descriptionPlaceholder')"
          />
        </div>

        <DialogFooter>
          <Button
            type="button"
            variant="outline"
            size="sm"
            class="h-10"
            @click="$emit('update:open', false)"
          >
            {{ $t('common.actions.cancel') }}
          </Button>
          <Button
            type="submit"
            size="sm"
            class="h-10 inline-flex items-center gap-2"
            :disabled="saving || !isValid"
          >
            <Loader2
              v-if="saving"
              data-icon="inline-start"
              class="size-4 shrink-0 animate-spin"
            />
            {{ saving ? $t('library.tags.form.saving') : (isEdit ? $t('library.tags.form.update') : $t('library.tags.form.create')) }}
          </Button>
        </DialogFooter>
      </form>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { LibraryService } from '@/modules/Library/services/libraryService';
import { useToast } from '@/shared/composables/useToast';
import { useFormValidation } from '@/shared/composables/useFormValidation';
import { tagSchema } from '@/shared/schemas';
import {
  Loader2,
} from 'lucide-vue-next';
import type { Tag } from '@/modules/Library/types/taxonomy';

// Shadcn UI
import {
    Button,
    Input,
    Label,
    Textarea,
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle
} from '@/shared/components/ui';

interface TagForm {
    name: string;
    slug: string;
    description: string;
    type?: string | null;
}

const props = defineProps<{
    open: boolean;
    tag?: Tag | null;
    scope?: string;
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
    'success': [];
}>();

const toast = useToast();
const { errors, validateWithZod, setErrors, clearErrors } = useFormValidation(tagSchema);

const saving = ref(false);
const isEdit = computed(() => !!props.tag);

const form = ref<TagForm>({
    name: '',
    slug: '',
    description: '',
    type: null,
});

// Initialize form when opening
watch(() => props.open, (isOpen) => {
    if (isOpen) {
        clearErrors();
        if (props.tag) {
            form.value = {
                name: props.tag.name || '',
                slug: props.tag.slug || '',
                description: props.tag.description || '',
                type: props.tag.type || props.scope || null,
            };
        } else {
            // Reset
            form.value = {
                name: '',
                slug: '',
                description: '',
                type: props.scope || null,
            };
        }
    }
});

const isValid = computed(() => {
    return !!form.value.name?.trim();
});

const generateSlug = () => {
    if (!isEdit.value || !form.value.slug || form.value.slug === slugify(form.value.name)) {
         form.value.slug = slugify(form.value.name);
    }
};

const slugify = (text: string) => {
    return text
        .toString()
        .toLowerCase()
        .trim()
        .replace(/\s+/g, '-')
        .replace(/[^\w-\s]+/g, '')
        .replace(/-+/, '-')
        .replace(/^-+/, '')
        .replace(/-+$/, '');
};

const handleSubmit = async () => {
    if (!validateWithZod(form.value)) return;

    saving.value = true;
    clearErrors();
    try {
        if (isEdit.value && props.tag?.id) {
            await LibraryService.updateTag(props.tag.id, form.value);
            toast.success.update('Tag');
        } else {
            await LibraryService.createTag(form.value);
            toast.success.create('Tag');
        }
        
        emit('success');
        emit('update:open', false);
    } catch (error: unknown) {
        if (error && typeof error === 'object' && 'response' in error) {
            const err = error as { response: { status: number, data: { errors: Record<string, string[]> } } };
            if (err.response?.status === 422) {
                setErrors(err.response.data.errors || {});
            }
        } else {
            toast.error.fromResponse(error);
        }
    } finally {
        saving.value = false;
    }
};
</script>
