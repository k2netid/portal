<template>
  <div class="canvas-grid-view">
    <div class="grid-header">
      <h2>{{ t('builder.canvas.gridViewTitle', 'All Canvases') }}</h2>
    </div>
    
    <div class="canvas-grid">
      <!-- Canvas Cards -->
      <div 
        v-for="canvas in canvases" 
        :key="canvas.id" 
        class="canvas-card"
        :class="{ 'canvas-card--active': activeCanvasId === canvas.id }"
        @click="switchCanvas(canvas.id)"
      >
        <div class="canvas-card__preview">
           <!-- Simplified preview or thumbnail -->
           <div class="preview-placeholder">
             <component :is="icons.Layout" :size="32" />
           </div>
           
           <div v-if="canvas.isMain" class="canvas-badge">Main</div>
        </div>
        
        <div class="canvas-card__info">
          <span class="canvas-title">{{ canvas.title }}</span>
          
          <button class="canvas-actions-btn" @click.stop="openMenu(canvas, $event)">
            <component :is="icons.MoreVertical" :size="14" />
          </button>
        </div>
      </div>
      
      <!-- Add New Card -->
      <div class="canvas-card canvas-card--add" @click="openAddModal">
        <div class="canvas-card__preview">
          <component :is="icons.Plus" :size="32" />
        </div>
        <div class="canvas-card__info">
          <span class="canvas-title">Add New Canvas</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, inject } from 'vue'
import Plus from 'lucide-vue-next/dist/esm/icons/plus.js';
import Layout from 'lucide-vue-next/dist/esm/icons/layout-dashboard.js';
import MoreVertical from 'lucide-vue-next/dist/esm/icons/ellipsis-vertical.js';
import { useI18n } from 'vue-i18n'
import type { BuilderInstance, Canvas } from '@/types/builder'

const builder = inject<BuilderInstance>('builder')
const { t } = useI18n()
const icons = { Layout, MoreVertical, Plus }

const canvases = computed<Canvas[]>(() => {
    const val = builder?.canvases?.value || builder?.canvases
    return Array.isArray(val) ? val : []
})

const activeCanvasId = computed(() => builder?.activeCanvasId?.value || builder?.activeCanvasId)

const switchCanvas = (id: string) => {
    if (builder?.switchCanvas) {
        builder.switchCanvas(id)
    }
}

const openMenu = (canvas: Canvas, event: MouseEvent) => {
    if (builder?.openContextMenu) {
        builder.openContextMenu(
            canvas.id, 
            event, 
            canvas.title, 
            canvas.isMain ? 'Main' : 'Canvas', 
            'canvas'
        )
    }
}

const openAddModal = () => {
    if (builder?.openAddCanvasModal) {
        builder.openAddCanvasModal()
    }
}
</script>

<style scoped>
.canvas-grid-view {
    padding: 40px;
    height: 100%;
    overflow-y: auto;
    background: #f1f5f9;
}

.grid-header {
    margin-bottom: 24px;
}

.grid-header h2 {
    font-size: 20px;
    font-weight: 600;
    color: #1e293b;
}

.canvas-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
    gap: 24px;
}

.canvas-card {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    border: 2px solid transparent;
    cursor: pointer;
    transition: all 0.2s ease;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.canvas-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
}

.canvas-card--active {
    border-color: var(--builder-accent);
}

.canvas-card__preview {
    height: 180px;
    background: #f8fafc;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    border-bottom: 1px solid #e2e8f0;
}

.preview-placeholder {
    color: #94a3b8;
}

.canvas-badge {
    position: absolute;
    top: 12px;
    left: 12px;
    background: #3b82f6;
    color: white;
    font-size: 10px;
    font-weight: 700;
    padding: 2px 8px;
    border-radius: 100px;
    text-transform: uppercase;
}

.canvas-card__info {
    padding: 12px 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.canvas-title {
    font-size: 14px;
    font-weight: 500;
    color: #334155;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.canvas-card--add {
    border: 2px dashed #cbd5e1;
    background: transparent;
    box-shadow: none;
}

.canvas-card--add .canvas-card__preview {
    background: transparent;
    border-bottom: none;
    color: #94a3b8;
}

.canvas-card--add:hover {
    border-color: var(--builder-accent);
    color: var(--builder-accent);
}

.canvas-actions-btn {
    background: none;
    border: none;
    color: #94a3b8;
    cursor: pointer;
    padding: 4px;
    border-radius: 4px;
}

.canvas-actions-btn:hover {
    background: #f1f5f9;
    color: #64748b;
}

.dropdown-item {
    display: flex;
    align-items: center;
    gap: 8px;
    width: 100%;
    padding: 8px 12px;
    font-size: 13px;
    color: #475569;
    border: none;
    background: none;
    cursor: pointer;
    text-align: left;
}

.dropdown-item:hover {
    background: #f8fafc;
}

.dropdown-item--danger {
    color: #ef4444;
}
.canvas-menu-dropdown {
  display: flex !important;
}

.canvas-menu {
  background: #1e293b;
  border-radius: 6px;
  overflow: hidden;
  min-width: 180px;
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.2), 0 4px 6px -2px rgba(0, 0, 0, 0.1);
  padding: 8px 0;
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.menu-item {
  display: flex;
  align-items: center;
  gap: 12px;
  width: 100%;
  padding: 10px 16px;
  border: none;
  background: transparent;
  color: #f8fafc;
  font-size: 13px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
  text-align: left;
}

.menu-item:hover {
  background: rgba(255, 255, 255, 0.1);
}

.menu-item--danger {
  color: #fb7185;
}

.menu-item--danger:hover {
  background: rgba(251, 113, 133, 0.1);
}

.menu-divider {
  height: 1px;
  background: rgba(255, 255, 255, 0.1);
  margin: 8px 0;
}

.menu-item svg {
  opacity: 0.7;
}

.menu-item:hover svg {
  opacity: 1;
}
</style>
