<template>
  <div class="console-page pb-20 min-w-0 max-w-full">
    <PageHeader
      :title="$t('publishing.content.form.editTitle')"
      borderless
    >
      <template #subtitle>
        <p class="text-sm text-muted-foreground mb-2">{{ $t('publishing.content.list.subtitle') }}</p>
        <div
          v-if="lockStatus"
          class="flex flex-wrap items-center gap-2"
        >
          <Badge
            variant="outline"
            :class="lockStatus.is_locked ? 'bg-warning/10 text-warning border-warning/20' : 'bg-success/10 text-success border-success/20'"
            class="gap-1.5 h-5 text-[10px]"
          >
            <Lock
              v-if="lockStatus.is_locked"
              class="w-3 h-3"
            />
            <Unlock
              v-else
              class="w-3 h-3"
            />
            {{ lockStatus.is_locked ? $t('publishing.content.form.locked') : $t('publishing.content.form.unlocked') }}
          </Badge>
          <span
            v-if="lockStatus.is_locked && lockStatus.locked_by"
            class="text-xs text-muted-foreground flex items-center gap-1.5"
          >
            <div class="w-1 h-1 rounded-full bg-muted-foreground/30" />
            {{ $t('publishing.content.form.lockedBy', { name: lockStatus.locked_by.name }) }}
          </span>
          <Button
            v-if="lockStatus.is_locked && lockStatus.can_unlock"
            variant="link"
            size="sm"
            class="h-auto p-0 text-xs text-primary hover:text-primary/80"
            @click="handleUnlock"
          >
            {{ $t('publishing.content.form.unlock') }}
          </Button>
          <AutoSaveIndicator
            :status="autoSaveStatus"
            :last-saved="lastSaved || undefined"
          />
        </div>
      </template>
      <template #actions>
        <div class="flex items-center gap-2">
          <Button
            variant="outline"
            size="sm"
            class="gap-1.5 text-xs font-medium"
            @click="router.push({ name: 'contents.revisions', params: { id } })"
          >
            <History class="w-3.5 h-3.5" />
            {{ $t('publishing.content.list.revisions') }}
          </Button>
          <template v-if="form.status === 'pending' && authStore.hasPermission('approve content')">
            <Button
              variant="default"
              size="sm"
              class="bg-emerald-600 hover:bg-emerald-700 text-white gap-1.5 text-xs font-medium"
              @click="handleApprove"
            >
              <CheckCircle2 class="w-3.5 h-3.5" />
              {{ $t('publishing.content.actions.approve') }}
            </Button>
            <Button
              variant="destructive"
              size="sm"
              class="gap-1.5 text-xs font-medium"
              @click="handleReject"
            >
              <XCircle class="w-3.5 h-3.5" />
              {{ $t('publishing.content.actions.reject') }}
            </Button>
          </template>
        </div>
      </template>
    </PageHeader>

    <!-- Pending Review Notice -->
    <Alert
      v-if="form.status === 'pending'"
      class="mb-6 bg-warning/10 border-warning/20 text-warning"
    >
      <Clock3 class="w-4 h-4" />
      <AlertTitle>{{ $t('publishing.content.status.pending') }}</AlertTitle>
      <AlertDescription>
        {{ $t('publishing.content.messages.pendingNotice') }}
      </AlertDescription>
    </Alert>

    <div
      v-if="loading && !form.title"
      class="flex flex-col items-center justify-center py-20 text-muted-foreground space-y-4"
    >
      <Loader2 class="w-10 h-10 animate-spin opacity-20" />
      <p class="text-sm font-medium animate-pulse">
        {{ $t('publishing.content.form.loading') }}
      </p>
    </div>

    <div
      v-else
      class="grid grid-cols-1 lg:grid-cols-12 gap-8 animate-in fade-in slide-in-from-bottom-4 duration-500"
    >
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
          :disabled="!isDirty"
          :is-edit="true"
          @toggle-sidebar="isSidebarOpen = !isSidebarOpen"
          @preview="handlePreview"
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
            @search-tags="fetchTags"
          >
            <!-- Actions Slot -->
            <template #actions>
              <Button
                variant="outline"
                size="sm"
                class="w-full"
                :disabled="loading || aiDraftLoading"
                @click="handleAiDraft"
              >
                {{ aiDraftLoading ? t('ai.publishing.draftGenerating') : t('ai.publishing.draft') }}
              </Button>
              <Button
                variant="outline"
                size="sm"
                class="w-full"
                :disabled="loading || aiTaxonomyLoading || !form.title.trim()"
                @click="handleAiTaxonomy"
              >
                {{ aiTaxonomyLoading ? t('ai.publishing.taxonomySuggesting') : t('ai.publishing.taxonomy') }}
              </Button>
              <Button
                variant="outline"
                size="sm"
                class="w-full"
                @click="handlePreview"
              >
                {{ $t('publishing.content.form.preview') }}
              </Button>
            </template>
          </ContentSidebar>
        </div>
      </div>
    </div>

    <!-- Preview Modal -->
    <ContentPreviewModal
      :show="showPreviewModal"
      :content="previewContent"
      :can-publish="form.status !== 'published'"
      @close="showPreviewModal = false"
      @publish="handlePublishFromPreview"
    />

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

    <!-- Fullscreen Visual Page Builder Modal -->
    <div
      v-if="isVisualBuilderOpen"
      class="fixed inset-0 z-50 bg-background flex flex-col"
    >
      <Builder
        :initial-data="{ blocks: form.meta?.builder_blocks || [], body: form.body || '', title: form.title || '', slug: form.slug || '' }"
        :content-id="id"
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

import { AiService } from '@/modules/Publishing/shims/ai/services/aiService';
import { logger } from '@/shared/utils/logger';
import { ref, onMounted, onUnmounted, computed, watch, nextTick } from 'vue';
import { useRouteBreadcrumbLabel } from '@/shared/composables/useRouteBreadcrumbLabel';
import type { Ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';
import { useHead } from '@unhead/vue';
import { usePublishingStore } from '@/modules/Publishing/stores/publishing';
import { useSystemStore } from '@/modules/Core/System/stores/system';
import api from '@/engine/api/client';
import { useAuthStore } from '@/modules/Core/System/stores/auth';

import {
  CheckCircle2,
  Clock3,
  History,
  Loader2,
  Lock,
  Unlock,
  XCircle,
} from 'lucide-vue-next';

// UI Components

import {
    Button,
    Badge,
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogDescription,
    DialogFooter,
    Alert,
    AlertTitle,
    AlertDescription
} from '@/shared/components/ui';
import ActionToolbar from '@/modules/Publishing/components/content/ActionToolbar.vue';
import AutoSaveIndicator from '@/shared/components/AutoSaveIndicator.vue';
import ContentPreviewModal from '@/modules/Core/System/components/console/ContentPreviewModal.vue';
import ContentMain from '@/modules/Publishing/components/content/ContentMain.vue';
import ContentSidebar from '@/modules/Publishing/components/content/ContentSidebar.vue';
import Builder from '@/modules/Layout/components/builder/Builder.vue';

// Composables & Utils
import { parseSingleResponse, parseResponse, ensureArray, getResponseList } from '@/shared/utils/responseParser';
import { useAutoSave } from '@/shared/composables/useAutoSave';
import { useConfirm } from '@/shared/composables/useConfirm';
import { useToast } from '@/shared/composables/useToast';
import { useFormValidation } from '@/shared/composables/useFormValidation';
import { contentSchema } from '@/modules/Publishing/schemas/content';
import type { Menu } from '@/modules/Layout/types/menu';
import type { Category } from '@/modules/Publishing/types/taxonomy';
import type { Tag } from '@/modules/Library/types/taxonomy';
import type { Content, ContentForm } from '@/modules/Publishing/types/content';
import type { BlockInstance } from '@/modules/Layout/types/builder';

interface LockStatus {
    is_locked: boolean;
    locked_by?: {
        id: string;
        name: string;
        email: string;
    };
    locked_at?: string;
    can_unlock?: boolean;
}

const { t } = useI18n();
const router = useRouter();
const route = useRoute();
const id = String(route.params.id || '');
const toast = useToast();
const { confirm } = useConfirm();
const publishingStore = usePublishingStore();
const systemStore = useSystemStore();
const authStore = useAuthStore();

// Approval Actions
const handleApprove = async () => {
  const confirmed = await confirm({
    title: t('publishing.content.actions.approve'),
    message: t('publishing.content.messages.approveConfirm', 'Are you sure you want to approve and publish this content?'),
    confirmText: t('publishing.content.actions.approve'),
    variant: 'success'
  });
  if (!confirmed) return;

  try {
    await api.put(`/manage/publishing/contents/${id}/approve`);
    form.value.status = 'published';
    toast.success.default(t('publishing.content.messages.approvedSuccess', 'Content approved and published successfully!'));
  } catch (error) {
    logger.error('Failed to approve content:', error);
    toast.error.default(t('publishing.content.messages.approveFailed', 'Failed to approve content'));
  }
};

const handleReject = async () => {
  const confirmed = await confirm({
    title: t('publishing.content.actions.reject'),
    message: t('publishing.content.messages.rejectConfirm', 'Are you sure you want to reject this content? It will be moved to draft.'),
    confirmText: t('publishing.content.actions.reject'),
    variant: 'danger'
  });
  if (!confirmed) return;

  try {
    await api.put(`/manage/publishing/contents/${id}/reject`);
    form.value.status = 'draft';
    toast.success.default(t('publishing.content.messages.rejectedSuccess', 'Content rejected and moved to draft.'));
  } catch (error) {
    logger.error('Failed to reject content:', error);
    toast.error.default(t('publishing.content.messages.rejectFailed', 'Failed to reject content'));
  }
};

// Visual Builder State
const isVisualBuilderOpen = ref(false);

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
  if (contentId) {
    try {
      await handleSubmit(form.value.status);
    } catch (e) {
      logger.error('Auto-submitting content after builder save failed', e);
    }
  }
  toast.success.default(t('publishing.content.builder.savedSuccess', 'Blok visual builder berhasil disinkronkan ke halaman!'));
};

const isSidebarOpen = ref(true);
const contentId = route.params.id as string;
const { validateWithZod, setErrors, clearErrors } = useFormValidation(contentSchema);

const loading = ref(false);
const aiDraftLoading = ref(false);
const aiTaxonomyLoading = ref(false);
const categories = ref<Category[]>([]);
const tags = ref<Tag[]>([]);
const menus = ref<Menu[]>([]);
const selectedTags = ref<Tag[]>([]);
const lockStatus = ref<LockStatus | null>(null);
const lockInterval = ref<ReturnType<typeof setInterval> | null>(null);
const initialForm = ref<string | null>(null);

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

useRouteBreadcrumbLabel(computed(() => form.value.title));

useHead({
    title: computed(() => `${form.value.title || t('publishing.content.form.editTitle')} | ${systemStore.siteSettings?.site_name || 'JA Jejakawan'}`)
});

const isDirty = computed(() => {
    if (!initialForm.value) return false;
    const currentForm = {
        ...form.value,
        tags: selectedPersistedTagIds(),
    };
    return JSON.stringify(currentForm) !== initialForm.value;
});

// Auto-generation logic (Similar to Create but cautious about overwriting existing data)
watch(() => form.value.title, (newTitle) => {
    // Only auto-update if slug is empty
    if (!form.value.slug) {
         form.value.slug = slugify(newTitle);
    }
    if (!form.value.meta_title) {
        form.value.meta_title = newTitle;
    }
});

watch(() => form.value.excerpt, (newExcerpt) => {
    if (!form.value.meta_description) {
        form.value.meta_description = newExcerpt;
    }
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

// Auto-save setup
const autoSaveEnabled = ref(false);
const autoSaveDefaultEnabled = ref(true);
const autoSaveIntervalMs = ref(30000);

const resolveAutoSaveIntervalMs = (raw: unknown): number => {
    const fallback = 30000;
    const parsed = Number(raw);
    if (!Number.isFinite(parsed)) return fallback;
    const clampedSeconds = Math.min(300, Math.max(5, Math.round(parsed)));
    return clampedSeconds * 1000;
};
const {
    lastSaved,
    saveStatus: autoSaveStatus,
    startAutoSave,
} = useAutoSave(formWithTags as Ref<Record<string, unknown>>, contentId as string, {
    interval: computed(() => autoSaveIntervalMs.value),
    enabled: computed(() => autoSaveEnabled.value),
});

const handleAutoSaveToggle = (isEnabled: boolean) => {
    autoSaveEnabled.value = isEnabled;
};

const loadAutoSavePreference = async () => {
    try {
        await publishingStore.fetchSettingsGroup('general');
        const raw = publishingStore.settings['content.autosave_enabled'];
        autoSaveDefaultEnabled.value = raw === undefined
            ? true
            : raw === true || raw === "1" || raw === '1' || raw === 'true';
        autoSaveIntervalMs.value = resolveAutoSaveIntervalMs(publishingStore.settings['content.autosave_interval_seconds']);
    } catch {
        autoSaveDefaultEnabled.value = true;
        autoSaveIntervalMs.value = 30000;
    }
};

const formatDateTimeLocal = (dateString: string | undefined | null): string | undefined => {
    if (!dateString) return undefined;
    const date = new Date(dateString);
    if (isNaN(date.getTime())) return undefined;
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');
    return `${year}-${month}-${day}T${hours}:${minutes}`;
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

const fetchContent = async () => {
    loading.value = true;
    try {
        const response = await api.get(`/manage/publishing/contents/${contentId}`);
        const content = parseSingleResponse<Content>(response);
        
        if (content) {
            form.value = {
                title: content.title || '',
                slug: content.slug || '',
                excerpt: content.excerpt || '',
                intro: content.intro || '',
                body: content.body || '',
                featured_image: content.featured_image || null,
                featured_image_title: content.featured_image_title || '',
                featured_image_caption: content.featured_image_caption || '',
                featured_image_position: content.featured_image_position || 'hero',
                status: content.status || 'draft',
                type: content.type || 'post',
                category_id: content.category_id || null,
                published_at: formatDateTimeLocal(content.published_at),
                meta_title: content.meta_title || '',
                meta_description: content.meta_description || '',
                meta_keywords: content.meta_keywords || '',
                og_image: content.og_image || null,
                meta: content.meta || {},
                menu_item: {
                    add_to_menu: false,
                    menu_id: '',
                    parent_id: null,
                    title: ''
                },
                comment_status: content.comment_status !== undefined ? !!content.comment_status : true,
                is_featured: !!content.is_featured
            };

            // Handle menu items
            if (content.menu_items && content.menu_items.length > 0) {
                const menuItem = content.menu_items[0];
                if (menuItem) {
                    form.value.menu_item = {
                        add_to_menu: true,
                        menu_id: String(menuItem.menu_id),
                        parent_id: menuItem.parent_id,
                        title: menuItem.title || ''
                    };
                }
            }

            // Set selected tags
            if (content.tags && Array.isArray(content.tags)) {
                selectedTags.value = content.tags as Tag[];
            }
        }
        
        // Wait for watchers to finish auto-generating fields (slug, meta_title, etc.)
        await nextTick();

        // Save initial state for dirty checking (including tags)
        initialForm.value = JSON.stringify({
            ...form.value,
            tags: selectedPersistedTagIds(),
        });
        
        // Enable auto-save after content is loaded (based on Jejakawan settings)
        autoSaveEnabled.value = autoSaveDefaultEnabled.value;
        if (autoSaveEnabled.value) {
            startAutoSave();
        }
        
        // Lock content on edit
        await lockContent();
    } catch (error: unknown) {
        logger.error('Failed to fetch content:', error);
        toast.error.load(error);
        router.push({ name: 'contents.index' });
    } finally {
        loading.value = false;
    }
};

const lockContent = async () => {
    try {
        const response = await api.post(`/manage/publishing/contents/${contentId}/lock`);
        const data = parseSingleResponse<LockStatus>(response);
        if (data && typeof data === 'object' && 'is_locked' in data) {
            lockStatus.value = data;
        } else {
            lockStatus.value = null;
        }
        
        // Refresh lock status every 30 seconds
        if (lockInterval.value) {
            clearInterval(lockInterval.value);
        }
        lockInterval.value = setInterval(checkLockStatus, 30000);
    } catch (error: unknown) {
        logger.error('Failed to lock content:', error);
    }
};

const checkLockStatus = async () => {
    try {
        const response = await api.get(`/manage/publishing/contents/${contentId}/lock-status`);
        const data = parseSingleResponse<LockStatus>(response);
        if (data) {
            lockStatus.value = data;
        }
    } catch (error: unknown) {
        logger.error('Failed to check lock status:', error);
    }
};

const handleUnlock = async () => {
    try {
        await api.post(`/manage/publishing/contents/${contentId}/unlock`);
        lockStatus.value = { is_locked: false, can_unlock: true };
        if (lockInterval.value) {
            clearInterval(lockInterval.value);
        }
    } catch (error: unknown) {
        logger.error('Failed to unlock content:', error);
        toast.error.fromResponse(error);
    }
};

const showPreviewModal = ref(false);

interface PublishingAiDraft {
    title: string;
    excerpt: string;
    intro: string;
    body: string;
}

async function handleAiDraft(): Promise<void> {
    const topic = form.value.title.trim() || window.prompt(t('ai.publishing.draftTopicPrompt'))?.trim();
    if (!topic) {
        return;
    }

    aiDraftLoading.value = true;
    try {
        const category = categories.value.find((c) => c.id === form.value.category_id);
        const tagNames = selectedTags.value.map((t) => t.name).filter(Boolean);
        const response = await AiService.draftPublishing({
            topic,
            content_type: form.value.type,
            category_name: category?.name ?? '',
            tags: tagNames,
            tone: 'professional',
        });
        const draft = parseSingleResponse<PublishingAiDraft>(response);
        if (!draft) {
            toast.error.default(t('ai.publishing.draftEmpty'));
            return;
        }
        if (!form.value.title.trim()) {
            form.value.title = draft.title;
        }
        form.value.excerpt = draft.excerpt || form.value.excerpt;
        form.value.intro = draft.intro || form.value.intro;
        form.value.body = draft.body || form.value.body;
        toast.success.default(t('ai.publishing.draftApplied'));
    } catch (e: unknown) {
        toast.error.action(e);
    } finally {
        aiDraftLoading.value = false;
    }
}

interface TaxonomySuggestion {
    category_name: string;
    tags: string[];
}

async function handleAiTaxonomy(): Promise<void> {
    if (!form.value.title.trim()) {
        return;
    }

    aiTaxonomyLoading.value = true;
    try {
        const response = await AiService.suggestTaxonomy({
            title: form.value.title,
            excerpt: form.value.excerpt,
            body: form.value.body,
            existing_categories: categories.value.map((c) => c.name),
            existing_tags: tags.value.map((t) => t.name),
        });
        const suggestion = parseSingleResponse<TaxonomySuggestion>(response);
        if (!suggestion) {
            toast.error.default(t('ai.publishing.taxonomyEmpty'));
            return;
        }

        if (suggestion.category_name) {
            const match = categories.value.find(
                (c) => c.name.toLowerCase() === suggestion.category_name.toLowerCase(),
            );
            if (match) {
                form.value.category_id = match.id;
            }
        }

        for (const tagName of suggestion.tags ?? []) {
            const existing = tags.value.find((t) => t.name.toLowerCase() === tagName.toLowerCase());
            const inSelected = selectedTags.value.some((t) => t.name.toLowerCase() === tagName.toLowerCase());
            if (inSelected) {
                continue;
            }
            if (existing) {
                selectedTags.value.push(existing);
            } else {
                selectedTags.value.push({ name: tagName } as Tag);
            }
        }

        toast.success.default(t('ai.publishing.taxonomyApplied'));
    } catch (e: unknown) {
        toast.error.action(e);
    } finally {
        aiTaxonomyLoading.value = false;
    }
}

const handlePreview = () => {
    showPreviewModal.value = true;
};

const previewContent = computed(() => {
    const category = categories.value.find((c: Category) => String(c.id) === String(form.value.category_id));
    return {
        title: form.value.title,
        body: form.value.body,
        excerpt: form.value.excerpt,
        featured_image: form.value.featured_image || undefined,
        author: { name: authStore.user?.name || 'Current User' },
        category: category ? { name: category.name } : null,
        published_at: form.value.published_at || new Date().toISOString(),
    } as unknown as Record<string, unknown>;
});

const handlePublishFromPreview = async () => {
    form.value.status = 'published';
    await handleSubmit();
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
        menus.value = getResponseList(response.data);
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
    
    // Optimistic UI update for lock status check
    if (lockStatus.value?.is_locked && lockStatus.value.locked_by?.id !== authStore.user?.id) {
        toast.error.action(t('publishing.content.form.locked'));
        loading.value = false;
        return;
    }

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
            const firstCat = categories.value[0];
            if (firstCat) form.value.category_id = firstCat.id;
        }

        // Prepare tags
        const tagIds = selectedPersistedTagIds();
        const newTags = selectedTags.value.filter((t: Tag) => t.id == null).map((t: Tag) => t.name);

        const payload = {
            ...form.value,
            published_at: toApiPublishedAt(form.value.published_at),
            comment_status: form.value.comment_status ? 'open' : 'closed',
            tags: tagIds,
            new_tags: newTags,
        };

        const response = await api.put(`/manage/publishing/contents/${contentId}`, payload);
        const updatedContent = parseSingleResponse(response);
        
        if (updatedContent) {
            if (response.data?.updated_at) {
                lastSaved.value = new Date(response.data.updated_at);
            }
        }
        
        // Update initial form after successful save
        initialForm.value = JSON.stringify({
            ...form.value,
            tags: selectedPersistedTagIds(),
        });
        
        toast.success.update(t('publishing.content.title_singular'));
        
        // Only redirect if not saving from within an embedded view
        if (status === null) {
            router.push({ name: 'contents.index' });
        }
    } catch (error: unknown) {
        if (error && typeof error === 'object' && 'response' in error) {
            const err = error as { response: { status: number, data: { errors: Record<string, string[]> } } };
            if (err.response?.status === 422) {
                setErrors(err.response.data.errors || {});
                return;
            }
        }
        logger.error('Failed to update content:', error);
        toast.error.fromResponse(error);
    } finally {
        loading.value = false;
    }
};

const showConfirmDialog = ref(false);

const handleCancel = () => {
    if (isDirty.value) {
        showConfirmDialog.value = true;
    } else {
        router.push({ name: 'contents.index' });
    }
};

const confirmCancel = () => {
    showConfirmDialog.value = false;
    router.push({ name: 'contents.index' });
};

onMounted(async () => {
    await loadAutoSavePreference();
    fetchContent();
    fetchCategories();
    fetchTags();
    fetchMenus();
});

onUnmounted(() => {
    // Clean up lock interval
    if (lockInterval.value) {
        clearInterval(lockInterval.value);
    }
    // Unlock content when leaving page
    if (lockStatus.value?.is_locked) {
        handleUnlock().catch(() => {});
    }
});
</script>
