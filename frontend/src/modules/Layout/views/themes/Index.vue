<template>
  <div class="space-y-6">
    <PageHeader
      borderless
      :title="$t('layout.themes.title')"
      :subtitle="$t('layout.themes.subtitle')"
    >
      <template #actions>
        <div class="flex items-center gap-3">
          <Select
            v-model="selectedType"
            @update:model-value="fetchThemes"
          >
            <SelectTrigger
              class="h-10 w-[180px] shrink-0 bg-background"
              :aria-label="$t('layout.themes.types.all')"
            >
              <SelectValue :placeholder="$t('layout.themes.types.all')" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="all">
                {{ $t('layout.themes.types.all') }}
              </SelectItem>
              <SelectItem value="frontend">
                {{ $t('layout.themes.types.frontend') }}
              </SelectItem>
              <SelectItem value="admin">
                {{ $t('layout.themes.types.admin') }}
              </SelectItem>
              <SelectItem value="email">
                {{ $t('layout.themes.types.email') }}
              </SelectItem>
            </SelectContent>
          </Select>
          <template v-if="uploadEnabled">
            <input
              ref="zipInputRef"
              type="file"
              accept=".zip,application/zip"
              class="hidden"
              @change="onZipSelected"
            >
            <Button
              variant="outline"
              size="sm"
              :disabled="uploading"
              @click="zipInputRef?.click()"
            >
              {{ uploading ? $t('layout.themes.uploading') : $t('layout.themes.actions.upload') }}
            </Button>
          </template>
          <Button
            size="sm"
            :disabled="scanning"
            variant="secondary"
            @click="scanThemes"
          >
            {{ scanning ? $t('layout.themes.scanning') : $t('layout.themes.scan') }}
          </Button>
        </div>
      </template>
    </PageHeader>

    <EmptyState
      v-if="themes.length === 0"
      :title="$t('layout.themes.list.empty')"
      :description="$t('layout.themes.list.emptySubtitle')"
      :icon="Palette"
    >
      <template #action>
        <Button size="sm" @click="scanThemes">
          {{ $t('layout.themes.scan') }}
        </Button>
      </template>
    </EmptyState>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div
        v-for="theme in themes"
        :key="theme.id"
        class="overflow-hidden rounded-xl border border-border/60 bg-card shadow-none transition-shadow"
        :class="{ 'ring-2 ring-primary/40 border-primary/30': theme.is_active }"
      >
        <!-- Preview -->
        <div class="relative aspect-[16/10] min-h-[12rem] bg-muted group overflow-hidden">
          <img
            v-if="theme.preview_image"
            :src="theme.preview_image"
            :alt="theme.name"
            class="h-full w-full object-cover"
          >
          <ThemeCardLivePreview
            v-else-if="theme.is_active && (theme.type || 'frontend') === 'frontend'"
            src="/"
            :title="`${theme.name} live preview`"
          />
          <div
            v-else
            class="flex h-full w-full items-center justify-center text-muted-foreground"
          >
            <Image class="h-16 w-16" />
          </div>
                    
          <!-- Status Badge -->
          <div class="absolute top-2 right-2">
            <Badge
              v-if="theme.is_active"
              variant="default"
              class="shadow-sm"
            >
              {{ $t('layout.themes.status.active') }}
            </Badge>
            <Badge
              v-else-if="theme.status && theme.status !== 'active'"
              class="shadow-sm"
              :variant="theme.status === 'broken' ? 'destructive' : (theme.status === 'pending' ? 'warning' : 'secondary')"
            >
              {{ $t('layout.themes.status.' + (theme.status || 'inactive')) }}
            </Badge>
          </div>

          <!-- Hover Actions -->
          <div class="absolute inset-0 bg-background/50 backdrop-blur-[1px] opacity-0 group-hover:opacity-100 transition-colors flex items-center justify-center gap-2">
            <Button
              variant="secondary"
              size="sm"
              @click="openPreview(theme)"
            >
              {{ $t('layout.themes.actions.preview') }}
            </Button>
            <Button
              v-if="theme.is_active"
              size="sm"
              @click="openThemeCustomizer(theme)"
            >
              {{ $t('layout.themes.actions.openCustomizer') }}
            </Button>
          </div>
        </div>

        <!-- Theme Info -->
        <div class="p-6">
          <div class="flex items-start justify-between">
            <div class="flex-1">
              <h3 class="text-lg font-semibold text-foreground">
                {{ theme.name }}
              </h3>
              <div class="flex items-center gap-2 mt-1">
                <span class="text-sm text-muted-foreground">{{ $t('layout.themes.list.version', { version: theme.version || '1.0.0' }) }}</span>
                <span class="text-xs px-2 py-0.5 bg-secondary text-secondary-foreground rounded">
                  {{ $t('layout.themes.types.' + (theme.type || 'frontend')) }}
                </span>
                <span
                  v-if="(theme as any).source === 'uploaded'"
                  class="text-xs px-2 py-0.5 bg-amber-100 text-amber-800 rounded"
                >
                  {{ $t('layout.themes.source.uploaded') }}
                </span>
                <span
                  v-else-if="(theme as any).source === 'bundled' || !(theme as any).source"
                  class="text-xs px-2 py-0.5 bg-emerald-100 text-emerald-800 rounded"
                >
                  {{ $t('layout.themes.source.bundled') }}
                </span>
                <span
                  v-if="theme.parent_theme"
                  class="text-xs px-2 py-0.5 bg-blue-100 text-blue-600 rounded"
                >
                  {{ $t('layout.themes.list.child') }}
                </span>
              </div>
            </div>
          </div>
                    
          <p
            v-if="theme.description"
            class="text-sm text-muted-foreground mt-2 line-clamp-2"
          >
            {{ theme.description }}
          </p>

          <div
            v-if="theme.author"
            class="mt-2 text-xs text-muted-foreground"
          >
            {{ $t('layout.themes.list.by', { author: theme.author }) }}
          </div>

          <!-- Actions -->
          <div class="mt-4 flex items-center gap-2 flex-wrap">
            <!-- Primary Action Button -->
            <div class="flex flex-col w-full gap-2">
              <div class="flex gap-2 w-full">
                <Button
                  v-if="theme.is_active"
                  size="sm"
                  class="flex-1 h-10 inline-flex items-center gap-2"
                  @click="openThemeCustomizer(theme)"
                >
                  <Palette data-icon="inline-start" class="size-4 shrink-0" />
                  {{ $t('layout.themes.actions.openCustomizer') }}
                </Button>
                <Button
                  v-else
                  size="sm"
                  class="flex-1 h-10 inline-flex items-center gap-2"
                  @click="activateTheme(theme)"
                >
                  <Check data-icon="inline-start" class="size-4 shrink-0" />
                  {{ $t('layout.themes.actions.activate') }}
                </Button>
              </div>
            </div>

            <!-- Secondary Action Buttons -->
            <Button
              variant="outline"
              size="icon"
              :aria-label="$t('layout.themes.actions.preview')"
              :title="$t('layout.themes.actions.preview')"
              @click="openPreview(theme)"
            >
              <Eye class="w-4 h-4" />
            </Button>
            <Button
              variant="outline"
              size="icon"
              :aria-label="$t('layout.themes.actions.validate')"
              :title="$t('layout.themes.actions.validate')"
              @click="validateTheme(theme)"
            >
              <CheckCircle class="w-4 h-4" />
            </Button>
          </div>
        </div>
      </div>
    </div>

    <!-- Preview Modal -->
    <div
      v-if="showPreviewModal"
      class="fixed inset-0 z-50 bg-black bg-opacity-50 flex items-center justify-center p-4"
      @click.self="showPreviewModal = false"
    >
      <div class="bg-card rounded-lg w-full max-w-6xl h-[90vh] flex flex-col">
        <div class="flex items-center justify-between p-4 border-b">
          <h3 class="text-lg font-semibold">
            {{ $t('layout.themes.modals.previewTitle', { name: selectedTheme?.name }) }}
          </h3>
          <button
            class="text-muted-foreground hover:text-muted-foreground"
            @click="showPreviewModal = false"
          >
            <X class="w-6 h-6" />
          </button>
        </div>
        <div class="flex-1 overflow-hidden">
          <ThemePreview
            v-if="selectedTheme"
            :theme="selectedTheme"
            :preview-url="publicPreviewUrl"
            @close="showPreviewModal = false"
          />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { EmptyState } from '@/shared/components/feedback';

import { PageHeader } from '@/shared/components/shell';

import { logger } from '@/shared/utils/logger';
import { ref, onMounted, toRaw } from 'vue';
import { useRouter } from 'vue-router';
import api from '@/engine/api/client';
import toast from '@/shared/services/toastService';
import { resolvePublicEmbedUrl } from '@/modules/Layout/utils/publicSiteUrl';
import { useConfirm } from '@/shared/composables/useConfirm';
import { parseResponse, ensureArray } from '@/shared/utils/responseParser';
import {
  Check,
  CheckCircle,
  Eye,
  Image,
  Palette,
  X,
} from 'lucide-vue-next';
import ThemePreview from '@/modules/Layout/components/themes/ThemePreview.vue';
import ThemeCardLivePreview from '@/modules/Layout/components/themes/ThemeCardLivePreview.vue';

import { useI18n } from 'vue-i18n';
import { Badge, Button, Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/shared/components/ui';

const publicPreviewUrl = resolvePublicEmbedUrl('/');
const { t } = useI18n();
const { confirm } = useConfirm();
const router = useRouter();

interface Theme {
    id: string;
    name: string;
    slug: string;
    description?: string;
    author?: string;
    version?: string;
    type?: string;
    parent_theme?: string;
    is_active?: boolean;
    status?: string;
    preview_image?: string;
    source?: string;
    bundle_url?: string;
}

const themes = ref<Theme[]>([]);
const selectedType = ref('all');
const scanning = ref(false);
const uploading = ref(false);
const uploadEnabled = ref(false);
const zipInputRef = ref<HTMLInputElement | null>(null);
const showPreviewModal = ref(false);
const selectedTheme = ref<Theme | null>(null);

const fetchThemes = async () => {
    try {
        const params = selectedType.value ? { type: selectedType.value } : {};
        const response = await api.get('/manage/layout/themes', { params });
        const { data } = parseResponse(response);
        themes.value = ensureArray(data);
    } catch (error: unknown) {
        logger.error('Failed to fetch themes:', error);
        themes.value = [];
    }
};

const scanThemes = async () => {
    scanning.value = true;
    try {
        const response = await api.post('/manage/layout/themes/scan');
        await fetchThemes();
        const count = response.data?.count || 0;
        toast.success(t('layout.themes.messages.scanSuccess', { count }));
    } catch (error: unknown) {
        logger.error('Failed to scan themes:', error);
        toast.error(t('common.messages.toast.error'), t('layout.themes.messages.scanFailed'));
    } finally {
        scanning.value = false;
    }
};

const activateTheme = async (theme: Theme) => {
    const confirmed = await confirm({
        title: t('layout.themes.actions.activate'),
        message: t('layout.themes.messages.activateConfirm', { name: theme.name }),
        variant: 'info',
        confirmText: t('layout.themes.actions.activate'),
    });

    if (!confirmed) return;

    try {
        await api.post(`/manage/layout/themes/${theme.slug}/activate`);
        const { notifyFrontendThemeActivated } = await import('@/modules/Layout/utils/themeActivationSync');
        notifyFrontendThemeActivated(theme.slug);
        await fetchThemes();
        toast.success(t('layout.themes.messages.activateSuccess'));
    } catch (error: unknown) {
        logger.error('Failed to activate theme:', error);
        toast.error(error instanceof Error ? error.message : t('layout.themes.messages.activateFailed'));
    }
};

const validateTheme = async (theme: Theme) => {
    try {
        const response = await api.post(`/manage/layout/themes/${theme.slug}/validate`);
        const data = response.data;
        
        if (data.valid) {
            toast.success(t('layout.themes.messages.validateSuccess'));
        } else {
            // Can be replaced with a modal or detailed toast if needed, 
            // but multiline toast might be tricky. Using error toast with detail.
            logger.error('Validation errors:', data.errors);
            toast.error(t('layout.themes.messages.validateFailed'), data.errors.join(', '));
        }
        
        await fetchThemes();
    } catch (error: unknown) {
        logger.error('Failed to validate theme:', error);
        toast.error(t('common.messages.toast.error'), t('layout.themes.messages.validateError'));
    }
};

const openPreview = (theme: Theme) => {
    selectedTheme.value = theme;
    showPreviewModal.value = true;
};

const openThemeCustomizer = (theme: Theme) => {
    const themeData = { ...toRaw(theme) };
    selectedTheme.value = themeData;
    router.push({ name: 'themes.customizer', params: { slug: themeData.slug } });
};


const fetchUploadStatus = async () => {
    try {
        const res = await api.get('/manage/layout/themes/upload-status');
        uploadEnabled.value = !!res.data?.enabled;
    } catch {
        uploadEnabled.value = false;
    }
};

const onZipSelected = async (event: Event) => {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0];
    if (!file) return;
    uploading.value = true;
    try {
        const form = new FormData();
        form.append('theme_zip', file);
        await api.post('/manage/layout/themes/install', form, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });
        toast.success(t('layout.themes.messages.uploadSuccess'));
        await fetchThemes();
    } catch (error: unknown) {
        logger.error('Theme upload failed:', error);
        toast.error(t('layout.themes.messages.uploadFailed'));
    } finally {
        uploading.value = false;
        input.value = '';
    }
};

onMounted(() => {
    void fetchUploadStatus();
    fetchThemes();
});
</script>
