<template>
  <section class="py-20 bg-background">
    <div class="container mx-auto px-6">
      <div class="text-center max-w-2xl mx-auto mb-12">
        <h2 class="text-2xl md:text-3xl font-bold text-foreground mb-3">
          {{ t('pages.solusi.stackTitle') }}
        </h2>
        <p class="text-muted-foreground">
          {{ t('pages.solusi.stackSubtitle') }}
        </p>
      </div>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <div
          v-for="mod in modules"
          :key="mod.key"
          class="p-6 rounded-xl border border-border bg-card/60"
        >
          <h3 class="font-bold text-foreground mb-2">
            {{ t(`pages.solusi.modules.${mod.key}.name`) }}
          </h3>
          <p class="text-xs text-muted-foreground leading-relaxed mb-4">
            {{ t(`pages.solusi.modules.${mod.key}.summary`) }}
          </p>
          <ul class="text-xs text-muted-foreground space-y-1">
            <li v-for="f in mod.features" :key="f">
              {{ t(`pages.solusi.modules.${mod.key}.features.${f}`) }}
            </li>
          </ul>
        </div>
      </div>
      <p class="text-center text-xs text-muted-foreground mt-10 max-w-xl mx-auto">
        {{ t('pages.solusi.workspaceNote') }}
      </p>
    </div>
  </section>
</template>

<script setup lang="ts">
import { useThemeI18n } from '@/modules/Layout/composables/useThemeI18n'

const { t } = useThemeI18n('janari')

const modules = [
  { key: 'publishing', features: ['0', '1', '2'] },
  { key: 'layout', features: ['0', '1'] },
  { key: 'forms', features: ['0', '1'] },
  { key: 'intelligence', features: ['0', '1', '2'] },
  { key: 'platform', features: ['0', '1', '2'] },
  { key: 'member', features: ['0', '1'] },
] as const
</script>
