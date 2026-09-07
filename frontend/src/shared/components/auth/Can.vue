<script setup lang="ts">
import { computed } from 'vue';
import { useAuthStore } from '@/modules/Core/System/stores/auth';

interface Props {
  permission?: string | string[];
  role?: string | string[];
  any?: boolean;
  minRank?: number;
}

const props = withDefaults(defineProps<Props>(), {
  permission: undefined,
  role: undefined,
  any: false,
  minRank: undefined,
});

const authStore = useAuthStore();

const isAllowed = computed<boolean>(() => {
  if (!authStore.isAuthenticated || !authStore.user) {
    return false;
  }

  // Super admin bypass
  if (authStore.getRoleRank() >= 100) {
    return true;
  }

  // Check minRank
  if (props.minRank !== undefined && authStore.getRoleRank() < props.minRank) {
    return false;
  }

  // Check role
  if (props.role !== undefined) {
    const roles = Array.isArray(props.role) ? props.role : [props.role];
    if (roles.length > 0) {
      const hasRoles = props.any
        ? roles.some((r) => authStore.hasRole(r))
        : roles.every((r) => authStore.hasRole(r));
      if (!hasRoles) {
        return false;
      }
    }
  }

  // Check permission
  if (props.permission !== undefined) {
    const permissions = Array.isArray(props.permission) ? props.permission : [props.permission];
    if (permissions.length > 0) {
      const hasPerms = props.any
        ? permissions.some((p) => authStore.hasPermission(p))
        : permissions.every((p) => authStore.hasPermission(p));
      if (!hasPerms) {
        return false;
      }
    }
  }

  return true;
});
</script>

<template>
  <template v-if="isAllowed">
    <slot />
  </template>
  <template v-else>
    <slot name="fallback" />
  </template>
</template>
