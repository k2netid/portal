<template>
  <div class="h-full">
    <!-- Loading State -->
    <div
      v-if="isLoading"
      class="flex flex-col items-center justify-center py-24"
    >
      <Loader2 class="w-10 h-10 animate-spin text-muted-foreground mb-4" />
      <p class="text-muted-foreground">
        {{ t('layout.menus.messages.loading') }}
      </p>
    </div>

    <div
      v-else
      class="space-y-4"
    >
      <!-- Full Toolbar -->
      <Card>
        <CardContent class="p-2">
          <div class="flex flex-wrap items-center justify-between gap-4 min-w-0">
            <!-- Left: Menu Selector & New -->
            <div class="flex items-center gap-2">
              <Button
                variant="outline"
                size="sm"
                class="h-10 px-3 text-xs inline-flex items-center gap-2"
                @click="$emit('back-to-list')"
              >
                <ArrowLeft data-icon="inline-start" class="size-4 shrink-0" />
                {{ t('layout.menus.title') }}
              </Button>

              <Select v-model="trashedFilter">
                <SelectTrigger :aria-label="t('common.labels.status')" class="h-10 w-[130px] lg:w-auto lg:min-w-[130px] lg:max-w-[200px] text-sm flex-shrink-0">
                  <SelectValue :placeholder="t('common.labels.status')" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="without">
                    {{ t('common.labels.activeOnly') }}
                  </SelectItem>
                  <SelectItem
                    v-if="trashedCount > 0 || trashedFilter === 'with'"
                    value="with"
                  >
                    {{ t('common.labels.includesTrashed') }}
                  </SelectItem>
                  <SelectItem
                    v-if="trashedCount > 0 || trashedFilter === 'only'"
                    value="only"
                  >
                    {{ t('common.labels.trashedOnly') }}
                  </SelectItem>
                </SelectContent>
              </Select>
                            
              <Select v-model="selectedMenuIdLocal">
                <SelectTrigger
                  :aria-label="t('layout.menus.actions.selectMenu')"
                  class="h-10 w-[180px] lg:w-auto lg:min-w-[180px] lg:max-w-[300px] text-sm flex-1 truncate"
                >
                  <SelectValue :placeholder="t('layout.menus.actions.selectMenu')" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem 
                    v-for="m in menus" 
                    :key="m.id" 
                    :value="m.id.toString()"
                  >
                    {{ m.name }}
                  </SelectItem>
                </SelectContent>
              </Select>

              <Button 
                variant="ghost" 
                size="icon" 
                class="h-10 w-10"
                :aria-label="t('layout.menus.actions.create')"
                :title="t('layout.menus.actions.create')"
                @click="$emit('create-menu')"
              >
                <Plus class="w-4 h-4" />
              </Button>
            </div>

            <!-- Center: Name & Location -->
            <div class="flex items-center gap-3 border-l border-r border-border px-4 flex-1 min-w-0">
              <div class="flex items-center gap-2 flex-1 min-w-0">
                <Label class="text-xs text-muted-foreground whitespace-nowrap">{{ t('layout.menus.form.name') }}</Label>
                <Input 
                  v-model="menuName" 
                  class="h-10 w-full min-w-[150px] max-w-[300px] text-sm" 
                  :placeholder="t('layout.menus.form.namePlaceholder')"
                  :disabled="isTrashed || !menuId"
                />
              </div>
              <div class="flex items-center gap-2 flex-1 min-w-0">
                <Label class="text-xs text-muted-foreground whitespace-nowrap">{{ t('layout.menus.form.location') }}</Label>
                <Select
                  v-model="menuLocation"
                  :disabled="isTrashed || !menuId"
                >
                  <SelectTrigger
                    :aria-label="t('layout.menus.form.location')"
                    class="h-10 w-full min-w-[150px] max-w-[250px] text-sm truncate"
                  >
                    <SelectValue :placeholder="t('layout.menus.form.placeholders.selectLocation')" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem 
                      v-for="loc in locations" 
                      :key="loc.value" 
                      :value="loc.value"
                    >
                      {{ loc.label }}
                    </SelectItem>
                  </SelectContent>
                </Select>
              </div>
            </div>

            <!-- Right: Actions -->
            <div class="flex items-center gap-1">
              <Button 
                variant="ghost" 
                size="icon" 
                class="h-10 w-10"
                :disabled="!canUndo || isTrashed"
                :aria-label="t('common.actions.undo') + ' (Ctrl+Z)'"
                :title="t('common.actions.undo') + ' (Ctrl+Z)'"
                @click="undo"
              >
                <Undo2 class="w-4 h-4" />
              </Button>
              <Button 
                variant="ghost" 
                size="icon" 
                class="h-10 w-10"
                :disabled="!canRedo || isTrashed"
                :aria-label="t('common.actions.redo') + ' (Ctrl+Y)'"
                :title="t('common.actions.redo') + ' (Ctrl+Y)'"
                @click="redo"
              >
                <Redo2 class="w-4 h-4" />
              </Button>

              <div class="w-px h-6 bg-border mx-1" />

              <Button 
                variant="ghost" 
                size="icon" 
                class="h-10 w-10"
                :aria-label="t('layout.menus.actions.preview')"
                :title="t('layout.menus.actions.preview')"
                :disabled="items.length === 0"
                @click="showPreview = true"
              >
                <Eye class="w-4 h-4" />
              </Button>

              <Button 
                variant="ghost" 
                size="icon" 
                class="h-10 w-10"
                :aria-label="t('common.actions.refresh')"
                :title="t('common.actions.refresh')"
                @click="menuState.fetchMenu()"
              >
                <RotateCcw class="w-4 h-4" />
              </Button>

              <Button 
                :disabled="isSaving || !isDirty || isTrashed" 
                variant="ghost"
                size="icon"
                class="h-10 w-10"
                :class="{ 'text-primary': isDirty && !isTrashed }"
                :aria-label="isSaving ? t('layout.menus.actions.saving') : t('layout.menus.actions.save')"
                :title="isSaving ? t('layout.menus.actions.saving') : t('layout.menus.actions.save')"
                @click="handleSave"
              >
                <Loader2
                  v-if="isSaving"
                  class="w-4 h-4 animate-spin"
                />
                <Save
                  v-else
                  class="w-4 h-4"
                />
              </Button>

              <div
                v-if="isTrashed"
                class="w-px h-6 bg-border mx-1"
              />

              <Button 
                v-if="isTrashed"
                variant="ghost"
                size="icon"
                class="h-9 w-9 text-emerald-600 hover:text-emerald-700 hover:bg-emerald-50"
                :aria-label="t('common.actions.restore')"
                :title="t('common.actions.restore')"
                @click="$emit('restore-menu')"
              >
                <RotateCcw class="w-4 h-4" />
              </Button>

              <Button 
                variant="ghost"
                size="icon"
                class="h-9 w-9 text-destructive hover:text-destructive hover:bg-destructive/10"
                :aria-label="isTrashed ? t('common.actions.forceDelete') : t('layout.menus.actions.delete')"
                :title="isTrashed ? t('common.actions.forceDelete') : t('layout.menus.actions.delete')"
                @click="$emit('delete-menu')"
              >
                <Trash2 class="w-4 h-4" />
              </Button>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Empty State when no menu selected -->
      <div
        v-if="!menuId"
        class="flex flex-col items-center justify-center p-12 border-2 border-dashed rounded-lg bg-muted/10 h-[600px]"
      >
        <MenuSquare class="w-16 h-16 text-muted-foreground/20 mb-4" />
        <h3 class="text-lg font-medium text-foreground mb-2">
          {{ t('layout.menus.messages.noMenuSelected') }}
        </h3>
        <p class="text-sm text-muted-foreground max-w-sm text-center mb-6">
          {{ menus.length === 0 ? t('layout.menus.messages.empty') : t('layout.menus.actions.selectMenu') }}
        </p>
        <Button size="sm" class="h-10 inline-flex items-center gap-2" @click="$emit('create-menu')">
          <Plus data-icon="inline-start" class="size-4 shrink-0" />
          {{ t('layout.menus.actions.create') }}
        </Button>
      </div>

      <!-- Main Layout -->
      <div
        v-else
        class="flex gap-6 items-start h-full"
        :class="{ 'opacity-60 pointer-events-none': isTrashed }"
      >
        <!-- Left: Source Panel -->
        <div 
          class="transition-[width] duration-300 shrink-0"
          :style="{ width: isSidebarCollapsed ? '48px' : '300px' }"
        >
          <Button
            v-if="isSidebarCollapsed"
            variant="outline"
            size="icon"
            class="w-full h-10"
            :aria-label="t('common.actions.expand')"
            :title="t('common.actions.expand')"
            @click="isSidebarCollapsed = false"
          >
            <PanelLeftOpen class="w-4 h-4" />
          </Button>
          <SourcePanel 
            v-else 
            @collapse="isSidebarCollapsed = true" 
          />
        </div>

        <!-- Center: Menu Tree -->
        <div class="flex-1 min-w-0">
          <Card>
            <CardHeader class="pb-3">
              <div class="flex items-center justify-between">
                <CardTitle class="text-base">
                  {{ t('layout.menus.form.menuStructure') }}
                </CardTitle>
                <Badge variant="secondary">
                  {{ items.length }} {{ t('layout.menus.headers.items') }}
                </Badge>
              </div>
            </CardHeader>
            <CardContent>
              <MenuTree :items="items" />
            </CardContent>
          </Card>
        </div>

        <!-- Resizer Handle -->
        <div 
          v-if="!isPropertiesCollapsed"
          class="w-1.5 hover:w-1.5 bg-transparent hover:bg-primary/30 cursor-col-resize self-stretch transition-colors relative z-10"
          @mousedown="startResizing"
        >
          <div class="absolute inset-y-0 left-1/2 -translate-x-1/2 w-px bg-border/50 group-hover:bg-primary/50" />
        </div>

        <!-- Right: Properties Panel -->
        <div 
          class="sticky top-4 transition-[width] duration-300 shrink-0"
          :style="{ width: isPropertiesCollapsed ? '48px' : propertiesWidth + 'px' }"
        >
          <Button 
            v-if="isPropertiesCollapsed"
            variant="outline" 
            size="icon"
            class="w-full h-10"
            :aria-label="t('common.actions.expand')"
            :title="t('common.actions.expand')"
            @click="isPropertiesCollapsed = false"
          >
            <PanelRightOpen class="w-4 h-4" />
          </Button>
          <ItemPropertiesPanel 
            v-else 
            @collapse="isPropertiesCollapsed = true" 
          />
        </div>
      </div>
    </div>

    <!-- Preview Modal -->
    <MenuPreview
      v-model:open="showPreview"
      :items="items"
    />
  </div>
</template>

<script setup lang="ts">
import { logger } from '@/shared/utils/logger';
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '@/engine/api/client';

import { useMenu, provideMenu } from '@/modules/Layout/composables/useMenu';
import { useToast } from '@/shared/composables/useToast';
import type { Menu } from '@/modules/Layout/types/menu';

// Modular Components
import SourcePanel from './sidebar/SourcePanel.vue';
import MenuTree from './canvas/MenuTree.vue';
import ItemPropertiesPanel from './properties/ItemPropertiesPanel.vue';
import MenuPreview from './preview/MenuPreview.vue';

// UI Components
import Card from '@/shared/components/ui/Card.vue';
import CardHeader from '@/shared/components/ui/CardHeader.vue';
import CardTitle from '@/shared/components/ui/CardTitle.vue';
import CardContent from '@/shared/components/ui/CardContent.vue';
import Badge from '@/shared/components/ui/Badge.vue';
import Button from '@/shared/components/ui/Button.vue';
import Input from '@/shared/components/ui/Input.vue';
import Label from '@/shared/components/ui/Label.vue';
import Select from '@/shared/components/ui/Select.vue';
import SelectTrigger from '@/shared/components/ui/SelectTrigger.vue';
import SelectValue from '@/shared/components/ui/SelectValue.vue';
import SelectContent from '@/shared/components/ui/SelectContent.vue';
import SelectItem from '@/shared/components/ui/SelectItem.vue';

import {
  ArrowLeft,
  Eye,
  Loader2,
  MenuSquare,
  PanelLeftOpen,
  PanelRightOpen,
  Plus,
  Redo2,
  RotateCcw,
  Save,
  Trash2,
  Undo2,
} from 'lucide-vue-next';

interface Props {
    menuId: string;
    menus?: Menu[];
    trashedFilter?: string;
    isTrashed?: boolean;
    trashedCount?: number;
}

const props = withDefaults(defineProps<Props>(), {
    menus: () => [],
    trashedFilter: 'without',
    isTrashed: false,
    trashedCount: 0
});

const emit = defineEmits<{
    (e: 'menu-updated'): void;
    (e: 'create-menu'): void;
    (e: 'delete-menu'): void;
    (e: 'restore-menu'): void;
    (e: 'select-menu', id: string): void;
    (e: 'update:trashedFilter', val: string): void;
    (e: 'back-to-list'): void;
}>();

const { t } = useI18n();
const toast = useToast();

// Initialize useMenu composable
// We need a ref that mirrors props.menuId but can change
const menuIdRef = ref(props.menuId);
const menuState = useMenu(menuIdRef);

// Provide context to child components
provideMenu(menuState);

// Destructure for template
const {
    menu,
    items,
    isLoading,
    isSaving,
    isDirty,
    canUndo,
    canRedo,
    undo,
    redo,
    saveMenu
} = menuState;

// Local state
const isSidebarCollapsed = ref(false);
const isPropertiesCollapsed = ref(false);
const showPreview = ref(false);

interface LocationOption {
    value: string;
    label: string;
}
const locations = ref<LocationOption[]>([]);


// Draggable Resize State
const propertiesWidth = ref(400); // Default width
const isResizing = ref(false);

const startResizing = (_e: MouseEvent) => {
    isResizing.value = true;
    document.body.style.cursor = 'col-resize';
    document.body.style.userSelect = 'none';
};

const stopResizing = () => {
    isResizing.value = false;
    document.body.style.cursor = '';
    document.body.style.userSelect = '';
};

const doResizing = (e: MouseEvent) => {
    if (!isResizing.value) return;
    
    // Calculate new width based on mouse position from the right
    const newWidth = window.innerWidth - e.clientX - 24; // 24 is approximate gap/margin
    
    // Constraints
    if (newWidth > 250 && newWidth < 800) {
        propertiesWidth.value = newWidth;
    }
};

// Computed for menu selector with emit
const selectedMenuIdLocal = computed({
    get: () => props.menuId?.toString() || '',
    set: (val) => emit('select-menu', val)
});

// Computed properties for v-model binding
const menuName = computed({
    get: () => menu.value?.name || '',
    set: (val) => {
        if (!menu.value) return;
        menu.value.name = val;
    }
});

const menuLocation = computed({
    get: () => menu.value?.location || '',
    set: (val) => {
        if (!menu.value) return;
        menu.value.location = val;
    }
});

// Computed for trashedFilter with v-model emit
const trashedFilter = computed({
    get: () => props.trashedFilter,
    set: (val) => emit('update:trashedFilter', val)
});

// Fetch locations
const fetchLocations = async () => {
    try {
        const response = await api.get('/manage/layout/themes/active/locations');
        const data = response.data || {};
        locations.value = Object.entries(data).map(([key, label]) => ({
            value: key,
            label: label as string
        }));
        locations.value.unshift({ value: 'none', label: t('layout.menus.form.options.none') });
    } catch (error) {
        logger.error('Failed to fetch locations:', error);
    }
};

// Save handler
const handleSave = async () => {
    const success = await saveMenu({
        name: menuName.value,
        location: menuLocation.value
    });
    
    if (success) {
        toast.success.update(t('layout.menus.title'));
        emit('menu-updated');
    } else {
        toast.error.action(t('layout.menus.messages.saveFailed'));
    }
};

// Keyboard shortcuts
const handleKeydown = (e: KeyboardEvent) => {
    if ((e.ctrlKey || e.metaKey) && e.key === 'z') {
        e.preventDefault();
        if (e.shiftKey) {
            redo();
        } else {
            undo();
        }
    }
    if ((e.ctrlKey || e.metaKey) && e.key === 'y') {
        e.preventDefault();
        redo();
    }
    if ((e.ctrlKey || e.metaKey) && e.key === 's') {
        e.preventDefault();
        if (isDirty.value) {
            handleSave();
        }
    }
};

// Watch for menuId changes
watch(() => props.menuId, (newId) => {
    menuIdRef.value = newId;
});

// Expose methods/state for parent component (Index.vue)
defineExpose({
    // Save
    saveMenu: handleSave,
    fetchMenu: menuState.fetchMenu,
    isDirty,
    saving: isSaving,
    // Menu settings
    menuName,
    menuLocation,
    locations,
    // Undo/Redo
    undo,
    redo,
    canUndo,
    canRedo
});

onMounted(() => {
    fetchLocations();
    window.addEventListener('keydown', handleKeydown);
    window.addEventListener('mousemove', doResizing);
    window.addEventListener('mouseup', stopResizing);
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleKeydown);
    window.removeEventListener('mousemove', doResizing);
    window.removeEventListener('mouseup', stopResizing);
});
</script>
