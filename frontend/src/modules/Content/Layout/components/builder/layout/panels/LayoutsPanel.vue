<template>
  <div class="layouts-panel">
    <!-- Header/Search -->
    <div class="panel-header">
        <div class="search-container">
            <Search :size="14" class="search-icon" />
            <input 
                type="text" 
                v-model="searchTerm" 
                :placeholder="$t('builder.panels.layouts.search', 'Search Templates...')"
                class="search-input"
            />
        </div>
    </div>

    <!-- Category Pills -->
    <div class="categories-nav">
        <div class="categories-scroll">
            <button 
                v-for="cat in categories" 
                :key="cat"
                class="category-pill"
                :class="{ 'active': activeCategory === cat }"
                @click="activeCategory = cat"
            >
                {{ cat === 'all' ? $t('builder.common.all', 'All') : cat.charAt(0).toUpperCase() + cat.slice(1) }}
            </button>
        </div>
    </div>

    <!-- Templates Grid -->
    <draggable
        v-model="filteredTemplates"
        :group="{ name: 'section', pull: 'clone', put: false }"
        :clone="cloneTemplate"
        item-key="id"
        class="layouts-content"
    >
      <template #item="{ element: template }">
        <div class="template-card" @dblclick="insertTemplate(template)">
            <div class="template-visual">
                <div class="visual-accent" :class="`accent-${template.category}`"></div>
                <div class="template-icon-circle" :class="`bg-${template.category}`">
                     <component :is="getPreviewIcon(template)" :size="20" class="template-icon" />
                </div>
                <!-- Interactive Overlay -->
                <div class="card-overlay">
                    <button class="action-btn add" @click.stop="insertTemplate(template)" :title="$t('builder.actions.add', 'Add Section')">
                        <Plus :size="16" />
                    </button>
                </div>
            </div>
            <div class="template-details">
                <div class="template-meta">
                    <h5 class="template-title">{{ template.name }}</h5>
                    <p v-if="template.description" class="template-description">{{ template.description }}</p>
                </div>
            </div>
        </div>
      </template>
    </draggable>
    
    <!-- Empty State -->
    <div v-if="filteredTemplates.length === 0" class="empty-state">
        <div class="empty-icon-box">
            <LayoutTemplate :size="32" />
        </div>
        <h4>{{ $t('builder.panels.layouts.emptyTitle', 'No templates found') }}</h4>
        <p>{{ $t('builder.panels.layouts.emptyDesc', 'Try adjusting your search or category.') }}</p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, inject } from 'vue';
import { useI18n } from 'vue-i18n';
import Plus from 'lucide-vue-next/dist/esm/icons/plus.js';
import LayoutTemplate from 'lucide-vue-next/dist/esm/icons/layout-template.js';
import Sparkles from 'lucide-vue-next/dist/esm/icons/sparkles.js';
import Layout from 'lucide-vue-next/dist/esm/icons/layout-dashboard.js';
import Users from 'lucide-vue-next/dist/esm/icons/users.js';
import MessageSquare from 'lucide-vue-next/dist/esm/icons/message-square.js';
import FileText from 'lucide-vue-next/dist/esm/icons/file-text.js';
import Megaphone from 'lucide-vue-next/dist/esm/icons/megaphone.js';
import Search from 'lucide-vue-next/dist/esm/icons/search.js';
import draggable from 'vuedraggable';
import { sectionTemplates } from '@/components/builder/templates/SectionTemplates';
import { pageTemplates } from '@/components/builder/templates/PageTemplates';
import ModuleRegistry from '@/components/builder/core/ModuleRegistry';
import type { BuilderInstance, BlockInstance } from '@/types/builder';
import type { SectionTemplate } from '@/components/builder/templates/SectionTemplates';

interface Template {
  id: string;
  name: string;
  description?: string;
  category: string;
  templateType: 'section' | 'page';
  factory?: () => BlockInstance | BlockInstance[];
}

const { t } = useI18n();
const builder = inject<BuilderInstance>('builder');

const searchTerm = ref('');
const activeCategory = ref('all');

// Extract unique categories including 'pages'
const categories = computed(() => {
    const cats = new Set(sectionTemplates.map((t: SectionTemplate) => t.category).filter(Boolean));
    return ['all', 'pages', ...Array.from(cats).sort()] as string[];
});

// Combined and filtered templates
const filteredTemplates = computed<Template[]>(() => {
    let all: Template[] = [];
    
    // Add sections
    sectionTemplates.forEach((s) => all.push({ 
        ...s, 
        templateType: 'section' 
    } as Template));
    
    // Add pages
    (pageTemplates as import('@/components/builder/templates/PageTemplates').PageTemplate[]).forEach((p) => all.push({ 
        ...p, 
        category: 'pages', 
        templateType: 'page' 
    } as Template));

    return all.filter(template => {
        const matchesSearch = template.name.toLowerCase().includes(searchTerm.value.toLowerCase()) || 
                              (template.description && template.description.toLowerCase().includes(searchTerm.value.toLowerCase()));
        const matchesCategory = activeCategory.value === 'all' || template.category === activeCategory.value;
        
        return matchesSearch && matchesCategory;
    });
});

// Icon mapping based on category with vibrant colors
const getPreviewIcon = (template: Template) => {
  if (template.templateType === 'page') return LayoutTemplate;
  
  const iconMap: Record<string, import('vue').Component> = {
    hero: Sparkles,
    features: Layout,
    content: FileText,
    cta: Megaphone,
    team: Users,
    header: FileText,
    contact: MessageSquare
  };
  return iconMap[template.category] || Layout;
};

// Clone function for Drag and Drop
const cloneTemplate = (template: Template) => {
    if (!template.factory) return null;
    
    // If it's a page, we shouldn't really drag it? 
    // But if we do, it might return an array of sections which Draggable might not handle well as a single item drop.
    // For now, let's treat Page templates as "Click to Insert/Replace" only or return first block if dragged.
    if (template.templateType === 'page') {
        const blocks = template.factory();
        return Array.isArray(blocks) ? blocks[0] : blocks; // Dragging a page only drags the first block for now
    }
    
    const blocks = template.factory();
    // Factory might return array or single object depending on template implementation.
    const block = Array.isArray(blocks) ? blocks[0] : (blocks as BlockInstance);

    const regenerateIds = (node: BlockInstance) => {
        node.id = ModuleRegistry.generateId();
        if (node.children) {
            node.children.forEach(regenerateIds);
        }
    };
    regenerateIds(block);
    return block;
};

// Click to Insert
const insertTemplate = async (template: Template) => {
    if (!builder || !template.factory) return;
    
    // Handle Page Templates (Full Content Replacement)
    if (template.templateType === 'page') {
        const confirmed = await builder.confirm?.({
            title: t('builder.modals.confirm.resetLayout'),
            message: t('builder.modals.confirm.resetLayoutDesc'),
            confirmText: t('builder.modals.confirm.confirm'),
            cancelText: t('builder.modals.confirm.cancel'),
            type: 'warning'
        });
        if (confirmed) {
            const blocks = template.factory();
            const blocksArray = Array.isArray(blocks) ? blocks : [blocks as BlockInstance];
            
            // Regenerate all IDs for safety
            const regenerateAll = (nodes: BlockInstance[]) => {
                nodes.forEach(node => {
                    node.id = ModuleRegistry.generateId();
                    if (node.children) regenerateAll(node.children);
                });
            };
            regenerateAll(blocksArray);
            
            builder.blocks.value = blocksArray;
            builder.takeSnapshot();
        }
        return;
    }

    // Handle Section Templates (Insertion)
    const block = cloneTemplate(template);
    if (!block) return;

    // Access the value of the computed ref
    if (!builder.blocks.value) builder.blocks.value = [];
    
    let index = builder.blocks.value.length;
    if (builder.selectedModuleId.value) {
        const selIndex = builder.blocks.value.findIndex((b: BlockInstance) => b.id === builder.selectedModuleId.value);
        if (selIndex !== -1) {
            index = selIndex + 1;
        }
    }
    
    builder.blocks.value.splice(index, 0, block);
    builder.takeSnapshot();
    builder.selectModule(block.id);
    
    setTimeout(() => {
        const el = document.getElementById(`module-${block.id}`);
        if (el) el.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }, 100);
};
</script>

<style scoped>
.layouts-panel {
  display: flex;
  flex-direction: column;
  height: 100%;
  background: var(--builder-bg-sidebar);
  color: var(--builder-text-primary);
  overflow: hidden;
}

/* Header & Search */
.panel-header {
    padding: 16px 16px 8px;
}

.search-container {
    position: relative;
    display: flex;
    align-items: center;
}

.search-icon {
    position: absolute;
    left: 12px;
    color: var(--builder-text-muted);
}

.search-input {
    width: 100%;
    height: 36px;
    padding: 0 12px 0 34px;
    background: var(--builder-bg-secondary);
    border: 1px solid var(--builder-border);
    border-radius: 8px;
    color: var(--builder-text-primary);
    font-size: 13px;
    outline: none;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

.search-input:focus {
    background: var(--builder-bg-primary);
    border-color: var(--builder-accent);
    box-shadow: 0 0 0 3px rgba(var(--builder-accent-rgb), 0.1);
}

/* Categories Nav */
.categories-nav {
    padding: 8px 0 12px;
    border-bottom: 1px solid var(--builder-border);
    margin: 0 12px;
}

.categories-scroll {
    display: flex;
    gap: 6px;
    overflow-x: auto;
    padding-bottom: 6px; /* Reduced for more compact look */
    scrollbar-width: thin;
    scrollbar-color: var(--builder-border) transparent;
    -ms-overflow-style: auto;
}

.categories-scroll::-webkit-scrollbar {
    display: block;
    height: 3px;
}

.categories-scroll::-webkit-scrollbar-track {
    background: transparent;
}

.categories-scroll::-webkit-scrollbar-thumb {
    background: var(--builder-border);
    border-radius: 10px;
}

.categories-scroll::-webkit-scrollbar-thumb:hover {
    background: var(--builder-text-muted);
}

.category-pill {
    padding: 6px 14px;
    border-radius: 20px;
    background: var(--builder-bg-secondary);
    border: 1px solid var(--builder-border);
    color: var(--builder-text-muted);
    font-size: 11px;
    font-weight: 500;
    white-space: nowrap;
    cursor: pointer;
    transition: all 0.2s ease;
}

.category-pill:hover {
    color: var(--builder-text-primary);
    border-color: var(--builder-text-muted);
}

.categories-tabs {
    display: flex;
    gap: 8px;
    overflow-x: auto;
    padding-bottom: 8px;
    scrollbar-width: thin;
}

.categories-tabs::-webkit-scrollbar {
    height: 3px;
}

.categories-tabs::-webkit-scrollbar-thumb {
    background: var(--builder-border);
    border-radius: 4px;
}

.category-pill.active {
    background: var(--builder-accent);
    border-color: var(--builder-accent);
    color: white;
    box-shadow: 0 4px 10px rgba(var(--builder-accent-rgb), 0.25);
}

/* Content Area */
.layouts-content {
    flex: 1;
    overflow-y: auto;
    padding: 10px;
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    grid-auto-rows: min-content;
    align-content: start; /* Fix row stretching */
    gap: 6px;
    scrollbar-width: thin;
    scrollbar-color: var(--builder-border) transparent;
}

.layouts-content::-webkit-scrollbar {
    width: 4px;
}

.layouts-content::-webkit-scrollbar-track {
    background: transparent;
}

.layouts-content::-webkit-scrollbar-thumb {
    background: var(--builder-border);
    border-radius: 10px;
}

.layouts-content::-webkit-scrollbar-thumb:hover {
    background: var(--builder-text-muted);
}

.template-card {
    background: var(--builder-bg-secondary);
    border: 1px solid var(--builder-border);
    border-radius: 8px;
    overflow: hidden;
    cursor: grab;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    display: flex;
    flex-direction: column;
    aspect-ratio: 1 / 1; /* Square for better 3-col fit */
}

.template-card:hover {
    border-color: var(--builder-accent);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
}

.template-card:active {
    cursor: grabbing;
}

.template-visual {
    flex: 1; /* Dynamic height based on aspect ratio */
    background: var(--builder-bg-tertiary);
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}

/* Category Accents */
.visual-accent {
    position: absolute;
    top: 0;
    left: 0;
    width: 3px;
    height: 100%;
    background: var(--builder-accent);
    opacity: 0.6;
}

.accent-hero { background: #f59e0b; }
.accent-features { background: #3b82f6; }
.accent-content { background: #10b981; }
.accent-cta { background: #ef4444; }
.accent-team { background: #8b5cf6; }

.template-icon-circle {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--builder-bg-primary);
    border: 1px solid var(--builder-border);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    z-index: 1;
    transition: transform 0.2s ease;
}

.template-icon {
    width: 16px;
    height: 16px;
}

.bg-hero { border-color: #f59e0b44; color: #f59e0b; }
.bg-features { border-color: #3b82f644; color: #3b82f6; }
.bg-content { border-color: #10b98144; color: #10b981; }
.bg-cta { border-color: #ef444444; color: #ef4444; }
.bg-team { border-color: #8b5cf644; color: #8b5cf6; }

.template-card:hover .template-icon-circle {
    transform: scale(1.1);
}

/* Card Overlay Actions */
.card-overlay {
    position: absolute;
    inset: 0;
    background: rgba(var(--builder-bg-primary-rgb), 0.4);
    backdrop-filter: blur(2px);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.2s ease;
    z-index: 2;
}

.template-card:hover .card-overlay {
    opacity: 1;
}

.action-btn {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: var(--builder-accent);
    border: none;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(var(--builder-accent-rgb), 0.3);
    transition: transform 0.2s ease;
}

.action-btn:hover {
    transform: scale(1.1);
}

/* Details Section */
.template-details {
    padding: 4px 2px;
    background: var(--builder-bg-secondary);
    border-top: 1px solid var(--builder-border);
    text-align: center;
}

.template-title {
    font-size: 10px;
    font-weight: 600;
    margin: 0;
    color: var(--builder-text-primary);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.template-description {
    display: none; /* Hide in 3-col layout for better fit */
}

/* Empty State */
.empty-state {
    padding: 48px 24px;
    text-align: center;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.empty-icon-box {
    width: 64px;
    height: 64px;
    background: var(--builder-bg-secondary);
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--builder-text-muted);
    margin-bottom: 20px;
}

.empty-state h4 {
    margin: 0 0 8px;
    font-size: 16px;
    font-weight: 600;
}

.empty-state p {
    margin: 0;
    font-size: 13px;
    color: var(--builder-text-muted);
    line-height: 1.5;
}

/* Dragging state */
.sortable-ghost {
    opacity: 0.4;
    border: 2px dashed var(--builder-accent);
}
</style>
