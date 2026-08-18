<template>
  <div
    class="newsletter-widget"
    :class="variant"
  >
    <div class="newsletter-content">
      <div
        v-if="showIcon"
        class="newsletter-icon"
      >
        <Mail class="w-8 h-8" />
      </div>
      
      <h3
        v-if="title || t('publishing.frontend.newsletter.title')"
        class="newsletter-title"
      >
        {{ title || t('publishing.frontend.newsletter.title') }}
      </h3>
      <p
        v-if="description || t('publishing.frontend.newsletter.description')"
        class="newsletter-description"
      >
        {{ description || t('publishing.frontend.newsletter.description') }}
      </p>
      
      <form
        class="newsletter-form"
        @submit.prevent="handleSubscribe"
      >
        <div class="form-group">
          <input
            v-model="email"
            type="email"
            :placeholder="t('publishing.frontend.newsletter.placeholder')"
            required
            :disabled="loading || success"
            class="newsletter-input"
            :class="{ 'error': error }"
          >
          <button
            type="submit"
            :disabled="loading || success"
            class="newsletter-button"
          >
            <span v-if="loading">
              <LoaderCircle class="w-5 h-5 animate-spin" />
            </span>
            <span v-else-if="success">✓</span>
            <span v-else>{{ buttonText || t('publishing.frontend.newsletter.button') }}</span>
          </button>
        </div>
        
        <div
          v-if="error"
          class="error-message"
        >
          {{ error }}
        </div>
        
        <div
          v-if="success"
          class="success-message"
        >
          {{ successMessage || t('publishing.frontend.newsletter.success') }}
        </div>
      </form>
      
      <p
        v-if="showPrivacy"
        class="newsletter-privacy"
      >
        {{ t('publishing.frontend.newsletter.privacy') }}
        <a
          href="/privacy"
          class="privacy-link"
        >{{ t('publishing.frontend.newsletter.privacyLink') }}</a>.
      </p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import {
  LoaderCircle,
  Mail,
} from 'lucide-vue-next';
import { NewsletterService } from '@/modules/Intelligence/Newsletter/services/newsletterService';

type NewsletterVariant = 'default' | 'compact' | 'inline';

const { t } = useI18n();

withDefaults(defineProps<{
  variant?: NewsletterVariant;
  title?: string;
  description?: string;
  buttonText?: string;
  showIcon?: boolean;
  showPrivacy?: boolean;
  successMessage?: string;
}>(), {
  variant: 'default' as const,
  title: '',
  description: '',
  buttonText: '',
  showIcon: true,
  showPrivacy: true,
  successMessage: '',
});

const email = ref('');
const loading = ref(false);
const success = ref(false);
const error = ref('');

const handleSubscribe = async () => {
  if (!email.value) return;
  
  loading.value = true;
  error.value = '';
  success.value = false;
  
  try {
    const response = await NewsletterService.subscribe({
      email: email.value,
    });
    const payload = response.data as { success?: boolean };
    
    if (payload.success !== false) {
      success.value = true;
      email.value = '';
      
      // Reset success message after 5 seconds
      setTimeout(() => {
        success.value = false;
      }, 5000);
    }
  } catch (err: unknown) {
    const errorData = err as { response?: { data?: { message?: string } } };
    error.value = errorData.response?.data?.message || t('common.messages.error.generic');
  } finally {
    loading.value = false;
  }
};
</script>

<style scoped>
.newsletter-widget {
  width: 100%;
}

.newsletter-content {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.newsletter-icon {
  display: flex;
  justify-content: center;
  color: var(--theme-primary-color, #2563eb);
}

.newsletter-title {
  font-size: 1.5rem;
  font-weight: 700;
  text-align: center;
  color: var(--theme-text-color, #1f2937);
  margin: 0;
}

.dark .newsletter-title {
  color: #f9fafb;
}

.newsletter-description {
  text-align: center;
  color: #6b7280;
  margin: 0;
}

.dark .newsletter-description {
  color: #9ca3af;
}

.newsletter-form {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.form-group {
  display: flex;
  gap: 0.5rem;
}

.newsletter-input {
  flex: 1;
  padding: 0.75rem 1rem;
  border: 2px solid #e5e7eb;
  border-radius: 0.5rem;
  font-size: 1rem;
  transition: all 0.2s;
  background-color: white;
  color: #1f2937;
}

.dark .newsletter-input {
  background-color: #1f2937;
  border-color: #374151;
  color: #f9fafb;
}

.newsletter-input:focus {
  outline: none;
  border-color: var(--theme-primary-color, #2563eb);
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

.newsletter-input:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.newsletter-input.error {
  border-color: #ef4444;
}

.newsletter-button {
  padding: 0.75rem 1.5rem;
  background-color: var(--theme-primary-color, #2563eb);
  color: white;
  border: none;
  border-radius: 0.5rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
  min-width: 120px;
}

.newsletter-button:hover:not(:disabled) {
  background-color: var(--theme-secondary-color, #1e40af);
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
}

.newsletter-button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.error-message {
  color: #ef4444;
  font-size: 0.875rem;
  text-align: center;
}

.success-message {
  color: #10b981;
  font-size: 0.875rem;
  text-align: center;
  font-weight: 600;
}

.newsletter-privacy {
  font-size: 0.75rem;
  color: #9ca3af;
  text-align: center;
  margin: 0;
}

.dark .newsletter-privacy {
  color: #6b7280;
}

.privacy-link {
  color: var(--theme-primary-color, #2563eb);
  text-decoration: underline;
}

.privacy-link:hover {
  text-decoration: none;
}

/* Variants */
.newsletter-widget.compact .newsletter-title {
  font-size: 1.25rem;
}

.newsletter-widget.compact .newsletter-description {
  font-size: 0.875rem;
}

.newsletter-widget.inline .form-group {
  flex-direction: row;
}

.newsletter-widget.inline .newsletter-content {
  flex-direction: row;
  align-items: center;
  gap: 1rem;
}

.newsletter-widget.inline .newsletter-title,
.newsletter-widget.inline .newsletter-description {
  text-align: left;
  margin: 0;
}

@media (max-width: 640px) {
  .form-group {
    flex-direction: column;
  }
  
  .newsletter-button {
    width: 100%;
  }
  
  .newsletter-widget.inline .newsletter-content {
    flex-direction: column;
    align-items: stretch;
  }
}
</style>

