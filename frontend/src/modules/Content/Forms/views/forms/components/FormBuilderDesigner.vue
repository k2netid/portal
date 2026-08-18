<template>
  <div class="space-y-6">
    <div
      v-if="loadError"
      class="rounded-lg border border-destructive/40 bg-destructive/5 px-4 py-3 text-sm text-destructive"
    >
      {{ loadError }}
    </div>

    <div
      v-else-if="loading"
      class="flex flex-col items-center justify-center py-16 text-muted-foreground gap-2"
    >
      <Loader2 class="h-8 w-8 animate-spin text-primary" />
      <span class="text-sm">{{ $t('forms.builder.loading') }}</span>
    </div>

    <div
      v-else
      class="flex flex-col xl:flex-row gap-8"
    >
      <!-- Sidebar: add field (Google Forms–style palette) -->
      <aside class="xl:w-56 shrink-0 space-y-3">
        <div>
          <h3 class="text-sm font-semibold text-foreground">
            {{ $t('forms.builder.sidebarTitle') }}
          </h3>
          <p class="text-xs text-muted-foreground mt-1 leading-relaxed">
            {{ $t('forms.builder.sidebarHint') }}
          </p>
        </div>
        <div class="grid grid-cols-2 xl:grid-cols-1 gap-2">
          <Button
            v-for="ft in fieldTypes"
            :key="ft.type"
            type="button"
            variant="outline"
            size="sm"
            class="h-auto py-2.5 px-3 justify-start gap-2 border-border/80 hover:border-primary/40 hover:bg-primary/5"
            :disabled="adding"
            @click="addField(ft.type)"
          >
            <component
              :is="ft.icon"
              class="h-4 w-4 shrink-0 text-primary"
            />
            <span class="text-xs font-medium text-left leading-tight">{{ $t(ft.labelKey) }}</span>
          </Button>
        </div>
      </aside>

      <!-- Canvas -->
      <div class="flex-1 min-w-0 space-y-4">
        <div
          v-if="fields.length === 0"
          class="rounded-xl border border-dashed border-border/80 bg-muted/20 px-6 py-14 text-center"
        >
          <p class="text-sm text-muted-foreground mb-4">
            {{ $t('forms.builder.empty') }}
          </p>
          <Button
            type="button"
            variant="secondary"
            size="sm"
            :disabled="adding"
            @click="addField('text')"
          >
            <Plus class="h-4 w-4 mr-2" />
            {{ $t('forms.builder.addFirst') }}
          </Button>
        </div>

        <draggable
          v-model="fields"
          item-key="id"
          handle=".drag-handle"
          animation="180"
          ghost-class="fb-ghost"
          class="space-y-4"
          @start="onDragStart"
          @end="onDragEnd"
        >
          <template #item="{ element: field }">
            <Card class="overflow-hidden border-border/70 shadow-sm rounded-xl transition-shadow hover:shadow-md">
              <div class="flex gap-0">
                <div
                  class="drag-handle w-10 shrink-0 flex items-center justify-center cursor-grab active:cursor-grabbing bg-muted/40 border-r border-border/60 text-muted-foreground hover:text-foreground hover:bg-muted/60"
                  :title="$t('forms.builder.dragHint')"
                >
                  <GripVertical class="h-5 w-5" />
                </div>
                <div class="flex-1 min-w-0 p-4 sm:p-5 space-y-4">
                  <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="flex flex-wrap items-center gap-2 min-w-[200px] flex-1">
                      <Select
                        :model-value="field.type"
                        @update:model-value="(v) => onTypeChange(field, String(v ?? ''))"
                      >
                        <SelectTrigger class="h-9 w-[200px] max-w-full text-xs font-medium" :aria-label="$t('forms.builder.questionLabel') + ': ' + field.label">
                          <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                          <SelectItem
                            v-for="ft in fieldTypes"
                            :key="ft.type"
                            :value="ft.type"
                          >
                            {{ $t(ft.labelKey) }}
                          </SelectItem>
                        </SelectContent>
                      </Select>
                      <Badge
                        variant="secondary"
                        class="font-mono text-[10px] uppercase tracking-wide"
                      >
                        {{ field.name }}
                      </Badge>
                    </div>
                    <Button
                      type="button"
                      variant="ghost"
                      size="icon"
                      class="text-muted-foreground hover:text-destructive shrink-0"
                      :title="$t('forms.builder.remove')"
                      :aria-label="$t('forms.builder.remove')"
                      @click="removeField(field)"
                    >
                      <Trash2 class="h-4 w-4" />
                    </Button>
                  </div>

                  <div class="space-y-1.5">
                    <label class="text-[11px] uppercase tracking-wider font-bold text-muted-foreground">
                      {{ $t('forms.builder.questionLabel') }}
                    </label>
                    <Input
                      v-model="field.label"
                      class="text-base font-medium h-11 bg-background/80"
                      :placeholder="$t('forms.builder.questionPlaceholder')"
                      @blur="void flushSave(field)"
                    />
                  </div>

                  <div class="space-y-1.5">
                    <label class="text-[11px] uppercase tracking-wider font-bold text-muted-foreground">
                      {{ $t('forms.builder.helpText') }}
                    </label>
                    <Input
                      v-model="field.help_text"
                      class="h-10 bg-background/80"
                      :placeholder="$t('forms.builder.helpPlaceholder')"
                      @blur="void flushSave(field)"
                    />
                  </div>

                  <div
                    v-if="field.type !== 'boolean'"
                    class="space-y-1.5"
                  >
                    <label class="text-[11px] uppercase tracking-wider font-bold text-muted-foreground">
                      {{ $t('forms.builder.placeholder') }}
                    </label>
                    <Input
                      v-model="field.placeholder"
                      class="h-10 bg-background/80"
                      :placeholder="$t('forms.builder.placeholderHint')"
                      @blur="void flushSave(field)"
                    />
                  </div>

                  <div
                    v-if="needsOptions(field.type)"
                    class="space-y-1.5"
                  >
                    <label class="text-[11px] uppercase tracking-wider font-bold text-muted-foreground">
                      {{ $t('forms.builder.options') }}
                    </label>
                    <Textarea
                      v-model="field._optionsLines"
                      rows="4"
                      class="font-mono text-sm bg-background/80"
                      :placeholder="$t('forms.builder.optionsPlaceholder')"
                      @blur="onOptionsBlur(field)"
                    />
                    <p class="text-[11px] text-muted-foreground">
                      {{ $t('forms.builder.optionsHelp') }}
                    </p>
                  </div>

                  <div class="flex items-center justify-between gap-4 pt-1 border-t border-border/50">
                    <div class="flex items-center gap-2">
                      <Switch
                        :id="'req-' + field.id"
                        :checked="field.is_required"
                        :aria-label="$t('forms.builder.required')"
                        @update:checked="(v: boolean | 'indeterminate') => { field.is_required = v === true; void flushSave(field); }"
                      />
                      <label :for="'req-' + field.id" class="text-sm text-foreground cursor-pointer">{{ $t('forms.builder.required') }}</label>
                    </div>
                    <span
                      v-if="savingIds.has(field.id)"
                      class="text-xs text-muted-foreground flex items-center gap-1"
                    >
                      <Loader2 class="h-3 w-3 animate-spin" />
                      {{ $t('forms.builder.saving') }}
                    </span>
                  </div>
                </div>
              </div>
            </Card>
          </template>
        </draggable>

        <Button
          v-if="fields.length > 0"
          type="button"
          variant="outline"
          class="w-full border-dashed h-12 text-muted-foreground hover:text-foreground hover:border-primary/50"
          :disabled="adding"
          @click="addField('text')"
        >
          <Plus class="h-4 w-4 mr-2" />
          {{ $t('forms.builder.addQuestion') }}
        </Button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { logger } from '@/shared/utils/logger';
import { ref, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import draggable from 'vuedraggable';
import { FormsService } from '@/modules/Content/Forms/services/formsService';
import { useToast } from '@/shared/composables/useToast';
import {
    Button,
    Badge,
    Card,
    Input,
    Textarea,
    Select,
    SelectTrigger,
    SelectValue,
    SelectContent,
    SelectItem,
    Switch,
} from '@/shared/components/ui';
import {
  AtSign,
  Calendar,
  CalendarClock,
  CircleDot,
  GripVertical,
  Hash,
  ImageIcon,
  Link2,
  List,
  ListChecks,
  Loader2,
  Paperclip,
  Pilcrow,
  Plus,
  ToggleLeft,
  Trash2,
  Type,
} from 'lucide-vue-next';

export interface AdminFormFieldRow {
    id: string;
    form_id?: string;
    name: string;
    label: string;
    type: string;
    placeholder: string | null;
    help_text: string | null;
    options: { label: string; value: string }[] | null;
    validation_rules: string[];
    is_required: boolean;
    sort_order: number;
    _optionsLines?: string;
}

const props = defineProps<{
    formId: string;
}>();

const { t } = useI18n();
const toast = useToast();

const loading = ref(true);
const loadError = ref<string | null>(null);
const adding = ref(false);
const fields = ref<AdminFormFieldRow[]>([]);
const savingIds = ref<Set<string | number>>(new Set());

const fieldTypes = [
    { type: 'text', icon: Type, labelKey: 'forms.builder.types.text' },
    { type: 'textarea', icon: Pilcrow, labelKey: 'forms.builder.types.textarea' },
    { type: 'email', icon: AtSign, labelKey: 'forms.builder.types.email' },
    { type: 'url', icon: Link2, labelKey: 'forms.builder.types.url' },
    { type: 'number', icon: Hash, labelKey: 'forms.builder.types.number' },
    { type: 'date', icon: Calendar, labelKey: 'forms.builder.types.date' },
    { type: 'datetime', icon: CalendarClock, labelKey: 'forms.builder.types.datetime' },
    { type: 'boolean', icon: ToggleLeft, labelKey: 'forms.builder.types.boolean' },
    { type: 'select', icon: List, labelKey: 'forms.builder.types.select' },
    { type: 'radio', icon: CircleDot, labelKey: 'forms.builder.types.radio' },
    { type: 'checkbox', icon: ListChecks, labelKey: 'forms.builder.types.checkbox' },
    { type: 'multiselect', icon: ListChecks, labelKey: 'forms.builder.types.multiselect' },
    { type: 'file', icon: Paperclip, labelKey: 'forms.builder.types.file' },
    { type: 'image', icon: ImageIcon, labelKey: 'forms.builder.types.image' },
];

function needsOptions(type: string): boolean {
    return ['select', 'radio', 'multiselect', 'checkbox'].includes(type);
}

function optionsToLines(opts: AdminFormFieldRow['options']): string {
    if (!opts?.length) {
        return '';
    }
    return opts.map((o) => `${o.label}|${o.value}`).join('\n');
}

function linesToOptions(text: string): { label: string; value: string }[] {
    const lines = text.split('\n').map((l) => l.trim()).filter(Boolean);
    return lines.map((line) => {
        const parts = line.split('|').map((p) => p.trim());
        if (parts.length >= 2) {
            return { label: parts[0] ?? '', value: parts[1] ?? parts[0] ?? '' };
        }
        const s = parts[0] ?? '';
        return { label: s, value: s };
    });
}

function decorateField(f: AdminFormFieldRow): AdminFormFieldRow {
    return {
        ...f,
        placeholder: f.placeholder ?? '',
        help_text: f.help_text ?? '',
        _optionsLines: optionsToLines(f.options),
    };
}

async function fetchFields(): Promise<void> {
    loading.value = true;
    loadError.value = null;
    try {
        const res = await FormsService.get(String(props.formId));
        const data = res.data as { fields?: AdminFormFieldRow[] };
        const raw = data.fields;
        const list = Array.isArray(raw) ? raw as AdminFormFieldRow[] : [];
        list.sort((a, b) => (a.sort_order ?? 0) - (b.sort_order ?? 0));
        fields.value = list.map((f) => decorateField({ ...f }));
    } catch (e: unknown) {
        logger.error('Form builder load failed', e);
        loadError.value = t('forms.builder.loadError');
    } finally {
        loading.value = false;
    }
}

async function persistField(field: AdminFormFieldRow): Promise<void> {
    savingIds.value = new Set(savingIds.value).add(field.id);
    try {
        const optionsPayload = needsOptions(field.type)
            ? linesToOptions(field._optionsLines ?? '')
            : null;
        await FormsService.updateField(String(props.formId), String(field.id), {
            label: field.label,
            type: field.type,
            placeholder: field.placeholder || null,
            help_text: field.help_text || null,
            options: optionsPayload,
            validation_rules: field.validation_rules ?? [],
            is_required: field.is_required,
        });
    } catch (e: unknown) {
        logger.error('Field save failed', e);
        toast.error.fromResponse(e);
    } finally {
        const next = new Set(savingIds.value);
        next.delete(field.id);
        savingIds.value = next;
    }
}

async function flushSave(field: AdminFormFieldRow): Promise<void> {
    await persistField(field);
}

function onOptionsBlur(field: AdminFormFieldRow): void {
    field.options = needsOptions(field.type) ? linesToOptions(field._optionsLines ?? '') : null;
    flushSave(field);
}

async function onTypeChange(field: AdminFormFieldRow, newType: string): Promise<void> {
    if (!newType || newType === field.type) return;
    field.type = newType;
    if (needsOptions(newType)) {
        if (!field._optionsLines?.trim()) {
            field._optionsLines = 'Option 1|option_1\nOption 2|option_2';
        }
    } else {
        field._optionsLines = '';
    }
    await flushSave(field);
}

async function addField(type: string): Promise<void> {
    adding.value = true;
    try {
        const res = await FormsService.addField(String(props.formId), {
            label: t('forms.builder.defaultQuestion'),
            type,
            is_required: false,
        });
        const created = res.data as AdminFormFieldRow;
        fields.value.push(decorateField({ ...created }));
    } catch (e: unknown) {
        logger.error('Add field failed', e);
        toast.error.fromResponse(e);
    } finally {
        adding.value = false;
    }
}

async function removeField(field: AdminFormFieldRow): Promise<void> {
    if (!window.confirm(t('forms.builder.deleteConfirm'))) {
        return;
    }
    try {
        await FormsService.deleteField(String(props.formId), String(field.id));
        fields.value = fields.value.filter((f) => f.id !== field.id);
        toast.success.default(t('forms.builder.deleted'));
    } catch (e: unknown) {
        logger.error('Delete field failed', e);
        toast.error.fromResponse(e);
    }
}

let orderBeforeDrag: (string | number)[] = [];

function onDragStart(): void {
    orderBeforeDrag = fields.value.map((f) => f.id);
}

async function onDragEnd(): Promise<void> {
    const after = fields.value.map((f) => f.id);
    if (after.length === orderBeforeDrag.length && after.every((id, i) => id === orderBeforeDrag[i])) {
        return;
    }
    try {
        await FormsService.reorderFields(String(props.formId), { order: after });
    } catch (e: unknown) {
        logger.error('Reorder failed', e);
        toast.error.fromResponse(e);
        await fetchFields();
    }
}

onMounted(() => {
    void fetchFields();
});

defineExpose({ reload: fetchFields });
</script>

<style scoped>
.fb-ghost {
  opacity: 0.55;
  background: hsl(var(--primary) / 0.08);
  border: 2px dashed hsl(var(--primary) / 0.45);
  border-radius: 0.75rem;
}
</style>
