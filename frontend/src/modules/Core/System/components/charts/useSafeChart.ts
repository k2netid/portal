import { ref, onMounted, onBeforeUnmount, nextTick, type Ref } from 'vue';
import type { Chart as ChartInstance } from 'chart.js';

export type ChartComponentExposed = {
    chart: Ref<ChartInstance | null> | ChartInstance | null;
};

function resolveChart(exposed: ChartComponentExposed | null | undefined): ChartInstance | null {
    if (!exposed?.chart) return null;
    const chart = exposed.chart;
    if (typeof chart === 'object' && chart !== null && 'value' in chart) {
        return (chart as Ref<ChartInstance | null>).value;
    }
    return chart as ChartInstance;
}

/**
 * Ensures Chart.js is destroyed before the canvas is removed from the DOM.
 * Prevents "can't access property ownerDocument, e is null" when a deferred
 * resize (resizeDelay) fires after navigation or v-if teardown.
 */
export function useSafeChart(chartElRef: Ref<ChartComponentExposed | null>) {
    const isMounted = ref(false);

    function destroyChartInstance() {
        const instance = resolveChart(chartElRef.value);
        if (!instance) return;
        try {
            instance.stop();
            instance.destroy();
        } catch {
            // Chart may already be destroyed
        }
    }

    onMounted(async () => {
        await nextTick();
        isMounted.value = true;
    });

    onBeforeUnmount(() => {
        destroyChartInstance();
        isMounted.value = false;
    });

    return { isMounted, destroyChartInstance };
}
