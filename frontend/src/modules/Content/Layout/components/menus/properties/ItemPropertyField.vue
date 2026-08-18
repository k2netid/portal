<template>
  <div class="space-y-1.5">
    <Label class="text-xs flex items-center gap-1">
      {{ t(setting.label) }}
      <span
        v-if="setting.required"
        class="text-destructive"
      >*</span>
    </Label>

    <!-- Text Input -->
    <Input 
      v-if="setting.type === 'text'"
      :model-value="(value as string)"
      :placeholder="setting.placeholder ? t(setting.placeholder) : ''"
      class="h-8"
      @update:model-value="$emit('update', $event)"
    />

    <!-- Textarea -->
    <Textarea 
      v-else-if="setting.type === 'textarea'"
      :model-value="(value as string)"
      :placeholder="setting.placeholder ? t(setting.placeholder) : ''"
      rows="2"
      @update:model-value="$emit('update', $event)"
    />

    <!-- Number Input -->
    <Input 
      v-else-if="setting.type === 'number'"
      type="number"
      :model-value="(value as number)"
      :min="setting.min"
      :max="setting.max"
      class="h-8"
      @update:model-value="$emit('update', Number($event))"
    />

    <!-- Select -->
    <Select 
      v-else-if="setting.type === 'select'"
      :model-value="String(value)"
      @update:model-value="$emit('update', $event)"
    >
      <SelectTrigger class="h-10" :aria-label="setting.label">
        <SelectValue :placeholder="setting.placeholder ? t(setting.placeholder) : t('common.placeholders.select')" />
      </SelectTrigger>
      <SelectContent>
        <SelectItem 
          v-for="opt in setting.options" 
          :key="String(opt.value)" 
          :value="String(opt.value)"
        >
          {{ t(opt.label) }}
        </SelectItem>
      </SelectContent>
    </Select>

    <!-- Boolean / Checkbox -->
    <div
      v-else-if="setting.type === 'boolean'"
      class="flex items-center gap-2 pt-1"
    >
      <Checkbox 
        :id="setting.key"
        :checked="Boolean(value)"
        @update:checked="$emit('update', $event)"
      />
      <Label
        :for="setting.key"
        class="text-xs font-normal cursor-pointer"
      >
        {{ t(setting.label) }}
      </Label>
    </div>

    <!-- Color Picker -->
    <div
      v-else-if="setting.type === 'color'"
      class="flex items-center gap-2"
    >
      <input 
        type="color" 
        :value="(value as string) || '#000000'"
        class="w-8 h-8 rounded border border-border cursor-pointer"
        @input="$emit('update', ($event.target as HTMLInputElement).value)"
      >
      <Input 
        :model-value="(value as string)"
        :placeholder="t('common.placeholders.hexColor')"
        class="h-8 flex-1 font-mono text-xs"
        @update:model-value="$emit('update', $event)"
      />
    </div>

    <div
      v-else-if="setting.type === 'icon_picker'"
      class="w-full"
    >
      <IconPicker 
        :model-value="(value as string)"
        @update:model-value="$emit('update', $event)"
      />
    </div>

    <!-- Media / Image -->
    <div
      v-else-if="setting.type === 'media'"
      class="space-y-2"
    >
      <div
        v-if="value"
        class="relative"
      >
        <img
          :src="(value as string)"
          alt=""
          class="w-full h-24 object-cover rounded border border-border"
        >
        <Button 
          size="icon" 
          variant="destructive" 
          class="absolute top-1 right-1 h-6 w-6"
          @click="$emit('update', null)"
        >
          <X class="w-3 h-3" />
        </Button>
      </div>
      <MediaPicker 
        :label="value ? 'Change Image' : 'Select Image'"
        @selected="handleMediaSelect"
      />
    </div>

    <!-- Data Select (for pages, posts, etc) -->
    <div
      v-else-if="setting.type === 'data_select'"
      class="text-xs text-muted-foreground"
    >
      ID: {{ value }}
      <!-- Full implementation would fetch and display options -->
    </div>

    <!-- Fallback -->
    <div
      v-else
      class="text-xs text-muted-foreground"
    >
      Unsupported type: {{ setting.type }}
    </div>

    <!-- Description -->
    <p
      v-if="setting.description"
      class="text-[10px] text-muted-foreground"
    >
      {{ setting.description }}
    </p>
  </div>
</template>

<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import {
  X,
} from 'lucide-vue-next';
import type { MenuItemSetting, PropertyValue } from '@/modules/Content/Layout/types/menu';

// UI Components
import {
    Input,
    Textarea,
    Label,
    Checkbox,
    Select,
    SelectTrigger,
    SelectValue,
    SelectContent,
    SelectItem,
    Button,
    IconPicker
} from '@/shared/components/ui';

import MediaPicker from '@/modules/Content/Media/components/picker/MediaPicker.vue';

const { t } = useI18n();

const emit = defineEmits<{
    (e: 'update', value: PropertyValue): void;
}>();

defineProps<{
    setting: MenuItemSetting;
    value?: PropertyValue;
}>();

const handleMediaSelect = (media: { url: string }) => {
    if (media && media.url) {
        emit('update', media.url);
    }
};
</script>
