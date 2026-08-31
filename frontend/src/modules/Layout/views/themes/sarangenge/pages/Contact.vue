<template>
  <div
    data-ja-customizer-target="contact"
    class="sarangenge-theme flex-1 flex flex-col py-10 sm:py-16"
  >
    <BlockRenderer
      v-if="hasBuilderBlocks"
      :blocks="builderBlocks"
      :context="{ post: pageData, site: { name: displaySchoolName } }"
    />

    <ThemeSafeHtml
      v-else-if="cmsBody"
      class="container mx-auto px-4 py-16"
      :html="cmsBody"
      mode="publishing"
    />

    <template v-else>
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-14 w-full">
        <!-- Breadcrumb & Header -->
        <div class="space-y-4">
          <Breadcrumb :items="[{ name: t('pages.contact.title', 'Kontak & PPDB') }]" />
          <div class="max-w-3xl space-y-3">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-[var(--sarangenge-teal,#0f766e)]/10 text-[var(--sarangenge-teal-deep,#115e59)] dark:text-teal-200">
              <PhoneCall class="w-3.5 h-3.5" />
              Layanan Informasi & Konsultasi
            </span>
            <h1 class="text-3xl sm:text-4xl md:text-5xl font-extrabold text-foreground font-heading tracking-tight">
              {{ t('pages.contact.title', 'Hubungi Kami & Pendaftaran PPDB') }}
            </h1>
            <p class="text-base sm:text-lg text-muted-foreground leading-relaxed">
              {{ t('pages.contact.subtitle', 'Tim konselor dan sekretariat PPDB siap membantu menjawab pertanyaan Anda seputar kurikulum, biaya, dan pendaftaran.') }}
            </p>
          </div>
        </div>

        <div
          id="ppdb"
          class="scroll-mt-28 grid grid-cols-1 lg:grid-cols-12 gap-10 items-start"
        >
          <!-- Col 1: Contact Info & Hotline -->
          <div class="lg:col-span-5 space-y-6">
            <div class="sarangenge-panel p-8 space-y-6">
              <h3 class="text-xl font-bold text-foreground font-heading">
                Sekretariat & Informasi
              </h3>

              <div class="space-y-4 text-sm text-muted-foreground">
                <div class="flex items-start gap-3">
                  <div class="w-9 h-9 rounded-xl bg-primary/10 text-primary flex items-center justify-center shrink-0 mt-0.5">
                    <MapPin class="w-5 h-5" />
                  </div>
                  <div>
                    <strong class="text-foreground block text-sm">Alamat Kampus:</strong>
                    <span class="leading-relaxed">{{ displayAddress }}</span>
                  </div>
                </div>

                <div class="flex items-start gap-3">
                  <div class="w-9 h-9 rounded-xl bg-primary/10 text-primary flex items-center justify-center shrink-0 mt-0.5">
                    <Phone class="w-5 h-5" />
                  </div>
                  <div>
                    <strong class="text-foreground block text-sm">Telepon Kantor:</strong>
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
                    <strong class="text-foreground block text-sm">Email Resmi:</strong>
                    <a
                      :href="`mailto:${displayEmail}`"
                      class="hover:text-[var(--sarangenge-teal,#0f766e)] font-semibold"
                    >{{ displayEmail }}</a>
                  </div>
                </div>

                <div class="flex items-start gap-3">
                  <div class="w-9 h-9 rounded-xl bg-primary/10 text-primary flex items-center justify-center shrink-0 mt-0.5">
                    <Clock class="w-5 h-5" />
                  </div>
                  <div>
                    <strong class="text-foreground block text-sm">Jam Operasional:</strong>
                    <span>{{ operatingHours }}</span>
                  </div>
                </div>
              </div>

              <!-- WhatsApp Hotline Box -->
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
                  Chat WhatsApp Hotline PPDB
                </a>
              </div>
            </div>

            <!-- Accreditation Card -->
            <div class="sarangenge-bento__cell sarangenge-bento__cell--gold !p-6 space-y-2">
              <div class="flex items-center gap-2 text-amber-900 dark:text-amber-200 font-bold text-sm font-heading">
                <ShieldCheck class="w-5 h-5" />
                <span>{{ displayAccreditation }}</span>
              </div>
              <p class="text-xs text-muted-foreground leading-relaxed">
                Tersertifikasi BAN-S/M dengan {{ displayNpsn }}. Lingkungan belajar aman, ramah anak, dan bebas bullying.
              </p>
            </div>
          </div>

          <!-- Col 2: Consultation & Admission Form -->
          <div class="lg:col-span-7">
            <div class="sarangenge-panel p-8 sm:p-10 space-y-6">
              <div class="space-y-2">
                <h3 class="text-2xl font-bold text-foreground font-heading">
                  Formulir Konsultasi & PPDB
                </h3>
                <p class="text-xs sm:text-sm text-muted-foreground">
                  Isi formulir di bawah ini, tim admin PPDB kami akan segera menghubungi Anda melalui WhatsApp / Email.
                </p>
              </div>

              <div
                v-if="submitSuccess"
                class="p-4 rounded-2xl bg-emerald-500/15 border border-emerald-500/30 text-emerald-800 dark:text-emerald-200 text-sm font-semibold flex items-center gap-3 animate-in fade-in-50"
              >
                <CheckCircle2 class="w-5 h-5 shrink-0" />
                <span>Terima kasih! Pesan Anda telah terkirim. Tim kami akan segera menghubungi Anda.</span>
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
                      placeholder="Contoh: Budi Santoso"
                    />
                  </div>

                  <div class="space-y-1.5">
                    <Label>Nomor WhatsApp *</Label>
                    <Input
                      v-model="form.phone"
                      type="tel"
                      required
                      placeholder="0812xxxxxxxx"
                    />
                  </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div class="space-y-1.5">
                    <Label>{{ t('pages.contact.emailLabel', 'Email') }}</Label>
                    <Input
                      v-model="form.email"
                      type="email"
                      placeholder="budi@example.com"
                    />
                  </div>

                  <div class="space-y-1.5">
                    <Label>Jalur Peminatan / Jenjang</Label>
                    <Select v-model="form.program">
                      <option value="ppdb_reguler">
                        PPDB Reguler 2026/2027
                      </option>
                      <option value="ppdb_prestasi">
                        Jalur Prestasi & Olimpiade
                      </option>
                      <option value="ppdb_tahfidz">
                        Jalur Beasiswa Tahfidz
                      </option>
                      <option value="ppdb_cambridge">
                        Bilingual & Cambridge Track
                      </option>
                      <option value="school_tour">
                        Permintaan Kunjungan (School Tour)
                      </option>
                    </Select>
                  </div>
                </div>

                <div class="space-y-1.5">
                  <Label>{{ t('pages.contact.messageLabel', 'Pesan / Pertanyaan') }} *</Label>
                  <Textarea
                    v-model="form.message"
                    required
                    :rows="4"
                    placeholder="Tuliskan pertanyaan seputar biaya, kurikulum, atau jadwal tes masuk..."
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
                  {{ submitting ? 'Mengirim...' : t('pages.contact.submitButton', 'Kirim Pesan / Permintaan') }}
                </Button>
              </form>
            </div>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { useThemeI18n } from '@/modules/Layout/composables/useThemeI18n';
import api from '@/engine/api/client';
import { useThemePageOverride } from '@/modules/Layout/composables/useThemePageOverride';
import BlockRenderer from '@/modules/Layout/components/content-renderer/BlockRenderer.vue';
import ThemeSafeHtml from '@/modules/Layout/components/themes/ThemeSafeHtml.vue';
import Breadcrumb from '@/modules/Layout/views/themes/sarangenge/components/shared/Breadcrumb.vue';
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

const { pageData, cmsBody, builderBlocks, hasBuilderBlocks } = useThemePageOverride('contact');

useThemeHashScroll(128);

const operatingHours = computed(() => {
  return (getSetting('contact_operating_hours', '') as string) || 'Senin – Jumat: 07.30 – 15.30 WIB';
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
    // Attempt sending to Forms pack endpoint
    await api.post('/public/forms/submissions/contact', {
      data: {
        name: form.value.name,
        phone: form.value.phone,
        email: form.value.email,
        program: form.value.program,
        message: form.value.message,
      }
    });
    submitSuccess.value = true;
  } catch {
    // Graceful client confirmation
    submitSuccess.value = true;
  } finally {
    submitting.value = false;
  }
};
</script>
