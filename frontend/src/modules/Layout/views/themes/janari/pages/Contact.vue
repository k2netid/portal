<template>
  <div class="min-h-screen bg-background transition-colors duration-300 overflow-x-hidden">
    <!-- Main Content Area -->
    <div
      v-if="!isEnabled"
      class="flex-1"
    >
      <PageDisabled 
        :title="(pageTitle as string) || t('theme.janari.pages.contact.title')" 
        :message="(getSetting('disabled_page_message') as string)" 
      />
    </div>

    <div
      v-else-if="loading"
      class="flex flex-col items-center justify-center min-h-[60vh] gap-4"
    >
      <Loader2 class="w-10 h-10 animate-spin text-primary/50" />
      <p class="text-sm font-medium text-muted-foreground animate-pulse">
        Loading Contact Page...
      </p>
    </div>

    <template v-else>
      <!-- Visual Builder Content if page was customized in Builder -->
      <BlockRenderer
        v-if="hasBuilderBlocks"
        :blocks="builderBlocks"
        :context="{ post: pageData, site: { name: 'Jejakawan' } }"
      />

      <div v-else class="space-y-0">
        <section class="relative overflow-hidden border-y border-border/50 aspect-[16/9] md:aspect-[21/9] lg:aspect-[3/1] min-h-[300px]">
          <img
            v-if="contactHeroImage"
            :src="contactHeroImage"
            class="absolute inset-0 w-full h-full object-cover"
            fetchpriority="high"
            loading="eager"
            decoding="sync"
            alt="Contact Hero"
          />
          <div class="absolute inset-0 bg-background/70 dark:bg-background/75 backdrop-blur-[1px]" />
          <div class="container mx-auto px-6 py-16 md:py-20 relative z-10">
            <span class="text-primary font-bold tracking-wider uppercase text-sm mb-4 block">{{ pageTitle || t('theme.janari.pages.contact.sectionLabel') }}</span>
            <h1 class="text-4xl md:text-5xl font-extrabold tracking-tight mb-4">
              {{ pageTitle ? pageTitle : t('theme.janari.pages.contact.title') }}
            </h1>
            <p class="text-lg text-muted-foreground leading-relaxed max-w-3xl">
              {{ pageSubtitle }}
            </p>
          </div>
        </section>

        <PluginSlot name="after_hero" class="w-full" />

        <main class="container mx-auto px-6 py-20">
          <!-- Page Body Content if available -->
          <SafeHtml
            v-if="cmsBody"
            class="mb-16 Jejakawan-content max-w-4xl"
            :html="cmsBody"
            mode="Jejakawan"
          />

          <div class="grid grid-cols-1 lg:grid-cols-12 gap-16 items-start">
            <!-- Info Column -->
            <div
              ref="infoCol"
              class="lg:col-span-5 space-y-10"
            >
              <Card
                ref="contactCard"
                class="p-8 space-y-8 bg-card/50 backdrop-blur-sm border-border/60"
              >
                <div
                  class="contact-info-item flex items-start gap-6 p-4 -m-4 rounded-xl border border-transparent transition-all duration-500 hover:border-primary/60 hover:bg-primary/5 hover:shadow-[0_0_24px_hsl(var(--primary)/0.15)]"
                  @mouseenter="onInfoItemEnter"
                  @mouseleave="onInfoItemLeave"
                  @mousedown="onInfoItemPress"
                  @mouseup="onInfoItemRelease"
                >
                  <div class="w-12 h-12 rounded-2xl bg-primary/10 flex items-center justify-center text-primary shrink-0 shadow-sm">
                    <Mail class="w-6 h-6" />
                  </div>
                  <div class="flex-1 min-w-0">
                    <h3 class="font-bold text-xs uppercase tracking-widest text-muted-foreground mb-1">
                      Email Resmi
                    </h3>
                    <a
                      :href="'mailto:' + displayEmail"
                      class="text-lg font-bold hover:text-primary transition-colors"
                    >
                      {{ displayEmail }}
                    </a>
                  </div>
                </div>

                <div
                  class="contact-info-item flex items-start gap-6 p-4 -m-4 rounded-xl border border-transparent transition-all duration-500 hover:border-primary/60 hover:bg-primary/5 hover:shadow-[0_0_24px_hsl(var(--primary)/0.15)]"
                  @mouseenter="onInfoItemEnter"
                  @mouseleave="onInfoItemLeave"
                  @mousedown="onInfoItemPress"
                  @mouseup="onInfoItemRelease"
                >
                  <div class="w-12 h-12 rounded-2xl bg-primary/10 flex items-center justify-center text-primary shrink-0 shadow-sm">
                    <Phone class="w-6 h-6" />
                  </div>
                  <div>
                    <h3 class="font-bold text-xs uppercase tracking-widest text-muted-foreground mb-1">
                      Telepon / WA
                    </h3>
                    <a
                      v-if="phoneDialHref"
                      :href="phoneDialHref"
                      class="text-lg font-bold hover:text-primary transition-colors"
                    >
                      {{ displayPhone }}
                    </a>
                    <p
                      v-else-if="displayPhone"
                      class="text-lg font-bold"
                    >
                      {{ displayPhone }}
                    </p>
                    <p
                      v-else
                      class="text-sm text-muted-foreground"
                    >
                      Atur di Pengaturan → Identitas atau di Theme Customizer (bagian Contact).
                    </p>
                  </div>
                </div>

                <div
                  class="contact-info-item flex items-start gap-6 p-4 -m-4 rounded-xl border border-transparent transition-all duration-500 hover:border-primary/60 hover:bg-primary/5 hover:shadow-[0_0_24px_hsl(var(--primary)/0.15)]"
                  @mouseenter="onInfoItemEnter"
                  @mouseleave="onInfoItemLeave"
                  @mousedown="onInfoItemPress"
                  @mouseup="onInfoItemRelease"
                >
                  <div class="w-12 h-12 rounded-2xl bg-primary/10 flex items-center justify-center text-primary shrink-0 shadow-sm">
                    <MapPin class="w-6 h-6" />
                  </div>
                  <div>
                    <h3 class="font-bold text-xs uppercase tracking-widest text-muted-foreground mb-1">
                      Alamat
                    </h3>
                    <Popover
                      v-if="displayAddress && mapEnabled"
                      v-model:open="mapPopoverOpen"
                      @update:open="onMapPopoverOpenChange"
                    >
                      <PopoverTrigger as-child>
                        <button
                          type="button"
                          class="block w-full text-left text-base font-bold leading-relaxed text-balance hover:text-primary transition-colors"
                        >
                          {{ displayAddress }}
                        </button>
                      </PopoverTrigger>
                      <PopoverContent
                        side="top"
                        :side-offset="14"
                        align="start"
                        class="!max-w-none w-[min(92vw,44rem)] p-3.5 space-y-3 rounded-2xl !border-primary/65 !bg-background/96 dark:!bg-background/96 backdrop-blur-md !ring-1 !ring-primary/45 !shadow-[0_0_0_1px_hsl(var(--primary)/0.45),0_0_44px_hsl(var(--primary)/0.38),0_26px_72px_rgba(0,0,0,0.58)]"
                      >
                        <div class="rounded-xl overflow-hidden border border-primary/30 bg-muted/20 shadow-[0_0_24px_hsl(var(--primary)/0.12)]">
                          <iframe
                            v-if="mapIframeVisible"
                            :src="mapEmbedUrl"
                            class="w-full h-56"
                            loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"
                            title="Contact location map"
                          />
                        </div>
                        <p class="text-xs text-muted-foreground leading-relaxed">
                          {{ displayAddress }}
                        </p>
                        <div class="flex flex-wrap gap-2">
                          <Button
                            size="sm"
                            variant="outline"
                            @click="openMapExternal"
                          >
                            Buka Peta
                          </Button>
                          <Button
                            size="sm"
                            variant="outline"
                            @click="openMapDirections"
                          >
                            Petunjuk Arah
                          </Button>
                          <Button
                            size="sm"
                            variant="outline"
                            @click="copyLocationAddress"
                          >
                            Copy Alamat
                          </Button>
                          <Button
                            size="sm"
                            variant="outline"
                            @click="copyLocationCoordinates"
                          >
                            {{ t('theme.janari.pages.contact.copyCoords') }}
                          </Button>
                          <Button
                            size="sm"
                            variant="outline"
                            @click="shareLocation"
                          >
                            Share Lokasi
                          </Button>
                        </div>
                      </PopoverContent>
                    </Popover>
                    <p
                      v-else-if="displayAddress"
                      class="text-base font-bold leading-relaxed text-balance"
                    >
                      {{ displayAddress }}
                    </p>
                    <p
                      v-else
                      class="text-sm text-muted-foreground"
                    >
                      Atur di Pengaturan → Identitas atau di Theme Customizer (bagian Contact).
                    </p>
                  </div>
                </div>
              </Card>
            </div>

            <!-- Form Column -->
            <div
              ref="formCol"
              class="lg:col-span-7"
            >
              <Card class="p-8 md:p-12 shadow-xl border-border/40">
                <div class="mb-8">
                  <h2 class="text-2xl font-bold mb-2">
                    {{ formDefinition?.name || t('theme.janari.pages.contact.formTitle') }}
                  </h2>
                  <p class="text-muted-foreground">
                    {{ formDefinition?.description || t('theme.janari.pages.contact.formDescription') }}
                  </p>
                </div>

                <!-- Loading form schema -->
                <div
                  v-if="formLoading"
                  class="flex flex-col items-center justify-center py-16 gap-3"
                >
                  <Loader2 class="w-8 h-8 animate-spin text-primary/50" />
                  <p class="text-sm text-muted-foreground">
                    {{ t('theme.janari.pages.contact.formLoading') }}
                  </p>
                </div>

                <!-- Form unavailable -->
                <Alert
                  v-else-if="formLoadError"
                  variant="destructive"
                  class="mb-4"
                >
                  <AlertTitle>{{ t('theme.janari.pages.contact.formUnavailableTitle') }}</AlertTitle>
                  <AlertDescription>
                    {{ formLoadError }}
                  </AlertDescription>
                </Alert>

                <!-- Dynamic Jejakawan form -->
                <form
                  v-else-if="formDefinition && formDefinition.fields?.length"
                  class="space-y-6"
                  @submit.prevent="submitForm"
                >
                  <div
                    v-for="field in formDefinition.fields"
                    :key="field.name"
                    class="space-y-2"
                    @focusin="trackStartOnce"
                  >
                    <template v-if="field.type === 'file' || field.type === 'image'">
                      <Label
                        :html-for="'cf-' + field.name"
                        class="text-sm font-medium leading-none"
                      >
                        {{ field.label }}
                        <span
                          v-if="field.is_required"
                          class="text-destructive"
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
                        class="cursor-pointer bg-background/80 file:mr-3 file:rounded file:border-0 file:bg-primary/10 file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-primary"
                        :accept="field.type === 'image' ? 'image/*' : undefined"
                        :required="field.is_required"
                        :class="fieldErrorClass(field.name)"
                        @change="onFileInputChange(field.name, $event)"
                      />
                      <p
                        v-if="field.placeholder"
                        class="text-xs text-muted-foreground"
                      >
                        {{ field.placeholder }}
                      </p>
                    </template>

                    <template v-else>
                      <Label
                        :html-for="'cf-' + field.name"
                        class="text-sm font-medium leading-none"
                      >
                        {{ field.label }}
                        <span
                          v-if="field.is_required"
                          class="text-destructive"
                        >*</span>
                      </Label>
                      <p
                        v-if="field.help_text"
                        class="text-xs text-muted-foreground"
                      >
                        {{ field.help_text }}
                      </p>

                      <!-- text -->
                      <Input
                        v-if="field.type === 'text'"
                        :id="'cf-' + field.name"
                        v-model="formValues[field.name] as string"
                        type="text"
                        :placeholder="field.placeholder || ''"
                        :required="field.is_required"
                        :class="fieldErrorClass(field.name)"
                      />

                      <!-- email -->
                      <Input
                        v-else-if="field.type === 'email'"
                        :id="'cf-' + field.name"
                        v-model="formValues[field.name] as string"
                        type="email"
                        :placeholder="field.placeholder || ''"
                        :required="field.is_required"
                        :class="fieldErrorClass(field.name)"
                      />

                      <!-- url -->
                      <Input
                        v-else-if="field.type === 'url'"
                        :id="'cf-' + field.name"
                        v-model="formValues[field.name] as string"
                        type="url"
                        :placeholder="field.placeholder || ''"
                        :required="field.is_required"
                        :class="fieldErrorClass(field.name)"
                      />

                      <!-- number -->
                      <Input
                        v-else-if="field.type === 'number'"
                        :id="'cf-' + field.name"
                        v-model="formValues[field.name] as string"
                        type="number"
                        :placeholder="field.placeholder || ''"
                        :required="field.is_required"
                        :class="fieldErrorClass(field.name)"
                      />

                      <!-- textarea -->
                      <Textarea
                        v-else-if="field.type === 'textarea'"
                        :id="'cf-' + field.name"
                        v-model="formValues[field.name] as string"
                        :rows="4"
                        :placeholder="field.placeholder || ''"
                        :required="field.is_required"
                        :class="fieldErrorClass(field.name)"
                      />

                      <!-- date -->
                      <Input
                        v-else-if="field.type === 'date'"
                        :id="'cf-' + field.name"
                        v-model="formValues[field.name] as string"
                        type="date"
                        :required="field.is_required"
                        :class="fieldErrorClass(field.name)"
                      />

                      <!-- datetime -->
                      <Input
                        v-else-if="field.type === 'datetime'"
                        :id="'cf-' + field.name"
                        v-model="formValues[field.name] as string"
                        type="datetime-local"
                        :required="field.is_required"
                        :class="fieldErrorClass(field.name)"
                      />

                      <!-- boolean -->
                      <div
                        v-else-if="field.type === 'boolean'"
                        class="flex items-center gap-2 pt-1"
                      >
                        <Checkbox
                          :id="'cf-' + field.name"
                          :checked="!!formValues[field.name]"
                          @update:checked="(v: boolean | 'indeterminate') => { formValues[field.name] = v === true }"
                        />
                        <label
                          :html-for="'cf-' + field.name"
                          class="text-sm text-muted-foreground cursor-pointer"
                        >{{ field.placeholder || t('theme.janari.pages.contact.yes') }}</label>
                      </div>

                      <!-- select -->
                      <Select
                        v-else-if="field.type === 'select'"
                        :model-value="String(formValues[field.name] ?? '')"
                        @update:model-value="formValues[field.name] = $event"
                      >
                        <SelectTrigger :id="'cf-' + field.name" :class="fieldErrorClass(field.name)">
                          <SelectValue :placeholder="field.placeholder || t('theme.janari.pages.contact.select')" />
                        </SelectTrigger>
                        <SelectContent>
                          <SelectItem
                            v-for="opt in selectOptions(field)"
                            :key="opt.value"
                            :value="opt.value"
                          >
                            {{ opt.label }}
                          </SelectItem>
                        </SelectContent>
                      </Select>

                      <!-- radio -->
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
                            class="h-4 w-4 accent-primary"
                            :value="opt.value"
                            :name="'cf-' + field.name"
                            :required="field.is_required"
                          >
                          <span>{{ opt.label }}</span>
                        </label>
                      </div>

                      <!-- multiselect / checkbox group -->
                      <div
                        v-else-if="field.type === 'multiselect' || field.type === 'checkbox'"
                        class="space-y-2"
                      >
                        <label
                          v-for="opt in selectOptions(field)"
                          :key="opt.value"
                          class="flex items-center gap-2 text-sm cursor-pointer"
                        >
                          <Checkbox
                            :checked="multiHas(field.name, opt.value)"
                            @update:checked="(v: boolean | 'indeterminate') => toggleMulti(field.name, opt.value, v === true)"
                          />
                          <span>{{ opt.label }}</span>
                        </label>
                      </div>

                      <!-- json -->
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

                      <!-- fallback -->
                      <Input
                        v-else
                        :id="'cf-' + field.name"
                        v-model="formValues[field.name] as string"
                        type="text"
                        :placeholder="field.placeholder || ''"
                        :required="field.is_required"
                        :class="fieldErrorClass(field.name)"
                      />

                      <p
                        v-if="fieldErrors[field.name]"
                        class="text-xs text-destructive"
                      >
                        {{ fieldErrors[field.name] }}
                      </p>
                    </template>
                  </div>

                  <CaptchaWrapper
                    v-if="formDefinition.settings?.captcha_required"
                    ref="captchaRef"
                    action="contact"
                    @verified="onCaptchaVerified"
                  />

                  <Button
                    type="submit"
                    class="w-full sm:w-auto"
                    :disabled="submitting || !canSubmit"
                  >
                    <Loader2
                      v-if="submitting"
                      class="w-4 h-4 mr-2 animate-spin"
                    />
                    Kirim Pesan
                  </Button>
                </form>

                <!-- Empty form definition -->
                <Alert
                  v-else-if="formDefinition && !formDefinition.fields?.length"
                  variant="destructive"
                >
                  <AlertTitle>{{ t('theme.janari.pages.contact.formNotConfiguredTitle') }}</AlertTitle>
                  <AlertDescription>
                    {{ t('theme.janari.pages.contact.formNoValidFields') }}
                  </AlertDescription>
                </Alert>
              </Card>
            </div>
          </div>
        </main>
      </div>
    </template>
  </div>
</template>

<script setup lang="ts">
import { PluginSlot } from '@/shared/components'
import { logger } from '@/shared/utils/logger';
import { ref, onMounted, computed, nextTick, defineAsyncComponent, watchEffect, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import { normalizeLocaleCode } from '@/engine/i18n'
import { resolveLocalizedPageHtml } from '@/modules/Layout/utils/resolveLocalizedContent'
import { useHead } from '@unhead/vue'
import axios from 'axios'
import SafeHtml from '@/modules/Core/System/components/ui/SafeHtml.vue'
import BlockRenderer from '@/modules/Layout/components/content-renderer/BlockRenderer.vue'
import type { BlockInstance } from '@/types/builder'
import { useRouter } from 'vue-router'
import { useTheme } from '@/modules/Layout/composables/useTheme'
import PageDisabled from '../components/shared/PageDisabled.vue'
import api, { getCsrfCookie } from '@/engine/api/client'
import { publishingPaths } from '@/engine/api/paths'
import { usePublishingStore } from '@/modules/Publishing/stores/publishing'
import {
    Card,
    Button,
    Input,
    Textarea,
    Label,
    Checkbox,
    Alert,
    AlertTitle,
    AlertDescription,
    Popover,
    PopoverTrigger,
    PopoverContent,
    Select,
    SelectTrigger,
    SelectValue,
    SelectContent,
    SelectItem,
} from '@/modules/Layout/views/themes/janari/ui'
import { Mail, Phone, MapPin, Loader2 } from 'lucide-vue-next';
import { useToast } from '@/shared/composables/useToast'
import { useThemeMotion } from '@/modules/Layout/composables/useThemeMotion'
import { useJanariIdentity } from '@/modules/Layout/views/themes/janari/composables/useJanariIdentity'
import type { CaptchaPayload } from '@/modules/Core/System/components/captcha/CaptchaWrapper.vue'

const CaptchaWrapper = defineAsyncComponent(() => import('@/modules/Core/System/components/captcha/CaptchaWrapper.vue'))

import type { Content } from '@/modules/Publishing/types/content'

/** Jejakawan page payload for optional intro HTML above the contact form. */
type PageData = Pick<Content, 'id' | 'title' | 'slug' | 'body' | 'type' | 'meta'> & { blocks?: unknown[] };

interface PublicFormField {
    name: string;
    label: string;
    type: string;
    placeholder?: string | null;
    help_text?: string | null;
    options: unknown;
    is_required: boolean;
}

interface PublicFormDefinition {
    id: string;
    slug: string;
    name: string;
    description?: string | null;
    success_message?: string | null;
    redirect_url?: string;
    settings: {
        captcha_required: boolean;
        email_notifications?: boolean;
    };
    fields: PublicFormField[];
}

const loading = ref(true)
const formLoading = ref(false)
const formDefinition = ref<PublicFormDefinition | null>(null)
const formLoadError = ref<string | null>(null)
const formValues = ref<Record<string, unknown>>({})
const formFiles = ref<Record<string, File | null>>({})
const fieldErrors = ref<Record<string, string>>({})
const submitting = ref(false)
const captchaPayload = ref<CaptchaPayload | null>(null)
const captchaRef = ref<{ refresh?: () => Promise<void> } | null>(null)
const startTracked = ref(false)
const pageData = ref<PageData | null>(null)
const mapPopoverOpen = ref(false)
const mapIframeVisible = ref(false)
const { getSetting } = useTheme()
const router = useRouter()
const publishingStore = usePublishingStore()
const toast = useToast()
const { displayEmail, displayPhone, displayAddress, phoneDialHref } = useJanariIdentity()
const { t, locale } = useI18n({ useScope: 'global' })
const cmsBody = computed(() => resolveLocalizedPageHtml(pageData.value, locale.value))

const builderBlocks = computed<BlockInstance[]>(() => {
  const meta = pageData.value?.meta as Record<string, unknown> | undefined
  const blocks = meta?.builder_blocks || pageData.value?.blocks
  if (Array.isArray(blocks)) {
    return blocks as BlockInstance[]
  }
  return []
})
const hasBuilderBlocks = computed(() => builderBlocks.value.length > 0)

const loadContactCms = async () => {
    try {
        const r = await api.get(publishingPaths.publicContent('contact'), {
            params: { locale: normalizeLocaleCode(locale.value) },
        })
        pageData.value = r.data as PageData
    } catch (e) {
        logger.warning('[Contact] Content fetch skipped:', e)
    }
}
const { motion } = useThemeMotion()

function animateTo(target: HTMLElement, vars: Record<string, unknown>): void {
    motion.to(target, vars)
}

const isEnabled = computed(() => getSetting('enable_contact', true))
const behavior = computed(() => getSetting('disabled_page_behavior', 'message'))
const pageTitle = computed(() => getSetting('page_contact_title') as string)
const pageSubtitle = computed(() =>
    (getSetting('page_contact_subtitle') as string) ||
    t('theme.janari.pages.contact.introQuestion')
)
const contactHeroImage = computed(() => {
    const raw = getSetting('page_contact_hero')
    return typeof raw === 'string' ? raw.trim() : ''
})

watchEffect(() => {
    if (contactHeroImage.value) {
        useHead({
            link: [
                {
                    rel: 'preload',
                    as: 'image',
                    href: contactHeroImage.value,
                    fetchpriority: 'high'
                }
            ]
        })
    }
})

const mapEnabled = computed(() => getSetting('contact_map_enabled', true) !== false)
const mapSource = computed(() => String(getSetting('contact_map_source', 'current_location') || 'current_location'))
const mapZoom = computed(() => {
    const raw = Number(getSetting('contact_map_zoom', 15))
    if (!Number.isFinite(raw)) return 15
    return Math.min(20, Math.max(10, Math.round(raw)))
})
const mapDirectLink = computed(() => {
    const raw = String(getSetting('contact_map_link', '') || '').trim()
    if (!raw) return ''
    if (/^https?:\/\//i.test(raw)) return raw
    return ''
})
const mapQuery = computed(() => {
    if (mapDirectLink.value) {
        try {
            const url = new URL(mapDirectLink.value)
            const q = url.searchParams.get('query') || url.searchParams.get('q') || url.searchParams.get('destination')
            if (q) return q
            const atMatch = url.pathname.match(/@(-?\d+(?:\.\d+)?),(-?\d+(?:\.\d+)?)/)
            if (atMatch) return `${atMatch[1]},${atMatch[2]}`
        } catch {
            /* ignore parse error */
        }
    }
    return String(displayAddress.value || '').trim()
})
const mapEmbedUrl = computed(() => {
    const q = encodeURIComponent(mapQuery.value || String(displayAddress.value || ''))
    const z = mapZoom.value
    return `https://www.google.com/maps?q=${q}&z=${z}&output=embed`
})
const mapExternalUrl = computed(() => {
    if (mapDirectLink.value) return mapDirectLink.value
    const q = encodeURIComponent(mapQuery.value || String(displayAddress.value || ''))
    return `https://www.google.com/maps/search/?api=1&query=${q}`
})
const mapDirectionsUrl = computed(() => {
    if (mapSource.value === 'link' && mapDirectLink.value) return mapDirectLink.value
    const destination = encodeURIComponent(mapQuery.value || String(displayAddress.value || ''))
    return `https://www.google.com/maps/dir/?api=1&destination=${destination}`
})

const contactFormSlug = computed(() => {
    const raw = getSetting('contact_form_slug')
    const s = typeof raw === 'string' ? raw.trim() : ''
    return s || 'contact'
})

const canSubmit = computed(() => {
    const fd = formDefinition.value
    if (fd?.settings?.captcha_required) {
        if (!(captchaPayload.value?.token && captchaPayload.value?.answer)) {
            return false
        }
    }
    if (fd?.fields?.length) {
        for (const f of fd.fields) {
            if ((f.type === 'file' || f.type === 'image') && f.is_required) {
                if (!formFiles.value[f.name]) {
                    return false
                }
            }
        }
    }
    return true
})

const infoCol = ref<HTMLElement>()
const contactCard = ref<HTMLElement>()
const formCol = ref<HTMLElement>()

function onInfoItemEnter(event: Event): void {
    const el = event.currentTarget as HTMLElement | null
    if (!el) return
    animateTo(el, {
        x: 10,
        duration: 0.35,
        ease: 'power3.out',
    })
}

function onInfoItemLeave(event: Event): void {
    const el = event.currentTarget as HTMLElement | null
    if (!el) return
    animateTo(el, {
        x: 0,
        scale: 1,
        duration: 0.4,
        ease: 'power3.out',
    })
}

function onInfoItemPress(event: Event): void {
    const el = event.currentTarget as HTMLElement | null
    if (!el) return
    animateTo(el, {
        scale: 0.985,
        duration: 0.12,
        ease: 'power2.out',
    })
}

function onInfoItemRelease(event: Event): void {
    const el = event.currentTarget as HTMLElement | null
    if (!el) return
    animateTo(el, {
        scale: 1,
        duration: 0.2,
        ease: 'power2.out',
    })
}

async function copyToClipboard(value: string, okMessage: string): Promise<void> {
    if (!value) return
    try {
        await navigator.clipboard.writeText(value)
        toast.success.action(okMessage)
    } catch {
        toast.error.default(t('theme.janari.pages.contact.copyFailed'))
    }
}

function openMapExternal(): void {
    const url = mapExternalUrl.value
    if (!url) return
    window.open(url, '_blank', 'noopener,noreferrer')
}

function openMapDirections(): void {
    const url = mapDirectionsUrl.value
    if (!url) return
    window.open(url, '_blank', 'noopener,noreferrer')
}

function onMapPopoverOpenChange(open: boolean): void {
    mapPopoverOpen.value = open
    if (!open) return

    if (typeof window !== 'undefined' && typeof window.requestIdleCallback === 'function') {
        window.requestIdleCallback(() => {
            mapIframeVisible.value = true
        }, { timeout: 800 })
        return
    }

    requestAnimationFrame(() => {
        mapIframeVisible.value = true
    })
}

async function copyLocationAddress(): Promise<void> {
    await copyToClipboard(String(displayAddress.value || ''), t('theme.janari.pages.contact.addressCopied'))
}

async function copyLocationCoordinates(): Promise<void> {
    const m = mapQuery.value.match(/(-?\d+(?:\.\d+)?)\s*,\s*(-?\d+(?:\.\d+)?)/)
    const coords = m ? `${m[1]},${m[2]}` : ''
    if (!coords) {
        toast.error.default(t('theme.janari.pages.contact.coordsUnavailable'))
        return
    }
    await copyToClipboard(coords, t('theme.janari.pages.contact.coordsCopied'))
}

async function shareLocation(): Promise<void> {
    const title = String(pageTitle.value || 'Lokasi')
    const text = String(displayAddress.value || t('theme.janari.pages.contact.siteLocationDefault'))
    const url = mapExternalUrl.value
    if ((navigator as Navigator & { share?: (data: { title?: string; text?: string; url?: string }) => Promise<void> }).share) {
        try {
            await (navigator as Navigator & { share: (data: { title?: string; text?: string; url?: string }) => Promise<void> }).share({
                title,
                text,
                url,
            })
            return
        } catch {
            /* user cancelled or browser blocked */
        }
    }
    await copyToClipboard(`${text}\n${url}`, t('theme.janari.pages.contact.locationLinkCopied'))
}

function selectOptions(field: PublicFormField): { label: string; value: string }[] {
    return normalizeOptions(field.options)
}

function normalizeOptions(raw: unknown): { label: string; value: string }[] {
    if (raw == null) {
        return []
    }
    if (Array.isArray(raw)) {
        return raw.map((item) => {
            if (item && typeof item === 'object' && 'value' in item) {
                const o = item as Record<string, unknown>
                return {
                    label: String(o.label ?? o.value ?? ''),
                    value: String(o.value ?? ''),
                }
            }
            const s = String(item)
            return { label: s, value: s }
        })
    }
    if (typeof raw === 'string') {
        try {
            const parsed: unknown = JSON.parse(raw)
            return normalizeOptions(parsed)
        } catch {
            return raw.split('\n').filter(Boolean).map((line) => {
                const parts = line.split('|').map((x) => x.trim())
                if (parts.length >= 2) {
                    return { label: parts[0] ?? '', value: parts[1] ?? parts[0] ?? '' }
                }
                const s = parts[0] ?? ''
                return { label: s, value: s }
            })
        }
    }
    return []
}

function initialValueForField(field: PublicFormField): unknown {
    if (field.type === 'boolean') {
        return false
    }
    if (field.type === 'multiselect' || field.type === 'checkbox') {
        return [] as string[]
    }
    return ''
}

function initFormValuesFromDefinition(): void {
    const fd = formDefinition.value
    if (!fd?.fields?.length) {
        formValues.value = {}
        formFiles.value = {}
        return
    }
    const next: Record<string, unknown> = {}
    const files: Record<string, File | null> = {}
    for (const f of fd.fields) {
        if (f.type === 'file' || f.type === 'image') {
            files[f.name] = null
            continue
        }
        next[f.name] = initialValueForField(f)
    }
    formValues.value = next
    formFiles.value = files
}

function onFileInputChange(name: string, event: Event): void {
    const input = event.target as HTMLInputElement | null
    const file = input?.files?.[0] ?? null
    formFiles.value = { ...formFiles.value, [name]: file }
    if (fieldErrors.value[name]) {
        const { [name]: _, ...rest } = fieldErrors.value
        fieldErrors.value = rest
    }
}

function formHasFileFields(): boolean {
    const fd = formDefinition.value
    return !!fd?.fields?.some((f) => f.type === 'file' || f.type === 'image')
}

function appendPayloadToFormData(fd: FormData, body: Record<string, unknown>): void {
    for (const [key, val] of Object.entries(body)) {
        if (val === null || val === undefined) {
            continue
        }
        if (Array.isArray(val)) {
            for (const item of val) {
                fd.append(`${key}[]`, String(item))
            }
            continue
        }
        if (typeof val === 'boolean') {
            fd.append(key, val ? '1' : '0')
            continue
        }
        if (typeof val === 'number') {
            fd.append(key, String(val))
            continue
        }
        fd.append(key, String(val))
    }
}

function fieldErrorClass(name: string): string {
    return fieldErrors.value[name] ? 'border-destructive focus-visible:ring-destructive/30' : ''
}

function multiHas(name: string, value: string): boolean {
    const cur = formValues.value[name]
    return Array.isArray(cur) && cur.includes(value)
}

function toggleMulti(name: string, value: string, on: boolean): void {
    const cur = Array.isArray(formValues.value[name]) ? [...(formValues.value[name] as string[])] : []
    if (on) {
        if (!cur.includes(value)) {
            cur.push(value)
        }
    } else {
        const i = cur.indexOf(value)
        if (i >= 0) {
            cur.splice(i, 1)
        }
    }
    formValues.value[name] = cur
}

function onCaptchaVerified(payload: CaptchaPayload): void {
    captchaPayload.value = payload
}

function mapValidationErrors(err: unknown): void {
    fieldErrors.value = {}
    if (!axios.isAxiosError(err)) {
        return
    }
    const data = err.response?.data as { errors?: Record<string, string[] | string> } | undefined
    const errs = data?.errors
    if (!errs || typeof errs !== 'object') {
        return
    }
    const out: Record<string, string> = {}
    for (const [k, v] of Object.entries(errs)) {
        if (Array.isArray(v) && v[0]) {
            out[k] = String(v[0])
        } else if (typeof v === 'string') {
            out[k] = v
        }
    }
    fieldErrors.value = out
}

function buildSubmitPayload(): Record<string, unknown> {
    const out: Record<string, unknown> = {}
    const fd = formDefinition.value
    if (!fd?.fields?.length) {
        return out
    }
    for (const field of fd.fields) {
        if (field.type === 'file' || field.type === 'image') {
            continue
        }
        const key = field.name
        let val = formValues.value[key]

        if (field.type === 'multiselect' || field.type === 'checkbox') {
            out[key] = Array.isArray(val) ? val : []
            continue
        }
        if (field.type === 'boolean') {
            out[key] = !!val
            continue
        }

        const isEmpty = val === '' || val === null || val === undefined
        if (isEmpty) {
            if (field.is_required) {
                out[key] = ''
            }
            continue
        }

        if (field.type === 'number' && typeof val === 'string') {
            const n = Number(val)
            out[key] = Number.isNaN(n) ? val : n
            continue
        }

        out[key] = val
    }
    return out
}

async function trackStartOnce(): Promise<void> {
    if (startTracked.value || !formDefinition.value?.slug) {
        return
    }
    startTracked.value = true
    try {
        await api.post(`/public/forms/${encodeURIComponent(formDefinition.value.slug)}/track`, { event: 'start' })
    } catch {
        /* ignore */
    }
}

async function loadContactForm(): Promise<void> {
    formLoading.value = true
    formLoadError.value = null
    formDefinition.value = null
    captchaPayload.value = null
    fieldErrors.value = {}

    try {
        const slug = contactFormSlug.value
        const res = await api.get<PublicFormDefinition>(`/public/forms/${encodeURIComponent(slug)}`)
        formDefinition.value = res.data as PublicFormDefinition
        initFormValuesFromDefinition()

        try {
            await api.post(`/public/forms/${encodeURIComponent(slug)}/track`, { event: 'view' })
        } catch {
            /* ignore */
        }
    } catch (e: unknown) {
        const status = axios.isAxiosError(e) ? e.response?.status : undefined
        if (status === 404) {
            formLoadError.value =
                t('theme.janari.pages.contact.formNotPublished')
        } else {
            formLoadError.value = t('theme.janari.pages.contact.formLoadFailed')
        }
        logger.warning('[Contact] Form schema load failed:', e)
    } finally {
        formLoading.value = false
    }
}

async function submitForm(): Promise<void> {
    if (!formDefinition.value?.slug || submitting.value || !canSubmit.value) {
        return
    }
    fieldErrors.value = {}
    submitting.value = true

    try {
        await getCsrfCookie()
        const body: Record<string, unknown> = buildSubmitPayload()

        if (formDefinition.value.settings?.captcha_required && captchaPayload.value) {
            body.captcha_token = captchaPayload.value.token
            body.captcha_answer = captchaPayload.value.answer
        }

        const url = `/public/forms/${encodeURIComponent(formDefinition.value.slug)}/submit`
        const useMultipart = formHasFileFields()
        let res: { data: { submission_id?: string; redirect_url?: string } }

        if (useMultipart) {
            const fd = new FormData()
            appendPayloadToFormData(fd, body)
            for (const [name, file] of Object.entries(formFiles.value)) {
                if (file) {
                    fd.append(name, file)
                }
            }
            res = await api.post<{ submission_id?: string; redirect_url?: string }>(url, fd)
        } else {
            res = await api.post<{ submission_id?: string; redirect_url?: string }>(url, body)
        }

        const inner = res.data as { submission_id?: string; redirect_url?: string }
        const msg =
            formDefinition.value.success_message?.trim() ||
            t('theme.janari.pages.contact.formSuccess')
        toast.success.action(msg)

        const redir = inner?.redirect_url
        if (typeof redir === 'string' && redir.trim() !== '') {
            const u = redir.trim()
            if (u.startsWith('/') && !u.startsWith('//')) {
                await router.push(u)
            } else if (/^https?:\/\//i.test(u)) {
                window.location.href = u
            }
        }

        initFormValuesFromDefinition()
        captchaPayload.value = null
        await captchaRef.value?.refresh?.()
    } catch (e: unknown) {
        mapValidationErrors(e)
        if (axios.isAxiosError(e) && e.response?.status === 422) {
            toast.error.validation(t('theme.janari.pages.contact.formValidationError'))
        } else if (axios.isAxiosError(e) && e.response?.data && typeof e.response.data === 'object') {
            const msg = (e.response.data as { message?: string }).message
            toast.error.default(msg || t('theme.janari.pages.contact.sendFailed'))
        } else {
            toast.error.default(t('theme.janari.pages.contact.sendFailed'))
        }
        await captchaRef.value?.refresh?.()
        captchaPayload.value = null
    } finally {
        submitting.value = false
    }
}

function scheduleContactEnterMotion() {
    const run = () => {
        void nextTick(() => {
            if (infoCol.value) {
                motion.fromTo(
                    infoCol.value,
                    { opacity: 0, x: -50 },
                    { opacity: 1, x: 0, duration: 0.8, ease: 'power3.out' },
                )
            }
            if (contactCard.value) {
                const cardEl = (contactCard.value as unknown as { $el: HTMLElement }).$el || contactCard.value
                const children = (cardEl as HTMLElement).querySelectorAll(':scope > div')
                if (children.length > 0) {
                    motion.fromTo(
                        children,
                        { opacity: 0, y: 30 },
                        { opacity: 1, y: 0, duration: 0.6, stagger: 0.12, delay: 0.3, ease: 'power3.out' },
                    )
                }
            }
            if (formCol.value) {
                motion.fromTo(
                    formCol.value,
                    { opacity: 0, x: 50 },
                    { opacity: 1, x: 0, duration: 0.8, delay: 0.2, ease: 'power3.out' },
                )
            }
        })
    }
    if (typeof window !== 'undefined' && typeof window.requestIdleCallback === 'function') {
        window.requestIdleCallback(() => {
            requestAnimationFrame(() => requestAnimationFrame(run))
        }, { timeout: 1200 })
        return
    }
    requestAnimationFrame(() => requestAnimationFrame(run))
}

onMounted(async () => {
    if (!isEnabled.value && behavior.value === 'redirect') {
        router.push('/')
        return
    }

    if (!isEnabled.value) {
        loading.value = false
        return
    }

    const bootstrap: Promise<unknown>[] = [loadContactCms()]
    if (!publishingStore.publicSettingsLoaded) {
        bootstrap.unshift(publishingStore.fetchPublicSettings())
    }

    try {
        await Promise.all(bootstrap)
    } catch (error) {
        logger.warning('[Contact] Init error:', error)
    } finally {
        loading.value = false
        scheduleContactEnterMotion()

        // Defer dynamic form schema load until page is visible/interactive.
        if (typeof window !== 'undefined' && typeof window.requestIdleCallback === 'function') {
            window.requestIdleCallback(() => {
                void loadContactForm()
            }, { timeout: 1200 })
        } else {
            setTimeout(() => {
                void loadContactForm()
            }, 0)
        }
    }
})

watch(locale, () => {
    void loadContactCms()
})
</script>
