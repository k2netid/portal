<template>
  <Dialog
    :open="isOpen"
    @update:open="v => { if(!v) $emit('close') }"
  >
    <DialogContent class="!p-0 !gap-0 max-w-lg h-[580px] max-h-[90vh] flex flex-col overflow-hidden rounded-2xl border border-border/80 bg-card shadow-2xl [&>button[aria-label=Close]]:hidden">
      <!-- Header -->
      <div class="h-12 px-4 bg-muted/40 border-b border-border/40 flex items-center justify-between select-none shrink-0">
        <DialogTitle class="text-xs font-bold text-foreground flex items-center gap-2">
          <Menu class="w-3.5 h-3.5 text-primary" />
          <span>{{ isEditing ? $t('system.navigation.menuEditor.editItem') : $t('system.navigation.menuEditor.addItem') }}</span>
        </DialogTitle>

        <Button
          variant="ghost"
          size="icon"
          class="h-7 w-7 text-muted-foreground hover:text-foreground rounded-lg"
          @click="$emit('close')"
        >
          <X class="w-4 h-4" />
        </Button>
      </div>

      <!-- Scrollable Form Body -->
      <form class="flex-1 overflow-y-auto p-4 space-y-4 custom-scrollbar min-h-0" @submit.prevent="handleSubmit">
        <!-- Basic Info Section -->
        <div class="space-y-3 p-3 rounded-xl bg-muted/20 border border-border/40">
          <h4 class="text-[11px] font-bold uppercase tracking-wider text-muted-foreground">
            {{ $t('system.navigation.menuEditor.basicInfo') }}
          </h4>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <!-- Name -->
            <div class="space-y-1">
              <label class="text-xs font-medium text-foreground">Menu Name *</label>
              <Input
                v-model="formData.name"
                placeholder="e.g. Mailbox, Invoices"
                class="h-8 text-xs"
                required
              />
            </div>

            <!-- Group Slug -->
            <div class="space-y-1">
              <label class="text-xs font-medium text-foreground">Group / Category *</label>
              <Select v-model="formData.group_slug">
                <SelectTrigger class="h-8 text-xs">
                  <SelectValue placeholder="Select Group" />
                </SelectTrigger>
                <SelectContent class="z-[1300] text-xs">
                  <SelectItem
                    v-for="grp in groupOptions"
                    :key="grp.slug"
                    :value="grp.slug"
                  >
                    {{ grp.name }}
                  </SelectItem>
                </SelectContent>
              </Select>
            </div>
          </div>

          <!-- Parent Menu Selector -->
          <div class="space-y-1">
            <label class="text-xs font-medium text-foreground">Parent Menu (Nesting)</label>
            <Select v-model="selectedParentId">
              <SelectTrigger class="h-8 text-xs">
                <SelectValue placeholder="None (Top Level Root Item)" />
              </SelectTrigger>
              <SelectContent class="z-[1300] text-xs max-h-48">
                <SelectItem value="none">
                  None (Top Level Root Item)
                </SelectItem>
                <SelectItem
                  v-for="pm in availableParents"
                  :key="pm.id"
                  :value="pm.id"
                >
                  {{ pm.name }} ({{ pm.group_slug }})
                </SelectItem>
              </SelectContent>
            </Select>
          </div>
        </div>

        <!-- Target Destination Section -->
        <div class="space-y-3 p-3 rounded-xl bg-muted/20 border border-border/40">
          <h4 class="text-[11px] font-bold uppercase tracking-wider text-muted-foreground">
            {{ $t('system.navigation.menuEditor.destination') }}
          </h4>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div class="space-y-1">
              <label class="text-xs font-medium text-foreground">Vue Route Name</label>
              <Input
                v-model="formData.route_name"
                placeholder="e.g. mail, users.index"
                class="h-8 text-xs"
              />
            </div>
            <div class="space-y-1">
              <label class="text-xs font-medium text-foreground">Or External URL</label>
              <Input
                v-model="formData.url"
                placeholder="https://example.com"
                class="h-8 text-xs"
              />
            </div>
          </div>

          <!-- Icon Picker -->
          <div class="space-y-1">
            <label class="text-xs font-medium text-foreground">Icon</label>
            <IconPicker
              v-model="formData.icon"
              placeholder-text="Choose Lucide Icon"
            />
          </div>
        </div>

        <!-- RBAC & Gating Section -->
        <div class="space-y-3 p-3 rounded-xl bg-muted/20 border border-border/40">
          <h4 class="text-[11px] font-bold uppercase tracking-wider text-muted-foreground">
            {{ $t('system.navigation.menuEditor.accessGating') }}
          </h4>

          <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <!-- Permission -->
            <div class="space-y-1">
              <label class="text-xs font-medium text-foreground">RBAC Permission</label>
              <Input
                v-model="formData.permission"
                placeholder="e.g. manage system"
                class="h-8 text-xs"
              />
            </div>

            <!-- Role -->
            <div class="space-y-1">
              <label class="text-xs font-medium text-foreground">Role Required</label>
              <Select v-model="selectedRole">
                <SelectTrigger class="h-8 text-xs">
                  <SelectValue placeholder="Any Role" />
                </SelectTrigger>
                <SelectContent class="z-[1300] text-xs">
                  <SelectItem value="none">
                    Any Role (Public to permitted)
                  </SelectItem>
                  <SelectItem value="super">
                    Super Admin Only
                  </SelectItem>
                  <SelectItem value="admin">
                    Admin & Super
                  </SelectItem>
                  <SelectItem value="editor">
                    Editor & Above
                  </SelectItem>
                </SelectContent>
              </Select>
            </div>

            <!-- Extension Slug -->
            <div class="space-y-1">
              <label class="text-xs font-medium text-foreground">Extension Slug</label>
              <Input
                v-model="formData.extension_slug"
                placeholder="e.g. mail, ai, cms"
                class="h-8 text-xs"
                title="Auto-hides menu if this extension is deactivated"
              />
            </div>
          </div>
        </div>

        <!-- Badges & Visibility Section -->
        <div class="space-y-3 p-3 rounded-xl bg-muted/20 border border-border/40">
          <h4 class="text-[11px] font-bold uppercase tracking-wider text-muted-foreground">
            {{ $t('system.navigation.menuEditor.badgeStyle') }}
          </h4>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 items-center">
            <div class="space-y-1">
              <label class="text-xs font-medium text-foreground">Badge Text</label>
              <Input
                v-model="formData.badge_text"
                placeholder="e.g. PRO, NEW, BETA"
                class="h-8 text-xs uppercase font-bold"
              />
            </div>

            <div class="space-y-1">
              <label class="text-xs font-medium text-foreground">Badge Color Variant</label>
              <Select v-model="formData.badge_variant">
                <SelectTrigger class="h-8 text-xs">
                  <SelectValue placeholder="Primary" />
                </SelectTrigger>
                <SelectContent class="z-[1300] text-xs">
                  <SelectItem value="primary">Primary Blue/Violet</SelectItem>
                  <SelectItem value="emerald">Emerald Green (New)</SelectItem>
                  <SelectItem value="amber">Amber Yellow (Beta)</SelectItem>
                  <SelectItem value="rose">Rose Red (Hot/Alert)</SelectItem>
                  <SelectItem value="default">Default Neutral</SelectItem>
                </SelectContent>
              </Select>
            </div>
          </div>

          <div class="flex items-center justify-between pt-1">
            <div>
              <p class="text-xs font-medium text-foreground">Visible in Navigation</p>
              <p class="text-[10px] text-muted-foreground">Toggle menu visibility in console sidebar</p>
            </div>
            <Switch v-model="formData.is_visible" />
          </div>
        </div>
      </form>

      <!-- Footer -->
      <div class="h-12 px-4 bg-muted/30 border-t border-border/40 flex items-center justify-end gap-2 shrink-0">
        <Button
          type="button"
          variant="ghost"
          size="sm"
          class="h-8 text-xs"
          @click="$emit('close')"
        >
          Cancel
        </Button>
        <Button
          type="button"
          size="sm"
          class="h-8 text-xs font-semibold px-4 shadow-xs"
          :disabled="!formData.name?.trim() || saving"
          @click="handleSubmit"
        >
          {{ isEditing ? 'Save Changes' : 'Create Menu' }}
        </Button>
      </div>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { ref, watch, computed } from 'vue';
import {
  Menu,
  X,
} from 'lucide-vue-next';
import {
  Dialog,
  DialogContent,
  DialogTitle,
  Button,
  Input,
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
  Switch,
  IconPicker,
} from '@/shared/components/ui';
import type { ConsoleMenuItem } from '@/modules/Core/System/composables/useConsoleMenu';

const props = withDefaults(defineProps<{
    isOpen: boolean;
    menu: ConsoleMenuItem | null;
    rootMenus: ConsoleMenuItem[];
    groups?: Array<{ slug: string; name: string; icon?: string }>;
    saving?: boolean;
}>(), {
    groups: () => [],
});

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'save', payload: Partial<ConsoleMenuItem>, id?: string): void;
}>();

const isEditing = computed(() => Boolean(props.menu?.id));

const groupOptions = computed(() => {
    const fromProps = (Array.isArray(props.groups) ? props.groups : []).filter((g) => g && g.slug && g.slug !== 'all');
    if (fromProps.length > 0) {
        return fromProps;
    }
    return [
        { slug: 'editorial', name: 'Editorial' },
        { slug: 'insight', name: 'Insight' },
        { slug: 'library', name: 'Library' },
        { slug: 'audience', name: 'Audience' },
        { slug: 'identity', name: 'Users & Access' },
        { slug: 'communications', name: 'Communications' },
        { slug: 'observability', name: 'Journals' },
        { slug: 'system_config', name: 'Configuration' },
        { slug: 'infrastructure', name: 'Infrastructure' },
        { slug: 'integrations_dev', name: 'Identity & Integrations' },
    ];
});

const selectedParentId = ref('none');
const selectedRole = ref('none');

interface MenuFormData {
    name: string;
    group_slug: string;
    parent_id: string | null;
    route_name: string;
    url: string;
    icon: string;
    permission: string;
    role: string | null;
    extension_slug: string;
    badge_text: string;
    badge_variant: 'default' | 'primary' | 'amber' | 'emerald' | 'rose';
    is_visible: boolean;
    order: number;
}

const formData = ref<MenuFormData>({
    name: '',
    group_slug: 'system_config',
    parent_id: null,
    route_name: '',
    url: '',
    icon: 'circle',
    permission: '',
    role: null,
    extension_slug: '',
    badge_text: '',
    badge_variant: 'primary',
    is_visible: true,
    order: 0,
});

const availableParents = computed(() => {
    return props.rootMenus.filter(m => !props.menu || m.id !== props.menu.id);
});

watch(() => props.menu, (newVal) => {
    if (newVal) {
        formData.value = {
            name: newVal.name || '',
            group_slug: newVal.group_slug || 'system_config',
            parent_id: newVal.parent_id || null,
            route_name: newVal.route_name || '',
            url: newVal.url || '',
            icon: newVal.icon || 'circle',
            permission: newVal.permission || '',
            role: newVal.role || null,
            extension_slug: newVal.extension_slug || '',
            badge_text: newVal.badge_text || '',
            badge_variant: newVal.badge_variant || 'primary',
            is_visible: Boolean(newVal.is_visible),
            order: newVal.order || 0,
        };
        selectedParentId.value = newVal.parent_id || 'none';
        selectedRole.value = newVal.role || 'none';
    } else {
        formData.value = {
            name: '',
            group_slug: 'system_config',
            parent_id: null,
            route_name: '',
            url: '',
            icon: 'circle',
            permission: '',
            role: null,
            extension_slug: '',
            badge_text: '',
            badge_variant: 'primary',
            is_visible: true,
            order: 0,
        };
        selectedParentId.value = 'none';
        selectedRole.value = 'none';
    }
}, { immediate: true });

const handleSubmit = () => {
    if (!formData.value.name?.trim()) return;

    const payload: Partial<ConsoleMenuItem> = {
        ...formData.value,
        parent_id: selectedParentId.value !== 'none' ? selectedParentId.value : null,
        role: selectedRole.value !== 'none' ? selectedRole.value : null,
    };

    emit('save', payload, props.menu?.id);
};
</script>
