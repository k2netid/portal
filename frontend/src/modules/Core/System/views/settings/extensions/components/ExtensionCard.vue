<template>
  <Card class="group relative flex flex-col justify-between overflow-hidden bg-card/80 dark:bg-card/20 backdrop-blur-md border border-border/70 dark:border-border/50 hover:border-indigo-500/50 dark:hover:border-indigo-500/40 transition-all duration-300 rounded-xl hover:shadow-lg hover:shadow-indigo-500/5 dark:hover:shadow-indigo-500/2">
    <CardContent class="p-6 flex-1">
      <!-- Card Header Info -->
      <div class="flex items-start justify-between gap-4">
        <div class="flex items-center gap-3">
          <div :class="['p-2.5 rounded-xl transition-colors', ext.type === 'module' ? 'bg-indigo-500/10 text-indigo-400' : 'bg-emerald-500/10 text-emerald-400']">
            <Layers
              v-if="ext.type === 'module'"
              class="w-5 h-5"
            />
            <Puzzle
              v-else
              class="w-5 h-5"
            />
          </div>
          <div>
            <h3 class="font-bold text-foreground text-base tracking-tight leading-none flex items-center gap-1.5">
              {{ ext.name }}
              <span class="text-xs text-muted-foreground font-normal">v{{ ext.version }}</span>
            </h3>
            <span class="text-xs text-indigo-600/90 dark:text-indigo-300 font-mono mt-1 block">{{ ext.slug }}</span>
          </div>
        </div>

        <div class="flex flex-col items-end gap-1.5 shrink-0">
          <Badge :variant="ext.status === 'active' ? 'success' : 'secondary'">
            {{ ext.status === 'active' ? t('system.appStore.card.statusActive') : t('system.appStore.card.statusInactive') }}
          </Badge>
          
          <span 
            v-if="getExtensionLicenseTier(ext.slug) === 'free'"
            class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-slate-500/10 text-slate-400 border border-slate-500/20"
          >
            {{ t('system.appStore.card.licenseCommunity') }}
          </span>
          <span 
            v-else-if="getExtensionLicenseTier(ext.slug) === 'pro'"
            class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-indigo-500/15 text-indigo-700 dark:text-indigo-300 border border-indigo-500/30"
          >
            {{ t('system.appStore.card.licensePro') }}
          </span>
          <span 
            v-else
            class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-amber-500/15 text-amber-500 dark:text-amber-400 border border-amber-500/30 shadow-sm shadow-amber-500/5 animate-pulse-subtle"
          >
            {{ t('system.appStore.card.licenseProPlus') }}
          </span>
        </div>
      </div>

      <!-- Dynamic Customized Description -->
      <p class="text-sm text-muted-foreground mt-4 line-clamp-2 min-h-[40px]" :title="getLocalizedDescription(ext)">
        {{ getLocalizedDescription(ext) }}
      </p>

      <!-- Meta Info Table -->
      <div class="mt-6 space-y-1.5 border-t border-border/30 pt-4 text-xs">
        <div class="flex justify-between">
          <span class="text-muted-foreground">{{ t('system.appStore.author') }}:</span>
          <span class="font-medium text-foreground">{{ ext.author || t('system.appStore.defaultAuthor') }}</span>
        </div>
        <div class="flex justify-between">
          <span class="text-muted-foreground">{{ t('system.appStore.license') }}:</span>
          <span class="font-mono text-foreground">
            {{ licenseLabel(ext.slug) }}
          </span>
        </div>
        <div class="flex justify-between" v-if="ext.is_core">
          <span class="text-muted-foreground">{{ t('system.appStore.core') }}:</span>
          <span class="text-indigo-400 font-semibold">{{ t('system.appStore.yes') }}</span>
        </div>
      </div>

      <!-- Shadcn Aligned Premium Features Accordion -->
      <div v-if="ext.features && ext.features.length > 0" class="mt-4 border-t border-border/30 pt-3">
        <Button
          variant="outline"
          size="sm"
          class="w-full flex items-center justify-between bg-secondary/35 hover:bg-secondary/70 text-xs border border-border/60 rounded-lg py-2.5 px-3 transition-all duration-200"
          @click="toggleFeaturesExpand"
        >
          <span class="flex items-center gap-2 font-semibold text-foreground">
            <Puzzle class="w-4 h-4 text-indigo-400 shrink-0" />
            {{ t('system.appStore.constituentFeatures') }} ({{ ext.features.length }})
          </span>
          <component
            :is="isExpanded ? ChevronUp : ChevronDown"
            class="w-4 h-4 text-muted-foreground/80 transition-transform duration-200"
          />
        </Button>

        <!-- Collapsible List grouped by Core Base and Operational Toggles -->
        <div
          v-show="isExpanded"
          class="mt-3 space-y-4 max-h-64 overflow-y-auto pr-1 transition-all duration-300"
        >
          <!-- Section 1: Mandatory Core Base Infrastructure -->
          <div v-if="groupedFeatures.core.length > 0" class="space-y-1.5">
            <div class="flex items-center justify-between text-[10px] font-bold uppercase tracking-wider text-muted-foreground/80 px-1 border-b border-border/20 pb-1">
              <span>{{ t('system.appStore.card.coreInfrastructure') }}</span>
              <span class="text-indigo-400 font-mono">{{ t('system.appStore.card.mandatoryService') }}</span>
            </div>
            
            <div
              v-for="feat in groupedFeatures.core"
              :key="feat.slug"
              class="flex items-center justify-between p-2.5 rounded-lg bg-secondary/15 border border-border/40 hover:bg-secondary/25 transition-all duration-200"
            >
              <div class="flex-1 min-w-0 pr-2">
                <div class="flex items-center gap-1.5">
                  <span class="text-xs font-semibold text-foreground/90 truncate">{{ feat.name }}</span>
                  <span class="w-1.5 h-1.5 rounded-full bg-indigo-400 shadow-sm shadow-indigo-500 shrink-0" />
                </div>
                <p class="text-[10px] text-muted-foreground mt-0.5 line-clamp-1" :title="feat.description">
                  {{ feat.description || t('system.appStore.card.featureDescCoreFallback') }}
                </p>
              </div>

              <!-- Read-only Locked Switch representing mandatory state -->
              <span class="text-[9px] font-bold text-indigo-400 uppercase tracking-widest bg-indigo-500/10 px-2 py-1 rounded">
                {{ t('system.appStore.card.featureActive') }}
              </span>
            </div>
          </div>

          <!-- Section 2: Operational Extensions & Dynamic Toggles -->
          <div v-if="groupedFeatures.operational.length > 0" class="space-y-1.5">
            <div class="flex items-center justify-between text-[10px] font-bold uppercase tracking-wider text-muted-foreground/80 px-1 border-b border-border/20 pb-1">
              <span>{{ t('system.appStore.card.operationalExtensions') }}</span>
              <span class="text-emerald-500">{{ t('system.appStore.card.activeToggleable') }}</span>
            </div>

            <div
              v-for="feat in groupedFeatures.operational"
              :key="feat.slug"
              class="flex items-center justify-between p-2.5 rounded-lg bg-secondary/25 border border-border/50 hover:border-indigo-500/20 hover:bg-secondary/35 transition-all duration-200"
            >
              <div class="flex-1 min-w-0 pr-2">
                <div class="flex items-center gap-1.5">
                  <span class="text-xs font-semibold text-foreground truncate">{{ feat.name }}</span>
                  <span :class="['w-1.5 h-1.5 rounded-full shrink-0', feat.is_active ? 'bg-emerald-500 shadow-sm shadow-emerald-500' : 'bg-rose-500 shadow-sm shadow-rose-500']" />
                </div>
                <p class="text-[10px] text-muted-foreground mt-0.5 line-clamp-1" :title="feat.description">
                  {{ feat.description || t('system.appStore.card.featureDescEmpty') }}
                </p>
              </div>

              <!-- Standard Shared Switch Component -->
              <Switch
                :checked="feat.is_active"
                :aria-label="feat.name"
                @update:checked="$emit('toggle-feature', feat)"
                class="data-[state=checked]:bg-emerald-500 focus-visible:ring-emerald-500/50"
              />
            </div>
          </div>
        </div>
      </div>
    </CardContent>

    <!-- Card Footer Actions -->
    <CardFooter class="px-6 py-4 bg-muted/20 border-t border-border/30 flex items-center justify-between gap-2">
      <div>
        <Button
          v-if="ext.status === 'active'"
          variant="secondary"
          size="sm"
          class="flex items-center gap-1.5 hover:bg-secondary/80"
          @click="$emit('configure', ext)"
        >
          <Settings class="w-3.5 h-3.5" />
          {{ t('system.appStore.configure') }}
        </Button>
      </div>

      <div class="flex items-center gap-2">
        <!-- Uninstall Inactive Non-Core -->
        <Button
          v-if="ext.status === 'inactive' && !ext.is_core"
          variant="destructive"
          size="sm"
          class="px-2 hover:bg-destructive/90"
          :aria-label="t('system.appStore.uninstall')"
          @click="$emit('uninstall', ext.slug)"
        >
          <Trash2 class="w-4 h-4" />
        </Button>

        <!-- Activate / Deactivate Toggle -->
        <Button
          v-if="!ext.is_core"
          :variant="ext.status === 'active' ? 'destructive' : 'secondary'"
          size="sm"
          class="flex items-center gap-1.5 font-semibold text-xs border-0"
          @click="$emit('toggle-status', ext)"
        >
          <Power class="w-3.5 h-3.5" />
          {{ ext.status === 'active' ? t('system.appStore.deactivate') : t('system.appStore.activate') }}
        </Button>
        <span
          v-else
          class="text-xs text-muted-foreground/80 font-semibold uppercase tracking-wider"
        >
          {{ t('system.appStore.locked') }}
        </span>
      </div>
    </CardFooter>
  </Card>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { Card, CardContent, CardFooter, Badge, Button, Switch } from '@/shared/components/ui';

const { t } = useI18n();

// Lucide icons
import {
  ChevronDown,
  ChevronUp,
  Layers,
  Power,
  Puzzle,
  Settings,
  Trash2,
} from 'lucide-vue-next';

interface FeatureItem {
  id: string;
  extension_slug: string;
  slug: string;
  name: string;
  description?: string;
  category: string;
  is_active: boolean;
}

interface ExtensionItem {
  id: string;
  slug: string;
  type: 'module' | 'plugin';
  name: string;
  version: string;
  status: 'active' | 'inactive';
  is_core: boolean;
  author?: string;
  license?: string;
  settings?: Record<string, unknown>;
  features?: FeatureItem[];
}

const props = defineProps<{
  ext: ExtensionItem;
  getLocalizedDescription: (ext: ExtensionItem) => string;
}>();

defineEmits<{
  (e: 'toggle-feature', feature: FeatureItem): void;
  (e: 'toggle-status', ext: ExtensionItem): void;
  (e: 'configure', ext: ExtensionItem): void;
  (e: 'uninstall', slug: string): void;
}>();

const isExpanded = ref(false);
const toggleFeaturesExpand = () => {
  isExpanded.value = !isExpanded.value;
};

// Advanced dynamic categorization for mandatory vs toggleable extension features
const groupedFeatures = computed(() => {
  if (!props.ext.features) return { core: [], operational: [] };
  
  const core: FeatureItem[] = [];
  const operational: FeatureItem[] = [];
  
  props.ext.features.forEach(feat => {
    const isCore = feat.category === 'core' || feat.category === 'auth' || feat.category === 'security' || props.ext.is_core || ['core', 'system', 'security', 'infra'].includes(props.ext.slug);
    if (isCore) {
      core.push(feat);
    } else {
      operational.push(feat);
    }
  });
  
  return { core, operational };
});

// License tiers for registry badges (kernel vs first-party vs legacy CMS leftovers)
const getExtensionLicenseTier = (slug: string): 'free' | 'pro' | 'pro_plus' => {
  const tierMap: Record<string, 'free' | 'pro' | 'pro_plus'> = {
    core: 'free',
    system: 'free',
    search: 'free',
    mail: 'pro',
    media: 'pro',
    Jejakawan: 'pro',
    forms: 'pro',
    newsletter: 'pro',
    library: 'pro',
    security: 'pro_plus',
    infra: 'pro_plus',
    ai: 'pro_plus',
  };
  return tierMap[slug] || 'pro';
};

const licenseLabel = (slug: string) => {
  if (slug === 'core' || props.ext.is_core) {
    return t('system.appStore.card.licensePlatform');
  }
  const tier = getExtensionLicenseTier(slug);
  if (tier === 'free') return t('system.appStore.card.licenseMit');
  if (tier === 'pro') return t('system.appStore.card.licenseCommercialPro');
  return t('system.appStore.card.licenseEnterprise');
};
</script>
