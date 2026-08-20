<template>
  <div class="space-y-1.5">
    <div class="flex items-center justify-between">
      <Label :for="inputId" class="text-xs font-medium text-foreground flex items-center gap-1.5">
        <span>{{ field.name }}</span>
        <span
          v-if="field.is_required"
          class="text-destructive font-bold"
        >*</span>
        <Badge v-if="field.type === 'relation'" variant="outline" class="text-[9px] h-4 px-1 py-0 font-normal">
          {{ field.target_type }}
        </Badge>
      </Label>
      <span class="text-[11px] font-mono text-muted-foreground">
        {{ field.slug }}
      </span>
    </div>

    <!-- 1. Long Text / Textarea -->
    <Textarea
      v-if="field.type === 'longtext'"
      :id="inputId"
      :model-value="stringValue"
      rows="4"
      class="text-xs resize-y font-normal"
      :required="field.is_required"
      :placeholder="field.placeholder || field.name"
      @update:model-value="onInput"
    />

    <!-- 2. Rich Text (WYSIWYG/HTML/Markdown) -->
    <div v-else-if="field.type === 'richtext'" class="space-y-1.5">
      <Textarea
        :id="inputId"
        :model-value="stringValue"
        rows="6"
        class="text-xs resize-y font-mono leading-relaxed"
        :required="field.is_required"
        :placeholder="field.placeholder || '<p>Enter formatted rich content...</p>'"
        @update:model-value="onInput"
      />
      <div class="flex items-center justify-between text-[11px] text-muted-foreground px-1">
        <span>{{ $t('infra.dynamic.record.richTextHint') }}</span>
        <span class="font-mono text-[10px]">{{ stringValue.length }} chars</span>
      </div>
    </div>

    <!-- 3. Relation (Foreign Key / Target Content Type Records) -->
    <div v-else-if="field.type === 'relation'" class="space-y-1">
      <div v-if="isLoadingRelation" class="flex items-center gap-2 text-xs text-muted-foreground py-1.5 px-3 rounded border border-border/50 bg-muted/20">
        <Loader2 class="h-3.5 w-3.5 animate-spin text-primary" />
        <span>{{ $t('infra.dynamic.record.loadingRelations') }}</span>
      </div>
      <div v-else class="relative">
        <select
          :id="inputId"
          :value="stringValue"
          class="w-full h-9 rounded-md border border-input bg-background px-3 py-1.5 text-xs text-foreground focus:outline-none focus:ring-2 focus:ring-primary/20"
          :required="field.is_required"
          @change="onInput(($event.target as HTMLSelectElement).value)"
        >
          <option value="">
            {{ $t('infra.dynamic.record.selectRelationPlaceholder') }}
          </option>
          <option
            v-for="rec in relationOptions"
            :key="rec.id"
            :value="rec.id"
          >
            {{ getRecordDisplayName(rec) }}
          </option>
        </select>
      </div>
      <p v-if="!isLoadingRelation && relationOptions.length === 0" class="text-[11px] text-amber-600 dark:text-amber-400">
        {{ $t('infra.dynamic.record.noRelationRecords', { type: field.target_type }) }}
      </p>
    </div>

    <!-- 4. Select Dropdown -->
    <div v-else-if="field.type === 'select'" class="relative">
      <select
        :id="inputId"
        :value="stringValue"
        class="w-full h-9 rounded-md border border-input bg-background px-3 py-1.5 text-xs text-foreground focus:outline-none focus:ring-2 focus:ring-primary/20"
        :required="field.is_required"
        @change="onInput(($event.target as HTMLSelectElement).value)"
      >
        <option value="" disabled>
          {{ $t('infra.dynamic.record.selectPlaceholder') }}
        </option>
        <option
          v-for="opt in field.options ?? []"
          :key="opt"
          :value="opt"
        >
          {{ opt }}
        </option>
      </select>
    </div>

    <!-- 5. Boolean Switch / Checkbox -->
    <div
      v-else-if="field.type === 'boolean'"
      class="flex items-center gap-2.5 pt-1"
    >
      <Checkbox
        :id="inputId"
        :checked="booleanValue"
        @update:checked="onInput($event === true)"
      />
      <label :for="inputId" class="text-xs text-foreground cursor-pointer select-none">
        {{ booleanValue ? $t('infra.dynamic.record.booleanYes') : $t('infra.dynamic.record.booleanNo') }}
      </label>
    </div>

    <!-- 6. Color Picker -->
    <div v-else-if="field.type === 'color'" class="flex items-center gap-2">
      <input
        type="color"
        :value="stringValue || '#3b82f6'"
        class="w-9 h-9 p-0.5 rounded border border-border/80 bg-background cursor-pointer shrink-0"
        @input="onInput(($event.target as HTMLInputElement).value)"
      />
      <Input
        :id="inputId"
        type="text"
        :model-value="stringValue"
        :required="field.is_required"
        class="h-9 font-mono text-xs max-w-[140px]"
        placeholder="#3b82f6"
        @update:model-value="onInput"
      />
      <div
        v-if="stringValue"
        class="h-7 w-7 rounded-md border border-border shrink-0 shadow-sm"
        :style="{ backgroundColor: stringValue }"
      />
    </div>

    <!-- 7. URL with Test Action -->
    <div v-else-if="field.type === 'url'" class="space-y-1">
      <div class="flex gap-2">
        <Input
          :id="inputId"
          type="url"
          :model-value="stringValue"
          :required="field.is_required"
          class="h-9 text-xs"
          placeholder="https://example.com"
          @update:model-value="onInput"
        />
        <Button
          v-if="stringValue"
          type="button"
          variant="outline"
          size="sm"
          class="h-9 px-2.5 shrink-0 text-xs gap-1"
          as="a"
          :href="stringValue"
          target="_blank"
          rel="noopener noreferrer"
        >
          <ExternalLink class="h-3.5 w-3.5" />
          {{ $t('infra.dynamic.record.openUrl') }}
        </Button>
      </div>
    </div>

    <!-- 8. JSON / Structured Object -->
    <div v-else-if="field.type === 'json'" class="space-y-1.5">
      <Textarea
        :id="inputId"
        :model-value="jsonStringValue"
        rows="4"
        class="text-xs font-mono resize-y bg-background"
        :placeholder="`{\n  &quot;key&quot;: &quot;value&quot;\n}`"
        @blur="onJsonBlur"
        @update:model-value="(val: string | number) => { rawJsonText = String(val); }"
      />
      <p v-if="jsonParseError" class="text-[11px] text-destructive">
        {{ $t('infra.dynamic.record.jsonError') }}
      </p>
      <p v-else class="text-[10px] text-muted-foreground">
        {{ $t('infra.dynamic.record.jsonHint') }}
      </p>
    </div>

    <!-- 9. Image / Media Asset Picker with Media Library integration -->
    <div v-else-if="field.type === 'image' || field.type === 'media'" class="space-y-2">
      <!-- Media Preview Card when value is present -->
      <div
        v-if="stringValue"
        class="flex items-center gap-3 p-2.5 rounded-lg border border-border/80 bg-muted/20"
      >
        <div class="w-14 h-14 rounded-md border bg-background overflow-hidden shrink-0 flex items-center justify-center shadow-xs">
          <img
            :src="stringValue"
            alt="Preview"
            class="w-full h-full object-cover"
            @error="imageLoadError = true"
            @load="imageLoadError = false"
          >
        </div>
        <div class="min-w-0 flex-1 space-y-1">
          <p class="text-xs font-mono font-medium text-foreground truncate">{{ stringValue }}</p>
          <div class="flex items-center gap-2">
            <Button
              type="button"
              variant="outline"
              size="sm"
              class="h-7 text-[11px] gap-1 px-2"
              @click="isMediaPickerOpen = true"
            >
              <ImageIcon class="h-3 w-3" />
              {{ $t('common.actions.change') || 'Change' }}
            </Button>
            <Button
              type="button"
              variant="ghost"
              size="sm"
              class="h-7 text-[11px] text-destructive hover:bg-destructive/10 px-2 gap-1"
              @click="onInput('')"
            >
              <X class="h-3 w-3" />
              {{ $t('common.actions.remove') || 'Remove' }}
            </Button>
          </div>
        </div>
      </div>

      <!-- Empty state picker button -->
      <div v-else class="flex flex-col sm:flex-row gap-2">
        <Button
          type="button"
          variant="outline"
          class="h-10 border-dashed gap-2 text-xs flex-1 justify-center"
          @click="isMediaPickerOpen = true"
        >
          <ImageIcon class="h-4 w-4 text-primary" />
          {{ $t('media.modals.picker.select') || 'Choose from Media Library' }}
        </Button>
        <Input
          :id="inputId"
          type="text"
          :model-value="stringValue"
          :required="field.is_required"
          class="h-10 text-xs sm:w-64"
          placeholder="or paste URL directly..."
          @update:model-value="onInput"
        />
      </div>

      <!-- MediaPicker Modal -->
      <MediaPicker
        v-model:open="isMediaPickerOpen"
        :label="field.name"
        @selected="(media: any) => onInput(media?.url || media?.path || '')"
      />
    </div>

    <!-- 10. Standard Text / Number / Email / Date Input -->
    <Input
      v-else
      :id="inputId"
      :type="inputType"
      :model-value="stringValue"
      :required="field.is_required"
      class="h-9 text-xs"
      :placeholder="field.placeholder || field.name"
      @update:model-value="onInput"
    />
  </div>
</template>

<script setup lang="ts">
import { computed, ref, onMounted, watch } from 'vue';
import { ExternalLink, Loader2, Image as ImageIcon, X } from 'lucide-vue-next';
import { Input, Label, Textarea, Checkbox, Button, Badge } from '@/shared/components/ui';
import MediaPicker from '@/modules/Content/Media/components/picker/MediaPicker.vue';
import type { DataModelFieldDefinition } from '../../services/dataModelService';
import DynamicRecordService, { type DynamicRecordRow } from '../../services/dynamicRecordService';

const props = defineProps<{
    field: DataModelFieldDefinition;
    modelValue: unknown;
}>();

const emit = defineEmits<{
    'update:modelValue': [value: unknown];
}>();

const isMediaPickerOpen = ref(false);
const imageLoadError = ref(false);
const inputId = computed(() => `dyn-${props.field.slug}`);

// Relational Loading
const isLoadingRelation = ref(false);
const relationOptions = ref<DynamicRecordRow[]>([]);

onMounted(async () => {
    if (props.field.type === 'relation' && props.field.target_type) {
        isLoadingRelation.value = true;
        try {
            const res = await DynamicRecordService.list(props.field.target_type, { per_page: 100 });
            if (res.data?.data?.data) {
                relationOptions.value = res.data.data.data as DynamicRecordRow[];
            }
        } catch {
            relationOptions.value = [];
        } finally {
            isLoadingRelation.value = false;
        }
    }
});

function getRecordDisplayName(rec: DynamicRecordRow): string {
    const data = rec.data || {};
    const titleCandidates = ['title', 'name', 'label', 'heading', 'email', 'slug', 'project_name'];
    for (const key of titleCandidates) {
        if (data[key] && typeof data[key] === 'string') {
            return `${data[key]} (${rec.id.slice(0, 8)})`;
        }
    }
    return `Record #${rec.id.slice(0, 8)}`;
}

// JSON Parsing Logic
const rawJsonText = ref('');
const jsonParseError = ref(false);

const jsonStringValue = computed(() => {
    if (rawJsonText.value) return rawJsonText.value;
    if (props.modelValue === null || props.modelValue === undefined) return '';
    if (typeof props.modelValue === 'object') {
        try {
            return JSON.stringify(props.modelValue, null, 2);
        } catch {
            return '';
        }
    }
    return String(props.modelValue);
});

function onJsonBlur(): void {
    if (!rawJsonText.value.trim()) {
        jsonParseError.value = false;
        emit('update:modelValue', null);
        return;
    }
    try {
        const parsed = JSON.parse(rawJsonText.value);
        jsonParseError.value = false;
        emit('update:modelValue', parsed);
    } catch {
        jsonParseError.value = true;
    }
}

watch(
    () => props.modelValue,
    (val) => {
        if (props.field.type === 'json' && typeof val === 'object' && val !== null) {
            rawJsonText.value = JSON.stringify(val, null, 2);
        }
    },
    { immediate: true },
);

const stringValue = computed(() => {
    if (props.modelValue === null || props.modelValue === undefined) {
        return '';
    }
    return String(props.modelValue);
});

const booleanValue = computed(() => Boolean(props.modelValue));

const inputType = computed(() => {
    switch (props.field.type) {
        case 'email':
            return 'email';
        case 'number':
            return 'number';
        case 'date':
            return 'date';
        default:
            return 'text';
    }
});

function onInput(value: unknown): void {
    if (props.field.type === 'number') {
        if (typeof value === 'string') {
            if (value === '') {
                emit('update:modelValue', null);
                return;
            }
            const num = Number(value);
            emit('update:modelValue', Number.isNaN(num) ? value : num);
            return;
        }
    }
    emit('update:modelValue', value);
}
</script>
