<template>
  <details
    v-if="rows.length > 0"
    class="mt-4 rounded-lg border border-border/40 bg-muted/10 px-4 py-3"
  >
    <summary class="text-xs font-medium text-muted-foreground cursor-pointer select-none">
      {{ summary }}
    </summary>
    <div class="mt-3 overflow-x-auto">
      <table class="w-full text-xs text-left">
        <caption class="sr-only">{{ summary }}</caption>
        <thead>
          <tr class="text-muted-foreground border-b border-border/50">
            <th scope="col" class="py-2 pr-4 font-semibold">{{ labelHeader }}</th>
            <th scope="col" class="py-2 font-semibold text-right">{{ valueHeader }}</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="(row, index) in rows"
            :key="index"
            class="border-b border-border/30 last:border-0"
          >
            <td class="py-2 pr-4 text-foreground">{{ row.label }}</td>
            <td class="py-2 text-right tabular-nums text-foreground">{{ row.value }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </details>
</template>

<script setup lang="ts">
export interface ChartAccessibleRow {
    label: string;
    value: string | number;
}

withDefaults(
    defineProps<{
        rows?: ChartAccessibleRow[];
        summary: string;
        labelHeader: string;
        valueHeader: string;
    }>(),
    { rows: () => [] },
);
</script>
