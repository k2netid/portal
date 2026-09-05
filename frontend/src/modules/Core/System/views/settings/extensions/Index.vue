<template>
  <div class="space-y-6">
        <PageHeader
      :title="trans.title"
      :subtitle="trans.subtitle"
      borderless
    >
      <template #actions>
        <div class="flex items-center gap-2 flex-wrap justify-end">
          <DropdownMenu>
            <DropdownMenuTrigger as-child>
              <Button
                variant="outline"
                class="flex items-center gap-2"
                :disabled="lifecycleBusy"
              >
                <Package class="w-4 h-4" />
                {{ t('system.appStore.installProfileBtn') }}
              </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end" class="w-64">
              <DropdownMenuLabel>{{ t('system.appStore.installProfileMenuTitle') }}</DropdownMenuLabel>
              <DropdownMenuSeparator />
              <DropdownMenuItem
                class="flex flex-col items-start gap-0.5 py-2"
                @click="applyInstallProfile('cms_site')"
              >
                <span class="font-medium">{{ t('system.appStore.installProfileCmsSite') }}</span>
                <span class="text-xs text-muted-foreground whitespace-normal">{{ t('system.appStore.installProfileCmsSiteHint') }}</span>
              </DropdownMenuItem>
              <DropdownMenuItem
                class="flex flex-col items-start gap-0.5 py-2"
                @click="applyInstallProfile('cms')"
              >
                <span class="font-medium">{{ t('system.appStore.installProfileCms') }}</span>
                <span class="text-xs text-muted-foreground whitespace-normal">{{ t('system.appStore.installProfileCmsHint') }}</span>
              </DropdownMenuItem>
              <DropdownMenuItem
                class="flex flex-col items-start gap-0.5 py-2"
                @click="applyInstallProfile('core')"
              >
                <span class="font-medium">{{ t('system.appStore.installProfileCore') }}</span>
                <span class="text-xs text-muted-foreground whitespace-normal">{{ t('system.appStore.installProfileCoreHint') }}</span>
              </DropdownMenuItem>
            </DropdownMenuContent>
          </DropdownMenu>
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
                  v-if="canUpload"
                  class="flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white border-0"
                  @click="openUploadModal"
                >
                  <UploadIcon class="w-4 h-4" />
                  {{ trans.uploadBtn }}
                </Button>
              </div>
      </template>
    </PageHeader>

    <div
      v-if="!loading && showEnablePublicSiteBanner"
      class="rounded-xl border border-primary/25 bg-primary/5 px-4 py-3 sm:px-5 sm:py-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
      role="status"
    >
      <div class="flex gap-3 min-w-0">
        <span class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-primary">
          <Globe class="h-4 w-4" />
        </span>
        <div class="min-w-0 space-y-1">
          <p class="text-sm font-semibold text-foreground">
            {{ t('system.appStore.enablePublicSiteTitle') }}
          </p>
          <p class="text-sm text-muted-foreground leading-relaxed">
            {{ t('system.appStore.enablePublicSiteBody') }}
          </p>
        </div>
      </div>
      <div class="flex items-center gap-2 shrink-0 sm:pl-4">
        <Button
          size="sm"
          class="gap-1.5"
          :disabled="lifecycleBusy"
          @click="applyInstallProfile('cms_site')"
        >
          <Globe class="h-3.5 w-3.5" />
          {{ t('system.appStore.enablePublicSiteCta') }}
        </Button>
        <Button
          variant="ghost"
          size="sm"
          class="text-muted-foreground"
          :disabled="lifecycleBusy"
          :aria-label="t('system.appStore.enablePublicSiteDismiss')"
          @click="dismissEnablePublicSiteBanner"
        >
          <X class="h-4 w-4" />
        </Button>
      </div>
    </div>

    <div
      v-else-if="!loading && showActivateCmsBanner"
      class="rounded-xl border border-border bg-muted/40 px-4 py-3 sm:px-5 sm:py-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
      role="status"
    >
      <div class="flex gap-3 min-w-0">
        <span class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-muted text-foreground/80">
          <Layers class="h-4 w-4" />
        </span>
        <div class="min-w-0 space-y-1">
          <p class="text-sm font-semibold text-foreground">
            {{ t('system.appStore.activateCmsBannerTitle') }}
          </p>
          <p class="text-sm text-muted-foreground leading-relaxed">
            {{ t('system.appStore.activateCmsBannerBody') }}
          </p>
        </div>
      </div>
      <div class="flex items-center gap-2 shrink-0 sm:pl-4">
        <Button
          variant="secondary"
          size="sm"
          class="gap-1.5"
          :disabled="lifecycleBusy"
          @click="applyInstallProfile('cms')"
        >
          <Layers class="h-3.5 w-3.5" />
          {{ t('system.appStore.activateCmsBannerCta') }}
        </Button>
        <Button
          variant="ghost"
          size="sm"
          class="text-muted-foreground"
          :disabled="lifecycleBusy"
          :aria-label="t('system.appStore.enablePublicSiteDismiss')"
          @click="dismissActivateCmsBanner"
        >
          <X class="h-4 w-4" />
        </Button>
      </div>
    </div>

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
      <div class="space-y-8">
        <section
          v-for="group in groupedExtensions"
          :key="group.key"
          class="space-y-3"
        >
          <div class="flex items-center justify-between gap-2 px-1">
            <h3 class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">
              {{ group.label }}
              <span class="font-normal text-muted-foreground/70">({{ group.items.length }})</span>
            </h3>
            <div class="flex items-center gap-2 shrink-0">
              <Button
                v-if="group.key === 'cms' && activeTab === 'cms' && inactiveCms.length > 0"
                variant="secondary"
                size="sm"
                class="gap-1.5"
                :disabled="lifecycleBusy"
                @click="bulkActivateCms"
              >
                <Layers class="h-3.5 w-3.5" />
                {{ t('system.appStore.bulkActivateCms') }}
              </Button>
              <Button
                v-if="group.key === 'cms' && activeTab === 'cms' && activeCms.length > 0"
                variant="outline"
                size="sm"
                class="gap-1.5"
                :disabled="lifecycleBusy"
                @click="bulkDeactivateCms"
              >
                <PowerOff class="h-3.5 w-3.5" />
                {{ t('system.appStore.bulkDeactivateCms') }}
              </Button>
            </div>
          </div>
          <div class="overflow-x-auto rounded-xl border border-border/60">
            <table class="min-w-full divide-y divide-border text-sm">
              <thead class="bg-muted/50">
                <tr>
                  <th class="px-4 py-2.5 text-left text-[11px] font-semibold uppercase tracking-wider text-foreground/70">{{ t('system.appStore.table.name') }}</th>
                  <th class="px-4 py-2.5 text-left text-[11px] font-semibold uppercase tracking-wider text-foreground/70">{{ t('system.appStore.table.status') }}</th>
                  <th class="px-4 py-2.5 text-left text-[11px] font-semibold uppercase tracking-wider text-foreground/70">{{ t('system.appStore.table.license') }}</th>
                  <th class="px-4 py-2.5 text-left text-[11px] font-semibold uppercase tracking-wider text-foreground/70">{{ t('system.appStore.table.requires') }}</th>
                  <th class="px-4 py-2.5 text-left text-[11px] font-semibold uppercase tracking-wider text-foreground/70">{{ t('system.appStore.table.health') }}</th>
                  <th class="px-4 py-2.5 text-right text-[11px] font-semibold uppercase tracking-wider text-foreground/70">{{ t('system.appStore.table.actions') }}</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-border bg-card">
                <tr
                  v-for="ext in group.items"
                  :key="ext.slug"
                  class="hover:bg-muted/40"
                >
                  <td class="px-4 py-3">
                    <div class="font-medium text-foreground">{{ ext.name }}</div>
                    <div class="font-mono text-[11px] text-muted-foreground">{{ ext.slug }} · v{{ ext.version }}</div>
                  </td>
                  <td class="px-4 py-3">
                    <Badge :variant="ext.status === 'active' ? 'success' : 'secondary'">
                      {{ ext.status === 'active' ? t('system.appStore.card.statusActive') : t('system.appStore.card.statusInactive') }}
                    </Badge>
                  </td>
                  <td class="px-4 py-3">
                    <span :class="licenseBadgeClass(ext)">{{ licenseBadgeLabel(ext) }}</span>
                  </td>
                  <td class="px-4 py-3 text-xs text-muted-foreground">
                    {{ requirementLabels(ext) }}
                  </td>
                  <td class="px-4 py-3">
                    <Badge
                      :variant="healthVariant(ext)"
                      :title="healthTitle(ext)"
                    >
                      {{ healthLabel(ext) }}
                    </Badge>
                  </td>
                  <td class="px-4 py-3 text-right whitespace-nowrap space-x-1">
                    <Button
                      v-if="!ext.is_core"
                      variant="ghost"
                      size="sm"
                      @click="toggleExtensionStatus(ext)"
                    >
                      {{ ext.status === 'active' ? trans.deactivate : trans.activate }}
                    </Button>
                    <Button
                      variant="ghost"
                      size="sm"
                      @click="openSettingsModal(ext)"
                    >
                      {{ trans.configure }}
                    </Button>
                    <Button
                      v-if="ext.can_export"
                      variant="ghost"
                      size="sm"
                      :disabled="exportingExtension === ext.slug"
                      :title="t('system.appStore.exportZip')"
                      @click="exportExtension(ext)"
                    >
                      <Download class="h-3.5 w-3.5 mr-1" :class="{ 'animate-bounce': exportingExtension === ext.slug }" />
                      {{ t('system.appStore.export') }}
                    </Button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </section>
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
import { Badge, Button, Input, Pagination, DropdownMenu, DropdownMenuTrigger, DropdownMenuContent, DropdownMenuItem, DropdownMenuLabel, DropdownMenuSeparator } from '@/shared/components/ui';

// Sub-components
import UploadModal from './components/UploadModal.vue';
import GitModal from './components/GitModal.vue';
import ConfigureModal from './components/ConfigureModal.vue';
import ScaffolderModal from './components/ScaffolderModal.vue';

// Lucide icons
import {
  Download,
  GitBranch,
  Globe,
  Layers,
  Package,
  PowerOff,
  Puzzle,
  SearchIcon,
  UploadIcon,
  Wand,
  X,
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
    family?: string;
    parent_slug?: string | null;
    name: string;
    version: string;
    status: 'active' | 'inactive';
    is_core: boolean;
    can_uninstall?: boolean;
    can_export?: boolean;
    author?: string;
    description?: string;
    license?: string;
    requirements?: Record<string, string> | null;
    settings?: Record<string, unknown>;
    manifest?: Record<string, unknown>;
    features?: FeatureItem[];
    health?: {
        status?: 'ok' | 'warning' | 'error';
        issues?: Array<{ code: string; message: string }>;
    };
}

import { useSystemStore } from '@/modules/Core/System/stores/system';
import { registry } from '@/engine/registry';
import { isOptionalFirstPartySlug } from '@/engine/bootstrap/deferredConsoleModules';

const { t, te } = useI18n();
const router = useRouter();
const route = useRoute();
const systemStore = useSystemStore();

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
    platform: t('system.appStore.platform'),
    modules: t('system.appStore.modules'),
    plugins: t('system.appStore.plugins'),
    familyCms: t('system.appStore.familyCms'),
    familyAudience: t('system.appStore.familyAudience'),
    familyCommunications: t('system.appStore.familyCommunications'),
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
    if (ext.description && ext.description.trim() !== '') {
        return ext.description;
    }
    const key = `system.appStore.descriptions.${ext.slug}`;
    return te(key) ? t(key) : t('system.appStore.descriptions.fallback', { type: ext.type });
};

const filterTabs = computed(() => [
    { label: trans.value.all, value: 'all' },
    { label: trans.value.platform, value: 'platform' },
    { label: trans.value.familyCms, value: 'cms' },
    { label: trans.value.familyAudience, value: 'audience' },
    { label: trans.value.familyCommunications, value: 'communications' },
    { label: trans.value.plugins, value: 'plugin' },
]);

const resolveFamily = (ext: ExtensionItem): string => {
    if (ext.type === 'plugin' || ext.family === 'plugin') {
        return 'plugin';
    }
    if (ext.family && ext.family !== 'module') {
        return ext.family;
    }
    if (ext.is_core || ext.slug === 'core') {
        return 'platform';
    }
    return ext.family || 'cms';
};

const familyLabel = (key: string): string => {
    const map: Record<string, string> = {
        platform: trans.value.platform,
        cms: trans.value.familyCms,
        audience: trans.value.familyAudience,
        communications: trans.value.familyCommunications,
        plugin: trans.value.plugins,
        module: trans.value.modules,
    };
    return map[key] || key;
};

const inactiveCms = computed(() =>
    extensions.value.filter((ext) => resolveFamily(ext) === 'cms' && ext.status !== 'active' && !ext.is_core),
);

const activeCms = computed(() =>
    extensions.value.filter((ext) => resolveFamily(ext) === 'cms' && ext.status === 'active' && !ext.is_core),
);

const siteExtension = computed(() =>
    extensions.value.find((ext) => ext.slug === 'site') ?? null,
);

const siteIsActive = computed(() => siteExtension.value?.status === 'active');

const DISMISS_PUBLIC_SITE_KEY = 'ja:appstore:dismiss:enable-public-site';
const DISMISS_ACTIVATE_CMS_KEY = 'ja:appstore:dismiss:activate-cms';

const publicSiteBannerDismissed = ref(
    typeof sessionStorage !== 'undefined' && sessionStorage.getItem(DISMISS_PUBLIC_SITE_KEY) === '1',
);
const activateCmsBannerDismissed = ref(
    typeof sessionStorage !== 'undefined' && sessionStorage.getItem(DISMISS_ACTIVATE_CMS_KEY) === '1',
);

/** Site pack exists but is off → nudge toward cms_site (apex public theme). */
const showEnablePublicSiteBanner = computed(() =>
    !publicSiteBannerDismissed.value
    && siteExtension.value !== null
    && !siteIsActive.value,
);

/** No Site row (or already handled) but CMS packs are mostly inactive. */
const showActivateCmsBanner = computed(() =>
    !activateCmsBannerDismissed.value
    && !showEnablePublicSiteBanner.value
    && !siteIsActive.value
    && inactiveCms.value.length > 0
    && activeCms.value.length === 0,
);

const dismissEnablePublicSiteBanner = () => {
    publicSiteBannerDismissed.value = true;
    try {
        sessionStorage.setItem(DISMISS_PUBLIC_SITE_KEY, '1');
    } catch {
        // ignore
    }
};

const dismissActivateCmsBanner = () => {
    activateCmsBannerDismissed.value = true;
    try {
        sessionStorage.setItem(DISMISS_ACTIVATE_CMS_KEY, '1');
    } catch {
        // ignore
    }
};

// Pagination state
const currentPage = ref(1);
const itemsPerPage = ref(24);
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
    } catch (_err: unknown) {
        toast.error(t('system.appStore.messages.featureToggleError'));
    } finally {
        togglingFeature.value[feature.slug] = false;
    }
};

const { confirm } = useConfirm();

const unwrapApiData = <T,>(payload: unknown): T | undefined => {
    if (payload && typeof payload === 'object' && 'data' in payload && (payload as { data?: unknown }).data !== undefined) {
        return (payload as { data: T }).data;
    }
    return payload as T | undefined;
};

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
    } catch (_err: unknown) {
        toast.error(t('system.appStore.messages.loadFailed'));
    } finally {
        loading.value = false;
    }
};

const FAMILY_ORDER = ['platform', 'cms', 'audience', 'communications', 'module', 'plugin'] as const;

const filteredExtensions = computed(() => {
    return extensions.value
        .filter((ext) => {
            const matchesSearch =
                ext.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
                ext.slug.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
                (ext.author || '').toLowerCase().includes(searchQuery.value.toLowerCase());

            const family = resolveFamily(ext);
            const matchesTab = activeTab.value === 'all' || activeTab.value === family;

            return matchesSearch && matchesTab;
        })
        .sort((a, b) => {
            const ai = FAMILY_ORDER.indexOf(resolveFamily(a) as typeof FAMILY_ORDER[number]);
            const bi = FAMILY_ORDER.indexOf(resolveFamily(b) as typeof FAMILY_ORDER[number]);
            return (ai === -1 ? FAMILY_ORDER.length : ai) - (bi === -1 ? FAMILY_ORDER.length : bi);
        });
});

const groupedExtensions = computed(() => {
    const buckets = new Map<string, ExtensionItem[]>();
    for (const ext of paginatedExtensions.value) {
        const key = resolveFamily(ext);
        const list = buckets.get(key) ?? [];
        list.push(ext);
        buckets.set(key, list);
    }

    const orderedKeys = [
        ...FAMILY_ORDER.filter((key) => buckets.has(key)),
        ...[...buckets.keys()].filter((key) => !FAMILY_ORDER.includes(key as typeof FAMILY_ORDER[number])),
    ];

    return orderedKeys.map((key) => ({
        key,
        label: familyLabel(key),
        items: buckets.get(key) ?? [],
    }));
});

const requirementLabels = (ext: ExtensionItem): string => {
    const req = ext.requirements;
    if (!req || Object.keys(req).length === 0) {
        return '—';
    }
    return Object.keys(req).join(', ');
};

const healthVariant = (ext: ExtensionItem): 'success' | 'warning' | 'destructive' | 'secondary' => {
    const status = ext.health?.status;
    if (status === 'error') {
        return 'destructive';
    }
    if (status === 'warning') {
        return 'warning';
    }
    return 'success';
};

const healthLabel = (ext: ExtensionItem): string => {
    const status = ext.health?.status;
    if (status === 'error') {
        return t('system.appStore.healthError');
    }
    if (status === 'warning') {
        return t('system.appStore.healthWarning');
    }
    return t('system.appStore.healthOk');
};

const healthTitle = (ext: ExtensionItem): string => {
    const issues = ext.health?.issues || [];
    if (issues.length === 0) {
        return t('system.appStore.healthOk');
    }
    return issues.map((issue) => issue.message).join('\n');
};

const packLicenseTier = (ext: ExtensionItem): 'free' | 'pro' | 'pro_plus' => {
    if (ext.is_core || ext.slug === 'core') {
        return 'free';
    }
    const fromSettings = ext.settings?.license_tier;
    const fromManifest = ext.manifest?.license_tier;
    for (const value of [fromSettings, fromManifest]) {
        if (value === 'free' || value === 'pro' || value === 'pro_plus') {
            return value;
        }
    }
    const fallback: Record<string, 'free' | 'pro' | 'pro_plus'> = {
        core: 'free',
        system: 'free',
        search: 'free',
        mail: 'pro',
        media: 'pro',
        publishing: 'pro',
        library: 'pro',
        layout: 'pro',
        forms: 'pro',
        newsletter: 'pro',
        analytics: 'pro',
        'cms-ai': 'pro_plus',
        security: 'pro_plus',
        infra: 'pro_plus',
        ai: 'pro_plus',
    };
    return fallback[ext.slug] || 'pro';
};

const licenseBadgeLabel = (ext: ExtensionItem): string => {
    if (ext.is_core || ext.slug === 'core') {
        return t('system.appStore.card.licensePlatform');
    }
    const tier = packLicenseTier(ext);
    if (tier === 'free') {
        return t('system.appStore.card.licenseCommunity');
    }
    if (tier === 'pro_plus') {
        return t('system.appStore.card.licenseProPlus');
    }
    return t('system.appStore.card.licensePro');
};

const licenseBadgeClass = (ext: ExtensionItem): string => {
    const tier = packLicenseTier(ext);
    if (ext.is_core || ext.slug === 'core' || tier === 'free') {
        return 'inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-slate-500/10 text-slate-500 dark:text-slate-400 border border-slate-500/20';
    }
    if (tier === 'pro_plus') {
        return 'inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-amber-500/15 text-amber-600 dark:text-amber-400 border border-amber-500/30';
    }
    return 'inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-indigo-500/15 text-indigo-700 dark:text-indigo-300 border border-indigo-500/30';
};

type LifecycleRelation = {
    slug: string;
    name: string;
    satisfied?: boolean;
};

type LifecyclePreview = {
    can_proceed: boolean;
    can_cascade?: boolean;
    requires: LifecycleRelation[];
    suggests: LifecycleRelation[];
    dependents: LifecycleRelation[];
    blockers?: Array<{ name?: string; slug?: string; reason?: string; constraint?: string }>;
    runtime?: Array<{ name?: string; constraint?: string; version?: string; satisfied?: boolean }>;
    license?: string;
    cascade_plan?: {
        will_activate: Array<{ slug: string; name: string; reason?: string }>;
        can_cascade?: boolean;
    };
};

const formatLifecycleMessage = (base: string, preview: LifecyclePreview, activating: boolean): string => {
    const parts = [base];
    if (activating) {
        const cascadeExtras = (preview.cascade_plan?.will_activate || []).filter((row) => row.reason === 'required');
        if (cascadeExtras.length > 0) {
            parts.push(t('system.appStore.messages.lifecycleCascade', {
                list: cascadeExtras.map((row) => row.name).join(', '),
            }));
        } else {
            const missing = (preview.requires || []).filter((row) => !row.satisfied);
            if (missing.length > 0) {
                parts.push(t('system.appStore.messages.lifecycleRequiresMissing', {
                    list: missing.map((row) => row.name).join(', '),
                }));
            }
        }
        if (preview.license) {
            parts.push(t('system.appStore.messages.lifecycleLicense', { detail: preview.license }));
        }
        const runtime = (preview.runtime || []).filter((row) => row.satisfied === false);
        if (runtime.length > 0) {
            parts.push(t('system.appStore.messages.lifecycleRuntime', {
                list: runtime.map((row) => `${row.name} ${row.constraint}`).join(', '),
            }));
        }
        const recommended = (preview.suggests || []).filter((row) => !row.satisfied);
        if (recommended.length > 0) {
            parts.push(t('system.appStore.messages.lifecycleSuggests', {
                list: recommended.map((row) => row.name).join(', '),
            }));
        }
        parts.push(t('system.appStore.messages.lifecycleSidebarActivate'));
    } else {
        if ((preview.dependents || []).length > 0) {
            parts.push(t('system.appStore.messages.lifecycleDependents', {
                list: preview.dependents.map((row) => row.name).join(', '),
            }));
        }
        parts.push(t('system.appStore.messages.lifecycleSidebarDeactivate'));
    }

    return parts.join('\n\n');
};

// Toggle Status (Activate / Deactivate)
const toggleExtensionStatus = async (ext: ExtensionItem) => {
    const action = ext.status === 'active' ? 'deactivate' : 'activate';
    const isActivating = action === 'activate';

    let preview: LifecyclePreview = {
        can_proceed: true,
        requires: [],
        suggests: [],
        dependents: [],
    };
    try {
        const previewResponse = await api.get(`/manage/infra/extensions/${ext.slug}/lifecycle-preview`, {
            params: { intent: action, cascade: isActivating ? 1 : 0 },
        });
        const previewPayload = unwrapApiData<LifecyclePreview>(previewResponse.data);
        if (previewPayload && typeof previewPayload === 'object') {
            preview = previewPayload;
        }
    } catch {
        /* fall back to generic copy */
    }

    const baseMessage = isActivating
        ? t('system.appStore.messages.toggleConfirmMessageActivate', { name: ext.name })
        : t('system.appStore.messages.toggleConfirmMessageDeactivate', { name: ext.name });
    const detailMessage = formatLifecycleMessage(baseMessage, preview, isActivating);

    if (!preview.can_proceed) {
        toast.error(t('system.appStore.messages.lifecycleBlocked', { detail: detailMessage }));
        return;
    }

    const confirmed = await confirm({
        title: isActivating
            ? t('system.appStore.messages.toggleConfirmTitleActivate', { name: ext.name })
            : t('system.appStore.messages.toggleConfirmTitleDeactivate', { name: ext.name }),
        message: detailMessage,
        variant: isActivating ? 'warning' : 'danger',
        confirmText: isActivating ? t('system.appStore.activate') : t('system.appStore.deactivate'),
    });

    if (!confirmed) return;

    try {
        const response = await api.post(
            `/manage/infra/extensions/${ext.slug}/${action}`,
            {},
            isActivating ? { params: { cascade: 1 } } : undefined,
        );
        const isSuccess = response.status === 200 || response.status === 201 || response.data?.success || (response.data && response.data.slug === ext.slug);
        if (isSuccess) {
            toast.success(isActivating
                ? t('system.appStore.messages.toggleSuccessActivated', { name: ext.name })
                : t('system.appStore.messages.toggleSuccessDeactivated', { name: ext.name }));
            await fetchExtensions();
            await systemStore.fetchPublicSettings({ force: true });
            // Optional FE modules are registered only when active; router snapshot needs reload.
            if (isActivating && isOptionalFirstPartySlug(ext.slug) && !registry.hasModule(ext.slug)) {
                window.location.reload();
                return;
            }
        } else {
            toast.error(t('system.appStore.messages.toggleFailed', { action }));
        }
    } catch (err: unknown) {
        const errData = (err as { response?: { data?: { message?: string; data?: { code?: string } } } })?.response?.data;
        const axiosMessage = errData?.message;
        toast.error(axiosMessage || t('system.appStore.messages.toggleError', { action }));
        if (errData?.data?.code === 'missing_required_settings') {
            activeExtConfig.value = ext;
            rawSettingsJson.value = JSON.stringify(ext.settings || {}, null, 2);
            settingsModalOpen.value = true;
        }
    }
};

const cmsSlugsToActivate = () => inactiveCms.value.map((ext) => ext.slug);

const cmsSlugsToDeactivate = () => activeCms.value.map((ext) => ext.slug);

type InstallProfile = 'core' | 'cms' | 'cms_site';

interface InstallProfilePreview {
    profile?: string;
    will_activate?: Array<{ slug: string; name?: string }>;
    will_deactivate?: Array<{ slug: string; name?: string }>;
    warnings?: string[];
    blockers?: string[];
    can_apply?: boolean;
    noop?: boolean;
    site_active?: boolean;
    cms_active_count?: number;
}

const lifecycleBusy = ref(false);

const applyInstallProfile = async (profile: InstallProfile) => {
    if (lifecycleBusy.value) {
        toast.error(t('system.appStore.messages.lifecycleBusy'));
        return;
    }

    lifecycleBusy.value = true;
    let preview: InstallProfilePreview = {
        profile,
        will_activate: [],
        warnings: [],
        blockers: [],
        can_apply: true,
        noop: false,
        site_active: false,
        cms_active_count: 0,
    };

    try {
        const previewResponse = await api.get('/manage/infra/extensions/install-profile-preview', {
            params: { profile },
        });
        const raw = unwrapApiData<InstallProfilePreview>(previewResponse.data);
        if (raw && typeof raw === 'object') {
            preview = { ...preview, ...raw };
        }
    } catch {
        lifecycleBusy.value = false;
        toast.error(t('system.appStore.messages.installProfileFailed'));
        return;
    }

    if (preview.blockers && preview.blockers.length > 0) {
        lifecycleBusy.value = false;
        toast.error(t('system.appStore.messages.installProfileBlocked', {
            detail: preview.blockers.slice(0, 3).join('; '),
        }));
        return;
    }

    if (preview.noop && (preview.will_activate?.length ?? 0) === 0 && (preview.will_deactivate?.length ?? 0) === 0) {
        lifecycleBusy.value = false;
        toast.success(t('system.appStore.messages.installProfileNoop'));
        return;
    }

    const warningLines = (preview.warnings || [])
        .map((code) => {
            const key = `system.appStore.messages.installProfileWarnings.${code}`;
            return te(key) ? t(key) : code;
        })
        .filter(Boolean);

    const activateList = (preview.will_activate || []).map((row) => row.name || row.slug).join(', ');
    const deactivateList = (preview.will_deactivate || []).map((row) => row.name || row.slug).join(', ');
    const messageParts = [
        t(`system.appStore.messages.installProfileConfirm.${profile}`),
        activateList
            ? t('system.appStore.messages.installProfileWillActivate', { list: activateList })
            : null,
        deactivateList
            ? t('system.appStore.messages.installProfileWillDeactivate', { list: deactivateList })
            : null,
        ...warningLines,
        t('system.appStore.messages.installProfileEnforceNote'),
    ].filter(Boolean);

    const confirmed = await confirm({
        title: t('system.appStore.messages.installProfileTitle'),
        message: messageParts.join('\n\n'),
        variant: warningLines.length > 0 ? 'danger' : 'warning',
        confirmText: t('system.appStore.installProfileBtn'),
    });
    if (!confirmed) {
        lifecycleBusy.value = false;
        return;
    }

    try {
        const response = await api.post('/manage/infra/extensions/apply-install-profile', { profile });
        const payload = unwrapApiData<{
            profile?: string;
            activated?: string[];
            errors?: string[];
            themes?: { active?: string | null };
            preview?: InstallProfilePreview;
        }>(response.data);

        const errors = payload?.errors || [];
        if (errors.length > 0) {
            toast.error(t('system.appStore.messages.installProfilePartial', {
                detail: errors.slice(0, 3).join('; '),
            }));
        } else {
            toast.success(t('system.appStore.messages.installProfileSuccess', {
                profile: t(`system.appStore.installProfileNames.${profile}`),
                count: payload?.activated?.length ?? 0,
            }));
        }

        await fetchExtensions();
        await systemStore.fetchPublicSettings({ force: true });
        window.location.reload();
    } catch (err: unknown) {
        const status = (err as { response?: { status?: number } })?.response?.status;
        const axiosMessage = (err as { response?: { data?: { message?: string; data?: { errors?: string[]; code?: string } } } })?.response?.data;
        if (status === 423 || axiosMessage?.data?.code === 'lifecycle_busy') {
            toast.error(t('system.appStore.messages.lifecycleBusy'));
        } else {
            const detail = axiosMessage?.data?.errors?.slice(0, 3).join('; ') || axiosMessage?.message;
            toast.error(detail || t('system.appStore.messages.installProfileFailed'));
        }
        lifecycleBusy.value = false;
    }
};

const bulkActivateCms = async () => {
    if (lifecycleBusy.value) {
        toast.error(t('system.appStore.messages.lifecycleBusy'));
        return;
    }
    const slugs = cmsSlugsToActivate();
    lifecycleBusy.value = true;
    let plan: { will_activate?: Array<{ slug: string; name: string }>; can_cascade?: boolean } = {};
    try {
        const planResponse = await api.get('/manage/infra/extensions/activation-plan', {
            params: { family: 'cms', slugs },
        });
        const raw = planResponse.data as typeof plan & { data?: typeof plan };
        const planPayload = unwrapApiData<typeof plan>(raw) ?? raw;
        if (planPayload && typeof planPayload === 'object') {
            plan = planPayload;
        }
    } catch {
        toast.error(t('system.appStore.messages.bulkActivateCmsFailed'));
        lifecycleBusy.value = false;
        return;
    }

    const willActivate = plan.will_activate || [];
    if (willActivate.length === 0) {
        lifecycleBusy.value = false;
        if (slugs.length > 0) {
            toast.error(t('system.appStore.messages.bulkActivateCmsFailed'));
            return;
        }
        toast.success(t('system.appStore.messages.bulkActivateCmsEmpty'));
        return;
    }

    if (plan.can_cascade === false) {
        lifecycleBusy.value = false;
        toast.error(t('system.appStore.messages.lifecycleBlocked', {
            detail: willActivate.map((row) => row.name).join(', '),
        }));
        return;
    }

    const list = willActivate.map((row) => row.name).join(', ');
    const confirmed = await confirm({
        title: t('system.appStore.messages.bulkActivateCmsTitle'),
        message: t('system.appStore.messages.bulkActivateCmsMessage', { list }),
        variant: 'warning',
        confirmText: t('system.appStore.bulkActivateCms'),
    });
    if (!confirmed) {
        lifecycleBusy.value = false;
        return;
    }

    try {
        const response = await api.post('/manage/infra/extensions/bulk-activate', { family: 'cms', slugs });
        const payload = unwrapApiData<{ activated?: Array<{ slug?: string; name?: string }> }>(response.data);
        const activated = payload?.activated || [];
        if (activated.length === 0) {
            toast.error(t('system.appStore.messages.bulkActivateCmsFailed'));
            lifecycleBusy.value = false;
            return;
        }

        const activatedSlugs = new Set(activated.map((row) => row.slug).filter(Boolean));
        extensions.value = extensions.value.map((ext) => (
            activatedSlugs.has(ext.slug) ? { ...ext, status: 'active' as const } : ext
        ));

        const names = activated.map((row) => row.name).filter(Boolean).join(', ');
        toast.success(t('system.appStore.messages.bulkActivateCmsSuccess', {
            count: activated.length,
            list: names,
        }));
        await fetchExtensions();
        await systemStore.fetchPublicSettings({ force: true });
        window.location.reload();
    } catch (err: unknown) {
        const status = (err as { response?: { status?: number } })?.response?.status;
        const axiosMessage = (err as { response?: { data?: { message?: string; data?: { code?: string } } } })?.response?.data;
        if (status === 423 || axiosMessage?.data?.code === 'lifecycle_busy') {
            toast.error(t('system.appStore.messages.lifecycleBusy'));
        } else {
            toast.error(axiosMessage?.message || t('system.appStore.messages.bulkActivateCmsFailed'));
        }
        lifecycleBusy.value = false;
    }
};

const bulkDeactivateCms = async () => {
    if (lifecycleBusy.value) {
        toast.error(t('system.appStore.messages.lifecycleBusy'));
        return;
    }
    const slugs = cmsSlugsToDeactivate();
    lifecycleBusy.value = true;
    let plan: { will_deactivate?: Array<{ slug: string; name: string }> } = {};
    try {
        const planResponse = await api.get('/manage/infra/extensions/deactivation-plan', {
            params: { family: 'cms', slugs },
        });
        const raw = planResponse.data as typeof plan & { data?: typeof plan };
        const planPayload = unwrapApiData<typeof plan>(raw) ?? raw;
        if (planPayload && typeof planPayload === 'object') {
            plan = planPayload;
        }
    } catch {
        toast.error(t('system.appStore.messages.bulkDeactivateCmsFailed'));
        lifecycleBusy.value = false;
        return;
    }

    const willDeactivate = plan.will_deactivate || [];
    if (willDeactivate.length === 0) {
        lifecycleBusy.value = false;
        if (slugs.length > 0) {
            toast.error(t('system.appStore.messages.bulkDeactivateCmsFailed'));
            return;
        }
        toast.success(t('system.appStore.messages.bulkDeactivateCmsEmpty'));
        return;
    }

    const list = willDeactivate.map((row) => row.name).join(', ');
    const confirmed = await confirm({
        title: t('system.appStore.messages.bulkDeactivateCmsTitle'),
        message: t('system.appStore.messages.bulkDeactivateCmsMessage', { list }),
        variant: 'danger',
        confirmText: t('system.appStore.bulkDeactivateCms'),
    });
    if (!confirmed) {
        lifecycleBusy.value = false;
        return;
    }

    try {
        const response = await api.post('/manage/infra/extensions/bulk-deactivate', { family: 'cms', slugs });
        const payload = unwrapApiData<{ deactivated?: Array<{ slug?: string; name?: string }> }>(response.data);
        const deactivated = payload?.deactivated || [];
        if (deactivated.length === 0) {
            toast.error(t('system.appStore.messages.bulkDeactivateCmsFailed'));
            lifecycleBusy.value = false;
            return;
        }

        const deactivatedSlugs = new Set(deactivated.map((row) => row.slug).filter(Boolean));
        extensions.value = extensions.value.map((ext) => (
            deactivatedSlugs.has(ext.slug) ? { ...ext, status: 'inactive' as const } : ext
        ));

        const names = deactivated.map((row) => row.name).filter(Boolean).join(', ');
        toast.success(t('system.appStore.messages.bulkDeactivateCmsSuccess', {
            count: deactivated.length,
            list: names,
        }));
        await fetchExtensions();
        await systemStore.fetchPublicSettings({ force: true });
        window.location.reload();
    } catch (err: unknown) {
        const status = (err as { response?: { status?: number } })?.response?.status;
        const axiosMessage = (err as { response?: { data?: { message?: string; data?: { code?: string } } } })?.response?.data;
        if (status === 423 || axiosMessage?.data?.code === 'lifecycle_busy') {
            toast.error(t('system.appStore.messages.lifecycleBusy'));
        } else {
            toast.error(axiosMessage?.message || t('system.appStore.messages.bulkDeactivateCmsFailed'));
        }
        lifecycleBusy.value = false;
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
            await systemStore.fetchPublicSettings({ force: true });
        }
    } catch (_err: unknown) {
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
    // Prefer manifest-driven settings_route, then legacy hardcoded map
    const settingsRoute = typeof ext.settings?.settings_route === 'string'
        ? ext.settings.settings_route
        : null;

    const routeMap: Record<string, { name: string; query?: Record<string, string> } | string> = {
        core: { name: 'settings', query: { tab: 'system' } },
        system: { name: 'settings', query: { tab: 'system' } },
        security: { name: 'settings', query: { tab: 'security' } },
        infra: { name: 'settings', query: { tab: 'performance' } },
        ai: { name: 'settings', query: { tab: 'ai' } },
        media: { name: 'settings', query: { tab: 'media' } },
        analytics: { name: 'analytics' },
        publishing: { name: 'publishing-settings' },
        Jejakawan: { name: 'publishing-settings' },
        mail: { name: 'mail', query: { openSettings: 'true', tab: 'accounts' } },
        forms: '/dash/forms',
        library: consolePath('/library'),
        newsletter: consolePath('/newsletter'),
        member: { name: 'members.index' },
        search: { name: 'settings', query: { tab: 'system' } },
    };

    let target: { name: string; query?: Record<string, string> } | string | undefined;
    if (settingsRoute) {
        if (settingsRoute.startsWith('/')) {
            target = settingsRoute;
        } else if (settingsRoute === 'mail') {
            target = routeMap.mail;
        } else {
            target = routeMap[settingsRoute] ?? { name: settingsRoute };
        }
    } else {
        target = routeMap[ext.slug];
    }
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
    } catch (_e) {
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
    } catch (_err: unknown) {
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

const scaffoldPlugin = async (payload: Record<string, unknown>) => {
    scaffolding.value = true;
    scaffoldError.value = '';
    try {
        if (payload.install_locally) {
            const response = await api.post('/manage/infra/models/scaffold', payload);
            toast.success(response.data?.message || t('system.appStore.scaffolder.installedSuccess'));
            scaffolderModalOpen.value = false;
            await fetchExtensions();
        } else {
            const response = await api.post('/manage/infra/models/scaffold', payload, {
                responseType: 'blob'
            });
            const blob = new Blob([response.data], { type: 'application/zip' });
            const url = window.URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.setAttribute('download', `${String(payload.slug ?? 'plugin')}.zip`);
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            toast.success(t('system.appStore.scaffolder.downloadSuccess'));
            scaffolderModalOpen.value = false;
        }
    } catch (err: unknown) {
        const axiosErr = err as { response?: { data?: { message?: string } } };
        scaffoldError.value = axiosErr.response?.data?.message || t('system.appStore.scaffolder.scaffoldFailed');
        toast.error(scaffoldError.value);
    } finally {
        scaffolding.value = false;
    }
};

const canUpload = ref(true);
const canExport = ref(true);
const exportingExtension = ref<string | null>(null);

const fetchCapabilities = async () => {
    try {
        const response = await api.get('/manage/infra/extensions/capabilities');
        const payload = response.data?.data || response.data;
        if (payload) {
            if (typeof payload.can_upload === 'boolean') {
                canUpload.value = payload.can_upload;
            }
            if (typeof payload.can_export === 'boolean') {
                canExport.value = payload.can_export;
            }
        }
    } catch {
        // Keep defaults
    }
};

const exportExtension = async (ext: ExtensionItem) => {
    exportingExtension.value = ext.slug;
    try {
        const response = await api.get(`/manage/infra/extensions/${ext.slug}/export`, {
            responseType: 'blob',
        });
        const blob = new Blob([response.data], { type: 'application/zip' });
        const url = window.URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = `${ext.slug}-extension.zip`;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        window.URL.revokeObjectURL(url);
        toast.success(t('system.appStore.messages.exportSuccess'));
    } catch {
        toast.error(t('system.appStore.messages.exportFailed'));
    } finally {
        exportingExtension.value = null;
    }
};

onMounted(() => {
    fetchCapabilities();
    fetchExtensions();
});

defineExpose({
    getLocalizedDescription,
    toggleFeatureStatus,
    uninstallExtension,
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
