<template>
  <div class="h-[calc(100vh-6.5rem)] flex flex-col bg-card border border-border/70 rounded-2xl shadow-xs overflow-hidden select-none">
    <!-- Top Unified Mail Toolbar Header -->
    <header class="h-12 border-b border-border/40 bg-card/60 backdrop-blur-md px-3 sm:px-4 flex items-center justify-between shrink-0 z-30">
      <!-- Left: Mobile Sidebar Drawer Button + JA-Mail Brand + Compose Email Button -->
      <div class="flex items-center gap-1.5 sm:gap-3">
        <!-- Mobile Sidebar Toggle Drawer Button -->
        <Button
          variant="ghost"
          size="icon"
          class="lg:hidden h-8 w-8 text-muted-foreground hover:text-foreground rounded-lg -ml-1 shrink-0"
          :title="$t('system.mail.folders')"
          @click="isMobileSidebarOpen = true"
        >
          <Menu class="w-4 h-4" />
        </Button>

        <div class="flex items-center gap-2 shrink-0">
          <div class="w-7 h-7 rounded-lg bg-primary/10 flex items-center justify-center text-primary border border-primary/20 shadow-2xs">
            <Mail class="w-3.5 h-3.5" />
          </div>
          <h1 class="text-xs font-bold tracking-tight text-foreground">
            {{ $t('system.navigation.menu.mail') }}
          </h1>
        </div>

        <div class="h-4 w-px bg-border/60 mx-0.5 hidden sm:block" />

        <!-- Compose Email Button (Beside Brand Label) -->
        <Button
          size="sm"
          class="h-7.5 gap-1.5 text-xs font-semibold px-2.5 sm:px-3 shadow-xs bg-primary hover:bg-primary/90 text-primary-foreground transition-all cursor-pointer shrink-0"
          @click="openComposer()"
        >
          <Edit3 class="w-3.5 h-3.5" />
          <span class="hidden sm:inline">{{ $t('system.mail.compose') }}</span>
        </Button>
      </div>

      <!-- Right: Global Actions (Sync, Empty Trash, Shortcuts, Labels, Settings, Account Switcher) -->
      <div class="flex items-center gap-1 sm:gap-1.5">
        <!-- Sync Button -->
        <Button
          variant="outline"
          size="sm"
          class="h-7 text-xs gap-1.5 px-2 sm:px-2.5 shadow-xs"
          :disabled="syncing || loading"
          @click="syncMailbox"
        >
          <RefreshCw :class="['w-3.5 h-3.5', syncing ? 'animate-spin text-primary' : '']" />
          <span class="hidden md:inline">{{ $t('system.mail.sync') }}</span>
        </Button>

        <!-- Empty Trash (Conditional) -->
        <Button
          v-if="activeFolder === 'trash' && messages.length > 0"
          variant="destructive"
          size="sm"
          class="h-7 text-xs gap-1 px-2 sm:px-2.5 shadow-xs"
          @click="emptyTrash"
        >
          <Trash2 class="w-3.5 h-3.5" />
          <span class="hidden md:inline">{{ $t('system.mail.empty_trash') }}</span>
        </Button>

        <!-- Keyboard Shortcuts Help Button (Desktop Only) -->
        <Button
          variant="ghost"
          size="icon"
          class="hidden md:flex h-8 w-8 text-muted-foreground hover:text-foreground rounded-lg"
          title="Keyboard Shortcuts (?)"
          @click="isShortcutsOpen = true"
        >
          <Keyboard class="w-4 h-4" />
        </Button>

        <!-- Manage Labels Button -->
        <Button
          variant="ghost"
          size="icon"
          class="hidden sm:flex h-8 w-8 text-muted-foreground hover:text-foreground rounded-lg"
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

        <!-- Mailbox Account Switcher Dropdown (Top Right Toolbar) -->
        <Popover v-if="accounts.length > 0" v-model:open="isAccountSwitcherOpen">
          <PopoverTrigger as-child>
            <button
              type="button"
              class="h-7 flex items-center gap-1.5 px-2 rounded-lg border border-border/60 bg-muted/40 hover:bg-muted text-left transition-all cursor-pointer shadow-2xs group ml-0.5"
              :title="activeAccount ? `${activeAccount.name} (${activeAccount.email})` : 'Switch Mailbox'"
            >
              <div class="w-4.5 h-4.5 rounded-md bg-primary/10 text-primary flex items-center justify-center font-bold text-[9px] shrink-0">
                {{ (activeAccount?.name || activeAccount?.email || 'M').charAt(0).toUpperCase() }}
              </div>
              <div class="hidden lg:block min-w-0 max-w-[120px]">
                <p class="text-[11px] font-bold text-foreground truncate leading-none">{{ activeAccount?.name || 'Mailbox' }}</p>
                <p class="text-[9px] text-muted-foreground truncate leading-none mt-0.5">{{ activeAccount?.email }}</p>
              </div>
              <ChevronsUpDown class="w-3 h-3 text-muted-foreground opacity-60 group-hover:opacity-100 shrink-0" />
            </button>
          </PopoverTrigger>
          <PopoverContent class="w-60 p-1.5 z-[1200] text-xs space-y-1 bg-card border border-border/80 shadow-xl rounded-xl" align="end">
            <div class="px-2 py-1 text-[10px] font-bold uppercase tracking-wider text-muted-foreground">
              {{ $t('system.mail.accounts.connectedAccounts') }}
            </div>
            <button
              v-for="acc in accounts"
              :key="acc.id"
              type="button"
              :class="[
                'w-full flex items-center justify-between px-2.5 py-1.5 rounded-lg text-left transition-colors cursor-pointer',
                activeAccountId === acc.id ? 'bg-primary/10 text-primary font-semibold' : 'hover:bg-muted text-foreground'
              ]"
              @click="switchAccount(acc.id); isAccountSwitcherOpen = false"
            >
              <div class="min-w-0 flex-1 pr-1">
                <p class="truncate leading-tight font-medium text-xs">{{ acc.name }}</p>
                <p class="text-[10px] text-muted-foreground truncate leading-tight">{{ acc.email }}</p>
              </div>
              <Check v-if="activeAccountId === acc.id" class="w-3.5 h-3.5 text-primary shrink-0" />
            </button>
            <Separator class="my-1" />
            <button
              type="button"
              class="w-full flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg hover:bg-muted text-muted-foreground hover:text-foreground text-left transition-colors text-[11px] font-medium cursor-pointer"
              @click="openSettingsWithTab('accounts'); isAccountSwitcherOpen = false"
            >
              <Settings class="w-3.5 h-3.5 text-primary" />
              <span>{{ $t('system.mail.accounts.title') }}</span>
            </button>
          </PopoverContent>
        </Popover>
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
          'hidden lg:flex border-r border-border/40 bg-card/40 shrink-0 transition-all duration-300 relative z-20',
          isSidebarMinimized ? 'w-16' : 'w-60'
        ]"
      >
        <!-- Floating Toggle Button (Desktop, like main sidebar) -->
        <button
          type="button"
          class="hidden lg:flex absolute -right-3 top-3 items-center justify-center h-6 w-6 rounded-full border border-border bg-sidebar text-muted-foreground hover:text-foreground hover:bg-muted shadow-xs z-30 transition-all cursor-pointer"
          :title="isSidebarMinimized ? $t('common.navigation.sidebar.expand') : $t('common.navigation.sidebar.minimize')"
          :aria-label="isSidebarMinimized ? $t('common.navigation.sidebar.expand') : $t('common.navigation.sidebar.minimize')"
          @click="toggleSidebarMinimize"
        >
          <ChevronLeft v-if="!isSidebarMinimized" class="w-3.5 h-3.5" />
          <ChevronRight v-else class="w-3.5 h-3.5" />
        </button>

        <div class="w-full h-full overflow-hidden flex flex-col">
          <MailSidebar
            :is-minimized="isSidebarMinimized"
            :active-folder="activeFolder"
            :active-label="activeLabel"
            :folder-counts="folderCounts"
            :labels="labels"
            :storage-stats="storageStats"
            @select-folder="selectFolder"
            @select-label="selectLabel"
            @update:labels="saveLabels"
            @manage-labels="isLabelsModalOpen = true"
          />
        </div>
      </aside>

      <!-- Column 2: Message List & Search (Draggable width on desktop) -->
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
          :current-page="currentPage"
          :last-page="lastPage"
          :total-messages="totalMessages"
          :from-range="fromRange"
          :to-range="toRange"
          @select-message="selectMessage"
          @toggle-star="toggleStar"
          @update:search-query="v => { searchQuery = v; fetchMessages(1); }"
          @update:filter-type="v => { filterType = v; fetchMessages(1); }"
          @refresh="() => fetchMessages()"
          @next-page="nextPage"
          @prev-page="prevPage"
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
          'flex-1 flex flex-col overflow-hidden min-h-0 bg-card/20',
          isMobileDetailOpen ? 'flex' : 'hidden md:flex'
        ]"
      >
        <MailDetail
          :message="selectedMessage"
          :all-messages="messages"
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
          @snooze="snoozeMessage"
        />
      </main>
    </div>

    <!-- Floating Undo Send Toast Banner -->
    <Teleport to="body">
      <div
        v-if="isUndoToastVisible"
        class="fixed bottom-6 right-6 z-[1000] flex items-center gap-3 p-3 px-4 rounded-xl bg-card border border-primary/40 shadow-2xl animate-in slide-in-from-bottom-5 duration-200"
      >
        <div class="flex items-center gap-2 text-xs">
          <span class="w-2 h-2 rounded-full bg-primary animate-ping" />
          <span class="font-medium text-foreground">Sending email in <strong class="text-primary font-bold">{{ undoCountdown }}s</strong>...</span>
        </div>
        <div class="flex items-center gap-1.5">
          <Button
            variant="default"
            size="sm"
            class="h-7 text-xs px-2.5 font-bold shadow-xs bg-primary text-primary-foreground hover:bg-primary/90"
            @click="undoSend"
          >
            {{ $t('system.mail.undo') }}
          </Button>
          <Button
            variant="ghost"
            size="sm"
            class="h-7 text-xs px-2 text-muted-foreground hover:text-foreground"
            @click="sendNow"
          >
            {{ $t('system.mail.send_now') }}
          </Button>
        </div>
      </div>
    </Teleport>

    <!-- Mobile Sidebar Drawer (Slide-Over Sheet for Folders & Labels) -->
    <Teleport to="body">
      <div
        v-if="isMobileSidebarOpen"
        class="fixed inset-0 z-[1100] lg:hidden flex"
      >
        <!-- Backdrop with Blur -->
        <div
          class="fixed inset-0 bg-background/80 backdrop-blur-xs transition-opacity animate-in fade-in-0 duration-200"
          @click="isMobileSidebarOpen = false"
        />

        <!-- Slide-over Content -->
        <div class="relative w-[280px] max-w-[85vw] h-full bg-card border-r border-border shadow-2xl flex flex-col z-10 animate-in slide-in-from-left duration-200">
          <div class="p-3 border-b border-border/40 flex items-center justify-between">
            <div class="flex items-center gap-2">
              <div class="w-6 h-6 rounded-lg bg-primary/10 flex items-center justify-center text-primary border border-primary/20">
                <Mail class="w-3.5 h-3.5" />
              </div>
              <span class="text-xs font-bold text-foreground">{{ $t('system.mail.folders') }}</span>
            </div>
            <Button
              variant="ghost"
              size="icon"
              class="h-7 w-7 rounded-lg text-muted-foreground hover:text-foreground"
              @click="isMobileSidebarOpen = false"
            >
              <X class="w-4 h-4" />
            </Button>
          </div>

          <div class="flex-1 overflow-hidden">
            <MailSidebar
              :is-minimized="false"
              :active-folder="activeFolder"
              :active-label="activeLabel"
              :folder-counts="folderCounts"
              :labels="labels"
              :storage-stats="storageStats"
              @select-folder="f => { selectFolder(f); isMobileSidebarOpen = false; isMobileDetailOpen = false; }"
              @select-label="l => { selectLabel(l); isMobileSidebarOpen = false; isMobileDetailOpen = false; }"
              @update:labels="saveLabels"
              @manage-labels="() => { isLabelsModalOpen = true; isMobileSidebarOpen = false; }"
            />
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Mail Composer Modal -->
    <MailComposerModal
      :is-open="isComposerOpen"
      :composer-data="composerData"
      :templates="templates"
      @update:composer-data="composerData = $event"
      @close="isComposerOpen = false"
      @send="sendEmail"
      @save-draft="saveDraft"
      @schedule-send="scheduleSend"
      @manage-templates="openSettingsWithTab('templates')"
    />

    <!-- Mail Settings Modal (Unified with Accounts Management) -->
    <MailSettingsModal
      :is-open="isSettingsOpen"
      :initial-tab="settingsInitialTab"
      :accounts="accounts"
      :capabilities="capabilities"
      @close="isSettingsOpen = false"
      @save-account="saveAccount"
      @delete-account="deleteAccount"
      @test-connection="testAccountConnection"
    />

    <!-- Keyboard Shortcuts Help Modal -->
    <MailShortcutsModal
      :is-open="isShortcutsOpen"
      @close="isShortcutsOpen = false"
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
import { useRoute } from 'vue-router';
import {
  Mail,
  Edit3,
  RefreshCw,
  ChevronLeft,
  ChevronRight,
  Settings,
  Tag,
  Trash2,
  Keyboard,
  ChevronsUpDown,
  Check,
  Menu,
  X,
} from 'lucide-vue-next';
import {
  Button,
  Popover,
  PopoverTrigger,
  PopoverContent,
  Separator,
} from '@/shared/components/ui';
import { useMailClient } from '@/modules/Mail/composables/useMailClient';
import MailSidebar from '@/modules/Mail/components/mail/MailSidebar.vue';
import MailList from '@/modules/Mail/components/mail/MailList.vue';
import MailDetail from '@/modules/Mail/components/mail/MailDetail.vue';
import MailComposerModal from '@/modules/Mail/components/mail/MailComposerModal.vue';
import MailSettingsModal from '@/modules/Mail/components/mail/MailSettingsModal.vue';
import MailLabelsModal from '@/modules/Mail/components/mail/MailLabelsModal.vue';
import MailShortcutsModal from '@/modules/Mail/components/mail/MailShortcutsModal.vue';

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
    currentPage,
    lastPage,
    totalMessages,
    fromRange,
    toRange,
    nextPage,
    prevPage,
    labels,
    templates,
    messages,
    folderCounts,
    storageStats,
    loading,
    syncing,
    isSettingsOpen,
    isLabelsModalOpen,
    isComposerOpen,
    composerData,
    isUndoToastVisible,
    undoCountdown,
    undoSend,
    sendNow,
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
    saveDraft,
    scheduleSend,
    snoozeMessage,
    saveLabels,
    accounts,
    activeAccountId,
    capabilities,
    switchAccount,
    saveAccount,
    deleteAccount,
    testAccountConnection,
} = useMailClient();

const isShortcutsOpen = ref(false);
const isMobileSidebarOpen = ref(false);
const isAccountSwitcherOpen = ref(false);

const activeAccount = computed(() => {
    if (!accounts.value || accounts.value.length === 0) return null;
    return accounts.value.find(a => a.id === activeAccountId.value)
        || accounts.value.find(a => a.is_default)
        || accounts.value[0]
        || null;
});

const settingsInitialTab = ref<'accounts' | 'general' | 'signature' | 'templates' | 'ai' | 'vacation' | 'server'>('general');

const openSettingsWithTab = (tab: 'accounts' | 'general' | 'signature' | 'templates' | 'ai' | 'vacation' | 'server' = 'general') => {
    settingsInitialTab.value = tab;
    isSettingsOpen.value = true;
};

// Draggable Resizer State
const listWidth = ref(380);
const isResizing = ref(false);
const layoutContainerRef = ref<HTMLElement | null>(null);
const isDesktop = computed(() => typeof window !== 'undefined' && window.innerWidth >= 768);

const startResize = (e: MouseEvent) => {
    e.preventDefault();
    isResizing.value = true;
    window.addEventListener('mousemove', handleResize);
    window.addEventListener('mouseup', stopResize);
};

const handleResize = (e: MouseEvent) => {
    if (!isResizing.value || !layoutContainerRef.value) return;
    const containerRect = layoutContainerRef.value.getBoundingClientRect();
    const sidebarWidth = isSidebarMinimized.value ? 64 : 240;
    const newWidth = e.clientX - containerRect.left - (isDesktop.value ? sidebarWidth : 0);

    // Clamp between 260px and 560px
    if (newWidth >= 260 && newWidth <= 560) {
        listWidth.value = newWidth;
    }
};

const stopResize = () => {
    isResizing.value = false;
    window.removeEventListener('mousemove', handleResize);
    window.removeEventListener('mouseup', stopResize);
    localStorage.setItem('ja_mail_list_width', String(listWidth.value));
};

// Next & Prev Email Selection Helpers for Shortcuts
const selectNextEmail = () => {
    if (messages.value.length === 0) return;
    const currentIndex = messages.value.findIndex(m => m.id === selectedMessageId.value);
    if (currentIndex < messages.value.length - 1) {
        selectMessage(messages.value[currentIndex + 1]!.id);
    }
};

const selectPrevEmail = () => {
    if (messages.value.length === 0) return;
    const currentIndex = messages.value.findIndex(m => m.id === selectedMessageId.value);
    if (currentIndex > 0) {
        selectMessage(messages.value[currentIndex - 1]!.id);
    }
};

// Global Keyboard Shortcuts Handler
const handleGlobalKeydown = (e: KeyboardEvent) => {
    const target = e.target as HTMLElement;
    const isInput = target && (target.tagName === 'INPUT' || target.tagName === 'TEXTAREA' || target.isContentEditable);

    if (isInput) return;

    if (e.key === '?' || (e.shiftKey && e.key === '/')) {
        e.preventDefault();
        isShortcutsOpen.value = true;
    } else if (e.key === 'c' || e.key === 'C') {
        e.preventDefault();
        openComposer();
    } else if (e.key === 'j' || e.key === 'ArrowDown') {
        e.preventDefault();
        selectNextEmail();
    } else if (e.key === 'k' || e.key === 'ArrowUp') {
        e.preventDefault();
        selectPrevEmail();
    } else if ((e.key === 'r' || e.key === 'R') && selectedMessage.value) {
        e.preventDefault();
        reply(selectedMessage.value);
    } else if ((e.key === 'f' || e.key === 'F') && selectedMessage.value) {
        e.preventDefault();
        forward(selectedMessage.value);
    } else if ((e.key === 's' || e.key === 'S') && selectedMessage.value) {
        e.preventDefault();
        toggleStar(selectedMessage.value.id);
    } else if ((e.key === '#' || e.key === 'Delete') && selectedMessage.value) {
        e.preventDefault();
        moveToTrash(selectedMessage.value.id);
    } else if (e.key === '/') {
        e.preventDefault();
        const searchInput = document.querySelector('input[placeholder*="Search"]') as HTMLInputElement;
        searchInput?.focus();
    }
};

const route = useRoute();

onMounted(() => {
    const saved = localStorage.getItem('ja_mail_list_width');
    if (saved && !isNaN(Number(saved))) {
        listWidth.value = Number(saved);
    }
    window.addEventListener('keydown', handleGlobalKeydown);

    if (route.query.openSettings === 'true' || route.query.tab) {
        const tab = (typeof route.query.tab === 'string' ? route.query.tab : 'accounts') as 'accounts' | 'general' | 'signature' | 'templates' | 'ai' | 'vacation' | 'server';
        openSettingsWithTab(tab);
    }
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleGlobalKeydown);
});
</script>
