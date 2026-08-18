<template>
  <node-view-wrapper class="code-block-wrapper">
    <div class="code-block-container">
      <!-- Header with language and copy button -->
      <div class="code-block-header">
        <span class="code-block-lang">{{ language || 'code' }}</span>
        <button 
          class="code-block-copy" 
          :title="copied ? 'Copied!' : 'Copy code'"
          type="button"
          @click="copyCode"
        >
          <Check
            v-if="copied"
            class="w-4 h-4"
          />
          <Copy
            v-else
            class="w-4 h-4"
          />
        </button>
      </div>
      <!-- Code content with line numbers -->
      <div class="code-block-content">
        <div
          class="code-block-lines"
          aria-hidden="true"
        >
          <span
            v-for="n in lineCount"
            :key="n"
            class="code-block-line-number"
          >{{ n }}</span>
        </div>
        <node-view-content
          as="pre"
          class="code-block-code"
        />
      </div>
    </div>
  </node-view-wrapper>
</template>

<script setup lang="ts">
import { logger } from '@/shared/utils/logger';
import { ref, computed } from 'vue'
import { NodeViewWrapper, NodeViewContent, nodeViewProps } from '@tiptap/vue-3'
import {
  Check,
  Copy,
} from 'lucide-vue-next';

const props = defineProps(nodeViewProps)

const copied = ref(false)

const language = computed(() => {
    return props.node.attrs.language || ''
})

const lineCount = computed(() => {
    const content = props.node.textContent || ''
    if (!content) return 1
    const lines = content.split('\n')
    return Math.max(lines.length, 1)
})

function copyCode() {
    const content = props.node.textContent || ''
    navigator.clipboard.writeText(content).then(() => {
        copied.value = true
        setTimeout(() => {
            copied.value = false
        }, 2000)
    }).catch(err => {
        logger.error('Failed to copy:', err)
    })
}
</script>

<style>
.code-block-wrapper {
    margin: 1rem 0;
}

.code-block-container {
    border-radius: 0.5rem;
    overflow: hidden;
    border: 1px solid hsl(var(--border));
    /* Light mode: light gray, Dark mode: dark */
    background-color: hsl(var(--muted));
}

/* Dark mode override */
:root.dark .code-block-container,
.dark .code-block-container {
    background-color: hsl(220 15% 12%);
}

.code-block-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.5rem 0.75rem;
    background: hsl(var(--muted));
    border-bottom: 1px solid hsl(var(--border));
}

:root.dark .code-block-header,
.dark .code-block-header {
    background: hsl(220 15% 18%);
    border-bottom-color: hsl(220 15% 25%);
}

.code-block-lang {
    font-size: 0.75rem;
    font-family: ui-monospace, SFMono-Regular, 'SF Mono', Menlo, Consolas, monospace;
    color: hsl(var(--muted-foreground));
    text-transform: lowercase;
}

.code-block-copy {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0.25rem;
    border-radius: 0.25rem;
    background: transparent;
    border: none;
    color: hsl(var(--muted-foreground));
    cursor: pointer;
    transition: all 0.2s;
}

.code-block-copy:hover {
    background: hsl(220 15% 25%);
    color: hsl(var(--foreground));
}

.code-block-content {
    display: flex;
    overflow-x: auto;
}

.code-block-lines {
    flex-shrink: 0;
    padding: 0.875rem 0;
    text-align: right;
    user-select: none;
    background: hsl(var(--muted) / 0.5);
    border-right: 1px solid hsl(var(--border));
}

:root.dark .code-block-lines,
.dark .code-block-lines {
    background: hsl(220 15% 15%);
    border-right-color: hsl(220 15% 25%);
}

.code-block-line-number {
    display: block;
    padding: 0 0.75rem;
    font-size: 0.875rem;
    font-family: ui-monospace, SFMono-Regular, 'SF Mono', Menlo, Consolas, monospace;
    line-height: 1.5rem;
    color: hsl(var(--muted-foreground));
}

.code-block-code {
    flex: 1;
    margin: 0;
    padding: 0.875rem 1rem;
    overflow-x: auto;
    background: transparent !important;
    border: none !important;
    border-radius: 0 !important;
}

.code-block-code code {
    display: block;
    font-size: 0.875rem;
    font-family: ui-monospace, SFMono-Regular, 'SF Mono', Menlo, Consolas, monospace;
    line-height: 1.5rem;
    color: hsl(var(--foreground));
    background: transparent !important;
    padding: 0 !important;
    border: none !important;
}

:root.dark .code-block-code code,
.dark .code-block-code code {
    color: hsl(210 14% 83%);
}

/* Syntax highlighting colors */
.code-block-code .hljs-comment,
.code-block-code .hljs-quote { color: #6a9955; }
.code-block-code .hljs-keyword,
.code-block-code .hljs-selector-tag,
.code-block-code .hljs-built_in { color: #569cd6; }
.code-block-code .hljs-string,
.code-block-code .hljs-title,
.code-block-code .hljs-attr { color: #ce9178; }
.code-block-code .hljs-number,
.code-block-code .hljs-literal { color: #b5cea8; }
.code-block-code .hljs-variable,
.code-block-code .hljs-template-variable { color: #9cdcfe; }
.code-block-code .hljs-type,
.code-block-code .hljs-class .hljs-title { color: #4ec9b0; }
.code-block-code .hljs-function { color: #dcdcaa; }
.code-block-code .hljs-meta { color: #c586c0; }
</style>
