<template>
  <div
    v-if="loading"
    class="flex items-center justify-center p-4"
  >
    <Loader2 class="w-5 h-5 animate-spin text-muted-foreground" />
  </div>
    
  <div
    v-else-if="enabled"
    class="captcha-wrapper"
  >
    <SliderCaptcha
      v-if="method === 'slider'"
      @verified="onVerified"
    />
    <MathCaptcha
      v-else-if="method === 'math'"
      @verified="onVerified"
    />
    <ImageCaptcha
      v-else-if="method === 'image'"
      @verified="onVerified"
    />
  </div>
</template>

<script setup lang="ts">
import { logger } from '@/shared/utils/logger';
import { ref, onMounted } from 'vue'
import {
  Loader2,
} from 'lucide-vue-next';
import api from '@/engine/api/client'
import SliderCaptcha from './SliderCaptcha.vue'
import MathCaptcha from './MathCaptcha.vue'
import ImageCaptcha from './ImageCaptcha.vue'

interface Props {
    action?: 'login' | 'register' | 'comment' | 'contact' | 'forgot-password'
}

export interface CaptchaPayload {
    token: string;
    answer: string;
}

const props = withDefaults(defineProps<Props>(), {
    action: 'login',
})

export interface CaptchaSettingsState {
    enabled: boolean;
    method: string;
}

const emit = defineEmits<{
    (e: 'verified', payload: CaptchaPayload): void
    (e: 'settings', state: CaptchaSettingsState): void
}>()

const loading = ref(true)
const enabled = ref(false)
const method = ref('slider')

const fetchSettings = async () => {
    try {
        const response = await api.get('/captcha/settings')
        const responseData = response.data
        const data = (responseData as { data?: Record<string, unknown> } | Record<string, unknown>);
        const unwrapped = (data && typeof data === 'object' && 'data' in data ? data.data : data) as Record<string, unknown> | undefined;
        
        if (props.action === 'login') {
            enabled.value = Boolean(unwrapped?.enabled_login)
        } else if (props.action === 'register') {
            enabled.value = Boolean(unwrapped?.enabled_register)
        } else if (props.action === 'comment') {
            enabled.value = Boolean(unwrapped?.enabled_comment || unwrapped?.enabled_guest_comment)
        } else if (props.action === 'contact') {
            enabled.value = Boolean(unwrapped?.enabled_contact)
        } else if (props.action === 'forgot-password') {
            enabled.value = Boolean(unwrapped?.enabled_forgot_password)
        } else {
            enabled.value = false
        }
        
        method.value = String(unwrapped?.method ?? '')
        
        // Fallback to slider if method is not recognized
        if (!['slider', 'math', 'image'].includes(method.value)) {
            logger.warning(`Unknown captcha method "${method.value}", falling back to slider`);
            method.value = 'slider'
        }

        emit('settings', { enabled: enabled.value, method: method.value })
    } catch (e) {
        logger.error('Failed to fetch captcha settings:', e)
        enabled.value = false
        emit('settings', { enabled: false, method: method.value })
    } finally {
        loading.value = false
    }
}

const onVerified = (payload: CaptchaPayload) => {
    emit('verified', payload)
}

const refresh = async () => {
    loading.value = true
    await fetchSettings()
}

onMounted(() => {
    fetchSettings()
})

// Expose state for parent components
defineExpose({
    enabled,
    method,
    refresh,
})
</script>
