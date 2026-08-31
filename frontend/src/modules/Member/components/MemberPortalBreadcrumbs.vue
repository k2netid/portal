<template>
  <nav
    class="flex items-center gap-1.5 min-w-0 text-sm"
    :aria-label="t('member.portal.breadcrumb.aria', 'Member area navigation')"
  >
    <template
      v-for="(crumb, index) in crumbs"
      :key="`${crumb.label}-${index}`"
    >
      <ChevronRight
        v-if="index > 0"
        class="w-3.5 h-3.5 shrink-0 text-muted-foreground/60"
        aria-hidden="true"
      />
      <router-link
        v-if="crumb.to && index < crumbs.length - 1"
        :to="crumb.to"
        class="truncate font-medium text-muted-foreground hover:text-foreground transition-colors"
      >
        {{ crumb.label }}
      </router-link>
      <span
        v-else
        class="truncate font-semibold text-foreground"
        :aria-current="index === crumbs.length - 1 ? 'page' : undefined"
      >
        {{ crumb.label }}
      </span>
    </template>
  </nav>
</template>

<script setup lang="ts">
import { ChevronRight } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';
import { useMemberPortalBreadcrumbs } from '@/modules/Member/composables/useMemberPortalBreadcrumbs';

const { t } = useI18n();
const { crumbs } = useMemberPortalBreadcrumbs();
</script>
