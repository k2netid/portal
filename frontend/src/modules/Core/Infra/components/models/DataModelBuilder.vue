<template>
  <div class="flex flex-col xl:flex-row gap-6 items-start w-full">
    <!-- Left Field Palette -->
    <div class="w-full xl:w-72 2xl:w-80 shrink-0 space-y-4">
      <div>
        <h3 class="text-sm font-semibold text-foreground flex items-center gap-2">
          <Layers class="h-4 w-4 text-primary" />
          {{ $t('infra.models.builder.title') }}
        </h3>
        <p class="text-xs text-muted-foreground mt-0.5">
          {{ $t('infra.models.builder.subtitle') }}
        </p>
      </div>

      <!-- Field Types Categorized Grouping -->
      <div class="space-y-4">
        <!-- 1. Standard Fields -->
        <div class="space-y-1.5">
          <span class="text-[11px] font-semibold tracking-wider text-muted-foreground uppercase px-1">
            {{ $t('infra.models.builder.categories.standard') }}
          </span>
          <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-1 gap-1.5">
            <button
              v-for="ft in standardTypes"
              :key="ft.type"
              type="button"
              class="flex items-center gap-2.5 p-2 rounded-lg border border-border/70 bg-card hover:bg-muted/60 hover:border-primary/50 text-left transition-all group focus:outline-none focus:ring-2 focus:ring-primary/20"
              @click="addField(ft.type)"
            >
              <div class="p-1.5 rounded-md bg-muted group-hover:bg-primary/10 text-muted-foreground group-hover:text-primary transition-colors shrink-0">
                <component :is="ft.icon" class="h-3.5 w-3.5" />
              </div>
              <div class="min-w-0 flex-1">
                <div class="text-xs font-medium text-foreground truncate">
                  {{ $t(`infra.models.builder.types.${ft.type}`) }}
                </div>
                <div class="text-[10px] text-muted-foreground truncate hidden 2xl:block">
                  {{ $t(`infra.models.builder.types.${ft.type}Desc`) }}
                </div>
              </div>
              <Plus class="h-3.5 w-3.5 text-muted-foreground opacity-0 group-hover:opacity-100 transition-opacity shrink-0 ml-auto hidden xl:block" />
            </button>
          </div>
        </div>

        <!-- 2. Rich & Media Fields -->
        <div class="space-y-1.5">
          <span class="text-[11px] font-semibold tracking-wider text-muted-foreground uppercase px-1">
            {{ $t('infra.models.builder.categories.media') }}
          </span>
          <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-1 gap-1.5">
            <button
              v-for="ft in mediaTypes"
              :key="ft.type"
              type="button"
              class="flex items-center gap-2.5 p-2 rounded-lg border border-border/70 bg-card hover:bg-muted/60 hover:border-primary/50 text-left transition-all group focus:outline-none focus:ring-2 focus:ring-primary/20"
              @click="addField(ft.type)"
            >
              <div class="p-1.5 rounded-md bg-muted group-hover:bg-primary/10 text-muted-foreground group-hover:text-primary transition-colors shrink-0">
                <component :is="ft.icon" class="h-3.5 w-3.5" />
              </div>
              <div class="min-w-0 flex-1">
                <div class="text-xs font-medium text-foreground truncate">
                  {{ $t(`infra.models.builder.types.${ft.type}`) }}
                </div>
                <div class="text-[10px] text-muted-foreground truncate hidden 2xl:block">
                  {{ $t(`infra.models.builder.types.${ft.type}Desc`) }}
                </div>
              </div>
              <Plus class="h-3.5 w-3.5 text-muted-foreground opacity-0 group-hover:opacity-100 transition-opacity shrink-0 ml-auto hidden xl:block" />
            </button>
          </div>
        </div>

        <!-- 3. Advanced & Relational -->
        <div class="space-y-1.5">
          <span class="text-[11px] font-semibold tracking-wider text-muted-foreground uppercase px-1">
            {{ $t('infra.models.builder.categories.advanced') }}
          </span>
          <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-1 gap-1.5">
            <button
              v-for="ft in advancedTypes"
              :key="ft.type"
              type="button"
              class="flex items-center gap-2.5 p-2 rounded-lg border border-primary/30 bg-primary/[0.03] hover:bg-primary/[0.08] hover:border-primary/60 text-left transition-all group focus:outline-none focus:ring-2 focus:ring-primary/20"
              @click="addField(ft.type)"
            >
              <div class="p-1.5 rounded-md bg-primary/10 text-primary transition-colors shrink-0">
                <component :is="ft.icon" class="h-3.5 w-3.5" />
              </div>
              <div class="min-w-0 flex-1">
                <div class="text-xs font-semibold text-foreground truncate">
                  {{ $t(`infra.models.builder.types.${ft.type}`) }}
                </div>
                <div class="text-[10px] text-muted-foreground truncate hidden 2xl:block">
                  {{ $t(`infra.models.builder.types.${ft.type}Desc`) }}
                </div>
              </div>
              <Plus class="h-3.5 w-3.5 text-primary opacity-0 group-hover:opacity-100 transition-opacity shrink-0 ml-auto hidden xl:block" />
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Right Draggable Field List -->
    <div class="flex-1 w-full min-w-0 space-y-3">
      <!-- Empty State -->
      <div
        v-if="fields.length === 0"
        class="rounded-xl border border-dashed border-border/80 p-10 text-center bg-muted/20 space-y-4"
      >
        <div class="mx-auto w-14 h-14 rounded-full bg-primary/10 flex items-center justify-center text-primary">
          <Network class="h-7 w-7" />
        </div>
        <div class="space-y-1 max-w-md mx-auto">
          <h4 class="text-sm font-semibold text-foreground">
            {{ $t('infra.models.builder.empty') }}
          </h4>
          <p class="text-xs text-muted-foreground leading-relaxed">
            {{ $t('infra.models.builder.emptyHint') }}
          </p>
        </div>
        <div class="flex flex-wrap items-center justify-center gap-2 pt-2">
          <Button
            v-for="ft in allFieldTypes.slice(0, 5)"
            :key="ft.type"
            type="button"
            variant="outline"
            size="sm"
            class="h-8 text-xs gap-1.5"
            @click="addField(ft.type)"
          >
            <component :is="ft.icon" class="h-3.5 w-3.5" />
            {{ $t(`infra.models.builder.types.${ft.type}`) }}
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
            <div class="flex flex-wrap items-center justify-between gap-2 px-3.5 py-2.5 bg-muted/30 border-b border-border/50 text-xs">
              <div class="flex flex-wrap items-center gap-2 min-w-0 flex-1">
                <div
                  class="drag-handle p-1 -ml-1 rounded cursor-grab active:cursor-grabbing hover:bg-muted text-muted-foreground hover:text-foreground transition-colors shrink-0"
                  :title="$t('infra.models.builder.dragHint')"
                >
                  <GripVertical class="h-4 w-4" />
                </div>
                <span class="font-mono text-[11px] text-muted-foreground font-semibold shrink-0">#{{ index + 1 }}</span>
                <span class="font-semibold text-foreground truncate max-w-[160px] sm:max-w-[240px]">
                  {{ field.name || 'Untitled' }}
                </span>
                <Badge variant="outline" class="text-[10px] h-5 px-1.5 py-0 gap-1 font-normal bg-background shrink-0">
                  <component :is="getFieldIcon(field.type)" class="h-3 w-3" />
                  {{ $t(`infra.models.builder.types.${field.type}`) }}
                </Badge>
                <Badge
                  v-if="field.type === 'relation' && field.target_type"
                  variant="secondary"
                  class="text-[10px] h-5 px-1.5 py-0 gap-1 font-mono shrink-0"
                >
                  <ArrowRight class="h-2.5 w-2.5" />
                  {{ field.target_type }} ({{ field.relation_mode || 'single' }})
                </Badge>
                <Badge
                  v-if="field.is_required"
                  variant="destructive"
                  class="text-[10px] h-5 px-1.5 py-0 font-normal shrink-0"
                >
                  {{ $t('infra.models.builder.required') }}
                </Badge>
              </div>

              <div class="flex items-center gap-1 shrink-0 ml-auto">
                <Button
                  type="button"
                  variant="ghost"
                  size="icon"
                  class="h-7 w-7 text-muted-foreground hover:text-foreground"
                  :title="$t('infra.models.builder.duplicate')"
                  @click="duplicateField(field)"
                >
                  <Copy class="h-3.5 w-3.5" />
                </Button>
                <Button
                  type="button"
                  variant="ghost"
                  size="icon"
                  class="h-7 w-7 text-destructive hover:bg-destructive/10"
                  :title="$t('infra.models.builder.remove')"
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
                    {{ $t('infra.models.builder.label') }}
                    <span class="text-destructive">*</span>
                  </Label>
                  <Input
                    v-model="field.name"
                    class="h-9 text-xs"
                    :placeholder="$t('infra.models.builder.labelPlaceholder')"
                    @blur="syncSlug(field)"
                  />
                </div>

                <!-- Field Slug -->
                <div>
                  <Label class="text-xs mb-1.5 block">
                    {{ $t('infra.models.builder.slug') }}
                    <span class="text-destructive">*</span>
                  </Label>
                  <Input
                    v-model="field.slug"
                    class="h-9 font-mono text-xs"
                    :placeholder="$t('infra.models.builder.slugPlaceholder')"
                    @input="sanitizeSlug(field)"
                  />
                </div>

                <!-- Field Type Selector -->
                <div>
                  <Label class="text-xs mb-1.5 block">
                    {{ $t('infra.models.builder.type') }}
                  </Label>
                  <select
                    v-model="field.type"
                    class="w-full h-9 rounded-md border border-input bg-background px-2.5 py-1.5 text-xs text-foreground focus:outline-none focus:ring-2 focus:ring-primary/20"
                    @change="onTypeChange(field)"
                  >
                    <optgroup :label="$t('infra.models.builder.categories.standard')">
                      <option
                        v-for="ft in standardTypes"
                        :key="ft.type"
                        :value="ft.type"
                      >
                        {{ $t(`infra.models.builder.types.${ft.type}`) }}
                      </option>
                    </optgroup>
                    <optgroup :label="$t('infra.models.builder.categories.media')">
                      <option
                        v-for="ft in mediaTypes"
                        :key="ft.type"
                        :value="ft.type"
                      >
                        {{ $t(`infra.models.builder.types.${ft.type}`) }}
                      </option>
                    </optgroup>
                    <optgroup :label="$t('infra.models.builder.categories.advanced')">
                      <option
                        v-for="ft in advancedTypes"
                        :key="ft.type"
                        :value="ft.type"
                      >
                        {{ $t(`infra.models.builder.types.${ft.type}`) }}
                      </option>
                    </optgroup>
                  </select>
                </div>
              </div>

              <!-- Relation Configurator (When type === 'relation') -->
              <div
                v-if="field.type === 'relation'"
                class="space-y-3 rounded-lg border border-primary/30 bg-primary/[0.02] p-3.5"
              >
                <div class="flex items-center gap-2 text-xs font-semibold text-primary">
                  <Network class="h-4 w-4" />
                  <span>{{ $t('infra.models.builder.relation.title') }}</span>
                </div>

                <div class="grid gap-3 sm:grid-cols-2">
                  <div>
                    <Label class="text-xs mb-1.5 block">
                      {{ $t('infra.models.builder.relation.targetType') }}
                      <span class="text-destructive">*</span>
                    </Label>
                    <select
                      v-model="field.target_type"
                      class="w-full h-9 rounded-md border border-input bg-background px-2.5 py-1.5 text-xs text-foreground focus:outline-none focus:ring-2 focus:ring-primary/20"
                    >
                      <option value="" disabled>
                        {{ $t('infra.models.builder.relation.selectTarget') }}
                      </option>
                      <option
                        v-for="ct in availableTypes"
                        :key="ct.slug"
                        :value="ct.slug"
                      >
                        {{ ct.name }} ({{ ct.slug }})
                      </option>
                    </select>
                  </div>

                  <div>
                    <Label class="text-xs mb-1.5 block">
                      {{ $t('infra.models.builder.relation.mode') }}
                    </Label>
                    <select
                      v-model="field.relation_mode"
                      class="w-full h-9 rounded-md border border-input bg-background px-2.5 py-1.5 text-xs text-foreground focus:outline-none focus:ring-2 focus:ring-primary/20"
                    >
                      <option value="single">
                        {{ $t('infra.models.builder.relation.single') }} (Many-to-One / BelongsTo)
                      </option>
                      <option value="multiple">
                        {{ $t('infra.models.builder.relation.multiple') }} (Many-to-Many / HasMany)
                      </option>
                    </select>
                  </div>
                </div>
              </div>

              <!-- Select Options Editor (When type === 'select') -->
              <div
                v-if="field.type === 'select'"
                class="space-y-1.5 rounded-lg border border-border/60 bg-muted/20 p-3"
              >
                <div class="flex items-center justify-between">
                  <Label class="text-xs font-medium">
                    {{ $t('infra.models.builder.options') }}
                  </Label>
                  <span class="text-[11px] text-muted-foreground">
                    {{ $t('infra.models.builder.optionsHint') }}
                  </span>
                </div>
                <Textarea
                  v-model="field._optionsLines"
                  rows="3"
                  class="font-mono text-xs bg-background resize-y"
                  :placeholder="$t('infra.models.builder.optionsPlaceholder')"
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
                  <span>{{ $t('infra.models.builder.required') }}</span>
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
import { ref, watch, onMounted, type Component } from 'vue';
import draggable from 'vuedraggable';
import {
  Type,
  AlignLeft,
  FileText,
  Hash,
  ToggleLeft,
  Calendar,
  Mail,
  Link as LinkIcon,
  Palette,
  Image as ImageIcon,
  FolderOpen,
  ListFilter,
  Network,
  Code,
  GripVertical,
  Trash2,
  Copy,
  Plus,
  Layers,
  ArrowRight,
} from 'lucide-vue-next';
import { Button, Card, CardContent, Checkbox, Input, Label, Textarea, Badge } from '@/shared/components/ui';
import DataModelService, { type DataModelFieldDefinition, type DataModelSchema } from '../../services/dataModelService';

export interface BuilderField extends DataModelFieldDefinition {
    _key: string;
    _optionsLines?: string;
}

const props = defineProps<{
    modelValue: DataModelFieldDefinition[];
    currentTypeSlug?: string;
}>();

const emit = defineEmits<{
    'update:modelValue': [value: DataModelFieldDefinition[]];
}>();

interface FieldTypeConfig {
    type: string;
    icon: Component;
}

const standardTypes: FieldTypeConfig[] = [
    { type: 'text', icon: Type },
    { type: 'longtext', icon: AlignLeft },
    { type: 'number', icon: Hash },
    { type: 'boolean', icon: ToggleLeft },
    { type: 'select', icon: ListFilter },
    { type: 'date', icon: Calendar },
    { type: 'color', icon: Palette },
];

const mediaTypes: FieldTypeConfig[] = [
    { type: 'richtext', icon: FileText },
    { type: 'image', icon: ImageIcon },
    { type: 'media', icon: FolderOpen },
    { type: 'url', icon: LinkIcon },
    { type: 'email', icon: Mail },
];

const advancedTypes: FieldTypeConfig[] = [
    { type: 'relation', icon: Network },
    { type: 'json', icon: Code },
];

const allFieldTypes: FieldTypeConfig[] = [
    ...standardTypes,
    ...mediaTypes,
    ...advancedTypes,
];

function getFieldIcon(type: string): Component {
    const found = allFieldTypes.find((f) => f.type === type);
    return found ? found.icon : Type;
}

const fields = ref<BuilderField[]>([]);
const availableTypes = ref<DataModelSchema[]>([]);

onMounted(async () => {
    try {
        const res = await DataModelService.listTypes();
        if (res.data?.data) {
            availableTypes.value = (res.data.data as DataModelSchema[]).filter(
                (t) => t.slug !== props.currentTypeSlug,
            );
        }
    } catch {
        // graceful fallback
    }
});

function toBuilderField(raw: DataModelFieldDefinition, index: number): BuilderField {
    return {
        ...raw,
        _key: `${raw.slug || 'field'}-${Date.now()}-${index}`,
        _optionsLines: (raw.options ?? []).join('\n'),
        is_required: raw.is_required ?? false,
        target_type: raw.target_type ?? '',
        relation_mode: raw.relation_mode ?? 'single',
    };
}

function fromBuilderField(field: BuilderField): DataModelFieldDefinition {
    const payload: DataModelFieldDefinition = {
        name: field.name,
        slug: field.slug,
        type: field.type,
        is_required: field.is_required ?? false,
    };
    if (field.type === 'select' && field.options?.length) {
        payload.options = field.options;
    }
    if (field.type === 'relation') {
        payload.target_type = field.target_type || '';
        payload.relation_mode = field.relation_mode || 'single';
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
        type: type as DataModelFieldDefinition['type'],
        is_required: false,
        _optionsLines: type === 'select' ? 'option_1\noption_2' : '',
        options: type === 'select' ? ['option_1', 'option_2'] : undefined,
        target_type: type === 'relation' && availableTypes.value[0] ? availableTypes.value[0].slug : '',
        relation_mode: 'single',
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
        target_type: field.target_type,
        relation_mode: field.relation_mode,
    };
    fields.value.push(cloned);
}

function removeField(key: string): void {
    fields.value = fields.value.filter((f) => f._key !== key);
}

function syncSlug(field: BuilderField): void {
    if (!field.slug || field.slug.startsWith('field_') || field.slug.startsWith('text_') || field.slug.startsWith('number_') || field.slug.startsWith('relation_')) {
        field.slug = slugify(field.name) || field.slug;
    }
}

function onTypeChange(field: BuilderField): void {
    if (field.type === 'select' && !field.options?.length) {
        field.options = ['option_1', 'option_2'];
        field._optionsLines = field.options.join('\n');
    }
    if (field.type === 'relation' && !field.target_type && availableTypes.value[0]) {
        field.target_type = availableTypes.value[0].slug;
        field.relation_mode = 'single';
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
