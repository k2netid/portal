<template>
  <ol
    v-if="breadcrumbs.length > 0"
    class="janari-breadcrumb"
    aria-label="Breadcrumb"
  >
    <li
      v-for="(crumb, index) in breadcrumbs"
      :key="'janari-bc-' + index"
      class="janari-breadcrumb-item"
    >
      <span
        v-if="index > 0"
        class="janari-breadcrumb-separator"
      >|</span>
      <router-link
        v-if="index < breadcrumbs.length - 1"
        :to="crumb.path"
        class="janari-breadcrumb-link"
      >
        {{ crumb.label }}
      </router-link>
      <span
        v-else
        class="janari-breadcrumb-current"
        aria-current="page"
      >
        {{ crumb.label }}
      </span>
    </li>
  </ol>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import { useBreadcrumbs } from '@/shared/composables/useBreadcrumbs'
import { logger } from '@/shared/utils/logger'

const route = useRoute()
const { getBreadcrumbs } = useBreadcrumbs()

const breadcrumbs = computed(() => {
  try {
    return getBreadcrumbs(route)
  } catch (error) {
    logger.error('[JanariBreadcrumbs] Failed to generate breadcrumbs:', error)
    return []
  }
})
</script>
