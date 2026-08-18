<template>
  <div class="chart-container w-full h-full">
    <Bar
      v-if="isMounted && hasData"
      ref="chartElRef"
      :aria-label="accessibilityLabel"
      :data="chartData"
      :options="chartOptions"
      :destroy-delay="0"
    />
  </div>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue';
import { Bar } from 'vue-chartjs';
import { useSafeChart, type ChartComponentExposed } from './useSafeChart';
import type {
    ChartOptions,
    ChartData
} from 'chart.js';
import {
    Chart as ChartJS,
    Title,
    Tooltip,
    Legend,
    BarElement,
    CategoryScale,
    LinearScale
} from 'chart.js';

ChartJS.register(CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend);

const chartElRef = ref<ChartComponentExposed | null>(null);
const { isMounted } = useSafeChart(chartElRef);

interface ChartItem {
    [key: string]: unknown;
}

const props = defineProps<{
    data: ChartItem[];
    labelKey: string;
    valueKey?: string;
    horizontal?: boolean;
    accessibilityLabel?: string;
}>();

const valueKey = computed(() => props.valueKey || 'count');
const isHorizontal = computed(() => props.horizontal !== false);

const hasData = computed(() => props.data && props.data.length > 0);



const chartData = computed<ChartData<'bar'>>(() => {
    return {
        labels: props.data.map(item => item[props.labelKey] || 'Unknown').slice(0, 10),
        datasets: [
            {
                label: 'Visits',
                backgroundColor: '#3B82F6', // blue-500
                data: props.data.map(item => Number(item[valueKey.value])).slice(0, 10),
            },
        ],
    };
});

const chartOptions = computed<ChartOptions<'bar'>>(() => ({
    indexAxis: isHorizontal.value ? 'y' : 'x',
    responsive: true,
    maintainAspectRatio: false,
    // Explicitly define events
    events: ['mousemove', 'mouseout', 'click', 'touchstart', 'touchmove'],
    plugins: {
        legend: {
            display: false,
        },
        tooltip: {
            enabled: true,
            mode: isHorizontal.value ? 'y' : 'x',
            intersect: false,
        }
    },
    interaction: {
        mode: isHorizontal.value ? 'y' : 'x',
        intersect: false,
    },
    scales: {
        x: {
            beginAtZero: true,
        },
    },
}));
</script>

<style scoped>
.chart-container :deep(canvas) {
    animation: revert !important;
    transition: revert !important;
}
</style>

