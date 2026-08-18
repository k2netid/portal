<template>
  <div class="markdown-editor">
    <!-- Toolbar -->
    <div class="flex items-center justify-between border-b border-border p-2 bg-muted">
      <div class="flex items-center space-x-2">
        <button
          v-for="tool in toolbar"
          :key="tool.action"
          :title="tool.title"
          class="p-2 text-muted-foreground hover:text-foreground hover:bg-muted rounded"
          @click="insertMarkdown(tool.action, tool.syntax)"
        >
          <component
            :is="tool.icon"
            v-if="tool.icon"
            class="w-5 h-5"
          />
          <span v-else>{{ tool.label }}</span>
        </button>
      </div>
      <div class="flex items-center space-x-2">
        <Button
          size="sm"
          :variant="showPreview ? 'default' : 'secondary'"
          @click="togglePreview"
        >
          {{ showPreview ? t('publishing.editor.markdown.edit') : t('publishing.editor.markdown.preview') }}
        </Button>
      </div>
    </div>

    <!-- Editor/Preview Split -->
    <div
      class="flex"
      :class="{ 'h-96': true }"
    >
      <!-- Markdown Editor -->
      <div
        v-if="!showPreview || splitView"
        class="flex-1 border-r border-border"
        :class="{ 'w-1/2': splitView, 'w-full': !splitView }"
      >
        <textarea
          ref="textarea"
          v-model="markdownContent"
          class="w-full h-full p-4 font-mono text-sm border-0 resize-none focus:outline-none"
          :placeholder="t('publishing.editor.markdown.placeholder')"
          spellcheck="false"
          @input="handleInput"
        />
      </div>

      <!-- Preview Pane -->
      <SafeHtml
        v-if="showPreview || splitView"
        class="flex-1 overflow-auto p-4 prose max-w-none"
        :class="{ 'w-1/2': splitView, 'w-full': !splitView }"
        :html="renderedPreview"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
import { logger } from '@/shared/utils/logger';
import { ref, computed, watch, nextTick, type Component } from 'vue';
import { useI18n } from 'vue-i18n';
import { marked } from 'marked';
import hljs from 'highlight.js';
import 'highlight.js/styles/github.min.css';
import SafeHtml from '@/modules/Core/System/components/ui/SafeHtml.vue';
import { Button } from '@/shared/components/ui';

import {
  Bold,
  Code,
  Heading2,
  Image,
  Italic,
  Link,
  List,
  Quote,
  SquareCode,
} from 'lucide-vue-next';

interface ToolbarItem {
    action: string;
    syntax: string;
    title: string;
    icon?: Component;
    label?: string;
}

// Configure marked
marked.use({
    renderer: {
        code({ text, lang }: { text: string; lang?: string }) {
            const validLang = lang && hljs.getLanguage(lang) ? lang : 'plaintext';
            const highlighted = hljs.highlight(text, { language: validLang }).value;
            return `<pre><code class="hljs lang-${validLang}">${highlighted}</code></pre>`;
        }
    },
    breaks: true,
    gfm: true,
});

const props = defineProps<{
    modelValue?: string;
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: string): void;
}>();

const { t } = useI18n();

const textarea = ref<HTMLTextAreaElement | null>(null);
const markdownContent = ref('');
const showPreview = ref(false);
const splitView = ref(false);

const toolbar = computed<ToolbarItem[]>(() => [
    { action: 'bold', syntax: '**text**', title: t('publishing.editor.markdown.toolbar.bold'), icon: Bold },
    { action: 'italic', syntax: '*text*', title: t('publishing.editor.markdown.toolbar.italic'), icon: Italic },
    { action: 'heading', syntax: '## Heading', title: t('publishing.editor.markdown.toolbar.heading'), icon: Heading2 },
    { action: 'link', syntax: '[text](url)', title: t('publishing.editor.markdown.toolbar.link'), icon: Link },
    { action: 'image', syntax: '![alt](url)', title: t('publishing.editor.markdown.toolbar.image'), icon: Image },
    { action: 'code', syntax: '`code`', title: t('publishing.editor.markdown.toolbar.inlineCode'), icon: Code },
    { action: 'codeblock', syntax: '```\ncode\n```', title: t('publishing.editor.markdown.toolbar.codeBlock'), icon: SquareCode },
    { action: 'list', syntax: '- item', title: t('publishing.editor.markdown.toolbar.list'), icon: List },
    { action: 'quote', syntax: '> quote', title: t('publishing.editor.markdown.toolbar.quote'), icon: Quote },
]);

const renderedPreview = computed(() => {
    if (!markdownContent.value) return '';
    try {
        return marked.parse(markdownContent.value) as string;
    } catch (e) {
        logger.error('Markdown parsing error:', e);
        return markdownContent.value;
    }
});

const handleInput = () => {
    emit('update:modelValue', markdownContent.value);
};

const insertMarkdown = (action: string, syntax: string) => {
    if (!textarea.value) return;

    const textareaEl = textarea.value;
    const start = textareaEl.selectionStart;
    const end = textareaEl.selectionEnd;
    const selectedText = markdownContent.value.substring(start, end);
    const before = markdownContent.value.substring(0, start);
    const after = markdownContent.value.substring(end);

    let replacement = '';

    switch (action) {
        case 'bold':
            replacement = selectedText ? `**${selectedText}**` : '**bold text**';
            break;
        case 'italic':
            replacement = selectedText ? `*${selectedText}*` : '*italic text*';
            break;
        case 'heading':
            replacement = selectedText ? `## ${selectedText}` : '## Heading';
            break;
        case 'link':
            replacement = selectedText ? `[${selectedText}](url)` : '[link text](url)';
            break;
        case 'image':
            replacement = '![alt text](image-url)';
            break;
        case 'code':
            replacement = selectedText ? `\`${selectedText}\`` : '`code`';
            break;
        case 'codeblock':
            replacement = selectedText ? `\`\`\`\n${selectedText}\n\`\`\`` : '```\ncode\n```';
            break;
        case 'list':
            replacement = selectedText ? `- ${selectedText.split('\n').join('\n- ')}` : '- List item';
            break;
        case 'quote':
            replacement = selectedText ? `> ${selectedText.split('\n').join('\n> ')}` : '> Quote';
            break;
        default:
            replacement = syntax;
    }

    markdownContent.value = before + replacement + after;
    
    nextTick(() => {
        textareaEl.focus();
        const newStart = start + replacement.length;
        textareaEl.setSelectionRange(newStart, newStart);
    });

    handleInput();
};

const togglePreview = () => {
    if (showPreview.value) {
        showPreview.value = false;
        splitView.value = false;
    } else {
        showPreview.value = true;
        splitView.value = true;
    }
};

// Convert HTML to Markdown on mount if needed
watch(() => props.modelValue, (newValue) => {
    if (newValue && newValue !== markdownContent.value) {
        // Check if it's HTML
        if (newValue.trim().startsWith('<')) {
            // Simple HTML to Markdown conversion (basic)
            markdownContent.value = htmlToMarkdown(newValue);
        } else {
            markdownContent.value = newValue;
        }
    }
}, { immediate: true });

// Simple HTML to Markdown converter
const htmlToMarkdown = (html: string) => {
    // This is a basic converter - you might want to use a library like turndown
    let md = html
        .replace(/<h1>(.*?)<\/h1>/gi, '# $1\n')
        .replace(/<h2>(.*?)<\/h2>/gi, '## $1\n')
        .replace(/<h3>(.*?)<\/h3>/gi, '### $1\n')
        .replace(/<strong>(.*?)<\/strong>/gi, '**$1**')
        .replace(/<b>(.*?)<\/b>/gi, '**$1**')
        .replace(/<em>(.*?)<\/em>/gi, '*$1*')
        .replace(/<i>(.*?)<\/i>/gi, '*$1*')
        .replace(/<a href="(.*?)">(.*?)<\/a>/gi, '[$2]($1)')
        .replace(/<img src="(.*?)" alt="(.*?)"\/?>/gi, '![$2]($1)')
        .replace(/<ul>(.*?)<\/ul>/gis, (_match, content) => {
            return content.replace(/<li>(.*?)<\/li>/gi, '- $1\n');
        })
        .replace(/<ol>(.*?)<\/ol>/gis, (_match, content) => {
            let counter = 1;
            return content.replace(/<li>(.*?)<\/li>/gi, () => `${counter++}. $1\n`);
        })
        .replace(/<p>(.*?)<\/p>/gi, '$1\n\n')
        .replace(/<br\s*\/?>/gi, '\n')
        .replace(/<[^>]+>/g, '')
        .trim();
    
    return md;
};
</script>

<style scoped>
.markdown-editor {
    border: 1px solid #e5e7eb;
    border-radius: 0.375rem;
    overflow: hidden;
}

textarea {
    font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
    line-height: 1.6;
    background-color: transparent;
    color: hsl(var(--foreground));
}

.prose {
    color: hsl(var(--foreground));
}

.prose :deep(h1), .prose :deep(h2), .prose :deep(h3), .prose :deep(h4) {
    color: hsl(var(--foreground));
}

.prose :deep(code) {
    background-color: hsl(var(--muted));
    color: hsl(var(--foreground));
    padding: 0.125rem 0.25rem;
    border-radius: 0.25rem;
    font-size: 0.875em;
}

.prose :deep(blockquote) {
    border-left: 4px solid hsl(var(--border));
    padding-left: 1rem;
    font-style: italic;
    color: hsl(var(--muted-foreground));
}
</style>

