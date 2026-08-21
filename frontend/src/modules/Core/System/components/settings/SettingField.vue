<template>
  <div :class="colSpanClass">
    <div class="flex items-center justify-between mb-1">
      <label :for="fieldId" class="block text-sm font-medium text-foreground">
        {{ label }}
      </label>

      <!-- Presets Picker -->
      <Popover v-if="hasPresets">
        <PopoverTrigger as-child>
          <button 
            type="button" 
            class="h-6 w-6 p-0 flex items-center justify-center rounded-md hover:bg-muted transition-colors focus:outline-none"
            :aria-label="`${$t('common.actions.select')} ${label}`"
            :title="$t('common.actions.select')"
          >
            <LucideIcon
              name="Wand2"
              class="h-3.5 w-3.5 text-muted-foreground"
            />
          </button>
        </PopoverTrigger>
        <PopoverContent
          class="w-72 p-1"
          align="end"
        >
          <div class="p-2 pb-1 text-xs font-semibold text-muted-foreground uppercase tracking-wider">
            {{ $t('common.actions.select') }}
          </div>
          <div class="grid gap-1">
            <button
              v-for="preset in presets"
              :key="String(preset.value)"
              class="w-full text-left px-2 py-1.5 text-sm rounded-md hover:bg-muted transition-colors line-clamp-2"
              :title="preset.labelKey ? $t(preset.labelKey) : (preset.label || String(preset.value))"
              @click="applyPreset(preset.value)"
            >
              {{ preset.labelKey ? $t(preset.labelKey) : (preset.label || String(preset.value)) }}
            </button>
          </div>
        </PopoverContent>
      </Popover>
    </div>
    <p
      v-if="description"
      class="text-xs text-muted-foreground mb-2"
    >
      {{ description }}
    </p>

    <!-- Dropdown Select (if field has options) -->
    <Select
      v-if="hasOptions && !isMailPort"
      :model-value="localValue !== null && localValue !== undefined ? String(localValue) : undefined"
      :disabled="disabled"
      @update:model-value="localValue = hasNumericOptions ? Number($event) : $event; updateValue()"
    >
      <SelectTrigger :aria-label="label" :class="error ? 'border-destructive focus:ring-destructive' : ''">
        <SelectValue :placeholder="$t('common.actions.select')" />
      </SelectTrigger>
      <SelectContent>
        <SelectItem
          v-for="option in translatedFieldOptions"
          :key="option.value"
          :value="option.value"
        >
          {{ option.label }}
        </SelectItem>
      </SelectContent>
    </Select>

    <!-- Mail Port Dropdown (dynamic based on encryption) -->
    <Select
      v-else-if="isMailPort"
      :model-value="String(localValue)"
      :disabled="disabled"
      @update:model-value="localValue = Number($event); updateValue()"
    >
      <SelectTrigger :aria-label="label" :class="error ? 'border-destructive focus:ring-destructive' : ''">
        <SelectValue :placeholder="$t('common.actions.select')" />
      </SelectTrigger>
      <SelectContent>
        <SelectItem
          v-for="option in translatedMailPortOptions"
          :key="option.value"
          :value="option.value"
        >
          {{ option.label }}
        </SelectItem>
      </SelectContent>
    </Select>

    <Input
      :id="fieldId"
      v-else-if="(type === 'string' || type === 'password' || type === 'datetime') && !isTextarea"
      :model-value="(localValue as string)"
      :disabled="disabled"
      :type="type === 'datetime' ? 'datetime-local' : ((isPassword || type === 'password') ? 'password' : 'text')"
      :class="error ? 'border-destructive focus-visible:ring-destructive' : ''"
      @input="localValue = ($event.target as HTMLInputElement).value; updateValue()"
    />

    <!-- Textarea -->
    <Textarea
      :id="fieldId"
      v-else-if="isTextarea"
      :model-value="(localValue as string)"
      :disabled="disabled"
      :rows="3"
      :class="error ? 'border-destructive focus-visible:ring-destructive' : ''"
      @input="localValue = ($event.target as HTMLTextAreaElement).value; updateValue()"
    />

    <!-- Number Input -->
    <Input
      :id="fieldId"
      v-else-if="type === 'integer'"
      :model-value="(localValue as number)"
      :disabled="disabled"
      type="number"
      :class="error ? 'border-destructive focus-visible:ring-destructive' : ''"
      @input="localValue = Number(($event.target as HTMLInputElement).value); updateValue()"
    />

    <!-- Boolean Toggle -->
    <div
      v-else-if="type === 'boolean'"
      class="mt-1 flex items-center space-x-2"
    >
      <Switch
        :aria-label="label"
        :checked="Boolean(localValue)"
        :disabled="disabled"
        @update:checked="localValue = $event; updateValue()"
      />
      <span class="text-sm text-foreground">
        {{ localValue ? enabledText : disabledText }}
      </span>
    </div>

    <!-- Image/Media Picker -->
    <div
      v-else-if="type === 'image' || type === 'media'"
      class="flex items-center gap-3"
    >
      <!-- Preview/Placeholder Box -->
      <div 
        class="h-10 w-24 border rounded-md overflow-hidden bg-muted/30 flex items-center justify-center flex-shrink-0 shadow-sm transition-colors"
        :class="{ 'border-2 border-dashed border-muted-foreground/30': !localValue }"
      >
        <img
          v-if="localValue"
          :src="(localValue as string)"
          class="w-full h-full object-contain p-1"
          alt="Preview"
        >
        <LucideIcon
          v-else
          name="Image"
          class="w-4 h-4 text-muted-foreground/50"
        />
      </div>

      <!-- Action Buttons -->
      <div class="flex items-center gap-1.5">
        <Button 
          type="button"
          variant="secondary"
          size="sm"
          class="h-8 px-2.5 text-xs font-medium"
          @click="showMediaPicker = true"
        >
          <LucideIcon
            name="Pencil"
            class="w-3.5 h-3.5 mr-1.5 opacity-70"
          />
          {{ localValue ? $t('common.actions.change') : $t('common.actions.select') }}
        </Button>
                
        <Button 
          v-if="localValue"
          type="button"
          variant="ghost"
          size="icon"
          class="h-8 w-8 text-destructive hover:text-destructive hover:bg-destructive/10"
          :aria-label="$t('common.actions.remove')"
          title="Remove"
          @click="localValue = ''; updateValue()"
        >
          <LucideIcon
            name="Trash2"
            class="w-3.5 h-3.5"
          />
        </Button>
      </div>

      <!-- Use an empty template for the trigger slot to hide the default button -->
      <MediaPicker
        v-model:open="showMediaPicker"
        @selected="handleMediaSelect"
      >
        <template #trigger>
          <span />
        </template>
      </MediaPicker>
    </div>
        
    <!-- Error Message -->
    <p
      v-if="error"
      class="text-sm text-destructive mt-1"
    >
      {{ Array.isArray(error) ? error[0] : error }}
    </p>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import { getFieldOptions, getMailPortOptions, getFieldPresets } from '@/config/settingsFieldOptions'
import type { SettingValue } from '@/engine/types/settings'
import {
    Input,
    Textarea,
    Switch,
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
    Popover,
    PopoverContent,
    PopoverTrigger,
    Button,
    LucideIcon
} from '@/shared/components/ui'
import MediaPicker from '@/shared/components/ui/MediaPicker.vue'

interface SettingOption {
    value: string | number;
    label?: string;
    labelKey?: string;
}

const { t } = useI18n()


const props = withDefaults(defineProps<{
    modelValue: SettingValue;
    fieldKey: string;
    label: string;
    description?: string;
    type?: string;
    mailEncryption?: string;
    enabledText?: string;
    disabledText?: string;
    error?: string | string[] | null;
    disabled?: boolean;
    colSpan?: number | 'full';
}>(), {
    description: '',
    type: 'string',
    mailEncryption: 'tls',
    enabledText: 'Enabled',
    disabledText: 'Disabled',
    error: null,
    disabled: false,
    colSpan: 1,
});

const emit = defineEmits<{
    (e: 'update:modelValue', value: SettingValue): void;
}>()

const localValue = ref<SettingValue>(props.modelValue)
const showMediaPicker = ref(false)

const handleMediaSelect = (media: { url: string }) => {
    localValue.value = media.url
    updateValue()
    showMediaPicker.value = false
}

// Watch for external changes
watch(() => props.modelValue, (newValue) => {
    if (newValue !== localValue.value) {
        localValue.value = newValue
    }
})

// Computed properties
const hasOptions = computed(() => {
    return getFieldOptions(props.fieldKey) !== null
})

const fieldOptions = computed<SettingOption[]>(() => {
    return getFieldOptions(props.fieldKey) || []
})

// Translate options using labelKey
const translatedFieldOptions = computed(() => {
    return fieldOptions.value.map(option => ({
        value: String(option.value),
        label: option.labelKey ? t(option.labelKey) : (option.label || String(option.value))
    }))
})

const hasNumericOptions = computed(() => {
    return fieldOptions.value.some(option => typeof option.value === 'number')
})

const isMailPort = computed(() => {
    return props.fieldKey === 'mail_port'
})

const mailPortOptions = computed<SettingOption[]>(() => {
    return getMailPortOptions(props.mailEncryption || 'tls')
})

const translatedMailPortOptions = computed(() => {
    return mailPortOptions.value.map(option => ({
        value: String(option.value),
        label: option.labelKey ? t(option.labelKey) : (option.label || String(option.value))
    }))
})

const isPassword = computed(() => {
    return props.fieldKey.includes('password')
})

const isTextarea = computed(() => {
    return props.type === 'text' || props.type === 'json'
})

const hasPresets = computed(() => {
    return getFieldPresets(props.fieldKey) !== null
})

const presets = computed<SettingOption[]>(() => {
    return getFieldPresets(props.fieldKey) || []
})

const applyPreset = (value: string | number) => {
    localValue.value = value
    updateValue()
}

const fieldId = computed(() => `setting-field-${props.fieldKey.replace(/[^a-zA-Z0-9_-]/g, '-')}`)

const colSpanClass = computed(() => {
    if (props.colSpan === 'full' || props.colSpan === 2) {
        return 'col-span-1 md:col-span-2'
    }
    return 'col-span-1'
})

// Update parent
const updateValue = () => {
    emit('update:modelValue', localValue.value)
}
</script>
