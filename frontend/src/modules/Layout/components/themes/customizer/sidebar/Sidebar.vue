<template>
  <aside
    :class="[
      'border-r border-border bg-card flex flex-col shrink-0 z-10 transition-all duration-300',
      sidebarCollapsed ? 'w-16' : 'w-72'
    ]"
  >
    <!-- Sidebar Header -->
    <div class="h-14 px-3.5 border-b border-border flex items-center justify-between shrink-0">
      <div
        v-if="!sidebarCollapsed"
        class="flex items-center gap-2"
      >
        <Sliders class="w-4 h-4 text-primary" />
        <span class="text-xs font-bold uppercase tracking-wider text-muted-foreground">
          {{ t('publishing.theme_customizer.sidebar.title', 'Customizer Menu') }}
        </span>
      </div>
      <Button
        variant="ghost"
        size="icon"
        class="h-8 w-8 rounded-lg text-muted-foreground hover:text-foreground"
        :class="{ 'mx-auto': sidebarCollapsed }"
        :aria-label="sidebarCollapsed ? t('common.actions.expand', 'Perluas') : t('common.actions.collapse', 'Kecilkan')"
        :title="sidebarCollapsed ? t('common.actions.expand', 'Perluas') : t('common.actions.collapse', 'Kecilkan')"
        @click="emit('update:sidebarCollapsed', !sidebarCollapsed)"
      >
        <ChevronsRight
          v-if="sidebarCollapsed"
          class="w-4 h-4"
        />
        <ChevronsLeft
          v-else
          class="w-4 h-4"
        />
      </Button>
    </div>

    <!-- Search Box (Expanded Only) -->
    <div
      v-if="!sidebarCollapsed"
      class="p-3 border-b border-border/60 bg-muted/10"
    >
      <div class="relative">
        <Search class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-muted-foreground" />
        <input
          :value="searchQuery"
          type="search"
          :aria-label="t('publishing.theme_customizer.sidebar.search_placeholder', 'Cari pengaturan...')"
          :placeholder="t('publishing.theme_customizer.sidebar.search_placeholder', 'Cari pengaturan...')"
          class="w-full pl-8 pr-3 py-1.5 bg-background border border-border/80 rounded-xl text-xs placeholder:text-muted-foreground focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all"
          @input="emit('update:searchQuery', ($event.target as HTMLInputElement).value)"
        >
      </div>
    </div>

    <!-- Navigation List (Expanded) -->
    <div
      v-if="!sidebarCollapsed"
      class="flex-1 overflow-y-auto custom-scrollbar px-2.5 py-3 space-y-5"
    >
      <div
        v-for="group in groups"
        :key="group.id"
        class="space-y-1"
      >
        <!-- Group Accordion Header -->
        <button
          type="button"
          class="w-full flex items-center justify-between px-2.5 py-1 text-[11px] font-bold uppercase tracking-wider text-muted-foreground hover:text-foreground transition-colors group rounded-lg hover:bg-accent/40"
          @click="emit('toggleGroup', group.id)"
        >
          <span class="truncate">{{ group.label }}</span>
          <ChevronDown
            class="w-3.5 h-3.5 shrink-0 transition-transform duration-200"
            :class="{ '-rotate-90': collapsedGroups.includes(group.id) }"
          />
        </button>

        <!-- Group Items -->
        <div
          v-show="!collapsedGroups.includes(group.id)"
          class="space-y-0.5 pt-0.5"
        >
          <button
            v-for="item in group.items"
            :key="item.id"
            type="button"
            class="w-full text-left px-2.5 py-2 rounded-xl text-xs font-medium transition-all flex items-center gap-2.5 group relative"
            :class="activeItemId === item.id
              ? 'bg-primary/10 text-primary font-semibold border border-primary/20 shadow-sm'
              : 'hover:bg-accent text-muted-foreground hover:text-foreground'"
            @click="emit('selectItem', item)"
          >
            <component
              :is="item.icon"
              class="w-4 h-4 shrink-0 transition-transform group-hover:scale-105"
              :class="activeItemId === item.id ? 'text-primary' : 'text-muted-foreground group-hover:text-foreground'"
            />
            <span class="truncate">{{ item.label }}</span>
            <div
              v-if="activeItemId === item.id"
              class="ml-auto flex items-center"
            >
              <div class="w-1.5 h-1.5 rounded-full bg-primary" />
            </div>
            <span
              v-else-if="item.hasBinding"
              class="ml-auto w-1.5 h-1.5 rounded-full bg-primary/40"
            />
          </button>
        </div>
      </div>
    </div>

    <!-- Mini Navigation List (Collapsed) -->
    <div
      v-else
      class="flex-1 overflow-y-auto custom-scrollbar px-2 py-3 space-y-1.5"
    >
      <TooltipProvider
        v-for="item in flatItems"
        :key="`mini-${item.id}`"
      >
        <Tooltip>
          <TooltipTrigger as-child>
            <button
              type="button"
              class="w-full h-10 rounded-xl flex items-center justify-center transition-all relative"
              :aria-label="item.label"
              :class="activeItemId === item.id
                ? 'bg-primary/10 text-primary border border-primary/20 shadow-sm'
                : 'text-muted-foreground hover:text-foreground hover:bg-accent'"
              @click="emit('selectItem', item)"
            >
              <component
                :is="item.icon"
                class="w-4 h-4"
              />
              <span
                v-if="activeItemId === item.id"
                class="absolute right-1.5 top-1.5 w-1.5 h-1.5 rounded-full bg-primary"
              />
            </button>
          </TooltipTrigger>
          <TooltipContent
            side="right"
            :side-offset="8"
          >
            <p class="font-semibold text-xs">{{ item.label }}</p>
            <p
              v-if="item.description"
              class="text-[10px] text-muted-foreground"
            >
              {{ item.description }}
            </p>
          </TooltipContent>
        </Tooltip>
      </TooltipProvider>
    </div>
  </aside>
</template>

<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import {
  Button,
  Tooltip,
  TooltipTrigger,
  TooltipContent,
  TooltipProvider,
} from '@/shared/components/ui';
import {
  ChevronDown,
  ChevronsLeft,
  ChevronsRight,
  Search,
  Sliders,
} from 'lucide-vue-next';

interface SidebarItem {
  id: string;
  label: string;
  description: string;
  icon: any;
  hasBinding?: boolean;
}

interface SidebarGroup {
  id: string;
  label: string;
  items: SidebarItem[];
}

defineProps<{
  groups: SidebarGroup[];
  flatItems: SidebarItem[];
  activeItemId: string;
  collapsedGroups: string[];
  searchQuery: string;
  sidebarCollapsed: boolean;
}>();

const emit = defineEmits<{
  (e: 'selectItem', item: SidebarItem): void;
  (e: 'toggleGroup', groupId: string): void;
  (e: 'update:searchQuery', value: string): void;
  (e: 'update:sidebarCollapsed', value: boolean): void;
}>();

const { t } = useI18n();
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: hsl(var(--muted-foreground) / 0.2);
  border-radius: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: hsl(var(--muted-foreground) / 0.4);
}
</style>
