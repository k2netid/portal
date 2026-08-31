<template>
  <aside
    class="member-portal-sidebar shrink-0 z-40 flex flex-col bg-card border-border/60 transition-[width,transform] duration-200
      lg:sticky lg:top-14 lg:self-start lg:min-h-[calc(100vh-3.5rem)] lg:max-h-[calc(100vh-3.5rem)] lg:overflow-hidden lg:border-r
      fixed top-[6rem] lg:top-14 bottom-0 left-0 max-w-[85vw] border-r shadow-xl lg:shadow-none"
    :class="[
      sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
      collapsed ? 'lg:w-16 w-72' : 'lg:w-60 w-72',
    ]"
    aria-label="Member portal"
  >
    <!-- Sidebar toolbar -->
    <div
      class="flex items-center border-b border-border/50 shrink-0"
      :class="collapsed ? 'justify-center px-2 py-3' : 'justify-between px-3 py-3'"
    >
      <p
        v-if="!collapsed"
        class="text-[10px] font-semibold uppercase tracking-[0.16em] text-muted-foreground truncate"
      >
        {{ t('member.portal.menuLabel', 'Account') }}
      </p>
      <button
        type="button"
        class="hidden lg:inline-flex items-center justify-center h-8 w-8 rounded-md text-muted-foreground hover:text-foreground hover:bg-muted/70 transition-colors"
        :title="collapsed
          ? t('member.portal.sidebar.expand', 'Expand sidebar')
          : t('member.portal.sidebar.collapse', 'Collapse sidebar')"
        :aria-label="collapsed
          ? t('member.portal.sidebar.expand', 'Expand sidebar')
          : t('member.portal.sidebar.collapse', 'Collapse sidebar')"
        @click="toggleCollapsed"
      >
        <PanelLeftOpen
          v-if="collapsed"
          class="w-4 h-4"
        />
        <PanelLeftClose
          v-else
          class="w-4 h-4"
        />
      </button>
    </div>

    <nav class="flex-1 overflow-y-auto py-2">
      <div
        v-for="(group, groupIndex) in navGroups"
        :key="group.id"
        :class="groupIndex > 0 ? 'mt-1 border-t border-border/40 pt-1' : ''"
      >
        <!-- Accordion header (expanded sidebar only) -->
        <button
          v-if="!collapsed && group.items.length > 1"
          type="button"
          class="flex w-full items-center gap-2 px-3 py-2 text-left text-[11px] font-semibold uppercase tracking-wide text-muted-foreground hover:text-foreground transition-colors"
          :aria-expanded="isSectionOpen(group.id)"
          @click="toggleSection(group.id)"
        >
          <ChevronDown
            class="w-3.5 h-3.5 shrink-0 transition-transform duration-200"
            :class="isSectionOpen(group.id) ? '' : '-rotate-90'"
          />
          <span class="truncate">{{ t(group.labelKey) }}</span>
        </button>

        <p
          v-else-if="!collapsed && group.items.length === 1"
          class="px-3 py-2 text-[11px] font-semibold uppercase tracking-wide text-muted-foreground truncate"
        >
          {{ t(group.labelKey) }}
        </p>

        <!-- Nav links -->
        <ul
          v-show="collapsed || group.items.length === 1 || isSectionOpen(group.id)"
          class="px-2 space-y-0.5"
        >
          <li
            v-for="item in group.items"
            :key="item.slug"
          >
            <router-link
              :to="{ name: item.routeName }"
              class="group flex items-center rounded-md text-sm font-medium transition-colors"
              :class="[
                collapsed ? 'justify-center px-2 py-2.5' : 'gap-2.5 px-3 py-2',
                isActive(item.routeName)
                  ? 'bg-primary/10 text-primary shadow-sm'
                  : 'text-muted-foreground hover:text-foreground hover:bg-muted/70',
              ]"
              :title="collapsed ? t(item.labelKey) : undefined"
              @click="emit('navigate')"
            >
              <component
                :is="item.icon"
                class="w-4 h-4 shrink-0"
                :class="isActive(item.routeName) ? 'text-primary' : 'text-muted-foreground group-hover:text-foreground'"
                aria-hidden="true"
              />
              <span
                v-if="!collapsed"
                class="truncate"
              >
                {{ t(item.labelKey) }}
              </span>
            </router-link>
          </li>
        </ul>
      </div>
    </nav>
  </aside>
</template>

<script setup lang="ts">
import {
  ChevronDown,
  PanelLeftClose,
  PanelLeftOpen,
} from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';
import { useMemberPortalSidebar } from '@/modules/Member/composables/useMemberPortalSidebar';

defineProps<{
  sidebarOpen: boolean;
}>();

const emit = defineEmits<{
  navigate: [];
}>();

const { t } = useI18n();
const {
  collapsed,
  navGroups,
  isActive,
  isSectionOpen,
  toggleSection,
  toggleCollapsed,
} = useMemberPortalSidebar();
</script>

<style scoped>
.member-portal-sidebar {
  scrollbar-width: thin;
}
</style>
