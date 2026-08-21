<template>
  <Dialog :open="show" @update:open="(open) => { if (!open) handleClose() }">
    <DialogContent class="console-dialog-preview p-0 gap-0 max-w-7xl w-[min(96vw,80rem)]">
      <DialogHeader class="flex-row items-center justify-between gap-4 space-y-0 px-4 py-3 border-b border-border/40">
        <div class="flex flex-wrap items-center gap-3 min-w-0">
          <DialogTitle class="text-lg font-semibold">
            {{ t('system.contentPreview.title') }}
          </DialogTitle>
          <div
            class="flex items-center gap-1"
            role="group"
            :aria-label="t('system.contentPreview.title')"
          >
            <Button
              v-for="device in devices"
              :key="device.name"
              type="button"
              :variant="selectedDevice === device.name ? 'default' : 'outline'"
              size="icon-sm"
              :title="device.label"
              :aria-label="device.label"
              :aria-pressed="selectedDevice === device.name"
              @click="selectedDevice = device.name"
            >
              <component :is="device.icon" class="size-4 shrink-0" />
            </Button>
          </div>
        </div>
        <div class="flex items-center gap-2 shrink-0">
          <Button
            v-if="canPublish"
            size="sm"
            @click="handlePublish"
          >
            {{ t('system.contentPreview.publish') }}
          </Button>
        </div>
      </DialogHeader>
      <div
        class="flex-1 overflow-auto p-4 min-h-[50vh]"
        :class="deviceClass"
      >
        <SafeHtml
          class="preview-content"
          :html="renderedContent"
          mode="cms"
        />
      </div>
    </DialogContent>
  </Dialog>
</template>


<script setup lang="ts">
import { ref, computed, watch, type Component } from 'vue';
import { useI18n } from 'vue-i18n';
import SafeHtml from '@/modules/Core/System/components/ui/SafeHtml.vue';
import {
  Monitor,
  Smartphone,
  Tablet,
} from 'lucide-vue-next';
import {
  Button,
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
} from '@/shared/components/ui';

interface Author {
    name: string;
    [key: string]: unknown;
}

interface Category {
    name: string;
    [key: string]: unknown;
}

interface Content {
    title?: string;
    body?: string;
    excerpt?: string;
    featured_image?: string;
    author?: Author | string;
    category?: Category | string;
    published_at?: string;
    [key: string]: unknown;
}

interface Device {
    name: string;
    label: string;
    width: string;
    icon: Component;
}

const props = withDefaults(defineProps<{
    show?: boolean;
    content?: Content;
    canPublish?: boolean;
}>(), {
    show: false,
    content: () => ({}),
    canPublish: false,
});

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'publish'): void;
}>();

const { t } = useI18n();

const selectedDevice = ref<string>('desktop');

const devices = computed<Device[]>(() => [
    { name: 'desktop', label: t('system.contentPreview.devices.desktop'), width: '100%', icon: Monitor },
    { name: 'tablet', label: t('system.contentPreview.devices.tablet'), width: '768px', icon: Tablet },
    { name: 'mobile', label: t('system.contentPreview.devices.mobile'), width: '375px', icon: Smartphone },
]);

const deviceClass = computed(() => {
    return {
        'max-w-full': selectedDevice.value === 'desktop',
        'max-w-3xl mx-auto': selectedDevice.value === 'tablet',
        'max-w-sm mx-auto': selectedDevice.value === 'mobile',
    };
});

const renderedContent = computed(() => {
    const { title, body, excerpt, featured_image, author, category, published_at } = props.content;
    
    let html = '';
    
    if (featured_image) {
        html += `<img src="${featured_image}" alt="${title}" class="w-full h-64 object-cover rounded-lg mb-6" />`;
    }
    
    html += `<h1 class="text-4xl font-bold mb-4">${title || t('common.labels.untitled')}</h1>`;
    
    if (excerpt) {
        html += `<p class="text-xl text-muted-foreground mb-6">${excerpt}</p>`;
    }
    
    if (author || category || published_at) {
        html += '<div class="flex items-center space-x-4 text-sm text-muted-foreground mb-6">';
        
        if (author) {
            const authorName = typeof author === 'string' ? author : author.name;
            html += `<span>${t('system.contentPreview.byPrefix')} ${authorName}</span>`;
        }
        
        if (category) {
            const categoryName = typeof category === 'string' ? category : category.name;
            html += `<span>• ${categoryName}</span>`;
        }
        
        if (published_at) {
            html += `<span>• ${new Date(published_at).toLocaleDateString()}</span>`;
        }
        
        html += '</div>';
    }
    
    html += `<div class="prose max-w-none">${body || ''}</div>`;
    
    return html;
});

const handleClose = () => {
    emit('close');
};

const handlePublish = () => {
    emit('publish');
    handleClose();
};

// Enhance code blocks in preview
function enhanceCodeBlocks() {
    const previewEl = document.querySelector('.preview-content');
    if (!previewEl) return;
    
    const codeBlocks = previewEl.querySelectorAll('pre:not([data-enhanced])');
    codeBlocks.forEach((preElement) => {
        const pre = preElement as HTMLPreElement;
        pre.setAttribute('data-enhanced', 'true');
        
        const code = pre.querySelector('code');
        if (!code) return;
        
        const content = code.textContent || '';
        const lines = content.split('\n');
        const totalLines = lines.length;
        const isLong = totalLines > 5;
        
        // Create wrapper
        const wrapper = document.createElement('div');
        wrapper.className = 'code-block-preview-wrapper';
        wrapper.style.cssText = 'position: relative; margin: 1rem 0; border: 1px solid #313244; border-radius: 0.5rem; overflow: hidden;';
        
        // Create header
        const header = document.createElement('div');
        header.style.cssText = 'display: flex; justify-content: space-between; align-items: center; padding: 0.5rem 0.75rem; background: #2d2d3a; border-bottom: 1px solid #313244;';
        const title = document.createElement('span');
        title.style.cssText = 'font-size: 0.75rem; font-family: monospace; color: #a6adc8;';
        title.textContent = `Code (${totalLines} lines)`;
        const copyBtn = document.createElement('button');
        copyBtn.className = 'copy-code-btn';
        copyBtn.style.cssText = 'padding: 0.25rem 0.5rem; font-size: 0.7rem; background: #3d3d4a; border: 1px solid #45475a; color: #a6adc8; border-radius: 0.25rem; cursor: pointer;';
        copyBtn.textContent = 'Copy';
        header.appendChild(title);
        header.appendChild(copyBtn);
        
        // Copy button functionality
        copyBtn.addEventListener('click', () => {
            navigator.clipboard.writeText(content).then(() => {
                copyBtn.textContent = 'Copied!';
                copyBtn.style.background = '#22c55e';
                copyBtn.style.color = '#fff';
                setTimeout(() => {
                    copyBtn.textContent = 'Copy';
                    copyBtn.style.background = '#3d3d4a';
                    copyBtn.style.color = '#a6adc8';
                }, 2000);
            });
        });
        
        // Create code content with line numbers
        const codeContainer = document.createElement('div');
        codeContainer.style.cssText = 'display: flex; background: #1e1e2e; max-height: ' + (isLong ? '150px' : 'none') + '; overflow: hidden; transition: max-height 0.3s;';
        codeContainer.setAttribute('data-collapsed', isLong ? 'true' : 'false');
        
        // Line numbers
        const lineNums = document.createElement('div');
        lineNums.style.cssText = 'padding: 0.75rem 0; text-align: right; user-select: none; background: rgba(45, 45, 58, 0.5); border-right: 1px solid #313244; flex-shrink: 0;';
        lines.forEach((_, i) => {
            const lineNumber = document.createElement('div');
            lineNumber.style.cssText = 'padding: 0 0.5rem; font-size: 0.75rem; line-height: 1.5rem; color: #6c7086; font-family: monospace;';
            lineNumber.textContent = String(i + 1);
            lineNums.appendChild(lineNumber);
        });
        
        // Code content
        const codeContent = document.createElement('div');
        codeContent.style.cssText = 'padding: 0.75rem 1rem; overflow-x: auto; flex: 1;';
        lines.forEach((line) => {
            const lineElement = document.createElement('div');
            lineElement.style.cssText = 'font-size: 0.875rem; line-height: 1.5rem; font-family: ui-monospace, monospace; color: #cdd6f4; white-space: pre;';
            lineElement.textContent = line || ' ';
            codeContent.appendChild(lineElement);
        });
        
        codeContainer.appendChild(lineNums);
        codeContainer.appendChild(codeContent);
        
        // Expand/collapse button for long code
        let expandBtn: HTMLButtonElement | null = null;
        if (isLong) {
            expandBtn = document.createElement('button');
            expandBtn.style.cssText = 'width: 100%; padding: 0.5rem; background: #2d2d3a; border: none; border-top: 1px solid #313244; color: #a6adc8; font-size: 0.75rem; cursor: pointer;';
            expandBtn.textContent = `Show all ${totalLines} lines`;
            expandBtn.addEventListener('click', () => {
                const isCollapsed = codeContainer.getAttribute('data-collapsed') === 'true';
                codeContainer.style.maxHeight = isCollapsed ? 'none' : '150px';
                codeContainer.setAttribute('data-collapsed', isCollapsed ? 'false' : 'true');
                if (expandBtn) {
                    expandBtn.textContent = isCollapsed ? 'Collapse' : `Show all ${totalLines} lines`;
                }
            });
        }
        
        // Assemble
        wrapper.appendChild(header);
        wrapper.appendChild(codeContainer);
        if (expandBtn) wrapper.appendChild(expandBtn);
        
        if (pre.parentNode) {
            pre.parentNode.replaceChild(wrapper, pre);
        }
    });
}

watch(() => props.show, (newVal) => {
    if (newVal) {
        selectedDevice.value = 'desktop';
        // Delay to allow DOM to render
        setTimeout(enhanceCodeBlocks, 100);
    }
});

watch(() => props.content?.body, () => {
    if (props.show) {
        setTimeout(enhanceCodeBlocks, 100);
    }
});
</script>

<style scoped>
.preview-content {
    min-height: 100%;
}

/* Apply theme styles if available */
:deep(.preview-content) {
    color: var(--theme-text-color, #1f2937);
    background-color: var(--theme-background-color, #ffffff);
}

:deep(.preview-content h1) {
    color: var(--theme-primary-color, #2563eb);
}

:deep(.preview-content .prose) {
    color: var(--theme-text-color, #1f2937);
}

:deep(.prose p) {
    margin-bottom: 1rem;
}

:deep(.prose img) {
    max-width: 100%;
    height: auto;
    border-radius: 0.5rem;
}

/* Table Styles for Preview */
:deep(.prose table) {
    width: 100%;
    border-collapse: collapse;
    margin: 1.5rem 0;
    border-radius: 0.5rem;
    overflow: hidden;
    border: 2px solid #e5e7eb;
}

:deep(.prose th),
:deep(.prose td) {
    padding: 0.75rem 1rem;
    border: 1px solid #e5e7eb;
    text-align: left;
}

:deep(.prose th) {
    background-color: rgba(59, 130, 246, 0.1);
    font-weight: 600;
    border-bottom: 2px solid rgba(59, 130, 246, 0.3);
}

:deep(.prose tr:nth-child(even) td) {
    background-color: rgba(243, 244, 246, 0.5);
}

/* Code Block Styles for Preview */
:deep(.prose pre) {
    position: relative;
    background-color: #1e1e2e;
    border-radius: 0.5rem;
    margin: 1.5rem 0;
    overflow: hidden;
    border: 1px solid #313244;
    padding: 0;
}

:deep(.prose pre)::before {
    content: 'Code';
    display: block;
    padding: 0.5rem 1rem;
    padding-right: 4rem;
    background: #2d2d3a;
    border-bottom: 1px solid #313244;
    font-size: 0.75rem;
    font-family: ui-monospace, SFMono-Regular, monospace;
    color: #a6adc8;
}

:deep(.prose pre code) {
    display: block;
    padding: 1rem;
    overflow-x: auto;
    font-size: 0.875rem;
    font-family: ui-monospace, SFMono-Regular, 'SF Mono', Menlo, Consolas, monospace;
    line-height: 1.7;
    color: #cdd6f4;
    background: transparent !important;
    border: none !important;
}

/* Inline Code for Preview */
:deep(.prose code:not(pre code)) {
    background-color: #f3f4f6;
    border-radius: 0.25rem;
    padding: 0.125rem 0.375rem;
    font-family: ui-monospace, SFMono-Regular, monospace;
    font-size: 0.875em;
    color: #e11d48;
}

/* Syntax Highlighting for Preview */
:deep(.prose pre .hljs-comment),
:deep(.prose pre .hljs-quote) { color: #6c7086; font-style: italic; }
:deep(.prose pre .hljs-keyword),
:deep(.prose pre .hljs-selector-tag),
:deep(.prose pre .hljs-built_in) { color: #cba6f7; }
:deep(.prose pre .hljs-string),
:deep(.prose pre .hljs-attr) { color: #a6e3a1; }
:deep(.prose pre .hljs-title),
:deep(.prose pre .hljs-section) { color: #89b4fa; }
:deep(.prose pre .hljs-number),
:deep(.prose pre .hljs-literal) { color: #fab387; }
:deep(.prose pre .hljs-variable),
:deep(.prose pre .hljs-template-variable) { color: #f38ba8; }
:deep(.prose pre .hljs-type),
:deep(.prose pre .hljs-class) { color: #f9e2af; }
:deep(.prose pre .hljs-function) { color: #89b4fa; }
:deep(.prose pre .hljs-meta) { color: #f5c2e7; }

/* Highlighted Text for Preview */
:deep(.prose mark) {
    background-color: #fef08a;
    color: #1e1e2e;
    padding: 0.125rem 0.25rem;
    border-radius: 0.125rem;
}
</style>

