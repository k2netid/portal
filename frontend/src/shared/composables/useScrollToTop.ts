import { ref, type Ref } from 'vue';

export function useScrollToTop(scrollContainerRef: Ref<HTMLElement | null>, threshold: number = 100) {
    const showBackToTop = ref(false);

    const handleScroll = () => {
        if (!scrollContainerRef.value) return;
        showBackToTop.value = scrollContainerRef.value.scrollTop > threshold;
    };

    const scrollToTop = () => {
        if (!scrollContainerRef.value) return;
        scrollContainerRef.value.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    };

    // Note: The component using this composable must bind @scroll="handleScroll" to the container
    // or add event listener manually if @scroll is not available on the element directly.

    return {
        showBackToTop,
        handleScroll,
        scrollToTop
    };
}
