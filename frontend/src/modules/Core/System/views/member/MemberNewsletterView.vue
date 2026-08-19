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

      <div>
        <Button
          size="sm"
          class="text-xs h-9 px-4 rounded-xl font-semibold gap-1.5 shadow-sm shadow-primary/20"
          :disabled="saving"
          @click="savePreferences"
        >
          <Save class="w-3.5 h-3.5" />
          <span>{{ saving ? t('common.status.saving', 'Menyimpan...') : t('common.actions.save', 'Simpan Preferensi') }}</span>
        </Button>
      </div>
    </div>

    <!-- Success Alert -->
    <div
      v-if="savedMessage"
      class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 dark:text-emerald-400 text-xs font-semibold flex items-center gap-2.5 animate-in fade-in duration-300 shadow-sm"
    >
      <CheckCircle2 class="w-4 h-4 shrink-0" />
      <span>{{ savedMessage }}</span>
    </div>

    <!-- Newsletter Toggles -->
    <div class="space-y-5">
      <Card class="rounded-2xl border-border/50 p-6 space-y-6 bg-card/60 backdrop-blur-sm">
        <div>
          <h3 class="text-sm font-bold text-foreground">
            {{ t('system.member.newsletter.frequencyTitle', 'Frekuensi Pengiriman') }}
          </h3>
          <p class="text-xs text-muted-foreground mt-0.5 font-medium">
            {{ t('system.member.newsletter.frequencySubtitle', 'Pilih seberapa sering Anda ingin menerima update artikel baru') }}
          </p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
          <button
            v-for="freq in frequencyOptions"
            :key="freq.value"
            type="button"
            class="p-4 rounded-2xl border text-left transition-all cursor-pointer flex flex-col justify-between gap-2.5"
            :class="[
              frequency === freq.value
                ? 'border-primary bg-primary/5 ring-2 ring-primary/20 text-foreground'
                : 'border-border/40 hover:border-border bg-card/40 text-muted-foreground hover:text-foreground'
            ]"
            @click="frequency = freq.value"
          >
            <div class="flex items-center justify-between w-full">
              <span class="text-xs font-bold">{{ freq.label }}</span>
              <div
                class="w-4 h-4 rounded-full border flex items-center justify-center"
                :class="frequency === freq.value ? 'border-primary bg-primary text-primary-foreground' : 'border-border/60'"
              >
                <div
                  v-if="frequency === freq.value"
                  class="w-1.5 h-1.5 rounded-full bg-white"
                />
              </div>
            </div>
            <span class="text-[11px] leading-relaxed opacity-80 font-medium">{{ freq.desc }}</span>
          </button>
        </div>
      </Card>

      <Card class="rounded-2xl border-border/50 p-6 space-y-6 bg-card/60 backdrop-blur-sm">
        <div>
          <h3 class="text-sm font-bold text-foreground">
            {{ t('system.member.newsletter.topicsTitle', 'Topik Minat Bacaan') }}
          </h3>
          <p class="text-xs text-muted-foreground mt-0.5 font-medium">
            {{ t('system.member.newsletter.topicsSubtitle', 'Pilih kategori konten yang paling Anda minati') }}
          </p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
          <div
            v-for="topic in topicOptions"
            :key="topic.id"
            class="flex items-start gap-3 p-4 rounded-2xl border border-border/40 hover:border-border bg-card/30 transition-colors"
          >
            <Checkbox
              :id="`topic-${topic.id}`"
              v-model="selectedTopics[topic.id]"
              class="mt-0.5"
            />
            <label
              :for="`topic-${topic.id}`"
              class="text-xs cursor-pointer select-none space-y-0.5"
            >
              <span class="font-bold text-foreground block">{{ topic.title }}</span>
              <span class="text-[11px] text-muted-foreground block leading-relaxed font-medium">{{ topic.desc }}</span>
            </label>
          </div>
        </div>
      </Card>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue';
import { useI18n } from 'vue-i18n';
import { Card, Button, Checkbox } from '@/shared/components/ui';
import {
  Mail,
  Save,
  CheckCircle2,
} from 'lucide-vue-next';

const { t } = useI18n();

const frequency = ref<'weekly' | 'monthly' | 'never'>('weekly');
const saving = ref(false);
const savedMessage = ref('');

const frequencyOptions = [
  {
    value: 'weekly' as const,
    label: 'Mingguan (Weekly)',
    desc: 'Intisari artikel terpopuler setiap akhir pekan.',
  },
  {
    value: 'monthly' as const,
    label: 'Bulanan (Monthly)',
    desc: 'Rekap edisi khusus dan artikel pilihan 1x per bulan.',
  },
  {
    value: 'never' as const,
    label: 'Nonaktifkan',
    desc: 'Jangan kirimkan buletin email berkala.',
  },
];

const topicOptions = [
  {
    id: 'technology',
    title: 'Teknologi & Rekayasa Perangkat Lunak',
    desc: 'Arsitektur sistem, keamanan, cloud, dan pengembangan web.',
  },
  {
    id: 'design',
    title: 'Desain & Pengalaman Pengguna (UI/UX)',
    desc: 'Tren estetika visual, tata letak modern, dan interaksi web.',
  },
  {
    id: 'business',
    title: 'Bisnis Digital & Strategi Konten',
    desc: 'Optimasi SEO, pertumbuhan audiens, dan monetisasi media.',
  },
  {
    id: 'updates',
    title: 'Pemberitahuan & Rilis Fitur Baru',
    desc: 'Pengumuman pembaruan platform dan fitur komunitas.',
  },
];

const selectedTopics = reactive<Record<string, boolean>>({
  technology: true,
  design: true,
  business: false,
  updates: true,
});

const savePreferences = () => {
  saving.value = true;
  savedMessage.value = '';
  setTimeout(() => {
    saving.value = false;
    savedMessage.value = t('system.member.newsletter.savedSuccess', 'Preferensi buletin berhasil disimpan!');
    setTimeout(() => {
      savedMessage.value = '';
    }, 4000);
  }, 600);
};
</script>
