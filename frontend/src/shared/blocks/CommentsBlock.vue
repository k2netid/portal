<template>
  <PublicComments
    :content-id="contentId"
    :title="settings?.title || ''"
    :placeholder="settings?.commentPlaceholder || ''"
    :submit-text="settings?.submitText || ''"
    :is-comments-open="true"
  />
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import PublicComments from '@/modules/Content/Publishing/components/comments/PublicComments.vue';

const props = defineProps<{
  settings?: Record<string, any>;
  context?: Record<string, any>;
}>();

const route = useRoute();
const contentId = computed(() => {
  return String(props.context?.post_id || props.context?.content_id || props.context?.id || route.params.id || route.params.slug || '');
});
</script>
