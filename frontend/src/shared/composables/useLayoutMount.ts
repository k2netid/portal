import { ref, onMounted } from 'vue';

// Shared mounted state across all layout components
const isLayoutMounted = ref(false);

/**
 * Composable to sync mounted state across layout components
 * This ensures all transitions enable at exactly the same time
 */
export function useLayoutMount() {
    // Init on first mount of any component
    onMounted(() => {
        if (!isLayoutMounted.value) {
            // Use double RAF for better timing
            requestAnimationFrame(() => {
                requestAnimationFrame(() => {
                    isLayoutMounted.value = true;
                });
            });
        }
    });

    return {
        mounted: isLayoutMounted,
    };
}
