<template>
  <Card class="border-border shadow-sm">
    <CardHeader class="pb-3">
      <div class="flex items-center justify-between">
        <CardTitle class="text-base">
          {{ selectedItem ? t('layout.menus.form.editItemTitle') : t('layout.menus.form.settings') }}
        </CardTitle>
        <div class="flex items-center gap-1">
          <Button
            variant="ghost"
            size="icon"
            class="h-10 w-10"
            :aria-label="t('common.actions.collapse')"
            :title="t('common.actions.collapse')"
            @click="$emit('collapse')"
          >
            <PanelRightClose class="w-4 h-4" />
          </Button>
          <Button
            v-if="selectedItem"
            variant="ghost"
            size="icon"
            class="h-10 w-10"
            :aria-label="t('common.actions.deselect')"
            :title="t('common.actions.deselect')"
            @click="menuContext.clearSelection()"
          >
            <X class="w-4 h-4" />
          </Button>
        </div>
      </div>
    </CardHeader>
    <CardContent>
      <!-- No Selection -->
      <div
        v-if="!selectedItem"
        class="flex flex-col items-center justify-center py-8 text-center"
      >
        <MousePointer class="w-8 h-8 text-muted-foreground mb-3" />
        <p class="text-sm text-muted-foreground">
          {{ t('layout.menus.messages.selectItemToEdit') }}
        </p>
      </div>

      <!-- Properties Form -->
      <div
        v-else
        class="space-y-4"
      >
        <!-- Type Badge -->
        <div class="flex items-center gap-2 pb-2 border-b border-border">
          <component
            :is="iconComponent"
            class="w-4 h-4"
            :class="iconColorClass"
          />
          <Badge variant="outline">
            {{ typeLabel }}
          </Badge>
        </div>

        <!-- Dynamic Fields -->
        <div
          v-for="setting in settingsSchema"
          :key="setting.key"
          class="space-y-1.5"
        >
          <!-- Skip grouped fields, render them in groups -->
          <template v-if="!setting.group">
            <ItemPropertyField 
              :setting="setting"
              :value="selectedItem[setting.key]"
              @update="updateField(setting.key, $event)"
            />
          </template>
        </div>
                
        <!-- Parent Selector -->
        <div class="space-y-1.5 pt-2 border-t border-border">
          <Label class="text-xs text-muted-foreground">{{ t('layout.menus.form.parentItem') }}</Label>
          <Select
            :model-value="currentParentId"
            @update:model-value="handleParentChange"
          >
            <SelectTrigger
              :aria-label="t('layout.menus.form.parentItem')"
              class="h-10 text-sm"
            >
              <SelectValue :placeholder="t('layout.menus.form.placeholders.selectParent') || 'Select Parent'" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="root">
                {{ t('layout.menus.form.rootItem') }}
              </SelectItem>
              <SelectItem 
                v-for="p in validParents" 
                :key="p.id || p._temp_id" 
                :value="(p.id || p._temp_id)!.toString()"
              >
                <span :style="{ paddingLeft: (p._depth * 12) + 'px' }">
                  {{ p.title || 'Untitled' }}
                </span>
              </SelectItem>
            </SelectContent>
          </Select>
        </div>

        <!-- Grouped Fields -->
        <Accordion
          v-if="groupedSettings.length > 0 || selectedItem"
          type="multiple"
          class="w-full"
        >
          <!-- Visibility & Security Group -->
          <AccordionItem
            value="visibility_security"
          >
            <AccordionTrigger class="py-2 text-sm font-bold">
              {{ t('layout.menus.form.groups.visibility') || 'Visibility & Security' }}
            </AccordionTrigger>
            <AccordionContent class="pt-2 pb-4 space-y-4">
              <div class="flex items-center gap-2 pt-1">
                <Checkbox 
                  id="requires_auth"
                  :checked="Boolean(getMetadata('requires_auth'))"
                  @update:checked="updateMetadataField('requires_auth', $event)"
                />
                <Label
                  for="requires_auth"
                  class="text-xs font-normal cursor-pointer"
                >
                  {{ t('layout.menus.form.requiresAuth') || 'Hanya Pengguna Login (Requires Authentication)' }}
                </Label>
              </div>

              <div class="flex items-center gap-2">
                <Checkbox 
                  id="guest_only"
                  :checked="Boolean(getMetadata('guest_only'))"
                  @update:checked="updateMetadataField('guest_only', $event)"
                />
                <Label
                  for="guest_only"
                  class="text-xs font-normal cursor-pointer"
                >
                  {{ t('layout.menus.form.guestOnly') || 'Hanya Tamu Belum Login (Guest Only)' }}
                </Label>
              </div>

              <div class="space-y-1.5">
                <Label class="text-xs">{{ t('layout.menus.form.requiredPermission') || 'Required Permission' }}</Label>
                <Input 
                  :model-value="String(getMetadata('required_permission', ''))"
                  :placeholder="t('layout.menus.form.permissionPlaceholder')"
                  class="h-8"
                  @update:model-value="updateMetadataField('required_permission', $event)"
                />
                <p class="text-[10px] text-muted-foreground">
                  Hide this menu item if the user does not have this permission.
                </p>
              </div>
            </AccordionContent>
          </AccordionItem>

          <!-- Translations / Localization Group -->
          <AccordionItem
            value="localization"
          >
            <AccordionTrigger class="py-2 text-sm font-bold">
              {{ t('layout.menus.form.groups.localization') || 'Translations / Localization' }}
            </AccordionTrigger>
            <AccordionContent class="pt-2 pb-4 space-y-4">
              <div class="space-y-1.5">
                <Label class="text-xs">{{ t('layout.menus.form.titleEn') || 'Title (English)' }}</Label>
                <Input 
                  :model-value="String(getMetadata('title_en', ''))"
                  :placeholder="t('layout.menus.form.titleEnPlaceholder')"
                  class="h-8"
                  @update:model-value="updateMetadataField('title_en', $event)"
                />
              </div>

              <div class="space-y-1.5">
                <Label class="text-xs">{{ t('layout.menus.form.titleId') || 'Title (Indonesian)' }}</Label>
                <Input 
                  :model-value="String(getMetadata('title_id', ''))"
                  :placeholder="t('layout.menus.form.titleIdPlaceholder')"
                  class="h-8"
                  @update:model-value="updateMetadataField('title_id', $event)"
                />
              </div>
            </AccordionContent>
          </AccordionItem>

          <AccordionItem
            v-for="group in groupedSettings"
            :key="group.name"
            :value="group.name"
          >
            <AccordionTrigger class="py-2 text-sm">
              {{ formatGroupName(group.name) }}
            </AccordionTrigger>
            <AccordionContent class="pt-2 pb-4 space-y-4">
              <ItemPropertyField 
                v-for="setting in group.settings" 
                :key="setting.key"
                :setting="setting"
                :value="selectedItem[setting.key]"
                @update="updateField(setting.key, $event)"
              />
            </AccordionContent>
          </AccordionItem>
        </Accordion>

        <!-- Quick Actions -->
        <div class="flex gap-2 pt-4 border-t border-border">
          <Button 
            size="sm" 
            variant="outline" 
            class="flex-1"
            @click="handleDuplicate"
          >
            <Copy class="w-3.5 h-3.5 mr-2" />
            {{ t('common.actions.duplicate') }}
          </Button>
          <Button 
            size="sm" 
            variant="destructive" 
            class="flex-1"
            @click="handleDelete"
          >
            <Trash2 class="w-3.5 h-3.5 mr-2" />
            {{ t('common.actions.delete') }}
          </Button>
        </div>
      </div>
    </CardContent>
  </Card>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { useMenuContext } from '@/modules/Layout/composables/useMenu';
import { menuItemRegistry } from '../registry';
import type { MenuItem, MenuItemSetting, PropertyValue } from '@/modules/Layout/types/menu';

// UI Components
import {
    Card,
    CardHeader,
    CardTitle,
    CardContent,
    Badge,
    Button,
    Accordion,
    AccordionContent,
    AccordionItem,
    AccordionTrigger,
    Select,
    SelectTrigger,
    SelectValue,
    SelectContent,
    SelectItem,
    Label,
    Checkbox,
    Input
} from '@/shared/components/ui';
import ItemPropertyField from './ItemPropertyField.vue';

import {
  Columns,
  Copy,
  File,
  FileText,
  LinkIcon,
  MousePointer,
  PanelRightClose,
  Tag,
  Trash2,
  X,
} from 'lucide-vue-next';

defineEmits<{
    (e: 'collapse'): void;
}>();

const { t } = useI18n();
const menuContext = useMenuContext();

const selectedItem = computed(() => menuContext.selectedItem.value);

interface ParentOption extends MenuItem {
    _depth: number;
}

// Flatten items with depth for select options
const validParents = computed(() => {
    if (!selectedItem.value) return [];
    
    const currentId = selectedItem.value.id || selectedItem.value._temp_id;
    const result: ParentOption[] = [];
    
    // Recursive flatten
    const traverse = (nodes: MenuItem[], depth: number) => {
        nodes.forEach(node => {
            const nodeId = node.id || node._temp_id;
            
            // Exclude self and its subtree
            if (nodeId === currentId) {
                return; 
            }
            
            result.push({ ...node, _depth: depth });
            
            if (node.children && node.children.length > 0) {
                traverse(node.children, depth + 1);
            }
        });
    };
    
    traverse(menuContext.items.value, 0);
    return result;
});

const currentParentId = computed(() => {
    if (!selectedItem.value) return 'root';
    const parent = menuContext.findParent(selectedItem.value.id || selectedItem.value._temp_id!);
    return parent ? (parent.id || parent._temp_id!).toString() : 'root';
});

const handleParentChange = (val: string) => {
    if (!selectedItem.value) return;
    const itemId = selectedItem.value.id || selectedItem.value._temp_id!;
    const newParentId = val === 'root' ? null : val;
    menuContext.moveItem(itemId, newParentId);
};

const typeDefinition = computed(() => {
    if (!selectedItem.value) return null;
    return menuItemRegistry.get(selectedItem.value.type || '');
});

const typeLabel = computed(() => {
    if (!selectedItem.value) return 'Unknown';
    const key = `layout.menus.form.types.${selectedItem.value.type}`;
    const translated = t(key);
    if (translated !== key) return translated;
    return typeDefinition.value?.label || selectedItem.value.type || 'Unknown';
});

const iconComponent = computed(() => {
    switch (selectedItem.value?.type) {
        case 'page': return FileText;
        case 'post': return File;
        case 'category': return Tag;
        case 'column_group': return Columns;
        default: return LinkIcon;
    }
});

const iconColorClass = computed(() => {
    const color = typeDefinition.value?.color || 'gray';
    return `text-${color}-500`;
});

const settingsSchema = computed(() => {
    return typeDefinition.value?.settings || [];
});

const groupedSettings = computed(() => {
    const groups: Record<string, MenuItemSetting[]> = {};
    settingsSchema.value.forEach((setting: MenuItemSetting) => {
        const groupName = setting.group;
        if (groupName) {
            if (!groups[groupName]) {
                groups[groupName] = [];
            }
            groups[groupName].push(setting);
        }
    });
    return Object.entries(groups).map(([name, settings]) => ({ name, settings }));
});

const formatGroupName = (name: string) => {
    const key = `layout.menus.form.groups.${name}`;
    const translated = t(key);
    if (translated !== key) return translated;
    return name.split('_').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' ');
};

const getMetadata = (key: string, fallback: any = null) => {
    if (!selectedItem.value) return fallback;
    const meta = selectedItem.value.metadata as Record<string, any> | undefined;
    return meta && meta[key] !== undefined ? meta[key] : fallback;
};

const updateMetadataField = (key: string, value: any) => {
    const current = selectedItem.value;
    if (!current) return;
    const currentMeta = (current.metadata as Record<string, any>) || {};
    const newMeta = {
        ...currentMeta,
        [key]: value
    };
    Object.assign(current, { metadata: newMeta });
    menuContext.takeSnapshot();
};

const updateField = (key: string, value: PropertyValue) => {
    const current = selectedItem.value;
    if (!current) return;
    // Mutate the live tree node (selectedItem is findItemById, not a copy).
    // A second lookup by id used to no-op when string/number ids diverged, so
    // the input looked edited while save still posted the old title/url.
    Object.assign(current, { [key]: value });
    menuContext.takeSnapshot();
};

const handleDuplicate = () => {
    if (!selectedItem.value) return;
    const itemId = selectedItem.value.id || selectedItem.value._temp_id!;
    menuContext.duplicateItem(itemId);
};

const handleDelete = () => {
    if (!selectedItem.value) return;
    const itemId = selectedItem.value.id || selectedItem.value._temp_id!;
    menuContext.deleteItem(itemId);
};
</script>
