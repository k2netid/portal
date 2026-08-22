<template>
  <div class="h-[calc(100vh-4.5rem)] flex flex-col overflow-hidden bg-background">
    <!-- Top Bar Title -->
    <div class="h-12 px-6 border-b border-border/40 flex items-center justify-between shrink-0 bg-card/60 backdrop-blur-sm">
      <div class="flex items-center gap-3">
        <div class="w-8 h-8 rounded-lg bg-primary/10 text-primary flex items-center justify-center border border-primary/20">
          <Mail class="w-4 h-4" />
        </div>
        <div>
          <h1 class="text-sm font-bold text-foreground tracking-tight flex items-center gap-2">
            {{ $t('system.mail.title') }}
            <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-muted text-muted-foreground">
              v1.0
            </span>
          </h1>
        </div>
      </div>

      <div class="flex items-center gap-2">
        <Button
          size="sm"
          class="h-8 gap-1.5 text-xs font-semibold px-3 shadow-xs"
          @click="openComposer()"
        >
          <Edit3 class="w-3.5 h-3.5" />
          <span>{{ $t('system.mail.compose') }}</span>
        </Button>
      </div>
    </div>

    <!-- 3-Column Layout Container -->
    <div class="flex-1 flex overflow-hidden">
      <!-- Column 1: Sidebar (Hidden on mobile if detail/list open, w-60 on desktop) -->
      <aside class="hidden lg:block w-60 border-r border-border/40 bg-card/40 shrink-0 overflow-y-auto custom-scrollbar">
        <MailSidebar
          :active-folder="activeFolder"
          :active-label="activeLabel"
          :folder-counts="folderCounts"
          :labels="labels"
          @select-folder="selectFolder"
          @select-label="selectLabel"
          @open-composer="openComposer()"
        />
      </aside>

      <!-- Column 2: Message List (w-full on mobile when not in detail, w-80/96 on desktop) -->
      <section
        :class="[
          'flex-1 md:flex-initial md:w-80 lg:w-96 border-r border-border/40 shrink-0 overflow-hidden',
          isMobileDetailOpen ? 'hidden md:block' : 'block'
        ]"
      >
        <MailList
          :messages="filteredMessages"
          :selected-message-id="selectedMessageId"
          :search-query="searchQuery"
          :filter-type="filterType"
          @select-message="selectMessage"
          @toggle-star="toggleStar"
          @update:search-query="v => searchQuery = v"
          @update:filter-type="v => filterType = v"
        />
      </section>

      <!-- Column 3: Message Detail View (flex-1) -->
      <main
        :class="[
          'flex-1 overflow-hidden bg-card/20',
          !isMobileDetailOpen ? 'hidden md:block' : 'block'
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
    activeFolder,
    activeLabel,
    selectedMessageId,
    selectedMessage,
    searchQuery,
    filterType,
    isMobileDetailOpen,
    labels,
    filteredMessages,
    folderCounts,
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
