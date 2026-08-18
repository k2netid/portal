<template>
  <div 
    class="sticky top-[64px] z-30 transition-colors duration-300 ease-in-out"
    :class="[
      isSidebarOpen ? 'w-full' : 'w-12',
      'bg-background/80 backdrop-blur-md border border-border shadow-sm rounded-xl overflow-hidden mb-6'
    ]"
  >
    <div
      class="flex items-center"
      :class="isSidebarOpen ? 'justify-between px-4 py-2.5' : 'flex-col py-4 gap-4 px-0'"
    >
      <!-- Left Side / Top (Toggle) -->
      <Button 
        variant="ghost" 
        size="icon" 
        :title="isSidebarOpen ? 'Collapse Settings' : 'Expand Settings'"
        class="h-8 w-8 hover:bg-accent/50"
        @click="$emit('toggle-sidebar')"
      >
        <PanelRightClose
          v-if="isSidebarOpen"
          class="w-4 h-4 text-muted-foreground"
        />
        <PanelRightOpen
          v-else
          class="w-4 h-4 text-muted-foreground"
        />
      </Button>

      <!-- Right Side / Bottom (Actions) -->
      <div 
        class="flex items-center gap-2"
        :class="isSidebarOpen ? '' : 'flex-col w-full'"
      >
        <template v-if="isSidebarOpen">
          <Button 
            variant="ghost" 
            size="sm" 
            class="text-muted-foreground hover:text-foreground h-8"
            @click="$emit('cancel')"
          >
            {{ $t('publishing.content.form.cancel') }}
          </Button>
          <Button
            :disabled="loading || disabled"
            size="sm"
            class="h-8 px-4 inline-flex items-center gap-1.5 bg-primary hover:bg-primary/90 text-primary-foreground shadow-sm"
            @click="$emit('save')"
          >
            <Loader2
              v-if="loading"
              data-icon="inline-start" class="size-3.5 shrink-0 animate-spin"
            />
            <Save
              v-else
              data-icon="inline-start" class="size-3.5 shrink-0"
            />
            {{ saveText }}
          </Button>
        </template>
        <template v-else>
          <!-- Icon-only mode when collapsed -->
          <Button
            variant="ghost"
            size="icon"
            :disabled="loading || disabled"
            class="h-8 w-8 bg-primary/10 hover:bg-primary/20 text-primary rounded-lg"
            :title="saveText"
            @click="$emit('save')"
          >
            <Loader2
              v-if="loading"
              class="w-4 h-4 animate-spin"
            />
            <Save
              v-else
              class="w-4 h-4"
            />
          </Button>
                    
          <Button 
            variant="ghost" 
            size="icon" 
            class="h-8 w-8 text-muted-foreground hover:text-destructive hover:bg-destructive/10"
            title="Cancel"
            @click="$emit('cancel')"
          >
            <X class="w-4 h-4" />
          </Button>
        </template>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { Button } from '@/shared/components/ui';
import {
  Loader2,
  PanelRightClose,
  PanelRightOpen,
  Save,
  X,
} from 'lucide-vue-next';

const props = withDefaults(defineProps<{
    isSidebarOpen?: boolean;
    loading?: boolean;
    disabled?: boolean;
    isEdit?: boolean;
}>(), {
    isSidebarOpen: true,
    loading: false,
    disabled: false,
    isEdit: false
});

defineEmits<{
    (e: 'toggle-sidebar'): void;
    (e: 'save'): void;
    (e: 'cancel'): void;
}>();

const saveText = computed(() => {
    if (props.isEdit) {
        return props.loading ? 'Updating...' : 'Update Content';
    }
    return props.loading ? 'Creating...' : 'Create Content';
});
</script>
