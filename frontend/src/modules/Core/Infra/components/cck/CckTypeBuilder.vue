<template>
  <div class="flex flex-col xl:flex-row gap-6">
    <aside class="xl:w-52 shrink-0 space-y-2">
      <h3 class="text-sm font-semibold">
        {{ $t('infra.cck.fields') }}
      </h3>
      <div class="grid grid-cols-2 xl:grid-cols-1 gap-2">
        <Button
          v-for="ft in fieldTypes"
          :key="ft.type"
          type="button"
          variant="outline"
          size="sm"
          class="justify-start text-xs"
          @click="addField(ft.type)"
        >
          {{ ft.label }}
        </Button>
      </div>
    </aside>

    <div class="flex-1 min-w-0 space-y-3">
      <div
        v-if="fields.length === 0"
        class="rounded-lg border border-dashed p-8 text-center text-sm text-muted-foreground"
      >
        {{ $t('infra.cck.addField') }}
      </div>

      <draggable
        v-else
        v-model="fields"
        item-key="_key"
        handle=".drag-handle"
        class="space-y-3"
      >
        <template #item="{ element: field }">
          <Card class="overflow-hidden">
            <div class="flex">
              <div class="drag-handle w-9 shrink-0 flex items-center justify-center cursor-grab bg-muted/40 border-r">
                <GripVertical class="h-4 w-4 text-muted-foreground" />
              </div>
              <CardContent class="flex-1 p-4 space-y-3">
                <div class="flex flex-wrap gap-2 items-center justify-between">
                  <select
                    :aria-label="'Field type'"
                    v-model="field.type"
                    class="rounded-md border border-input bg-background px-2 py-1.5 text-xs"
                    @change="onTypeChange(field)"
                  >
                    <option
                      v-for="ft in fieldTypes"
                      :key="ft.type"
                      :value="ft.type"
                    >
                      {{ ft.label }}
                    </option>
                  </select>
                  <Button
                    type="button"
                    variant="ghost"
                    size="icon"
                    class="text-red-800 hover:bg-red-50"
                    :aria-label="$t('common.actions.delete')"
                    @click="removeField(field._key)"
                  >
                    <Trash2 class="h-4 w-4" />
                  </Button>
                </div>
                <div class="grid gap-2 sm:grid-cols-2">
                  <div>
                    <Label class="text-xs">Label</Label>
                    <Input
                      :aria-label="field.name || 'Field label'"
                      v-model="field.name"
                      class="h-9"
                      @blur="syncSlug(field)"
                    />
                  </div>
                  <div>
                    <Label class="text-xs">Slug</Label>
                    <Input
                      aria-label="Field slug"
                      v-model="field.slug"
                      class="h-9 font-mono text-xs"
                    />
                  </div>
                </div>
                <div
                  v-if="field.type === 'select'"
                  class="space-y-1"
                >
                  <Label class="text-xs">Options (one per line)</Label>
                  <Textarea
                    v-model="field._optionsLines"
                    rows="3"
                    class="font-mono text-xs"
                    @blur="applyOptions(field)"
                  />
                </div>
                <label class="flex items-center gap-2 text-sm">
                  <Checkbox
                    :checked="field.is_required"
                    @update:checked="field.is_required = $event === true"
                  />
                  Required
                </label>
              </CardContent>
            </div>
          </Card>
        </template>
      </draggable>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';
import draggable from 'vuedraggable';
import {
  GripVertical,
  Trash2,
} from 'lucide-vue-next';
import { Button, Card, CardContent, Checkbox, Input, Label, Textarea } from '@/shared/components/ui';
import type { CckFieldDefinition } from '../../services/cckService';

export interface BuilderField extends CckFieldDefinition {
    _key: string;
    _optionsLines?: string;
}

const props = defineProps<{
    modelValue: CckFieldDefinition[];
}>();

const emit = defineEmits<{
    'update:modelValue': [value: CckFieldDefinition[]];
}>();

const fieldTypes = [
    { type: 'text', label: 'Text' },
    { type: 'longtext', label: 'Long text' },
    { type: 'number', label: 'Number' },
    { type: 'boolean', label: 'Boolean' },
    { type: 'date', label: 'Date' },
    { type: 'email', label: 'Email' },
    { type: 'image', label: 'Image URL' },
    { type: 'select', label: 'Select' },
] as const;

const fields = ref<BuilderField[]>([]);

function toBuilderField(raw: CckFieldDefinition, index: number): BuilderField {
    return {
        ...raw,
        _key: `${raw.slug}-${index}`,
        _optionsLines: (raw.options ?? []).join('\n'),
        is_required: raw.is_required ?? false,
    };
}

function fromBuilderField(field: BuilderField): CckFieldDefinition {
    const payload: CckFieldDefinition = {
        name: field.name,
        slug: field.slug,
        type: field.type,
        is_required: field.is_required ?? false,
    };
    if (field.type === 'select' && field.options?.length) {
        payload.options = field.options;
    }
    return payload;
}

watch(
    () => props.modelValue,
    (value) => {
        fields.value = value.map((f, i) => toBuilderField(f, i));
    },
    { immediate: true, deep: true },
);

watch(
    fields,
    () => {
        emit('update:modelValue', fields.value.map(fromBuilderField));
    },
    { deep: true },
);

function slugify(text: string): string {
    return text
        .toLowerCase()
        .trim()
        .replace(/[^a-z0-9]+/g, '_')
        .replace(/^_+|_+$/g, '');
}

let fieldCounter = 0;

function addField(type: string): void {
    fieldCounter += 1;
    fields.value.push({
        _key: `new-${fieldCounter}`,
        name: 'New field',
        slug: `field_${fieldCounter}`,
        type,
        is_required: false,
        _optionsLines: '',
        options: type === 'select' ? ['option_a', 'option_b'] : undefined,
    });
}

function removeField(key: string): void {
    fields.value = fields.value.filter((f) => f._key !== key);
}

function syncSlug(field: BuilderField): void {
    if (!field.slug || field.slug.startsWith('field_')) {
        field.slug = slugify(field.name) || field.slug;
    }
}

function onTypeChange(field: BuilderField): void {
    if (field.type === 'select' && !field.options?.length) {
        field.options = ['option_a', 'option_b'];
        field._optionsLines = field.options.join('\n');
    }
}

function applyOptions(field: BuilderField): void {
    const lines = (field._optionsLines ?? '')
        .split('\n')
        .map((l) => l.trim())
        .filter(Boolean);
    field.options = lines;
}
</script>
