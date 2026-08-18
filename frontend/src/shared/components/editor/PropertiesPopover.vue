<template>
  <Popover
    :open="open"
    @update:open="$emit('update:open', $event)"
  >
    <!-- Anchor the popover to the actual media node -->
    <PopoverAnchor
      v-if="anchor"
      :element="anchor"
    />
    <PopoverTrigger
      v-else
      as-child
    >
      <button
        type="button"
        class="sr-only"
        :aria-label="t('publishing.editor.properties.title')"
      >
        {{ t('publishing.editor.properties.title') }}
      </button>
    </PopoverTrigger>
        
    <PopoverContent 
      class="w-80 p-0 z-[100] outline-none bg-transparent border-none shadow-none" 
      align="center" 
      side="right" 
      :side-offset="10"
    >
      <div 
        class="flex flex-col h-full w-full bg-card border border-border shadow-2xl rounded-lg overflow-hidden"
        :style="{ transform: `translate(${dragOffset.x}px, ${dragOffset.y}px)` }"
      >
        <div 
          class="flex items-center justify-between p-3 border-b bg-muted/30 cursor-move select-none"
          @pointerdown="startDrag"
        >
          <div class="flex items-center gap-2">
            <GripHorizontal class="w-3.5 h-3.5 text-muted-foreground" />
            <h3 class="text-sm font-semibold text-foreground">
              {{ t('publishing.editor.properties.title') }}
            </h3>
          </div>
          <Button
            variant="ghost"
            size="icon"
            class="h-6 w-6 rounded-full hover:bg-destructive/10 hover:text-destructive"
            @pointerdown.stop
            @click="$emit('update:open', false)"
          >
            <X class="w-3.5 h-3.5" />
          </Button>
        </div>

        <div class="p-0">
          <Accordion
            type="single"
            collapsible
            default-value="general"
          >
            <!-- HTML Content (Embed Specific) -->
            <AccordionItem
              v-if="isHtmlEmbedNode"
              value="html-content"
              class="border-b px-3"
            >
              <AccordionTrigger class="text-xs font-semibold py-2.5 hover:no-underline">
                {{ t('publishing.editor.properties.html') }}
              </AccordionTrigger>
              <AccordionContent class="pt-1 pb-4">
                <textarea 
                  v-model="form.html" 
                  class="flex min-h-[120px] w-full rounded-md border border-input bg-background px-3 py-2 text-xs shadow-sm placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50 font-mono"
                  :placeholder="t('publishing.editor.placeholders.html')"
                />
              </AccordionContent>
            </AccordionItem>

            <!-- Icon Settings -->
            <AccordionItem
              v-if="isIconNode"
              value="icon-settings"
              class="border-b px-3"
            >
              <AccordionTrigger class="text-xs font-semibold py-2.5 hover:no-underline">
                Icon Style
              </AccordionTrigger>
              <AccordionContent class="pt-1 pb-4 space-y-4">
                <div class="grid grid-cols-2 gap-3">
                  <!-- Size -->
                  <div class="space-y-1.5">
                    <label class="text-[11px] font-medium text-muted-foreground">{{ t('publishing.editor.fields.size') }}</label>
                    <Input
                      v-model="form.size"
                      :placeholder="t('publishing.editor.placeholders.unitEm')"
                      class="h-8 text-xs"
                    />
                  </div>
                  <!-- Color -->
                  <div class="space-y-1.5">
                    <label class="text-[11px] font-medium text-muted-foreground">{{ t('publishing.editor.fields.color') }}</label>
                    <div class="flex gap-2 items-center">
                      <ColorPicker v-model="form.color">
                        <Button
                          variant="outline"
                          size="icon"
                          class="h-8 w-8 p-0 shrink-0 relative overflow-hidden"
                        >
                          <div
                            class="absolute inset-0"
                            :style="{ backgroundColor: form.color === 'currentColor' ? '#000' : form.color }"
                          />
                        </Button>
                      </ColorPicker>
                      <Input
                        v-model="form.color"
                        class="h-8 text-xs flex-1 uppercase font-mono"
                      />
                    </div>
                  </div>
                </div>

                <!-- Stroke & Rotate -->
                <div class="grid grid-cols-2 gap-3">
                  <div class="space-y-1.5">
                    <label class="text-[11px] font-medium text-muted-foreground flex justify-between">
                      Stroke Width <span>{{ form.strokeWidth }}px</span>
                    </label>
                    <input
                      v-model.number="form.strokeWidth"
                      type="range"
                      min="0.5"
                      max="4"
                      step="0.5"
                      class="w-full h-1.5 bg-secondary rounded-lg appearance-none cursor-pointer"
                    >
                  </div>
                  <div class="space-y-1.5">
                    <label class="text-[11px] font-medium text-muted-foreground flex justify-between">
                      Rotate <span>{{ form.rotate }}°</span>
                    </label>
                    <input
                      v-model.number="form.rotate"
                      type="range"
                      min="0"
                      max="360"
                      step="15"
                      class="w-full h-1.5 bg-secondary rounded-lg appearance-none cursor-pointer"
                    >
                  </div>
                </div>
                            
                <!-- Opacity -->
                <div class="space-y-1.5">
                  <label class="text-[11px] font-medium text-muted-foreground flex justify-between">
                    Opacity <span>{{ form.opacity * 100 }}%</span>
                  </label>
                  <input
                    v-model.number="form.opacity"
                    type="range"
                    min="0"
                    max="1"
                    step="0.1"
                    class="w-full h-1.5 bg-secondary rounded-lg appearance-none cursor-pointer"
                  >
                </div>

                <!-- Background & Padding -->
                <Accordion
                  type="single"
                  collapsible
                >
                  <AccordionItem
                    value="icon-appearance"
                    class="border-t pt-2 -mx-0 border-b-0"
                  >
                    <AccordionTrigger class="text-[11px] font-medium py-1.5 hover:no-underline text-muted-foreground">
                      Background & Spacing
                    </AccordionTrigger>
                    <AccordionContent class="pt-2 pb-0 space-y-3">
                      <div class="space-y-1.5">
                        <label class="text-[11px] font-medium text-muted-foreground">{{ t('publishing.editor.fields.backgroundColor') }}</label>
                        <div class="flex gap-2 items-center">
                          <ColorPicker v-model="form.backgroundColor">
                            <Button
                              variant="outline"
                              size="icon"
                              class="h-8 w-8 p-0 shrink-0 relative overflow-hidden"
                            >
                              <Palette
                                v-if="!form.backgroundColor"
                                class="w-3.5 h-3.5 text-muted-foreground"
                              />
                              <div
                                v-else
                                class="absolute inset-0"
                                :style="{ backgroundColor: form.backgroundColor }"
                              />
                            </Button>
                          </ColorPicker>
                          <Input
                            v-model="form.backgroundColor"
                            :placeholder="t('publishing.editor.placeholders.none')"
                            class="h-8 text-xs flex-1 uppercase font-mono"
                          />
                        </div>
                      </div>
                      <div class="grid grid-cols-2 gap-3">
                        <div class="space-y-1.5">
                          <label class="text-[11px] font-medium text-muted-foreground">{{ t('publishing.editor.fields.padding') }}</label>
                          <Input
                            v-model="form.padding"
                            :placeholder="t('publishing.editor.placeholders.zeroPx')"
                            class="h-8 text-xs"
                          />
                        </div>
                        <div class="space-y-1.5">
                          <label class="text-[11px] font-medium text-muted-foreground">{{ t('publishing.editor.fields.borderRadius') }}</label>
                          <Input
                            v-model="form.borderRadius"
                            :placeholder="t('publishing.editor.placeholders.zeroPx')"
                            class="h-8 text-xs"
                          />
                        </div>
                      </div>
                    </AccordionContent>
                  </AccordionItem>
                </Accordion>
              </AccordionContent>
            </AccordionItem>

            <!-- General Settings (Not for Embeds or Shapes) -->
            <AccordionItem
              v-if="!isHtmlEmbedNode && !isIconNode"
              value="general"
              class="border-b px-3"
            >
              <AccordionTrigger class="text-xs font-semibold py-2.5 hover:no-underline">
                {{ t('publishing.editor.properties.general') }}
              </AccordionTrigger>
              <AccordionContent class="pt-1 pb-4 space-y-3">
                <!-- Source URL -->
                <div class="space-y-1.5">
                  <label class="text-[11px] font-medium text-muted-foreground">{{ t('publishing.editor.fields.source') }}</label>
                  <div class="flex gap-1.5">
                    <Input
                      v-model="form.src"
                      :placeholder="t('publishing.editor.placeholders.urlEllipsis')"
                      class="h-8 text-xs"
                    />
                  </div>
                </div>
                            
                <!-- Display Mode -->
                <div class="space-y-1.5">
                  <label class="text-[11px] font-medium text-muted-foreground">{{ t('publishing.editor.fields.displayMode') }}</label>
                  <div class="grid grid-cols-2 gap-1.5">
                    <Button 
                      v-for="mode in [
                        { val: 'block', lab: t('publishing.editor.values.block') },
                        { val: 'inline', lab: t('publishing.editor.values.inline') },
                        { val: 'float-left', lab: t('publishing.editor.values.floatLeft') },
                        { val: 'float-right', lab: t('publishing.editor.values.floatRight') }
                      ]" 
                      :key="mode.val"
                      variant="outline"
                      size="sm"
                      class="text-[10px] h-7 px-1 font-normal"
                      :class="{ 'bg-primary text-primary-foreground border-primary font-medium': form.displayMode === mode.val }"
                      @click="form.displayMode = mode.val"
                    >
                      {{ mode.lab }}
                    </Button>
                  </div>
                </div>

                <!-- Alignment -->
                <div
                  v-if="form.displayMode === 'block'"
                  class="space-y-1.5"
                >
                  <label class="text-[11px] font-medium text-muted-foreground">{{ t('publishing.editor.fields.alignment') }}</label>
                  <div class="flex gap-1.5">
                    <Button 
                      v-for="align in ['left', 'center', 'right']" 
                      :key="align"
                      variant="outline"
                      size="sm"
                      class="flex-1 capitalize h-7 text-[10px] font-normal"
                      :class="{ 'bg-primary text-primary-foreground border-primary font-medium': form.align === align }"
                      @click="form.align = align"
                    >
                      {{ align }}
                    </Button>
                  </div>
                </div>

                <!-- Alt Text -->
                <div class="space-y-1.5">
                  <label class="text-[11px] font-medium text-muted-foreground">{{ t('publishing.editor.fields.altText') }}</label>
                  <Input
                    v-model="form.alt"
                    :placeholder="t('publishing.editor.placeholders.alt')"
                    class="h-8 text-xs"
                  />
                </div>
              </AccordionContent>
            </AccordionItem>

            <!-- Dimensions -->
            <AccordionItem
              v-if="!isIconNode"
              value="dimensions"
              class="border-b px-3"
            >
              <AccordionTrigger class="text-xs font-semibold py-2.5 hover:no-underline">
                {{ t('publishing.editor.properties.dimensions') }}
              </AccordionTrigger>
              <AccordionContent class="pt-1 pb-4">
                <div class="flex items-end gap-2">
                  <div class="space-y-1.5 flex-1">
                    <label class="text-[11px] font-medium text-muted-foreground">{{ t('publishing.editor.fields.width') }}</label>
                    <Input
                      v-model="form.width"
                      :placeholder="t('publishing.editor.placeholders.auto')"
                      class="h-8 text-xs"
                      @input="onDimensionChange('width')"
                    />
                  </div>
                                
                  <div class="pb-1">
                    <Button 
                      variant="ghost" 
                      size="icon" 
                      class="h-6 w-6 text-muted-foreground"
                      :class="{ 'bg-muted text-primary': constrainProportions }"
                      :title="t('publishing.editor.actions.constrainProportions')"
                      @click="constrainProportions = !constrainProportions"
                    >
                      <LinkIcon
                        v-if="constrainProportions"
                        class="w-3.5 h-3.5"
                      />
                      <UnlinkIcon
                        v-else
                        class="w-3.5 h-3.5"
                      />
                    </Button>
                  </div>

                  <div class="space-y-1.5 flex-1">
                    <label class="text-[11px] font-medium text-muted-foreground">{{ t('publishing.editor.fields.height') }}</label>
                    <Input
                      v-model="form.height"
                      :placeholder="t('publishing.editor.placeholders.auto')"
                      class="h-8 text-xs"
                      @input="onDimensionChange('height')"
                    />
                  </div>
                </div>
              </AccordionContent>
            </AccordionItem>
                    
            <!-- Video Settings -->
            <AccordionItem
              v-if="isVideoNode || isYoutubeEmbed"
              value="video"
              class="border-b px-3"
            >
              <AccordionTrigger class="text-xs font-semibold py-2.5 hover:no-underline">
                {{ t('publishing.editor.properties.video') }}
              </AccordionTrigger>
              <AccordionContent class="pt-1 pb-4 space-y-3">
                <div class="flex items-center justify-between group">
                  <label
                    for="video-autoplay"
                    class="text-[11px] font-medium text-muted-foreground group-hover:text-foreground cursor-pointer select-none"
                  >{{ t('publishing.editor.fields.autoplay') }}</label>
                  <Switch
                    id="video-autoplay"
                    v-model:checked="form.autoplay"
                    class="scale-75 origin-right"
                  />
                </div>
                <div class="flex items-center justify-between group">
                  <label
                    for="video-controls"
                    class="text-[11px] font-medium text-muted-foreground group-hover:text-foreground cursor-pointer select-none"
                  >{{ t('publishing.editor.fields.controls') }}</label>
                  <Switch
                    id="video-controls"
                    v-model:checked="form.controls"
                    class="scale-75 origin-right"
                  />
                </div>
                <div class="flex items-center justify-between group">
                  <label
                    for="video-loop"
                    class="text-[11px] font-medium text-muted-foreground group-hover:text-foreground cursor-pointer select-none"
                  >{{ t('publishing.editor.fields.loop') }}</label>
                  <Switch
                    id="video-loop"
                    v-model:checked="form.loop"
                    class="scale-75 origin-right"
                  />
                </div>
              </AccordionContent>
            </AccordionItem>

            <!-- Appearance -->
            <AccordionItem
              v-if="!isIconNode"
              value="appearance"
              class="px-3 border-b-0"
            >
              <AccordionTrigger class="text-xs font-semibold py-2.5 hover:no-underline">
                {{ t('publishing.editor.properties.appearance') }}
              </AccordionTrigger>
              <AccordionContent class="pt-1 pb-4 space-y-3">
                <div class="grid grid-cols-2 gap-3">
                  <div class="space-y-1.5">
                    <label class="text-[11px] font-medium text-muted-foreground">{{ t('publishing.editor.fields.borderRadius') }}</label>
                    <Input
                      v-model="form.borderRadius"
                      type="number"
                      :placeholder="t('publishing.editor.placeholders.zero')"
                      class="h-8 text-xs"
                    />
                  </div>
                  <div class="space-y-1.5">
                    <label class="text-[11px] font-medium text-muted-foreground">{{ t('publishing.editor.fields.borderWidth') }}</label>
                    <Input
                      v-model="form.borderWidth"
                      type="number"
                      :placeholder="t('publishing.editor.placeholders.zero')"
                      class="h-8 text-xs"
                    />
                  </div>
                </div>
                <div class="space-y-1.5">
                  <label class="text-[11px] font-medium text-muted-foreground">{{ t('publishing.editor.fields.borderColor') }}</label>
                  <div class="flex gap-2 items-center">
                    <ColorPicker
                      v-model="form.borderColor"
                      :title="t('publishing.editor.fields.borderColor')"
                    >
                      <Button
                        variant="outline"
                        size="icon"
                        class="h-8 w-8 p-0 shrink-0 relative overflow-hidden"
                      >
                        <Palette
                          v-if="!form.borderColor"
                          class="w-4 h-4 text-muted-foreground"
                        />
                        <div
                          v-else
                          class="absolute inset-0"
                          :style="{ backgroundColor: form.borderColor }"
                        />
                      </Button>
                    </ColorPicker>
                    <Input
                      v-model="form.borderColor"
                      :placeholder="t('publishing.editor.placeholders.none')"
                      class="h-8 text-xs flex-1 uppercase font-mono"
                    />
                  </div>
                </div>
                <div class="space-y-1.5">
                  <label class="text-[11px] font-medium text-muted-foreground">{{ t('publishing.editor.fields.margin') }}</label>
                  <Input
                    v-model="form.margin"
                    type="number"
                    :placeholder="t('publishing.editor.placeholders.fontSizePx')"
                    class="h-8 text-xs"
                  />
                </div>
              </AccordionContent>
            </AccordionItem>
          </Accordion>
        </div>

        <div class="flex justify-end gap-2 p-3 border-t bg-muted/10">
          <Button
            variant="outline"
            size="sm"
            class="h-7 text-xs px-3"
            @click="$emit('update:open', false)"
          >
            {{ t('publishing.editor.actions.cancel') }}
          </Button>
          <Button
            size="sm"
            class="h-7 text-xs px-3"
            @click="save"
          >
            {{ t('publishing.editor.actions.save') }}
          </Button>
        </div>
      </div>
    </PopoverContent>
  </Popover>
</template>

<script setup lang="ts">
import { ref, watch, reactive, computed } from 'vue';
import {
  GripHorizontal,
  LinkIcon,
  Palette,
  UnlinkIcon,
  X,
} from 'lucide-vue-next';
import { 
    Button, 
    Input, 
    Popover, 
    PopoverTrigger, 
    PopoverContent, 
    Accordion, 
    AccordionItem, 
    AccordionTrigger, 
    AccordionContent, 
    ColorPicker, 
    Switch
} from '@/shared/components/ui';
import { PopoverAnchor } from 'radix-vue';
import { useI18n } from 'vue-i18n';

interface PropertiesForm {
    src?: string;
    width: string | number;
    height: string | number;
    alt: string;
    title: string;
    className: string;
    // Icon specific
    name: string;
    size: string;
    color: string;
    strokeWidth: number;
    rotate: number;
    backgroundColor: string;
    opacity: number;
    // Video specific
    autoplay: boolean;
    controls: boolean;
    loop: boolean;
    muted: boolean;
    // HTML Embed specific
    html: string;
    // Common
    borderRadius: string | number;
    borderWidth: string | number;
    borderColor: string;
    borderStyle: string;
    margin: string | number;
    padding: string | number;
    align?: string;
    displayMode?: string;
}

const { t } = useI18n();

const props = defineProps<{
    open: boolean;
    node: { type: string; attrs: Record<string, unknown> } | null;
    anchor: HTMLElement | null;
}>();

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void;
    (e: 'save', attrs: Record<string, unknown>): void;
}>();

const constrainProportions = ref(true);

// Dragging Logic
const dragOffset = reactive({ x: 0, y: 0 });
let isDragging = false;
let startX = 0;
let startY = 0;

const startDrag = (e: PointerEvent) => {
    isDragging = true;
    startX = e.clientX - dragOffset.x;
    startY = e.clientY - dragOffset.y;
    document.addEventListener('pointermove', onDrag);
    document.addEventListener('pointerup', stopDrag);
    document.body.style.userSelect = 'none';
};

const onDrag = (e: PointerEvent) => {
    if (!isDragging) return;
    dragOffset.x = e.clientX - startX;
    dragOffset.y = e.clientY - startY;
};

const stopDrag = () => {
    isDragging = false;
    document.removeEventListener('pointermove', onDrag);
    document.removeEventListener('pointerup', stopDrag);
    document.body.style.userSelect = '';
};

const isVideoNode = computed(() => props.node?.type === 'video');
const isHtmlEmbedNode = computed(() => props.node?.type === 'htmlEmbed');
const isIconNode = computed(() => props.node?.type === 'icon');

// Initialize form
const form = ref<PropertiesForm>({
    src: '',
    width: '',
    height: '',
    alt: '',
    title: '',
    className: '',
    name: '',
    size: '1em',
    color: 'currentColor',
    strokeWidth: 2,
    rotate: 0,
    backgroundColor: '',
    opacity: 1,
    autoplay: false,
    controls: true,
    loop: false,
    muted: false,
    html: '',
    borderRadius: '4px',
    borderWidth: '0',
    borderColor: '',
    borderStyle: 'none',
    margin: '0',
    padding: '0',
    align: 'center',
    displayMode: 'block'
});

// Helper to detect YouTube
const isYoutubeEmbed = computed(() => {
    return isHtmlEmbedNode.value && form.value.html && (form.value.html.includes('youtube.com/embed') || form.value.html.includes('youtu.be'));
});

watch(() => props.open, (isOpen) => {
    if (isOpen) {
        dragOffset.x = 0;
        dragOffset.y = 0;
        
        if (props.node) {
            const attrs = props.node.attrs as Record<string, unknown>;
            form.value = {
                src: (attrs.src as string) || '',
                width: (attrs.width as string) || '',
                height: (attrs.height as string) || '',
                title: (attrs.title as string) || '',
                className: (attrs.class as string) || '',

                // Icon attrs
                name: (attrs.name as string) || 'Circle',
                size: (attrs.size as string) || '1em',
                color: (attrs.color as string) || 'currentColor',
                strokeWidth: (attrs.strokeWidth as number) || 2,
                rotate: (attrs.rotate as number) || 0,
                backgroundColor: (attrs.backgroundColor as string) || '',
                opacity: attrs.opacity !== undefined ? (attrs.opacity as number) : 1,
                
                borderRadius: (attrs.borderRadius as string) || '0',
                borderWidth: parseInt(attrs.borderWidth as string) || 0,
                borderColor: (attrs.borderColor as string) || '',
                padding: (attrs.padding as string) || '0',
                align: (attrs.align as string) || 'center',
                alt: (attrs.alt as string) || '',
                displayMode: (attrs.displayMode as string) || 'block',
                margin: attrs.margin ? parseInt(attrs.margin as string) : 16,
                // Video attrs
                autoplay: attrs.autoplay !== undefined ? (attrs.autoplay as boolean) : false,
                controls: attrs.controls !== undefined ? (attrs.controls as boolean) : true,
                loop: attrs.loop !== undefined ? (attrs.loop as boolean) : false,
                muted: attrs.muted !== undefined ? (attrs.muted as boolean) : false,
                // Embed specific
                html: (attrs.html as string) || '',
                borderStyle: (attrs.borderStyle as string) || 'none'
            };

            // Sync controls state from YouTube URL if applicable
            if (isYoutubeEmbed.value) {
                const srcMatch = form.value.html.match(/src=["']([^"']+)["']/);
                if (srcMatch && srcMatch[1]) {
                    const url = srcMatch[1];
                    form.value.autoplay = url.includes('autoplay=1');
                    form.value.controls = !url.includes('controls=0');
                    form.value.loop = url.includes('loop=1');
                }
            }
        }
    }
});

function onDimensionChange(changedField: 'width' | 'height') {
    if (!constrainProportions.value) return;
    
    if (changedField === 'width' && form.value.width) {
        form.value.height = 'auto';
    } else if (changedField === 'height' && form.value.height) {
        form.value.width = 'auto';
    }
}

// Live update watcher
watch(form, () => {
   emitChanges();
}, { deep: true });

const emitChanges = () => {
    const baseAttrs: Record<string, unknown> = {
        ...(form.value as unknown as Record<string, unknown>),
        borderRadius: form.value.borderRadius ? ensureUnit(form.value.borderRadius) : null,
        borderWidth: form.value.borderWidth ? `${form.value.borderWidth}px` : '0px',
        borderColor: form.value.borderColor || null,
        borderStyle: form.value.borderStyle || 'none',
        margin: form.value.margin ? `${form.value.margin}px` : '0px',
        padding: form.value.padding ? ensureUnit(form.value.padding) : '0px'
    };
    
    function ensureUnit(val: string | number) {
        if (!val && val !== 0) return val;
        const sVal = String(val).trim();
        return (sVal !== '' && !isNaN(Number(sVal))) ? `${sVal}px` : sVal;
    }

    if (isVideoNode.value) {
        baseAttrs.autoplay = form.value.autoplay;
        baseAttrs.controls = form.value.controls;
        baseAttrs.loop = form.value.loop;
    } else if (isHtmlEmbedNode.value) {
        let html = form.value.html;

        if (isYoutubeEmbed.value) {
           const srcMatch = html.match(/src=["']([^"']+)["']/);
           if (srcMatch && srcMatch[1]) {
                let url = srcMatch[1];
                let params = [];

                if (form.value.autoplay) {
                    if (!url.includes('autoplay=1')) params.push('autoplay=1');
                    if (!url.includes('mute=1')) params.push('mute=1'); 
                } else {
                     url = url.replace(/autoplay=1/g, '').replace(/mute=1/g, '');
                }

                if (!form.value.controls) {
                    if (!url.includes('controls=0')) params.push('controls=0');
                } else {
                    url = url.replace(/controls=0/g, '');
                }

                if (form.value.loop) {
                    if (!url.includes('loop=1')) params.push('loop=1');
                    const idMatch = url.match(/(?:embed\/|v=|youtu\.be\/)([^?&"']{11})/);
                    if (idMatch && idMatch[1]) {
                        const videoId = idMatch[1];
                        if (!url.includes(`playlist=${videoId}`)) {
                            params.push(`playlist=${videoId}`);
                        }
                    }
                } else {
                    url = url.replace(/loop=1/g, '').replace(/playlist=[^&]*/g, '');
                }
                
                url = url.replace(/&+/g, '&').replace(/\?&/g, '?');
                if (url.endsWith('&') || url.endsWith('?')) url = url.slice(0, -1);

                if (params.length > 0) {
                    const separator = url.includes('?') ? '&' : '?';
                    url = `${url}${separator}${params.join('&')}`;
                }
                
               html = html.replace(srcMatch[1], url);
           }
        }

        baseAttrs.html = html;
        
        if (baseAttrs.html && (!baseAttrs.width || baseAttrs.width === 'auto' || !baseAttrs.height || baseAttrs.height === 'auto')) {
            const htmlString = baseAttrs.html as string;
            const widthMatch = htmlString.match(/(?:width|WIDTH)=["']?(\d+)(?:px)?["']?/);
            const heightMatch = htmlString.match(/(?:height|HEIGHT)=["']?(\d+)(?:px)?["']?/);
            
            if (widthMatch && widthMatch[1]) {
                baseAttrs.width = `${widthMatch[1]}px`;
            }
            if (heightMatch && heightMatch[1]) {
                baseAttrs.height = `${heightMatch[1]}px`;
            }
        }
    } else if (isIconNode.value) {
        baseAttrs.name = form.value.name;
        baseAttrs.size = form.value.size;
        baseAttrs.color = form.value.color;
        baseAttrs.strokeWidth = form.value.strokeWidth;
        baseAttrs.rotate = form.value.rotate;
        baseAttrs.backgroundColor = form.value.backgroundColor;
        baseAttrs.borderRadius = ensureUnit(form.value.borderRadius);
        baseAttrs.padding = ensureUnit(form.value.padding);
        baseAttrs.opacity = form.value.opacity;
    } else {
        delete baseAttrs.autoplay;
        delete baseAttrs.controls;
        delete baseAttrs.loop;
    }

    emit('save', baseAttrs);
};

const save = () => {
    emit('update:open', false);
};
</script>
