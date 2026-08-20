<template>
  <div class="zenith-theme flex-1 flex flex-col">
    <Header />

    <main class="flex-1 py-16 sm:py-24">
      <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
        <div class="text-center space-y-4">
          <h1 class="text-4xl sm:text-5xl font-extrabold text-foreground font-heading">
            {{ t('theme.zenith.pages.contact.title', 'Get in Touch') }}
          </h1>
          <p class="text-lg text-muted-foreground">
            {{ t('theme.zenith.pages.contact.subtitle', "We'd love to hear from you. Reach out with any inquiries.") }}
          </p>
        </div>

        <Card class="space-y-6">
          <form
            class="space-y-5"
            @submit.prevent="handleSubmit"
          >
            <div class="space-y-2">
              <label class="text-sm font-semibold text-foreground">
                {{ t('theme.zenith.pages.contact.nameLabel', 'Your Name') }}
              </label>
              <input
                v-model="form.name"
                type="text"
                required
                class="w-full px-4 py-2.5 rounded-xl border border-border/80 bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/50"
                placeholder="John Doe"
              >
            </div>

            <div class="space-y-2">
              <label class="text-sm font-semibold text-foreground">
                {{ t('theme.zenith.pages.contact.emailLabel', 'Email Address') }}
              </label>
              <input
                v-model="form.email"
                type="email"
                required
                class="w-full px-4 py-2.5 rounded-xl border border-border/80 bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/50"
                placeholder="john@example.com"
              >
            </div>

            <div class="space-y-2">
              <label class="text-sm font-semibold text-foreground">
                {{ t('theme.zenith.pages.contact.messageLabel', 'Message') }}
              </label>
              <textarea
                v-model="form.message"
                rows="5"
                required
                class="w-full px-4 py-2.5 rounded-xl border border-border/80 bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/50 resize-none"
                placeholder="How can we help you?"
              />
            </div>

            <Button
              type="submit"
              size="lg"
              variant="primary"
              class="w-full justify-center"
              :disabled="submitted"
            >
              {{ submitted ? 'Message Sent!' : t('theme.zenith.pages.contact.submitButton', 'Send Message') }}
            </Button>
          </form>
        </Card>
      </div>
    </main>

    <Footer />
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import Header from '@/modules/Content/Layout/views/themes/zenith/components/layout/Header.vue';
import Footer from '@/modules/Content/Layout/views/themes/zenith/components/layout/Footer.vue';
import { Card, Button } from '@/modules/Content/Layout/views/themes/zenith/ui';

const { t } = useI18n();

const form = ref({
  name: '',
  email: '',
  message: '',
});
const submitted = ref(false);

function handleSubmit() {
  submitted.value = true;
  setTimeout(() => {
    form.value = { name: '', email: '', message: '' };
    submitted.value = false;
  }, 3000);
}
</script>
