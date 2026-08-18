<template>
  <Dialog
    :open="open"
    @update:open="$emit('update:open', $event)"
  >
    <DialogContent class="console-dialog-preview">
      <DialogHeader>
        <DialogTitle class="flex items-center gap-2">
          <Eye class="w-5 h-5" />
          {{ t('layout.menus.actions.preview') }}
        </DialogTitle>
        <DialogDescription>
          {{ t('layout.menus.messages.previewDescription') || 'Preview how your menu will appear on the frontend' }}
        </DialogDescription>
      </DialogHeader>

      <!-- Preview Controls -->
      <div class="flex items-center gap-2 py-2 border-b border-border">
        <Button 
          v-for="style in previewStyles" 
          :key="style.value"
          size="sm" 
          :variant="activeStyle === style.value ? 'default' : 'outline'"
          @click="activeStyle = style.value"
        >
          <component
            :is="style.icon"
            data-icon="inline-start" class="size-4 shrink-0"
          />
          {{ style.label }}
        </Button>
      </div>

      <!-- Preview Area -->
      <div class="flex-1 min-h-[300px] bg-background rounded-lg border border-border mt-4 overflow-visible">
        <!-- Desktop Header Preview -->
        <div
          v-if="activeStyle === 'header'"
          class="bg-card border-b border-border"
        >
          <div class="px-6 py-4">
            <div class="flex items-center justify-between">
              <!-- Fake Logo -->
              <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-primary flex items-center justify-center text-primary-foreground font-bold">
                  JA
                </div>
                <span class="text-lg font-bold">Site Name</span>
              </div>

              <!-- Navigation -->
              <nav class="flex items-center gap-1">
                <template
                  v-for="item in items"
                  :key="item.id || item._temp_id"
                >
                  <!-- Item with children -->
                  <div 
                    v-if="item.children && item.children.length > 0"
                    class="group relative"
                  >
                    <button class="px-3 py-2 text-sm font-medium text-muted-foreground hover:text-foreground hover:bg-muted rounded-md flex items-center gap-1 transition-colors">
                      <component
                        :is="getIcon(item.icon)"
                        v-if="item.icon"
                        class="w-4 h-4"
                      />
                      {{ item.title || 'Untitled' }}
                      <ChevronDown class="w-3 h-3" />
                    </button>
                    <!-- Dropdown Preview -->
                    <div class="absolute top-full left-0 pt-1 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-[opacity,transform] z-[100]">
                      <div class="bg-card border border-border rounded-lg shadow-xl p-2 min-w-[200px]">
                        <template
                          v-for="child in item.children"
                          :key="child.id || child._temp_id"
                        >
                          <div class="px-3 py-2 text-sm text-muted-foreground hover:text-foreground hover:bg-muted rounded-md cursor-pointer flex items-center gap-2">
                            <component
                              :is="getIcon(child.icon)"
                              v-if="child.icon"
                              class="w-4 h-4"
                            />
                            {{ child.title || 'Untitled' }}
                          </div>
                        </template>
                      </div>
                    </div>
                  </div>
                  <!-- Simple item -->
                  <button 
                    v-else
                    class="px-3 py-2 text-sm font-medium text-muted-foreground hover:text-foreground hover:bg-muted rounded-md flex items-center gap-1 transition-colors"
                  >
                    <component
                      :is="getIcon(item.icon)"
                      v-if="item.icon"
                      class="w-4 h-4"
                    />
                    {{ item.title || 'Untitled' }}
                  </button>
                </template>
              </nav>

              <!-- Fake Actions -->
              <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-full bg-muted" />
              </div>
            </div>
          </div>
        </div>

        <!-- Mobile Menu Preview -->
        <div
          v-else-if="activeStyle === 'mobile'"
          class="bg-card max-w-sm mx-auto border-x border-border"
        >
          <!-- Mobile Header -->
          <div class="flex items-center justify-between p-4 border-b border-border">
            <div class="flex items-center gap-2">
              <div class="w-6 h-6 rounded bg-primary" />
              <span class="font-bold">Site</span>
            </div>
            <Menu class="w-5 h-5" />
          </div>
          <!-- Mobile Nav List -->
          <div class="p-2 space-y-1">
            <template
              v-for="item in items"
              :key="item.id || item._temp_id"
            >
              <div 
                class="px-3 py-2.5 text-sm font-medium rounded-md hover:bg-muted flex items-center justify-between cursor-pointer"
                @click="toggleMobileItem(item)"
              >
                <div class="flex items-center gap-2">
                  <component
                    :is="getIcon(item.icon)"
                    v-if="item.icon"
                    class="w-4 h-4"
                  />
                  {{ item.title || 'Untitled' }}
                </div>
                <ChevronDown 
                  v-if="item.children && item.children.length > 0" 
                  class="w-4 h-4 transition-transform"
                  :class="{ 'rotate-180': expandedMobile.includes(item.id || item._temp_id!) }"
                />
              </div>
              <!-- Mobile Submenu -->
              <div 
                v-if="item.children && item.children.length > 0 && expandedMobile.includes(item.id || item._temp_id!)"
                class="ml-4 pl-3 border-l border-border space-y-1"
              >
                <div 
                  v-for="child in item.children" 
                  :key="child.id || child._temp_id"
                  class="px-3 py-2 text-sm text-muted-foreground hover:text-foreground rounded-md hover:bg-muted cursor-pointer flex items-center gap-2"
                >
                  <component
                    :is="getIcon(child.icon)"
                    v-if="child.icon"
                    class="w-4 h-4"
                  />
                  {{ child.title || 'Untitled' }}
                </div>
              </div>
            </template>
          </div>
        </div>

        <!-- Footer Preview -->
        <div
          v-else-if="activeStyle === 'footer'"
          class="bg-muted/30 p-8"
        >
          <div class="max-w-4xl mx-auto">
            <div class="grid grid-cols-4 gap-8">
              <template
                v-for="item in items"
                :key="item.id || item._temp_id"
              >
                <div>
                  <h4 class="font-semibold text-sm mb-3 flex items-center gap-2">
                    <component
                      :is="getIcon(item.icon)"
                      v-if="item.icon"
                      class="w-4 h-4"
                    />
                    {{ item.title || 'Untitled' }}
                  </h4>
                  <ul
                    v-if="item.children && item.children.length > 0"
                    class="space-y-2"
                  >
                    <li 
                      v-for="child in item.children" 
                      :key="child.id || child._temp_id"
                      class="text-sm text-muted-foreground hover:text-foreground cursor-pointer flex items-center gap-2"
                    >
                      <component
                        :is="getIcon(child.icon)"
                        v-if="child.icon"
                        class="w-3 h-3"
                      />
                      {{ child.title || 'Untitled' }}
                    </li>
                  </ul>
                </div>
              </template>
            </div>
          </div>
        </div>
      </div>

      <DialogFooter class="mt-4">
        <Button
          variant="outline"
          @click="$emit('update:open', false)"
        >
          {{ t('common.actions.close') }}
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import {
  Bell,
  ChevronDown,
  Circle,
  Eye,
  FileText,
  Globe,
  Grid,
  Heart,
  HelpCircle,
  Home,
  Info,
  Layers,
  Layout,
  LayoutGrid,
  List,
  LogIn,
  LogOut,
  Mail,
  Menu,
  Monitor,
  Search,
  Settings,
  ShoppingCart,
  Smartphone,
  Star,
  User,
  UserPlus,
  Zap,
} from 'lucide-vue-next';
import type { Component } from 'vue';

const iconMap: Record<string, Component> = {
    Home, User, Mail, Settings, HelpCircle, Info, Globe, FileText, 
    Layers, Layout, Zap, Star, Heart, List, Grid, Search, 
    LogOut, LogIn, UserPlus, ShoppingCart, Bell,
    Eye, ChevronDown, Monitor, Smartphone, Menu, LayoutGrid, Circle
};
import type { MenuItem } from '@/modules/Content/Layout/types/menu';

// UI Components
import Dialog from '@/shared/components/ui/Dialog.vue';
import DialogContent from '@/shared/components/ui/DialogContent.vue';
import DialogHeader from '@/shared/components/ui/DialogHeader.vue';
import DialogTitle from '@/shared/components/ui/DialogTitle.vue';
import DialogDescription from '@/shared/components/ui/DialogDescription.vue';
import DialogFooter from '@/shared/components/ui/DialogFooter.vue';
import Button from '@/shared/components/ui/Button.vue';

const { t } = useI18n();

defineProps<{
    open: boolean;
    items: MenuItem[];
}>();

defineEmits<{
    (e: 'update:open', value: boolean): void;
}>();

const activeStyle = ref<string>('header');
const expandedMobile = ref<(string)[]>([]);

const previewStyles = [
    { value: 'header', label: 'Header', icon: Monitor },
    { value: 'mobile', label: 'Mobile', icon: Smartphone },
    { value: 'footer', label: 'Footer', icon: LayoutGrid }
];

const getIcon = (iconName: string) => {
    return iconMap[iconName] || iconMap.Circle;
};

const toggleMobileItem = (item: MenuItem) => {
    const id = item.id || item._temp_id!;
    const index = expandedMobile.value.indexOf(id);
    if (index === -1) {
        expandedMobile.value.push(id);
    } else {
        expandedMobile.value.splice(index, 1);
    }
};
</script>
