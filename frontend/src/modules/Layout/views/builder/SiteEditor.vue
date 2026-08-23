<template>
  <div class="site-editor-view" :class="{ 'is-modal': !isFullscreen }">
    <div v-if="!isFullscreen" class="site-editor-overlay" @click="handleClose"></div>
    <div class="site-editor-container">
      <Builder 
        ref="builderRef"
        mode="site" 
        @close="handleClose" 
        @save="handleSave"
        @update:fullscreen="handleFullscreenUpdate"
      />
      
      <!-- Confirm Dialog -->
      <Dialog :open="showConfirmDialog" @update:open="showConfirmDialog = $event">
            <DialogContent class="sm:max-w-[425px]">
                <DialogHeader>
                    <DialogTitle>{{ t('builder.modals.confirm.discardChanges') }}</DialogTitle>
                    <DialogDescription>
                        {{ t('builder.modals.confirm.discardChangesDesc') }}
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter>
                    <Button variant="outline" @click="showConfirmDialog = false">
                        {{ t('builder.common.cancel') }}
                    </Button>
                    <Button variant="destructive" @click="confirmClose">
                        {{ t('builder.modals.confirm.discard') }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
  </div>
</template>

<script setup lang="ts">
import { logger } from '@/shared/utils/logger';
import { ref, onMounted, onUnmounted, unref } from 'vue';
import { useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';
import { toast } from '@/shared/services/toastService';
import type { BuilderInstance } from '@/modules/Layout/types/builder';
import Builder from '../../components/builder/Builder.vue';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogDescription,
    DialogFooter,
    Button
} from '@/shared/components/ui';

const { t } = useI18n();
const router = useRouter();
const isFullscreen = ref(false);
const builderRef = ref<{ builder?: BuilderInstance } | null>(null);
const showConfirmDialog = ref(false);

const handleSave = async (status: string | null) => {
    if (!builderRef.value?.builder) return
    
    // In site mode, we might just use saveContent logic from builder
    // Or we might need to handle status if it's draft/published.
    if (status && builderRef.value.builder.content?.value) {
        (builderRef.value.builder.content.value as any).status = status
    }
    
    try {
        await builderRef.value.builder.saveContent()
        builderRef.value.builder.markAsSaved()
        toast.success(status === 'published' ? 'Site published successfully' : 'Site saved successfully')
    } catch (err) {
        toast.error('Failed to save site')
        logger.error(err instanceof Error ? err.message : 'Failed to save site', { error: err })
    }
}

const handleClose = () => {
    const isDirty = unref(builderRef.value?.builder?.isDirty)
    if (isDirty) {
        showConfirmDialog.value = true
    } else {
        router.push({ name: 'dashboard' })
    }
}

const confirmClose = () => {
    showConfirmDialog.value = false
    router.push({ name: 'dashboard' })
}

const handleFullscreenUpdate = (val: boolean) => {
  isFullscreen.value = val
}

// Provide a way for the builder to communicate fullscreen state if needed, 
// though we usually rely on builder internals and teleport.
// For SiteEditor, we watch the builder's state if we can, or just let it teleport.

// Handle escape key to close modal if not fullscreen
const handleEsc = (e: KeyboardEvent) => {
  if (e.key === 'Escape' && !isFullscreen.value) {
    handleClose()
  }
}

onMounted(async () => {
  window.addEventListener('keydown', handleEsc)
  // We can't easily listen to builder internal state here without props/emits
  // Let's ensure Builder emits fullscreen changes (we'll need to add that)
})

onUnmounted(() => {
  window.removeEventListener('keydown', handleEsc)
})
</script>

<style scoped>
.site-editor-view {
  position: fixed;
  inset: 0;
  z-index: 1000;
  display: flex;
  align-items: center;
  justify-content: center;
}

.site-editor-view.is-modal {
  padding: 40px;
}

.site-editor-overlay {
  position: absolute;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  backdrop-filter: blur(4px);
  z-index: -1;
}

.site-editor-container {
  width: 100%;
  height: 100%;
  background: var(--background);
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
  display: flex;
  flex-direction: column;
}

.is-modal .site-editor-container {
  max-width: 1600px;
  max-height: 900px;
  border: 1px solid var(--border);
}

.site-editor-container :deep(.ja-builder) {
  border: none !important;
  border-radius: 0 !important;
  box-shadow: none !important;
  height: 100% !important;
}

/* When builder is fullscreen, it teleports to body. 
   We should hide our container to avoid ghost elements? 
   No, Teleport moves the DOM nodes, so the container will be empty anyway.
*/

</style>
