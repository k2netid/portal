<template>
  <Dialog
    :open="open"
    @update:open="$emit('update:open', $event)"
  >
    <DialogContent
class="console-dialog-lg"
      @pointer-down-outside="handlePointerDownOutside"
    >
      <DialogHeader>
        <DialogTitle>{{ isEdit ? $t('publishing.categories.form.editTitle') : $t('publishing.categories.form.createTitle') }}</DialogTitle>
        <DialogDescription>
          {{ isEdit ? $t('publishing.categories.form.editDescription') : $t('publishing.categories.form.createDescription') }}
        </DialogDescription>
      </DialogHeader>

      <form
        class="space-y-6 py-4"
        @submit.prevent="handleSubmit"
      >
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Name -->
          <div class="space-y-2">
            <Label for="categoryFormName">
              {{ $t('publishing.categories.form.name') }} <span class="text-destructive">*</span>
            </Label>
            <Input
              id="categoryFormName"
              v-model="form.name"
              required
              :class="errors.name ? 'border-destructive focus-visible:ring-destructive' : ''"
              :placeholder="$t('publishing.categories.form.namePlaceholder')"
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
            <Label for="categoryFormSlug">
              {{ $t('publishing.categories.form.slug') }} <span class="text-destructive">*</span>
            </Label>
            <Input
              id="categoryFormSlug"
              v-model="form.slug"
              required
              :class="errors.slug ? 'border-destructive focus-visible:ring-destructive' : ''"
              :placeholder="$t('publishing.categories.form.slugPlaceholder')"
            />
            <p class="text-xs text-muted-foreground">
              {{ $t('publishing.categories.form.slugHelp') }}
            </p>
            <p
              v-if="errors.slug"
              class="text-sm text-destructive"
            >
              {{ Array.isArray(errors.slug) ? errors.slug[0] : errors.slug }}
            </p>
          </div>
        </div>

        <!-- Description -->
        <div class="space-y-2">
          <Label for="categoryFormDescription">
            {{ $t('publishing.categories.form.description') }}
          </Label>
          <Textarea
            id="categoryFormDescription"
            v-model="form.description"
            rows="3"
            :placeholder="$t('publishing.categories.form.descriptionPlaceholder')"
          />
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Parent Category -->
          <div class="space-y-2">
            <Label for="categoryFormParent">{{ $t('publishing.categories.form.parent') }}</Label>
            <Select
              :model-value="String(form.parent_id ?? 'null_value')"
              @update:model-value="(val) => form.parent_id = val === 'null_value' ? null : val"
            >
              <SelectTrigger id="categoryFormParent" :aria-label="$t('publishing.categories.form.parent')">
                <SelectValue :placeholder="$t('publishing.categories.form.noParent')" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="null_value">
                  {{ $t('publishing.categories.form.noParent') }}
                </SelectItem>
                <SelectItem
                  v-for="cat in availableParents"
                  :key="cat.id"
                  :value="cat.id.toString()"
                >
                  {{ cat.label }}
                </SelectItem>
              </SelectContent>
            </Select>
          </div>

          <!-- Sort Order -->
          <div class="space-y-2">
            <Label for="categoryFormSortOrder">{{ $t('publishing.categories.form.sortOrder') }}</Label>
            <Input
              id="categoryFormSortOrder"
              v-model.number="form.sort_order"
              type="number"
              min="0"
              :placeholder="$t('common.placeholders.numericZero')"
            />
          </div>
        </div>

        <!-- Image -->
        <div class="space-y-2">
          <Label>
            {{ $t('publishing.categories.form.image') }}
          </Label>
          <div
            v-if="form.image"
            class="mb-2 relative w-32 h-32 group"
          >
            <img
              :src="form.image"
              alt="Category image"
              class="w-full h-full object-cover rounded-lg border border-border"
            >
            <Button
              type="button"
              variant="destructive"
              size="icon"
              class="absolute -top-2 -right-2 h-6 w-6 opacity-0 group-hover:opacity-100 transition-opacity"
              @click="form.image = null"
            >
              <X class="w-3 h-3" />
            </Button>
          </div>
          <MediaPicker
            v-else
            v-model:open="isMediaPickerOpen"
            :label="$t('publishing.categories.form.selectImage')"
            @selected="(media) => form.image = media.url"
          />
        </div>

        <!-- Active Status -->
        <div class="flex items-center space-x-2">
          <Checkbox 
            id="is_active" 
            :checked="form.is_active"
            @update:checked="(val) => form.is_active = val"
          />
          <Label
            for="is_active"
            class="cursor-pointer"
          >
            {{ $t('publishing.categories.form.active') }}
          </Label>
        </div>

        <DialogFooter>
          <Button
            type="button"
            variant="outline"
            size="sm"
            @click="$emit('update:open', false)"
          >
            {{ $t('common.actions.cancel') }}
          </Button>
          <Button
            type="submit"
            size="sm"
            :disabled="saving || !isValid"
          >
            <Loader2
              v-if="saving"
              data-icon="inline-start" class="size-4 shrink-0 animate-spin"
            />
            {{ saving ? (isEdit ? $t('common.messages.loading.updating') : $t('common.messages.loading.creating')) : (isEdit ? $t('common.actions.update') : $t('common.actions.create')) }}
          </Button>
        </DialogFooter>
      </form>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import api from '@/engine/api/client';
import { useToast } from '@/shared/composables/useToast';
import { useFormValidation } from '@/shared/composables/useFormValidation';
import { categorySchema } from '@/shared/schemas';
import type { z } from 'zod';
import MediaPicker from '@/modules/Media/components/picker/MediaPicker.vue';
import {
  Loader2,
  X,
} from 'lucide-vue-next';
import type { Category } from '@/modules/Publishing/types/content';

// Shadcn UI
import {
    Button,
    Input,
    Label,
    Textarea,
    Checkbox,
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle
} from '@/shared/components/ui';

interface CategoryForm {
    name: string;
    slug: string;
    description: string;
    image: string | null;
    parent_id: string | null;
    is_active: boolean;
    sort_order: number;
}

interface FlattenedCategory {
    id: string;
    label: string;
    raw: Category;
}

const props = defineProps<{
    open: boolean;
    category?: Category | null;
    categories?: Category[]; // For parent selection
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
    'success': [];
}>();

const toast = useToast();
const { errors, validateWithZod, setErrors, clearErrors } = useFormValidation<z.infer<typeof categorySchema>>(categorySchema);

const saving = ref(false);
const isEdit = computed(() => !!props.category);
const isMediaPickerOpen = ref(false);

const form = ref<CategoryForm>({
    name: '',
    slug: '',
    description: '',
    image: null,
    parent_id: null,
    is_active: true,
    sort_order: 0
});

// Initialize form when opening
watch(() => props.open, (isOpen) => {
    if (isOpen) {
        clearErrors();
        if (props.category) {
            form.value = {
                name: props.category.name || '',
                slug: props.category.slug || '',
                description: props.category.description || '',
                image: props.category.image || null,
                parent_id: props.category.parent_id ? props.category.parent_id.toString() : null,
                is_active: !!props.category.is_active,
                sort_order: props.category.sort_order || 0
            };
        } else {
            // Reset
            form.value = {
                name: '',
                slug: '',
                description: '',
                image: null,
                parent_id: null,
                is_active: true,
                sort_order: 0
            };
        }
    }
});

const isValid = computed(() => {
    return !!form.value.name?.trim();
});

// Prevent closing modal when clicking inside MediaPicker (which is effectively outside the dialog content)
const handlePointerDownOutside = (e: Event) => {
    if (isMediaPickerOpen.value) {
        e.preventDefault();
    }
};

// Flatten categories for parent selection
const flattenTree = (nodes: Category[], depth = 0): FlattenedCategory[] => {
    if (!nodes) return [];
    let result: FlattenedCategory[] = [];
    nodes.forEach(node => {
        // Prevent selecting self or children as parent (circular)
        if (isEdit.value && (node.id === props.category?.id)) return;
        
        result.push({
            id: node.id,
            label: '— '.repeat(depth) + node.name,
            raw: node
        });
        
        const children = node.all_children || node.children;
        if (children && children.length > 0) {
            // If editing, filter out children of current node to prevent cycles
            // Ideally we check if node is a descendant, but simple containment check of ID in parent set is complex here without full tree traversal.
            // For now, simple client side recursion:
            result = result.concat(flattenTree(children, depth + 1));
        }
    });
    return result;
};

// Filtered available parents
const availableParents = computed(() => {
    return flattenTree(props.categories || []);
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
    const payload = { 
        ...form.value,
        parent_id: (form.value.parent_id === 'null_value' || !form.value.parent_id) 
            ? null 
            : parseInt(String(form.value.parent_id), 10)
    };

    if (!validateWithZod(payload)) return;

    saving.value = true;
    clearErrors();
    try {
        if (isEdit.value) {
            await api.put(`/manage/library/categories/${props.category?.id}`, payload);
            toast.success.update('Category');
        } else {
            await api.post('/manage/library/categories', payload);
            toast.success.create('Category');
        }
        
        emit('success');
        emit('update:open', false);
    } catch (error: unknown) {
        const err = error as { response?: { status?: number, data?: { errors?: Record<string, string[]> } } };
        if (err.response?.status === 422) {
            setErrors(err.response.data?.errors || {});
        } else {
            toast.error.fromResponse(error);
        }
    } finally {
        saving.value = false;
    }
};
</script>
