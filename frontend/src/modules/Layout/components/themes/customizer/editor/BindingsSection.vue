<template>
  <section
    v-if="selectedItem?.bindingComponent && organizationMode === 'bindings'"
    class="space-y-4"
  >
    <div class="flex items-center justify-between gap-2 px-0.5 min-w-0">
      <h4 class="text-xs font-bold uppercase tracking-widest text-muted-foreground flex items-center gap-2 min-w-0">
        <Database class="w-3 h-3 text-primary shrink-0" />
        <span class="truncate">{{ t('publishing.theme_customizer.editor.sections.bindings') }}</span>
      </h4>
      <span
        v-if="!panelMode"
        class="text-[10px] text-primary/70 font-mono shrink-0"
      >{{ t('publishing.theme_customizer.editor.bindings.subtitle') }}</span>
    </div>

    <div class="space-y-3">
      <div
        v-for="slot in selectedItem.bindingComponent.slots"
        :key="slot.id"
        class="bg-card border border-border rounded-xl overflow-hidden shadow-sm transition-all hover:shadow-md min-w-0"
      >
        <button
          type="button"
          class="w-full flex items-center justify-between gap-3 hover:bg-muted/30 transition-colors group min-w-0"
          :class="panelMode ? 'p-3.5' : 'p-6'"
          @click="toggleSlot(slot.id)"
        >
          <div class="flex items-center gap-3 min-w-0">
            <div
              class="rounded-xl flex items-center justify-center text-xs font-black shadow-inner transition-colors shrink-0"
              :class="[
                panelMode ? 'w-8 h-8' : 'w-10 h-10',
                getSlotConfig(selectedItem.bindingComponent.id, slot.id).sourceType !== 'static'
                  ? 'bg-primary text-primary-foreground'
                  : 'bg-muted text-muted-foreground',
              ]"
            >
              {{ slot.id.charAt(0).toUpperCase() }}{{ slot.id.charAt(1) || '' }}
            </div>
            <div class="text-left min-w-0">
              <span
                class="font-bold tracking-tight block truncate"
                :class="panelMode ? 'text-sm' : 'text-base'"
              >{{ slot.label }}</span>
              <div class="flex items-center gap-2 mt-0.5 min-w-0">
                <span class="text-[10px] uppercase font-bold text-muted-foreground/50 shrink-0">{{ t('publishing.theme_customizer.editor.bindings.source_label') }}</span>
                <span
                  class="text-[10px] uppercase font-black truncate"
                  :class="getSlotConfig(selectedItem.bindingComponent.id, slot.id).sourceType !== 'static' ? 'text-primary' : 'text-muted-foreground'"
                >
                  {{ getSourceLabel(getSlotConfig(selectedItem.bindingComponent.id, slot.id).sourceType) }}
                </span>
              </div>
            </div>
          </div>
          <ChevronDown
            class="w-5 h-5 text-muted-foreground/50 transition-transform duration-300 shrink-0"
            :class="{ 'rotate-180': expandedSlots.includes(slot.id) }"
          />
        </button>

        <div
          v-show="expandedSlots.includes(slot.id)"
          class="border-t border-border/50 space-y-5 animate-in zoom-in-95 fade-in duration-300 min-w-0"
          :class="panelMode ? 'p-3.5 space-y-4' : 'p-8 space-y-8'"
        >
          <div
            class="rounded-xl border border-border/50 bg-muted/20 min-w-0"
            :class="panelMode ? 'p-3' : 'p-6'"
          >
            <label class="text-[11px] font-black uppercase tracking-tighter text-muted-foreground/70 mb-2.5 block">
              {{ t('publishing.theme_customizer.editor.bindings.source_title') }}
            </label>
            <div
              class="grid gap-2"
              :class="panelMode ? 'grid-cols-2' : 'grid-cols-2 md:grid-cols-4 gap-3'"
            >
              <button
                v-for="src in ['static', 'api_posts', 'api_pages', 'api_categories']"
                :key="src"
                type="button"
                class="rounded-xl border text-[10px] font-bold transition-all flex flex-col items-center gap-1.5 min-w-0"
                :class="[
                  panelMode ? 'px-2 py-2.5' : 'px-4 py-3 text-[11px] gap-2 hover:translate-y-[-2px]',
                  getSlotConfig(activeBindingComponentId, slot.id).sourceType === src
                    ? 'bg-primary border-primary text-primary-foreground shadow-lg shadow-primary/30'
                    : 'bg-background border-border/50 hover:border-primary/50 text-muted-foreground hover:text-foreground',
                ]"
                @click="updateBinding(activeBindingComponentId, slot.id, 'sourceType', src)"
              >
                <component
                  :is="getSourceIcon(src)"
                  :class="panelMode ? 'w-4 h-4' : 'w-5 h-5'"
                />
                <span class="truncate max-w-full">{{ getSourceLabel(src) }}</span>
              </button>
            </div>
          </div>

          <div
            v-if="getSlotConfig(selectedItem.bindingComponent.id, slot.id).sourceType !== 'static'"
            class="space-y-5 min-w-0"
            :class="panelMode ? 'space-y-4' : 'space-y-8'"
          >
            <!-- Always stack in panel; side-by-side only in wide canvas -->
            <div
              class="ring-1 ring-border/50 rounded-xl bg-muted/10 min-w-0"
              :class="panelMode
                ? 'grid grid-cols-1 gap-5 p-3.5'
                : 'grid grid-cols-1 lg:grid-cols-2 gap-8 p-6'"
            >
              <div class="space-y-3 min-w-0">
                <h5 class="text-[11px] font-black text-foreground uppercase tracking-wider flex items-center gap-2">
                  <Filter class="w-3 h-3 text-primary shrink-0" />
                  {{ t('publishing.theme_customizer.editor.bindings.query_title') }}
                </h5>

                <div
                  v-if="getSlotConfig(selectedItem.bindingComponent.id, slot.id).sourceType === 'api_posts'"
                  class="grid grid-cols-1 gap-3"
                >
                  <div class="space-y-1.5 min-w-0">
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

                  <div
                    class="grid gap-3 min-w-0"
                    :class="panelMode ? 'grid-cols-1' : 'grid-cols-2'"
                  >
                    <div class="space-y-1.5 min-w-0">
                      <label class="text-[10px] font-bold text-muted-foreground uppercase block">
                        {{ t('common.labels.limit') }}
                      </label>
                      <Input
                        :model-value="String(getSlotConfig(selectedItem.bindingComponent.id, slot.id).limit || 5)"
                        type="number"
                        class="w-full h-8 text-xs bg-background"
                        min="1"
                        max="50"
                        @update:model-value="(val: string | number) => updateBinding(activeBindingComponentId, slot.id, 'limit', Number(val || 5))"
                      />
                    </div>
                    <div class="space-y-1.5 min-w-0">
                      <label class="text-[10px] font-bold text-muted-foreground uppercase block">
                        {{ t('common.labels.sort') }}
                      </label>
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
                  class="space-y-1.5 min-w-0"
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
                class="space-y-3 min-w-0"
              >
                <h5 class="text-[11px] font-black text-foreground uppercase tracking-wider flex items-center gap-2">
                  <Link2 class="w-3 h-3 text-primary shrink-0" />
                  {{ t('publishing.theme_customizer.editor.bindings.mapping_title') }}
                </h5>
                <div
                  class="space-y-2 overflow-y-auto pr-1 custom-scrollbar"
                  :class="panelMode ? 'max-h-[220px]' : 'max-h-[160px]'"
                >
                  <div
                    v-for="prop in slot.props"
                    :key="prop.key"
                    class="flex flex-col gap-1.5 p-2.5 bg-background border rounded-lg hover:border-primary/30 transition-colors min-w-0"
                  >
                    <div class="flex items-center gap-2 min-w-0">
                      <div class="w-1.5 h-1.5 rounded-full bg-primary/40 shrink-0" />
                      <span
                        class="text-[11px] font-bold text-muted-foreground/80 truncate min-w-0"
                        :title="prop.label"
                      >{{ prop.label }}</span>
                    </div>
                    <Select
                      :model-value="(selectedItem?.bindingComponent && getSlotConfig(selectedItem.bindingComponent.id, slot.id).propMapping?.[prop.key]) || ''"
                      @update:model-value="(val: string) => updatePropMapping(slot.id, prop.key, val)"
                    >
                      <SelectTrigger class="h-8 w-full border bg-muted/40 text-[10px]">
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

            <div class="pt-1 min-w-0">
              <Button
                variant="ghost"
                size="sm"
                :disabled="previewLoading === slot.id"
                class="h-9 text-[11px] font-black tracking-widest hover:text-primary transition-colors group inline-flex items-center gap-2"
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
                class="mt-3 bg-black/90 rounded-xl p-4 relative overflow-hidden group/card shadow-inner min-w-0"
              >
                <div class="absolute inset-0 bg-primary/5 pointer-events-none" />
                <div class="flex items-center justify-between gap-2 mb-3 relative z-10 border-b border-white/10 pb-2 min-w-0">
                  <div class="flex items-center gap-2 min-w-0">
                    <div class="w-2 h-2 rounded-full bg-green-500 shadow-[0_0_8px_rgba(34,197,94,0.6)] shrink-0" />
                    <span class="text-[10px] font-mono font-bold text-green-500 uppercase truncate">{{ t('publishing.theme_customizer.editor.bindings.inspector_title') }}</span>
                  </div>
                  <span class="text-[10px] font-mono text-white/40 uppercase shrink-0">{{ previewResults[slot.id]?.length ?? 0 }} {{ t('publishing.theme_customizer.editor.bindings.items_found') }}</span>
                </div>

                <div class="space-y-3 max-h-60 overflow-y-auto custom-scrollbar pr-1 relative z-10">
                  <div
                    v-for="(item, idx) in previewResults[slot.id]"
                    :key="idx"
                    class="group/item flex items-start gap-3 p-2.5 rounded-xl hover:bg-white/5 transition-colors border border-transparent hover:border-white/5 min-w-0"
                  >
                    <div class="w-6 h-6 rounded bg-white/10 flex items-center justify-center text-[10px] font-mono text-white/50 shrink-0">
                      {{ idx + 1 }}
                    </div>
                    <div class="flex-1 min-w-0">
                      <p class="text-[11px] font-bold text-white mb-1 truncate">
                        {{ (item as any).title || (item as any).name || t('publishing.theme_customizer.editor.bindings.object_data') }}
                      </p>
                      <div class="flex flex-wrap gap-1.5">
                        <span
                          v-for="(val, field) in filterPreviewFields(item)"
                          :key="field"
                          class="text-[9px] font-mono bg-white/5 px-1.5 py-0.5 rounded text-white/30 border border-white/5 max-w-full truncate"
                        >
                          <span class="text-white/60">{{ field }}:</span> {{ String(val).slice(0, 30) }}{{ String(val).length > 30 ? '...' : '' }}
                        </span>
                      </div>
                    </div>
                  </div>
                </div>
                <button
                  type="button"
                  class="mt-3 relative z-10 text-[10px] font-bold text-white/20 hover:text-white/90 transition-colors uppercase tracking-widest"
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
  ChevronDown,
  Database,
  Eye,
  Filter,
  Link2,
  Loader2,
} from 'lucide-vue-next'

const props = withDefaults(defineProps<{
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
  /** Narrow right-rail customizer — force single-column layouts. */
  panelMode?: boolean;
}>(), {
  panelMode: false,
})

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
