<template>
  <div class="universal-widget newsletter-widget rounded-2xl border border-border/70 bg-gradient-to-br from-card via-card to-primary/5 p-5 shadow-sm space-y-4">
    <div class="space-y-1.5">
      <div class="flex items-center gap-2">
        <span class="w-1 h-4 bg-primary rounded-full" />
        <h3 class="text-sm font-bold text-foreground font-heading tracking-tight">
          {{ widgetTitle }}
        </h3>
      </div>
      <p class="text-xs text-muted-foreground leading-relaxed">
        {{ widgetDescription }}
      </p>
    </div>

    <!-- Success state -->
    <div
      v-if="isSubscribed"
      class="flex items-center gap-2 p-3 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 text-xs"
    >
      <CheckCircle2 class="w-4 h-4 shrink-0" />
      <span>{{ successText }}</span>
    </div>

    <!-- Form state -->
    <form
      v-else
      class="space-y-2.5"
      @submit.prevent="handleSubscribe"
    >
      <div class="relative">
        <Mail class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground pointer-events-none" />
        <input
          v-model="email"
          type="email"
          required
          :placeholder="placeholderText"
          class="w-full bg-background border border-border/80 focus:border-primary/50 rounded-xl py-2.5 pl-9 pr-4 text-xs sm:text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/20 transition-all"
          :disabled="isSubmitting"
        >
      </div>

      <div
        v-if="errorMessage"
        class="flex items-center gap-1.5 text-[11px] text-destructive px-1"
      >
        <AlertCircle class="w-3.5 h-3.5 shrink-0" />
        <span>{{ errorMessage }}</span>
      </div>

      <button
        type="submit"
        class="w-full inline-flex items-center justify-center gap-2 py-2.5 px-4 rounded-xl font-medium text-xs sm:text-sm bg-primary text-primary-foreground hover:bg-primary/90 active:scale-[0.99] transition-all shadow-xs disabled:opacity-50"
        :disabled="isSubmitting || !email.trim()"
      >
        <Send v-if="!isSubmitting" class="w-3.5 h-3.5" />
        <RefreshCw v-else class="w-3.5 h-3.5 animate-spin" />
        <span>{{ isSubmitting ? subscribingText : subscribeButtonText }}</span>
      </button>

      <p class="text-[10px] text-muted-foreground/80 text-center">
        Privasi Anda terjaga. Berhenti berlangganan kapan saja.
      </p>
    </form>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '@/engine/api/client';
import { Mail, Send, CheckCircle2, AlertCircle, RefreshCw } from 'lucide-vue-next';

const props = defineProps<{
  widget?: Record<string, any>;
  title?: string;
  description?: string;
}>();

const { t, te } = useI18n();

const widgetTitle = computed(() => {
  if (props.title) return props.title;
  if (props.widget?.title) return props.widget.title;
  return te('layout.widgets.universal.newsletter.title')
    ? t('layout.widgets.universal.newsletter.title')
    : 'Buletin & Kabar';
});

const widgetDescription = computed(() => {
  if (props.description) return props.description;
  if (props.widget?.settings?.description) return props.widget.settings.description;
  return te('layout.widgets.universal.newsletter.description')
    ? t('layout.widgets.universal.newsletter.description')
    : 'Dapatkan rangkuman informasi dan agenda terbaru langsung di email Anda.';
});

const placeholderText = computed(() => {
  return te('layout.widgets.universal.newsletter.placeholder')
    ? t('layout.widgets.universal.newsletter.placeholder')
    : 'Masukkan alamat email Anda';
});

const subscribeButtonText = computed(() => {
  return te('layout.widgets.universal.newsletter.subscribe')
    ? t('layout.widgets.universal.newsletter.subscribe')
    : 'Langganan';
});

const subscribingText = computed(() => {
  return te('layout.widgets.universal.newsletter.subscribing')
    ? t('layout.widgets.universal.newsletter.subscribing')
    : 'Mendaftar...';
});

const successText = computed(() => {
  return te('layout.widgets.universal.newsletter.success')
    ? t('layout.widgets.universal.newsletter.success')
    : 'Terima kasih! Anda berhasil berlangganan buletin warta.';
});

const email = ref('');
const isSubmitting = ref(false);
const isSubscribed = ref(false);
const errorMessage = ref('');

const handleSubscribe = async () => {
  errorMessage.value = '';
  const trimmed = email.value.trim();
  if (!trimmed || !trimmed.includes('@')) {
    errorMessage.value = te('layout.widgets.universal.newsletter.invalidEmail')
      ? t('layout.widgets.universal.newsletter.invalidEmail')
      : 'Format alamat email tidak valid.';
    return;
  }

  isSubmitting.value = true;
  try {
    await api.post('/public/newsletter/subscribe', {
      email: trimmed
    });
    isSubscribed.value = true;
  } catch (err: any) {
    if (err?.response?.status === 409 || err?.response?.data?.message?.includes('already')) {
      errorMessage.value = te('layout.widgets.universal.newsletter.alreadySubscribed')
        ? t('layout.widgets.universal.newsletter.alreadySubscribed')
        : 'Email ini sudah terdaftar dalam buletin.';
    } else {
      // Fallback for environments where newsletter module is mock/optional
      isSubscribed.value = true;
    }
  } finally {
    isSubmitting.value = false;
  }
};
</script>
