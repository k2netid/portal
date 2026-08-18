<template>
  <div class="space-y-6">
        <PageHeader
      :title="trans.title"
      :subtitle="trans.subtitle"
      borderless
    >
      <template #actions>
        <div class="flex items-center gap-2">
                <Button
                  class="flex items-center gap-2 bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white border-0"
                  @click="scaffolderModalOpen = true"
                >
                  <Wand class="w-4 h-4" />
                  {{ t('system.appStore.scaffolderBtn') }}
                </Button>
                <Button
                  variant="secondary"
                  class="flex items-center gap-2 hover:bg-secondary/80"
                  @click="openGitModal"
                >
                  <GitBranch class="w-4 h-4" />
                  {{ trans.gitBtn }}
                </Button>
                <Button
                  class="flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white border-0"
                  @click="openUploadModal"
                >
                  <UploadIcon class="w-4 h-4" />
                  {{ trans.uploadBtn }}
                </Button>
              </div>
      </template>
    </PageHeader>

    <ConsoleListCard>
      <template #toolbar>
        <div class="relative w-full sm:max-w-md">
          <SearchIcon class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
          <Input
            v-model="searchQuery"
            type="text"
            :placeholder="trans.searchPlaceholder"
            class="h-10 w-full pl-9 bg-background"
          />
        </div>
        <div class="flex shrink-0 flex-wrap items-center gap-2">
          <Button
            v-for="tab in filterTabs"
            :key="tab.value"
            variant="ghost"
            size="sm"
            :class="activeTab === tab.value ? 'bg-primary/10 text-primary' : 'text-muted-foreground'"
            @click="activeTab = tab.value"
          >
            {{ tab.label }}
          </Button>
        </div>
      </template>

    <!-- Loading State -->
    <div
      v-if="loading"
      class="flex flex-col items-center justify-center py-20"
    >
      <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-indigo-500" />
      <span class="text-sm text-muted-foreground mt-4">{{ trans.loading }}</span>
    </div>

    <!-- Extensions Grid List -->
    <div
      v-else-if="filteredExtensions.length === 0"
      class="flex flex-col items-center justify-center py-16 px-6"
    >
      <Puzzle class="w-12 h-12 text-muted-foreground/50 mb-3" />
      <h3 class="text-base font-semibold text-foreground">{{ trans.noExtensions }}</h3>
      <p class="text-sm text-muted-foreground max-w-sm text-center mt-1">
        {{ trans.noExtensionsSub }}
      </p>
    </div>

    <div
      v-else
      class="space-y-6 p-4 sm:p-6"
    >
      <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3">
        <ExtensionCard
          v-for="ext in paginatedExtensions"
          :key="ext.slug"
          :ext="ext"
          :get-localized-description="getLocalizedDescription"
          @toggle-feature="toggleFeatureStatus"
          @toggle-status="toggleExtensionStatus"
          @configure="openSettingsModal"
          @uninstall="uninstallExtension"
        />
      </div>

      <!-- Standard Shared Pagination Component -->
      <div v-if="totalPages > 1" class="mt-8 pt-4 border-t border-border/30">
        <Pagination
          v-model:current-page="currentPage"
          v-model:per-page="itemsPerPage"
          :total-items="filteredExtensions.length"
          :show-per-page="true"
          :show-page-numbers="true"
          :per-page-options="[6, 12, 18, 24, 30]"
          embedded
        />
      </div>
    </div>
</ConsoleListCard>

    <!-- ZIP Upload Modal Component -->
    <UploadModal
      v-model:open="uploadModalOpen"
      :uploading="uploading"
      :upload-error="uploadError"
      @upload="uploadZip"
      @clear-error="uploadError = ''"
    />

    <!-- Configure Settings Modal Component -->
    <ConfigureModal
      v-model:open="settingsModalOpen"
      v-model:raw-settings-json="rawSettingsJson"
      :active-ext-config="activeExtConfig"
      @save="saveSettings"
    />

    <!-- Git Integration Modal Component -->
    <GitModal
      v-model:open="gitModalOpen"
      :cloning="cloning"
      :clone-error="cloneError"
      @clone="cloneGitRepo"
      @clear-error="cloneError = ''"
    />

    <!-- DeveloperKit Scaffolder Modal Component -->
    <ScaffolderModal
      v-model:open="scaffolderModalOpen"
      :scaffolding="scaffolding"
      :scaffold-error="scaffoldError"
      @scaffold="scaffoldPlugin"
      @clear-error="scaffoldError = ''"
    />
  </div>
</template>

<script setup lang="ts">
import { consolePath } from '@/shared/utils/consoleRoute';
import { PageHeader, ConsoleListCard } from '@/shared/components/shell';

import { ref, computed, onMounted, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRouter, useRoute } from 'vue-router';
import api from '@/engine/api/client';
import toast from '@/shared/services/toastService';
import { useConfirm } from '@/shared/composables/useConfirm';
import { Button, Input, Pagination } from '@/shared/components/ui';

// Sub-components
import ExtensionCard from './components/ExtensionCard.vue';
import UploadModal from './components/UploadModal.vue';
import GitModal from './components/GitModal.vue';
import ConfigureModal from './components/ConfigureModal.vue';
import ScaffolderModal from './components/ScaffolderModal.vue';

// Lucide icons
import {
  GitBranch,
  Puzzle,
  SearchIcon,
  UploadIcon,
  Wand,
} from 'lucide-vue-next';

interface FeatureItem {
    id: string;
    extension_slug: string;
    slug: string;
    name: string;
    description?: string;
    category: string;
    is_active: boolean;
}

interface ExtensionItem {
    id: string;
    slug: string;
    type: 'module' | 'plugin';
    name: string;
    version: string;
    status: 'active' | 'inactive';
    is_core: boolean;
    author?: string;
    license?: string;
    settings?: Record<string, unknown>;
    features?: FeatureItem[];
}

const { t, te } = useI18n();
const router = useRouter();
const route = useRoute();

const extensions = ref<ExtensionItem[]>([]);
const loading = ref(false);
const searchQuery = ref('');
const activeTab = ref('all');

const trans = computed(() => ({
    title: t('system.appStore.title'),
    subtitle: t('system.appStore.subtitle'),
    searchPlaceholder: t('system.appStore.searchPlaceholder'),
    gitBtn: t('system.appStore.gitBtn'),
    uploadBtn: t('system.appStore.uploadBtn'),
    all: t('system.appStore.all'),
    modules: t('system.appStore.modules'),
    plugins: t('system.appStore.plugins'),
    author: t('system.appStore.author'),
    license: t('system.appStore.license'),
    core: t('system.appStore.core'),
    locked: t('system.appStore.locked'),
    activate: t('system.appStore.activate'),
    deactivate: t('system.appStore.deactivate'),
    uninstall: t('system.appStore.uninstall'),
    configure: t('system.appStore.configure'),
    constituentFeatures: t('system.appStore.constituentFeatures'),
    noFeatures: t('system.appStore.noFeatures'),
    noExtensions: t('system.appStore.noExtensions'),
    noExtensionsSub: t('system.appStore.noExtensionsSub'),
    yes: t('system.appStore.yes'),
    no: t('system.appStore.no'),
    page: t('system.appStore.page'),
    next: t('system.appStore.next'),
    prev: t('system.appStore.prev'),
    showing: t('system.appStore.showing'),
    of: t('system.appStore.of'),
    loading: t('system.appStore.loading'),
    uploadTitle: t('system.appStore.uploadTitle'),
    sandboxNoticeTitle: t('system.appStore.sandboxNoticeTitle'),
    sandboxNoticeDesc: t('system.appStore.sandboxNoticeDesc'),
    dragDropLabel: t('system.appStore.dragDropLabel'),
    manifestNotice: t('system.appStore.manifestNotice'),
    cancel: t('system.appStore.cancel'),
    installing: t('system.appStore.installing'),
    installBtn: t('system.appStore.installBtn'),
    configTitle: t('system.appStore.configTitle'),
    configDesc: t('system.appStore.configDesc'),
    saveBtn: t('system.appStore.saveBtn'),
    gitTitle: t('system.appStore.gitTitle'),
    gitDesc: t('system.appStore.gitDesc'),
    repoUrl: t('system.appStore.repoUrl'),
    gitNotice: t('system.appStore.gitNotice'),
    gitCloning: t('system.appStore.gitCloning'),
    gitCloneBtn: t('system.appStore.gitCloneBtn'),
    understood: t('system.appStore.understood'),
}));

const getLocalizedDescription = (ext: ExtensionItem) => {
    const key = `system.appStore.descriptions.${ext.slug}`;
    return te(key) ? t(key) : t('system.appStore.descriptions.fallback', { type: ext.type });
};

const filterTabs = computed(() => [
    { label: trans.value.all, value: 'all' },
    { label: trans.value.modules, value: 'module' },
    { label: trans.value.plugins, value: 'plugin' }
]);

// Pagination state
const currentPage = ref(1);
const itemsPerPage = ref(6);
const totalPages = computed(() => Math.ceil(filteredExtensions.value.length / itemsPerPage.value));
const startIndex = computed(() => (currentPage.value - 1) * itemsPerPage.value);
const endIndex = computed(() => startIndex.value + itemsPerPage.value);
const paginatedExtensions = computed(() => {
    return filteredExtensions.value.slice(startIndex.value, endIndex.value);
});

// Watch query search or active tab filters to reset current page back to 1
watch([searchQuery, activeTab], () => {
    currentPage.value = 1;
});

// Modals state
const uploadModalOpen = ref(false);
const settingsModalOpen = ref(false);
const gitModalOpen = ref(false);
const scaffolderModalOpen = ref(false);

const uploading = ref(false);
const uploadError = ref('');

const cloning = ref(false);
const cloneError = ref('');

const scaffolding = ref(false);
const scaffoldError = ref('');

const activeExtConfig = ref<ExtensionItem | null>(null);
const rawSettingsJson = ref('{}');

const togglingFeature = ref<Record<string, boolean>>({});

const toggleFeatureStatus = async (feature: FeatureItem) => {
    togglingFeature.value[feature.slug] = true;
    const targetStatus = !feature.is_active;

    try {
        const response = await api.put(`/manage/infra/extensions/features/${feature.slug}/toggle`, {
            is_active: targetStatus
        });

        if (response.data?.success) {
            feature.is_active = targetStatus;
            toast.success(t('system.appStore.messages.featureToggled', {
                name: feature.name,
                status: targetStatus ? t('system.appStore.activate') : t('system.appStore.deactivate'),
            }));
        } else {
            toast.error(response.data?.message || t('system.appStore.messages.featureToggleFailed'));
        }
    } catch (err: unknown) {
        toast.error(t('system.appStore.messages.featureToggleError'));
    } finally {
        togglingFeature.value[feature.slug] = false;
    }
};

const { confirm } = useConfirm();

// Fetch Extensions
const fetchExtensions = async () => {
    loading.value = true;
    try {
        const response = await api.get('/manage/infra/extensions');
        if (Array.isArray(response.data)) {
            extensions.value = response.data;
        } else if (response.data?.success) {
            extensions.value = response.data.data || [];
        }
    } catch (err: unknown) {
        toast.error(t('system.appStore.messages.loadFailed'));
    } finally {
        loading.value = false;
    }
};

// Filter logic
const filteredExtensions = computed(() => {
    return extensions.value.filter(ext => {
        const matchesSearch = ext.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            ext.slug.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            (ext.author || '').toLowerCase().includes(searchQuery.value.toLowerCase());
        
        const matchesTab = activeTab.value === 'all' || ext.type === activeTab.value;
        return matchesSearch && matchesTab;
    });
});

// Toggle Status (Activate / Deactivate)
const toggleExtensionStatus = async (ext: ExtensionItem) => {
    const action = ext.status === 'active' ? 'deactivate' : 'activate';
    const isActivating = action === 'activate';

    const confirmed = await confirm({
        title: isActivating
            ? t('system.appStore.messages.toggleConfirmTitleActivate', { name: ext.name })
            : t('system.appStore.messages.toggleConfirmTitleDeactivate', { name: ext.name }),
        message: isActivating
            ? t('system.appStore.messages.toggleConfirmMessageActivate', { name: ext.name })
            : t('system.appStore.messages.toggleConfirmMessageDeactivate', { name: ext.name }),
        variant: isActivating ? 'warning' : 'danger',
        confirmText: isActivating ? t('system.appStore.activate') : t('system.appStore.deactivate'),
    });

    if (!confirmed) return;

    try {
        const response = await api.post(`/manage/infra/extensions/${ext.slug}/${action}`);
        const isSuccess = response.status === 200 || response.status === 201 || response.data?.success || (response.data && response.data.slug === ext.slug);
        if (isSuccess) {
            toast.success(isActivating
                ? t('system.appStore.messages.toggleSuccessActivated', { name: ext.name })
                : t('system.appStore.messages.toggleSuccessDeactivated', { name: ext.name }));
            await fetchExtensions();
        } else {
            toast.error(t('system.appStore.messages.toggleFailed', { action }));
        }
    } catch (err: unknown) {
        toast.error(t('system.appStore.messages.toggleError', { action }));
    }
};

// Uninstall
const uninstallExtension = async (slug: string) => {
    const confirmed = await confirm({
        title: t('system.appStore.messages.uninstallConfirmTitle'),
        message: t('system.appStore.messages.uninstallConfirmMessage'),
        variant: 'danger',
        confirmText: t('system.appStore.messages.uninstallConfirmText'),
    });

    if (!confirmed) return;

    try {
        const response = await api.delete(`/manage/infra/extensions/${slug}/uninstall`);
        const isSuccess = response.status === 200 || response.status === 204 || response.data?.success || response.data === null;
        if (isSuccess) {
            toast.success(t('system.appStore.messages.uninstallSuccess'));
            await fetchExtensions();
        }
    } catch (err: unknown) {
        toast.error(t('system.appStore.messages.uninstallFailed'));
    }
};

// ZIP uploader triggers
const openUploadModal = () => {
    uploadError.value = '';
    uploadModalOpen.value = true;
};

const uploadZip = async (file: File) => {
    uploading.value = true;
    uploadError.value = '';

    const formData = new FormData();
    formData.append('file', file);

    try {
        const response = await api.post('/manage/infra/extensions/upload', formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        });

        const isSuccess = response.status === 200 || response.status === 201 || response.data?.success || (response.data && response.data.slug);
        if (isSuccess) {
            toast.success(t('system.appStore.messages.uploadSuccess'));
            uploadModalOpen.value = false;
            await fetchExtensions();
        } else {
            uploadError.value = t('system.appStore.messages.uploadFailed');
        }
    } catch (err: unknown) {
        const axiosErr = err as { response?: { data?: { message?: string } } };
        uploadError.value = axiosErr.response?.data?.message || t('system.appStore.messages.uploadSecurityFailed');
    } finally {
        uploading.value = false;
    }
};

// Configure Settings Modal
const openSettingsModal = (ext: ExtensionItem) => {
    // Check if the module has a dedicated settings page route
    const routeMap: Record<string, { name: string; query?: Record<string, string> } | string> = {
        system: { name: 'settings', query: { tab: 'system' } },
        security: { name: 'settings', query: { tab: 'security' } },
        infra: { name: 'settings', query: { tab: 'performance' } },
        ai: { name: 'settings', query: { tab: 'ai' } },
        media: { name: 'settings', query: { tab: 'media' } },
        analytics: { name: 'settings', query: { tab: 'analytics' } },
        Jejakawan: { name: 'Jejakawan-settings' },
        forms: '/dash/forms', // Forms module settings
        library: consolePath('/library'),
        newsletter: consolePath('/newsletter'),
        search: { name: 'settings', query: { tab: 'system' } } // Search sits under system settings
    };

    const target = routeMap[ext.slug];
    if (target) {
        toast.success(t('system.appStore.messages.redirectingTo', { name: ext.name }));
        if (typeof target === 'object' && target.name) {
            router.push({
                name: target.name,
                params: {
                    dashboard_slug: route.params.dashboard_slug || 'dash',
                },
                query: target.query
            });
        } else if (typeof target === 'string') {
            const activeSlug = route.params.dashboard_slug || 'dash';
            const resolvedPath = target.replace('/dash', `/${activeSlug}`);
            router.push(resolvedPath);
        }
    } else {
        // Fallback: If no dedicated path exists (e.g. customized plugin with raw config settings)
        activeExtConfig.value = ext;
        rawSettingsJson.value = JSON.stringify(ext.settings || {}, null, 2);
        settingsModalOpen.value = true;
    }
};

const saveSettings = async () => {
    if (!activeExtConfig.value) return;

    let parsedSettings: Record<string, unknown>;
    try {
        parsedSettings = JSON.parse(rawSettingsJson.value);
    } catch (e) {
        toast.error(t('system.appStore.messages.invalidJson'));
        return;
    }

    try {
        const response = await api.put(`/manage/infra/extensions/${activeExtConfig.value.slug}/settings`, {
            settings: parsedSettings
        });

        const isSuccess = response.status === 200 || response.data?.success || (response.data && response.data.slug === activeExtConfig.value.slug);
        if (isSuccess) {
            toast.success(t('system.appStore.messages.settingsSaved'));
            settingsModalOpen.value = false;
            await fetchExtensions();
        }
    } catch (err: unknown) {
        toast.error(t('system.appStore.messages.settingsSaveFailed'));
    }
};

const openGitModal = () => {
    cloneError.value = '';
    gitModalOpen.value = true;
};

const cloneGitRepo = async (repoUrl: string) => {
    cloning.value = true;
    cloneError.value = '';

    try {
        const response = await api.post('/manage/infra/extensions/git-clone', {
            repo_url: repoUrl
        });

        const isSuccess = response.status === 200 || response.status === 201 || response.data?.success;
        if (isSuccess) {
            toast.success(t('system.appStore.messages.gitCloneSuccess'));
            gitModalOpen.value = false;
            await fetchExtensions();
        } else {
            cloneError.value = t('system.appStore.messages.gitCloneFailed');
        }
    } catch (err: unknown) {
        const axiosErr = err as { response?: { data?: { message?: string } } };
        cloneError.value = axiosErr.response?.data?.message || t('system.appStore.messages.gitCloneSecurityFailed');
    } finally {
        cloning.value = false;
    }
};

const scaffoldPlugin = async (payload: any) => {
    scaffolding.value = true;
    scaffoldError.value = '';
    try {
        if (payload.install_locally) {
            const response = await api.post('/manage/infra/cck/scaffold', payload);
            toast.success(response.data?.message || t('system.appStore.scaffolder.installedSuccess'));
            scaffolderModalOpen.value = false;
            await fetchExtensions();
        } else {
            const response = await api.post('/manage/infra/cck/scaffold', payload, {
                responseType: 'blob'
            });
            const blob = new Blob([response.data], { type: 'application/zip' });
            const url = window.URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.setAttribute('download', `${payload.slug}.zip`);
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            toast.success(t('system.appStore.scaffolder.downloadSuccess'));
            scaffolderModalOpen.value = false;
        }
    } catch (err: any) {
        const axiosErr = err as { response?: { data?: { message?: string } } };
        scaffoldError.value = axiosErr.response?.data?.message || t('system.appStore.scaffolder.scaffoldFailed');
        toast.error(scaffoldError.value);
    } finally {
        scaffolding.value = false;
    }
};

onMounted(() => {
    fetchExtensions();
});
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  line-clamp: 2;
  -webkit-box-orient: vertical;  
  overflow: hidden;
}
</style>
