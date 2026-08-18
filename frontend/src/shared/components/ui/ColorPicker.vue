<template>
  <Popover>
    <PopoverTrigger
      v-if="$slots.default"
      as-child
    >
      <slot />
    </PopoverTrigger>
    <PopoverTrigger
      v-else
      as-child
    >
      <button 
        class="w-8 h-8 rounded border border-border shadow-sm shrink-0 cursor-pointer transition-transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-primary/50 relative overflow-hidden"
        :style="{ backgroundColor: modelValue || '#000000' }"
        :title="title || 'Pick a color'"
      >
        <div class="absolute inset-0 bg-gradient-to-br from-transparent to-black/10" />
      </button>
    </PopoverTrigger>
    <PopoverContent
      class="w-72 p-0 z-[9999]"
      align="start"
    >
      <Tabs
        default-value="picker"
        class="w-full"
      >
        <TabsList class="w-full grid grid-cols-2 rounded-t-md rounded-b-none">
          <TabsTrigger
            value="picker"
            class="text-xs"
          >
            Custom
          </TabsTrigger>
          <TabsTrigger
            value="presets"
            class="text-xs"
          >
            Presets
          </TabsTrigger>
        </TabsList>
                
        <!-- CUSTOM PICKER TAB -->
        <TabsContent
          value="picker"
          class="p-3 space-y-4"
        >
          <!-- Saturation/Brightness Area -->
          <div 
            ref="saturationArea"
            class="w-full h-32 rounded-md relative cursor-crosshair overflow-hidden border border-border"
            :style="{ backgroundColor: `hsl(${hsv.h}, 100%, 50%)` }"
            @mousedown="startDragSaturation"
          >
            <div class="absolute inset-0 bg-gradient-to-r from-white to-transparent" />
            <div class="absolute inset-0 bg-gradient-to-t from-black to-transparent" />
            <div 
              class="absolute w-3 h-3 rounded-full border-2 border-white shadow-sm -ml-1.5 -mt-1.5 pointer-events-none"
              :style="{ 
                left: `${hsv.s}%`, 
                top: `${100 - hsv.v}%`,
                backgroundColor: modelValue
              }"
            />
          </div>

          <!-- Sliders -->
          <div class="space-y-3">
            <!-- Hue Slider -->
            <div class="space-y-1">
              <input 
                v-model.number="hsv.h" 
                type="range" 
                min="0" 
                max="360" 
                class="w-full h-3 rounded-lg appearance-none cursor-pointer"
                :style="{ background: 'linear-gradient(to right, #f00 0%, #ff0 17%, #0f0 33%, #0ff 50%, #00f 67%, #f0f 83%, #f00 100%)' }"
              >
            </div>
          </div>

          <!-- Inputs -->
          <div class="flex gap-2 items-end">
            <div class="flex-1 space-y-0.5">
              <label class="text-[9px] text-muted-foreground font-mono ml-1">HEX</label>
              <Input 
                :model-value="modelValue" 
                class="h-8 text-xs font-mono uppercase"
                maxlength="7" 
                @update:model-value="handleHexInput"
              />
            </div>
            <div class="flex gap-1">
              <div
                class="h-8 w-8 rounded border border-border shrink-0"
                :style="{ backgroundColor: modelValue }"
              />
                             
              <!-- Eyedropper Button -->
              <button
                type="button" 
                class="h-8 w-8 rounded border border-input bg-background hover:bg-accent hover:text-accent-foreground flex items-center justify-center transition-colors"
                title="Pick color from screen"
                @click="pickEyeDropper"
              >
                <Pipette class="w-4 h-4" />
              </button>
            </div>
          </div>
        </TabsContent>

        <!-- PRESETS TAB -->
        <TabsContent
          value="presets"
          class="p-3 space-y-4"
        >
          <!-- Quick Colors -->
          <div class="space-y-2">
            <p class="text-[10px] font-bold text-muted-foreground">
              Quick Colors
            </p>
            <div class="grid grid-cols-8 gap-1.5">
              <button
                v-for="color in colorPalette"
                :key="color"
                type="button"
                class="w-6 h-6 rounded border border-border/50 transition-transform hover:scale-110 focus:outline-none focus:ring-2 focus:ring-primary/50"
                :class="{ 'ring-2 ring-primary': modelValue === color }"
                :style="{ backgroundColor: color }"
                :title="color"
                @click="updateFromPreset(color)"
              />
            </div>
          </div>

          <!-- Theme Colors -->
          <div
            v-if="themeColors && themeColors.length > 0"
            class="space-y-2"
          >
            <p class="text-[10px] font-bold text-muted-foreground">
              Theme Colors
            </p>
            <div class="flex flex-wrap gap-1.5">
              <button
                v-for="color in themeColors"
                :key="color.variable"
                type="button"
                class="w-6 h-6 rounded border border-border/50 transition-transform hover:scale-110 focus:outline-none focus:ring-2 focus:ring-primary/50"
                :class="{ 'ring-2 ring-primary': modelValue === color.variable }"
                :style="{ backgroundColor: color.value }"
                :title="color.name"
                @click="updateFromPreset(color.variable)"
              />
            </div>
          </div>
        </TabsContent>
      </Tabs>
    </PopoverContent>
  </Popover>
</template>

<script setup lang="ts">
import { logger } from '@/shared/utils/logger';
import { ref, reactive, watch } from 'vue';
import Popover from './Popover.vue';
import PopoverTrigger from './PopoverTrigger.vue';
import PopoverContent from './PopoverContent.vue';
import Tabs from './Tabs.vue';
import TabsList from './TabsList.vue';
import TabsTrigger from './TabsTrigger.vue';
import TabsContent from './TabsContent.vue';
import Input from './Input.vue';
import {
  Pipette,
} from 'lucide-vue-next';

interface ColorPreset {
    variable: string;
    value: string;
    name: string;
}

const props = withDefaults(defineProps<{
    modelValue?: string;
    title?: string;
    themeColors?: ColorPreset[];
}>(), {
    modelValue: '#000000',
    title: '',
    themeColors: () => []
});

const emit = defineEmits<{
    'update:modelValue': [value: string];
}>();

// HSV State
const hsv = reactive({ h: 0, s: 0, v: 0 });
const saturationArea = ref<HTMLElement | null>(null);
const isDragging = ref(false);

// Helper: Hex to HSV
const hexToHsv = (hex: string) => {
    let r = 0, g = 0, b = 0;
    if (hex.length === 4) {
        r = parseInt("0x" + hex[1] + hex[1]);
        g = parseInt("0x" + hex[2] + hex[2]);
        b = parseInt("0x" + hex[3] + hex[3]);
    } else if (hex.length === 7) {
        r = parseInt("0x" + hex[1] + hex[2]);
        g = parseInt("0x" + hex[3] + hex[4]);
        b = parseInt("0x" + hex[5] + hex[6]);
    }
    r /= 255; g /= 255; b /= 255;
    
    let max = Math.max(r, g, b), min = Math.min(r, g, b);
    let h = 0, s, v = max;
    let d = max - min;
    s = max === 0 ? 0 : d / max;

    if (max === min) {
        h = 0; 
    } else {
        switch (max) {
            case r: h = (g - b) / d + (g < b ? 6 : 0); break;
            case g: h = (b - r) / d + 2; break;
            case b: h = (r - g) / d + 4; break;
        }
        h /= 6;
    }
    return { h: Math.round(h * 360), s: Math.round(s * 100), v: Math.round(v * 100) };
};

// Helper: HSV to Hex
const hsvToHex = (h: number, s: number, v: number) => {
    let r = 0, g = 0, b = 0, i, f, p, q, t;
    h /= 360; s /= 100; v /= 100;
    i = Math.floor(h * 6);
    f = h * 6 - i;
    p = v * (1 - s);
    q = v * (1 - f * s);
    t = v * (1 - (1 - f) * s);
    switch (i % 6) {
        case 0: r = v; g = t; b = p; break;
        case 1: r = q; g = v; b = p; break;
        case 2: r = p; g = v; b = t; break;
        case 3: r = p; g = q; b = v; break;
        case 4: r = t; g = p; b = v; break;
        case 5: r = v; g = p; b = q; break;
    }
    const toHex = (x: number) => {
        let hex = Math.round(x * 255).toString(16);
        return hex.length === 1 ? '0' + hex : hex;
    };
    return `#${toHex(r)}${toHex(g)}${toHex(b)}`;
};

// Sync HSV from Prop
watch(() => props.modelValue, (val) => {
    // If dragging, we are the source of truth, don't sync back from prop (prevents lag/loop)
    if (isDragging.value) return;

    if (val && !val.startsWith('var')) { // Don't parse theme vars
        const newHsv = hexToHsv(val);
        // Only update if significantly different (avoid slider jitter)
        if (Math.abs(newHsv.h - hsv.h) > 1 || Math.abs(newHsv.s - hsv.s) > 1 || Math.abs(newHsv.v - hsv.v) > 1) {
            Object.assign(hsv, newHsv);
        }
    }
}, { immediate: true });

// Sync Prop from HSV (Watcher on HSV)
watch(hsv, () => {
    // Only emit if not triggered by prop change (cycle)
    // Actually we strictly compute Hex from HSV here
    const hex = hsvToHex(hsv.h, hsv.s, hsv.v);
    if (hex.toUpperCase() !== (props.modelValue || '').toUpperCase()) {
        emit('update:modelValue', hex);
    }
});

const handleHexInput = (val: string | number) => {
    const sVal = String(val);
    if (/^#[0-9A-Fa-f]{6}$/.test(sVal)) {
        emit('update:modelValue', sVal);
    }
};

const updateFromPreset = (color: string) => {
    emit('update:modelValue', color);
};

const pickEyeDropper = async () => {
    if (!(window as unknown as { EyeDropper: unknown }).EyeDropper) {
        alert('Your browser does not support the Eyedropper API. Test in Chrome/Edge.');
        return;
    }
    const EyeDropper = (window as unknown as { EyeDropper: new () => { open: () => Promise<{ sRGBHex: string }> } }).EyeDropper;
    const eyeDropper = new EyeDropper();
    try {
        const result = await eyeDropper.open();
        if (result && result.sRGBHex) {
            emit('update:modelValue', result.sRGBHex);
            const newHsv = hexToHsv(result.sRGBHex);
            Object.assign(hsv, newHsv);
        }
    } catch (e) {
        logger.warning('Eyedropper failed:', e);
    }
};

// Saturation/Brightness Drag Logic
const handleDrag = (event: MouseEvent) => {
    if (!saturationArea.value) return;
    const rect = saturationArea.value.getBoundingClientRect();
    let x = (event.clientX - rect.left) / rect.width;
    let y = 1 - (event.clientY - rect.top) / rect.height; // Invert Y (Bottom is 0)

    x = Math.max(0, Math.min(1, x));
    y = Math.max(0, Math.min(1, y));

    hsv.s = Math.round(x * 100);
    hsv.v = Math.round(y * 100);
};

const startDragSaturation = (event: MouseEvent) => {
    isDragging.value = true;
    handleDrag(event);
    window.addEventListener('mousemove', onMouseMove);
    window.addEventListener('mouseup', stopDragSaturation);
};

const onMouseMove = (event: MouseEvent) => {
    if (isDragging.value) handleDrag(event);
};

const stopDragSaturation = () => {
    isDragging.value = false;
    window.removeEventListener('mousemove', onMouseMove);
    window.removeEventListener('mouseup', stopDragSaturation);
};

// Palette
const colorPalette = [
    '#000000', '#374151', '#6B7280', '#9CA3AF', '#D1D5DB', '#E5E7EB', '#F3F4F6', '#FFFFFF',
    '#EF4444', '#F87171', '#FCA5A5', '#FECACA',
    '#F97316', '#FB923C', '#FDBA74', '#FED7AA',
    '#EAB308', '#FACC15', '#FDE047', '#FEF08A',
    '#22C55E', '#4ADE80', '#86EFAC', '#BBF7D0',
    '#06B6D4', '#22D3EE', '#67E8F9', '#A5F3FC',
    '#3B82F6', '#60A5FA', '#93C5FD', '#BFDBFE',
    '#8B5CF6', '#A78BFA', '#C4B5FD', '#DDD6FE',
    '#EC4899', '#F472B6', '#FBCFE8', '#FCE7F3'
];
</script>
