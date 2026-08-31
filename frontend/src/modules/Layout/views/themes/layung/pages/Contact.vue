<template>
  <div class="py-10 md:py-12 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
    <Breadcrumb :items="[{ name: t('pages.contact.title', 'Hubungi NOC') }]" />

    <template v-if="hasBuilderBlocks">
      <BlockRenderer
        :blocks="builderBlocks"
        :context="{ post: pageData, site: { name: displayCompanyName } }"
      />
    </template>

    <template v-else>
      <ThemeSafeHtml
        v-if="cmsBody"
        class="sr-only"
        :html="cmsBody"
        mode="publishing"
      />

      <div class="space-y-4 max-w-3xl">
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-orange-500/10 text-orange-600 dark:text-orange-400 border border-orange-500/20 font-mono uppercase">
          {{ t('pages.contact.badge', 'Pusat Operasional & Konsultasi') }}
        </span>
        <h1 class="text-3xl sm:text-4xl md:text-5xl font-black text-foreground font-heading tracking-tight">
          {{ t('pages.contact.title', 'Hubungi NOC & Cek Area Jangkauan Fiber') }}
        </h1>
        <p class="text-base sm:text-lg text-muted-foreground leading-relaxed">
          {{ t('pages.contact.subtitle', 'Butuh bantuan teknis darurat atau ingin berlangganan internet kantor baru? Tim konsultan kami siap merespon dalam hitungan menit.') }}
        </p>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        <div class="lg:col-span-5 space-y-6">
          <div class="layung-panel p-6 sm:p-8 bg-slate-950 text-white border border-slate-800 space-y-6">
            <div class="flex items-center gap-3">
              <span class="layung-status-dot" />
              <h3 class="text-lg font-bold font-heading text-white">
                {{ t('pages.contact.nocTitle', 'NOC 24/7/365 Command Center') }}
              </h3>
            </div>

            <div class="space-y-4 text-xs font-mono text-slate-300">
              <div class="flex items-start gap-3">
                <MapPin class="w-4 h-4 text-orange-400 shrink-0 mt-0.5" />
                <div>
                  <span class="text-slate-500 block text-[10px]">{{ t('pages.contact.labelAddress', 'Headquarter & Data Center') }}</span>
                  <button
                    v-if="displayAddress && mapEnabled"
                    type="button"
                    class="text-left hover:text-orange-300 transition-colors"
                    @click="openMapExternal"
                  >
                    {{ displayAddress }}
                  </button>
                  <span v-else>{{ displayAddress }}</span>
                  <div
                    v-if="displayAddress && mapEnabled"
                    class="flex flex-wrap gap-2 pt-2"
                  >
                    <button
                      type="button"
                      class="text-[10px] font-bold text-orange-400 hover:underline"
                      @click="openMapExternal"
                    >
                      {{ t('pages.contact.openMap', 'Buka di Google Maps') }}
                    </button>
                    <button
                      type="button"
                      class="text-[10px] font-bold text-slate-400 hover:text-white hover:underline"
                      @click="openMapDirections"
                    >
                      {{ t('pages.contact.getDirections', 'Petunjuk arah') }}
                    </button>
                  </div>
                </div>
              </div>

              <div class="flex items-center gap-3">
                <Phone class="w-4 h-4 text-orange-400 shrink-0" />
                <div>
                  <span class="text-slate-500 block text-[10px]">{{ t('pages.contact.labelPhone', 'Hotline Darurat NOC') }}</span>
                  <a
                    :href="nocDialHref"
                    class="text-orange-400 font-bold hover:underline"
                  >{{ displayNocPhone }}</a>
                </div>
              </div>

              <div class="flex items-center gap-3">
                <Mail class="w-4 h-4 text-orange-400 shrink-0" />
                <div>
                  <span class="text-slate-500 block text-[10px]">{{ t('pages.contact.labelEmail', 'Email Dispatch') }}</span>
                  <a
                    :href="`mailto:${displayEmail}`"
                    class="text-white hover:underline break-all"
                  >{{ displayEmail }}</a>
                </div>
              </div>
            </div>

            <div
              v-if="nocWhatsAppUrl"
              class="pt-2"
            >
              <a
                :href="nocWhatsAppUrl"
                target="_blank"
                rel="noopener noreferrer"
                class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-[var(--layung-radius-sm)] bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-md transition-colors"
              >
                <MessageCircle class="w-4 h-4" />
                <span>{{ t('pages.contact.whatsappCta', 'WhatsApp Response Cepat') }}</span>
              </a>
            </div>
          </div>
        </div>

        <div class="lg:col-span-7">
          <form
            class="layung-panel p-6 sm:p-8 space-y-6"
            @submit.prevent="handleSubmit"
          >
            <h3 class="text-2xl font-bold font-heading text-foreground">
              {{ t('pages.contact.formTitle', 'Formulir Permintaan Penawaran & Survei Fiber') }}
            </h3>

            <div
              v-if="submitted"
              class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-700 dark:text-emerald-300 text-sm font-semibold flex items-center gap-3"
            >
              <CheckCircle class="w-5 h-5 shrink-0" />
              <span>{{ t('pages.contact.submitSuccess', 'Terima kasih! Tim Solution Architect Layung akan menghubungi Anda dalam waktu maksimal 15 menit.') }}</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <Label>{{ t('pages.contact.nameLabel', 'Nama Lengkap PIC') }}</Label>
                <Input
                  v-model="form.name"
                  :placeholder="t('pages.contact.namePlaceholder', 'Contoh: Budi Santoso')"
                  required
                />
              </div>
              <div>
                <Label>{{ t('pages.contact.companyLabel', 'Nama Perusahaan / Organisasi') }}</Label>
                <Input
                  v-model="form.company"
                  :placeholder="t('pages.contact.companyPlaceholder', 'PT Inovasi Digital')"
                  required
                />
              </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <Label>{{ t('pages.contact.emailLabel', 'Email Perusahaan') }}</Label>
                <Input
                  v-model="form.email"
                  type="email"
                  :placeholder="t('pages.contact.emailPlaceholder', 'budi@perusahaan.com')"
                  required
                />
              </div>
              <div>
                <Label>{{ t('pages.contact.phoneLabel', 'Nomor Telepon / WhatsApp') }}</Label>
                <Input
                  v-model="form.phone"
                  type="tel"
                  :placeholder="t('pages.contact.phonePlaceholder', '08123456789')"
                  required
                />
              </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <Label>{{ t('pages.contact.serviceLabel', 'Kategori Layanan yang Diminati') }}</Label>
                <Select v-model="form.service">
                  <option value="dia">{{ t('pages.contact.serviceDia', 'Dedicated Internet 1:1 (DIA)') }}</option>
                  <option value="broadband">{{ t('pages.contact.serviceBroadband', 'Business Broadband') }}</option>
                  <option value="sdwan">{{ t('pages.contact.serviceSdwan', 'Managed SD-WAN & Cloud') }}</option>
                  <option value="soc">{{ t('pages.contact.serviceSoc', '24/7 Cyber Security SOC') }}</option>
                  <option value="darkfiber">{{ t('pages.contact.serviceDarkfiber', 'Dark Fiber & Metro-E') }}</option>
                </Select>
              </div>
              <div>
                <Label>{{ t('pages.contact.capacityLabel', 'Estimasi Kebutuhan Kapasitas') }}</Label>
                <Select v-model="form.capacity">
                  <option value="100m">100 Mbps</option>
                  <option value="300m">300 Mbps</option>
                  <option value="500m">500 Mbps</option>
                  <option value="1g">{{ t('pages.contact.capacityEnterprise', '1 Gbps - 10 Gbps (Enterprise)') }}</option>
                </Select>
              </div>
            </div>

            <div>
              <Label>{{ t('pages.contact.addressLabel', 'Alamat Lengkap Pemasangan / Gedung') }}</Label>
              <Textarea
                v-model="form.address"
                :placeholder="t('pages.contact.addressPlaceholder', 'Sebutkan nama gedung, lantai, jalan dan kota untuk pengecekan jarak ke ODP terdekat...')"
                :rows="3"
                required
              />
            </div>

            <Button
              type="submit"
              variant="primary"
              size="lg"
              class="w-full font-bold"
            >
              {{ t('pages.contact.submitButton', 'Kirim Permintaan Survei & Penawaran') }}
            </Button>
          </form>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { MapPin, Phone, Mail, MessageCircle, CheckCircle } from 'lucide-vue-next';
import { useThemeI18n } from '@/modules/Layout/composables/useThemeI18n';
import { useThemePageOverride } from '@/modules/Layout/composables/useThemePageOverride';
import { useThemeContactMap } from '@/modules/Layout/composables/useThemeContactMap';
import BlockRenderer from '@/modules/Layout/components/content-renderer/BlockRenderer.vue';
import ThemeSafeHtml from '@/modules/Layout/components/themes/ThemeSafeHtml.vue';
import Breadcrumb from '../components/shared/Breadcrumb.vue';
import { Button, Input, Textarea, Label, Select } from '@/modules/Layout/views/themes/layung/ui';
import { useLayungIdentity } from '../composables/useLayungIdentity';

const { t } = useThemeI18n('layung');
const { displayCompanyName, displayAddress, displayNocPhone, displayEmail, nocDialHref, nocWhatsAppUrl } = useLayungIdentity();
const { mapEnabled, openMapExternal, openMapDirections } = useThemeContactMap(displayAddress);
const { pageData, cmsBody, builderBlocks, hasBuilderBlocks } = useThemePageOverride('contact');

const submitted = ref(false);
const form = ref({
  name: '',
  company: '',
  email: '',
  phone: '',
  service: 'dia',
  capacity: '300m',
  address: '',
});

const handleSubmit = () => {
  submitted.value = true;
  setTimeout(() => {
    form.value = {
      name: '',
      company: '',
      email: '',
      phone: '',
      service: 'dia',
      capacity: '300m',
      address: '',
    };
  }, 1000);
};
</script>
