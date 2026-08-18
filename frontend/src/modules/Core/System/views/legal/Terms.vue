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
            <FileText class="h-6 w-6 text-primary-foreground" />
          </div>
          <PageHeader
            borderless
            display-size
            class="min-w-0 flex-1 mb-0"
            :title="t('system.legal.terms.title')"
            :subtitle="t('system.legal.terms.subtitle', { date: lastUpdated })"
          />
        </div>

        <div class="prose prose-sm max-w-none text-muted-foreground/90 space-y-6">
          <section>
            <h2 class="text-xl font-bold text-foreground mb-3 border-b border-border/40 pb-2 mt-8">
              1. Agreement to Terms
            </h2>
            <p>
              By accessing or using {{ siteName }}, you agree to be bound by these Terms of Service. If you do not agree to all of these terms, do not access or use our services.
            </p>
          </section>

          <section>
            <h2 class="text-xl font-bold text-foreground mb-3 border-b border-border/40 pb-2 mt-8">
              2. User Accounts
            </h2>
            <p>
              To access certain features of {{ siteName }}, you may be required to create an account. You are responsible for maintaining the confidentiality of your account credentials and for all activities that occur under your account.
            </p>
          </section>

          <section>
            <h2 class="text-xl font-bold text-foreground mb-3 border-b border-border/40 pb-2 mt-8">
              3. Acceptable Use
            </h2>
            <p>
              You agree not to use {{ siteName }} for any unlawful purpose or in any way that could damage, disable, or impair our services. This includes, but is not limited to, attempting to gain unauthorized access to our systems or interfering with other users' enjoyment of the platform.
            </p>
          </section>

          <section>
            <h2 class="text-xl font-bold text-foreground mb-3 border-b border-border/40 pb-2 mt-8">
              4. Intellectual Property
            </h2>
            <p>
              All content, features, and functionality on {{ siteName }} are the exclusive property of {{ siteName }} and its licensors and are protected by international copyright, trademark, and other intellectual property laws.
            </p>
          </section>

          <section>
            <h2 class="text-xl font-bold text-foreground mb-3 border-b border-border/40 pb-2 mt-8">
              5. Limitation of Liability
            </h2>
            <p>
              To the maximum extent permitted by law, {{ siteName }} shall not be liable for any indirect, incidental, special, consequential, or punitive damages resulting from your use of or inability to use the service.
            </p>
          </section>

          <section>
            <h2 class="text-xl font-bold text-foreground mb-3 border-b border-border/40 pb-2 mt-8">
              6. Changes to Terms
            </h2>
            <p>
              We reserve the right to modify or replace these Terms at any time. We will provide notice of any material changes by posting the new Terms on this page.
            </p>
          </section>
        </div>

        <div class="mt-12 pt-8 border-t border-border/40 text-center">
          <p class="text-xs text-muted-foreground">
            &copy; {{ new Date().getFullYear() }} {{ siteName }}. {{ t('system.legal.terms.footer') }}
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
  FileText,
} from 'lucide-vue-next';

const { t } = useI18n();

const systemStore = useSystemStore();
const siteName = computed(() => systemStore.siteSettings?.site_name || 'Jejakawan');
const lastUpdated = computed(() => {
    const today = new Date();
    return today.toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });
});
</script>

<style scoped>
</style>
