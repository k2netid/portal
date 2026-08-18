<template>
  <header class="top-toolbar">
    <!-- Left Section: Menu & Branding -->
    <div class="top-toolbar__left">
      <!-- Menu Button (Mobile Only) -->
      <IconButton 
        variant="ghost"
        :icon="sidebarVisible ? ChevronsLeft : Menu" 
        :active="sidebarVisible"
        :class="['toolbar-btn-lg', 'toolbar-btn-mobile', 'menu-toggle']" 
        @click="$emit('toggle-sidebar')" 
        :title="sidebarVisible ? t('builder.common.close') : t('builder.toolbar.menu')" 
      />
      
      <!-- Divider (Mobile Only) -->
      <BaseDivider orientation="vertical" :margin="6" class="mobile-only" />

      <!-- Branding (Logo with Override) -->
      <AdminLogo 
        :minimized="false" 
        class="admin-logo-override" 
        title="JA-Builder" 
        subtitle="Visual Editor"
      />
    </div>
    
    <!-- Center Section: Tools (Zoom, Wireframe, Devices) -->
    <div class="top-toolbar__center">
       <!-- Zoom -->
       <BaseDropdown align="center" width="auto">
            <template #trigger="{ open }">
              <IconButton 
                variant="ghost"
                :icon="Search" 
                :active="open" 
                :title="t('builder.toolbar.zoom')" 
              />
            </template>
            
            <template #default="{ close }">
              <button 
                v-for="opt in zoomOptions" 
                :key="opt"
                class="dropdown-item"
                :class="{ 'active': opt === zoom }"
                @click="selectZoom(opt); close()"
              >
                {{ opt }}%
              </button>
            </template>
       </BaseDropdown>



      <!-- Device Modes (Desktop) -->
      <div class="device-modes desktop-only">
        <IconButton 
          variant="ghost"
          :icon="Wand2"
          :active="builder.deviceModeType.value === 'auto'"
          :title="t('builder.toolbar.devices.auto') || 'Auto (Responsive)'"
          @click="builder.setDeviceModeAuto()"
        />
        <BaseDivider orientation="vertical" :margin="2" />
        <IconButton 
          v-for="mode in deviceModes"
          :key="mode.id"
          variant="ghost"
          :icon="icons[mode.icon] || mode.icon"
          :active="device === mode.id && builder.deviceModeType.value === 'manual'"
          :title="t('builder.toolbar.devices.' + mode.id)"
          @click="setDeviceMode(mode.id)"
        />
      </div>

      <!-- Device Modes (Mobile Dropdown) -->
      <div class="mobile-only">
        <BaseDropdown align="center" width="auto">
            <template #trigger="{ open }">
              <IconButton 
                variant="ghost"
                :icon="builder.deviceModeType.value === 'auto' ? Wand2 : (icons[currentDeviceIcon] || Monitor)" 
                :active="open" 
                :title="builder.deviceModeType.value === 'auto' ? 'Auto Mode' : t('builder.toolbar.devices.' + device)" 
                class="device-dropdown-trigger"
              />
            </template>
            
            <template #default="{ close }">
              <button 
                class="dropdown-item"
                :class="{ 'active': builder.deviceModeType.value === 'auto' }"
                @click="builder.setDeviceModeAuto(); close()"
              >
                <div class="flex items-center gap-2">
                    <Wand2 class="w-4 h-4" />
                    {{ t('builder.toolbar.devices.auto') || 'Auto (Responsive)' }}
                </div>
              </button>
              <BaseDivider orientation="horizontal" :margin="4" />
              <button 
                v-for="mode in deviceModes" 
                :key="mode.id"
                class="dropdown-item"
                :class="{ 'active': mode.id === device && builder.deviceModeType.value === 'manual' }"
                @click="setDeviceMode(mode.id); close()"
              >
                <div class="flex items-center gap-2">
                    <component :is="icons[mode.icon] || mode.icon" class="w-4 h-4" />
                    {{ t('builder.toolbar.devices.' + mode.id) }}
                </div>
              </button>
            </template>
        </BaseDropdown>
      </div>

      <BaseDivider orientation="vertical" :margin="6" />

      <!-- Fullview Toggle -->
      <IconButton 
        variant="ghost"
        :icon="isFullscreen ? Minimize : Maximize" 
        :active="isFullscreen"
        :title="isFullscreen ? t('builder.toolbar.exitFullscreen') || 'Exit Full View' : t('builder.toolbar.fullscreen') || 'Enter Full View'" 
        @click="toggleFullscreen"
      />
    </div>
    
    <!-- Right Section: Save & Status -->
    <div class="top-toolbar__right">
<!-- Save Actions -->
      <div class="save-actions">
        <button 
          class="save-draft-btn" 
          @click="$emit('save', 'draft')"
          :disabled="!builder.isDirty.value"
          :class="{ 'opacity-50 cursor-not-allowed': !builder.isDirty.value }"
          :title="t('builder.toolbar.saveDraft') || 'Save as Draft'"
        >
          {{ t('builder.toolbar.draft') || 'Draft' }}
        </button>
        <button 
          class="publish-btn" 
          @click="$emit('save', 'published')"
          :disabled="!builder.isDirty.value"
          :class="{ 'opacity-50 cursor-not-allowed': !builder.isDirty.value }"
          :title="builder.content.value.status === 'published' ? t('builder.toolbar.update') : t('builder.toolbar.publish')"
        >
          <Save class="w-3.5 h-3.5 mr-1.5" />
          {{ builder.content.value.status === 'published' ? (t('builder.toolbar.update') || 'Update') : (t('builder.toolbar.publish') || 'Publish') }}
        </button>
      </div>

      <BaseDivider orientation="vertical" :margin="6" />

      <!-- Cancel/Close Button -->
      <IconButton 
        variant="ghost"
        :icon="X" 
        class="toolbar-btn-lg cancel-btn" 
        @click="$emit('close-builder')" 
        :title="t('builder.toolbar.close') || 'Close'" 
      />
</div>
  </header>
</template>

<script setup lang="ts">
import { inject, computed } from 'vue'
import Menu from 'lucide-vue-next/dist/esm/icons/menu.js';
import Monitor from 'lucide-vue-next/dist/esm/icons/monitor.js';
import Tablet from 'lucide-vue-next/dist/esm/icons/tablet.js';
import Smartphone from 'lucide-vue-next/dist/esm/icons/smartphone.js';
import Search from 'lucide-vue-next/dist/esm/icons/search.js';
import Save from 'lucide-vue-next/dist/esm/icons/save.js';
import X from 'lucide-vue-next/dist/esm/icons/x.js';
import Maximize from 'lucide-vue-next/dist/esm/icons/maximize.js';
import Minimize from 'lucide-vue-next/dist/esm/icons/minimize.js';
import ChevronsLeft from 'lucide-vue-next/dist/esm/icons/chevrons-left.js';
import Wand2 from 'lucide-vue-next/dist/esm/icons/wand-sparkles.js';
import { useI18n } from 'vue-i18n'
import { DEVICE_MODES } from '@/components/builder/core/constants'
import { IconButton, BaseDropdown, BaseDivider } from '@/components/builder/ui'
import AdminLogo from '@/components/layouts/AdminLogo.vue'
import type { BuilderInstance } from '@/types/builder'

import type { Component } from 'vue';

// Icons mapping for dynamic components
const icons: Record<string, Component> = { Monitor, Tablet, Smartphone, Wand2 }

// Inject builder state
const builder = inject<BuilderInstance>('builder')!

// Props & Emits
defineProps<{
  sidebarVisible?: boolean
}>()
defineEmits<{
  (e: 'toggle-sidebar'): void
  (e: 'change-device', device: string): void
  (e: 'open-pages'): void
  (e: 'close-builder'): void
  (e: 'save', status: 'draft' | 'published'): void
}>()

const { t } = useI18n()

// State
const device = computed(() => builder?.device.value || 'desktop')
const zoom = computed(() => builder?.zoom.value || 100)


const deviceModes = Object.values(DEVICE_MODES)

const currentDeviceIcon = computed(() => {
    const mode = deviceModes.find(m => m.id === device.value)
    return mode ? (mode.icon as string) : 'Monitor'
})

const setDeviceMode = (modeId: string) => {
    builder.setDeviceMode(modeId as 'desktop' | 'tablet' | 'mobile')
}

// Zoom Options
const zoomOptions = [50, 75, 100, 125, 150]

const selectZoom = (val: number) => {
    if (builder) {
        builder.zoom.value = val
    }
}

const isFullscreen = computed(() => !!builder?.isFullscreen.value)

const toggleFullscreen = () => {
    if (builder) {
        builder.isFullscreen.value = !builder.isFullscreen.value
    }
}


</script>

<style scoped>
.top-toolbar {  
  /* Force dark theme text for Top Toolbar since background is always dark */
  --builder-text-primary: #ffffff;
  --builder-text-secondary: rgba(255, 255, 255, 0.7);
  --builder-border: rgba(255, 255, 255, 0.1);
  --builder-border-layout: rgba(255, 255, 255, 0.1); /* Force dark border in Light Mode */
  --builder-bg-tertiary: rgba(255, 255, 255, 0.1);
  --builder-accent: #2059ea;

  display: flex;
  align-items: center;
  justify-content: space-between;
  height: var(--toolbar-height);
  padding: 0 20px 0 6px;
  background: var(--builder-bg-topbar);
  border-bottom: 1px solid var(--builder-border-layout);
  color: var(--builder-text-primary);
  position: relative; /* For absolute center */
}

.top-toolbar__left,
.top-toolbar__right {
  display: flex;
  align-items: center;
  gap: 8px;
  z-index: 2; /* Above center */
}

/* True center using absolute positioning */
.top-toolbar__center {
    position: absolute;
    left: 50%;
    top: 50%;
    transform: translate(-50%, -50%);
    display: flex;
    align-items: center;
    gap: 8px;
    z-index: 1;
}

@media (max-width: 768px) {
    .top-toolbar__center {
        position: static;
        transform: none;
        flex: 1;
        justify-content: center;
        gap: 4px;
        order: 2;
    }
    
    .top-toolbar__left {
        order: 1;
        flex: 0;
    }
    
    .top-toolbar__right {
        order: 3;
        flex: 0;
        gap: 4px;
    }

    .device-modes {
        display: none !important; /* Hide device modes on small mobile */
    }
}

/* Branding */
/* Override AdminLogo styles for toolbar context */
:deep(.admin-logo-override) {
    color: white;
}
:deep(.admin-logo-override .text-foreground) {
    color: white !important;
}
:deep(.admin-logo-override .text-muted-foreground) {
    color: rgba(255,255,255,0.7) !important;
}
/* Make the logo box border crisp without shadow */
:deep(.admin-logo-override .border-primary) {
    border-color: #10b981 !important; /* Emerald green */
    border-width: 2px !important;
    box-shadow: none !important; /* Remove blur/shadow */
}
:deep(.admin-logo-override .shadow-sm) {
    box-shadow: none !important; /* Remove the shadow-sm class effect */
}
:deep(.admin-logo-override [class*="text-primary"]) {
    color: #10b981 !important;
}

/* All toolbar buttons - crisp borders without shadow */
.top-toolbar :deep(.icon-button),
.top-toolbar :deep(button) {
    box-shadow: none !important;
}

.dropdown-item {
  padding: 6px 12px;
  background: none;
  border: none;
  border-radius: var(--border-radius-sm);
  color: var(--builder-text-secondary);
  font-size: var(--font-size-sm);
  text-align: left;
  cursor: pointer;
  white-space: nowrap;
}

.dropdown-item:hover {
  background: var(--builder-bg-tertiary);
  color: var(--builder-text-primary);
}

.dropdown-item.active {
  background: var(--builder-accent);
  color: white;
}

.device-modes {
  display: flex;
  gap: 4px;
}

@media (max-width: 768px) {
  .top-toolbar {
    padding: 0 8px;
  }
  
  .builder-title-badge {
      display: none; /* Hide title on small screens */
  }
  
  /* Hide logo on mobile to prevent overlap */
  :deep(.admin-logo-override) {
      display: none;
  }

  /* Hide zoom on very small screens */
  @media (max-width: 480px) {
     .top-toolbar__center {
         display: none;
     }
  }
}

/* Cancel Button */
.cancel-btn {
    color: var(--builder-text-secondary);
}
.cancel-btn:hover {
    color: var(--builder-error);
    background: rgba(231, 76, 60, 0.1) !important;
    border-color: var(--builder-error) !important;
}

/* Save Actions */
.save-actions {
    display: flex;
    align-items: center;
    gap: 8px;
}

.save-draft-btn {
    font-size: 12px;
    font-weight: 600;
    color: var(--builder-text-secondary);
    padding: 6px 12px;
    border-radius: 6px;
    background: transparent;
    border: 1px solid var(--builder-border);
    cursor: pointer;
    transition: all 0.2s;
}

.save-draft-btn:hover {
    background: rgba(255, 255, 255, 0.05);
    color: var(--builder-text-primary);
    border-color: var(--builder-text-secondary);
}

.publish-btn {
    display: flex;
    align-items: center;
    font-size: 12px;
    font-weight: 700;
    color: white;
    padding: 6px 16px;
    border-radius: 6px;
    background: #10b981; /* Emerald green */
    border: 1px solid #10b981;
    cursor: pointer;
    transition: all 0.2s;
    box-shadow: 0 2px 4px rgba(16, 185, 129, 0.2);
}

.publish-btn:hover {
    background: #059669; /* Darker emerald */
    border-color: #059669;
    transform: translateY(-1px);
    box-shadow: 0 4px 6px rgba(16, 185, 129, 0.3);
}

.publish-btn:active {
    transform: translateY(0);
}

/* Mobile Visibility Helpers */
.mobile-only {
    display: none;
}
.desktop-only {
    display: flex;
}

@media (max-width: 768px) {
    .mobile-only {
        display: flex;
    }
    .desktop-only {
        display: none !important;
    }
}

/* Toolbar Fixes */
.toolbar-btn-mobile {
    display: none;
}
@media (max-width: 768px) {
    .toolbar-btn-mobile {
        display: flex;
    }
}

/* Menu Toggle Button - Active State Styling */
.menu-toggle--active {
    background: var(--builder-accent, #2059ea) !important;
    border-color: var(--builder-accent, #2059ea) !important;
    color: white !important;
}
.menu-toggle--active:hover {
    background: rgba(32, 89, 234, 0.8) !important;
}

/* Force specific button styling for Toolbar to prevent "White Block" on light mode */
/* Force specific button styling for Toolbar to prevent "White Block" on light mode */
:deep(.icon-button) {
    background: transparent !important;
    border-color: rgba(255, 255, 255, 0.1) !important;
    color: rgba(255, 255, 255, 0.8) !important;
}

:deep(.icon-button:hover) {
    background-color: rgba(255, 255, 255, 0.1) !important;
    color: #ffffff !important;
}

/* Active state now handled by IconButton switching to primary (default) variant */
:deep(.icon-button.border-white\/20) {
    border-color: rgba(255, 255, 255, 0.4) !important;
    box-shadow: 0 0 10px rgba(32, 89, 234, 0.3) !important;
}
</style>
