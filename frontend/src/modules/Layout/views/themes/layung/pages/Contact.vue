<template>
  <div
    id="contact"
    class="layung-page flex-1 w-full py-10 md:py-16 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10"
    data-ja-customizer-target="contact"
  >
    <div v-if="!isEnabled">
      <PageDisabled
        :title="t('pages.contact.title', 'Kontak')"
        :message="disabledPageMessage"
      />
    </div>

    <div
      v-else-if="pageLoading"
      class="flex flex-col items-center justify-center min-h-[50vh] gap-4"
    >
      <Loader2 class="w-10 h-10 animate-spin text-sky-500/60" />
      <p class="text-sm font-medium text-muted-foreground">
        {{ t('pages.contact.loadingPage', 'Memuat halaman kontak…') }}
      </p>
    </div>

    <template v-else>
      <Breadcrumb :items="[{ name: t('pages.contact.title', 'Kontak') }]" />

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
          <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-sky-500/10 text-sky-600 dark:text-sky-400 border border-sky-500/20 font-mono uppercase">
            {{ t('pages.contact.badge', 'Kontak') }}
          </span>
          <h1 class="text-3xl sm:text-4xl md:text-5xl font-black text-foreground font-heading tracking-tight">
            {{ t('pages.contact.title', 'Hubungi Kami') }}
          </h1>
          <p class="text-base sm:text-lg text-muted-foreground leading-relaxed">
            {{ t('pages.contact.subtitle', 'Sales, CS, NOC, dan Service Desk siap membantu kebutuhan internet dan IT Anda.') }}
          </p>
        </div>

        <PluginSlot
          name="after_hero"
          class="w-full"
        />

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8 items-stretch">
          <div
            ref="infoCol"
            class="lg:col-span-5 flex"
          >
            <div class="layung-panel p-6 sm:p-8 space-y-5 w-full h-full flex flex-col">
              <div class="flex items-center gap-3">
                <span class="layung-status-dot" />
                <h3 class="text-lg font-bold font-heading text-foreground">
                  {{ t('pages.contact.nocTitle', 'Kontak operasional') }}
                </h3>
              </div>

            <div class="grid grid-cols-1 gap-3">
              <ContactMapPopover
                v-for="place in locationCards"
                :key="place.key"
                as-card
                :address="place.address"
                :label="place.label"
                :use-direct-link="place.useDirectLink"
                @mouseenter="onInfoItemEnter"
                @mouseleave="onInfoItemLeave"
                @mousedown="onInfoItemPress"
                @mouseup="onInfoItemRelease"
              />
            </div>

            <div class="grid grid-cols-1 gap-3 mt-auto">
              <div
                v-for="(row, index) in channelRows"
                :key="`channel-${index}`"
                class="grid grid-cols-1 sm:grid-cols-2 gap-3"
              >
                <a
                  v-if="row.wa"
                  :href="row.wa.url"
                  target="_blank"
                  rel="noopener noreferrer"
                  class="layung-contact-card layung-contact-card--channel contact-info-item"
                  :aria-label="`${t('pages.contact.whatsappAria', 'WhatsApp')} ${row.wa.label}`"
                  @mouseenter="onInfoItemEnter"
                  @mouseleave="onInfoItemLeave"
                  @mousedown="onInfoItemPress"
                  @mouseup="onInfoItemRelease"
                >
                  <span class="layung-contact-card__icon layung-contact-card__icon--wa">
                    <MessageCircle class="w-4 h-4" />
                  </span>
                  <span class="min-w-0 flex-1">
                    <span class="layung-contact-card__label">{{ row.wa.label }}</span>
                  </span>
                </a>
                <span
                  v-else
                  class="hidden sm:block"
                />

                <a
                  v-if="row.email"
                  :href="`mailto:${row.email.address}`"
                  class="layung-contact-card layung-contact-card--channel contact-info-item"
                  :aria-label="`${row.email.label} ${row.email.address}`"
                  @mouseenter="onInfoItemEnter"
                  @mouseleave="onInfoItemLeave"
                  @mousedown="onInfoItemPress"
                  @mouseup="onInfoItemRelease"
                >
                  <span class="layung-contact-card__icon text-sky-500">
                    <Mail class="w-4 h-4" />
                  </span>
                  <span class="min-w-0 flex-1">
                    <span class="layung-contact-card__label">{{ row.email.label }}</span>
                    <span class="layung-contact-card__value break-all">{{ row.email.address }}</span>
                  </span>
                </a>
              </div>
            </div>
            </div>
          </div>

          <div
            id="contact-form"
            ref="formCol"
            class="lg:col-span-7 scroll-mt-20 flex"
          >
            <div class="layung-panel p-6 sm:p-8 space-y-6 w-full h-full flex flex-col">
              <div>
                <h3 class="text-2xl font-bold font-heading text-foreground">
                  {{ formDefinition?.name || t('pages.contact.formTitle', 'Formulir permintaan penawaran') }}
                </h3>
                <p class="mt-2 text-sm text-muted-foreground leading-relaxed">
                  {{ formDefinition?.description || t('pages.contact.formDescription', 'Isi formulir di bawah ini. Tim Kami akan menghubungi Anda.') }}
                </p>
              </div>

              <div
                v-if="formLoading"
                class="flex flex-col items-center justify-center py-16 gap-3"
              >
                <Loader2 class="w-8 h-8 animate-spin text-sky-500/60" />
                <p class="text-sm text-muted-foreground">
                  {{ t('pages.contact.formLoading', 'Memuat formulir…') }}
                </p>
              </div>

              <Alert
                v-else-if="formLoadError"
                variant="error"
              >
                <p class="font-semibold">
                  {{ t('pages.contact.formUnavailableTitle', 'Formulir tidak tersedia') }}
                </p>
                <p>{{ formLoadError }}</p>
              </Alert>

              <form
                v-else-if="formDefinition && formDefinition.fields?.length"
                class="space-y-6 flex-1 flex flex-col"
                @submit.prevent="submitForm"
              >
                <div
                  v-for="field in formDefinition.fields"
                  :key="field.name"
                  class="space-y-2"
                  @focusin="trackStartOnce"
                >
                  <template v-if="field.type === 'file' || field.type === 'image'">
                    <Label :for="'cf-' + field.name">
                      {{ field.label }}
                      <span
                        v-if="field.is_required"
                        class="text-red-500"
                      >*</span>
                    </Label>
                    <p
                      v-if="field.help_text"
                      class="text-xs text-muted-foreground"
                    >
                      {{ field.help_text }}
                    </p>
                    <Input
                      :id="'cf-' + field.name"
                      type="file"
                      class="cursor-pointer file:mr-3 file:rounded file:border-0 file:bg-sky-500/10 file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-sky-600"
                      :accept="field.type === 'image' ? 'image/*' : undefined"
                      :required="field.is_required"
                      :class="fieldErrorClass(field.name)"
                      @change="onFileInputChange(field.name, $event)"
                    />
                  </template>

                  <template v-else>
                    <Label :for="'cf-' + field.name">
                      {{ field.label }}
                      <span
                        v-if="field.is_required"
                        class="text-red-500"
                      >*</span>
                    </Label>
                    <p
                      v-if="field.help_text"
                      class="text-xs text-muted-foreground"
                    >
                      {{ field.help_text }}
                    </p>

                    <Input
                      v-if="field.type === 'text'"
                      :id="'cf-' + field.name"
                      v-model="formValues[field.name] as string"
                      :type="field.name === 'phone' ? 'tel' : 'text'"
                      :placeholder="field.placeholder || ''"
                      :required="field.is_required"
                      :class="fieldErrorClass(field.name)"
                    />

                    <Input
                      v-else-if="field.type === 'email'"
                      :id="'cf-' + field.name"
                      v-model="formValues[field.name] as string"
                      type="email"
                      :placeholder="field.placeholder || ''"
                      :required="field.is_required"
                      :class="fieldErrorClass(field.name)"
                    />

                    <Input
                      v-else-if="field.type === 'url'"
                      :id="'cf-' + field.name"
                      v-model="formValues[field.name] as string"
                      type="url"
                      :placeholder="field.placeholder || ''"
                      :required="field.is_required"
                      :class="fieldErrorClass(field.name)"
                    />

                    <Input
                      v-else-if="field.type === 'number'"
                      :id="'cf-' + field.name"
                      v-model="formValues[field.name] as string"
                      type="number"
                      :placeholder="field.placeholder || ''"
                      :required="field.is_required"
                      :class="fieldErrorClass(field.name)"
                    />

                    <Textarea
                      v-else-if="field.type === 'textarea'"
                      :id="'cf-' + field.name"
                      v-model="formValues[field.name] as string"
                      :rows="4"
                      :placeholder="field.placeholder || ''"
                      :required="field.is_required"
                      :class="fieldErrorClass(field.name)"
                    />

                    <Input
                      v-else-if="field.type === 'date'"
                      :id="'cf-' + field.name"
                      v-model="formValues[field.name] as string"
                      type="date"
                      :required="field.is_required"
                      :class="fieldErrorClass(field.name)"
                    />

                    <Input
                      v-else-if="field.type === 'datetime'"
                      :id="'cf-' + field.name"
                      v-model="formValues[field.name] as string"
                      type="datetime-local"
                      :required="field.is_required"
                      :class="fieldErrorClass(field.name)"
                    />

                    <div
                      v-else-if="field.type === 'boolean'"
                      class="pt-1"
                    >
                      <Checkbox
                        :id="'cf-' + field.name"
                        :model-value="!!formValues[field.name]"
                        @update:model-value="formValues[field.name] = $event"
                      >
                        {{ field.placeholder || t('pages.contact.yes', 'Ya') }}
                      </Checkbox>
                    </div>

                    <Select
                      v-else-if="field.type === 'select'"
                      :id="'cf-' + field.name"
                      :model-value="String(formValues[field.name] ?? '')"
                      :required="field.is_required"
                      :class="fieldErrorClass(field.name)"
                      @update:model-value="formValues[field.name] = $event"
                    >
                      <option
                        value=""
                        disabled
                      >
                        {{ field.placeholder || t('pages.contact.select', 'Pilih') }}
                      </option>
                      <option
                        v-for="opt in selectOptions(field)"
                        :key="opt.value"
                        :value="opt.value"
                      >
                        {{ opt.label }}
                      </option>
                    </Select>

                    <div
                      v-else-if="field.type === 'radio'"
                      class="space-y-2"
                    >
                      <label
                        v-for="opt in selectOptions(field)"
                        :key="opt.value"
                        class="flex items-center gap-2 text-sm cursor-pointer"
                      >
                        <input
                          v-model="formValues[field.name]"
                          type="radio"
                          class="h-4 w-4 accent-sky-600"
                          :value="opt.value"
                          :name="'cf-' + field.name"
                          :required="field.is_required"
                        >
                        <span>{{ opt.label }}</span>
                      </label>
                    </div>

                    <div
                      v-else-if="field.type === 'multiselect' || field.type === 'checkbox'"
                      class="space-y-2"
                    >
                      <label
                        v-for="opt in selectOptions(field)"
                        :key="opt.value"
                        class="flex items-center gap-2 text-sm cursor-pointer"
                      >
                        <input
                          type="checkbox"
                          class="w-4 h-4 rounded text-sky-600 border-border/80 focus:ring-sky-500"
                          :checked="multiHas(field.name, opt.value)"
                          @change="toggleMulti(field.name, opt.value, ($event.target as HTMLInputElement).checked)"
                        >
                        <span>{{ opt.label }}</span>
                      </label>
                    </div>

                    <Textarea
                      v-else-if="field.type === 'json'"
                      :id="'cf-' + field.name"
                      v-model="formValues[field.name] as string"
                      :rows="4"
                      class="font-mono text-xs"
                      :placeholder="field.placeholder || '{}'"
                      :required="field.is_required"
                      :class="fieldErrorClass(field.name)"
                    />

                    <Input
                      v-else
                      :id="'cf-' + field.name"
                      v-model="formValues[field.name] as string"
                      type="text"
                      :placeholder="field.placeholder || ''"
                      :required="field.is_required"
                      :class="fieldErrorClass(field.name)"
                    />
                  </template>

                  <p
                    v-if="fieldErrors[field.name]"
                    class="text-xs text-red-600 dark:text-red-400"
                  >
                    {{ fieldErrors[field.name] }}
                  </p>
                </div>

                <CaptchaWrapper
                  v-if="formDefinition.settings?.captcha_required"
                  ref="captchaRef"
                  action="contact"
                  @verified="onCaptchaVerified"
                />

                <Button
                  type="submit"
                  variant="primary"
                  size="lg"
                  class="w-full font-bold mt-auto"
                  :disabled="submitting || !canSubmit"
                  :loading="submitting"
                >
                  {{ submitting
                    ? t('pages.contact.submitting', 'Mengirim…')
                    : t('pages.contact.submitButton', 'Kirim permintaan') }}
                </Button>
              </form>

              <Alert
                v-else-if="formDefinition && !formDefinition.fields?.length"
                variant="error"
              >
                <p class="font-semibold">
                  {{ t('pages.contact.formNotConfiguredTitle', 'Formulir belum dikonfigurasi') }}
                </p>
                <p>{{ t('pages.contact.formNoValidFields', 'Formulir aktif tetapi tidak memiliki kolom yang valid untuk pengiriman publik.') }}</p>
              </Alert>
            </div>
          </div>
        </div>
      </template>
    </template>
  </div>
</template>

<script setup lang="ts">
import { computed, defineAsyncComponent, nextTick, onMounted, ref, watch } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import { Mail, MessageCircle, Loader2 } from 'lucide-vue-next';
import { useThemeI18n } from '@/modules/Layout/composables/useThemeI18n';
import { useTheme } from '@/modules/Layout/composables/useTheme';
import { useThemePageOverride } from '@/modules/Layout/composables/useThemePageOverride';
import { useThemeMotion } from '@/modules/Layout/composables/useThemeMotion';
import BlockRenderer from '@/modules/Layout/components/content-renderer/BlockRenderer.vue';
import ThemeSafeHtml from '@/modules/Layout/components/themes/ThemeSafeHtml.vue';
import PluginSlot from '@/shared/components/PluginSlot.vue';
import Breadcrumb from '../components/shared/Breadcrumb.vue';
import PageDisabled from '../components/shared/PageDisabled.vue';
import ContactMapPopover from '../components/shared/ContactMapPopover.vue';
import { Alert, Button, Checkbox, Input, Textarea, Label, Select } from '@/modules/Layout/views/themes/layung/ui';
import { useLayungIdentity } from '../composables/useLayungIdentity';
import { useThemeHashScroll } from '@/modules/Layout/composables/useThemeHashScroll';
import api, { getCsrfCookie } from '@/engine/api/client';
import { useToast } from '@/shared/composables/useToast';
import { logger } from '@/shared/utils/logger';
import type { CaptchaPayload } from '@/modules/Core/System/components/captcha/CaptchaWrapper.vue';
import {
  appendPayloadToFormData,
  buildPublicFormSubmitPayload,
  classifyFormRedirect,
  mapPublicFormValidationErrors,
  publicFormSubmitPath,
  publicFormTrackPath,
  type PublicFormDefinition,
  type PublicFormField,
} from '../composables/layungPublicForm';

const CaptchaWrapper = defineAsyncComponent(() => import('@/modules/Core/System/components/captcha/CaptchaWrapper.vue'));

const { t } = useThemeI18n('layung');
const { getSetting } = useTheme();
const router = useRouter();
const toast = useToast();
const {
  displayCompanyName,
  displayAddress,
  displayGarutAddress,
  displayStoreAddress,
  displayEmail,
  displayCsEmail,
  displaySalesEmail,
  displayBillingEmail,
  csWhatsAppUrl,
  nocLineWhatsAppUrl,
  salesWhatsAppUrl,
  serviceDeskWhatsAppUrl,
} = useLayungIdentity();
const { pageData, cmsBody, builderBlocks, hasBuilderBlocks, loading: pageLoading } = useThemePageOverride('contact');
const { motion } = useThemeMotion();

const infoCol = ref<HTMLElement>();
const formCol = ref<HTMLElement>();
let contactMotionScheduled = false;

function animateTo(target: HTMLElement, vars: Record<string, unknown>): void {
  motion.to(target, vars);
}

function onInfoItemEnter(event: Event): void {
  const el = event.currentTarget as HTMLElement | null;
  if (!el) return;
  animateTo(el, {
    x: 8,
    duration: 0.35,
    ease: 'power3.out',
  });
}

function onInfoItemLeave(event: Event): void {
  const el = event.currentTarget as HTMLElement | null;
  if (!el) return;
  animateTo(el, {
    x: 0,
    scale: 1,
    duration: 0.4,
    ease: 'power3.out',
  });
}

function onInfoItemPress(event: Event): void {
  const el = event.currentTarget as HTMLElement | null;
  if (!el) return;
  animateTo(el, {
    scale: 0.985,
    duration: 0.12,
    ease: 'power2.out',
  });
}

function onInfoItemRelease(event: Event): void {
  const el = event.currentTarget as HTMLElement | null;
  if (!el) return;
  animateTo(el, {
    scale: 1,
    duration: 0.2,
    ease: 'power2.out',
  });
}

function scheduleContactEnterMotion(): void {
  if (contactMotionScheduled || hasBuilderBlocks.value || pageLoading.value || !isEnabled.value) {
    return;
  }
  contactMotionScheduled = true;
  const run = () => {
    void nextTick(() => {
      if (infoCol.value) {
        motion.fromTo(
          infoCol.value,
          { opacity: 0, x: -50 },
          { opacity: 1, x: 0, duration: 0.8, ease: 'power3.out' },
        );
        const cards = infoCol.value.querySelectorAll('.contact-info-item');
        if (cards.length > 0) {
          motion.fromTo(
            cards,
            { opacity: 0, y: 30 },
            { opacity: 1, y: 0, duration: 0.6, stagger: 0.08, delay: 0.2, ease: 'power3.out' },
          );
        }
      }
      if (formCol.value) {
        motion.fromTo(
          formCol.value,
          { opacity: 0, x: 50 },
          { opacity: 1, x: 0, duration: 0.8, delay: 0.2, ease: 'power3.out' },
        );
      }
    });
  };
  if (typeof window !== 'undefined' && typeof window.requestIdleCallback === 'function') {
    window.requestIdleCallback(() => {
      requestAnimationFrame(() => requestAnimationFrame(run));
    }, { timeout: 1200 });
    return;
  }
  setTimeout(run, 0);
}

const whatsappLines = computed(() => [
  { key: 'cs', label: t('pages.contact.labelPhoneCs', 'Customer Service (CS)'), url: csWhatsAppUrl.value },
  { key: 'noc', label: t('pages.contact.labelPhoneNoc', 'Hotline NOC'), url: nocLineWhatsAppUrl.value },
  { key: 'sales', label: t('pages.contact.labelPhoneSales', 'Telepon Sales'), url: salesWhatsAppUrl.value },
  { key: 'desk', label: t('pages.contact.labelPhoneServiceDesk', 'Service Desk'), url: serviceDeskWhatsAppUrl.value },
].filter((line) => line.url));

const emailLines = computed(() => [
  { key: 'cs', label: t('pages.contact.labelEmailCs', 'Email CS'), address: displayCsEmail.value },
  { key: 'info', label: t('pages.contact.labelEmailInfo', 'Email umum'), address: displayEmail.value },
  { key: 'sales', label: t('pages.contact.labelEmailSales', 'Email Sales'), address: displaySalesEmail.value },
  { key: 'billing', label: t('pages.contact.labelEmailBilling', 'Email Billing'), address: displayBillingEmail.value },
].filter((item) => item.address));

const locationCards = computed(() => [
  {
    key: 'bandung',
    label: t('pages.contact.labelAddressBandung', 'Kantor Bandung'),
    address: displayAddress.value,
    useDirectLink: true,
  },
  {
    key: 'garut',
    label: t('pages.contact.labelAddressGarut', 'Kantor Garut'),
    address: displayGarutAddress.value,
    useDirectLink: false,
  },
  {
    key: 'store',
    label: t('pages.contact.labelStoreAddress', 'Toko offline'),
    address: displayStoreAddress.value,
    useDirectLink: false,
  },
].filter((place) => place.address.trim()));

const channelRows = computed(() => {
  const wa = whatsappLines.value;
  const emails = emailLines.value;
  const count = Math.max(wa.length, emails.length);
  return Array.from({ length: count }, (_, index) => ({
    wa: wa[index] ?? null,
    email: emails[index] ?? null,
  }));
});

useThemeHashScroll(72);

const isEnabled = computed(() => getSetting('enable_contact', true) !== false);
const disabledPageMessage = computed(() => {
  const raw = getSetting('disabled_page_message');
  return typeof raw === 'string' ? raw : '';
});

const contactFormSlug = computed(() => {
  const raw = getSetting('contact_form_slug');
  const s = typeof raw === 'string' ? raw.trim() : '';
  return s || 'contact';
});

const formLoading = ref(false);
const formDefinition = ref<PublicFormDefinition | null>(null);
const formLoadError = ref<string | null>(null);
const formValues = ref<Record<string, unknown>>({});
const formFiles = ref<Record<string, File | null>>({});
const fieldErrors = ref<Record<string, string>>({});
const submitting = ref(false);
const captchaPayload = ref<CaptchaPayload | null>(null);
const captchaRef = ref<{ refresh?: () => Promise<void> } | null>(null);
const startTracked = ref(false);

const canSubmit = computed(() => {
  const fd = formDefinition.value;
  if (fd?.settings?.captcha_required) {
    if (!(captchaPayload.value?.token && captchaPayload.value?.answer)) {
      return false;
    }
  }
  if (fd?.fields?.length) {
    for (const f of fd.fields) {
      if ((f.type === 'file' || f.type === 'image') && f.is_required) {
        if (!formFiles.value[f.name]) {
          return false;
        }
      }
    }
  }
  return true;
});

function selectOptions(field: PublicFormField): { label: string; value: string }[] {
  return normalizeOptions(field.options);
}

function normalizeOptions(raw: unknown): { label: string; value: string }[] {
  if (raw == null) {
    return [];
  }
  if (Array.isArray(raw)) {
    return raw.map((item) => {
      if (item && typeof item === 'object' && 'value' in item) {
        const o = item as Record<string, unknown>;
        return {
          label: String(o.label ?? o.value ?? ''),
          value: String(o.value ?? ''),
        };
      }
      const s = String(item);
      return { label: s, value: s };
    });
  }
  if (typeof raw === 'string') {
    try {
      const parsed: unknown = JSON.parse(raw);
      return normalizeOptions(parsed);
    } catch {
      return raw.split('\n').filter(Boolean).map((line) => {
        const parts = line.split('|').map((x) => x.trim());
        if (parts.length >= 2) {
          return { label: parts[0] ?? '', value: parts[1] ?? parts[0] ?? '' };
        }
        const s = parts[0] ?? '';
        return { label: s, value: s };
      });
    }
  }
  return [];
}

function initialValueForField(field: PublicFormField): unknown {
  if (field.type === 'boolean') {
    return false;
  }
  if (field.type === 'multiselect' || field.type === 'checkbox') {
    return [] as string[];
  }
  return '';
}

function initFormValuesFromDefinition(): void {
  const fd = formDefinition.value;
  if (!fd?.fields?.length) {
    formValues.value = {};
    formFiles.value = {};
    return;
  }
  const next: Record<string, unknown> = {};
  const files: Record<string, File | null> = {};
  for (const f of fd.fields) {
    if (f.type === 'file' || f.type === 'image') {
      files[f.name] = null;
      continue;
    }
    next[f.name] = initialValueForField(f);
  }
  formValues.value = next;
  formFiles.value = files;
}

function onFileInputChange(name: string, event: Event): void {
  const input = event.target as HTMLInputElement | null;
  const file = input?.files?.[0] ?? null;
  formFiles.value = { ...formFiles.value, [name]: file };
  if (fieldErrors.value[name]) {
    const { [name]: _, ...rest } = fieldErrors.value;
    fieldErrors.value = rest;
  }
}

function formHasFileFields(): boolean {
  const fd = formDefinition.value;
  return !!fd?.fields?.some((f) => f.type === 'file' || f.type === 'image');
}

function fieldErrorClass(name: string): string {
  return fieldErrors.value[name] ? 'border-red-500 focus:ring-red-500/30' : '';
}

function multiHas(name: string, value: string): boolean {
  const cur = formValues.value[name];
  return Array.isArray(cur) && cur.includes(value);
}

function toggleMulti(name: string, value: string, on: boolean): void {
  const cur = Array.isArray(formValues.value[name]) ? [...(formValues.value[name] as string[])] : [];
  if (on) {
    if (!cur.includes(value)) {
      cur.push(value);
    }
  } else {
    const i = cur.indexOf(value);
    if (i >= 0) {
      cur.splice(i, 1);
    }
  }
  formValues.value[name] = cur;
}

function onCaptchaVerified(payload: CaptchaPayload): void {
  captchaPayload.value = payload;
}

function mapValidationErrors(err: unknown): void {
  fieldErrors.value = axios.isAxiosError(err)
    ? mapPublicFormValidationErrors(err.response?.data)
    : {};
}

function buildSubmitPayload(): Record<string, unknown> {
  return buildPublicFormSubmitPayload(formDefinition.value?.fields, formValues.value);
}

async function trackStartOnce(): Promise<void> {
  if (startTracked.value || !formDefinition.value?.slug) {
    return;
  }
  startTracked.value = true;
  try {
    await api.post(publicFormTrackPath(formDefinition.value.slug), { event: 'start' });
  } catch {
    /* ignore */
  }
}

async function loadContactForm(): Promise<void> {
  formLoading.value = true;
  formLoadError.value = null;
  formDefinition.value = null;
  captchaPayload.value = null;
  fieldErrors.value = {};
  startTracked.value = false;

  try {
    const slug = contactFormSlug.value;
    const res = await api.get<PublicFormDefinition>(`/public/forms/${encodeURIComponent(slug)}`);
    formDefinition.value = res.data as PublicFormDefinition;
    initFormValuesFromDefinition();

    try {
      await api.post(publicFormTrackPath(slug), { event: 'view' });
    } catch {
      /* ignore */
    }
  } catch (e: unknown) {
    const status = axios.isAxiosError(e) ? e.response?.status : undefined;
    if (status === 404) {
      formLoadError.value = t('pages.contact.formNotPublished', 'Formulir kontak belum dipublikasikan atau tidak aktif.');
    } else {
      formLoadError.value = t('pages.contact.formLoadFailed', 'Tidak dapat memuat formulir. Silakan refresh halaman atau coba lagi nanti.');
    }
    logger.warning('[Contact] Form schema load failed:', e);
  } finally {
    formLoading.value = false;
  }
}

async function submitForm(): Promise<void> {
  if (!formDefinition.value?.slug || submitting.value || !canSubmit.value) {
    return;
  }
  fieldErrors.value = {};
  submitting.value = true;

  try {
    await getCsrfCookie();
    const body: Record<string, unknown> = buildSubmitPayload();

    if (formDefinition.value.settings?.captcha_required && captchaPayload.value) {
      body.captcha_token = captchaPayload.value.token;
      body.captcha_answer = captchaPayload.value.answer;
    }

    const url = publicFormSubmitPath(formDefinition.value.slug);
    const useMultipart = formHasFileFields();
    let res: { data: { submission_id?: string; redirect_url?: string } };

    if (useMultipart) {
      const fd = new FormData();
      appendPayloadToFormData(fd, body);
      for (const [name, file] of Object.entries(formFiles.value)) {
        if (file) {
          fd.append(name, file);
        }
      }
      res = await api.post<{ submission_id?: string; redirect_url?: string }>(url, fd);
    } else {
      res = await api.post<{ submission_id?: string; redirect_url?: string }>(url, body);
    }

    const inner = res.data as { submission_id?: string; redirect_url?: string };
    const msg =
      formDefinition.value.success_message?.trim()
      || t('pages.contact.formSuccess', 'Terima kasih, pesan Anda telah terkirim.');
    toast.success.action(msg);

    const redir = classifyFormRedirect(inner?.redirect_url);
    if (redir.kind === 'in-app') {
      await router.push(redir.url);
    } else if (redir.kind === 'absolute') {
      window.location.href = redir.url;
    }

    initFormValuesFromDefinition();
    captchaPayload.value = null;
    await captchaRef.value?.refresh?.();
  } catch (e: unknown) {
    mapValidationErrors(e);
    if (axios.isAxiosError(e) && e.response?.status === 422) {
      toast.error.validation(t('pages.contact.formValidationError', 'Periksa kembali isian formulir.'));
    } else if (axios.isAxiosError(e) && e.response?.data && typeof e.response.data === 'object') {
      const msg = (e.response.data as { message?: string }).message;
      toast.error.default(msg || t('pages.contact.sendFailed', 'Pengiriman gagal. Coba lagi.'));
    } else {
      toast.error.default(t('pages.contact.sendFailed', 'Pengiriman gagal. Coba lagi.'));
    }
    await captchaRef.value?.refresh?.();
    captchaPayload.value = null;
  } finally {
    submitting.value = false;
  }
}

function scheduleFormLoad(): void {
  if (!isEnabled.value) return;
  if (typeof window !== 'undefined' && typeof window.requestIdleCallback === 'function') {
    window.requestIdleCallback(() => {
      void loadContactForm();
    }, { timeout: 1200 });
    return;
  }
  setTimeout(() => {
    void loadContactForm();
  }, 0);
}

onMounted(() => {
  if (!isEnabled.value && getSetting('disabled_page_behavior', 'message') === 'redirect') {
    void router.push('/');
    return;
  }
  scheduleFormLoad();
  scheduleContactEnterMotion();
});

watch([pageLoading, isEnabled, hasBuilderBlocks], () => {
  scheduleContactEnterMotion();
});

watch(contactFormSlug, () => {
  if (isEnabled.value) {
    void loadContactForm();
  }
});
</script>
