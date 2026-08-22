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

    <!-- 2-Column Main Workspace -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
      <!-- Left Column: Navigation Groups Sidebar -->
      <div class="lg:col-span-4 xl:col-span-3.5 flex flex-col space-y-3">
        <div class="p-3.5 rounded-2xl bg-card border border-border/60 shadow-xs space-y-3">
          <!-- Sidebar Section Title & Badge -->
          <div class="flex items-center justify-between px-1">
            <h3 class="text-xs font-bold uppercase tracking-wider text-muted-foreground flex items-center gap-2">
              <Layers class="w-3.5 h-3.5 text-primary" />
              <span>Menu Groups</span>
            </h3>
            <span class="text-[10px] font-semibold text-muted-foreground bg-muted px-2 py-0.5 rounded-full">
              {{ availableGroups.length - 1 }} Groups
            </span>
          </div>

          <!-- Group Search Filter Input -->
          <div class="relative">
            <Search class="w-3.5 h-3.5 absolute left-2.5 top-1/2 -translate-y-1/2 text-muted-foreground" />
            <Input
              v-model="groupSearchQuery"
              type="text"
              placeholder="Search groups..."
              class="pl-8 h-8 text-xs bg-muted/30 border-border/60 rounded-xl focus-visible:bg-background transition-colors"
            />
          </div>

          <!-- Vertical Groups List -->
          <div class="space-y-1 max-h-[calc(100vh-22rem)] overflow-y-auto custom-scrollbar pr-0.5">
            <button
              v-for="grp in filteredAvailableGroups"
              :key="grp.slug"
              :class="[
                'w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-medium transition-all text-left group cursor-pointer',
                selectedGroup === grp.slug
                  ? 'bg-primary text-primary-foreground font-semibold shadow-xs'
                  : 'text-muted-foreground hover:bg-muted hover:text-foreground'
              ]"
              @click="selectGroupFilter(grp.slug)"
            >
              <div class="flex items-center gap-2.5 min-w-0">
                <LucideIcon
                  :name="grp.icon"
                  :class="[
                    'w-4 h-4 shrink-0 transition-colors',
                    selectedGroup === grp.slug ? 'text-primary-foreground' : 'text-muted-foreground group-hover:text-foreground'
                  ]"
                />
                <span class="truncate">{{ grp.name }}</span>
              </div>
              <div class="flex items-center gap-1 shrink-0 ml-2">
                <span
                  v-if="getGroupCount(grp.slug) > 0"
                  :class="[
                    'px-2 py-0.5 rounded-md text-[10px] font-bold tracking-tight',
                    selectedGroup === grp.slug
                      ? 'bg-primary-foreground/20 text-primary-foreground'
                      : 'bg-muted text-muted-foreground group-hover:bg-background'
                  ]"
                >
                  {{ getGroupCount(grp.slug) }}
                </span>
                <ChevronRight
                  :class="[
                    'w-3.5 h-3.5 transition-transform opacity-60',
                    selectedGroup === grp.slug ? 'translate-x-0.5 text-primary-foreground' : 'group-hover:translate-x-0.5'
                  ]"
                />
              </div>
            </button>
          </div>
        </div>

        <!-- Live Sync Status Banner -->
        <div class="p-3.5 rounded-2xl bg-muted/20 border border-border/40 text-xs space-y-1 text-muted-foreground hidden lg:block">
          <p class="font-semibold text-foreground flex items-center gap-1.5">
            <Sparkles class="w-3.5 h-3.5 text-primary" />
            <span>Live Sync Active</span>
          </p>
          <p class="text-[11px] leading-relaxed text-muted-foreground/80">
            Changes made in this tree automatically synchronize with the live console sidebar in real time.
          </p>
        </div>
      </div>

      <!-- Right Column: Navigation Tree Workspace -->
      <div class="lg:col-span-8 xl:col-span-8.5 space-y-4">
        <div class="p-4 md:p-6 rounded-2xl bg-card border border-border/60 shadow-xs space-y-4">
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-border/40">
            <div>
              <div class="flex items-center gap-2">
                <h3 class="text-sm font-bold text-foreground flex items-center gap-2">
                  <LucideIcon :name="activeGroupMeta?.icon || 'menu'" class="w-4 h-4 text-primary" />
                  <span>{{ activeGroupMeta?.name || 'All Groups' }}</span>
                </h3>
                <span class="text-xs text-muted-foreground bg-muted px-2 py-0.5 rounded-md font-medium">
                  {{ filteredMenus.length }} items
                </span>
              </div>
              <p class="text-xs text-muted-foreground mt-0.5">
                {{ $t('system.navigation.menuEditor.dragInstructions') }}
              </p>
            </div>

            <!-- Top Level Un-nest Drop Area -->
            <div
              :class="[
                'px-3.5 py-1.5 rounded-xl border border-dashed text-xs text-muted-foreground transition-colors select-none text-center',
                isRootDropHover ? 'border-primary bg-primary/10 text-primary font-semibold' : 'border-border/60 hover:bg-muted/30'
              ]"
              @dragover.prevent="onRootDragOver"
              @dragleave="isRootDropHover = false"
              @drop.prevent="onRootDrop"
            >
              <span>Drop here to un-nest to Root level</span>
            </div>
          </div>

          <!-- Loading State -->
          <div v-if="loading" class="p-12 flex items-center justify-center">
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
import { ref, computed, onMounted } from 'vue';
import { PageHeader } from '@/shared/components/shell';
import {
  Menu,
  Plus,
  RotateCcw,
  Loader2,
  Layers,
  Search,
  ChevronRight,
  Sparkles,
} from 'lucide-vue-next';
import { Button, Input, LucideIcon } from '@/shared/components/ui';
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
const groupSearchQuery = ref('');

const filteredAvailableGroups = computed(() => {
    if (!groupSearchQuery.value.trim()) return availableGroups;
    const query = groupSearchQuery.value.toLowerCase();
    return availableGroups.filter(g => g.name.toLowerCase().includes(query) || g.slug.toLowerCase().includes(query));
});

const activeGroupMeta = computed(() => {
    return availableGroups.find(g => g.slug === selectedGroup.value) || availableGroups[0];
});

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
