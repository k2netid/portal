<template>
  <div class="h-[calc(100vh-7.5rem)] lg:h-[calc(100vh-8.5rem)] flex flex-col rounded-2xl border border-border/60 shadow-xs overflow-hidden bg-card">
    <!-- Top Navbar / Main Webmail Toolbar -->
    <header class="h-12 px-3 lg:px-4 border-b border-border/40 flex items-center justify-between shrink-0 bg-card/80 backdrop-blur-sm select-none">
      <!-- Left: Sidebar Toggle, Brand, Folder Name & Sync -->
      <div class="flex items-center gap-2.5">
        <Button
          variant="ghost"
          size="icon"
          class="hidden lg:flex h-8 w-8 text-muted-foreground hover:text-foreground rounded-lg"
          :title="isSidebarMinimized ? $t('system.mail.expand_sidebar') : $t('system.mail.minimize_sidebar')"
          @click="toggleSidebarMinimize"
        >
          <PanelLeftClose v-if="!isSidebarMinimized" class="w-4 h-4" />
          <PanelLeftOpen v-else class="w-4 h-4 text-primary" />
        </Button>

        <div class="flex items-center gap-2">
          <div class="w-7 h-7 rounded-lg bg-primary/10 text-primary flex items-center justify-center border border-primary/20">
            <Mail class="w-3.5 h-3.5" />
          </div>
          <div class="flex items-center gap-1.5">
            <h1 class="text-xs font-bold text-foreground tracking-tight">
              {{ $t('system.mail.title') }}
            </h1>
            <span class="text-[10px] font-semibold px-1.5 py-0.2 rounded-full bg-primary/10 text-primary uppercase">
              {{ activeLabel ? activeLabel : $t(`system.mail.folder_${activeFolder}`) }}
            </span>
          </div>
        </div>

        <!-- Sync Button -->
        <Button
          variant="ghost"
          size="sm"
          class="h-7 text-xs gap-1.5 text-muted-foreground hover:text-foreground px-2 ml-1"
          :disabled="syncing"
          :title="$t('system.mail.sync_mailbox')"
          @click="syncMailbox"
        >
          <RefreshCw :class="['w-3.5 h-3.5', syncing ? 'animate-spin text-primary' : '']" />
          <span class="hidden sm:inline text-[11px]">{{ syncing ? $t('system.mail.syncing') : $t('system.mail.sync_mailbox') }}</span>
        </Button>
      </div>

      <!-- Right: Empty Trash (if in trash), Manage Labels, Settings & Primary Compose Button -->
      <div class="flex items-center gap-1.5">
        <!-- Empty Trash Button (when active folder is trash) -->
        <Button
          v-if="activeFolder === 'trash' && messages.length > 0"
          variant="destructive"
          size="sm"
          class="h-7 text-xs gap-1 px-2.5 shadow-xs"
          @click="emptyTrash"
        >
          <Trash2 class="w-3.5 h-3.5" />
          <span class="hidden sm:inline">{{ $t('system.mail.empty_trash') }}</span>
        </Button>

        <!-- Manage Labels Button -->
        <Button
          variant="ghost"
          size="icon"
          class="h-8 w-8 text-muted-foreground hover:text-foreground rounded-lg"
          :title="$t('system.mail.manage_labels')"
          @click="isLabelsModalOpen = true"
        >
          <Tag class="w-4 h-4" />
        </Button>

        <!-- Mail Client Settings Button -->
        <Button
          variant="ghost"
          size="icon"
          class="h-8 w-8 text-muted-foreground hover:text-foreground rounded-lg"
          :title="$t('system.mail.settings_title')"
          @click="isSettingsOpen = true"
        >
          <Settings class="w-4 h-4" />
        </Button>

        <!-- Compose Email Button -->
        <Button
          size="sm"
          class="h-7 gap-1.5 text-xs font-semibold px-3 shadow-xs ml-1"
          @click="openComposer()"
        >
          <Edit3 class="w-3.5 h-3.5" />
          <span>{{ $t('system.mail.compose') }}</span>
        </Button>
      </div>
    </header>

    <!-- 3-Column Layout Container -->
    <div
      ref="layoutContainerRef"
      class="flex-1 flex overflow-hidden min-h-0 relative select-none"
      :class="{ 'cursor-col-resize': isResizing }"
    >
      <!-- Column 1: Sidebar (Collapsible on desktop, hidden on mobile) -->
      <aside
        :class="[
          'hidden lg:flex border-r border-border/40 bg-card/40 shrink-0 overflow-hidden transition-all duration-300',
          isSidebarMinimized ? 'w-16' : 'w-60'
        ]"
      >
        <MailSidebar
          :is-minimized="isSidebarMinimized"
          :active-folder="activeFolder"
          :active-label="activeLabel"
          :folder-counts="folderCounts"
          :labels="labels"
          @select-folder="selectFolder"
          @select-label="selectLabel"
          @manage-labels="isLabelsModalOpen = true"
        />
      </aside>

      <!-- Column 2: Message List (Draggable resizable width on desktop) -->
      <section
        :style="{ width: isDesktop ? `${listWidth}px` : undefined }"
        :class="[
          'border-r border-border/40 shrink-0 overflow-hidden flex flex-col min-h-0',
          isMobileDetailOpen ? 'hidden md:flex' : 'flex flex-1 md:flex-initial'
        ]"
      >
        <MailList
          :messages="messages"
          :selected-message-id="selectedMessageId"
          :search-query="searchQuery"
          :filter-type="filterType"
          :loading="loading"
          @select-message="selectMessage"
          @toggle-star="toggleStar"
          @update:search-query="v => { searchQuery = v; fetchMessages(); }"
          @update:filter-type="v => { filterType = v; fetchMessages(); }"
          @refresh="fetchMessages"
        />
      </section>

      <!-- Draggable Splitter Handle (Desktop Only) -->
      <div
        class="hidden md:flex w-2 -ml-1 cursor-col-resize items-center justify-center z-20 group relative shrink-0 select-none hover:bg-primary/20 active:bg-primary/30 transition-colors"
        :title="'Drag to resize list panel'"
        @mousedown="startResize"
      >
        <div class="w-0.5 h-8 bg-border/60 group-hover:bg-primary rounded-full transition-colors" />
      </div>

      <!-- Column 3: Message Detail View (flex-1) -->
      <main
        :class="[
          'flex-1 overflow-hidden bg-card/20 min-h-0 flex flex-col',
          !isMobileDetailOpen ? 'hidden md:flex' : 'flex'
        ]"
      >
        <MailDetail
          :message="selectedMessage"
          :available-labels="labels"
          @back="isMobileDetailOpen = false"
          @reply="reply"
          @forward="forward"
          @toggle-star="toggleStar"
          @move-to-trash="moveToTrash"
          @restore-from-trash="restoreFromTrash"
          @delete-permanently="deletePermanently"
          @move-to-folder="moveMessage"
          @toggle-label="toggleMessageLabel"
        />
      </main>
    </div>

    <!-- Mail Composer Modal -->
    <MailComposerModal
      :is-open="isComposerOpen"
      :composer-data="composerData"
      @close="isComposerOpen = false"
      @send="sendEmail"
    />

    <!-- Mail Settings Modal -->
    <MailSettingsModal
      :is-open="isSettingsOpen"
      @close="isSettingsOpen = false"
    />

    <!-- Custom Labels & Categories Manager Modal -->
    <MailLabelsModal
      :is-open="isLabelsModalOpen"
      :labels="labels"
      @close="isLabelsModalOpen = false"
      @update:labels="v => labels = v"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted, computed } from 'vue';
import {
  Mail,
  Edit3,
  RefreshCw,
  PanelLeftClose,
  PanelLeftOpen,
  Settings,
  Tag,
  Trash2,
} from 'lucide-vue-next';
import { Button } from '@/shared/components/ui';
import { useMailClient } from '@/modules/Core/System/composables/useMailClient';
import MailSidebar from '@/modules/Core/System/components/mail/MailSidebar.vue';
import MailList from '@/modules/Core/System/components/mail/MailList.vue';
import MailDetail from '@/modules/Core/System/components/mail/MailDetail.vue';
import MailComposerModal from '@/modules/Core/System/components/mail/MailComposerModal.vue';
import MailSettingsModal from '@/modules/Core/System/components/mail/MailSettingsModal.vue';
import MailLabelsModal from '@/modules/Core/System/components/mail/MailLabelsModal.vue';

const {
    isSidebarMinimized,
    toggleSidebarMinimize,
    activeFolder,
    activeLabel,
    selectedMessageId,
    selectedMessage,
    searchQuery,
    filterType,
    isMobileDetailOpen,
    labels,
    messages,
    folderCounts,
    loading,
    syncing,
    isSettingsOpen,
    isLabelsModalOpen,
    isComposerOpen,
    composerData,
    fetchMessages,
    syncMailbox,
    selectFolder,
    selectLabel,
    selectMessage,
    toggleStar,
    moveMessage,
    toggleMessageLabel,
    moveToTrash,
    restoreFromTrash,
    deletePermanently,
    emptyTrash,
    openComposer,
    reply,
    forward,
    sendEmail,
} = useMailClient();

// Draggable List Panel Resizing Logic
const layoutContainerRef = ref<HTMLElement | null>(null);
const listWidth = ref(340);
const isResizing = ref(false);
const windowWidth = ref(typeof window !== 'undefined' ? window.innerWidth : 1200);

const isDesktop = computed(() => windowWidth.value >= 768);

const handleResizeWindow = () => {
    windowWidth.value = window.innerWidth;
};

const startResize = (event: MouseEvent) => {
    event.preventDefault();
    isResizing.value = true;
    document.addEventListener('mousemove', onMouseMove);
    document.addEventListener('mouseup', stopResize);
    document.body.style.cursor = 'col-resize';
    document.body.style.userSelect = 'none';
};

const onMouseMove = (event: MouseEvent) => {
    if (!isResizing.value || !layoutContainerRef.value) return;
    const containerRect = layoutContainerRef.value.getBoundingClientRect();
    const sidebarWidth = isSidebarMinimized.value ? 64 : 240;
    const rawWidth = event.clientX - containerRect.left - (windowWidth.value >= 1024 ? sidebarWidth : 0);
    
    // Constraints: min 260px, max 540px
    const clamped = Math.max(260, Math.min(540, rawWidth));
    listWidth.value = clamped;
};

const stopResize = () => {
    if (isResizing.value) {
        isResizing.value = false;
        document.removeEventListener('mousemove', onMouseMove);
        document.removeEventListener('mouseup', stopResize);
        document.body.style.cursor = '';
        document.body.style.userSelect = '';
        try {
            localStorage.setItem('ja_mail_list_width', String(listWidth.value));
        } catch {
            // localStorage not accessible
        }
    }
};

onMounted(() => {
    window.addEventListener('resize', handleResizeWindow);
    try {
        const saved = localStorage.getItem('ja_mail_list_width');
        if (saved) {
            const parsed = parseInt(saved, 10);
            if (!isNaN(parsed) && parsed >= 260 && parsed <= 540) {
                listWidth.value = parsed;
            }
        }
    } catch {
        // localStorage not accessible
    }
});

onUnmounted(() => {
    window.removeEventListener('resize', handleResizeWindow);
    document.removeEventListener('mousemove', onMouseMove);
    document.removeEventListener('mouseup', stopResize);
});
</script>
