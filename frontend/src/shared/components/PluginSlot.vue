<template>
  <div
    v-if="activeBlocks.length > 0"
    class="plugin-slot-wrapper plugin-slot-isolate flex flex-col gap-4 w-full"
    :data-plugin-slot="name"
  >
    <div
      v-for="block in activeBlocks"
      :key="`${name}-${block.pluginSlug}`"
      class="ja-plugin-block w-full"
      :data-plugin="block.pluginSlug"
    >
      <component
        :is="block.component"
        v-bind="context"
        :plugin-slug="block.pluginSlug"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { getAppBlocksForSlot } from '@/engine/plugins/pluginRegistry';

const props = defineProps<{
  name: string;
  context?: Record<string, unknown>;
}>();

const activeBlocks = computed(() => getAppBlocksForSlot(props.name));
</script>


<style scoped>
.plugin-slot-isolate {
  isolation: isolate;
  max-width: 100%;
}
.plugin-slot-isolate :deep(.ja-plugin-block) {
  max-width: 100%;
  overflow-wrap: anywhere;
}
/* Third-party blocks: prefer BEM prefix ja-plugin-block__* in plugin CSS */
</style>
