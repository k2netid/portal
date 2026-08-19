<template>
  <section
    v-if="selectedItem?.bindingComponent && organizationMode === 'bindings'"
    class="space-y-4"
  >
    <div class="flex items-center justify-between px-1">
      <h4 class="text-xs font-bold uppercase tracking-widest text-muted-foreground flex items-center gap-2">
        <Database class="w-3 h-3 text-primary" />
        {{ t('publishing.theme_customizer.editor.sections.bindings') }}
      </h4>
      <span class="text-[10px] text-primary/70 font-mono">{{ t('publishing.theme_customizer.editor.bindings.subtitle') }}</span>
    </div>

    <div class="space-y-4">
      <div
        v-for="slot in selectedItem.bindingComponent.slots"
        :key="slot.id"
        class="bg-card border border-border rounded-2xl overflow-hidden shadow-sm transition-all hover:shadow-md"
      >
        <button
          class="w-full flex items-center justify-between p-6 hover:bg-muted/30 transition-colors group"
          @click="toggleSlot(slot.id)"
        >
          <div class="flex items-center gap-4">
            <div
              class="w-10 h-10 rounded-xl flex items-center justify-center text-xs font-black shadow-inner transition-colors"
              :class="getSlotConfig(selectedItem.bindingComponent.id, slot.id).sourceType !== 'static' ? 'bg-primary text-primary-foreground' : 'bg-muted text-muted-foreground'"
            >
              {{ slot.id.charAt(0).toUpperCase() }}{{ slot.id.charAt(1) || '' }}
            </div>
            <div class="text-left">
              <span class="font-bold text-base tracking-tight">{{ slot.label }}</span>
              <div class="flex items-center gap-2 mt-0.5">
                <span class="text-[10px] uppercase font-bold text-muted-foreground/50">{{ t('publishing.theme_customizer.editor.bindings.source_label') }}</span>
                <span
                  class="text-[10px] uppercase font-black"
                  :class="getSlotConfig(selectedItem.bindingComponent.id, slot.id).sourceType !== 'static' ? 'text-primary' : 'text-muted-foreground'"
                >
                  {{ getSourceLabel(getSlotConfig(selectedItem.bindingComponent.id, slot.id).sourceType) }}
                </span>
              </div>
            </div>
          </div>
          <ChevronDown
            class="w-5 h-5 text-muted-foreground/50 transition-transform duration-300"
            :class="{ 'rotate-180': expandedSlots.includes(slot.id) }"
          />
        </button>

        <div
          v-show="expandedSlots.includes(slot.id)"
          class="border-t border-border/50 p-8 space-y-8 animate-in zoom-in-95 fade-in duration-300"
        >
          <div class="p-6 bg-muted/20 rounded-2xl border border-border/50">
            <label class="text-[11px] font-black uppercase tracking-tighter text-muted-foreground/70 mb-3 block">{{ t('publishing.theme_customizer.editor.bindings.source_title') }}</label>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
              <button
                v-for="src in ['static', 'api_posts', 'api_pages', 'api_categories']"
                :key="src"
                class="px-4 py-3 rounded-xl border text-[11px] font-bold transition-all flex flex-col items-center gap-2 hover:translate-y-[-2px]"
                :class="getSlotConfig(activeBindingComponentId, slot.id).sourceType === src
                  ? 'bg-primary border-primary text-primary-foreground shadow-lg shadow-primary/30'
                  : 'bg-background border-border/50 hover:border-primary/50 text-muted-foreground hover:text-foreground'"
                @click="updateBinding(activeBindingComponentId, slot.id, 'sourceType', src)"
              >
                <component
                  :is="getSourceIcon(src)"
                  class="w-5 h-5"
                />
                {{ getSourceLabel(src) }}
              </button>
            </div>
          </div>

          <div
            v-if="getSlotConfig(selectedItem.bindingComponent.id, slot.id).sourceType !== 'static'"
            class="space-y-8"
          >
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 ring-1 ring-border/50 p-6 rounded-2xl bg-muted/10">
              <div class="space-y-4">
                <h5 class="text-[11px] font-black text-foreground uppercase tracking-wider flex items-center gap-2">
                  <Filter class="w-3 h-3 text-primary" />
                  {{ t('publishing.theme_customizer.editor.bindings.query_title') }}
                </h5>

                <div
                  v-if="getSlotConfig(selectedItem.bindingComponent.id, slot.id).sourceType === 'api_posts'"
                  class="grid grid-cols-1 gap-4"
                >
                  <div class="space-y-1.5">
                    <label class="text-[10px] font-bold text-muted-foreground uppercase">{{ t('common.labels.category') }}</label>
                    <Select
                      :model-value="String(getSlotConfig(selectedItem.bindingComponent.id, slot.id).categoryFilter || 'all')"
                      @update:model-value="(val: string | number) => updateBinding(activeBindingComponentId, slot.id, 'categoryFilter', val)"
                    >
                      <SelectTrigger class="w-full h-8 text-xs bg-background">
                        <SelectValue :placeholder="t('common.placeholders.select')" />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem value="all">
                          {{ t('common.labels.all') }}
                        </SelectItem>
                        <SelectItem
                          v-for="cat in categories"
                          :key="cat.id"
                          :value="cat.slug"
                        >
                          {{ cat.name }}
                        </SelectItem>
                      </SelectContent>
                    </Select>
                  </div>
                  <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                      <div class="flex items-center justify-between mb-4">
                        <label class="text-[10px] font-bold text-muted-foreground uppercase">{{ t('common.labels.limit') }}</label>
                        <span class="text-[10px] text-muted-foreground font-mono">{{ t('publishing.theme_customizer.editor.bindings.items_found') }}</span>
                      </div>
                      <div class="flex items-center gap-3">
                        <Input
                          :model-value="String(getSlotConfig(selectedItem.bindingComponent.id, slot.id).limit || 5)"
                          @update:model-value="(val: string | number) => updateBinding(activeBindingComponentId, slot.id, 'limit', Number(val || 5))"
                          type="number"
                          class="h-8 text-xs bg-background"
                          min="1"
                          max="50"
                        />
                      </div>
                    </div>
                    <div class="space-y-1.5">
                      <label class="text-[10px] font-bold text-muted-foreground uppercase">{{ t('common.labels.sort') }}</label>
                      <Select
                        :model-value="String(getSlotConfig(selectedItem.bindingComponent.id, slot.id).orderBy || 'published_at')"
                        @update:model-value="(val: string | number) => updateBinding(activeBindingComponentId, slot.id, 'orderBy', val)"
                      >
                        <SelectTrigger class="w-full h-8 text-xs bg-background">
                          <SelectValue :placeholder="t('publishing.theme_customizer.editor.bindings.sort_options.latest')" />
                        </SelectTrigger>
                        <SelectContent>
                          <SelectItem value="published_at">
                            {{ t('publishing.theme_customizer.editor.bindings.sort_options.published_at') }}
                          </SelectItem>
                          <SelectItem value="title">
                            {{ t('publishing.theme_customizer.editor.bindings.sort_options.title') }}
                          </SelectItem>
                          <SelectItem value="views">
                            {{ t('publishing.theme_customizer.editor.bindings.sort_options.views') }}
                          </SelectItem>
                        </SelectContent>
                      </Select>
                    </div>
                  </div>
                </div>

                <div
                  v-if="getSlotConfig(selectedItem.bindingComponent.id, slot.id).sourceType === 'api_pages'"
                  class="space-y-1.5"
                >
                  <label class="text-[10px] font-bold text-muted-foreground uppercase">{{ t('common.labels.page') }}</label>
                  <Select
                    :model-value="String(getSlotConfig(selectedItem.bindingComponent.id, slot.id).pageSlug || '')"
                    @update:model-value="(val: string | number) => updateBinding(activeBindingComponentId, slot.id, 'pageSlug', val)"
                  >
                    <SelectTrigger class="w-full h-8 text-xs bg-background">
                      <SelectValue :placeholder="t('common.placeholders.select')" />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem
                        v-for="page in pages"
                        :key="page.slug || page.id"
                        :value="String(page.slug || '')"
                      >
                        {{ page.title }}
                      </SelectItem>
                    </SelectContent>
                  </Select>
                </div>
              </div>

              <div
                v-if="slot.props.length > 0"
                class="space-y-4"
              >
                <h5 class="text-[11px] font-black text-foreground uppercase tracking-wider flex items-center gap-2">
                  <Link2 class="w-3 h-3 text-primary" />
                  {{ t('publishing.theme_customizer.editor.bindings.mapping_title') }}
                </h5>
                <div class="space-y-2 max-h-[160px] overflow-y-auto pr-2 custom-scrollbar">
                  <div
                    v-for="prop in slot.props"
                    :key="prop.key"
                    class="flex items-center gap-3 p-2 bg-background border rounded-lg hover:border-primary/30 transition-colors"
                  >
                    <div class="w-1.5 h-1.5 rounded-full bg-primary/40 shrink-0" />
                    <span
                      class="text-[11px] font-bold text-muted-foreground/80 truncate w-32"
                      :title="prop.label"
                    >{{ prop.label }}</span>
                    <ArrowRight class="w-3 h-3 text-muted-foreground/20" />
                    <Select
                      :model-value="(selectedItem?.bindingComponent && getSlotConfig(selectedItem.bindingComponent.id, slot.id).propMapping?.[prop.key]) || ''"
                      @update:model-value="(val: string) => updatePropMapping(slot.id, prop.key, val)"
                    >
                      <SelectTrigger class="h-7 border-0 bg-muted/40 text-[10px]">
                        <SelectValue :placeholder="t('publishing.theme_customizer.editor.bindings.field_placeholder')" />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem
                          v-for="field in getFieldsForSource(getSlotConfig(activeBindingComponentId, slot.id).sourceType)"
                          :key="field.value"
                          :value="field.value"
                        >
                          {{ field.label }}
                        </SelectItem>
                      </SelectContent>
                    </Select>
                  </div>
                </div>
              </div>
            </div>

            <div class="pt-2">
              <Button
                variant="ghost"
                size="sm"
                :disabled="previewLoading === slot.id"
                class="h-10 text-[11px] font-black tracking-widest hover:text-primary transition-colors group inline-flex items-center gap-2"
                @click="previewSlotData(slot.id)"
              >
                <Eye data-icon="inline-start" class="size-4 shrink-0 group-hover:scale-125 transition-transform" />
                {{ t('publishing.theme_customizer.editor.bindings.probe_button') }}
                <Loader2
                  v-if="previewLoading === slot.id"
                  class="ml-2 w-3 h-3 animate-spin"
                />
              </Button>

              <div
                v-if="previewResults[slot.id]"
                class="mt-4 bg-black/90 rounded-2xl p-6 relative overflow-hidden group/card shadow-inner"
              >
                <div class="absolute inset-0 bg-primary/5 pointer-events-none" />
                <div class="flex items-center justify-between mb-4 relative z-10 border-b border-white/10 pb-2">
                  <div class="flex items-center gap-2">
                    <div class="w-2 h-2 rounded-full bg-green-500 shadow-[0_0_8px_rgba(34,197,94,0.6)]" />
                    <span class="text-[10px] font-mono font-bold text-green-500 uppercase">{{ t('publishing.theme_customizer.editor.bindings.inspector_title') }}</span>
                  </div>
                  <span class="text-[10px] font-mono text-white/40 uppercase">{{ previewResults[slot.id]?.length ?? 0 }} {{ t('publishing.theme_customizer.editor.bindings.items_found') }}</span>
                </div>

                <div class="space-y-4 max-h-60 overflow-y-auto custom-scrollbar pr-2 relative z-10">
                  <div
                    v-for="(item, idx) in previewResults[slot.id]"
                    :key="idx"
                    class="group/item flex items-start gap-4 p-3 rounded-xl hover:bg-white/5 transition-colors border border-transparent hover:border-white/5"
                  >
                    <div class="w-6 h-6 rounded bg-white/10 flex items-center justify-center text-[10px] font-mono text-white/50">
                      {{ idx + 1 }}
                    </div>
                    <div class="flex-1 min-w-0">
                      <p class="text-[11px] font-bold text-white mb-1 truncate">
                        {{ (item as any).title || (item as any).name || t('publishing.theme_customizer.editor.bindings.object_data') }}
                      </p>
                      <div class="flex flex-wrap gap-2">
                        <span
                          v-for="(val, field) in filterPreviewFields(item)"
                          :key="field"
                          class="text-[9px] font-mono bg-white/5 px-1.5 py-0.5 rounded text-white/30 border border-white/5"
                        >
                          <span class="text-white/60">{{ field }}:</span> {{ String(val).slice(0, 30) }}{{ String(val).length > 30 ? '...' : '' }}
                        </span>
                      </div>
                    </div>
                  </div>
                </div>
                <button
                  class="absolute bottom-4 right-6 text-[10px] font-bold text-white/20 hover:text-white/90 transition-colors uppercase tracking-widest"
                  @click="$emit('clear-preview', slot.id)"
                >
                  {{ t('publishing.theme_customizer.editor.bindings.clear_buffer') }}
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { useI18n } from 'vue-i18n'
import { Button, Input, Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/shared/components/ui'
import {
  ArrowRight,
  ChevronDown,
  Database,
  Eye,
  Filter,
  Link2,
  Loader2,
} from 'lucide-vue-next';

const props = defineProps<{
  selectedItem: any;
  organizationMode: 'design' | 'bindings' | 'advanced';
  expandedSlots: string[];
  activeBindingComponentId: string;
  categories: any[];
  pages: any[];
  previewLoading: string | null;
  previewResults: Record<string, any[]>;
  toggleSlot: (slotId: string) => void;
  getSlotConfig: (componentId: string, slotId: string) => any;
  getSourceLabel: (src: string) => string;
  getSourceIcon: (src: string) => any;
  updateBinding: (componentId: string, slotId: string, field: string, value: any) => void;
  getFieldsForSource: (src: string) => Array<{ value: string; label: string }>;
  filterPreviewFields: (item: any) => Record<string, any>;
  previewSlotData: (slotId: string) => void;
  saveHistory: () => void;
}>()

defineEmits<{
  (e: 'clear-preview', id: string): void;
}>()

const { t } = useI18n()

function updatePropMapping(slotId: string, propKey: string, value: string) {
  if (!props.selectedItem?.bindingComponent) return
  const cfg = props.getSlotConfig(props.selectedItem.bindingComponent.id, slotId)
  if (!cfg.propMapping) cfg.propMapping = {}
  cfg.propMapping[propKey] = value
  props.saveHistory()
}
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: hsl(var(--muted-foreground) / 0.3);
  border-radius: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: hsl(var(--muted-foreground) / 0.5);
}
</style>
