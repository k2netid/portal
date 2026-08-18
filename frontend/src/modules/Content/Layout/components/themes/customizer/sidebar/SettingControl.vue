<template>
  <div class="space-y-2">
    <div class="flex items-center justify-between">
      <label :for="fieldId" class="text-xs font-medium text-foreground tracking-wide">
        {{ settingLabel(setting.key, setting.label) }}
      </label>
      <span
        v-if="setting.required"
        class="text-xs text-red-800"
      >*</span>
    </div>

    <!-- Color Picker -->
    <div
      v-if="setting.type === 'color'"
      class="flex gap-2"
    >
      <div class="relative w-10 h-10 rounded-lg overflow-hidden border shadow-sm shrink-0 group cursor-pointer">
        <input
          type="color"
          :value="modelValue"
          :aria-label="settingLabel(setting.key, setting.label)"
          class="absolute inset-0 w-[150%] h-[150%] -top-[25%] -left-[25%] p-0 m-0 opacity-0 cursor-pointer"
          @input="$emit('update:modelValue', ($event.target as HTMLInputElement).value)"
          @change="$emit('change')"
        >
        <div 
          class="w-full h-full"
          :style="{ backgroundColor: (modelValue as string) }"
        />
      </div>
      <input
        :id="fieldId"
        type="text"
        :value="modelValue"
        class="flex-1 h-10 px-3 py-2 bg-background border rounded-lg text-sm font-mono focus:ring-1 focus:ring-inset focus:ring-primary focus:border-primary outline-none transition-colors"
        @input="$emit('update:modelValue', ($event.target as HTMLInputElement).value)"
        @change="$emit('change')"
      >
    </div>

    <!-- Select -->
    <div
      v-else-if="setting.type === 'select'"
      class="relative"
    >
      <Select 
        :model-value="String(modelValue)" 
        @update:model-value="(val) => { handleInput(val); $emit('change'); }"
      >
        <SelectTrigger :aria-label="settingLabel(setting.key, setting.label)" class="h-10">
          <SelectValue :placeholder="setting.placeholder ? $t('publishing.theme_customizer.items.' + setting.key + '_placeholder') : $t('publishing.theme_customizer.editor.menus.placeholder')" />
        </SelectTrigger>
        <SelectContent v-if="Array.isArray(setting.options)">
          <SelectItem
            v-for="opt in (setting.options as ThemeOption[])"
            :key="String(opt.value)"
            :value="String(opt.value)"
          >
            {{ translateOption(opt.label) }}
          </SelectItem>
        </SelectContent>
      </Select>
    </div>
        
    <!-- Range Slider -->
    <div
      v-else-if="setting.type === 'range'"
      class="flex items-center gap-3"
    >
      <input
        type="range"
        :aria-label="settingLabel(setting.key, setting.label)"
        :min="setting.min || 0"
        :max="setting.max || 100"
        :step="setting.step || 1"
        :value="(modelValue as number)"
        class="flex-1 h-2 bg-secondary rounded-lg appearance-none cursor-pointer accent-primary"
        @input="handleInput(($event.target as HTMLInputElement).value)"
        @change="$emit('change')"
      >
      <span class="text-xs font-mono bg-muted px-2 py-1 rounded text-muted-foreground min-w-[3ch] text-center">
        {{ modelValue }}
      </span>
    </div>

    <!-- Textarea -->
    <textarea
      :id="fieldId"
      v-else-if="setting.type === 'textarea'"
      :value="(modelValue as string)"
      :aria-label="settingLabel(setting.key, setting.label)"
      rows="3"
      class="w-full p-3 bg-background border rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-colors resize-y min-h-[80px]"
      :placeholder="setting.placeholder"
      @input="handleInput(($event.target as HTMLTextAreaElement).value)"
      @change="$emit('change')"
    />

    <!-- Toggle Switch / Boolean -->
    <div
      v-else-if="setting.type === 'checkbox' || setting.type === 'boolean'"
      class="flex items-center justify-between p-3 border rounded-lg bg-muted/20 hover:bg-muted/30 transition-colors"
    >
      <span class="text-sm font-medium text-foreground select-none">
        {{ modelValue ? $t('publishing.theme_customizer.items.common_options.enabled') : $t('publishing.theme_customizer.items.common_options.disabled') }}
      </span>
      <Switch
        :aria-label="settingLabel(setting.key, setting.label)"
        :checked="Boolean(modelValue)"
        @update:checked="(val) => { handleInput(val); $emit('change'); }"
      />
    </div>

    <!-- Checkbox List (Multi-select) -->
    <div
      v-else-if="setting.type === 'checkbox_list' && Array.isArray(setting.options)"
      class="space-y-2 p-3 border rounded-lg bg-muted/10"
    >
      <div
        v-for="opt in (setting.options as ThemeOption[])"
        :key="String(opt.value)"
        class="flex items-center space-x-2 py-1"
      >
        <Checkbox 
          :id="setting.key + '-' + opt.value"
          :checked="Array.isArray(modelValue) ? modelValue.includes(opt.value) : false"
          @update:checked="(checked) => {
            let newValue = Array.isArray(modelValue) ? [...modelValue] : [];
            if (checked) {
              if (!newValue.includes(opt.value)) newValue.push(opt.value);
            } else {
              newValue = newValue.filter(v => v !== opt.value);
            }
            handleInput(newValue);
            $emit('change');
          }"
        />
        <label
          :for="setting.key + '-' + opt.value"
          class="text-sm font-medium leading-none cursor-pointer select-none"
        >
          {{ translateOption(opt.label) }}
        </label>
      </div>
    </div>

    <!-- Media Picker -->
    <div
      v-else-if="setting.type === 'media'"
      class="space-y-2"
    >
      <div
        v-if="modelValue"
        class="relative group h-32 bg-muted/50 rounded-lg overflow-hidden border shadow-sm"
      >
        <img
          :src="(modelValue as string)"
          class="w-full h-full object-contain p-2"
          alt="Preview"
        >
        <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2">
          <button
            class="p-2 bg-white/20 hover:bg-white/40 rounded-full text-white backdrop-blur-sm transition-colors"
            :aria-label="$t('publishing.theme_customizer.items.common_options.change_image')"
            :title="$t('publishing.theme_customizer.items.common_options.change_image')"
            @click="$emit('pick-media')"
          >
            <Pencil class="w-4 h-4" />
          </button>
          <button
            class="p-2 bg-white/20 hover:bg-white/40 rounded-full text-white backdrop-blur-sm transition-colors"
            :aria-label="$t('publishing.theme_customizer.items.common_options.remove')"
            :title="$t('publishing.theme_customizer.items.common_options.remove')"
            @click="handleInput(''); $emit('change')"
          >
            <Trash2 class="w-4 h-4" />
          </button>
        </div>
      </div>
      <button 
        v-else
        class="w-full h-20 border-2 border-dashed rounded-lg flex flex-col items-center justify-center gap-1 text-muted-foreground hover:text-primary hover:border-primary/50 transition-colors bg-muted/10 hover:bg-muted/20"
        @click="$emit('pick-media')"
      >
        <Image class="w-5 h-5" />
        <span class="text-[10px] font-medium">{{ $t('publishing.theme_customizer.items.common_options.select_media') }}</span>
      </button>
    </div>

    <!-- Dynamic Repeater -->
    <div
      v-else-if="setting.type === 'repeater'"
      class="space-y-4"
    >
      <div 
        v-for="(item, idx) in (Array.isArray(modelValue) ? modelValue : [])" 
        :key="idx"
        class="group p-4 bg-muted/10 border rounded-xl space-y-4 relative transition-all hover:bg-muted/20"
      >
        <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity z-10">
          <button 
            class="p-1.5 text-muted-foreground hover:text-destructive transition-colors"
            :aria-label="$t('common.actions.remove')"
            :title="$t('common.actions.remove')"
            @click="() => {
              const newValue = [...(modelValue as any[])];
              newValue.splice(idx, 1);
              handleInput(newValue);
              $emit('change');
            }"
          >
            <Trash2 class="w-3.5 h-3.5" />
          </button>
        </div>

        <div class="grid grid-cols-1 gap-4 pt-1">
          <div
            v-for="field in (setting.fields || [])"
            :key="field.name"
            class="space-y-1.5"
          >
            <label class="text-[10px] uppercase font-bold text-muted-foreground/60 tracking-wider">
              {{ translateLabel(field.label) }}
            </label>
                        
            <!-- Text input in repeater -->
            <input
              v-if="field.type === 'text'"
              type="text"
              :value="item[field.name]"
              class="w-full h-8 px-2 bg-background border rounded-lg text-xs focus:ring-1 focus:ring-primary outline-none transition-colors"
              @input="(e) => {
                const newValue = [...(modelValue as any[])];
                newValue[idx] = { ...item, [field.name]: (e.target as HTMLInputElement).value };
                handleInput(newValue);
              }"
              @change="$emit('change')"
            >
            <p
              v-if="setting.key === 'social_links' && field.name === 'url'"
              class="text-[10px] text-muted-foreground leading-snug"
            >
              {{
                item.icon === 'MessageCircle' || item.icon === 'WhatsApp'
                  ? 'Untuk WhatsApp: isi nomor (contoh 08123456789 / 628123456789) atau URL WA.'
                  : item.icon === 'Mail' || item.icon === 'Email'
                    ? 'Untuk Email: isi alamat email (contoh info@domain.com) atau mailto:.'
                    : 'Untuk sosial lain: isi URL lengkap (https://...).'
              }}
            </p>

            <!-- Textarea in repeater -->
            <textarea
              v-else-if="field.type === 'textarea'"
              :value="item[field.name]"
              rows="2"
              class="w-full p-2 bg-background border rounded-lg text-xs focus:ring-1 focus:ring-primary outline-none transition-colors resize-y min-h-[50px]"
              @input="(e) => {
                const newValue = [...(modelValue as any[])];
                newValue[idx] = { ...item, [field.name]: (e.target as HTMLTextAreaElement).value };
                handleInput(newValue);
              }"
              @change="$emit('change')"
            />

            <!-- Select in repeater -->
            <Select 
              v-else-if="field.type === 'select'"
              :model-value="item[field.name]" 
              @update:model-value="(val) => {
                const newValue = [...(modelValue as any[])];
                newValue[idx] = { ...item, [field.name]: val };
                handleInput(newValue);
                $emit('change');
              }"
            >
              <SelectTrigger class="h-8 text-xs bg-background">
                <div class="flex items-center gap-2">
                  <template v-if="field.options === 'social_icons' || field.options === 'feature_icons'">
                    <component
                      :is="getGenericIcon(item[field.name])"
                      class="w-3 h-3"
                    />
                  </template>
                  <SelectValue />
                </div>
              </SelectTrigger>
              <SelectContent>
                <template v-if="field.options === 'social_icons'">
                  <SelectItem
                    v-for="opt in socialIcons"
                    :key="opt.value"
                    :value="opt.value"
                  >
                    <div class="flex items-center gap-2">
                      <component
                        :is="opt.icon"
                        class="w-3.5 h-3.5"
                      />
                      <span>{{ opt.label }}</span>
                    </div>
                  </SelectItem>
                </template>
                <template v-else-if="field.options === 'feature_icons'">
                  <SelectItem
                    v-for="opt in featureIcons"
                    :key="opt.value"
                    :value="opt.value"
                  >
                    <div class="flex items-center gap-2">
                      <component
                        :is="opt.icon"
                        class="w-3.5 h-3.5"
                      />
                      <span>{{ opt.label }}</span>
                    </div>
                  </SelectItem>
                </template>
              </SelectContent>
            </Select>
          </div>
        </div>
      </div>

      <button 
        class="w-full h-9 border border-dashed border-primary/30 rounded-xl flex items-center justify-center gap-2 text-[11px] font-bold text-primary hover:bg-primary/5 transition-all"
        @click="() => {
          const newValue = Array.isArray(modelValue) ? [...(modelValue as any[])] : [];
          const newItem: Record<string, any> = {};
          if (setting.fields) {
            setting.fields.forEach((f: any) => { 
              if (f.name === 'icon') {
                newItem[f.name] = (f.options === 'social_icons') ? 'Instagram' : 'UserPlus';
              } else {
                newItem[f.name] = '';
              }
            });
          } else {
            // Fallback for social links without explicit fields
            newItem.icon = 'Instagram';
            newItem.url = '';
          }
          newValue.push(newItem);
          handleInput(newValue);
          $emit('change');
        }"
      >
        <Plus class="w-3.5 h-3.5" />
        {{ optionLabel('Add New Item') }}
      </button>
    </div>

    <!-- Default Input (Text/URL/etc) -->
    <input
      :id="fieldId"
      v-else
      :type="setting.type || 'text'"
      :value="(modelValue as string)"
      :placeholder="setting.placeholder"
      class="w-full h-10 px-3 bg-background border rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-colors"
      @input="handleInput(($event.target as HTMLInputElement).value)"
      @change="$emit('change')"
    >
        
    <div
      v-if="helperVisible"
      class="space-y-2"
    >
      <button
        v-if="canResolveFromLink"
        type="button"
        class="h-8 px-2.5 text-[11px] rounded-md border border-primary/30 text-primary hover:bg-primary/10 transition-colors"
        @click="useMapLinkDirectly"
      >
        Gunakan link map langsung
      </button>
      <button
        v-if="isCurrentLocationMode"
        type="button"
        class="h-8 px-2.5 text-[11px] rounded-md border border-primary/30 text-primary hover:bg-primary/10 transition-colors disabled:opacity-50"
        :disabled="helperLoading"
        @click="resolveFromCurrentLocation"
      >
        {{ helperLoading ? 'Memproses...' : 'Gunakan lokasi saat ini' }}
      </button>
      <p
        v-if="helperMessage"
        class="text-[10px] text-emerald-600 leading-snug"
      >
        {{ helperMessage }}
      </p>
      <p
        v-if="helperError"
        class="text-xs text-red-800 leading-snug"
      >
        {{ helperError }}
      </p>
    </div>

    <p
      v-if="setting.description"
      class="text-[10px] text-muted-foreground leading-snug"
    >
      {{ settingHint(setting.key, setting.description) }}
    </p>
  </div>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue';
import { useThemeCustomizerLabels } from '@/modules/Content/Layout/composables/useThemeCustomizerLabels';
import type { ThemeSetting, ThemeOption } from '@/modules/Content/Layout/types/theme';

// Common Icons
import {
  BadgeCheck,
  CreditCard,
  Facebook,
  FileCheck,
  Github,
  Globe,
  Image,
  Instagram,
  Linkedin,
  Mail,
  MessageCircle,
  Music2,
  Pencil,
  Plus,
  Trash2,
  Twitter,
  UserPlus,
  Youtube,
} from 'lucide-vue-next';

// Icons for Repeater

import { 
    Select, 
    SelectTrigger, 
    SelectValue, 
    SelectContent, 
    SelectItem,
    Switch,
    Checkbox
} from '@/shared/components/ui';

const props = defineProps<{
    setting: ThemeSetting & { key?: string };
    modelValue: unknown;
    /** Active theme slug for theme.<slug>.customizer.* lookups */
    themeSlug?: string;
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: unknown): void;
    (e: 'update:multiple', value: Record<string, unknown>): void;
    (e: 'change'): void;
    (e: 'pick-media'): void;
}>();

const { settingLabel, settingHint, optionLabel, fieldLabel } = useThemeCustomizerLabels(
    () => props.themeSlug,
);

const fieldId = computed(() => `theme-setting-${(props.setting.key || 'field').replace(/[^a-zA-Z0-9_-]/g, '-')}`);

const socialIcons = [
    { label: 'Instagram', value: 'Instagram', icon: Instagram },
    { label: 'Twitter / X', value: 'Twitter', icon: Twitter },
    { label: 'Facebook', value: 'Facebook', icon: Facebook },
    { label: 'YouTube', value: 'Youtube', icon: Youtube },
    { label: 'LinkedIn', value: 'Linkedin', icon: Linkedin },
    { label: 'GitHub', value: 'Github', icon: Github },
    { label: 'TikTok', value: 'Music2', icon: Music2 },
    { label: 'Website', value: 'Globe', icon: Globe },
    { label: 'WhatsApp', value: 'MessageCircle', icon: MessageCircle },
    { label: 'Email', value: 'Mail', icon: Mail },
];

const featureIcons = [
    { label: 'Daftar Akun', value: 'UserPlus', icon: UserPlus },
    { label: 'Lengkapi Data', value: 'FileCheck', icon: FileCheck },
    { label: 'Seleksi', value: 'BadgeCheck', icon: BadgeCheck },
    { label: 'Pembayaran', value: 'CreditCard', icon: CreditCard },
];

const getGenericIcon = (key: string) => {
    const foundSocial = socialIcons.find(i => i.value === key);
    if (foundSocial) return foundSocial.icon;
    const foundFeature = featureIcons.find(i => i.value === key);
    if (foundFeature) return foundFeature.icon;
    return Globe;
};

const translateOption = (label: string) => optionLabel(label);
const translateLabel = (label: string) => fieldLabel(label);

const handleInput = (val: unknown) => {
    emit('update:modelValue', val);
};

const helperLoading = ref(false);
const helperMessage = ref('');
const helperError = ref('');
const settingKey = computed(() => props.setting?.key || '');
const helperVisible = computed(() =>
    settingKey.value === 'contact_map_source'
    || settingKey.value === 'contact_map_link'
);
const isCurrentLocationMode = computed(() => settingKey.value === 'contact_map_source' && String(props.modelValue || '') === 'current_location');
const canResolveFromLink = computed(() => settingKey.value === 'contact_map_link' && String(props.modelValue || '').trim() !== '');

async function resolveFromCurrentLocation(): Promise<void> {
    if (helperLoading.value) return;
    if (typeof navigator === 'undefined' || !navigator.geolocation) {
        helperError.value = 'Browser tidak mendukung geolocation.';
        return;
    }
    helperLoading.value = true;
    helperError.value = '';
    helperMessage.value = '';
    try {
        const pos = await new Promise<GeolocationPosition>((resolve, reject) => {
            navigator.geolocation.getCurrentPosition(resolve, reject, {
                enableHighAccuracy: true,
                timeout: 15000,
                maximumAge: 60000,
            });
        });
        const lat = Number(pos.coords.latitude);
        const lon = Number(pos.coords.longitude);
        const mapLink = `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(`${lat},${lon}`)}`;
        emit('update:multiple', {
            contact_map_source: 'current_location',
            contact_map_link: mapLink,
        });
        emit('change');
        helperMessage.value = 'Lokasi saat ini berhasil dipakai dan link map diperbarui.';
    } catch {
        helperError.value = 'Izin lokasi ditolak atau gagal mengambil lokasi saat ini.';
    } finally {
        helperLoading.value = false;
    }
}

function useMapLinkDirectly(): void {
    const link = String(props.modelValue || '').trim();
    if (!link) return;
    emit('update:multiple', { contact_map_link: link });
    emit('change');
    helperError.value = '';
    helperMessage.value = 'Link map langsung disimpan.';
}
</script>
