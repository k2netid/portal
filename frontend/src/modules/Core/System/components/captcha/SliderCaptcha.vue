<template>
  <div class="captcha-slider">
    <p class="text-sm text-muted-foreground mb-2">
      {{ t('system.auth.captcha.slideToVerify') }}
    </p>
        
    <div 
      ref="trackRef"
      class="relative h-12 bg-muted rounded-lg border border-border overflow-hidden select-none"
      @mousedown="startDrag"
      @touchstart.passive="startDrag"
    >
      <!-- Progress fill -->
      <div 
        class="absolute inset-y-0 left-0 bg-primary/20 transition-colors duration-75"
        :style="{ width: `${progress}%` }"
      />
            
      <!-- Target indicator (Box slot) -->
      <div 
        class="absolute top-1 bottom-1 w-12 bg-primary/5 border-2 border-primary/20 border-dashed rounded-md box-border"
        :style="{ left: `${targetPosition}%` }"
      />
            
      <!-- Slider handle -->
      <div 
        class="absolute top-1 bottom-1 w-12 bg-background/80 backdrop-blur-sm border-2 rounded-md flex items-center justify-center cursor-grab active:cursor-grabbing transition-colors z-10"
        :class="[
          verified ? 'border-green-500 bg-green-50/80 dark:bg-green-950/80' : 'border-primary hover:bg-muted/80',
          dragging ? 'shadow-lg' : ''
        ]"
        :style="{ left: `${progress}%` }"
      >
        <CheckCircle
          v-if="verified"
          class="w-5 h-5 text-green-500"
        />
        <GripVertical
          v-else
          class="w-5 h-5 text-muted-foreground"
        />
      </div>
            
      <!-- Instructions text -->
      <span 
        v-if="!dragging && !verified" 
        class="absolute inset-0 flex items-center justify-center text-sm text-muted-foreground pointer-events-none"
      >
        {{ t('system.auth.captcha.dragSlider') }}
      </span>
    </div>
        
    <p
      v-if="error"
      class="text-sm text-destructive mt-1"
    >
      {{ error }}
    </p>
  </div>
</template>

<script setup lang="ts">
import { logger } from '@/shared/utils/logger';
import { ref, onMounted, onUnmounted } from 'vue'
import { useI18n } from 'vue-i18n'
import {
  CheckCircle,
  GripVertical,
} from 'lucide-vue-next';
import api from '@/engine/api/client'
import type { AxiosError } from 'axios'

const { t } = useI18n()

const emit = defineEmits<{
    (e: 'verified', payload: { token: string; answer: string }): void
    (e: 'error', message: string): void
}>()

const trackRef = ref<HTMLElement | null>(null)
const token = ref('')
const targetPosition = ref(75)
const progress = ref(0)
const dragging = ref(false)
const verified = ref(false)
const error = ref('')
const startX = ref(0)
const trackWidth = ref(0)
const generatedAt = ref(0)

const sleep = (ms: number) => new Promise((resolve) => setTimeout(resolve, ms))

const generateCaptcha = async () => {
    try {
        const response = await api.get('/captcha/generate')
        const data = response.data
        token.value = data.token
        targetPosition.value = data.target
        progress.value = 0
        verified.value = false
        error.value = ''
        generatedAt.value = Date.now()
    } catch (e) {
        logger.error('Failed to generate captcha:', e)
        error.value = 'Failed to load captcha'
    }
}

const startDrag = (e: MouseEvent | TouchEvent) => {
    if (verified.value) return
    
    dragging.value = true
    trackWidth.value = trackRef.value?.offsetWidth || 300
    const clientX = e instanceof MouseEvent ? e.clientX : (e.touches[0]?.clientX || 0)
    startX.value = clientX - (progress.value / 100) * trackWidth.value
    
    document.addEventListener('mousemove', onDrag)
    document.addEventListener('mouseup', endDrag)
    document.addEventListener('touchmove', onDrag, { passive: true })
    document.addEventListener('touchend', endDrag)
}

const onDrag = (e: MouseEvent | TouchEvent) => {
    if (!dragging.value) return
    
    const clientX = e instanceof MouseEvent ? e.clientX : (e as TouchEvent).touches[0]?.clientX || 0
    // Handle is w-12 (3rem = 48px). We must ensure left + 48px <= trackWidth
    const handleWidth = 48 
    const maxLeft = trackWidth.value - handleWidth
    
    // Calculate raw delta
    let deltaX = clientX - startX.value
    
    // Clamp deltaX between 0 and maxLeft
    deltaX = Math.max(0, Math.min(maxLeft, deltaX))
    
    // Convert to percentage for width/position
    const newProgress = (deltaX / trackWidth.value) * 100
    progress.value = newProgress
}

const endDrag = async (_e?: MouseEvent | TouchEvent) => {
    if (!dragging.value) return
    
    dragging.value = false
    document.removeEventListener('mousemove', onDrag as (evt: MouseEvent) => void)
    document.removeEventListener('mouseup', endDrag as (evt: MouseEvent) => void)
    document.removeEventListener('touchmove', onDrag as (evt: TouchEvent) => void)
    document.removeEventListener('touchend', endDrag as (evt: TouchEvent) => void)
    
    // Server-side verification
    try {
        // Backend enforces minimum solve timing (~800ms). Wait briefly if user solved too fast.
        const elapsed = Date.now() - generatedAt.value
        if (elapsed < 850) {
            await sleep(850 - elapsed)
        }

        await api.post('/captcha/verify', {
            token: token.value,
            answer: String(Math.round(progress.value))
        })
        
        verified.value = true
        emit('verified', { token: token.value, answer: String(Math.round(progress.value)) })
    } catch (e: unknown) {
        // Just like MathCaptcha, we catch 422 as expected verification failure
        error.value = t('system.auth.captcha.tryAgain')
        progress.value = 0
        
        const err = e as AxiosError;
        if (err.response?.status !== 422) {
             logger.error('Slider verification failed:', err)
        }
        
        // Generate new captcha after failed attempt
        setTimeout(generateCaptcha, 1000)
    }
}

onMounted(() => {
    generateCaptcha()
})

onUnmounted(() => {
    document.removeEventListener('mousemove', onDrag as (evt: MouseEvent) => void)
    document.removeEventListener('mouseup', endDrag as (evt: MouseEvent) => void)
    document.removeEventListener('touchmove', onDrag as (evt: TouchEvent) => void)
    document.removeEventListener('touchend', endDrag as (evt: TouchEvent) => void)
})
</script>
