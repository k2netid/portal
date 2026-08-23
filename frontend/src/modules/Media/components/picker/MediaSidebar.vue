<template>
  <div 
    :class="[
      'h-full flex flex-col overflow-hidden bg-card border-r border-border lg:border-r-0',
      sidebarCollapsed ? 'w-0' : 'w-72',
      'transition-[width] duration-300 ease-in-out'
    ]"
  >
    <!-- Sidebar Header (Title) -->
    <div
      v-if="!sidebarCollapsed"
      class="h-14 flex items-center px-2 shrink-0"
    >
      <h2 class="text-sm font-bold text-foreground whitespace-nowrap px-4">
        {{ $t('media.title') }}
      </h2>
    </div>
    <div
      v-if="!sidebarCollapsed"
      class="px-4 shrink-0"
    >
      <div class="h-px bg-border/40 w-full" />
    </div>

    <div
      v-if="!sidebarCollapsed"
      class="flex items-center justify-between p-4 pb-0"
    >
      <h2 class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground flex items-center">
        <Folder class="w-3 h-3 mr-2" />
        {{ $t('media.folders') }}
      </h2>
    </div>
        
    <div
      v-if="!sidebarCollapsed"
      class="flex-1 overflow-y-auto custom-scrollbar p-2 pr-4 space-y-0.5 mt-2"
    >
      <!-- Root folder (All Media) -->
      <ContextMenu>
        <ContextMenuTrigger>
          <button
            :class="[
              'w-full flex items-center gap-2 text-[13px] h-9 px-2 rounded-lg transition-colors duration-200 group/root',
              selectedFolder === null && !isTrashMode ? 'bg-primary/10 text-primary font-bold shadow-none' : 'text-muted-foreground hover:bg-accent/10 hover:text-foreground active:scale-[0.98]'
            ]"
            @click="selectFolder(null)"
          >
            <FolderOpen
              v-if="selectedFolder === null && !isTrashMode"
              class="w-4 h-4 shrink-0 transition-transform group-hover/root:scale-110 text-primary"
              stroke-width="1.5"
            />
            <Folder
              v-else
              class="w-4 h-4 shrink-0 opacity-70 group-hover/root:opacity-100 transition-transform group-hover/root:scale-110"
              stroke-width="1.5"
            />
            <span class="truncate font-medium">{{ $t('media.allMedia') }}</span>
          </button>
        </ContextMenuTrigger>
        <ContextMenuContent>
          <ContextMenuItem @click="showFolderModal = true">
            <FolderPlus class="w-4 h-4 text-muted-foreground group-hover:text-primary transition-colors" />
            {{ $t('media.newFolder') }}
          </ContextMenuItem>
          <ContextMenuItem @click="showUploadModal = true">
            <Plus class="w-4 h-4 text-muted-foreground group-hover:text-primary transition-colors" />
            {{ $t('media.upload') }}
          </ContextMenuItem>
          <ContextMenuSeparator />
          <ContextMenuItem @click="fetchMedia(); fetchFolders();">
            <RefreshCw class="w-4 h-4 text-muted-foreground group-hover:text-primary transition-colors" />
            {{ $t('media.actions.refresh') }}
          </ContextMenuItem>
        </ContextMenuContent>
      </ContextMenu>

      <!-- Recursive Folder Tree -->
      <template
        v-for="folder in treeFolders"
        :key="folder.id"
      >
        <div class="flex flex-col">
          <ContextMenu v-if="!folder.is_trashed">
            <ContextMenuTrigger>
              <div 
                :class="[
                  'group w-full flex items-center gap-1 text-[13px] h-9 px-2 rounded-lg transition-colors cursor-pointer',
                  selectedFolder === folder.id ? 'bg-primary/10 text-primary font-bold' : 'text-muted-foreground hover:bg-accent/10 hover:text-foreground'
                ]"
                @click="selectFolder(folder.id)"
              >
                <button 
                  v-if="(folder.children?.length || 0) > 0"
                  class="h-6 w-6 flex items-center justify-center rounded-md hover:bg-accent/50 text-muted-foreground/50 hover:text-foreground transition-colors shrink-0"
                  @click.stop="toggleFolder(folder.id)"
                >
                  <ChevronDown
                    v-if="expandedFolders.has(folder.id)"
                    class="w-3 h-3"
                    stroke-width="1.5"
                  />
                  <ChevronRight
                    v-else
                    class="w-3 h-3"
                    stroke-width="1.5"
                  />
                </button>
                <div
                  v-else
                  class="w-6 shrink-0"
                />
                                
                <FolderOpen
                  v-if="selectedFolder === folder.id"
                  class="w-4 h-4 flex-shrink-0 text-primary -ml-1 transition-transform"
                  stroke-width="1.5"
                />
                <Folder
                  v-else
                  class="w-4 h-4 flex-shrink-0 -ml-1 opacity-70 group-hover:opacity-100"
                  stroke-width="1.5"
                />
                <span
                  class="truncate ml-1 flex-1"
                  :class="{ 'line-through text-muted-foreground/50': folder.is_trashed }"
                >{{ folder.name }}</span>
                                
                <!-- Folder Actions -->
                <div class="opacity-0 group-hover:opacity-100 flex items-center gap-1 z-20 relative ml-auto">
                  <button 
                    class="p-1.5 hover:bg-destructive/10 text-muted-foreground hover:text-destructive rounded-sm transition-colors pointer-events-auto"
                    :title="$t('media.actions.delete')"
                    @click.stop.prevent="deleteFolder(folder)"
                  >
                    <Trash2 class="w-3.5 h-3.5" />
                  </button>
                </div>
              </div>
            </ContextMenuTrigger>
            <ContextMenuContent>
              <ContextMenuItem @click="selectFolder(folder.id)">
                <FolderOpen class="w-4 h-4 text-muted-foreground group-hover:text-primary transition-colors" />
                {{ $t('media.actions.open') }}
              </ContextMenuItem>
              <ContextMenuSeparator />
              <ContextMenuItem
                class="text-destructive focus:bg-destructive/10 focus:text-destructive"
                @click="deleteFolder(folder)"
              >
                <Trash2 class="w-4 h-4 text-destructive/70 transition-colors" />
                {{ $t('media.actions.delete') }}
              </ContextMenuItem>
            </ContextMenuContent>
          </ContextMenu>

          <!-- Sub folders -->
          <div
            v-if="expandedFolders.has(folder.id) && (folder.children?.length || 0) > 0"
            class="ml-2.5 border-l border-border/40 pl-1 mt-0.5 space-y-0.5"
          >
            <template
              v-for="child in folder.children"
              :key="child.id"
            >
              <ContextMenu v-if="!child.is_trashed">
                <ContextMenuTrigger>
                  <div 
                    :class="[
                      'group w-full flex items-center gap-1 text-[13px] h-9 px-2 rounded-lg transition-colors cursor-pointer',
                      selectedFolder === child.id ? 'bg-primary/10 text-primary font-bold' : 'text-muted-foreground hover:bg-accent/10 hover:text-foreground'
                    ]"
                    @click="selectFolder(child.id)"
                  >
                    <button 
                      v-if="(child.children?.length || 0) > 0"
                      class="h-6 w-6 flex items-center justify-center rounded-md hover:bg-accent/50 text-muted-foreground/50 hover:text-foreground transition-colors shrink-0"
                      @click.stop="toggleFolder(child.id)"
                    >
                      <ChevronDown
                        v-if="expandedFolders.has(child.id)"
                        class="w-3 h-3"
                        stroke-width="1.5"
                      />
                      <ChevronRight
                        v-else
                        class="w-3 h-3"
                        stroke-width="1.5"
                      />
                    </button>
                    <div
                      v-else
                      class="w-6 shrink-0"
                    />
                                        
                    <FolderOpen
                      v-if="selectedFolder === child.id"
                      class="w-4 h-4 flex-shrink-0 text-primary -ml-1"
                      stroke-width="1.5"
                    />
                    <Folder
                      v-else
                      class="w-4 h-4 flex-shrink-0 -ml-1 opacity-70 group-hover:opacity-100"
                      stroke-width="1.5"
                    />
                    <span
                      class="truncate ml-1 flex-1"
                      :class="{ 'line-through text-muted-foreground/50': child.is_trashed }"
                    >{{ child.name }}</span>

                    <!-- Folder Actions -->
                    <div class="opacity-0 group-hover:opacity-100 flex items-center gap-1 z-20 relative ml-auto">
                      <button 
                        class="p-1.5 hover:bg-destructive/10 text-muted-foreground hover:text-destructive rounded-sm transition-colors pointer-events-auto"
                        :title="$t('media.actions.delete')"
                        @click.stop.prevent="deleteFolder(child)"
                      >
                        <Trash2 class="w-3.5 h-3.5" />
                      </button>
                    </div>
                  </div>
                </ContextMenuTrigger>
                <ContextMenuContent>
                  <ContextMenuItem @click="selectFolder(child.id)">
                    <FolderOpen class="w-4 h-4 text-muted-foreground group-hover:text-primary transition-colors" />
                    {{ $t('media.actions.open') }}
                  </ContextMenuItem>
                  <ContextMenuSeparator />
                  <ContextMenuItem
                    class="text-destructive focus:bg-destructive/10 focus:text-destructive"
                    @click="deleteFolder(child)"
                  >
                    <Trash2 class="w-4 h-4 text-destructive/70 transition-colors" />
                    {{ $t('media.actions.delete') }}
                  </ContextMenuItem>
                </ContextMenuContent>
              </ContextMenu>

              <!-- Level 3 -->
              <div
                v-if="expandedFolders.has(child.id) && (child.children?.length || 0) > 0"
                class="ml-2.5 border-l border-border/40 pl-1 mt-0.5 space-y-0.5"
              >
                <template
                  v-for="subChild in child.children"
                  :key="subChild.id"
                >
                  <ContextMenu v-if="!subChild.is_trashed">
                    <ContextMenuTrigger>
                      <div 
                        :class="[
                          'group w-full flex items-center gap-1 text-[13px] h-9 px-2 rounded-lg transition-colors cursor-pointer',
                          selectedFolder === subChild.id ? 'bg-primary/10 text-primary font-bold' : 'text-muted-foreground hover:bg-accent/10 hover:text-foreground'
                        ]"
                        @click="selectFolder(subChild.id)"
                      >
                        <div class="w-6 shrink-0" />
                        <FolderOpen
                          v-if="selectedFolder === subChild.id"
                          class="w-4 h-4 flex-shrink-0 text-primary -ml-1"
                          stroke-width="1.5"
                        />
                        <Folder
                          v-else
                          class="w-4 h-4 flex-shrink-0 -ml-1 opacity-70 group-hover:opacity-100"
                          stroke-width="1.5"
                        />
                        <span class="truncate ml-1 flex-1">{{ subChild.name }}</span>

                        <!-- Folder Actions -->
                        <div class="opacity-0 group-hover:opacity-100 flex items-center gap-1 z-20 relative ml-auto">
                          <button 
                            class="p-1.5 hover:bg-destructive/10 text-muted-foreground hover:text-destructive rounded-sm transition-colors pointer-events-auto"
                            :title="$t('media.actions.delete')"
                            @click.stop.prevent="deleteFolder(subChild)"
                          >
                            <Trash2 class="w-3.5 h-3.5" />
                          </button>
                        </div>
                      </div>
                    </ContextMenuTrigger>
                    <ContextMenuContent>
                      <ContextMenuItem @click="selectFolder(subChild.id)">
                        <FolderOpen class="w-4 h-4 text-muted-foreground group-hover:text-primary transition-colors" />
                        {{ $t('media.actions.open') }}
                      </ContextMenuItem>
                      <ContextMenuSeparator />
                      <ContextMenuItem
                        class="text-destructive focus:bg-destructive/10 focus:text-destructive"
                        @click="deleteFolder(subChild)"
                      >
                        <Trash2 class="w-4 h-4 text-destructive/70 transition-colors" />
                        {{ $t('media.actions.delete') }}
                      </ContextMenuItem>
                    </ContextMenuContent>
                  </ContextMenu>
                </template>
              </div>
            </template>
          </div>
        </div>
      </template>
    </div>

    <!-- Trash navigation (Bottom) -->
    <div
      v-if="!sidebarCollapsed"
      class="px-4 shrink-0"
    >
      <div class="h-px bg-border/40 w-full" />
    </div>
    <div
      v-if="!sidebarCollapsed"
      class="p-3 bg-transparent mt-auto"
    >
      <button
        :class="[
          'w-full flex items-center gap-3 text-sm h-10 px-3 rounded-lg transition-colors duration-200 group/trash relative',
          isTrashMode 
            ? 'bg-destructive/10 text-destructive' 
            : 'text-muted-foreground hover:bg-destructive/5 hover:text-destructive active:scale-[0.98]'
        ]"
        @click="toggleTrashMode"
      >
        <Trash2 
          :class="[
            'w-4 h-4 shrink-0 transition-transform duration-300',
            isTrashMode ? 'scale-110' : 'group-hover:rotate-12'
          ]" 
        />
        <span class="truncate font-semibold tracking-tight">{{ $t('media.trash') }}</span>
                
        <div 
          v-if="(statistics?.trash_count || 0) > 0" 
          :class="[
            'ml-auto px-1.5 py-0.5 rounded text-[10px] font-bold transition-colors',
            isTrashMode 
              ? 'bg-destructive text-white' 
              : 'bg-destructive/10 text-destructive group-hover:bg-destructive group-hover:text-white'
          ]"
        >
          {{ statistics?.trash_count }}
        </div>
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { inject } from 'vue';
import {
  ChevronDown,
  ChevronRight,
  Folder,
  FolderOpen,
  FolderPlus,
  Plus,
  RefreshCw,
  Trash2,
} from 'lucide-vue-next';
import { 
    ContextMenu, 
    ContextMenuTrigger, 
    ContextMenuContent, 
    ContextMenuItem, 
    ContextMenuSeparator 
} from '@/shared/components/ui';
import { MediaManagerKey } from '@/engine/keys';

const {
    selectedFolder,
    isTrashMode,
    treeFolders,
    expandedFolders,
    statistics,
    selectFolder,
    deleteFolder,
    toggleFolder,
    toggleTrashMode,
    sidebarCollapsed,
    showFolderModal,
    showUploadModal,
    fetchMedia,
    fetchFolders
} = inject(MediaManagerKey)!;
</script>
