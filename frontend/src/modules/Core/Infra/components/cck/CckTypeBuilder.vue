<template>
  <div class="flex flex-col lg:flex-row gap-6 items-start">
    <!-- Left Field Palette -->
    <div class="w-full lg:w-64 shrink-0 space-y-3">
      <div class="flex items-center justify-between">
        <div>
          <h3 class="text-sm font-semibold text-foreground">
            {{ $t('infra.cck.builder.title') }}
          </h3>
          <p class="text-xs text-muted-foreground mt-0.5">
            {{ $t('infra.cck.builder.subtitle') }}
          </p>
        </div>
      </div>

      <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-1 gap-2">
        <button
          v-for="ft in fieldTypes"
          :key="ft.type"
          type="button"
          class="flex items-center gap-2.5 p-2.5 rounded-lg border border-border/70 bg-card hover:bg-muted/60 hover:border-primary/50 text-left transition-all group focus:outline-none focus:ring-2 focus:ring-primary/20"
          @click="addField(ft.type)"
        >
          <div class="p-1.5 rounded-md bg-muted group-hover:bg-primary/10 text-muted-foreground group-hover:text-primary transition-colors shrink-0">
            <component :is="ft.icon" class="h-4 w-4" />
          </div>
          <div class="min-w-0 flex-1">
            <div class="text-xs font-medium text-foreground truncate">
              {{ $t(`infra.cck.builder.types.${ft.type}`) }}
            </div>
            <div class="text-[10px] text-muted-foreground truncate hidden sm:block lg:block">
              {{ $t(`infra.cck.builder.types.${ft.type}Desc`) }}
            </div>
          </div>
          <Plus class="h-3.5 w-3.5 text-muted-foreground opacity-0 group-hover:opacity-100 transition-opacity shrink-0 ml-auto hidden lg:block" />
        </button>
      </div>
    </div>

    <!-- Right Draggable Field List -->
    <div class="flex-1 w-full min-w-0 space-y-3">
      <!-- Empty State -->
      <div
        v-if="fields.length === 0"
        class="rounded-xl border border-dashed border-border/80 p-8 text-center bg-muted/20 space-y-3"
      >
        <div class="mx-auto w-12 h-12 rounded-full bg-primary/10 flex items-center justify-center text-primary">
          <Layers class="h-6 w-6" />
        </div>
        <div class="space-y-1 max-w-sm mx-auto">
          <h4 class="text-sm font-medium text-foreground">
            {{ $t('infra.cck.builder.empty') }}
          </h4>
          <p class="text-xs text-muted-foreground">
            {{ $t('infra.cck.builder.emptyHint') }}
          </p>
        </div>
        <div class="flex flex-wrap items-center justify-center gap-1.5 pt-2">
          <Button
            v-for="ft in fieldTypes.slice(0, 4)"
            :key="ft.type"
            type="button"
            variant="outline"
            size="sm"
            class="h-8 text-xs gap-1.5"
            @click="addField(ft.type)"
          >
            <component :is="ft.icon" class="h-3.5 w-3.5" />
            {{ $t(`infra.cck.builder.types.${ft.type}`) }}
          </Button>
        </div>
      </div>

      <!-- Draggable Fields List -->
      <draggable
        v-else
        v-model="fields"
        item-key="_key"
        handle=".drag-handle"
        animation="200"
        ghost-class="opacity-40"
        class="space-y-3"
      >
        <template #item="{ element: field, index }">
          <Card class="overflow-hidden border border-border/70 hover:border-border transition-colors shadow-none">
            <!-- Card Header Bar -->
            <div class="flex items-center justify-between px-3 py-2 bg-muted/30 border-b border-border/50 text-xs">
              <div class="flex items-center gap-2 min-w-0">
                <div
                  class="drag-handle p-1 -ml-1 rounded cursor-grab active:cursor-grabbing hover:bg-muted text-muted-foreground hover:text-foreground transition-colors"
                  :title="$t('infra.cck.builder.dragHint')"
                >
                  <GripVertical class="h-4 w-4" />
                </div>
                <span class="font-mono text-[11px] text-muted-foreground font-semibold">#{{ index + 1 }}</span>
                <span class="font-medium text-foreground truncate max-w-[140px] sm:max-w-[220px]">
                  {{ field.name || 'Untitled' }}
                </span>
                <Badge variant="outline" class="text-[10px] h-5 px-1.5 py-0 gap-1 font-normal bg-background">
                  <component :is="getFieldIcon(field.type)" class="h-3 w-3" />
                  {{ $t(`infra.cck.builder.types.${field.type}`) }}
                </Badge>
                <Badge
                  v-if="field.is_required"
                  variant="destructive"
                  class="text-[10px] h-5 px-1.5 py-0 font-normal"
                >
                  {{ $t('infra.cck.builder.required') }}
                </Badge>
              </div>

              <div class="flex items-center gap-1">
                <Button
                  type="button"
                  variant="ghost"
                  size="icon"
                  class="h-7 w-7 text-muted-foreground hover:text-foreground"
                  :title="$t('infra.cck.builder.duplicate')"
                  @click="duplicateField(field)"
                >
                  <Copy class="h-3.5 w-3.5" />
                </Button>
                <Button
                  type="button"
                  variant="ghost"
                  size="icon"
                  class="h-7 w-7 text-destructive hover:bg-destructive/10"
                  :title="$t('infra.cck.builder.remove')"
                  @click="removeField(field._key)"
                >
                  <Trash2 class="h-3.5 w-3.5" />
                </Button>
              </div>
            </div>

            <!-- Card Body Form -->
            <CardContent class="p-4 space-y-4">
              <div class="grid gap-3 sm:grid-cols-3">
                <!-- Field Label -->
                <div>
                  <Label class="text-xs mb-1.5 block">
                    {{ $t('infra.cck.builder.label') }}
                    <span class="text-destructive">*</span>
                  </Label>
                  <Input
                    v-model="field.name"
                    class="h-9 text-xs"
                    :placeholder="$t('infra.cck.builder.labelPlaceholder')"
                    @blur="syncSlug(field)"
                  />
                </div>

                <!-- Field Slug -->
                <div>
                  <Label class="text-xs mb-1.5 block">
                    {{ $t('infra.cck.builder.slug') }}
                    <span class="text-destructive">*</span>
                  </Label>
                  <Input
                    v-model="field.slug"
                    class="h-9 font-mono text-xs"
                    :placeholder="$t('infra.cck.builder.slugPlaceholder')"
                    @input="sanitizeSlug(field)"
                  />
                </div>

                <!-- Field Type Selector -->
                <div>
                  <Label class="text-xs mb-1.5 block">
                    {{ $t('infra.cck.builder.type') }}
                  </Label>
                  <select
                    v-model="field.type"
                    class="w-full h-9 rounded-md border border-input bg-background px-2.5 py-1.5 text-xs text-foreground focus:outline-none focus:ring-2 focus:ring-primary/20"
                    @change="onTypeChange(field)"
                  >
                    <option
                      v-for="ft in fieldTypes"
                      :key="ft.type"
                      :value="ft.type"
                    >
                      {{ $t(`infra.cck.builder.types.${ft.type}`) }}
                    </option>
                  </select>
                </div>
              </div>

              <!-- Select Options Editor -->
              <div
                v-if="field.type === 'select'"
                class="space-y-1.5 rounded-lg border border-border/60 bg-muted/20 p-3"
              >
                <div class="flex items-center justify-between">
                  <Label class="text-xs font-medium">
                    {{ $t('infra.cck.builder.options') }}
                  </Label>
                  <span class="text-[11px] text-muted-foreground">
                    {{ $t('infra.cck.builder.optionsHint') }}
                  </span>
                </div>
                <Textarea
                  v-model="field._optionsLines"
                  rows="3"
                  class="font-mono text-xs bg-background resize-y"
                  :placeholder="$t('infra.cck.builder.optionsPlaceholder')"
                  @blur="applyOptions(field)"
                />
              </div>

              <!-- Required Toggle -->
              <div class="flex items-center justify-between pt-1 border-t border-border/40">
                <label class="flex items-center gap-2 text-xs font-medium text-foreground cursor-pointer select-none">
                  <Checkbox
                    :checked="field.is_required"
                    @update:checked="field.is_required = $event === true"
                  />
                  <span>{{ $t('infra.cck.builder.required') }}</span>
                </label>
              </div>
            </CardContent>
          </Card>
        </template>
      </draggable>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, watch, type Component } from 'vue';
import draggable from 'vuedraggable';
import {
  Type,
  AlignLeft,
  Hash,
  ToggleLeft,
  Calendar,
  Mail,
  Image as ImageIcon,
  ListFilter,
  GripVertical,
  Trash2,
  Copy,
  Plus,
  Layers,
} from 'lucide-vue-next';
import { Button, Card, CardContent, Checkbox, Input, Label, Textarea, Badge } from '@/shared/components/ui';
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

interface FieldTypeConfig {
    type: string;
    icon: Component;
}

const fieldTypes: FieldTypeConfig[] = [
    { type: 'text', icon: Type },
    { type: 'longtext', icon: AlignLeft },
    { type: 'number', icon: Hash },
    { type: 'boolean', icon: ToggleLeft },
    { type: 'date', icon: Calendar },
    { type: 'email', icon: Mail },
    { type: 'image', icon: ImageIcon },
    { type: 'select', icon: ListFilter },
];

function getFieldIcon(type: string): Component {
    const found = fieldTypes.find((f) => f.type === type);
    return found ? found.icon : Type;
}

const fields = ref<BuilderField[]>([]);

function toBuilderField(raw: CckFieldDefinition, index: number): BuilderField {
    return {
        ...raw,
        _key: `${raw.slug || 'field'}-${Date.now()}-${index}`,
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
        if (!value) {
            fields.value = [];
            return;
        }
        // Only update if length or keys differ to preserve local uncommitted edits
        if (value.length !== fields.value.length) {
            fields.value = value.map((f, i) => toBuilderField(f, i));
        }
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

function sanitizeSlug(field: BuilderField): void {
    field.slug = field.slug.toLowerCase().replace(/[^a-z0-9_]/g, '');
}

let fieldCounter = 0;

function addField(type: string): void {
    fieldCounter += 1;
    const defaultLabel = type.charAt(0).toUpperCase() + type.slice(1);
    const defaultSlug = `${type}_${fieldCounter}`;
    const newField: BuilderField = {
        _key: `field-${Date.now()}-${fieldCounter}`,
        name: defaultLabel,
        slug: defaultSlug,
        type,
        is_required: false,
        _optionsLines: type === 'select' ? 'option_1\noption_2' : '',
        options: type === 'select' ? ['option_1', 'option_2'] : undefined,
    };
    fields.value.push(newField);
}

function duplicateField(field: BuilderField): void {
    fieldCounter += 1;
    const clonedSlug = `${field.slug}_copy_${fieldCounter}`;
    const cloned: BuilderField = {
        ...field,
        _key: `field-clone-${Date.now()}-${fieldCounter}`,
        name: `${field.name} (Copy)`,
        slug: clonedSlug,
        options: field.options ? [...field.options] : undefined,
        _optionsLines: field._optionsLines,
    };
    fields.value.push(cloned);
}

function removeField(key: string): void {
    fields.value = fields.value.filter((f) => f._key !== key);
}

function syncSlug(field: BuilderField): void {
    if (!field.slug || field.slug.startsWith('field_') || field.slug.startsWith('text_') || field.slug.startsWith('number_')) {
        field.slug = slugify(field.name) || field.slug;
    }
}

function onTypeChange(field: BuilderField): void {
    if (field.type === 'select' && !field.options?.length) {
        field.options = ['option_1', 'option_2'];
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
