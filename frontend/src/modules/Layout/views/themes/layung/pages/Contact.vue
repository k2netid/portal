<template>
  <div class="py-12 sm:py-16 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-16">
    <Breadcrumb :items="[{ name: t('pages.contact.title', 'Hubungi NOC') }]" />

    <template v-if="hasBuilderBlocks">
      <BlockRenderer
        :blocks="builderBlocks"
        :context="{ post: pageData, site: { name: displayCompanyName } }"
      />
    </template>

    <template v-else-if="cmsBody">
      <div class="prose dark:prose-invert max-w-none text-muted-foreground leading-relaxed">
        <ThemeSafeHtml :html="cmsBody" />
      </div>
    </template>

    <template v-else>
      <div class="space-y-4 max-w-3xl">
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-orange-500/10 text-orange-600 dark:text-orange-400 border border-orange-500/20 font-mono uppercase">
          Pusat Operasional & Konsultasi
        </span>
        <h1 class="text-4xl sm:text-5xl font-black text-foreground font-heading tracking-tight">
          {{ t('pages.contact.title', 'Hubungi NOC & Cek Area Jangkauan Fiber') }}
        </h1>
        <p class="text-base sm:text-lg text-muted-foreground leading-relaxed">
          {{ t('pages.contact.subtitle', 'Butuh bantuan teknis darurat atau ingin berlangganan internet kantor baru? Tim konsultan kami siap merespon dalam hitungan menit.') }}
        </p>
      </div>

      <!-- Contact & Form Grid -->
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-12">
        <!-- Left: NOC Details -->
        <div class="lg:col-span-5 space-y-8">
          <div class="layung-panel p-8 bg-slate-950 text-white border border-slate-800 space-y-6">
            <div class="flex items-center gap-3">
              <span class="layung-status-dot" />
              <h3 class="text-lg font-bold font-heading text-white">
                NOC 24/7/365 Command Center
              </h3>
            </div>

            <div class="space-y-4 text-xs font-mono text-slate-300">
              <div class="flex items-start gap-3">
                <MapPin class="w-4 h-4 text-orange-400 shrink-0 mt-0.5" />
                <div>
                  <span class="text-slate-500 block text-[10px]">Headquarter & Data Center</span>
                  <span>{{ displayAddress }}</span>
                </div>
              </div>

              <div class="flex items-center gap-3">
                <Phone class="w-4 h-4 text-orange-400 shrink-0" />
                <div>
                  <span class="text-slate-500 block text-[10px]">Hotline Darurat NOC</span>
                  <a
                    :href="nocDialHref"
                    class="text-orange-400 font-bold hover:underline"
                  >{{ displayNocPhone }}</a>
                </div>
              </div>

              <div class="flex items-center gap-3">
                <Mail class="w-4 h-4 text-orange-400 shrink-0" />
                <div>
                  <span class="text-slate-500 block text-[10px]">Email Dispatch</span>
                  <a
                    :href="`mailto:${displayEmail}`"
                    class="text-white hover:underline"
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
                <span>WhatsApp Response Cepat</span>
              </a>
            </div>
          </div>
        </div>

        <!-- Right: Enterprise Quotation Form -->
        <div class="lg:col-span-7">
          <form
            class="layung-panel p-8 sm:p-10 space-y-6"
            @submit.prevent="handleSubmit"
          >
            <h3 class="text-2xl font-bold font-heading text-foreground">
              Formulir Permintaan Penawaran & Survei Fiber
            </h3>

            <div
              v-if="submitted"
              class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-700 dark:text-emerald-300 text-sm font-semibold flex items-center gap-3"
            >
              <CheckCircle class="w-5 h-5 shrink-0" />
              <span>Terima kasih! Tim Solution Architect Layung akan menghubungi Anda dalam waktu maksimal 15 menit.</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <Label>Nama Lengkap PIC</Label>
                <Input
                  v-model="form.name"
                  placeholder="Contoh: Budi Santoso"
                  required
                />
              </div>
              <div>
                <Label>Nama Perusahaan / Organisasi</Label>
                <Input
                  v-model="form.company"
                  placeholder="PT Inovasi Digital"
                  required
                />
              </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <Label>Email Perusahaan</Label>
                <Input
                  v-model="form.email"
                  type="email"
                  placeholder="budi@perusahaan.com"
                  required
                />
              </div>
              <div>
                <Label>Nomor Telepon / WhatsApp</Label>
                <Input
                  v-model="form.phone"
                  type="tel"
                  placeholder="08123456789"
                  required
                />
              </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <Label>Kategori Layanan yang Diminati</Label>
                <Select v-model="form.service">
                  <option value="dia">
                    Dedicated Internet 1:1 (DIA)
                  </option>
                  <option value="broadband">
                    Business Broadband
                  </option>
                  <option value="sdwan">
                    Managed SD-WAN & Cloud
                  </option>
                  <option value="soc">
                    24/7 Cyber Security SOC
                  </option>
                  <option value="darkfiber">
                    Dark Fiber & Metro-E
                  </option>
                </Select>
              </div>
              <div>
                <Label>Estimasi Kebutuhan Kapasitas</Label>
                <Select v-model="form.capacity">
                  <option value="100m">
                    100 Mbps
                  </option>
                  <option value="300m">
                    300 Mbps
                  </option>
                  <option value="500m">
                    500 Mbps
                  </option>
                  <option value="1g">
                    1 Gbps - 10 Gbps (Enterprise)
                  </option>
                </Select>
              </div>
            </div>

            <div>
              <Label>Alamat Lengkap Pemasangan / Gedung</Label>
              <Textarea
                v-model="form.address"
                placeholder="Sebutkan nama gedung, lantai, jalan dan kota untuk pengecekan jarak ke Optical Distribution Point (ODP) terdekat..."
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
              Kirim Permintaan Survei & Penawaran
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
import BlockRenderer from '@/modules/Layout/components/content-renderer/BlockRenderer.vue';
import ThemeSafeHtml from '@/modules/Layout/components/themes/ThemeSafeHtml.vue';
import Breadcrumb from '../components/shared/Breadcrumb.vue';
import { Button, Input, Textarea, Label, Select } from '@/modules/Layout/views/themes/layung/ui';
import { useLayungIdentity } from '../composables/useLayungIdentity';

const { t } = useThemeI18n('layung');
const { displayCompanyName, displayAddress, displayNocPhone, displayEmail, nocDialHref, nocWhatsAppUrl } = useLayungIdentity();
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
