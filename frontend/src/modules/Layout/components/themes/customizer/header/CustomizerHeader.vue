<template>
  <header class="flex items-center justify-between border-b border-border px-3.5 sm:px-5 py-2.5 sm:py-3 bg-card shrink-0 z-20 gap-2 overflow-x-auto custom-scrollbar">
    <!-- Left: Back Button & Title -->
    <div class="flex items-center gap-2 sm:gap-3 shrink-0 min-w-0">
      <Button
        variant="ghost"
        size="icon"
        class="h-8 w-8 sm:h-9 sm:w-9 rounded-xl text-muted-foreground hover:text-foreground shrink-0"
        :aria-label="t('publishing.theme_customizer.actions.back_tooltip')"
        :title="t('publishing.theme_customizer.actions.back_tooltip')"
        @click="emit('back')"
      >
        <ArrowLeft class="w-4 h-4 sm:w-5 sm:h-5" />
      </Button>
      <div class="min-w-0">
        <h1 class="text-xs sm:text-sm font-bold tracking-tight text-foreground truncate max-w-[120px] sm:max-w-xs">
          {{ t('publishing.theme_customizer.title', 'Theme Customizer') }}
        </h1>
        <p class="text-[10px] sm:text-xs font-semibold text-muted-foreground capitalize truncate max-w-[120px] sm:max-w-xs">
          {{ theme?.name || t('common.labels.loading') }}
        </p>
      </div>
    </div>
          
    <!-- Right: History, Mode Switcher, Revert, Publish -->
    <div class="flex items-center gap-1.5 sm:gap-2.5 shrink-0">
      <!-- History Controls (Undo / Redo) -->
      <div class="flex items-center border border-border rounded-xl bg-background overflow-hidden p-0.5 shadow-2xs">
        <button
          type="button"
          :disabled="!canUndo"
          class="h-7 w-7 rounded-lg hover:bg-muted text-muted-foreground hover:text-foreground disabled:opacity-20 transition-colors flex items-center justify-center"
          :aria-label="t('publishing.theme_customizer.actions.undo')"
          :title="t('publishing.theme_customizer.actions.undo')"
          @click="emit('undo')"
        >
          <Undo2 class="w-3.5 h-3.5" />
        </button>
        <div class="w-px h-3.5 bg-border mx-0.5" />
        <button
          type="button"
          :disabled="!canRedo"
          class="h-7 w-7 rounded-lg hover:bg-muted text-muted-foreground hover:text-foreground disabled:opacity-20 transition-colors flex items-center justify-center"
          :aria-label="t('publishing.theme_customizer.actions.redo')"
          :title="t('publishing.theme_customizer.actions.redo')"
          @click="emit('redo')"
        >
          <Redo2 class="w-3.5 h-3.5" />
        </button>
      </div>

      <!-- Mode Selector (Segmented Control) -->
      <div class="hidden sm:flex items-center gap-0.5 rounded-xl border border-border bg-muted/40 p-0.5 sm:p-1">
        <button
          type="button"
          class="h-7 px-2 sm:px-2.5 text-xs font-semibold rounded-lg transition-all flex items-center gap-1.5"
          :class="organizationMode === 'design' ? 'bg-background text-foreground shadow-2xs font-bold' : 'text-muted-foreground hover:text-foreground hover:bg-muted/60'"
          :title="t('publishing.theme_customizer.organization.modes.design', 'Design')"
          :aria-label="t('publishing.theme_customizer.organization.modes.design', 'Design')"
          @click="emit('update:organizationMode', 'design')"
        >
          <Paintbrush class="w-3.5 h-3.5" />
          <span class="hidden md:inline">{{ t('publishing.theme_customizer.organization.modes.design', 'Design') }}</span>
        </button>
        <button
          type="button"
          class="h-7 px-2 sm:px-2.5 text-xs font-semibold rounded-lg transition-all flex items-center gap-1.5"
          :class="organizationMode === 'bindings' ? 'bg-background text-foreground shadow-2xs font-bold' : 'text-muted-foreground hover:text-foreground hover:bg-muted/60'"
          :title="t('publishing.theme_customizer.organization.modes.bindings', 'Content')"
          :aria-label="t('publishing.theme_customizer.organization.modes.bindings', 'Content')"
          @click="emit('update:organizationMode', 'bindings')"
        >
          <Database class="w-3.5 h-3.5" />
          <span class="hidden md:inline">{{ t('publishing.theme_customizer.organization.modes.bindings', 'Content') }}</span>
        </button>
        <button
          type="button"
          class="h-7 px-2 sm:px-2.5 text-xs font-semibold rounded-lg transition-all flex items-center gap-1.5"
          :class="organizationMode === 'advanced' ? 'bg-background text-foreground shadow-2xs font-bold' : 'text-muted-foreground hover:text-foreground hover:bg-muted/60'"
          :title="t('publishing.theme_customizer.organization.modes.advanced', 'Advanced')"
          :aria-label="t('publishing.theme_customizer.organization.modes.advanced', 'Advanced')"
          @click="emit('update:organizationMode', 'advanced')"
        >
          <Code class="w-3.5 h-3.5" />
          <span class="hidden md:inline">{{ t('publishing.theme_customizer.organization.modes.advanced', 'Advanced') }}</span>
        </button>
      </div>

      <!-- Revert & Publish Controls -->
      <div class="flex items-center gap-1.5 sm:gap-2">
        <!-- Unsaved Indicator -->
        <span
          v-if="isDirty"
          class="flex items-center gap-1 px-2 py-1 rounded-full bg-amber-500/10 text-amber-500 text-xs font-semibold border border-amber-500/20 shrink-0"
          :title="t('publishing.theme_customizer.status.unsaved', 'Belum Disimpan')"
        >
          <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse" />
          <span class="hidden xl:inline">{{ t('publishing.theme_customizer.status.unsaved', 'Belum Disimpan') }}</span>
        </span>

        <!-- Revert Icon Button -->
        <DropdownMenu>
          <DropdownMenuTrigger as-child>
            <Button
              variant="outline"
              size="icon"
              class="h-8 w-8 sm:h-9 sm:w-9 rounded-xl shrink-0"
              :disabled="!isDirty"
              :title="t('publishing.theme_customizer.actions.revert', 'Kembalikan')"
              :aria-label="t('publishing.theme_customizer.actions.revert', 'Kembalikan')"
            >
              <RotateCcw class="w-3.5 h-3.5 sm:w-4 sm:h-4" />
            </Button>
          </DropdownMenuTrigger>
          <DropdownMenuContent
            align="end"
            class="w-56 rounded-xl"
          >
            <DropdownMenuItem @click="emit('reset-initial')">
              <History class="w-4 h-4 mr-2" />
              {{ t('publishing.theme_customizer.revert.session_start', 'Awal Sesi') }}
            </DropdownMenuItem>
            <DropdownMenuItem
              class="text-destructive focus:text-destructive"
              @click="emit('reset-defaults')"
            >
              <Zap class="w-4 h-4 mr-2" />
              {{ t('publishing.theme_customizer.revert.theme_defaults', 'Pengaturan Default') }}
            </DropdownMenuItem>
          </DropdownMenuContent>
        </DropdownMenu>

        <!-- Publish Button -->
        <Button
          size="sm"
          class="h-8 sm:h-9 px-2.5 sm:px-3.5 inline-flex items-center gap-1.5 rounded-xl font-semibold text-xs shadow-sm shadow-primary/20 bg-primary hover:bg-primary/90 shrink-0"
          :disabled="saving || !isDirty"
          :title="saving ? t('publishing.theme_customizer.status.saving', 'Menyimpan...') : t('publishing.theme_customizer.actions.publish', 'Terbitkan')"
          :aria-label="t('publishing.theme_customizer.actions.publish', 'Terbitkan')"
          @click="emit('save')"
        >
          <Save
            v-if="!saving"
            data-icon="inline-start"
            class="w-3.5 h-3.5 shrink-0"
          />
          <Loader2
            v-else
            data-icon="inline-start"
            class="w-3.5 h-3.5 shrink-0 animate-spin"
          />
          <span class="hidden sm:inline">{{ saving ? t('publishing.theme_customizer.status.saving', 'Menyimpan...') : t('publishing.theme_customizer.actions.publish', 'Terbitkan') }}</span>
        </Button>
      </div>
    </div>
  </header>
</template>

<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import type { Theme } from '@/modules/Layout/types/theme';
import {
  Button,
  DropdownMenu,
  DropdownMenuTrigger,
  DropdownMenuContent,
  DropdownMenuItem,
} from '@/shared/components/ui';
import {
  ArrowLeft,
  Code,
  Database,
  History,
  Loader2,
  Paintbrush,
  Redo2,
  RotateCcw,
  Save,
  Undo2,
  Zap,
} from 'lucide-vue-next';

defineProps<{
  theme: Theme | null;
  organizationMode: 'design' | 'bindings' | 'advanced';
  canUndo: boolean;
  canRedo: boolean;
  isDirty: boolean;
  saving: boolean;
}>();

const emit = defineEmits<{
  (e: 'back'): void;
  (e: 'undo'): void;
  (e: 'redo'): void;
  (e: 'update:organizationMode', mode: 'design' | 'bindings' | 'advanced'): void;
  (e: 'reset-initial'): void;
  (e: 'reset-defaults'): void;
  (e: 'save'): void;
}>();

const { t } = useI18n();
</script>
