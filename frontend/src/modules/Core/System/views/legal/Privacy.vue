<template>
  <div class="min-h-screen bg-linear-to-br from-background via-muted/20 to-background px-4 py-12 sm:px-6 lg:px-8">
    <div class="max-w-4xl mx-auto">
      <!-- Back Button -->
      <div class="mb-8">
        <router-link
          :to="{ name: 'register' }"
          class="inline-flex items-center text-sm font-bold text-primary hover:text-primary/80 transition-all group"
        >
          <ArrowLeft class="mr-2 h-4 w-4 transition-transform group-hover:-translate-x-1" />
          {{ t('system.legal.backToRegister') }}
        </router-link>
      </div>

      <!-- Content Card -->
      <div class="bg-card rounded-3xl shadow-2xl shadow-primary/5 overflow-hidden border border-border/40 p-8 sm:p-12 animate-fade-up">
        <div class="flex items-start gap-3 mb-8">
          <div class="bg-primary rounded-xl p-2.5 shadow-lg shadow-primary/20 shrink-0">
            <ShieldCheck class="h-6 w-6 text-primary-foreground" />
          </div>
          <PageHeader
            borderless
            display-size
            class="min-w-0 flex-1 mb-0"
            :title="t('system.legal.privacy.title')"
            :subtitle="t('system.legal.privacy.subtitle', { date: lastUpdated })"
          />
        </div>

        <div class="prose prose-sm max-w-none text-muted-foreground/90 space-y-6">
          <section>
            <h2 class="text-xl font-bold text-foreground mb-3 border-b border-border/40 pb-2 mt-8">
              1. Information We Collect
            </h2>
            <p>
              We collect information you provide directly to us when you create an account, such as your name, email address, and any other information you choose to provide.
            </p>
          </section>

          <section>
            <h2 class="text-xl font-bold text-foreground mb-3 border-b border-border/40 pb-2 mt-8">
              2. How We Use Information
            </h2>
            <p>
              We use the information we collect to operate and improve our services, communicate with you, and personalize your experience on {{ siteName }}.
            </p>
          </section>

          <section>
            <h2 class="text-xl font-bold text-foreground mb-3 border-b border-border/40 pb-2 mt-8">
              3. Information Sharing
            </h2>
            <p>
              We do not share your personal information with third parties except as described in this policy, such as with your consent or as required by law.
            </p>
          </section>

          <section>
            <h2 class="text-xl font-bold text-foreground mb-3 border-b border-border/40 pb-2 mt-8">
              4. Data Security
            </h2>
            <p>
              We take reasonable measures to protect your personal information from loss, theft, misuse, and unauthorized access, disclosure, alteration, and destruction.
            </p>
          </section>

          <section>
            <h2 class="text-xl font-bold text-foreground mb-3 border-b border-border/40 pb-2 mt-8">
              5. Cookies and Tracking
            </h2>
            <p>
              We use cookies and similar tracking technologies to analyze trends, administer the website, and track users' movements around the website. You can control the use of cookies at the individual browser level.
            </p>
          </section>

          <section>
            <h2 class="text-xl font-bold text-foreground mb-3 border-b border-border/40 pb-2 mt-8">
              6. Contact Us
            </h2>
            <p>
              If you have any questions about this Privacy Policy, please contact us at <a
                :href="`mailto:${contactEmail}`"
                class="text-primary hover:underline"
              >{{ contactEmail }}</a>.
            </p>
          </section>
        </div>

        <div class="mt-12 pt-8 border-t border-border/40 text-center">
          <p class="text-xs text-muted-foreground">
            &copy; {{ new Date().getFullYear() }} {{ siteName }}. {{ t('system.legal.privacy.footer') }}
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { useSystemStore } from '@/modules/Core/System/stores/system';
import { PageHeader } from '@/shared/components/shell';
import {
  ArrowLeft,
  ShieldCheck,
} from 'lucide-vue-next';

const { t } = useI18n();

const systemStore = useSystemStore();
const siteName = computed(() => systemStore.siteSettings?.site_name || 'Jejakawan');
const contactEmail = computed(() => systemStore.siteSettings?.admin_email || 'support@jejakawan.com');
const lastUpdated = computed(() => {
    const today = new Date();
    return today.toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });
});
</script>

<style scoped>
</style>
