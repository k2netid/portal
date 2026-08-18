<template>
  <Popover v-model:open="open">
    <PopoverTrigger as-child>
      <slot name="trigger">
        <Button
          variant="outline"
          role="combobox"
          :aria-expanded="open"
          class="w-full justify-between h-9"
        >
          <div class="flex items-center gap-2">
            <component
              :is="getIconComponent(selectedIcon)"
              v-if="selectedIcon"
              class="w-4 h-4"
            />
            <span
              v-else
              class="text-muted-foreground"
            >{{ placeholderText }}</span>
            <span
              v-if="selectedIcon"
              class="text-sm"
            >{{ selectedIcon }}</span>
          </div>
          <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
        </Button>
      </slot>
    </PopoverTrigger>
    <PopoverContent
      class="w-[340px] p-0"
      align="start"
    >
      <div class="p-2 border-b">
        <Input
          v-model="searchQuery"
          :placeholder="t('common.labels.iconPicker.search')"
          class="h-8 mb-2"
        />
        
        <Tabs
          v-model="activeTab"
          class="w-full"
        >
          <TabsList class="grid grid-cols-4 h-auto p-1 gap-1">
            <TabsTrigger
              value="all"
              class="text-[10px] h-6 px-1"
            >
              {{ t('common.labels.iconPicker.all') }}
            </TabsTrigger>
            <TabsTrigger 
              v-for="(_, category) in displayCategories" 
              :key="category" 
              :value="category"
              class="text-[10px] h-6 px-1 truncate"
              :title="category"
            >
              {{ category }}
            </TabsTrigger>
          </TabsList>
        </Tabs>
      </div>

      <div class="max-h-[300px] overflow-y-auto p-2 scrollbar-thin">
        <div
          v-if="filteredIcons.length === 0"
          class="text-center py-6 text-sm text-muted-foreground"
        >
          {{ t('common.labels.iconPicker.empty') }}
        </div>
        <div class="grid grid-cols-6 gap-1">
          <button
            v-for="icon in filteredIcons"
            :key="icon"
            type="button"
            class="p-2 rounded-md hover:bg-muted transition-colors flex items-center justify-center relative group"
            :class="{ 'bg-primary/10 ring-1 ring-primary': selectedIcon === icon }"
            :title="icon"
            @click="selectIcon(icon)"
          >
            <component
              :is="getIconComponent(icon)"
              class="w-5 h-5"
            />
          </button>
        </div>
      </div>
      <div
        v-if="modelValue"
        class="p-2 border-t"
      >
        <Button
          variant="ghost"
          size="sm"
          class="w-full text-destructive hover:text-destructive"
          @click="clearIcon"
        >
          <X class="w-4 h-4 mr-2" />
          {{ t('common.labels.iconPicker.clear') }}
        </Button>
      </div>
    </PopoverContent>
  </Popover>
</template>

<script setup lang="ts">
import { logger } from '@/shared/utils/logger';
import { ref, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import Button from './Button.vue';
import Input from './Input.vue';
import Popover from './Popover.vue';
import PopoverTrigger from './PopoverTrigger.vue';
import PopoverContent from './PopoverContent.vue';
import Tabs from './Tabs.vue';
import TabsList from './TabsList.vue';
import TabsTrigger from './TabsTrigger.vue';
import {
  ChevronsUpDown,
  Circle,
  X,
} from 'lucide-vue-next';
import { iconCategories } from '@/config/icon-categories';

import type { Component } from 'vue';

// Keep a few common icons for the trigger and initial render
const commonIcons: Record<string, Component> = { Circle };

const { t } = useI18n();

const props = withDefaults(defineProps<{
  modelValue?: string;
  placeholder?: string;
}>(), {
  modelValue: '',
  placeholder: undefined,
});

const placeholderText = computed(() => props.placeholder || t('common.labels.iconPicker.placeholder'));

const emit = defineEmits<{
  'update:modelValue': [value: string];
}>();

const open = ref(false);
const searchQuery = ref('');
const activeTab = ref('General');

const selectedIcon = computed(() => props.modelValue);

const LucideIconsBatch = ref<Record<string, unknown> | null>(null);
const isLoadingIcons = ref(false);

const loadIcons = async () => {
    if (LucideIconsBatch.value || isLoadingIcons.value) return;
    isLoadingIcons.value = true;
    try {
        LucideIconsBatch.value = await import('lucide-vue-next') as unknown as Record<string, unknown>;
    } catch (err) {
        logger.error('Failed to load icons:', err);
    } finally {
        isLoadingIcons.value = false;
    }
};

// Start loading when popover opens
import { watch } from 'vue';
watch(open, (isOpen) => {
    if (isOpen) loadIcons();
});

// Get ALL icon names from Lucide once loaded
const allIconNames = computed(() => {
    if (!LucideIconsBatch.value) return [];
    return Object.keys(LucideIconsBatch.value).filter(key => {
        if (key === 'default' || key === 'createLucideIcon' || key === 'Icon' || !/^[A-Z]/.test(key)) {
            return false;
        }
        const exp = (LucideIconsBatch.value as Record<string, unknown>)[key];
        return typeof exp === 'object' || typeof exp === 'function';
    });
});

// Map categories for display
const displayCategories = computed(() => {
    return iconCategories; 
});

const filteredIcons = computed(() => {
  const icons: string[] = activeTab.value === 'all'
      ? allIconNames.value
      : (iconCategories as Record<string, string[]>)[activeTab.value] || [];

  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    return icons.filter(icon => icon.toLowerCase().includes(query));
  }

  return icons;
});

const getIconComponent = (iconName: string) => {
  if (commonIcons[iconName]) return commonIcons[iconName];
  if (LucideIconsBatch.value && LucideIconsBatch.value[iconName]) return LucideIconsBatch.value[iconName] as Component;
  return commonIcons.Circle;
};

const selectIcon = (icon: string) => {
  emit('update:modelValue', icon);
  open.value = false;
  searchQuery.value = '';
};

const clearIcon = () => {
  emit('update:modelValue', '');
  open.value = false;
};
</script>
