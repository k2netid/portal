<template>
  <div class="console-page pb-20 min-w-0 max-w-full">
    <PageHeader
      :title="$t('publishing.content.list.createNew')"
      borderless
    >
      <template #subtitle>
        <div class="space-y-1">
          <p class="text-sm text-muted-foreground">{{ $t('publishing.content.list.subtitle') }}</p>
          <AutoSaveIndicator
          :status="autoSaveStatus"
          :last-saved="lastSaved || undefined"
        />
        </div>
      </template>
    </PageHeader>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 animate-in fade-in slide-in-from-bottom-4 duration-500">
      <!-- Main Content Area (Center) -->
      <div
        :class="[
          isSidebarOpen ? 'lg:col-span-8' : 'lg:col-span-11',
          'space-y-8 transition-colors duration-300 ease-in-out'
        ]"
      >
        <ConsoleFormCard :padded="false">
          <ContentMain
            v-model="form"
            @save="handleSubmit"
            @toggle-auto-save="handleAutoSaveToggle"
            @cancel="handleCancel"
            @open-builder="isVisualBuilderOpen = true"
          />
        </ConsoleFormCard>
      </div>

      <!-- Sidebar (Right) - Control Tower -->
      <div :class="[isSidebarOpen ? 'lg:col-span-4' : 'lg:col-span-1', 'space-y-6 transition-colors duration-300 ease-in-out']">
        <ActionToolbar 
          :is-sidebar-open="isSidebarOpen"
          :loading="loading"
          :disabled="!isValid"
          @toggle-sidebar="isSidebarOpen = !isSidebarOpen"
          @preview="isPreviewModalOpen = true"
          @save="handleSubmit"
          @cancel="handleCancel"
        />

        <div
          v-show="isSidebarOpen"
          class="space-y-6 animate-in fade-in slide-in-from-right-4"
        >
          <ContentSidebar
            v-model="form"
            v-model:selected-tags="selectedTags"
            :categories="categories"
            :tags="tags"
            :menus="menus"
          />
        </div>
      </div>
    </div>

    <!-- Confirm Dialog -->
    <Dialog
      :open="showConfirmDialog"
      @update:open="showConfirmDialog = $event"
    >
      <DialogContent class="console-dialog-sm">
        <DialogHeader>
          <DialogTitle>{{ $t('common.messages.confirm.title') }}</DialogTitle>
          <DialogDescription>
            {{ $t('publishing.content.messages.unsavedChanges') }}
          </DialogDescription>
        </DialogHeader>
        <DialogFooter>
          <Button
            variant="outline"
            size="sm"
            @click="showConfirmDialog = false"
          >
            {{ $t('publishing.content.form.cancel') }}
          </Button>
          <Button size="sm" @click="confirmCancel">
            {{ $t('common.actions.confirm') }}
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>

    <!-- Slug Conflict Dialog -->
    <Dialog
      :open="!!slugConflict"
      @update:open="slugConflict = null"
    >
      <DialogContent class="console-dialog-md">
        <DialogHeader>
          <DialogTitle class="text-warning flex items-center gap-2">
            <span>⚠️</span> {{ $t('common.messages.slugConflict.title') }}
          </DialogTitle>
          <DialogDescription class="pt-2 space-y-2">
            <p>
              {{ $t('common.messages.slugConflict.message', { slug: form.slug }) }}
            </p>
            <div
              v-if="slugConflict?.details"
              class="text-sm bg-muted p-3 rounded-md"
            >
              <p><strong>{{ $t('common.messages.slugConflict.existingTitle') }}:</strong> {{ slugConflict.details.title }}</p>
              <p><strong>{{ $t('common.messages.slugConflict.status') }}:</strong> {{ slugConflict.details.is_trashed ? $t('common.messages.slugConflict.trashed') : $t('common.messages.slugConflict.active') }}</p>
            </div>
          </DialogDescription>
        </DialogHeader>
        <DialogFooter class="flex-col sm:flex-row gap-2 mt-4">
          <Button
            variant="outline"
            size="sm"
            @click="slugConflict = null"
          >
            {{ $t('common.actions.cancel') }}
          </Button>
          <Button
            v-if="slugConflict?.details?.is_trashed"
            variant="ghost"
            size="sm"
            class="border border-red-800/40 bg-red-800 text-white hover:bg-red-900 hover:text-white"
            @click="resolveConflict('force_delete')"
          >
            {{ $t('common.messages.slugConflict.action.overwrite') }}
          </Button>
          <Button size="sm" @click="resolveConflict('unique')">
            {{ $t('common.messages.slugConflict.action.useUnique') }}
            <span class="ml-1 text-xs opacity-70">({{ slugConflict?.details?.suggested_slug }})</span>
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>

    <!-- Live Device Preview Modal -->
    <Dialog :open="isPreviewModalOpen" @update:open="isPreviewModalOpen = $event">
      <DialogContent class="max-w-6xl w-[95vw] h-[90vh] flex flex-col p-0 overflow-hidden bg-background border border-border rounded-3xl">
        <DialogHeader class="px-6 py-4 border-b border-border flex flex-row items-center justify-between space-y-0 shrink-0 bg-card/60">
          <div class="flex items-center gap-3">
            <DialogTitle class="text-base font-bold">{{ $t('publishing.content.form.preview') }}</DialogTitle>
            <span class="text-xs px-2.5 py-0.5 rounded-full bg-primary/10 text-primary font-mono uppercase">{{ previewDevice }}</span>
          </div>

          <!-- Device Switcher -->
          <div class="flex items-center gap-1.5 p-1 bg-muted rounded-xl border border-border/50">
            <Button
              type="button"
              variant="ghost"
              size="sm"
              class="h-7 px-2.5 rounded-lg text-xs"
              :class="{ 'bg-background shadow-xs font-bold text-foreground': previewDevice === 'desktop' }"
              @click="previewDevice = 'desktop'"
            >
              <Monitor class="w-3.5 h-3.5 mr-1" /> Desktop
            </Button>
            <Button
              type="button"
              variant="ghost"
              size="sm"
              class="h-7 px-2.5 rounded-lg text-xs"
              :class="{ 'bg-background shadow-xs font-bold text-foreground': previewDevice === 'tablet' }"
              @click="previewDevice = 'tablet'"
            >
              <Tablet class="w-3.5 h-3.5 mr-1" /> Tablet
            </Button>
            <Button
              type="button"
              variant="ghost"
              size="sm"
              class="h-7 px-2.5 rounded-lg text-xs"
              :class="{ 'bg-background shadow-xs font-bold text-foreground': previewDevice === 'mobile' }"
              @click="previewDevice = 'mobile'"
            >
              <Smartphone class="w-3.5 h-3.5 mr-1" /> Mobile
            </Button>
          </div>
        </DialogHeader>

        <div class="flex-1 overflow-y-auto p-6 bg-muted/20 flex justify-center">
          <div
            class="transition-all duration-300 bg-background border border-border shadow-xl rounded-2xl p-6 sm:p-10 overflow-x-hidden"
            :style="{
              width: previewDevice === 'mobile' ? '375px' : (previewDevice === 'tablet' ? '768px' : '100%'),
              minHeight: '100%'
            }"
          >
            <h1 class="text-2xl sm:text-4xl font-black mb-6">{{ form.title || 'Judul Halaman' }}</h1>
            <BlockRenderer
              v-if="form.meta?.builder_blocks && form.meta.builder_blocks.length > 0"
              :blocks="form.meta.builder_blocks"
            />
            <!-- eslint-disable vue/no-v-html -- trusted author preview of own draft -->
            <div
              v-else
              class="prose prose-slate dark:prose-invert max-w-none"
              v-html="form.body || '<p class=\'text-muted-foreground italic\'>Belum ada konten.</p>'"
            />
            <!-- eslint-enable vue/no-v-html -->
          </div>
        </div>
      </DialogContent>
    </Dialog>

    <!-- Fullscreen Visual Page Builder Modal -->
    <div
      v-if="isVisualBuilderOpen"
      class="fixed inset-0 z-50 bg-background flex flex-col"
    >
      <Builder
        :initial-data="{ blocks: form.meta?.builder_blocks || [], body: form.body || '', title: form.title || '', slug: form.slug || '' }"
        mode="page"
        @close="isVisualBuilderOpen = false"
        @save="handleBuilderSave"
        @update="handleBuilderUpdate"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
import { PageHeader, ConsoleFormCard } from '@/shared/components/shell';

import { logger } from '@/shared/utils/logger';
import { ref, computed, watch, onMounted, nextTick } from 'vue';
import type { Ref } from 'vue';
import { useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';
import { useHead } from '@unhead/vue';
import { useSystemStore } from '@/modules/Core/System/stores/system';
import api from '@/engine/api/client';

// UI Components
import {
    Button,
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogDescription,
    DialogFooter
} from '@/shared/components/ui';
import ActionToolbar from '@/modules/Publishing/components/content/ActionToolbar.vue';
import AutoSaveIndicator from '@/shared/components/AutoSaveIndicator.vue';
import ContentMain from '@/modules/Publishing/components/content/ContentMain.vue';
import ContentSidebar from '@/modules/Publishing/components/content/ContentSidebar.vue';
import Builder from '@/modules/Layout/components/builder/Builder.vue';
import BlockRenderer from '@/modules/Layout/components/content-renderer/BlockRenderer.vue';
import { Monitor, Tablet, Smartphone } from 'lucide-vue-next';

// Composables & Utils
import { parseResponse, ensureArray } from '@/shared/utils/responseParser';
import { useAutoSave } from '@/shared/composables/useAutoSave';
import { useToast } from '@/shared/composables/useToast';
import { useFormValidation } from '@/shared/composables/useFormValidation';
import { contentSchema } from '@/modules/Publishing/schemas/content';
import type { Menu } from '@/modules/Layout/types/menu';
import type { Category } from '@/modules/Publishing/types/taxonomy';
import type { Tag } from '@/modules/Library/types/taxonomy';
import type { ContentForm } from '@/modules/Publishing/types/content';
import type { BlockInstance } from '@/modules/Layout/types/builder';
import { builderToHtml } from '@/modules/Layout/utils/builderTransformer';

interface ConflictDetails {
    id: string;
    title: string;
    is_trashed: boolean;
    suggested_slug: string;
}

interface SlugConflict {
    details: ConflictDetails;
    originalError: unknown;
}

const { t } = useI18n();
const router = useRouter();
const toast = useToast();
const systemStore = useSystemStore();

// Visual Builder & Preview State
const isVisualBuilderOpen = ref(false);
const isPreviewModalOpen = ref(false);
const previewDevice = ref<'desktop' | 'tablet' | 'mobile'>('desktop');

const handleBuilderUpdate = (payload: { blocks: BlockInstance[] }) => {
  if (!form.value.meta) {
    form.value.meta = {};
  }
  form.value.meta.builder_blocks = payload.blocks;
};

const handleBuilderSave = async (status?: string | null) => {
  isVisualBuilderOpen.value = false;
  if (status && (status === 'draft' || status === 'published')) {
    form.value.status = status;
  }
  const blocks = form.value.meta?.builder_blocks;
  if (Array.isArray(blocks) && blocks.length > 0) {
    const html = builderToHtml(blocks as BlockInstance[]);
    if (html) {
      form.value.body = html;
    }
  }
  if (form.value.title && form.value.title.trim().length > 0) {
    try {
      await handleSubmit(form.value.status);
    } catch (e) {
      logger.error('Auto-submitting content after builder save failed', e);
    }
  }
}

const { validateWithZod, setErrors, clearErrors } = useFormValidation(contentSchema);

const isSidebarOpen = ref(true);
const loading = ref(false);
const categories = ref<Category[]>([]);
const tags = ref<Tag[]>([]);
const menus = ref<Menu[]>([]);
const selectedTags = ref<Tag[]>([]);
const contentId = ref<string | null>(null);

const form = ref<ContentForm>({
    title: '',
    slug: '',
    excerpt: '',
    intro: '',
    body: '',
    featured_image: null,
    featured_image_title: '',
    featured_image_caption: '',
    featured_image_position: 'hero',
    status: 'draft',
    type: 'post',
    category_id: null,
    meta_title: '',
    meta_description: '',
    meta_keywords: '',
    og_image: null,
    menu_item: {
        add_to_menu: false,
        menu_id: '',
        parent_id: null,
        title: ''
    },
    comment_status: true,
    is_featured: false
});

// Auto-generation logic
watch(() => form.value.title, (newTitle) => {
    // Slug
    if (!form.value.slug || form.value.slug === slugify(localStorage.getItem('last_title') || '')) {
         form.value.slug = slugify(newTitle);
    }
    // Meta Title
    if (!form.value.meta_title || form.value.meta_title === localStorage.getItem('last_title')) {
        form.value.meta_title = newTitle;
    }
    localStorage.setItem('last_title', newTitle);
});

useHead({
    title: computed(() => `${t('publishing.content.list.createNew')} | ${systemStore.siteSettings?.site_name || 'JA Jejakawan'}`)
});

watch(() => form.value.excerpt, (newExcerpt) => {
    // Meta Description
    if (!form.value.meta_description || form.value.meta_description === localStorage.getItem('last_excerpt')) {
        form.value.meta_description = newExcerpt;
    }
    localStorage.setItem('last_excerpt', newExcerpt || '');
});

// Helper for slugify
const slugify = (text: string) => {
    if (!text) return '';
    return text.toString().toLowerCase().trim()
        .replace(/\s+/g, '-')
        .replace(/[^\w-\s]+/g, '')
        .replace(/-+/, '-')
        .replace(/^-+/, '')
        .replace(/-+$/, '');
};

const toApiPublishedAt = (value: string | undefined | null): string | null | undefined => {
    if (!value) return value;
    if (/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/.test(value)) {
        const date = new Date(value);
        if (!isNaN(date.getTime())) {
            return date.toISOString();
        }
    }
    return value;
};

/** Tag ids only (omit new tags without id) — avoids 422 on tags.* validation. */
const selectedPersistedTagIds = (): (string)[] =>
    selectedTags.value
        .filter((t: Tag) => t.id != null)
        .map((t: Tag) => t.id);

// Create a computed form that includes tags for auto-save
const formWithTags = computed(() => ({
    ...form.value,
    tags: selectedPersistedTagIds(),
}));

const isValid = computed(() => {
    return !!form.value.title?.trim();
});

// Auto-save setup
const autoSaveEnabled = ref(false);
const autoSaveIntervalMs = ref(30000);

const resolveAutoSaveIntervalMs = (raw: unknown): number => {
    const fallback = 30000;
    const parsed = Number(raw);
    if (!Number.isFinite(parsed)) return fallback;
    const clampedSeconds = Math.min(300, Math.max(5, Math.round(parsed)));
    return clampedSeconds * 1000;
};

const loadAutoSavePreference = async () => {
    try {
        await systemStore.fetchSettingsGroup('general');
        const raw = systemStore.settings['content.autosave_enabled'];
        const enabled = raw === undefined
            ? true
            : raw === true || raw === "1" || raw === '1' || raw === 'true';
        autoSaveEnabled.value = enabled;
        autoSaveIntervalMs.value = resolveAutoSaveIntervalMs(systemStore.settings['content.autosave_interval_seconds']);
    } catch {
        autoSaveEnabled.value = true;
        autoSaveIntervalMs.value = 30000;
    }
};

const {
    lastSaved,
    saveStatus: autoSaveStatus,
    hasChanges,
} = useAutoSave(formWithTags as Ref<Record<string, unknown>>, contentId as Ref<number | null>, {
    interval: computed(() => autoSaveIntervalMs.value),
    enabled: computed(() => autoSaveEnabled.value),
    onSave: (response: unknown) => {
        const res = response as { data?: { id?: string } };
        // Update contentId if new content was created
        if (res?.data?.id && !contentId.value) {
            contentId.value = res.data.id;
        }
    },
    onError: (error: unknown) => {
        // Handle slug conflict from autosave
        if (error && typeof error === 'object' && 'response' in error) {
            const err = error as { response?: { status?: number, data?: { code?: string, data?: { conflict: ConflictDetails } } } };
            if (err.response?.status === 409 && err.response?.data?.code === 'SLUG_CONFLICT') {
                const conflictData = err.response?.data?.data;
                if (conflictData) {
                    slugConflict.value = {
                        details: conflictData.conflict,
                        originalError: error
                    };
                }
            }
        }
    },
    shouldSave: (formData: Record<string, unknown>) => {
        // Always save if we already have an ID (updates)
        if (contentId.value) return true;

        // For new content, require Title AND (Body OR Blocks OR Excerpt)
        // This prevents creating empty drafts just by typing a title
        const hasTitle = formData.title && typeof formData.title === 'string' && formData.title.trim().length > 0;
        
        // Check content substance
        const hasBody = formData.body && typeof formData.body === 'string' && formData.body.trim().length > 0;
        const hasBlocks = formData.blocks && Array.isArray(formData.blocks) && formData.blocks.length > 0;
        const hasExcerpt = formData.excerpt && typeof formData.excerpt === 'string' && formData.excerpt.trim().length > 0;

        return !!hasTitle && (!!hasBody || !!hasBlocks || !!hasExcerpt);
    }
});

const handleAutoSaveToggle = (isEnabled: boolean) => {
    autoSaveEnabled.value = isEnabled;
};

const fetchCategories = async () => {
    try {
        const response = await api.get('/manage/library/categories', { params: { per_page: 100 } });
        const { data } = parseResponse(response);
        categories.value = ensureArray(data);
    } catch (error: unknown) {
        logger.error('Failed to fetch categories:', error);
        categories.value = [];
    }
};

const fetchTags = async (query = '') => {
    try {
        const params: Record<string, unknown> = { per_page: 50 };
        if (query) {
            params.search = query;
        }
        const response = await api.get('/manage/library/tags', { params });
        const { data } = parseResponse(response);
        tags.value = ensureArray(data);
    } catch (error: unknown) {
        logger.error('Failed to fetch tags:', error);
    }
};

const fetchMenus = async () => {
    try {
        const response = await api.get('/manage/layout/menus');
        const { data } = parseResponse(response);
        menus.value = ensureArray(data);
    } catch (error: unknown) {
        logger.error('Failed to fetch menus:', error);
    }
};


const handleSubmit = async (status: string | null = null) => {
    // Update status if provided
    if (status && (status === 'draft' || status === 'published' || status === 'archived' || status === 'scheduled')) {
        form.value.status = status;
    }

     
    if (!validateWithZod(form.value)) return;

    loading.value = true;
    clearErrors();
    try {
        // Auto-fill SEO fields if empty
        if (!form.value.meta_title && form.value.title) {
            form.value.meta_title = form.value.title;
        }
        if (!form.value.meta_description && form.value.excerpt) {
            form.value.meta_description = form.value.excerpt;
        }
        if (!form.value.meta_keywords && selectedTags.value.length > 0) {
            form.value.meta_keywords = selectedTags.value.map((t: Tag) => t.name).join(', ');
        }

        // Auto-select first category if none selected and categories are available
        if (!form.value.category_id && categories.value.length > 0) {
            const firstCategory = categories.value[0];
            if (firstCategory) form.value.category_id = firstCategory.id;
        }
        
        // Prepare tags: send ids for existing, names for new
        const tagIds = selectedPersistedTagIds();
        const newTags = selectedTags.value.filter((t: Tag) => t.id == null).map((t: Tag) => t.name);
        
        const payload = {
            ...form.value,
            published_at: toApiPublishedAt(form.value.published_at),
            tags: tagIds,
            new_tags: newTags,
        };

        // If content was auto-saved, use update endpoint
        const endpoint = contentId.value
            ? `/manage/publishing/contents/${contentId.value}`
            : '/manage/publishing/contents';
        const method = contentId.value ? 'put' : 'post';

        const response = await (method === 'put'
            ? api.put(endpoint, payload)
            : api.post(endpoint, payload));
        
        if (response.data?.updated_at) {
            lastSaved.value = new Date(response.data.updated_at);
        }
        
        toast.success.create(t('publishing.content.title_singular'));
        
        // Only redirect if not saving from within an embedded view
        if (status === null) {
        router.push({ name: 'contents.index' });
        } else {
             // If staying, update contentId for future saves (though auto-save handles this too)
             if (response.data?.id && !contentId.value) {
                 contentId.value = response.data.id;
             }
        }
    } catch (error: unknown) {
        if (error && typeof error === 'object' && 'response' in error) {
            const err = error as { response?: { status?: number, data?: { errors?: Record<string, string[]>, code?: string, data?: { conflict: ConflictDetails } } } };
            if (err.response?.status === 422 && err.response.data) {
                setErrors(err.response.data.errors || {});
            } else if (err.response?.status === 409 && err.response?.data?.code === 'SLUG_CONFLICT') {
                // Handle slug conflict
                slugConflict.value = {
                    details: err.response?.data?.data?.conflict as ConflictDetails,
                    originalError: error
                };
            } else {
                logger.error('Failed to create content:', error);
                toast.error.fromResponse(error);
            }
        } else {
            logger.error('Failed to create content:', error);
            toast.error.fromResponse(error);
        }
    } finally {
        loading.value = false;
    }
};

const showConfirmDialog = ref(false);

const handleCancel = () => {
    if (hasChanges.value) {
        showConfirmDialog.value = true;
    } else {
        router.push({ name: 'contents.index' });
    }
};

const confirmCancel = () => {
    showConfirmDialog.value = false;
    router.push({ name: 'contents.index' });
};

// Slug Conflict Handling
const slugConflict = ref<SlugConflict | null>(null);

const resolveConflict = async (action: 'unique' | 'force_delete') => {
    if (!slugConflict.value) return;

    const details = slugConflict.value.details;
    const suggested = details?.suggested_slug;
    const conflictId = details?.id;

    if (action === 'unique' && suggested) {
        // Option 1: Use suggested unique slug
        form.value.slug = suggested;
        slugConflict.value = null;
        // Retry submission
        await handleSubmit(form.value.status);
    } else if (action === 'force_delete' && conflictId) {
        // Option 2: Force delete the conflicting item (only if trashed)
        try {
            loading.value = true;
            await api.delete(`/manage/publishing/contents/${conflictId}/force-delete`);
            slugConflict.value = null;
            toast.success.action(t('publishing.content.messages.conflictResolved'));
            // Retry submission
            await handleSubmit(form.value.status);
        } catch (error: unknown) {
            logger.error('Failed to force delete conflicting item:', error);
            toast.error.action({ message: t('publishing.content.messages.conflictResolveFailed') });
        } finally {
            loading.value = false;
        }
    }
};

onMounted(async () => {
    await loadAutoSavePreference();
    fetchCategories();
    fetchTags();
    fetchMenus();
    
    // Wait for any child components or watchers to stabilize
    await nextTick();
    hasChanges.value = false;
});
</script>
