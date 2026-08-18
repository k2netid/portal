<template>
  <div class="captcha-math w-full">
    <!-- Unified Input Group -->
    <div 
      class="flex items-center w-full rounded-md border border-input bg-background ring-offset-background focus-within:ring-2 focus-within:ring-ring focus-within:ring-offset-2 overflow-hidden h-10 transition-colors duration-200"
      :class="{ 'border-destructive focus-within:ring-destructive': error, 'border-green-500 focus-within:ring-green-500': verified }"
    >
      <!-- Question Prefix (Balanced Width) -->
      <div class="bg-muted/50 px-3 py-2 border-r border-border text-sm font-mono font-medium text-muted-foreground select-none flex items-center justify-center h-full min-w-[40%] text-center">
        {{ question?.replace('=', '') }} =
      </div>

      <!-- Input Field -->
      <input
        v-model="answer"
        type="tel"
        class="flex h-full w-full px-3 py-2 text-sm placeholder:text-muted-foreground focus:outline-none disabled:cursor-not-allowed disabled:opacity-50 font-mono text-center bg-secondary/20 dark:bg-secondary/20 shadow-inner"
        :class="{ 'text-green-500 font-bold': verified }"
        :placeholder="t('system.auth.captcha.answer')"
        :disabled="verified"
        @keyup.enter="verify"
      >

      <!-- Actions (Right side) -->
      <div class="flex items-center gap-0.5 pr-1 h-full flex-none">
        <!-- Verified Indicator -->
        <div
          v-if="verified"
          class="px-2 text-green-500 flex items-center animate-in fade-in zoom-in duration-300"
        >
          <CheckCircle class="w-4 h-4" />
        </div>

        <!-- Verify Button -->
        <Button 
          v-if="!verified"
          type="button"
          variant="ghost" 
          size="icon"
          class="h-7 w-7 text-muted-foreground hover:text-primary hover:bg-transparent"
          :class="{ 'text-primary opacity-100': answer, 'opacity-0 w-0 px-0 overflow-hidden': !answer }"
          :disabled="!answer"
          :title="t('common.actions.verify')"
          @click="verify"
        >
          <Check class="w-4 h-4" />
        </Button>

        <!-- Divider -->
        <div
          v-if="!verified"
          class="w-px h-4 bg-border mx-1"
        />

        <!-- Refresh Button -->
        <Button 
          v-if="!verified"
          type="button"
          variant="ghost"
          size="icon"
          class="h-7 w-7 text-muted-foreground hover:text-foreground"
          :title="t('system.auth.captcha.refresh')"
          @click="refresh"
        >
          <RefreshCw class="w-3.5 h-3.5" />
        </Button>
      </div>
    </div>
        
    <!-- Error Message (Absolute or clearly positioned below) -->
    <p
      v-if="error"
      class="text-[0.8rem] font-medium text-destructive mt-1.5 ml-1 animate-in slide-in-from-top-1"
    >
      {{ error }}
    </p>
  </div>
</template>

<script setup lang="ts">
import { logger } from '@/shared/utils/logger';
import { ref, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import {
  Check,
  CheckCircle,
  RefreshCw,
} from 'lucide-vue-next';
import api from '@/engine/api/client'
import type { AxiosError } from 'axios'
import { Button } from '@/shared/components/ui'

const { t } = useI18n()

const emit = defineEmits<{
    (e: 'verified', payload: { token: string; answer: string }): void
    (e: 'error', message: string): void
}>()

const token = ref('')
const question = ref('...')
const answer = ref('')
const verified = ref(false)
const error = ref('')

const generateCaptcha = async () => {
    try {
        const response = await api.get('/captcha/generate')
        const data = response.data
        token.value = data.token
        question.value = data.question
        answer.value = ''
        verified.value = false
        error.value = ''
    } catch (e) {
        logger.error('Failed to generate captcha:', e)
        error.value = 'Failed to load captcha'
    }
}

const verify = async () => {
    if (!answer.value || verified.value) return
    
    try {
        await api.post('/captcha/verify', {
            token: token.value,
            answer: answer.value
        })
        
        verified.value = true
        error.value = ''
        emit('verified', { token: token.value, answer: answer.value })
    } catch (e: unknown) {
        verified.value = false
        const err = e as AxiosError<{ message?: string }>;
        // 422 is a validation error (wrong answer or expired token), not a system crash
        if (err.response && err.response.status === 422) {
            error.value = t('system.auth.captcha.error')
            // We just log a warning instead of error to avoid alarming developers
            logger.warning('Captcha verification failed:', err.response.data?.message || 'Invalid answer')
        } else {
            // Other errors (500, etc)
            error.value = 'Validation failed'
            logger.error('Captcha system error:', err)
        }
    }
}

const refresh = () => {
    generateCaptcha()
}

onMounted(() => {
    generateCaptcha()
})
</script>
