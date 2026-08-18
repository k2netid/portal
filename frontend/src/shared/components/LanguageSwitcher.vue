<template>
  <DropdownMenu>
    <DropdownMenuTrigger as-child>
      <button
        class="flex items-center justify-center p-2 rounded-md transition-colors hover:bg-muted text-muted-foreground hover:text-foreground focus:outline-none"
        :title="currentLanguage?.native_name || 'Select Language'"
      >
        <span class="text-xl leading-none">{{ currentLanguage ? getLanguageFlag(currentLanguage) : '🌐' }}</span>
      </button>
    </DropdownMenuTrigger>

    <DropdownMenuContent
      align="end"
      :side-offset="8"
      class="w-56"
    >
      <div class="max-h-64 overflow-y-auto py-1">
        <DropdownMenuItem
          v-for="language in languages"
          :key="language.id"
          class="cursor-pointer flex items-center space-x-3"
          :class="{
            'bg-primary/10 text-primary font-medium': currentLanguage?.id === language.id
          }"
          @click="selectLanguage(language)"
        >
          <span class="text-xl leading-none mr-2">{{ getLanguageFlag(language) }}</span>
          <div class="flex-1 min-w-0">
            <div class="font-medium text-sm truncate">
              {{ language.native_name || language.name }}
            </div>
            <div
              v-if="language.native_name && language.native_name !== language.name"
              class="text-xs text-muted-foreground truncate"
            >
              {{ language.name }}
            </div>
          </div>
          <Check
            v-if="currentLanguage?.id === language.id"
            class="w-4 h-4 ml-auto text-primary"
          />
        </DropdownMenuItem>
      </div>
    </DropdownMenuContent>
  </DropdownMenu>
</template>

<script setup lang="ts">
import { onMounted } from 'vue';
import { useLanguage, type Language } from '@/shared/composables/useLanguage';
import { 
    DropdownMenu, 
    DropdownMenuTrigger, 
    DropdownMenuContent, 
    DropdownMenuItem 
} from '@/shared/components/ui';
import {
  Check,
} from 'lucide-vue-next';

const {
    currentLanguage,
    languages,
    setLanguage,
    getLanguageFlag,
    initializeLanguage,
} = useLanguage();

const selectLanguage = async (language: Language) => {
    await setLanguage(language.code);
    
    // Emit event for parent components
    if (typeof window !== 'undefined') {
        window.dispatchEvent(new CustomEvent('language-changed', { detail: language }));
    }
};

onMounted(async () => {
    await initializeLanguage();
});
</script>

<style scoped>
/* RTL Support is now handled by Shadcn's align="end" and placement logic */
</style>

