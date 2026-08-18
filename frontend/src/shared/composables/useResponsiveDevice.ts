import { ref, computed, onMounted, onUnmounted } from 'vue'
import { throttle } from '@/shared/utils/performance'
export function useResponsiveDevice() {
    // We are in frontend mode, listen to window resize
    const device = ref('desktop')

    const checkDevice = () => {
        if (typeof window === 'undefined') return

        const w = window.innerWidth
        if (w < 768) device.value = 'mobile'
        else if (w < 1024) device.value = 'tablet'
        else device.value = 'desktop'
    }

    const throttledCheck = throttle(checkDevice, 150)

    onMounted(() => {
        checkDevice()
        window.addEventListener('resize', throttledCheck)
    })

    onUnmounted(() => {
        window.removeEventListener('resize', throttledCheck)
    })

    return computed(() => device.value)
}
