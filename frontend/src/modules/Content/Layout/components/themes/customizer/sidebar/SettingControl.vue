<template>
  <div class="space-y-2">
    <div class="flex items-center justify-between">
      <label
        :for="fieldId"
        class="text-xs font-semibold text-foreground tracking-tight"
      >
        {{ settingLabel(setting.key, setting.label) }}
      </label>
      <span
        v-if="setting.required"
        class="text-xs font-bold text-destructive"
      >*</span>
    </div>

    <!-- Color Picker -->
    <div
      v-if="setting.type === 'color'"
      class="flex items-center gap-2.5"
    >
      <div class="relative w-10 h-10 rounded-xl overflow-hidden border border-border shadow-sm shrink-0 group cursor-pointer hover:ring-2 hover:ring-primary/20 transition-all">
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
        class="flex-1 h-10 px-3 py-2 bg-background border border-border rounded-xl text-xs font-mono uppercase focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all"
        @input="$emit('update:modelValue', ($event.target as HTMLInputElement).value)"
        @change="$emit('change')"
      >
    </div>

    <!-- Select (Static or Dynamic Forms) -->
    <div
      v-else-if="setting.type === 'select' || isDynamicFormSelect"
      class="relative"
    >
      <Select
        :model-value="String(modelValue || (isDynamicFormSelect ? 'contact' : ''))"
        @update:model-value="(val) => { handleInput(val); $emit('change'); }"
      >
        <SelectTrigger
          :aria-label="settingLabel(setting.key, setting.label)"
          class="h-10 rounded-xl border border-border bg-background text-sm font-medium"
        >
          <SelectValue :placeholder="setting.placeholder ? $t('publishing.theme_customizer.items.' + setting.key + '_placeholder') : $t('publishing.theme_customizer.editor.menus.placeholder')" />
        </SelectTrigger>
        <SelectContent v-if="resolvedOptions.length > 0">
          <SelectItem
            v-for="opt in resolvedOptions"
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
      class="flex items-center gap-3 p-3 rounded-xl border border-border bg-muted/20"
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
      <span class="text-xs font-mono font-bold bg-background border border-border px-2.5 py-1 rounded-lg text-foreground min-w-[4ch] text-center shadow-sm">
        {{ modelValue }}
      </span>
    </div>

    <!-- Textarea -->
    <textarea
      v-else-if="setting.type === 'textarea'"
      :id="fieldId"
      :value="(modelValue as string)"
      :aria-label="settingLabel(setting.key, setting.label)"
      rows="3"
      class="w-full p-3 bg-background border border-border rounded-xl text-sm font-medium focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all resize-y min-h-[90px]"
      :placeholder="setting.placeholder"
      @input="handleInput(($event.target as HTMLTextAreaElement).value)"
      @change="$emit('change')"
    />

    <!-- Toggle Switch / Boolean -->
    <div
      v-else-if="setting.type === 'checkbox' || setting.type === 'boolean'"
      class="flex items-center justify-between p-3.5 border border-border rounded-xl bg-muted/20 hover:bg-muted/30 transition-colors"
    >
      <span class="text-xs font-semibold text-foreground select-none">
        {{ modelValue ? $t('publishing.theme_customizer.items.common_options.enabled', 'Aktif') : $t('publishing.theme_customizer.items.common_options.disabled', 'Nonaktif') }}
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
      class="space-y-2 p-3.5 border border-border rounded-xl bg-muted/10"
    >
      <div
        v-for="opt in (setting.options as ThemeOption[])"
        :key="String(opt.value)"
        class="flex items-center space-x-2.5 py-1"
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
          class="text-xs font-medium text-foreground cursor-pointer select-none"
        >
          {{ translateOption(opt.label) }}
        </label>
      </div>
    </div>

    <!-- Media Picker (Clean & Compact) -->
    <div
      v-else-if="setting.type === 'media'"
      class="space-y-2"
    >
      <!-- Media Selected State -->
      <div
        v-if="modelValue"
        class="flex items-center gap-4 p-3 rounded-2xl border border-border bg-card/80 shadow-sm"
      >
        <div class="w-16 h-16 rounded-xl border border-border/80 bg-muted/30 p-1.5 flex items-center justify-center shrink-0 overflow-hidden shadow-inner">
          <img
            :src="(modelValue as string)"
            class="max-w-full max-h-full object-contain"
            alt="Media Preview"
          >
        </div>

        <div class="flex-1 min-w-0 space-y-1">
          <p class="text-xs font-semibold text-foreground truncate">
            {{ (modelValue as string).split('/').pop() || 'media_asset' }}
          </p>
          <div class="flex items-center gap-2">
            <Button
              type="button"
              variant="outline"
              size="sm"
              class="h-7 px-2.5 text-[11px] rounded-lg gap-1 font-medium"
              @click="$emit('pick-media')"
            >
              <Pencil class="w-3 h-3" />
              <span>{{ $t('publishing.theme_customizer.items.common_options.change_image', 'Ganti') }}</span>
            </Button>
            <Button
              type="button"
              variant="ghost"
              size="sm"
              class="h-7 px-2 text-[11px] rounded-lg gap-1 text-destructive hover:bg-destructive/10 font-medium"
              @click="handleInput(''); $emit('change')"
            >
              <Trash2 class="w-3 h-3" />
              <span>{{ $t('publishing.theme_customizer.items.common_options.remove', 'Hapus') }}</span>
            </Button>
          </div>
        </div>
      </div>

      <!-- Media Empty State (Clean Dropzone) -->
      <button
        v-else
        type="button"
        class="w-full h-20 rounded-2xl border-2 border-dashed border-border hover:border-primary/50 hover:bg-primary/5 transition-all flex items-center justify-center gap-2.5 text-muted-foreground hover:text-primary cursor-pointer group"
        @click="$emit('pick-media')"
      >
        <div class="w-8 h-8 rounded-lg bg-muted/60 group-hover:bg-primary/10 flex items-center justify-center transition-colors">
          <Image class="w-4 h-4" />
        </div>
        <span class="text-xs font-semibold">{{ $t('publishing.theme_customizer.items.common_options.select_media', 'Pilih dari Media Library') }}</span>
      </button>
    </div>

    <!-- Dynamic Repeater -->
    <div
      v-else-if="setting.type === 'repeater'"
      class="space-y-3"
    >
      <div
        v-for="(item, idx) in (Array.isArray(modelValue) ? modelValue : [])"
        :key="idx"
        class="group p-4 bg-muted/15 border border-border rounded-2xl space-y-3 relative transition-all hover:border-border/80"
      >
        <div class="flex items-center justify-between border-b border-border/40 pb-2">
          <span class="text-[11px] font-bold text-muted-foreground uppercase tracking-wider">Item #{{ idx + 1 }}</span>
          <Button
            type="button"
            variant="ghost"
            size="icon"
            class="h-7 w-7 text-muted-foreground hover:text-destructive hover:bg-destructive/10 rounded-lg"
            :aria-label="$t('common.actions.remove', 'Hapus')"
            :title="$t('common.actions.remove', 'Hapus')"
            @click="() => {
              const newValue = [...(modelValue as any[])];
              newValue.splice(idx, 1);
              handleInput(newValue);
              $emit('change');
            }"
          >
            <Trash2 class="w-3.5 h-3.5" />
          </Button>
        </div>

        <div class="grid grid-cols-1 gap-3 pt-1">
          <div
            v-for="field in (setting.fields || [])"
            :key="field.name"
            class="space-y-1.5"
          >
            <label class="text-[10px] uppercase font-bold text-muted-foreground tracking-wider">
              {{ translateLabel(field.label) }}
            </label>

            <!-- Text input in repeater -->
            <input
              v-if="field.type === 'text'"
              type="text"
              :value="item[field.name]"
              class="w-full h-9 px-3 bg-background border border-border rounded-xl text-xs font-medium focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all"
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
              class="w-full p-2.5 bg-background border border-border rounded-xl text-xs font-medium focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all resize-y min-h-[60px]"
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
              <SelectTrigger class="h-9 text-xs bg-background border border-border rounded-xl">
                <div class="flex items-center gap-2">
                  <template v-if="field.options === 'social_icons' || field.options === 'feature_icons'">
                    <component
                      :is="getGenericIcon(item[field.name])"
                      class="w-3.5 h-3.5"
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

      <Button
        type="button"
        variant="outline"
        size="sm"
        class="w-full h-9 rounded-xl border-dashed gap-1.5 font-semibold text-xs"
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
            newItem.icon = 'Instagram';
            newItem.url = '';
          }
          newValue.push(newItem);
          handleInput(newValue);
          $emit('change');
        }"
      >
        <Plus class="w-3.5 h-3.5" />
        <span>{{ optionLabel('Add New Item') }}</span>
      </Button>
    </div>

    <!-- Default Input (Text/URL/etc) -->
    <input
      v-else
      :id="fieldId"
      :type="setting.type || 'text'"
      :value="(modelValue as string)"
      :placeholder="setting.placeholder"
      class="w-full h-10 px-3 bg-background border border-border rounded-xl text-sm font-medium focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all placeholder:text-muted-foreground/60"
      @input="handleInput(($event.target as HTMLInputElement).value)"
      @change="$emit('change')"
    >

    <div
      v-if="helperVisible"
      class="space-y-2 pt-1"
    >
      <div class="flex flex-wrap items-center gap-2">
        <Button
          v-if="canResolveFromLink"
          type="button"
          variant="outline"
          size="sm"
          class="h-8 px-3 text-xs rounded-xl"
          @click="useMapLinkDirectly"
        >
          Gunakan link map langsung
        </Button>
        <Button
          v-if="isCurrentLocationMode"
          type="button"
          variant="outline"
          size="sm"
          class="h-8 px-3 text-xs rounded-xl"
          :disabled="helperLoading"
          @click="resolveFromCurrentLocation"
        >
          {{ helperLoading ? 'Memproses...' : 'Gunakan lokasi saat ini' }}
        </Button>
      </div>
      <p
        v-if="helperMessage"
        class="text-xs text-emerald-600 leading-snug font-medium"
      >
        {{ helperMessage }}
      </p>
      <p
        v-if="helperError"
        class="text-xs text-destructive leading-snug font-medium"
      >
        {{ helperError }}
      </p>
    </div>

    <p
      v-if="setting.description"
      class="text-[11px] text-muted-foreground leading-normal"
    >
      {{ settingHint(setting.key, setting.description) }}
    </p>
  </div>
</template>

<script setup lang="ts">
import { computed, ref, onMounted } from 'vue';
import api from '@/engine/api/client';
import { parseResponse, ensureArray } from '@/shared/utils/responseParser';
import { useThemeCustomizerLabels } from '@/modules/Content/Layout/composables/useThemeCustomizerLabels';
import type { ThemeSetting, ThemeOption } from '@/modules/Content/Layout/types/theme';
import {
  Button,
  Select,
  SelectTrigger,
  SelectValue,
  SelectContent,
  SelectItem,
  Switch,
  Checkbox,
} from '@/shared/components/ui';
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

const props = defineProps<{
  setting: ThemeSetting & { key?: string };
  modelValue: unknown;
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
  const foundSocial = socialIcons.find((i) => i.value === key);
  if (foundSocial) return foundSocial.icon;
  const foundFeature = featureIcons.find((i) => i.value === key);
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
  || settingKey.value === 'contact_map_link',
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

const dynamicFormOptions = ref<ThemeOption[]>([]);

const isDynamicFormSelect = computed(() => {
  return props.setting?.key === 'contact_form_slug' || props.setting?.options === 'dynamic:forms';
});

const resolvedOptions = computed<ThemeOption[]>(() => {
  if (isDynamicFormSelect.value) {
    return dynamicFormOptions.value;
  }
  if (Array.isArray(props.setting?.options)) {
    return props.setting.options as ThemeOption[];
  }
  return [];
});

onMounted(async () => {
  if (isDynamicFormSelect.value) {
    try {
      const res = await api.get('/manage/forms');
      const parsed = parseResponse<any>(res);
      const data = ensureArray<any>(parsed.data);
      const opts: ThemeOption[] = data.map((f: any) => ({
        value: f.slug,
        label: `${f.name || f.title || f.slug} (${f.slug})`,
      }));
      if (opts.length === 0) {
        opts.unshift({ value: 'contact', label: 'Default Contact (contact)' });
      }
      dynamicFormOptions.value = opts;
    } catch {
      dynamicFormOptions.value = [{ value: 'contact', label: 'Default Contact (contact)' }];
    }
  }
});
</script>
