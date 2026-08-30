<template>
  <Dialog
    :open="true"
    @update:open="$emit('close')"
  >
    <DialogContent class="console-dialog-sm">
      <DialogHeader>
        <DialogTitle>
          {{ t('publishing.categories.move.title') }}
        </DialogTitle>
      </DialogHeader>

      <!-- Content -->
      <form
        class="space-y-4 py-4"
        @submit.prevent="handleSubmit"
      >
        <div>
          <p class="text-sm text-muted-foreground mb-4">
            {{ t('publishing.categories.move.description', { name: category.name }) }}
          </p>
          <div class="grid gap-2">
            <Label>
              {{ t('publishing.categories.move.newParent') }}
            </Label>
            <Select 
              :model-value="selectedParentId === null ? 'null' : String(selectedParentId)"
              @update:model-value="(val) => selectedParentId = (val === 'null' ? null : val)"
            >
              <SelectTrigger>
                <SelectValue :placeholder="t('publishing.categories.form.noParent')" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="null">
                  {{ t('publishing.categories.form.noParent') }}
                </SelectItem>
                <SelectItem
                  v-for="cat in availableParents"
                  :key="cat.id"
                  :value="String(cat.id)"
                >
                  {{ getCategoryPath(cat) }}
                </SelectItem>
              </SelectContent>
            </Select>
          </div>
        </div>
      </form>

      <DialogFooter>
        <Button
          variant="outline"
          size="sm"
          @click="$emit('close')"
        >
          {{ t('publishing.categories.move.cancel') }}
        </Button>
        <Button
          size="sm"
          :disabled="saving"
          @click="handleSubmit"
        >
          {{ saving ? t('publishing.categories.move.moving') : t('publishing.categories.move.submit') }}
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { 
    Button, 
    Dialog, 
    DialogContent, 
    DialogHeader, 
    DialogTitle, 
    DialogFooter,
    Label,
    Select,
    SelectTrigger,
    SelectValue,
    SelectContent,
    SelectItem
} from '@/shared/components/ui';
import { useToast } from '@/shared/composables/useToast';
import { useFormValidation } from '@/shared/composables/useFormValidation';
import { moveCategorySchema } from '@/shared/schemas';
import api from '@/engine/api/client';

interface Category {
    id: string | string;
    name: string;
    parent_id?: string | null;
    [key: string]: unknown;
}

const { t } = useI18n();
const toast = useToast();
const { errors: _errors, validateWithZod, setErrors, clearErrors } = useFormValidation(moveCategorySchema);

const props = defineProps<{
    category: Category;
    categories?: Category[];
}>();

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'moved'): void;
}>();

const saving = ref(false);
const selectedParentId = ref<string | null>(null);

const availableParents = computed(() => {
    const cats = props.categories || [];
    // Exclude self and descendants from parent options
    return cats.filter(cat => {
        if (cat.id === props.category.id) return false;
        // Check if cat is a descendant of current category
        let check: Category | undefined = cat;
        while (check?.parent_id) {
            if (check.parent_id === props.category.id) return false;
            check = cats.find(c => c.id === check!.parent_id);
            if (!check) break;
        }
        return true;
    });
});

const getCategoryPath = (category: Category) => {
    let path = category.name;
    let current = category;
    const cats = props.categories || [];
    
    while (current.parent_id) {
        const parent = cats.find(c => c.id === current.parent_id);
        if (parent) {
            path = parent.name + ' > ' + path;
            current = parent;
        } else {
            break;
        }
    }
    return path;
};

const handleSubmit = async () => {
    if (!validateWithZod({ parent_id: selectedParentId.value })) return;

    saving.value = true;
    clearErrors();
    try {
        await api.post(`/manage/library/categories/${props.category.id}/move`, {
            parent_id: selectedParentId.value,
        });
        toast.success.action(t('publishing.categories.messages.moveSuccess'));
        emit('moved');
    } catch (error: unknown) {
        const err = error as import('axios').AxiosError<{ errors: Record<string, string[]> }>;
        if (err.response?.status === 422) {
            setErrors(err.response.data.errors || {});
        } else {
            toast.error.fromResponse(err);
        }
    } finally {
        saving.value = false;
    }
};

onMounted(() => {
    selectedParentId.value = props.category.parent_id || null;
});
</script>

