<template>
  <TooltipProvider>
    <div class="bg-transparent px-4 sm:px-6 py-2 shadow-none">
      <div class="flex items-center gap-2 sm:gap-4 overflow-x-auto flex-nowrap custom-scrollbar overflow-y-hidden pb-1 items-center">
        <!-- Mobile Sidebar Toggle -->
        <Button
          variant="ghost"
          size="icon"
          class="lg:hidden h-9 w-9 text-muted-foreground hover:text-foreground rounded-md shrink-0 border border-border/40 sm:border-transparent"
          :aria-label="$t('navigation.toggleSidebar')"
          @click="toggleSidebar"
        >
          <MenuIcon class="w-4 h-4" />
        </Button>

        <!-- Breadcrumbs (Left) -->
        <div class="flex items-center gap-0.5 sm:gap-1 text-sm shrink-0 min-w-0">
          <Tooltip>
            <TooltipTrigger as-child>
              <Button 
                variant="ghost" 
                size="icon"
                class="h-8 w-8 text-muted-foreground hover:text-foreground rounded-md shrink-0"
                :aria-label="$t('infra.fileManager.actions.home')"
                @click="navigateToPath('/')"
              >
                <Home class="w-3.5 h-3.5 sm:w-4 sm:h-4" />
              </Button>
            </TooltipTrigger>
            <TooltipContent side="bottom">
              {{ $t('infra.fileManager.actions.home') }}
            </TooltipContent>
          </Tooltip>
          <template
            v-for="(part, index) in pathParts"
            :key="index"
          >
            <span
              v-if="Number(index) === 0 || Number(index) >= pathParts.length - 2"
              class="flex items-center gap-0.5 sm:gap-1 min-w-0"
            >
              <span class="text-muted-foreground/40 text-[10px] select-none">/</span>
              <Button 
                variant="ghost" 
                size="sm" 
                class="h-7 px-1.5 sm:px-2 font-medium rounded-md hover:text-foreground truncate max-w-[60px] sm:max-w-[120px]"
                :class="Number(index) === pathParts.length - 1 ? 'text-primary font-bold' : 'text-muted-foreground'"
                @click="navigateToPath(part.path)"
              >
                {{ part.name }}
              </Button>
            </span>
            <span
              v-else-if="Number(index) === 1 && pathParts.length > 3"
              class="flex items-center gap-0.5 sm:gap-1"
            >
              <span class="text-muted-foreground/40 text-[10px] select-none">/</span>
              <span class="text-muted-foreground opacity-50 px-1">...</span>
            </span>
          </template>
        </div>

        <!-- Search (Center) -->
        <div class="flex-1 flex justify-center min-w-0">
          <div class="relative group w-full max-w-md">
            <SearchIcon class="absolute left-2.5 sm:left-3 top-1/2 -translate-y-1/2 h-3 w-3 sm:h-3.5 sm:h-3.5 text-muted-foreground group-focus-within:text-primary transition-colors" />
            <Input
              v-model="searchQuery"
              type="text"
              :placeholder="$t('infra.fileManager.actions.search')"
              class="pl-8 sm:pl-9 bg-background border-border/40 rounded-lg h-8 sm:h-9 text-[11px] sm:text-xs focus-visible:ring-1 focus-visible:ring-primary/20 transition-colors duration-200 w-full"
            />
          </div>
        </div>
            
        <!-- Actions (Right) -->
        <div class="flex items-center gap-1 sm:gap-2 shrink-0">
          <!-- Unified Filter Toggle with Tooltip -->
          <TooltipProvider>
            <Tooltip>
              <TooltipTrigger as-child>
                <Button
                  variant="ghost"
                  size="icon"
                  :class="`h-9 w-9 p-0 rounded-lg border border-border/40 transition-colors duration-200 ${showAdvancedFilters ? 'bg-accent/10 text-primary border-primary/20' : 'bg-background hover:bg-accent/10'}`"
                  :aria-label="$t('infra.fileManager.filter.all')"
                  @click="showAdvancedFilters = !showAdvancedFilters"
                >
                  <FilterIcon class="w-3.5 h-3.5" />
                </Button>
              </TooltipTrigger>
              <TooltipContent
                side="bottom"
                align="center"
              >
                {{ $t('infra.fileManager.filter.all') }}
              </TooltipContent>
            </Tooltip>
          </TooltipProvider>

          <div class="mx-0.5 h-5 w-[1px] bg-border/40" />

          <!-- View Mode Toggle -->
          <Tooltip>
            <TooltipTrigger as-child>
              <Button
                variant="ghost"
                size="icon"
                type="button"
                class="h-9 w-9 rounded-lg border border-border/40 transition-colors duration-200 text-muted-foreground hover:text-foreground hover:bg-accent/10"
                :aria-label="viewMode === 'grid' ? $t('infra.fileManager.bulk.list_view') : $t('infra.fileManager.bulk.grid_view')"
                @click="viewMode = viewMode === 'grid' ? 'list' : 'grid'"
              >
                <LayoutGrid
                  v-if="viewMode === 'list'"
                  class="w-3.5 h-3.5"
                />
                <List
                  v-else
                  class="w-3.5 h-3.5"
                />
              </Button>
            </TooltipTrigger>
            <TooltipContent side="bottom">
              {{ viewMode === 'grid' ? $t('infra.fileManager.bulk.list_view') : $t('infra.fileManager.bulk.grid_view') }}
            </TooltipContent>
          </Tooltip>
        </div>
      </div>

      <!-- Bulk Actions Panel -->
      <div
        v-if="selectedItems.length > 0"
        class="mt-2 pt-2 border-t border-border/40 animate-in fade-in slide-in-from-top-1 duration-200"
      >
        <div class="flex items-center justify-between gap-4 overflow-x-auto flex-nowrap custom-scrollbar pb-1">
          <div class="flex items-center gap-2">
            <div class="h-8 flex-shrink-0 flex items-center px-3 rounded-lg bg-primary/10 text-primary text-xs font-semibold select-none border border-primary/20">
              {{ selectedItems.length }} <span class="hidden sm:inline ml-1">{{ $t('infra.fileManager.bulk.label_count') }}</span>
            </div>
                    
            <div class="flex items-center gap-1 p-0.5 bg-accent/5 rounded-lg border border-border/40">
              <Tooltip>
                <TooltipTrigger as-child>
                  <Button
                    variant="ghost"
                    size="sm"
                    class="h-7 px-3 rounded-md hover:bg-background text-xs font-medium transition-colors duration-200"
                    @click="copyToClipboard(selectedItems, 'copy')"
                  >
                    <Copy class="w-3.5 h-3.5 mr-1.5" />
                    <span>{{ $t('infra.fileManager.actions.copy') }}</span>
                  </Button>
                </TooltipTrigger>
                <TooltipContent side="bottom">
                  {{ $t('infra.fileManager.actions.copy') }}
                </TooltipContent>
              </Tooltip>

              <Tooltip>
                <TooltipTrigger as-child>
                  <Button
                    variant="ghost"
                    size="sm"
                    class="h-7 px-3 rounded-md hover:bg-background text-xs font-medium transition-colors duration-200"
                    @click="copyToClipboard(selectedItems, 'move')"
                  >
                    <MoveIcon class="w-3.5 h-3.5 mr-1.5" />
                    <span>{{ $t('infra.fileManager.actions.move') }}</span>
                  </Button>
                </TooltipTrigger>
                <TooltipContent side="bottom">
                  {{ $t('infra.fileManager.actions.move') }}
                </TooltipContent>
              </Tooltip>

              <div class="w-px h-4 bg-border/60 mx-0.5" />

              <Tooltip>
                <TooltipTrigger as-child>
                  <Button
                    variant="ghost"
                    size="sm"
                    class="h-7 px-3 rounded-md text-destructive hover:bg-destructive/10 hover:text-destructive text-xs font-medium transition-colors duration-200"
                    @click="bulkDelete"
                  >
                    <Trash2 class="w-3.5 h-3.5 mr-1.5" />
                    <span>{{ $t('infra.fileManager.actions.delete') }}</span>
                  </Button>
                </TooltipTrigger>
                <TooltipContent side="bottom">
                  {{ $t('infra.fileManager.actions.delete') }}
                </TooltipContent>
              </Tooltip>
            </div>
          </div>

          <Tooltip>
            <TooltipTrigger as-child>
              <Button
                variant="ghost"
                size="sm"
                class="h-8 px-3 rounded-lg text-muted-foreground hover:text-foreground hover:bg-destructive/10 hover:text-destructive transition-colors text-xs"
                @click="clearSelection"
              >
                {{ $t('infra.fileManager.bulk.clear_selection') }}
                <X class="w-3.5 h-3.5 ml-1.5" />
              </Button>
            </TooltipTrigger>
            <TooltipContent side="bottom">
              {{ $t('infra.fileManager.bulk.clear_selection') }}
            </TooltipContent>
          </Tooltip>
        </div>
      </div>

      <!-- Advanced Filter Panel -->
      <div
        v-if="showAdvancedFilters"
        class="mt-4 pt-4 border-t border-border/40 space-y-4 animate-in fade-in slide-in-from-top-2 duration-300"
      >
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
          <!-- Type Filter -->
          <div class="space-y-1.5 min-w-0">
            <label class="text-xs font-semibold text-foreground/70 ml-1">
              {{ $t('infra.fileManager.properties.type') }}
            </label>
            <Select v-model="filterType">
              <SelectTrigger class="bg-background h-10 border-border/40 rounded-lg w-full">
                <SelectValue :placeholder="$t('infra.fileManager.filter.all')" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all">
                  {{ $t('infra.fileManager.filter.all') }}
                </SelectItem>
                <SelectItem value="images">
                  {{ $t('infra.fileManager.filter.images') }}
                </SelectItem>
                <SelectItem value="videos">
                  {{ $t('infra.fileManager.filter.videos') }}
                </SelectItem>
                <SelectItem value="documents">
                  {{ $t('infra.fileManager.filter.documents') }}
                </SelectItem>
                <SelectItem value="audio">
                  {{ $t('infra.fileManager.filter.audio') }}
                </SelectItem>
                <SelectItem value="archives">
                  {{ $t('infra.fileManager.filter.archives') }}
                </SelectItem>
              </SelectContent>
            </Select>
          </div>

          <!-- Sort Field Filter -->
          <div class="space-y-1.5 min-w-0">
            <label class="text-xs font-semibold text-foreground/70 ml-1">
              {{ $t('infra.fileManager.sort.label') }}
            </label>
            <div class="flex items-center gap-1.5">
              <Select v-model="sortBy">
                <SelectTrigger class="bg-background h-10 border-border/40 rounded-lg w-full">
                  <SelectValue :placeholder="$t('infra.fileManager.sort.name')" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="name">
                    {{ $t('infra.fileManager.sort.name') }}
                  </SelectItem>
                  <SelectItem value="size">
                    {{ $t('infra.fileManager.sort.size') }}
                  </SelectItem>
                  <SelectItem value="date">
                    {{ $t('infra.fileManager.sort.date') }}
                  </SelectItem>
                </SelectContent>
              </Select>
              <Button
                variant="ghost"
                size="icon"
                class="h-10 w-10 p-0 rounded-lg border border-border/40 flex-shrink-0"
                :aria-label="sortDirection === 'asc' ? $t('infra.fileManager.sort.asc') : $t('infra.fileManager.sort.desc')"
                @click="sortDirection = sortDirection === 'asc' ? 'desc' : 'asc'"
              >
                <component
                  :is="sortDirection === 'asc' ? SortAsc : SortDesc"
                  class="w-4 h-4"
                />
              </Button>
            </div>
          </div>

          <!-- Size Filter -->
          <div class="space-y-1.5 min-w-0">
            <label class="text-xs font-semibold text-foreground/70 ml-1">
              {{ $t('infra.fileManager.properties.size') }}
            </label>
            <div class="flex items-center gap-1.5">
              <Input 
                v-model="minSizeFilter" 
                type="number" 
                placeholder="Min KB" 
                class="bg-background h-10 border-border/40 rounded-lg w-full min-w-0" 
              />
              <span class="text-muted-foreground">-</span>
              <Input 
                v-model="maxSizeFilter" 
                type="number" 
                placeholder="Max KB" 
                class="bg-background h-10 border-border/40 rounded-lg w-full min-w-0" 
              />
            </div>
          </div>

          <!-- Date Filter -->
          <div class="space-y-1.5 min-w-0 xl:col-span-3">
            <label class="text-xs font-semibold text-foreground/70 ml-1">
              {{ $t('infra.fileManager.sort.date') }}
            </label>
            <div class="flex items-center gap-1.5">
              <Input 
                v-model="dateFromFilter" 
                type="date" 
                class="bg-background h-10 border-border/40 rounded-lg text-[10px] px-2 w-full min-w-0" 
              />
              <span class="text-muted-foreground text-[10px] flex-shrink-0 px-1">{{ $t('common.labels.to') }}</span>
              <Input 
                v-model="dateToFilter" 
                type="date" 
                class="bg-background h-10 border-border/40 rounded-lg text-[10px] px-2 w-full min-w-0" 
              />
            </div>
          </div>
        </div>

        <div class="flex justify-end pt-2">
          <Button
            variant="ghost"
            size="sm"
            class="text-xs h-8 hover:bg-destructive/10 hover:text-destructive rounded-lg"
            @click="clearFilters"
          >
            <X class="w-3.5 h-3.5 mr-1.5" />
            {{ $t('common.labels.clear') }}
          </Button>
        </div>
      </div>
    </div>
  </TooltipProvider>
</template>

<script setup lang="ts">
import { inject, ref } from 'vue';
import {
  Copy,
  FilterIcon,
  Home,
  LayoutGrid,
  List,
  MenuIcon,
  MoveIcon,
  SearchIcon,
  SortAsc,
  SortDesc,
  Trash2,
  X,
} from 'lucide-vue-next';
import { 
    Button, 
    Input, 
    Select, 
    SelectContent, 
    SelectItem, 
    SelectTrigger, 
    SelectValue,
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger
} from '@/shared/components/ui';
import { FileManagerKey } from '@/engine/keys';

const showAdvancedFilters = ref(false);

const {
    pathParts,
    navigateToPath,
    searchQuery,
    filterType,
    sortBy,
    sortDirection,
    viewMode,
    selectedItems,
    authorFilter,
    minSizeFilter,
    maxSizeFilter,
    dateFromFilter,
    dateToFilter,
    clearSelection,
    copyToClipboard,
    bulkDelete,
    toggleSidebar
} = inject(FileManagerKey)!;

const clearFilters = () => {
    authorFilter.value = 'all';
    minSizeFilter.value = '';
    maxSizeFilter.value = '';
    dateFromFilter.value = '';
    dateToFilter.value = '';
    filterType.value = 'all';
};
</script>
