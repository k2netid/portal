import { ref, computed, onMounted, onUnmounted, inject, unref, type Ref } from 'vue'
import { throttle } from '@/shared/utils/performance'
import type { BuilderInstance } from '@/types/builder'

export function useResponsiveDevice() {
    const builder = inject<BuilderInstance | null>('builder', null)
    const injectedDevice = inject<Ref<string> | string | null>('builderDevice', null)

    // We are in frontend mode, listen to window resize
    const windowDevice = ref('desktop')

    const checkDevice = () => {
        if (typeof window === 'undefined') return

        const w = window.innerWidth
        if (w < 768) windowDevice.value = 'mobile'
        else if (w < 1024) windowDevice.value = 'tablet'
        else windowDevice.value = 'desktop'
    }

    const throttledCheck = throttle(checkDevice, 150)

    onMounted(() => {
        checkDevice()
        window.addEventListener('resize', throttledCheck)
    })

    onUnmounted(() => {
        window.removeEventListener('resize', throttledCheck)
    })

    return computed(() => {
        if (builder?.device?.value) {
            return builder.device.value
        }
        if (injectedDevice) {
            return unref(injectedDevice)
        }
        return windowDevice.value
    })
}
