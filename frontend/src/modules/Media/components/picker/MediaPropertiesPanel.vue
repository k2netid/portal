<template>
  <div 
    class="border-l border-border/40 bg-background/95 lg:bg-transparent flex flex-col transition-[width] duration-300 overflow-hidden h-full fixed right-0 top-0 bottom-0 z-50 lg:static lg:z-auto shadow-xl lg:shadow-none will-change-[width]"
    :class="[isVisible ? 'w-80 sm:w-96 backdrop-blur-md lg:backdrop-blur-none' : 'w-0 border-l-0']"
  >
    <div
      v-if="isVisible"
      class="flex flex-col h-full w-80 sm:w-96"
    >
      <!-- Header -->
      <div class="p-3 border-b border-border/40 flex items-center justify-between bg-transparent shrink-0">
        <h3 class="font-semibold text-xs uppercase tracking-widest text-muted-foreground/80">
          {{ $t('media.modals.edit.title') }}
        </h3>
        <Button
          variant="ghost"
          size="icon"
          class="h-7 w-7"
          @click="closePanel"
        >
          <X class="w-3.5 h-3.5" />
        </Button>
      </div>

      <!-- Content -->
      <div
        v-if="activeMedia"
        class="flex-1 overflow-y-auto p-4 space-y-4"
      >
        <!-- Preview Section -->
        <div class="relative h-40 rounded-xl bg-muted/30 border border-border/40 overflow-hidden flex items-center justify-center group shrink-0">
          <img 
            v-if="activeMedia.mime_type?.startsWith('image/')" 
            :src="activeMedia.url" 
            :alt="activeMedia.alt || activeMedia.name" 
            class="max-w-full max-h-full object-contain p-2" 
          >
          <div
            v-else
            class="flex flex-col items-center gap-2 text-muted-foreground"
          >
            <VideoIcon
              v-if="activeMedia.mime_type?.startsWith('video/')"
              class="w-12 h-12 stroke-1"
            />
            <FileIcon
              v-else
              class="w-12 h-12 stroke-1"
            />
            <span class="text-xs font-medium uppercase tracking-tighter">{{ activeMedia.mime_type }}</span>
          </div>
        </div>

        <!-- Info Grid -->
        <div class="grid grid-cols-2 gap-3 p-3 bg-muted/30 rounded-lg border border-border/20 text-xs">
          <div>
            <span class="text-muted-foreground block mb-0.5">{{ $t('media.modals.edit.size') }}</span>
            <span class="font-medium text-foreground">{{ formatFileSize(activeMedia.size) }}</span>
          </div>
          <div class="truncate">
            <span class="text-muted-foreground block mb-0.5">{{ $t('media.modals.edit.type') }}</span>
            <span
              class="font-medium text-foreground truncate"
              :title="activeMedia.mime_type"
            >{{ activeMedia.mime_type }}</span>
          </div>
          <div class="col-span-2 truncate">
            <span class="text-muted-foreground block mb-0.5">{{ $t('media.modals.edit.folder') }}</span>
            <span class="font-medium text-foreground truncate">
              {{ getFolderName(activeMedia.folder_id) }}
            </span>
          </div>
        </div>

        <div class="h-px bg-border/40 w-full my-4" />

        <!-- Edit Form -->
        <form
          class="space-y-4"
          @submit.prevent="handleSubmit"
        >
          <!-- Name -->
          <div class="space-y-1">
            <label class="text-xs font-medium text-foreground">
              {{ $t('media.modals.edit.name') }}
            </label>
            <Input
              v-model="form.name"
              type="text"
              required
              class="bg-background h-8 text-xs"
            />
          </div>

          <!-- Alt Text -->
          <div class="space-y-1">
            <label class="text-xs font-medium text-foreground">
              {{ $t('media.modals.edit.altText') }}
            </label>
            <Input
              v-model="form.alt"
              type="text"
              :placeholder="$t('media.modals.edit.altPlaceholder')"
              class="bg-background h-8 text-xs"
            />
          </div>

          <!-- Caption -->
          <div class="space-y-1">
            <label class="text-xs font-medium text-foreground">
              {{ $t('media.modals.edit.caption') }}
            </label>
            <Input
              v-model="form.caption"
              type="text"
              class="bg-background h-8 text-xs"
            />
          </div>

          <!-- Description -->
          <div class="space-y-1">
            <label class="text-xs font-medium text-foreground">
              {{ $t('media.modals.edit.description') }}
            </label>
            <textarea
              v-model="form.description"
              rows="2"
              class="w-full px-3 py-2 border border-input bg-background/50 text-foreground rounded-md focus:outline-none focus:ring-2 focus:ring-ring resize-none text-xs"
            />
          </div>

          <!-- Tags -->
          <div class="space-y-1">
            <label class="text-xs font-medium text-foreground">
              {{ $t('media.modals.edit.tags') }}
            </label>
            <div class="flex flex-wrap gap-1.5 mb-2">
              <div 
                v-for="tag in form.tags" 
                :key="tag" 
                class="inline-flex items-center px-2 py-0.5 rounded-md bg-primary/10 text-primary text-[10px] font-medium border border-primary/20"
              >
                {{ tag }}
                <button 
                  type="button"
                  class="ml-1.5 text-primary/60 hover:text-primary"
                  @click="removeTag(tag)"
                >
                  <X class="w-3 h-3" />
                </button>
              </div>
            </div>
            <Input
              v-model="tagInput"
              type="text"
              :placeholder="t('media.modals.edit.tagsPlaceholder')"
              class="bg-background h-8 text-xs"
              @keydown.enter.prevent="addTag"
              @keydown="onTagKeydown"
            />
          </div>

          <!-- Shared Status -->
          <div
            v-if="canManageMedia"
            class="flex items-start space-x-2 p-2.5 bg-blue-50/50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg"
          >
            <Checkbox 
              id="is_shared_sidebar" 
              v-model:checked="form.is_shared"
              class="mt-0.5"
            />
            <div class="flex-1">
              <label
                for="is_shared_sidebar"
                class="text-xs font-medium text-foreground cursor-pointer block"
              >
                {{ $t('media.modals.edit.isShared') }}
              </label>
              <p class="text-[10px] text-muted-foreground mt-0.5 leading-snug">
                {{ $t('media.modals.edit.isSharedHelp') }}
              </p>
            </div>
          </div>

          <div class="pt-3 sticky bottom-0 pb-2 mt-4 bg-transparent">
            <div class="flex gap-2">
              <Button 
                type="submit" 
                class="flex-1 h-9"
                size="sm"
                :disabled="saving || !isDirty"
              >
                {{ saving ? $t('media.modals.edit.saving') : $t('media.actions.save') }}
              </Button>
              <Button 
                type="button"
                variant="outline"
                size="icon"
                class="shrink-0 h-9 w-9"
                :title="$t('media.actions.copyUrl')"
                @click="copyUrl"
              >
                <Link class="w-4 h-4" />
              </Button>
              <Button 
                type="button"
                variant="outline"
                size="icon"
                class="shrink-0 h-9 w-9"
                :title="$t('media.actions.download')"
                @click="handleDownload"
              >
                <Download class="w-4 h-4" />
              </Button>
            </div>
          </div>
        </form>
      </div>

      <!-- Empty State -->
      <div
        v-else
        class="flex-1 flex flex-col items-center justify-center p-8 text-center text-muted-foreground"
      >
        <div class="w-16 h-16 rounded-full bg-muted/10 flex items-center justify-center mb-4">
          <Info class="w-8 h-8 opacity-20" />
        </div>
        <p class="text-sm font-medium">
          {{ $t('media.modals.edit.select_item') }}
        </p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, inject, computed, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { useToast } from '@/shared/composables/useToast';
import { useAuthStore } from '@/modules/Core/System/stores/auth';
import { MediaService } from '@/modules/Media/services/mediaService';
import {
  Download,
  FileIcon,
  Info,
  Link,
  VideoIcon,
  X,
} from 'lucide-vue-next';
import { Button, Input, Checkbox } from '@/shared/components/ui';
import { MediaManagerKey } from '@/engine/keys';


const { t } = useI18n();
const toast = useToast();
const authStore = useAuthStore();

const {
    showPropertiesPanel: isVisible,
    editingMedia: activeMedia,
    togglePropertiesPanel,
    folders,
    fetchMedia
} = inject(MediaManagerKey)!;

const saving = ref(false);
const tagInput = ref('');

interface MediaEditForm {
    name: string;
    alt: string;
    description: string;
    caption: string;
    is_shared: boolean;
    tags: string[];
}

const form = ref<MediaEditForm>({
    name: '',
    alt: '',
    description: '',
    caption: '',
    is_shared: false,
    tags: [],
});

const initialForm = ref<MediaEditForm | null>(null);



const isDirty = computed(() => {
    if (!initialForm.value) return false;
    return JSON.stringify(form.value) !== JSON.stringify(initialForm.value);
});

const canManageMedia = authStore.hasPermission('manage media');

const getFolderName = (id?: string | null) => {
    if (!id) return t('media.modals.edit.noFolder');
    const folder = folders.value.find(f => f.id === id);
    return folder ? folder.name : t('media.modals.edit.noFolder');
};

const formatFileSize = (bytes: number) => {
    if (!bytes) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
};

const closePanel = () => {
    togglePropertiesPanel(); // Toggles off
};

// Initialize form when activeMedia changes
watch(activeMedia, (media) => {
    if (media) {
        form.value = {
            name: media.name || '',
            alt: media.alt || '',
            description: media.description || '',
            caption: media.caption || '',
            is_shared: media.is_shared || false,
            tags: [...(media.tag_names || [])],
        };
        initialForm.value = JSON.parse(JSON.stringify(form.value));
    }
}, { immediate: true });

const onTagKeydown = (e: KeyboardEvent) => {
    if (e.key === ',') {
        e.preventDefault();
        addTag();
    }
};

const addTag = () => {
    const val = tagInput.value.trim().replace(/,$/, '');
    if (val && !form.value.tags.includes(val)) {
        form.value.tags = [...form.value.tags, val];
    }
    tagInput.value = '';
};

const removeTag = (tag: string) => {
    form.value.tags = form.value.tags.filter(t => t !== tag);
};

const copyUrl = async () => {
    if (activeMedia.value?.url) {
        try {
            await navigator.clipboard.writeText(activeMedia.value.url);
            toast.success.action(t('media.toast.urlCopied'));
        } catch (_err) {
            toast.error.default(t('media.file_manager.messages.copy_failed'));
        }
    }
};

const handleDownload = () => {
    if (activeMedia.value?.url) {
        const link = document.createElement('a');
        link.href = activeMedia.value.url;
        link.download = activeMedia.value.name;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
};

const handleSubmit = async () => {
    if (!activeMedia.value) return;
    
    saving.value = true;
    try {
        await MediaService.update(String(activeMedia.value.id), {
            name: form.value.name,
            alt: form.value.alt,
            description: form.value.description,
            caption: form.value.caption,
            is_shared: form.value.is_shared,
            tags: form.value.tags,
             // Note: Folder change requires move action, keep separate or impl here if API supports
        });
        
        initialForm.value = JSON.parse(JSON.stringify(form.value));
        await fetchMedia(); // Refresh list to reflect changes
        toast.success.action(t('media.messages.updateSuccess'));
    } catch (error) {
        toast.error.fromResponse(error);
    } finally {
        saving.value = false;
    }
};
</script>
