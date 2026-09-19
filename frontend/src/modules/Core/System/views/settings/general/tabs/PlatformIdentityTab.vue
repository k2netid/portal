<template>
  <div class="space-y-4">
    <SettingGroup
      v-for="group in generalSettingsGrouped"
      :key="group.id"
      :title="group.title"
      :description="group.description"
      :icon="(group.icon as any)"
      :color="group.color"
      :default-expanded="group.defaultExpanded"
    >
      <!-- Site Module Inactive Notice -->
      <div
        v-if="!isSiteActive"
        class="mb-4 rounded-xl border border-amber-500/25 bg-amber-500/10 p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs text-amber-900 dark:text-amber-200"
      >
        <div class="flex items-start gap-2.5">
          <PowerOff class="h-4 w-4 shrink-0 mt-0.5 text-amber-600 dark:text-amber-400" />
          <div class="space-y-0.5">
            <span class="font-semibold text-sm">{{ $t('system.settings.site_module_inactive_title') }}</span>
            <p class="text-amber-800/90 dark:text-amber-300/90 leading-relaxed">
              {{ $t('system.settings.site_module_inactive_notice') }}
            </p>
          </div>
        </div>
        <router-link
          to="/dash/settings/extensions?tab=audience"
          class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-amber-600 hover:bg-amber-700 text-white font-medium text-xs shrink-0 self-start sm:self-center transition-colors"
        >
          <ExternalLink class="h-3.5 w-3.5" />
          {{ $t('system.settings.site_module_inactive_cta') }}
        </router-link>
      </div>

      <!-- Incomplete Dependencies Warning (Layout / Publishing) -->
      <div
        v-else-if="isDependenciesIncomplete"
        class="mb-4 rounded-xl border border-rose-500/25 bg-rose-500/10 p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs text-rose-900 dark:text-rose-200"
      >
        <div class="flex items-start gap-2.5">
          <AlertTriangle class="h-4 w-4 shrink-0 mt-0.5 text-rose-600 dark:text-rose-400" />
          <div class="space-y-0.5">
            <span class="font-semibold text-sm">{{ $t('system.settings.site_module_deps_warning_title') }}</span>
            <p class="text-rose-800/90 dark:text-rose-300/90 leading-relaxed">
              {{ $t('system.settings.site_module_deps_warning_notice') }}
            </p>
          </div>
        </div>
        <router-link
          to="/dash/settings/extensions?tab=cms"
          class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-rose-600 hover:bg-rose-700 text-white font-medium text-xs shrink-0 self-start sm:self-center transition-colors"
        >
          <ExternalLink class="h-3.5 w-3.5" />
          {{ $t('system.settings.site_module_deps_warning_cta') }}
        </router-link>
      </div>

      <!-- Active Brand Sync Notice -->
      <div
        v-if="isBrandSyncActive && isSiteActive"
        class="mb-4 rounded-xl border border-sky-500/25 bg-sky-500/10 p-3.5 flex items-start gap-2.5 text-xs text-sky-900 dark:text-sky-200"
      >
        <Info class="h-4 w-4 shrink-0 mt-0.5 text-sky-600 dark:text-sky-400" />
        <div class="space-y-0.5">
          <span class="font-semibold">{{ $t('system.settings.labels.brand_sync_site_identity') }}</span>
          <p class="text-sky-800/80 dark:text-sky-300/80">
            {{ $t('system.settings.brand_sync_active_notice') }}
          </p>
        </div>
      </div>

      <template
        v-for="setting in group.settings"
        :key="setting.id"
      >
        <SettingField
          :model-value="(formData[setting.key] as any)"
          :field-key="setting.key"
          :label="$t('system.settings.labels.' + setting.key)"
          :description="getFieldDescription(setting.key)"
          :type="setting.type"
          :enabled-text="$t('system.settings.enabled')"
          :disabled-text="$t('system.settings.disabled')"
          :error="errors?.[setting.key]"
          :disabled="isFieldDisabled(setting.key)"
          :readonly="isFieldDisabled(setting.key)"
          @update:model-value="(value) => updateField(setting.key, value)"
        />

        <!-- GPS Coordinates Geolocation Helper -->
        <div
          v-if="setting.key === 'contact_coordinates'"
          class="mt-2.5 p-3.5 rounded-xl border border-border/80 bg-muted/20 space-y-2.5"
        >
          <div class="flex flex-wrap items-center gap-2">
            <button
              type="button"
              class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-primary/40 bg-primary/10 hover:bg-primary/20 text-primary font-semibold text-xs transition-colors"
              :disabled="geoLoading"
              @click="getCurrentGpsCoordinates"
            >
              <Navigation class="w-3.5 h-3.5" :class="{ 'animate-spin': geoLoading }" />
              {{ geoLoading ? $t('system.settings.contact_geo_locating', 'Mengambil GPS...') : $t('system.settings.contact_geo_get_btn', 'Ambil Lokasi GPS Saat Ini') }}
            </button>
            <a
              v-if="hasValidCoordinates"
              :href="`https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(String(formData.contact_coordinates || ''))}`"
              target="_blank"
              rel="noopener noreferrer"
              class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-border bg-background hover:bg-muted text-foreground font-semibold text-xs transition-colors"
            >
              <ExternalLink class="w-3.5 h-3.5 text-muted-foreground" />
              {{ $t('system.settings.contact_geo_test_btn', 'Cek di Google Maps') }}
            </a>
          </div>
          <p
            v-if="geoSuccessMessage"
            class="text-xs text-emerald-600 dark:text-emerald-400 font-medium"
          >
            {{ geoSuccessMessage }}
          </p>
          <p
            v-if="geoErrorMessage"
            class="text-xs text-rose-600 dark:text-rose-400 font-medium"
          >
            {{ geoErrorMessage }}
          </p>
          <p class="text-[11px] text-muted-foreground leading-relaxed">
            {{ $t('system.settings.contact_geo_hint', 'Format: latitude, longitude (contoh: -7.7151, 108.4731). Digunakan otomatis oleh semua tema (Sarangenge, Layung, Sareupna, Janari) agar pin peta terkunci tepat di atas gedung/gerbang dan tidak meleset ke laut.') }}
          </p>
        </div>
      </template>
    </SettingGroup>
  </div>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import SettingGroup from '@/modules/Core/System/components/settings/SettingGroup.vue'
import SettingField from '@/modules/Core/System/components/settings/SettingField.vue'
import { Globe, Info, PowerOff, AlertTriangle, ExternalLink, MapPin, Navigation } from 'lucide-vue-next'
import { useSystemStore } from '@/modules/Core/System/stores/system'

interface Setting {
    id: string | string;
    key: string;
    value: unknown;
    type: string;
    group: string;
}

interface Props {
    settings: Setting[];
    formData: Record<string, unknown>;
    errors?: Record<string, string[]>;
}

const { t } = useI18n()
const props = defineProps<Props>()

const emit = defineEmits<{
    (e: 'update:formData', value: Record<string, unknown>): void;
}>()

const updateField = (key: string, value: unknown) => {
    emit('update:formData', { ...props.formData, [key]: value })
}

const systemStore = useSystemStore()
const isSiteActive = computed(() => systemStore.activeExtensions?.includes('site') ?? false)
const isLayoutActive = computed(() => systemStore.activeExtensions?.includes('layout') ?? false)
const isPublishingActive = computed(() => systemStore.activeExtensions?.includes('publishing') ?? false)
const isDependenciesIncomplete = computed(() => isSiteActive.value && (!isLayoutActive.value || !isPublishingActive.value))
const isBrandSyncActive = computed(() => Boolean(props.formData.brand_sync_site_identity ?? systemStore.getSetting('brand_sync_site_identity')) && systemStore.appIdentity.has_white_label)

const geoLoading = ref(false)
const geoSuccessMessage = ref('')
const geoErrorMessage = ref('')

const hasValidCoordinates = computed(() => {
  const c = String(props.formData.contact_coordinates || '').trim()
  return /(-?\d+(?:\.\d+)?)\s*,\s*(-?\d+(?:\.\d+)?)/.test(c)
})

async function getCurrentGpsCoordinates(): Promise<void> {
  if (geoLoading.value) return
  if (typeof navigator === 'undefined' || !navigator.geolocation) {
    geoErrorMessage.value = 'Browser Anda tidak mendukung Geolocation API.'
    return
  }
  geoLoading.value = true
  geoSuccessMessage.value = ''
  geoErrorMessage.value = ''
  try {
    const pos = await new Promise<GeolocationPosition>((resolve, reject) => {
      navigator.geolocation.getCurrentPosition(resolve, reject, {
        enableHighAccuracy: true,
        timeout: 15000,
        maximumAge: 60000,
      })
    })
    const lat = Number(pos.coords.latitude).toFixed(6)
    const lon = Number(pos.coords.longitude).toFixed(6)
    const coords = `${lat}, ${lon}`
    updateField('contact_coordinates', coords)
    geoSuccessMessage.value = `Koordinat GPS (${coords}) berhasil didapatkan dari perangkat Anda.`
  } catch (err: unknown) {
    const e = err as GeolocationPositionError
    if (e?.code === 1) {
      geoErrorMessage.value = 'Izin lokasi GPS ditolak oleh pengguna/browser.'
    } else if (e?.code === 2) {
      geoErrorMessage.value = 'Posisi GPS tidak dapat ditentukan.'
    } else if (e?.code === 3) {
      geoErrorMessage.value = 'Waktu permintaan lokasi GPS habis (timeout).'
    } else {
      geoErrorMessage.value = 'Gagal mengambil koordinat GPS perangkat.'
    }
  } finally {
    geoLoading.value = false
  }
}

const isFieldDisabled = (key: string) => {
    if (!isSiteActive.value) return true
    if (isBrandSyncActive.value && (key === 'site_logo' || key === 'site_favicon')) {
        return true
    }
    return false
}

const getFieldDescription = (key: string) => {
    const base = t('system.settings.descriptions.' + key)
    if (isBrandSyncActive.value && (key === 'site_logo' || key === 'site_favicon')) {
        return `${base} (${t('system.settings.brand_sync_locked_badge')})`
    }
    return base
}

interface SettingGroupData {
    id: string;
    title: string;
    description: string;
    icon: unknown;
    color: 'primary' | 'blue' | 'emerald' | 'amber' | 'red' | 'purple' | 'indigo' | 'orange' | 'pink';
    keys: string[];
    settings: Setting[];
    defaultExpanded: boolean;
}

const generalSettingsGrouped = computed(() => {
    const generalSettings = props.settings.filter(s => s && (s.group === 'general' || s.group === 'brand' || s.group === 'identity'))
    
    const groups: SettingGroupData[] = [
        {
            id: 'site',
            title: t('system.settings.groups.siteInfo.title'),
            description: t('system.settings.groups.siteInfo.description'),
            icon: Globe,
            color: 'blue',
            keys: ['site_name', 'site_logo', 'site_favicon', 'site_description', 'site_url', 'admin_email'],
            settings: [],
            defaultExpanded: true,
        },
        {
            id: 'contact',
            title: t('system.settings.groups.contactInfo.title', 'Kontak & Geolokasi Peta (GPS)'),
            description: t('system.settings.groups.contactInfo.description', 'Alamat fisik, kontak telepon, dan titik koordinat GPS presisi untuk semua tema publik.'),
            icon: MapPin,
            color: 'emerald',
            keys: ['contact_address', 'contact_phone', 'contact_coordinates'],
            settings: [],
            defaultExpanded: true,
        },
    ]

    groups.forEach(group => {
        group.settings = generalSettings.filter(s => group.keys.includes(s.key))
        
        // Ensure settings are in logical order
        if (group.id === 'site') {
            const order = ['site_name', 'site_logo', 'site_favicon', 'site_description', 'site_url', 'admin_email'];
            group.settings.sort((a, b) => order.indexOf(a.key) - order.indexOf(b.key));
        } else if (group.id === 'contact') {
            const order = ['contact_address', 'contact_phone', 'contact_coordinates'];
            group.settings.sort((a, b) => order.indexOf(a.key) - order.indexOf(b.key));
        }
    })

    return groups.filter(group => group.settings.length > 0)
})
</script>
