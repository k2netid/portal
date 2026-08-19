<template>
  <div class="space-y-6 max-w-4xl">
    <!-- Header Hero Banner -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-6 rounded-2xl bg-card/80 border border-border/50 shadow-sm backdrop-blur-md">
      <div class="flex items-center gap-3.5">
        <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 text-emerald-500 border border-emerald-500/20 flex items-center justify-center shrink-0 shadow-inner">
          <Mail class="w-6 h-6" />
        </div>
        <div>
          <h1 class="text-xl sm:text-2xl font-black tracking-tight text-foreground">
            {{ t('system.member.newsletter.title', 'Preferensi Buletin & Email') }}
          </h1>
          <p class="text-xs sm:text-sm text-muted-foreground mt-0.5 font-medium">
            {{ t('system.member.newsletter.subtitle', 'Atur topik bacaan dan frekuensi penerimaan intisari artikel ke email Anda') }}
          </p>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div
      v-if="loading"
      class="flex items-center justify-center py-16"
    >
      <Spinner class="w-6 h-6 text-primary animate-spin" />
    </div>

    <template v-else>
      <!-- Success/Error Alert -->
      <div
        v-if="statusMessage"
        class="p-4 rounded-2xl text-xs font-semibold flex items-center gap-2.5 animate-in fade-in duration-300 shadow-sm"
        :class="statusIsError
          ? 'bg-red-500/10 border border-red-500/20 text-red-600 dark:text-red-400'
          : 'bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 dark:text-emerald-400'"
      >
        <CheckCircle2
          v-if="!statusIsError"
          class="w-4 h-4 shrink-0"
        />
        <AlertCircle
          v-else
          class="w-4 h-4 shrink-0"
        />
        <span>{{ statusMessage }}</span>
      </div>

      <!-- Subscription Status Card -->
      <Card class="rounded-2xl border-border/50 p-6 bg-card/60 backdrop-blur-sm">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
          <div class="space-y-1.5">
            <h3 class="text-sm font-bold text-foreground">
              {{ t('system.member.newsletter.subscriptionTitle', 'Status Langganan Buletin') }}
            </h3>
            <div class="flex items-center gap-2.5">
              <div
                class="w-2.5 h-2.5 rounded-full shrink-0"
                :class="isSubscribed ? 'bg-emerald-500 animate-pulse' : 'bg-zinc-400'"
              />
              <span class="text-xs font-semibold" :class="isSubscribed ? 'text-emerald-600 dark:text-emerald-400' : 'text-muted-foreground'">
                {{ isSubscribed
                  ? t('system.member.newsletter.statusActive', 'Aktif — Anda menerima buletin berkala')
                  : t('system.member.newsletter.statusInactive', 'Nonaktif — Anda tidak menerima buletin') }}
              </span>
            </div>
            <p
              v-if="subscribedAt && isSubscribed"
              class="text-[11px] text-muted-foreground"
            >
              {{ t('system.member.newsletter.subscribedSince', 'Berlangganan sejak') }}: {{ formatDate(subscribedAt) }}
            </p>
          </div>

          <Button
            size="sm"
            class="text-xs h-9 px-5 rounded-xl font-semibold gap-2 shadow-sm shrink-0"
            :class="isSubscribed
              ? 'bg-destructive text-destructive-foreground hover:bg-destructive/90'
              : 'bg-primary text-primary-foreground hover:bg-primary/90 shadow-primary/20'"
            :disabled="toggling"
            @click="toggleSubscription"
          >
            <Loader2
              v-if="toggling"
              class="w-3.5 h-3.5 animate-spin"
            />
            <template v-else>
              <BellOff
                v-if="isSubscribed"
                class="w-3.5 h-3.5"
              />
              <Bell
                v-else
                class="w-3.5 h-3.5"
              />
            </template>
            <span>
              {{ isSubscribed
                ? t('system.member.newsletter.unsubscribe', 'Berhenti Berlangganan')
                : t('system.member.newsletter.subscribe', 'Mulai Berlangganan') }}
            </span>
          </Button>
        </div>
      </Card>

      <!-- Info Box -->
      <Card class="rounded-2xl border-border/50 p-6 space-y-4 bg-card/60 backdrop-blur-sm">
        <div>
          <h3 class="text-sm font-bold text-foreground">
            {{ t('system.member.newsletter.aboutTitle', 'Tentang Buletin Kami') }}
          </h3>
          <p class="text-xs text-muted-foreground mt-1 leading-relaxed font-medium">
            {{ t('system.member.newsletter.aboutDescription', 'Buletin kami dikirimkan secara berkala berisi intisari artikel terpopuler, tips eksklusif, dan pemberitahuan fitur baru. Email Anda tidak akan pernah dibagikan ke pihak ketiga.') }}
          </p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
          <div class="flex items-start gap-3 p-3.5 rounded-2xl border border-border/30 bg-muted/10">
            <Newspaper class="w-5 h-5 text-primary shrink-0 mt-0.5" />
            <div class="text-xs space-y-0.5">
              <span class="font-bold text-foreground block">{{ t('system.member.newsletter.featureDigest', 'Intisari Artikel') }}</span>
              <span class="text-muted-foreground font-medium">{{ t('system.member.newsletter.featureDigestDesc', 'Ringkasan konten pilihan mingguan') }}</span>
            </div>
          </div>
          <div class="flex items-start gap-3 p-3.5 rounded-2xl border border-border/30 bg-muted/10">
            <Sparkles class="w-5 h-5 text-amber-500 shrink-0 mt-0.5" />
            <div class="text-xs space-y-0.5">
              <span class="font-bold text-foreground block">{{ t('system.member.newsletter.featureTips', 'Tips Eksklusif') }}</span>
              <span class="text-muted-foreground font-medium">{{ t('system.member.newsletter.featureTipsDesc', 'Wawasan dan panduan khusus anggota') }}</span>
            </div>
          </div>
          <div class="flex items-start gap-3 p-3.5 rounded-2xl border border-border/30 bg-muted/10">
            <Bell class="w-5 h-5 text-blue-500 shrink-0 mt-0.5" />
            <div class="text-xs space-y-0.5">
              <span class="font-bold text-foreground block">{{ t('system.member.newsletter.featureUpdates', 'Rilis Fitur Baru') }}</span>
              <span class="text-muted-foreground font-medium">{{ t('system.member.newsletter.featureUpdatesDesc', 'Pengumuman pembaruan platform') }}</span>
            </div>
          </div>
        </div>
      </Card>
    </template>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import apiClient from '@/engine/api/client';
import { memberPaths } from '@/engine/api/paths';
import { Card, Button, Spinner } from '@/shared/components/ui';
import {
  Mail,
  CheckCircle2,
  AlertCircle,
  Bell,
  BellOff,
  Newspaper,
  Sparkles,
  Loader2,
} from 'lucide-vue-next';

const { t } = useI18n();

const loading = ref(true);
const toggling = ref(false);
const isSubscribed = ref(false);
const subscribedAt = ref<string | null>(null);
const statusMessage = ref('');
const statusIsError = ref(false);

const formatDate = (dateStr: string): string => {
  try {
    return new Date(dateStr).toLocaleDateString('id-ID', {
      day: 'numeric',
      month: 'long',
      year: 'numeric',
    });
  } catch {
    return dateStr;
  }
};

const fetchNewsletterStatus = async () => {
  loading.value = true;
  try {
    const { data } = await apiClient.get(memberPaths.newsletter);
    isSubscribed.value = data.data?.subscribed === true;
    subscribedAt.value = data.data?.subscribed_at || null;
  } catch (e) {
    console.error('Failed to fetch newsletter status:', e);
  } finally {
    loading.value = false;
  }
};

const toggleSubscription = async () => {
  toggling.value = true;
  statusMessage.value = '';
  statusIsError.value = false;
  try {
    const { data } = await apiClient.put(memberPaths.newsletter, {
      subscribe: !isSubscribed.value,
    });
    isSubscribed.value = data.data?.subscribed === true;
    subscribedAt.value = data.data?.subscribed_at || subscribedAt.value;
    statusMessage.value = data.message || (isSubscribed.value
      ? t('system.member.newsletter.subscribedSuccess', 'Berhasil berlangganan buletin!')
      : t('system.member.newsletter.unsubscribedSuccess', 'Berhasil berhenti berlangganan.'));
    setTimeout(() => { statusMessage.value = ''; }, 5000);
  } catch (e) {
    statusIsError.value = true;
    statusMessage.value = t('system.member.newsletter.error', 'Terjadi kesalahan. Silakan coba lagi.');
    setTimeout(() => { statusMessage.value = ''; }, 5000);
    console.error('Failed to toggle newsletter:', e);
  } finally {
    toggling.value = false;
  }
};

onMounted(() => {
  fetchNewsletterStatus();
});
</script>
