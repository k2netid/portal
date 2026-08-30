<template>
  <nav
    v-if="crumbs.length > 0"
    class="janari-bc"
    aria-label="Breadcrumb"
  >
    <template
      v-for="(crumb, index) in crumbs"
      :key="`${crumb.path}-${index}`"
    >
      <span
        v-if="index > 0"
        class="janari-bc__sep"
        aria-hidden="true"
      >|</span>
      <router-link
        v-if="index < crumbs.length - 1"
        :to="crumb.path"
        class="janari-bc__link"
      >
        {{ crumb.label }}
      </router-link>
      <span
        v-else
        class="janari-bc__current"
        aria-current="page"
      >
        {{ crumb.label }}
      </span>
    </template>
  </nav>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import { useThemeI18n } from '@/modules/Layout/composables/useThemeI18n'

const route = useRoute()
const { t: tt } = useThemeI18n('janari')

/** Public path → theme locale key (artist-bar crumbs). */
const PUBLIC_CRUMB_KEYS: Record<string, string> = {
  '/': 'header.navHome',
  '/about': 'header.navAbout',
  '/solusi': 'header.navSolutions',
  '/services': 'header.navSolutions',
  '/tim': 'header.navTeam',
  '/pricing': 'header.navPricing',
  '/blog': 'header.navNews',
  '/contact': 'header.navContact',
  '/career': 'footer.defaultCareers',
  '/achievement': 'footer.defaultAchievements',
  '/search': 'header.navSearch',
}

type Crumb = { label: string; path: string }

const crumbs = computed((): Crumb[] => {
  const path = route.path === '/' ? '/' : route.path.replace(/\/$/, '') || '/'
  if (path === '/') {
    return [{ label: tt('header.navHome'), path: '/' }]
  }

  const leafKey = PUBLIC_CRUMB_KEYS[path]
  const leafLabel = leafKey
    ? tt(leafKey)
    : path.split('/').filter(Boolean).pop()?.replace(/-/g, ' ') || path

  return [
    { label: tt('header.navHome'), path: '/' },
    { label: leafLabel, path },
  ]
})
</script>

<style scoped>
/* Inline flex row — do not rely on theme @layer (was stacking as block list). */
.janari-bc {
  display: flex;
  flex-direction: row;
  flex-wrap: nowrap;
  align-items: center;
  gap: 0.375rem;
  margin: 0;
  padding: 0;
  max-width: 100%;
  overflow: hidden;
  white-space: nowrap;
  line-height: 1;
}

.janari-bc__sep {
  flex: 0 0 auto;
  font-size: 9px;
  font-weight: 300;
  opacity: 0.3;
  user-select: none;
}

.janari-bc__link,
.janari-bc__current {
  flex: 0 0 auto;
  font-size: 10px;
  font-weight: 700;
  letter-spacing: 0.18em;
  text-transform: uppercase;
  line-height: 1;
  text-decoration: none;
}

.janari-bc__link {
  color: hsl(var(--foreground) / 0.45);
  transition: color 0.2s ease;
}

.janari-bc__link:hover {
  color: hsl(var(--foreground));
}

.janari-bc__current {
  color: hsl(var(--foreground));
}
</style>
