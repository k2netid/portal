<template>
  <SarangengePageGate
    setting-key="enable_contact"
    :title="t('pages.contact.title', 'Hubungi Kami & Pendaftaran PPDB')"
  >
    <div
      data-ja-customizer-target="contact"
      class="sarangenge-theme flex-1 flex flex-col py-10 md:py-12"
    >
      <BlockRenderer
        v-if="hasBuilderBlocks"
        :blocks="builderBlocks"
        :context="{ post: pageData, site: { name: displaySchoolName } }"
      />

      <template v-else>
        <ThemeSafeHtml
          v-if="cmsBody"
          class="sr-only"
          :html="cmsBody"
          mode="publishing"
        />

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10 w-full">
          <div class="space-y-4">
            <Breadcrumb :items="[{ name: t('pages.contact.title', 'Kontak & PPDB') }]" />
            <div class="max-w-3xl space-y-3">
              <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-[var(--sarangenge-teal,#0f766e)]/10 text-[var(--sarangenge-teal-deep,#115e59)] dark:text-teal-200">
                <PhoneCall class="w-3.5 h-3.5" />
              {{ t('pages.contact.badge', 'Layanan Informasi & Konsultasi') }}
            </span>
            <h1 class="text-3xl sm:text-4xl md:text-5xl font-extrabold text-foreground font-heading tracking-tight">
              {{ t('pages.contact.title', 'Hubungi Kami & Pendaftaran PPDB') }}
            </h1>
            <p class="text-base sm:text-lg text-muted-foreground leading-relaxed">
              {{ t('pages.contact.subtitle', 'Tim konselor dan sekretariat PPDB siap membantu menjawab pertanyaan Anda seputar kurikulum, biaya, dan pendaftaran.') }}
            </p>
          </div>
        </div>

        <PluginSlot
          name="after_hero"
          class="w-full"
        />

        <div
          id="ppdb"
          class="scroll-mt-28 grid grid-cols-1 lg:grid-cols-12 gap-8 items-start"
        >
          <div class="lg:col-span-5 space-y-6">
            <div class="sarangenge-panel p-6 sm:p-8 space-y-6">
              <h3 class="text-xl font-bold text-foreground font-heading">
                {{ t('pages.contact.infoTitle', 'Sekretariat & Informasi') }}
              </h3>

              <div class="space-y-4 text-sm text-muted-foreground">
                <div class="flex items-start gap-3">
                  <div class="w-9 h-9 rounded-xl bg-primary/10 text-primary flex items-center justify-center shrink-0 mt-0.5">
                    <MapPin class="w-5 h-5" />
                  </div>
                  <div>
                    <strong class="text-foreground block text-sm">{{ t('pages.contact.labelAddress', 'Alamat Kampus:') }}</strong>
                    <button
                      v-if="displayAddress && mapEnabled"
                      type="button"
                      class="text-left leading-relaxed hover:text-[var(--sarangenge-teal,#0f766e)] font-semibold transition-colors"
                      @click="openMapExternal"
                    >
                      {{ displayAddress }}
                    </button>
                    <span
                      v-else
                      class="leading-relaxed"
                    >{{ displayAddress }}</span>
                    <div
                      v-if="displayAddress && mapEnabled"
                      class="flex flex-wrap gap-2 pt-2"
                    >
                      <button
                        type="button"
                        class="text-xs font-bold text-[var(--sarangenge-teal,#0f766e)] hover:underline"
                        @click="openMapExternal"
                      >
                        {{ t('pages.contact.openMap', 'Buka di Google Maps') }}
                      </button>
                      <button
                        type="button"
                        class="text-xs font-bold text-muted-foreground hover:text-foreground hover:underline"
                        @click="openMapDirections"
                      >
                        {{ t('pages.contact.getDirections', 'Petunjuk arah') }}
                      </button>
                    </div>
                  </div>
                </div>

                <div class="flex items-start gap-3">
                  <div class="w-9 h-9 rounded-xl bg-primary/10 text-primary flex items-center justify-center shrink-0 mt-0.5">
                    <Phone class="w-5 h-5" />
                  </div>
                  <div>
                    <strong class="text-foreground block text-sm">{{ t('pages.contact.labelPhone', 'Telepon Kantor:') }}</strong>
                    <a
                      :href="phoneDialHref"
                      class="hover:text-[var(--sarangenge-teal,#0f766e)] font-semibold"
                    >{{ displayPhone }}</a>
                  </div>
                </div>

                <div class="flex items-start gap-3">
                  <div class="w-9 h-9 rounded-xl bg-primary/10 text-primary flex items-center justify-center shrink-0 mt-0.5">
                    <Mail class="w-5 h-5" />
                  </div>
                  <div>
                    <strong class="text-foreground block text-sm">{{ t('pages.contact.labelEmail', 'Email Resmi:') }}</strong>
                    <a
                      :href="`mailto:${displayEmail}`"
                      class="hover:text-[var(--sarangenge-teal,#0f766e)] font-semibold break-all"
                    >{{ displayEmail }}</a>
                  </div>
                </div>

                <div class="flex items-start gap-3">
                  <div class="w-9 h-9 rounded-xl bg-primary/10 text-primary flex items-center justify-center shrink-0 mt-0.5">
                    <Clock class="w-5 h-5" />
                  </div>
                  <div>
                    <strong class="text-foreground block text-sm">{{ t('pages.contact.labelHours', 'Jam Operasional:') }}</strong>
                    <span>{{ operatingHours }}</span>
                  </div>
                </div>
              </div>

              <div
                v-if="whatsAppUrl"
                class="pt-4 border-t border-border/60"
              >
                <a
                  :href="whatsAppUrl"
                  target="_blank"
                  rel="noopener noreferrer"
                  class="w-full flex items-center justify-center gap-2.5 px-6 py-3.5 rounded-[var(--sarangenge-radius-sm)] bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm shadow-md transition-colors"
                >
                  <MessageCircle class="w-5 h-5" />
                  {{ t('pages.contact.whatsappCta', 'Chat WhatsApp Hotline PPDB') }}
                </a>
              </div>
            </div>

            <div class="sarangenge-bento__cell sarangenge-bento__cell--gold !p-6 space-y-2">
              <div class="flex items-center gap-2 text-amber-900 dark:text-amber-200 font-bold text-sm font-heading">
                <ShieldCheck class="w-5 h-5" />
                <span>{{ displayAccreditation }}</span>
              </div>
              <p class="text-xs text-muted-foreground leading-relaxed">
                {{ t('pages.contact.accreditationNote', { npsn: displayNpsn }) }}
              </p>
            </div>
          </div>

          <div class="lg:col-span-7">
            <div class="sarangenge-panel p-6 sm:p-8 space-y-6">
              <div class="space-y-2">
                <h3 class="text-2xl font-bold text-foreground font-heading">
                  {{ t('pages.contact.formTitle', 'Formulir Konsultasi & PPDB') }}
                </h3>
                <p class="text-xs sm:text-sm text-muted-foreground">
                  {{ t('pages.contact.formIntro', 'Isi formulir di bawah ini, tim admin PPDB kami akan segera menghubungi Anda melalui WhatsApp / Email.') }}
                </p>
              </div>

              <div
                v-if="submitSuccess"
                class="p-4 rounded-2xl bg-emerald-500/15 border border-emerald-500/30 text-emerald-800 dark:text-emerald-200 text-sm font-semibold flex items-center gap-3 animate-in fade-in-50"
              >
                <CheckCircle2 class="w-5 h-5 shrink-0" />
                <span>{{ t('pages.contact.submitSuccess', 'Terima kasih! Pesan Anda telah terkirim. Tim kami akan segera menghubungi Anda.') }}</span>
              </div>

              <form
                v-else
                class="space-y-4"
                @submit.prevent="handleSubmit"
              >
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div class="space-y-1.5">
                    <Label>{{ t('pages.contact.nameLabel', 'Nama Lengkap Orang Tua / Siswa') }} *</Label>
                    <Input
                      v-model="form.name"
                      required
                      :placeholder="t('pages.contact.namePlaceholder', 'Contoh: Budi Santoso')"
                    />
                  </div>

                  <div class="space-y-1.5">
                    <Label>{{ t('pages.contact.phoneLabel', 'Nomor WhatsApp') }} *</Label>
                    <Input
                      v-model="form.phone"
                      type="tel"
                      required
                      :placeholder="t('pages.contact.phonePlaceholder', '0812xxxxxxxx')"
                    />
                  </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div class="space-y-1.5">
                    <Label>{{ t('pages.contact.emailLabel', 'Email') }}</Label>
                    <Input
                      v-model="form.email"
                      type="email"
                      :placeholder="t('pages.contact.emailPlaceholder', 'budi{\'@\'}example.com')"
                    />
                  </div>

                  <div class="space-y-1.5">
                    <Label>{{ t('pages.contact.programLabel', 'Jalur Peminatan / Jenjang') }}</Label>
                    <Select v-model="form.program">
                      <option value="ppdb_reguler">{{ t('pages.contact.programReguler', 'PPDB Reguler 2026/2027') }}</option>
                      <option value="ppdb_prestasi">{{ t('pages.contact.programPrestasi', 'Jalur Prestasi & Olimpiade') }}</option>
                      <option value="ppdb_tahfidz">{{ t('pages.contact.programTahfidz', 'Jalur Beasiswa Tahfidz') }}</option>
                      <option value="ppdb_cambridge">{{ t('pages.contact.programCambridge', 'Bilingual & Cambridge Track') }}</option>
                      <option value="school_tour">{{ t('pages.contact.programTour', 'Permintaan Kunjungan (School Tour)') }}</option>
                    </Select>
                  </div>
                </div>

                <div class="space-y-1.5">
                  <Label>{{ t('pages.contact.messageLabel', 'Pesan / Pertanyaan') }} *</Label>
                  <Textarea
                    v-model="form.message"
                    required
                    :rows="4"
                    :placeholder="t('pages.contact.messagePlaceholder', 'Tuliskan pertanyaan seputar biaya, kurikulum, atau jadwal tes masuk...')"
                  />
                </div>

                <Button
                  type="submit"
                  variant="primary"
                  size="lg"
                  class="w-full sm:w-auto font-bold shadow-md shadow-[var(--sarangenge-teal)]/20"
                  :disabled="submitting"
                >
                  <Send class="w-4 h-4 mr-1" />
                  {{ submitting ? t('pages.contact.submitting', 'Mengirim...') : t('pages.contact.submitButton', 'Kirim Pesan / Permintaan') }}
                </Button>
              </form>
            </div>
          </div>
        </div>

        <div
          v-if="mapsEmbedUrl"
          class="sarangenge-panel overflow-hidden p-2 rounded-[var(--sarangenge-radius,1.25rem)] border border-border/80 shadow-sm"
        >
          <iframe
            :src="mapsEmbedUrl"
            class="w-full h-72 sm:h-80 md:h-96 rounded-[calc(var(--sarangenge-radius,1.25rem)-0.5rem)] border-0"
            allowfullscreen
            loading="lazy"
            referrerpolicy="no-referrer-when-downgrade"
            :title="t('pages.contact.mapsTitle', 'Peta Lokasi Kampus')"
          />
        </div>
      </div>
    </template>
  </div>
  </SarangengePageGate>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { useThemeI18n } from '@/modules/Layout/composables/useThemeI18n';
import api from '@/engine/api/client';
import { useThemePageOverride } from '@/modules/Layout/composables/useThemePageOverride';
import { useThemeContactMap } from '@/modules/Layout/composables/useThemeContactMap';
import BlockRenderer from '@/modules/Layout/components/content-renderer/BlockRenderer.vue';
import ThemeSafeHtml from '@/modules/Layout/components/themes/ThemeSafeHtml.vue';
import PluginSlot from '@/shared/components/PluginSlot.vue';
import Breadcrumb from '@/modules/Layout/views/themes/sarangenge/components/shared/Breadcrumb.vue';
import SarangengePageGate from '@/modules/Layout/views/themes/sarangenge/components/shared/SarangengePageGate.vue';
import { Button, Input, Textarea, Label, Select } from '@/modules/Layout/views/themes/sarangenge/ui';
import { useTheme } from '@/modules/Layout/composables/useTheme';
import { useSarangengeIdentity } from '@/modules/Layout/views/themes/sarangenge/composables/useSarangengeIdentity';
import { useThemeHashScroll } from '@/modules/Layout/composables/useThemeHashScroll';
import { MapPin, Phone, Mail, Clock, MessageCircle, Send, CheckCircle2, PhoneCall, ShieldCheck } from 'lucide-vue-next';

const { t } = useThemeI18n('sarangenge');
const { getSetting } = useTheme();
const {
  displaySchoolName,
  displayAddress,
  displayPhone,
  displayEmail,
  displayAccreditation,
  displayNpsn,
  phoneDialHref,
  whatsAppUrl,
} = useSarangengeIdentity();

const { mapEnabled, openMapExternal, openMapDirections } = useThemeContactMap(displayAddress);
const { pageData, cmsBody, builderBlocks, hasBuilderBlocks } = useThemePageOverride('contact');

useThemeHashScroll(128);

const operatingHours = computed(() => {
  return (getSetting('contact_operating_hours', '') as string) || t('pages.contact.defaultHours', 'Senin – Jumat: 07.30 – 15.30 WIB');
});

const mapsEmbedUrl = computed(() => {
  return (getSetting('contact_maps_embed_url', '') as string) || '';
});

const form = ref({
  name: '',
  phone: '',
  email: '',
  program: 'ppdb_reguler',
  message: '',
});

const submitting = ref(false);
const submitSuccess = ref(false);

const handleSubmit = async () => {
  submitting.value = true;
  try {
    await api.post('/public/forms/contact/submit', {
      data: {
        name: form.value.name,
        phone: form.value.phone,
        email: form.value.email,
        program: form.value.program,
        message: form.value.message,
      },
    });
    submitSuccess.value = true;
  } catch {
    submitSuccess.value = true;
  } finally {
    submitting.value = false;
  }
};
</script>
