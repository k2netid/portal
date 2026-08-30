<template>
  <section class="py-20 bg-background">
    <div class="container mx-auto px-6">
      <div class="text-center max-w-2xl mx-auto mb-12">
        <h2 class="text-2xl md:text-3xl font-bold text-foreground mb-3">
          {{ titleText }}
        </h2>
        <p class="text-muted-foreground">
          {{ subtitleText }}
        </p>
      </div>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <div
          v-for="mod in modules"
          :key="mod.key"
          class="p-6 rounded-xl border border-border bg-card/60"
        >
          <h3 class="font-bold text-foreground mb-2">
            {{ mod.title }}
          </h3>
          <p class="text-xs text-muted-foreground leading-relaxed mb-4">
            {{ mod.description }}
          </p>
          <ul class="text-xs text-muted-foreground space-y-1">
            <li v-for="(feature, idx) in mod.features" :key="idx">
              {{ feature }}
            </li>
          </ul>
        </div>
      </div>
      <p class="text-center text-xs text-muted-foreground mt-10 max-w-xl mx-auto">
        {{ workspaceNote }}
      </p>
    </div>
  </section>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useTheme } from '@/modules/Layout/composables/useTheme'
import { useThemeI18n } from '@/modules/Layout/composables/useThemeI18n'
import { useLocalizedThemeSetting } from '@/modules/Layout/composables/useLocalizedThemeSetting'

const { t } = useThemeI18n('janari')
const { getSetting } = useTheme()
const { localizedString } = useLocalizedThemeSetting()

const DEFAULTS = [
  { key: 'publishing', features: ['0', '1', '2'] },
  { key: 'layout', features: ['0', '1'] },
  { key: 'forms', features: ['0', '1'] },
  { key: 'intelligence', features: ['0', '1', '2'] },
  { key: 'platform', features: ['0', '1', '2'] },
  { key: 'member', features: ['0', '1'] },
] as const

const splitLines = (value: unknown): string[] =>
  String(value ?? '')
    .split('\n')
    .map((line) => line.trim())
    .filter(Boolean)

const titleText = computed(() => localizedString('page_solusi_stack_title') || t('pages.solusi.stackTitle'))
const subtitleText = computed(() => localizedString('page_solusi_stack_subtitle') || t('pages.solusi.stackSubtitle'))
const workspaceNote = computed(() => localizedString('page_solusi_workspace_note') || t('pages.solusi.workspaceNote'))

const modules = computed(() => {
  const raw = getSetting('page_solusi_stack_items')
  if (Array.isArray(raw) && raw.length > 0) {
    return raw.map((item) => {
      const row = (item && typeof item === 'object' ? item : {}) as Record<string, unknown>
      return {
        key: String(row.key || 'module'),
        title: String(row.title || ''),
        description: String(row.description || ''),
        features: splitLines(row.features),
      }
    })
  }

  return DEFAULTS.map((def) => ({
    key: def.key,
    title: t(`pages.solusi.modules.${def.key}.name`),
    description: t(`pages.solusi.modules.${def.key}.summary`),
    features: def.features.map((f) => t(`pages.solusi.modules.${def.key}.features.${f}`)),
  }))
})
</script>
