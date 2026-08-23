<template>
  <header class="flex items-center justify-between border-b border-border px-5 py-3.5 bg-card shrink-0 z-20">
    <!-- Left: Back Button & Title -->
    <div class="flex items-center gap-3.5">
      <Button
        variant="ghost"
        size="icon"
        class="h-9 w-9 rounded-xl text-muted-foreground hover:text-foreground"
        :aria-label="t('publishing.theme_customizer.actions.back_tooltip')"
        :title="t('publishing.theme_customizer.actions.back_tooltip')"
        @click="emit('back')"
      >
        <ArrowLeft class="w-5 h-5" />
      </Button>
      <div>
        <h1 class="text-base font-bold tracking-tight text-foreground">
          {{ t('publishing.theme_customizer.title', 'Theme Customizer') }}
        </h1>
        <p class="text-xs font-semibold text-muted-foreground capitalize">
          {{ theme?.name || t('common.labels.loading') }}
        </p>
      </div>
    </div>
          
    <!-- Right: History, Mode Switcher, Preview, Revert, Publish -->
    <div class="flex items-center gap-3">
      <!-- History Controls -->
      <div class="flex items-center border border-border rounded-xl bg-background overflow-hidden p-0.5 shadow-sm">
        <button
          type="button"
          :disabled="!canUndo"
          class="p-1.5 rounded-lg hover:bg-muted text-muted-foreground hover:text-foreground disabled:opacity-20 transition-colors"
          :aria-label="t('publishing.theme_customizer.actions.undo')"
          :title="t('publishing.theme_customizer.actions.undo')"
          @click="emit('undo')"
        >
          <Undo2 class="w-4 h-4" />
        </button>
        <div class="w-px h-4 bg-border mx-0.5" />
        <button
          type="button"
          :disabled="!canRedo"
          class="p-1.5 rounded-lg hover:bg-muted text-muted-foreground hover:text-foreground disabled:opacity-20 transition-colors"
          :aria-label="t('publishing.theme_customizer.actions.redo')"
          :title="t('publishing.theme_customizer.actions.redo')"
          @click="emit('redo')"
        >
          <Redo2 class="w-4 h-4" />
        </button>
      </div>

      <div class="h-6 w-px bg-border hidden sm:block" />

      <!-- Mode Selector (Segmented Control) -->
      <div class="hidden lg:flex items-center gap-1 rounded-xl border border-border bg-muted/40 p-1">
        <button
          type="button"
          class="px-3 py-1.5 text-xs font-semibold rounded-lg transition-all"
          :class="organizationMode === 'design' ? 'bg-background text-foreground shadow-sm' : 'text-muted-foreground hover:text-foreground hover:bg-muted/60'"
          @click="emit('update:organizationMode', 'design')"
        >
          {{ t('publishing.theme_customizer.organization.modes.design', 'Design') }}
        </button>
        <button
          type="button"
          class="px-3 py-1.5 text-xs font-semibold rounded-lg transition-all"
          :class="organizationMode === 'bindings' ? 'bg-background text-foreground shadow-sm' : 'text-muted-foreground hover:text-foreground hover:bg-muted/60'"
          @click="emit('update:organizationMode', 'bindings')"
        >
          {{ t('publishing.theme_customizer.organization.modes.bindings', 'Content') }}
        </button>
        <button
          type="button"
          class="px-3 py-1.5 text-xs font-semibold rounded-lg transition-all"
          :class="organizationMode === 'advanced' ? 'bg-background text-foreground shadow-sm' : 'text-muted-foreground hover:text-foreground hover:bg-muted/60'"
          @click="emit('update:organizationMode', 'advanced')"
        >
          {{ t('publishing.theme_customizer.organization.modes.advanced', 'Advanced') }}
        </button>
      </div>

      <!-- Preview Button -->
      <Button
        variant="outline"
        size="sm"
        class="hidden xl:inline-flex h-9 items-center gap-1.5 rounded-xl font-medium text-xs"
        @click="emit('open-preview')"
      >
        <Eye data-icon="inline-start" class="w-3.5 h-3.5 shrink-0" />
        {{ t('publishing.theme_customizer.organization.preview', 'Pratinjau') }}
      </Button>

      <!-- Revert & Publish Controls -->
      <div class="flex items-center gap-2">
        <span
          v-if="isDirty"
          class="flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-amber-500/10 text-amber-500 text-xs font-semibold border border-amber-500/20"
        >
          <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse" />
          {{ t('publishing.theme_customizer.status.unsaved', 'Belum Disimpan') }}
        </span>

        <DropdownMenu>
          <DropdownMenuTrigger as-child>
            <Button
              variant="outline"
              size="sm"
              class="h-9 inline-flex items-center gap-1.5 rounded-xl font-medium text-xs"
              :disabled="!isDirty"
            >
              <RotateCcw data-icon="inline-start" class="w-3.5 h-3.5 shrink-0" />
              {{ t('publishing.theme_customizer.actions.revert', 'Kembalikan') }}
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

        <Button
          size="sm"
          class="h-9 inline-flex items-center gap-1.5 rounded-xl font-semibold text-xs shadow-sm shadow-primary/20 bg-primary hover:bg-primary/90"
          :disabled="saving || !isDirty"
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
          {{ saving ? t('publishing.theme_customizer.status.saving', 'Menyimpan...') : t('publishing.theme_customizer.actions.publish', 'Terbitkan') }}
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
  Eye,
  History,
  Loader2,
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
  (e: 'open-preview'): void;
  (e: 'reset-initial'): void;
  (e: 'reset-defaults'): void;
  (e: 'save'): void;
}>();

const { t } = useI18n();
</script>
