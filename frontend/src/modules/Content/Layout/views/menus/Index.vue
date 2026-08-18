<template>
  <div class="console-page min-w-0 max-w-full flex flex-col">
    <!-- Header - Clean, just title -->
        <PageHeader
      :title="t('layout.menus.title')"
      :subtitle="t('layout.menus.subtitle')"
      borderless
>
      <template #actions>
        <div class="inline-flex items-center gap-1 rounded-lg border border-border/50 bg-muted/30 p-1">
                <Button
                  variant="ghost"
                  size="sm"
                  class="h-8 px-3 text-xs inline-flex items-center gap-1.5"
                  :class="viewMode === 'list' ? 'bg-background shadow-sm' : ''"
                  @click="viewMode = 'list'"
                >
                  <List data-icon="inline-start" class="size-3.5 shrink-0" />
                  {{ t('layout.menus.actions.listView') }}
                </Button>
                <Button
                  variant="ghost"
                  size="sm"
                  class="h-8 px-3 text-xs inline-flex items-center gap-1.5"
                  :class="viewMode === 'builder' ? 'bg-background shadow-sm' : ''"
                  :disabled="!selectedMenuId"
                  @click="viewMode = 'builder'"
                >
                  <Edit3 data-icon="inline-start" class="size-3.5 shrink-0" />
                  {{ t('layout.menus.actions.builderView') }}
                </Button>
              </div>
      </template>
    </PageHeader>

    <!-- Main Content Area -->
    <div
      v-if="isLoading && !selectedMenuId && viewMode === 'builder'"
      class="flex-1 flex items-center justify-center min-h-[400px]"
    >
      <Loader2 class="w-8 h-8 animate-spin text-muted-foreground" />
    </div>

    <div
      v-else
      class="min-w-0 flex-1"
    >
      <MenuList
        v-if="viewMode === 'list'"
        @select-menu="handleSelectMenu"
        @create-menu="openCreateModal"
      />
      <MenuBuilder
        v-else
        :key="selectedMenuId!"
        :menu-id="selectedMenuId!"
        :menus="(menus as any[])"
        :trashed-filter="trashedFilter"
        :trashed-count="trashedCount"
        :is-trashed="!!selectedMenu?.deleted_at"
        @menu-updated="handleMenuUpdated"
        @create-menu="openCreateModal"
        @delete-menu="deleteCurrentMenu"
        @restore-menu="restoreCurrentMenu"
        @select-menu="handleSelectMenu"
        @update:trashed-filter="trashedFilter = $event"
        @back-to-list="viewMode = 'list'"
      />
    </div>

    <!-- Create Menu Modal -->
    <MenuModal
      v-if="showCreateModal"
      @close="showCreateModal = false"
      @saved="handleMenuCreated"
    />
  </div>
</template>

<script setup lang="ts">
import { PageHeader } from '@/shared/components/shell';

import { logger } from '@/shared/utils/logger';
import { ref, onMounted, watch, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { useLayoutStore } from '@/modules/Content/Layout/stores/layout';
import { useToast } from '@/shared/composables/useToast';
import { useConfirm } from '@/shared/composables/useConfirm';
import { useMenuDeleteConfirm } from '@/modules/Content/Layout/composables/useMenuDeleteConfirm';
import MenuBuilder from '@/modules/Content/Layout/components/menus/MenuBuilder.vue';
import MenuList from '@/modules/Content/Layout/components/menus/MenuList.vue';
import MenuModal from '@/modules/Content/Layout/components/menus/MenuModal.vue';
import { Button } from '@/shared/components/ui';
import {
  Edit3,
  List,
  Loader2,
} from 'lucide-vue-next';

const { t } = useI18n();
const toast = useToast();
const { confirm } = useConfirm();
const { confirmMenuDelete } = useMenuDeleteConfirm();
const layoutStore = useLayoutStore();

interface Menu {
    id: string;
    name: string;
    deleted_at?: string | null;
}

const showCreateModal = ref(false);
const selectedMenuId = ref<string | null>(null);
const trashedFilter = ref('without');
const viewMode = ref<'list' | 'builder'>('list');

// Computed
const menus = computed(() => layoutStore.menuList as Menu[]);
const isLoading = computed(() => layoutStore.loading);
const trashedCount = computed(() => layoutStore.trashedCount);
const selectedMenu = computed(() => {
    return menus.value.find(m => m.id === selectedMenuId.value);
});

// Fetch all menus
const fetchMenus = async () => {
    try {
        await layoutStore.fetchAllMenus({
            trashed: trashedFilter.value,
            per_page: 100
        });
    } catch (error: unknown) {
        toast.error.action(t('layout.menus.messages.loadingFailed') || 'Failed to load menus');
    }
};

const openCreateModal = () => {
    showCreateModal.value = true;
};

const handleMenuCreated = async (newMenu: { id?: string }) => {
    showCreateModal.value = false;
    await fetchMenus();
    if (newMenu && newMenu.id) {
        selectedMenuId.value = String(newMenu.id);
        viewMode.value = 'builder';
    }
};

const handleSelectMenu = (menuId: string) => {
    selectedMenuId.value = String(menuId);
    viewMode.value = 'builder';
};

const handleMenuUpdated = async () => {
    const currentId = selectedMenuId.value;
    await fetchMenus();
    selectedMenuId.value = currentId;
};

const deleteCurrentMenu = async () => {
    if (!selectedMenu.value || !selectedMenuId.value) return;
    const isTrashed = !!selectedMenu.value.deleted_at;
    const mode = isTrashed ? 'force' : 'soft';

    const { confirmed, blocked } = await confirmMenuDelete(
        selectedMenuId.value,
        selectedMenu.value.name,
        mode,
    );

    if (!confirmed) return;

    try {
        await layoutStore.deleteMenu(selectedMenuId.value, isTrashed);
        toast.success.delete(t('layout.menus.title'));
        selectedMenuId.value = null;
        await fetchMenus();
    } catch (error: unknown) {
        logger.error('Error deleting menu:', error);
        if (!blocked) {
            toast.error.delete(error, t('layout.menus.title'));
        }
    }
};

const restoreCurrentMenu = async () => {
    if (!selectedMenu.value || !selectedMenuId.value) return;

     const confirmed = await confirm({
        title: t('common.actions.restore'),
        message: t('layout.menus.messages.restoreConfirm', { name: selectedMenu.value.name }),
        variant: 'info',
        confirmText: t('common.actions.restore'),
    });

    if (!confirmed) return;

    try {
        await layoutStore.restoreMenu(selectedMenuId.value);
        toast.success.restore(t('layout.menus.title'));
        await fetchMenus();
    } catch (error: unknown) {
         logger.error('Error restoring menu:', error);
        toast.error.fromResponse(error);
    }
};

onMounted(() => {
    fetchMenus();
});

watch(trashedFilter, () => {
    selectedMenuId.value = null;
    fetchMenus();
});
</script>
