<template>
  <div class="console-page min-w-0 max-w-full flex flex-col space-y-6">
    <!-- Page Header -->
    <PageHeader
      :title="$t('system.navigation.menuEditor.title')"
      :subtitle="$t('system.navigation.menuEditor.subtitle')"
      borderless
    >
      <template #actions>
        <div class="flex items-center gap-2">
          <!-- Reset to Default Button -->
          <Button
            variant="outline"
            size="sm"
            class="h-8 text-xs gap-1.5 border-border/60 hover:border-destructive/40 hover:text-destructive shadow-xs transition-colors"
            :disabled="saving || loading"
            @click="handleResetConfirm"
          >
            <RotateCcw class="w-3.5 h-3.5" />
            <span>{{ $t('system.navigation.menuEditor.resetDefaults') }}</span>
          </Button>

          <!-- Add Item Button -->
          <Button
            size="sm"
            class="h-8 text-xs font-semibold gap-1.5 px-3 shadow-xs"
            @click="openCreateModal"
          >
            <Plus class="w-3.5 h-3.5" />
            <span>{{ $t('system.navigation.menuEditor.addItem') }}</span>
          </Button>
        </div>
      </template>
    </PageHeader>

    <!-- Group Filter Tabs Bar -->
    <div class="flex items-center gap-1.5 p-1 rounded-xl bg-card border border-border/60 shadow-xs overflow-x-auto custom-scrollbar">
      <button
        v-for="grp in availableGroups"
        :key="grp.slug"
        :class="[
          'px-3 py-1.5 rounded-lg text-xs font-medium transition-all shrink-0 flex items-center gap-2',
          selectedGroup === grp.slug
            ? 'bg-primary text-primary-foreground font-semibold shadow-xs'
            : 'text-muted-foreground hover:bg-muted hover:text-foreground'
        ]"
        @click="selectGroupFilter(grp.slug)"
      >
        <LucideIcon :name="grp.icon" class="w-3.5 h-3.5" />
        <span>{{ grp.name }}</span>
        <span
          v-if="getGroupCount(grp.slug) > 0"
          :class="[
            'px-1.5 py-0.2 rounded text-[10px] font-bold',
            selectedGroup === grp.slug ? 'bg-primary-foreground/20 text-primary-foreground' : 'bg-muted text-muted-foreground'
          ]"
        >
          {{ getGroupCount(grp.slug) }}
        </span>
      </button>
    </div>

    <!-- Main Navigation Tree Area -->
    <div class="p-4 md:p-6 rounded-2xl bg-card border border-border/60 shadow-xs space-y-4">
      <div class="flex items-center justify-between">
        <div>
          <h3 class="text-xs font-bold uppercase tracking-wider text-muted-foreground flex items-center gap-2">
            <Menu class="w-4 h-4 text-primary" />
            <span>{{ $t('system.navigation.menuEditor.navigationTree') }}</span>
          </h3>
          <p class="text-xs text-muted-foreground mt-0.5">
            {{ $t('system.navigation.menuEditor.dragInstructions') }}
          </p>
        </div>

        <!-- Top Level Un-nest Drop Area -->
        <div
          :class="[
            'px-3 py-1 rounded-lg border border-dashed text-xs text-muted-foreground transition-colors select-none',
            isRootDropHover ? 'border-primary bg-primary/10 text-primary font-semibold' : 'border-border/60'
          ]"
          @dragover.prevent="onRootDragOver"
          @dragleave="isRootDropHover = false"
          @drop.prevent="onRootDrop"
        >
          <span>Drop here to un-nest to Root level</span>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="p-8 flex items-center justify-center">
        <Loader2 class="w-7 h-7 animate-spin text-primary" />
      </div>

      <!-- Empty State -->
      <div
        v-else-if="filteredMenus.length === 0"
        class="h-48 flex flex-col items-center justify-center p-6 text-center text-muted-foreground space-y-2 border border-dashed rounded-xl border-border/60"
      >
        <Menu class="w-8 h-8 opacity-30 stroke-[1.5]" />
        <p class="text-xs font-semibold">{{ $t('system.navigation.menuEditor.noMenus') }}</p>
        <p class="text-[11px] text-muted-foreground/70 max-w-sm">{{ $t('system.navigation.menuEditor.noMenusDesc') }}</p>
      </div>

      <!-- Tree List -->
      <div v-else class="space-y-2">
        <MenuTreeItem
          v-for="menuItem in filteredMenus"
          :key="menuItem.id"
          :menu="menuItem"
          :dragged-id="draggedId"
          @edit="openEditModal"
          @delete="handleDelete"
          @drag-start="handleDragStart"
          @drag-end="handleDragEnd"
          @drop-item="handleDropItem"
        />
      </div>
    </div>

    <!-- Create / Edit Menu Modal -->
    <MenuEditModal
      :is-open="isEditModalOpen"
      :menu="editingMenu"
      :root-menus="menus"
      :saving="saving"
      @close="isEditModalOpen = false"
      @save="handleSave"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { PageHeader } from '@/shared/components/shell';
import {
  Menu,
  Plus,
  RotateCcw,
  Loader2,
} from 'lucide-vue-next';
import { Button, LucideIcon } from '@/shared/components/ui';
import MenuTreeItem from '@/modules/Core/System/components/menus/MenuTreeItem.vue';
import MenuEditModal from '@/modules/Core/System/components/menus/MenuEditModal.vue';
import { useConsoleMenu, type ConsoleMenuItem } from '@/modules/Core/System/composables/useConsoleMenu';

const {
    menus,
    filteredMenus,
    loading,
    saving,
    selectedGroup,
    availableGroups,
    fetchMenus,
    saveMenu,
    deleteMenu,
    reorderMenus,
    resetDefaults,
} = useConsoleMenu();

const isEditModalOpen = ref(false);
const editingMenu = ref<ConsoleMenuItem | null>(null);
const draggedId = ref<string | null>(null);
const isRootDropHover = ref(false);

onMounted(() => {
    fetchMenus();
});

const selectGroupFilter = (groupSlug: string) => {
    selectedGroup.value = groupSlug;
    fetchMenus(groupSlug);
};

const getGroupCount = (groupSlug: string): number => {
    if (groupSlug === 'all') return menus.value.length;
    return menus.value.filter(m => m.group_slug === groupSlug).length;
};

const openCreateModal = () => {
    editingMenu.value = null;
    isEditModalOpen.value = true;
};

const openEditModal = (menu: ConsoleMenuItem) => {
    editingMenu.value = menu;
    isEditModalOpen.value = true;
};

const handleSave = async (payload: Partial<ConsoleMenuItem>, id?: string) => {
    const success = await saveMenu(payload, id);
    if (success) {
        isEditModalOpen.value = false;
    }
};

const handleDelete = async (id: string) => {
    if (confirm('Are you sure you want to delete this menu item? Any nested sub-menu items will also be removed.')) {
        await deleteMenu(id);
    }
};

const handleResetConfirm = async () => {
    if (confirm('Are you sure you want to reset all console navigation menus to system factory defaults? Any custom menus will be reset.')) {
        await resetDefaults();
    }
};

const handleDragStart = (id: string) => {
    draggedId.value = id;
};

const handleDragEnd = () => {
    draggedId.value = null;
    isRootDropHover.value = false;
};

const onRootDragOver = () => {
    if (draggedId.value) {
        isRootDropHover.value = true;
    }
};

const onRootDrop = async () => {
    if (!draggedId.value) return;

    // Un-nest dragged item to root
    const flatItems: Array<{ id: string; parent_id?: string | null; order: number; group_slug?: string }> = [];
    let curOrder = 1;

    menus.value.forEach((m) => {
        if (m.id === draggedId.value) {
            flatItems.push({ id: m.id, parent_id: null, order: curOrder++ });
        } else {
            flatItems.push({ id: m.id, parent_id: m.parent_id, order: curOrder++ });
        }
    });

    await reorderMenus(flatItems);
    handleDragEnd();
};

const handleDropItem = async ({ draggedId: dragId, targetId }: { draggedId: string; targetId: string }) => {
    if (dragId === targetId) return;

    // Nest dragged item into target item
    const flatItems: Array<{ id: string; parent_id?: string | null; order: number }> = [];
    let curOrder = 1;

    menus.value.forEach((m) => {
        if (m.id === dragId) {
            flatItems.push({ id: m.id, parent_id: targetId, order: curOrder++ });
        } else {
            flatItems.push({ id: m.id, parent_id: m.parent_id, order: curOrder++ });
        }
    });

    await reorderMenus(flatItems);
    handleDragEnd();
};
</script>
