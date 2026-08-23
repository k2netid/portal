<template>
  <div class="space-y-6">
    <PageHeader
      borderless
      :title="t('library.customFields.title')"
      :subtitle="t('library.customFields.description')"
    >
      <template #actions>
        <div class="flex gap-2">
          <Button
            variant="outline"
            size="sm"
            class="h-10 inline-flex items-center gap-2"
            @click="showCreateGroupModal = true"
          >
            <Layout
              data-icon="inline-start"
              class="size-4 shrink-0"
            />
            {{ t('library.customFields.create_group') }}
          </Button>
          <Button
            size="sm"
            class="h-10 inline-flex items-center gap-2"
            @click="showCreateFieldModal = true"
          >
            <Plus
              data-icon="inline-start"
              class="size-4 shrink-0"
            />
            {{ t('library.customFields.create_field') }}
          </Button>
        </div>
      </template>
    </PageHeader>

    <Tabs
      v-model="currentTab"
      class="w-full"
    >
      <ConsoleListCard>
        <template #toolbar>
          <div class="flex items-center justify-between w-full">
            <TabsList class="bg-transparent p-0 h-auto gap-0 flex-wrap border-none">
              <TabsTrigger
                value="groups"
                class="relative px-6 py-3 data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none transition-colors"
              >
                <Layout class="w-4 h-4 mr-2" />
                {{ t('library.customFields.tabs.groups') }}
              </TabsTrigger>
              <TabsTrigger
                value="fields"
                class="relative px-6 py-3 data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none transition-colors"
              >
                <FileCode class="w-4 h-4 mr-2" />
                {{ t('library.customFields.tabs.fields') }}
              </TabsTrigger>
            </TabsList>
            <div v-if="currentTab === 'fields'" class="relative flex-1 max-w-sm ml-4">
              <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
              <Input
                v-model="fieldSearch"
                :placeholder="t('library.customFields.fields.search')"
                :aria-label="t('library.customFields.fields.search')"
                class="h-10 pl-9"
              />
            </div>
          </div>
        </template>

      <TabsContent
        value="groups"
        class="p-6"
      >
            <div
              v-if="loadingGroups"
              class="p-12 text-center"
            >
              <Loader2 class="w-8 h-8 animate-spin mx-auto text-muted-foreground mb-4" />
              <p class="text-muted-foreground font-medium">
                {{ t('library.customFields.loading') }}
              </p>
            </div>
            <EmptyState
              v-else-if="fieldGroups.length === 0"
              :title="t('library.customFields.groups.empty')"
              :icon="Layout"
            />
            <Table v-else>
              <TableHeader>
                <TableRow>
                  <TableHead>{{ t('library.customFields.groups.table.name') }}</TableHead>
                  <TableHead>{{ t('library.customFields.groups.table.fields') }}</TableHead>
                  <TableHead>{{ t('library.customFields.groups.table.attached_to') }}</TableHead>
                  <TableHead class="text-right">
                    {{ t('library.customFields.groups.table.actions') }}
                  </TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <TableRow
                  v-for="group in fieldGroups"
                  :key="group.id"
                  class="hover:bg-muted/50 transition-colors group"
                >
                  <TableCell>
                    <div class="text-sm font-semibold text-foreground">
                      {{ group.name }}
                    </div>
                    <div class="text-xs text-muted-foreground">
                      {{ group.description }}
                    </div>
                  </TableCell>
                  <TableCell>
                    <Badge
                      variant="outline"
                      class="font-mono"
                    >
                      {{ group.fields_count || 0 }}
                    </Badge>
                  </TableCell>
                  <TableCell>
                    <Badge variant="secondary">
                      {{ group.attachable_type ? group.attachable_type.split('\\').pop() : '-' }}
                    </Badge>
                  </TableCell>
                  <TableCell class="text-right">
                    <div class="flex justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                      <Button
                        variant="ghost"
                        size="icon"
                        :aria-label="t('common.actions.edit')"
                        class="h-10 w-10"
                        @click="editGroup(group)"
                      >
                        <Pencil class="size-4 shrink-0" />
                      </Button>
                      <Button
                        variant="ghost"
                        size="icon"
                        :aria-label="t('common.actions.delete')"
                        class="h-10 w-10 text-destructive hover:text-destructive hover:bg-destructive/10"
                        @click="deleteGroup(group)"
                      >
                        <Trash2 class="size-4 shrink-0" />
                      </Button>
                    </div>
                  </TableCell>
                </TableRow>
              </TableBody>
            </Table>
      </TabsContent>

      <TabsContent
        value="fields"
        class="p-6"
      >
            <div
              v-if="loadingFields"
              class="p-12 text-center"
            >
              <Loader2 class="w-8 h-8 animate-spin mx-auto text-muted-foreground mb-4" />
              <p class="text-muted-foreground font-medium">
                {{ t('library.customFields.loading') }}
              </p>
            </div>
            <EmptyState
              v-else-if="filteredFields.length === 0"
              :title="t('library.customFields.fields.empty')"
              :icon="FileCode"
            />
            <Table v-else>
              <TableHeader>
                <TableRow>
                  <TableHead>{{ t('library.customFields.fields.table.label') }}</TableHead>
                  <TableHead>{{ t('library.customFields.fields.table.name') }}</TableHead>
                  <TableHead>{{ t('library.customFields.fields.table.type') }}</TableHead>
                  <TableHead>{{ t('library.customFields.fields.table.group') }}</TableHead>
                  <TableHead class="text-right">
                    {{ t('library.customFields.fields.table.actions') }}
                  </TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <TableRow
                  v-for="field in filteredFields"
                  :key="field.id"
                  class="hover:bg-muted/50 transition-colors group"
                >
                  <TableCell class="text-sm font-semibold text-foreground">
                    {{ field.label }}
                  </TableCell>
                  <TableCell>
                    <code class="text-[11px] bg-muted px-2 py-0.5 rounded border border-border group-hover:bg-background transition-colors">{{ field.name }}</code>
                  </TableCell>
                  <TableCell>
                    <Badge
                      variant="secondary"
                      class="capitalize"
                    >
                      {{ field.type }}
                    </Badge>
                  </TableCell>
                  <TableCell class="text-sm text-muted-foreground">
                    {{ field.field_group?.name || '-' }}
                  </TableCell>
                  <TableCell class="text-right">
                    <div class="flex justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                      <Button
                        variant="ghost"
                        size="icon"
                        :aria-label="t('common.actions.edit')"
                        class="h-10 w-10"
                        @click="editField(field)"
                      >
                        <Pencil class="size-4 shrink-0" />
                      </Button>
                      <Button
                        variant="ghost"
                        size="icon"
                        :aria-label="t('common.actions.delete')"
                        class="h-10 w-10 text-destructive hover:text-destructive hover:bg-destructive/10"
                        @click="deleteField(field)"
                      >
                        <Trash2 class="size-4 shrink-0" />
                      </Button>
                    </div>
                  </TableCell>
                </TableRow>
              </TableBody>
            </Table>
      </TabsContent>
    </ConsoleListCard>
    </Tabs>

    <!-- Modals -->
    <FieldGroupModal
      v-if="showCreateGroupModal || showEditGroupModal"
      :field-group="(editingGroup as any)"
      @close="closeGroupModal"
      @saved="handleGroupSaved"
    />

    <FieldModal
      v-if="showCreateFieldModal || showEditFieldModal"
      :field="(editingField as any)"
      :field-groups="fieldGroups"
      @close="closeFieldModal"
      @saved="handleFieldSaved"
    />
  </div>
</template>

<script setup lang="ts">
import { EmptyState } from '@/shared/components/feedback';

import { PageHeader, ConsoleListCard } from '@/shared/components/shell';

import { logger } from '@/shared/utils/logger';
import { ref, onMounted, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '@/engine/api/client';
import { useToast } from '@/shared/composables/useToast';
import { useConfirm } from '@/shared/composables/useConfirm';
import FieldGroupModal from '@/modules/Library/components/custom-fields/FieldGroupModal.vue';
import FieldModal from '@/modules/Library/components/custom-fields/FieldModal.vue';
import { parseResponse, ensureArray } from '@/shared/utils/responseParser';
import { Badge, Button, Input, Table, TableBody, TableCell, TableHead, TableHeader, TableRow, Tabs, TabsContent, TabsList, TabsTrigger } from '@/shared/components/ui';

import {
  FileCode,
  Layout,
  Loader2,
  Pencil,
  Plus,
  Search,
  Trash2,
} from 'lucide-vue-next';

import type { FieldGroup, CustomField } from '@/modules/Library/types/custom-fields';

const { t } = useI18n();
const { confirm } = useConfirm();
const toast = useToast();

const currentTab = ref('groups');

const fieldGroups = ref<FieldGroup[]>([]);
const customFields = ref<CustomField[]>([]);
const loadingGroups = ref(false);
const loadingFields = ref(false);
const fieldSearch = ref('');

// Modals state
const showCreateGroupModal = ref(false);
const showEditGroupModal = ref(false);
const editingGroup = ref<FieldGroup | null>(null);
const showCreateFieldModal = ref(false);
const showEditFieldModal = ref(false);
const editingField = ref<CustomField | null>(null);

const filteredFields = computed(() => {
    if (!fieldSearch.value) return customFields.value;
    
    const searchLower = fieldSearch.value.toLowerCase();
    return customFields.value.filter(field => 
        field.label.toLowerCase().includes(searchLower) ||
        field.name.toLowerCase().includes(searchLower)
    );
});

const fetchFieldGroups = async () => {
    loadingGroups.value = true;
    try {
        const response = await api.get('/manage/library/field-groups');
        const { data } = parseResponse<FieldGroup>(response);
        fieldGroups.value = ensureArray<FieldGroup>(data);
    } catch (error: unknown) {
        logger.error('Failed to fetch field groups:', error);
    } finally {
        loadingGroups.value = false;
    }
};

const fetchCustomFields = async () => {
    loadingFields.value = true;
    try {
        const response = await api.get('/manage/library/custom-fields');
        const { data } = parseResponse<CustomField>(response);
        customFields.value = ensureArray<CustomField>(data);
    } catch (error: unknown) {
        logger.error('Failed to fetch custom fields:', error);
    } finally {
        loadingFields.value = false;
    }
};

// Group Actions
const editGroup = (group: FieldGroup) => {
    editingGroup.value = group;
    showEditGroupModal.value = true;
};

const deleteGroup = async (group: FieldGroup) => {
    const confirmed = await confirm({
        title: t('library.customFields.groups.actions.delete'),
        message: t('library.customFields.groups.confirm.delete', { name: group.name }),
        variant: 'danger',
        confirmText: t('common.actions.delete'),
    });

    if (!confirmed) return;

    try {
        await api.delete(`/manage/library/field-groups/${group.id}`);
        toast.success.delete(t('library.customFields.tabs.groups'));
        fetchFieldGroups();
    } catch (error: unknown) {
        logger.error('Failed to delete group:', error);
        toast.error.delete(error, t('library.customFields.tabs.groups'));
    }
};

const closeGroupModal = () => {
    showCreateGroupModal.value = false;
    showEditGroupModal.value = false;
    editingGroup.value = null;
};

const handleGroupSaved = () => {
    fetchFieldGroups();
    closeGroupModal();
};

// Field Actions
const editField = (field: CustomField) => {
    editingField.value = field;
    showEditFieldModal.value = true;
};

const deleteField = async (field: CustomField) => {
    const confirmed = await confirm({
        title: t('library.customFields.fields.actions.delete'),
        message: t('library.customFields.fields.confirm.delete', { label: field.label }),
        variant: 'danger',
        confirmText: t('common.actions.delete'),
    });

    if (!confirmed) return;

    try {
        await api.delete(`/manage/library/custom-fields/${field.id}`);
        toast.success.delete(t('library.customFields.tabs.fields'));
        fetchCustomFields();
    } catch (error: unknown) {
        logger.error('Failed to delete field:', error);
        toast.error.delete(error, t('library.customFields.tabs.fields'));
    }
};

const closeFieldModal = () => {
    showCreateFieldModal.value = false;
    showEditFieldModal.value = false;
    editingField.value = null;
};

const handleFieldSaved = () => {
    fetchCustomFields();
    closeFieldModal();
};

onMounted(() => {
    fetchFieldGroups();
    fetchCustomFields();
});
</script>

