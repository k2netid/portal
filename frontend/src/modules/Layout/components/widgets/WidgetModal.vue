<template>
  <Dialog
    :open="true"
    @update:open="$emit('close')"
  >
    <DialogContent class="console-dialog-md sm:max-w-lg">
      <DialogHeader>
        <DialogTitle>
          {{ widget ? $t('layout.widgets.modals.widget.titleEdit') : $t('layout.widgets.modals.widget.titleCreate') }}
        </DialogTitle>
      </DialogHeader>

      <form
        class="space-y-4 py-3"
        @submit.prevent="handleSubmit"
      >
        <!-- Type Selection -->
        <div class="space-y-1.5">
          <Label>{{ $t('layout.widgets.modals.widget.type') }} <span class="text-destructive">*</span></Label>
          <Select
            v-model="form.type"
            @update:model-value="handleTypeChange"
          >
            <SelectTrigger>
              <SelectValue :placeholder="$t('layout.widgets.modals.widget.type')" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="search">
                {{ $t('layout.widgets.types.search') }}
              </SelectItem>
              <SelectItem value="categories">
                {{ $t('layout.widgets.types.categories') }}
              </SelectItem>
              <SelectItem value="recent_posts">
                {{ $t('layout.widgets.types.recent_posts') }}
              </SelectItem>
              <SelectItem value="newsletter">
                {{ $t('layout.widgets.types.newsletter') }}
              </SelectItem>
              <SelectItem value="social_share">
                {{ $t('layout.widgets.types.social_share') }}
              </SelectItem>
              <SelectItem value="text">
                {{ $t('layout.widgets.types.text') }}
              </SelectItem>
              <SelectItem value="html">
                {{ $t('layout.widgets.types.html') }}
              </SelectItem>
              <SelectItem value="custom">
                {{ $t('layout.widgets.types.custom') }}
              </SelectItem>
            </SelectContent>
          </Select>
          <span
            v-if="errorMessage('type')"
            class="text-xs text-destructive"
          >{{ errorMessage('type') }}</span>
        </div>

        <!-- Title -->
        <div class="space-y-1.5">
          <Label>{{ $t('layout.widgets.modals.widget.title') }} <span class="text-destructive">*</span></Label>
          <Input
            v-model="form.title"
            type="text"
            required
            :placeholder="titlePlaceholder"
          />
          <span
            v-if="errorMessage('title')"
            class="text-xs text-destructive"
          >{{ errorMessage('title') }}</span>
        </div>

        <!-- Location with quick preset pills -->
        <div class="space-y-1.5">
          <div class="flex items-center justify-between">
            <Label>{{ $t('layout.widgets.modals.widget.location') }} <span class="text-destructive">*</span></Label>
            <span class="text-[11px] text-muted-foreground">{{ $t('layout.widgets.modals.widget.locationHint') }}</span>
          </div>
          <Input
            v-model="form.location"
            type="text"
            required
            placeholder="sidebar"
          />
          <!-- Quick presets -->
          <div class="flex items-center gap-1.5 flex-wrap pt-1">
            <span class="text-[11px] text-muted-foreground mr-1">{{ $t('layout.widgets.modals.widget.preset') }}</span>
            <button
              v-for="loc in ['sidebar', 'footer', 'footer_col_1', 'footer_col_2']"
              :key="loc"
              type="button"
              class="px-2 py-0.5 rounded text-[11px] font-mono border transition-colors"
              :class="form.location === loc ? 'bg-primary text-primary-foreground border-primary font-bold' : 'bg-muted/50 border-border text-muted-foreground hover:bg-muted'"
              @click="form.location = loc"
            >
              {{ loc }}
            </button>
          </div>
          <span
            v-if="errorMessage('location')"
            class="text-xs text-destructive"
          >{{ errorMessage('location') }}</span>
        </div>

        <!-- Info Card for Universal Dynamic Widgets -->
        <div
          v-if="isUniversalDynamicWidget"
          class="rounded-xl border border-primary/20 bg-primary/5 p-3.5 text-xs space-y-1"
        >
          <div class="font-semibold text-foreground flex items-center gap-1.5">
            <Sparkles class="w-3.5 h-3.5 text-primary" />
            <span>{{ widgetTypeInfo.title }}</span>
          </div>
          <p class="text-muted-foreground leading-relaxed">
            {{ widgetTypeInfo.description }}
          </p>
        </div>

        <!-- Content (only for text, html, custom) -->
        <div
          v-else
          class="space-y-1.5"
        >
          <Label>{{ $t('layout.widgets.modals.widget.content') }}</Label>
          <Textarea
            v-model="form.content"
            :rows="4"
            :placeholder="form.type === 'html' ? $t('layout.widgets.modals.widget.contentPlaceholderHtml') : $t('layout.widgets.modals.widget.contentPlaceholderText')"
          />
          <span
            v-if="errorMessage('content')"
            class="text-xs text-destructive"
          >{{ errorMessage('content') }}</span>
        </div>

        <!-- Active Toggle -->
        <div class="flex items-center space-x-2 pt-1">
          <Checkbox
            id="is_active"
            v-model:checked="form.is_active"
          />
          <Label
            for="is_active"
            class="text-sm font-normal cursor-pointer"
          >
            {{ $t('layout.widgets.modals.widget.active') }}
          </Label>
        </div>
      </form>

      <DialogFooter class="pt-2">
        <Button
          variant="outline"
          size="sm"
          type="button"
          @click="$emit('close')"
        >
          {{ $t('common.actions.cancel') }}
        </Button>
        <Button
          :disabled="isSubmitting || !isValid || (widget && !isDirty)"
          @click="handleSubmit"
        >
          <Loader2
            v-if="isSubmitting"
            data-icon="inline-start"
            class="size-4 shrink-0 animate-spin"
          />
          {{ isSubmitting ? $t('layout.widgets.modals.widget.saving') : (widget ? $t('common.actions.update') : $t('common.actions.create')) }}
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { onMounted, ref, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '@/engine/api/client';
import { 
    Dialog, 
    DialogContent, 
    DialogHeader, 
    DialogTitle, 
    DialogFooter, 
    Button, 
    Input, 
    Label, 
    Textarea, 
    Checkbox, 
    Select, 
    SelectTrigger, 
    SelectValue, 
    SelectContent, 
    SelectItem 
} from '@/shared/components/ui';
import {
  Loader2,
  Sparkles,
} from 'lucide-vue-next';
import { useToast } from '@/shared/composables/useToast';
import { useFormValidation } from '@/shared/composables/useFormValidation';
import { widgetSchema } from '@/shared/schemas/common';

type WidgetType = 'html' | 'text' | 'recent_posts' | 'categories' | 'search' | 'newsletter' | 'social_share' | 'custom';

interface Widget {
    id: string;
    title: string;
    type: string;
    location?: string;
    content?: string;
    is_active?: boolean | number;
}

interface WidgetForm {
    title: string;
    type: WidgetType;
    location: string;
    content: string;
    is_active: boolean;
}

const props = withDefaults(defineProps<{
    widget?: Widget | null;
}>(), {
    widget: null
});

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'saved'): void;
}>();

const { t } = useI18n();
const toast = useToast();
const isSubmitting = ref(false);

const { errors, validateWithZod, setErrors } = useFormValidation(widgetSchema);

const errorMessage = (field: string): string => {
    const err = errors.value?.[field];
    if (Array.isArray(err)) return err[0] || '';
    if (typeof err === 'string') return err;
    return '';
};

const form = ref<WidgetForm>({
    title: '',
    type: 'search',
    location: 'sidebar',
    content: '',
    is_active: true
});

const initialForm = ref<WidgetForm | null>(null);

const isUniversalDynamicWidget = computed(() => {
    return ['search', 'categories', 'recent_posts', 'newsletter', 'social_share'].includes(form.value.type);
});

const widgetTypeInfo = computed(() => {
    switch (form.value.type) {
        case 'search':
            return {
                title: t('layout.widgets.modals.widget.info.searchTitle'),
                description: t('layout.widgets.modals.widget.info.searchDesc')
            };
        case 'categories':
            return {
                title: t('layout.widgets.modals.widget.info.categoriesTitle'),
                description: t('layout.widgets.modals.widget.info.categoriesDesc')
            };
        case 'recent_posts':
            return {
                title: t('layout.widgets.modals.widget.info.recentPostsTitle'),
                description: t('layout.widgets.modals.widget.info.recentPostsDesc')
            };
        case 'newsletter':
            return {
                title: t('layout.widgets.modals.widget.info.newsletterTitle'),
                description: t('layout.widgets.modals.widget.info.newsletterDesc')
            };
        case 'social_share':
            return {
                title: t('layout.widgets.modals.widget.info.socialShareTitle'),
                description: t('layout.widgets.modals.widget.info.socialShareDesc')
            };
        default:
            return { title: '', description: '' };
    }
});

const titlePlaceholder = computed(() => {
    switch (form.value.type) {
        case 'search': return t('layout.widgets.modals.widget.titlePlaceholders.search');
        case 'categories': return t('layout.widgets.modals.widget.titlePlaceholders.categories');
        case 'recent_posts': return t('layout.widgets.modals.widget.titlePlaceholders.recent_posts');
        case 'newsletter': return t('layout.widgets.modals.widget.titlePlaceholders.newsletter');
        case 'social_share': return t('layout.widgets.modals.widget.titlePlaceholders.social_share');
        case 'html': return t('layout.widgets.modals.widget.titlePlaceholders.html');
        case 'text': return t('layout.widgets.modals.widget.titlePlaceholders.text');
        case 'custom': return t('layout.widgets.modals.widget.titlePlaceholders.custom');
        default: return t('layout.widgets.modals.widget.titlePlaceholders.default');
    }
});

const handleTypeChange = (newType: unknown) => {
    const val = String(newType) as WidgetType;
    form.value.type = val;
    // Auto-fill title if empty
    if (!form.value.title.trim()) {
        form.value.title = titlePlaceholder.value;
    }
};

const isDirty = computed(() => {
    if (!props.widget || !initialForm.value) return true;
    return JSON.stringify(form.value) !== JSON.stringify(initialForm.value);
});

const isValid = computed(() => {
    return !!form.value.title?.trim() && !!form.value.type && !!form.value.location?.trim();
});

const loadWidget = () => {
    if (props.widget) {
        form.value = { 
            title: props.widget.title || '',
            type: (props.widget.type as WidgetType) || 'search',
            location: props.widget.location || 'sidebar',
            content: props.widget.content || '',
            is_active: props.widget.is_active !== false 
        };
        initialForm.value = JSON.parse(JSON.stringify(form.value));
    } else {
        // Defaults for new widget
        form.value.title = titlePlaceholder.value;
    }
};

const handleSubmit = async () => {
    if (!validateWithZod(form.value)) return;
    isSubmitting.value = true;

    try {
        if (props.widget) {
            await api.put(`/manage/layout/widgets/${props.widget.id}`, form.value);
            toast.success.update(t('layout.widgets.title'));
        } else {
            await api.post('/manage/layout/widgets', form.value);
            toast.success.create(t('layout.widgets.title'));
        }
        emit('saved');
    } catch (error: unknown) {
        const resp = (error as { response?: { status?: number, data?: { errors?: Record<string, string[]> } } }).response;
        if (resp?.status === 422) {
            setErrors(resp.data?.errors || {});
        } else {
            toast.error.fromResponse(error);
        }
    } finally {
        isSubmitting.value = false;
    }
};

onMounted(() => {
    loadWidget();
});
</script>
