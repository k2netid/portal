<template>
  <div class="h-[calc(100vh-7.5rem)] lg:h-[calc(100vh-8.5rem)] flex flex-col rounded-2xl border border-border/60 shadow-xs overflow-hidden bg-card">
    <!-- Top Bar Title -->
    <div class="h-12 px-4 border-b border-border/40 flex items-center justify-between shrink-0 bg-card/60 backdrop-blur-sm">
      <div class="flex items-center gap-2.5">
        <div class="w-7 h-7 rounded-lg bg-primary/10 text-primary flex items-center justify-center border border-primary/20">
          <Mail class="w-3.5 h-3.5" />
        </div>
        <div>
          <h1 class="text-xs font-bold text-foreground tracking-tight flex items-center gap-1.5">
            {{ $t('system.mail.title') }}
            <span class="text-[9px] font-semibold px-1.5 py-0.2 rounded-full bg-muted text-muted-foreground">
              v1.0
            </span>
          </h1>
        </div>
      </div>

      <div class="flex items-center gap-2">
        <Button
          size="sm"
          class="h-7 gap-1.5 text-xs font-semibold px-3 shadow-xs"
          @click="openComposer()"
        >
          <Edit3 class="w-3 h-3" />
          <span>{{ $t('system.mail.compose') }}</span>
        </Button>
      </div>
    </div>

    <!-- 3-Column Layout Container -->
    <div class="flex-1 flex overflow-hidden min-h-0">
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
          :syncing="syncing"
          @select-folder="selectFolder"
          @select-label="selectLabel"
          @open-composer="openComposer()"
          @toggle-minimize="toggleSidebarMinimize"
          @sync="syncMailbox"
        />
      </aside>

      <!-- Column 2: Message List (w-full on mobile when not in detail, w-80/96 on desktop) -->
      <section
        :class="[
          'flex-1 md:flex-initial md:w-80 lg:w-96 border-r border-border/40 shrink-0 overflow-hidden flex flex-col min-h-0',
          isMobileDetailOpen ? 'hidden md:flex' : 'flex'
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

      <!-- Column 3: Message Detail View (flex-1) -->
      <main
        :class="[
          'flex-1 overflow-hidden bg-card/20 min-h-0 flex flex-col',
          !isMobileDetailOpen ? 'hidden md:flex' : 'flex'
        ]"
      >
        <MailDetail
          :message="selectedMessage"
          @back="isMobileDetailOpen = false"
          @reply="reply"
          @forward="forward"
          @toggle-star="toggleStar"
          @move-to-trash="moveToTrash"
          @restore-from-trash="restoreFromTrash"
          @delete-permanently="deletePermanently"
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
  </div>
</template>

<script setup lang="ts">
import { Mail, Edit3 } from 'lucide-vue-next';
import { Button } from '@/shared/components/ui';
import { useMailClient } from '@/modules/Core/System/composables/useMailClient';
import MailSidebar from '@/modules/Core/System/components/mail/MailSidebar.vue';
import MailList from '@/modules/Core/System/components/mail/MailList.vue';
import MailDetail from '@/modules/Core/System/components/mail/MailDetail.vue';
import MailComposerModal from '@/modules/Core/System/components/mail/MailComposerModal.vue';

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
    fetchMessages,
    syncMailbox,
    selectFolder,
    selectLabel,
    selectMessage,
    toggleStar,
    moveToTrash,
    restoreFromTrash,
    deletePermanently,
    isComposerOpen,
    composerData,
    openComposer,
    reply,
    forward,
    sendEmail,
} = useMailClient();
</script>
