<template>
  <div class="console-page min-w-0 max-w-full flex flex-col">
        <PageHeader
      borderless
      :title="t('system.roles.title')"
      :subtitle="t('system.roles.subtitle')"
    >
    </PageHeader>

    <!-- Main organization -->
    <ConsoleListCard class="flex-1">
      <div class="flex min-h-[420px] flex-1 overflow-hidden bg-transparent">
      <!-- Left Sidebar: Role List -->
      <div class="w-72 border-r border-border bg-transparent flex flex-col shrink-0">
        <div class="h-14 flex items-center px-4 border-b border-border bg-transparent">
          <div class="relative">
            <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
            <Input
              v-model="search"
              :placeholder="$t('common.actions.search')"
              class="pl-9 h-9"
            />
          </div>
        </div>

        <div class="flex-1 overflow-y-auto p-2 space-y-1 custom-scrollbar">
          <div
            v-if="loading && roles.length === 0"
            class="p-4 space-y-3"
          >
            <div
              v-for="i in 5"
              :key="i"
              class="h-10 w-full bg-muted rounded-lg"
            />
          </div>
          <div
            v-else-if="filteredRoles.length === 0"
            class="p-8 text-center text-muted-foreground text-sm italic"
          >
            {{ $t('system.roles.list.empty') }}
          </div>
          <div
            v-for="role in filteredRoles"
            :key="role.id"
            class="group flex items-center gap-3 p-2.5 rounded-lg cursor-pointer relative"
            :class="[ isSelected(role.id) ? 'bg-primary/5 border-primary/20' : 'hover:bg-muted/5' , String(activeRoleId) === String(role.id) ? 'ring-1 ring-primary/50 bg-primary/5' : '' ]"
            @click="setActiveRole(role)"
          >
            <Checkbox 
              v-if="!isProtectedRole(role.name)"
              :checked="isSelected(role.id)"
              class="shrink-0"
              :aria-label="t('common.actions.selectRow') + ': ' + (role?.name || '')"
              @update:checked="() => toggleSelection(role.id)"
              @click.stop
            />
            <div
              v-else
              class="w-4 h-4 flex items-center justify-center shrink-0"
            >
              <Lock class="w-3 h-3 text-muted-foreground/50" />
            </div>

            <div class="flex-1 overflow-hidden">
              <div class="flex items-center justify-between gap-2">
                <span class="text-sm font-semibold truncate text-foreground group-hover:text-primary">
                  {{ role?.name || t('system.roles.unknown') }}
                </span>
                <Badge
                  v-if="role && isProtectedRole(role.name)"
                  variant="outline"
                  class="text-[9px] h-3.5 px-1 py-0 border-muted-foreground/30 text-muted-foreground"
                >
                  System
                </Badge>
              </div>
              <div
                v-if="role"
                class="text-xs text-foreground/70 flex items-center gap-1.5 mt-0.5"
              >
                <Users class="w-3 h-3" />
                {{ $t('system.roles.list.usersCount', { count: role.users_count || 0 }) }}
              </div>
            </div>

            <div class="opacity-0 group-hover:opacity-100 flex gap-0.5">
              <Button
                v-if="!isProtectedRole(role.name) && authStore.hasPermission('delete roles')"
                variant="ghost"
                size="icon" :aria-label="t('common.actions.delete')"
                class="h-7 w-7 text-muted-foreground hover:text-destructive hover:bg-destructive/10"
                @click.stop="deleteRole(role)"
              >
                <Trash2 class="w-3.5 h-3.5" />
              </Button>
              <Button
                v-if="authStore.hasPermission('create roles')"
                variant="ghost"
                size="icon"
              :aria-label="$t('system.roles.panel.duplicate')"
                class="h-7 w-7 text-muted-foreground hover:text-primary hover:bg-primary/10"
                :title="$t('system.roles.panel.duplicate')"
                @click.stop="duplicateRole(role)"
              >
                <Copy class="w-3.5 h-3.5" />
              </Button>
            </div>
          </div>
        </div>
                
        <!-- Footer / Bulk Actions -->
        <div
          v-if="selectedRoleIds.length > 0"
          class="p-4 border-t border-border bg-transparent"
        >
          <div class="flex items-center justify-between mb-3 px-1">
            <span class="text-xs font-semibold text-foreground/70">{{ $t('system.roles.panel.comparison.mode') }}</span>
            <span class="text-xs font-semibold text-primary">{{ $t('system.roles.panel.comparison.syncing', { count: selectedRoleIds.length }) }}</span>
          </div>
          <Button
            variant="outline"
            size="xs"
            class="w-full h-8 text-xs font-semibold border-primary/20 text-primary hover:bg-muted"
            @click="selectedRoleIds = []"
          >
            {{ $t('system.roles.panel.clearSelection') }}
          </Button>
        </div>
      </div>

      <!-- Main Content Area -->
      <div class="flex-1 flex flex-col bg-transparent overflow-hidden relative">
        <!-- organization Header -->
        <div class="h-14 border-b border-border flex items-center justify-between px-6 bg-transparent shrink-0 z-10">
          <div class="flex items-center gap-3">
            <div
              v-if="panelMode === 'comparison'"
              class="flex items-center gap-2"
            >
              <div class="p-1.5 bg-primary/10 rounded-lg">
                <Columns class="w-4 h-4 text-primary" />
              </div>
              <h2 class="font-bold text-sm">
                {{ $t('system.roles.panel.comparison.title') }}
              </h2>
            </div>
            <div
              v-else-if="panelMode === 'edit'"
              class="flex items-center gap-2"
            >
              <div class="p-1.5 bg-primary/10 rounded-lg">
                <Edit3 class="w-4 h-4 text-primary" />
              </div>
              <h2 class="font-bold text-sm">
                {{ activeRole?.name }}
              </h2>
              <Badge
                variant="outline"
                class="text-xs border-primary/30 text-primary bg-primary/5"
              >
                {{ $t('system.roles.panel.edit.mode') }}
              </Badge>
            </div>
            <div
              v-else-if="panelMode === 'create'"
              class="flex items-center gap-2"
            >
              <div class="p-1.5 bg-success/10 rounded-lg">
                <Plus class="w-4 h-4 text-success" />
              </div>
              <h2 class="font-bold text-sm">
                {{ $t('system.roles.panel.create.title') }}
              </h2>
            </div>
            <div
              v-else
              class="flex items-center gap-2 text-muted-foreground mr-2"
            >
              <Button
                variant="outline"
                size="xs"
                :disabled="loading"
                class="h-8 border-primary/20 text-primary hover:bg-primary/5 font-bold"
                @click="fetchRoles"
              >
                <RefreshCw :class="cn('w-3.5 h-3.5 mr-2', loading && '')" />
                {{ $t('common.actions.refresh') }}
              </Button>
              <Button
                v-if="authStore.hasPermission('create roles')"
                variant="default"
                class="h-8"
                @click="createNewRole"
              >
                <Plus class="w-3.5 h-3.5 mr-2" />
                {{ $t('system.roles.create') }}
              </Button>
            </div>
          </div>

          <div
            v-if="panelMode !== 'welcome'"
            class="flex items-center gap-2"
          >
            <Button
              variant="ghost"
              size="sm"
          class="h-8 text-xs"
              @click="cancelAction"
            >
              {{ isDirty ? $t('system.roles.panel.reset') : $t('common.actions.cancel') }}
            </Button>
            <Button
              variant="default"
              size="sm"
          :disabled="saving || !isDirty"
              class="h-8 text-xs font-bold"
              @click="saveChanges"
            >
              <Loader2
                v-if="saving"
                class="w-3 h-3 mr-2"
              />
              {{ $t('system.roles.panel.save') }}
            </Button>
          </div>
        </div>

        <!-- organization Body -->
        <div class="flex-1 overflow-y-auto custom-scrollbar relative p-6">
          <!-- Welcome Screen -->
          <div
            v-if="panelMode === 'welcome'"
            class="h-full flex flex-col items-center justify-center text-center p-12"
          >
            <div class="p-6 rounded-full bg-primary/5 border border-primary/10 mb-6 flex items-center justify-center">
              <Shield class="w-12 h-12 text-primary/30" />
            </div>
            <h3 class="text-lg font-bold mb-2">
              {{ $t('system.roles.panel.welcome.title') }}
            </h3>
            <p class="text-muted-foreground text-sm max-w-md mx-auto leading-relaxed">
              {{ $t('system.roles.panel.welcome.description') }}
            </p>
          </div>

          <!-- Single Role Edit / Create Mode -->
          <ConsoleFormCard
            v-else-if="panelMode === 'edit' || panelMode === 'create'"
            class="max-w-4xl mx-auto"
            :padded="false"
          >
            <div class="space-y-10 p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="text-xs font-semibold text-foreground/70 mb-1.5 block">{{ $t('system.roles.panel.identity') }}</label>
                <Input
                  v-model="form.name"
                  :placeholder="$t('system.roles.form.namePlaceholder')"
                  :disabled="isProtectedRole(activeRole?.name || '')"
                  class="h-10 font-medium"
                />
                <p
                  v-if="errors.name"
                  class="text-xs text-destructive mt-1.5 font-medium"
                >
                  {{ Array.isArray(errors.name) ? errors.name[0] : errors.name }}
                </p>
              </div>
              <div class="flex flex-col justify-end">
                <div class="flex items-center gap-4 text-xs text-foreground/80">
                  <div class="flex items-center gap-1.5">
                    <div class="w-1.5 h-1.5 rounded-full bg-primary" />
                    {{ $t('system.roles.panel.liveEditing') }}
                  </div>
                  <div class="flex items-center gap-1.5">
                    <CheckCircle2
                      v-if="isDirty"
                      class="w-3.5 h-3.5 text-success"
                    />
                    <Circle
                      v-else
                      class="w-3.5 h-3.5"
                    />
                    {{ isDirty ? $t('system.roles.panel.unsavedChanges') : $t('system.roles.panel.synced') }}
                  </div>
                </div>
              </div>
            </div>

            <div class="space-y-4">
              <div class="flex items-center justify-between pb-2 border-b">
                <h4 class="text-sm font-bold border-l-2 border-primary pl-3">
                  {{ $t('system.roles.panel.matrix.title') }}
                </h4>
                <div class="flex items-center gap-2">
                  <Button
                    variant="outline"
                    size="xs"
                    class="h-7 text-xs border-primary/20 text-primary hover:bg-primary/5 font-bold"
                    @click="expandAll"
                  >
                    {{ $t('system.roles.panel.matrix.expandAll') }}
                  </Button>
                  <Button
                    variant="outline"
                    size="xs"
                    class="h-7 text-xs border-primary/20 text-primary hover:bg-primary/5 font-bold"
                    @click="collapseAll"
                  >
                    {{ $t('system.roles.panel.matrix.collapseAll') }}
                  </Button>
                </div>
              </div>

              <Accordion
                v-model:model-value="expandedCategories"
                type="multiple"
                class="space-y-0 border-t border-border"
              >
                <AccordionItem 
                  v-for="(perms, category) in groupedPermissions" 
                  :key="category" 
                  :value="String(category)"
                  class="bg-transparent border-b border-border overflow-hidden"
                >
                  <div class="flex items-center gap-2 px-2">
                    <AccordionTrigger class="flex-1 py-5 bg-transparent hover:bg-muted/10 hover:no-underline">
                      <div class="flex items-center gap-3">
                        <div class="p-1.5 bg-primary/5 border border-primary/10 rounded-lg group-data-[state=open]:bg-primary/20">
                          <FolderOpen class="w-4 h-4 text-primary group-data-[state=open]:text-primary" />
                        </div>
                        <div class="text-left">
                          <h5 class="text-xs font-bold text-foreground">
                            {{ category }}
                          </h5>
                          <p class="text-xs text-foreground/70 mt-0.5">
                            {{ $t('system.roles.panel.matrix.available', { count: perms.length }) }}
                          </p>
                        </div>
                      </div>
                    </AccordionTrigger>
                    <Button
                      type="button"
                      variant="outline"
                      size="xs"
                      class="h-7 shrink-0 text-xs font-bold border-primary/20 text-primary hover:bg-primary hover:text-primary-foreground"
                      @click.stop="toggleCategory(String(category))"
                    >
                      {{ isCategorySelected(String(category)) ? $t('system.roles.panel.matrix.deselectCategory') : $t('system.roles.panel.matrix.selectCategory') }}
                    </Button>
                  </div>
                  <AccordionContent>
                    <div class="px-2 py-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-y-4 gap-x-8">
                      <div
                        v-for="permission in perms"
                        :key="permission.id"
                        class="flex items-center space-x-3 p-2.5 rounded-lg border border-transparent hover:border-border/60 hover:bg-card cursor-pointer group/perm"
                        @click="togglePermission(permission.name)"
                      >
                        <Checkbox
                          :checked="isSelectedPermission(permission?.name || '')"
                          :aria-label="formatPermissionName(permission?.name || '', String(category))"
                          @update:checked="() => togglePermission(permission?.name || '')"
                        />
                        <span class="text-xs font-medium text-muted-foreground group-hover/perm:text-foreground pt-0.5">
                          {{ formatPermissionName(permission?.name || '', String(category)) }}
                        </span>
                      </div>
                    </div>
                  </AccordionContent>
                </AccordionItem>
              </Accordion>
            </div>
            </div>
          </ConsoleFormCard>

          <!-- Comparison Matrix Mode -->
          <div
            v-else-if="panelMode === 'comparison'"
            class="h-full flex flex-col"
          >
            <div class="mb-6 rounded-xl border border-primary/20 bg-primary/5 p-4 flex items-center justify-between">
              <div class="flex items-center gap-4">
                <div class="flex -space-x-3">
                  <div
                    v-for="rId in selectedRoleIds.slice(0, 5)"
                    :key="rId"
                    class="w-10 h-10 rounded-full border-2 border-background bg-primary/10 flex items-center justify-center text-primary text-xs font-black"
                  >
                    {{ getRole(rId)?.name?.substring(0, 2) || '??' }}
                  </div>
                  <div
                    v-if="selectedRoleIds.length > 5"
                    class="w-10 h-10 rounded-full border-2 border-background bg-muted flex items-center justify-center text-muted-foreground text-xs font-semibold"
                  >
                    +{{ selectedRoleIds.length - 5 }}
                  </div>
                </div>
                <div>
                  <h4 class="text-sm font-bold">
                    {{ $t('system.roles.panel.comparison.syncing', { count: selectedRoleIds.length }) }}
                  </h4>
                  <p class="text-xs text-foreground/70 font-bold mt-0.5">
                    {{ $t('system.roles.panel.comparison.subtitle') }}
                  </p>
                </div>
              </div>
              <Button
                variant="outline"
                size="sm"
          class="h-8 text-xs"
                @click="selectedRoleIds = []"
              >
                {{ $t('system.roles.panel.comparison.exit') }}
              </Button>
            </div>

            <div class="space-y-6">
              <Accordion
                v-model:model-value="expandedCategories"
                type="multiple"
                class="space-y-0 border-t border-border"
              >
                <AccordionItem 
                  v-for="(perms, category) in groupedPermissions" 
                  :key="category" 
                  :value="String(category)"
                  class="bg-transparent border-b border-border overflow-hidden"
                >
                  <AccordionTrigger class="px-2 py-5 bg-transparent hover:bg-muted/10 hover:no-underline">
                    <div class="flex items-center gap-3">
                      <div class="p-1.5 bg-background border border-border/50 rounded-lg">
                        <FolderOpen class="w-4 h-4 text-muted-foreground" />
                      </div>
                      <h5 class="text-xs font-bold text-foreground">
                        {{ category }}
                      </h5>
                    </div>
                  </AccordionTrigger>
                  <AccordionContent>
                    <div class="overflow-x-auto pb-4 custom-scrollbar">
                      <Table>
                        <TableHeader>
                          <TableRow class="bg-transparent hover:bg-transparent border-b border-border">
                            <TableHead class="min-w-[200px] sticky left-0 bg-background z-20 border-r px-4 py-3 text-xs font-semibold h-12">
                              {{ $t('system.roles.permissions') }}
                            </TableHead>
                            <TableHead 
                              v-for="rId in selectedRoleIds" 
                              :key="rId" 
                              class="min-w-[140px] text-center px-4 py-3 h-12"
                            >
                              <div class="flex flex-col items-center gap-1">
                                <span
                                  class="text-xs font-semibold truncate max-w-[120px] text-primary"
                                  :title="getRole(rId)?.name"
                                >
                                  {{ getRole(rId)?.name }}
                                </span>
                                <Button 
                                  variant="ghost" 
                                  size="icon"
              :aria-label="$t('system.roles.form.selectAll') + ': ' + getRole(rId)?.name"
                                  class="h-5 w-5 hover:bg-primary/20 hover:text-primary rounded-md"
                                  :title="$t('system.roles.form.selectAll') + ': ' + getRole(rId)?.name"
                                  :disabled="isProtectedRole(getRole(rId)?.name || '')"
                                  @click.stop="toggleCategoryForRole(String(category), rId)"
                                >
                                  <CheckSquare class="w-3 h-3" />
                                </Button>
                              </div>
                            </TableHead>
                          </TableRow>
                        </TableHeader>
                        <TableBody>
                          <TableRow 
                            v-for="permission in perms" 
                            :key="permission.id"
                            class="border-b border-border/40 hover:bg-muted/5"
                          >
                            <TableCell class="sticky left-0 bg-background z-20 border-r font-medium text-xs px-4 py-3">
                              {{ formatPermissionName(permission.name, String(category)) }}
                            </TableCell>
                            <TableCell 
                              v-for="rId in selectedRoleIds" 
                              :key="rId" 
                              class="text-center px-4 py-3"
                            >
                              <Checkbox
                                :checked="isRoleHasPermission(roleIdToNum(rId), permission.name)"
                                class="mx-auto"
                                :aria-label="(getRole(rId)?.name || '') + ': ' + formatPermissionName(permission.name, String(category))"
                                :disabled="isProtectedRole(getRole(rId)?.name || '')"
                                @update:checked="() => togglePermissionForRole(roleIdToNum(rId), permission.name)"
                              />
                            </TableCell>
                          </TableRow>
                        </TableBody>
                      </Table>
                    </div>
                  </AccordionContent>
                </AccordionItem>
              </Accordion>
            </div>
          </div>
        </div>

        <!-- Global Overlay Loading -->
        <div
          v-if="saving"
          class="absolute inset-0 bg-background/60 backdrop-blur-[2px] z-50 flex items-center justify-center"
        >
          <div class="bg-card border border-border shadow-xl rounded-2xl p-8 flex flex-col items-center gap-4">
            <div class="relative">
              <div class="w-12 h-12 rounded-full border-4 border-primary/20 border-t-primary" />
              <Shield class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-5 h-5 text-primary" />
            </div>
            <div class="text-center">
              <h4 class="font-bold text-sm">
                {{ $t('system.roles.panel.saving.title') }}
              </h4>
              <p class="text-xs text-foreground/70 mt-1">
                {{ $t('system.roles.panel.saving.subtitle') }}
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
    </ConsoleListCard>
  </div>
</template>

<script setup lang="ts">
import { PageHeader, ConsoleListCard, ConsoleFormCard } from '@/shared/components/shell';

import { logger } from '@/shared/utils/logger';
import { ref, onMounted, computed, watch } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useI18n } from 'vue-i18n';
import api from '@/engine/api/client';
import { useToast } from '@/shared/composables/useToast';
import { useConfirm } from '@/shared/composables/useConfirm';
import { useAuthStore, ROLE_RANKS } from '@/modules/Core/System/stores/auth';
import { getResponseList } from '@/shared/utils/responseParser';
import { cn } from '@/shared/utils/lib-utils';
import { roleSchema } from '@/shared/schemas';
import { useFormValidation } from '@/shared/composables/useFormValidation';

// UI Components
// UI Components
// UI Components
import {
    Button,
    Badge,
    Input,
    Checkbox,
    Table,
    TableHeader,
    TableRow,
    TableHead,
    TableBody,
    TableCell,
    Accordion,
    AccordionItem,
    AccordionTrigger,
    AccordionContent
} from '@/shared/components/ui';

import {
  CheckCircle2,
  CheckSquare,
  Circle,
  Columns,
  Copy,
  Edit3,
  FolderOpen,
  Loader2,
  Lock,
  Plus,
  RefreshCw,
  Search,
  Shield,
  Trash2,
  Users,
} from 'lucide-vue-next';

import type { Role, Permission } from '@/engine/types/auth';

const router = useRouter();
const route = useRoute();
const { t } = useI18n();
const toast = useToast();
const { confirm } = useConfirm();
const authStore = useAuthStore();
const { errors, validateWithZod, clearErrors, setErrors } = useFormValidation(roleSchema);

// State
const loading = ref(false);
const saving = ref(false);
const roles = ref<Role[]>([]);
const permissions = ref<Record<string, Permission[]>>({});
const search = ref('');
const activeRoleId = ref<string | number | null>(null);
const selectedRoleIds = ref<string[]>([]);
const expandedCategories = ref<string[]>([]);
const initialData = ref<{ name: string; permissions: string[]; matrixPermissions: Record<string, string[]> } | null>(null);

// Form State
const form = ref<{
    name: string;
    permissions: string[]; 
    matrixPermissions: Record<string, string[]>;
}>({
    name: '',
    permissions: [],
    matrixPermissions: {}
});

const protectedRoles = ['super', 'super-admin', 'superadmin', 'super_admin', 'supe_admin'];
const isProtectedRole = (name: string) => protectedRoles.includes(name) || (ROLE_RANKS[name] || 0) >= 100;

// Computed
const filteredRoles = computed(() => {
    if (!search.value) return roles.value;
    const s = search.value.toLowerCase();
    return roles.value.filter(r => r?.name?.toLowerCase().includes(s));
});

const activeRole = computed(() => roles.value.find(r => r?.id === activeRoleId.value) || null);

const panelMode = computed(() => {
    if (selectedRoleIds.value.length > 1) return 'comparison';
    if (activeRoleId.value === -1) return 'create';
    if (activeRoleId.value) return 'edit';
    return 'welcome';
});

const groupedPermissions = computed(() => permissions.value);

const isDirty = computed(() => {
    if (!initialData.value) return false;
    
    if (panelMode.value === 'create') return !!form.value.name || form.value.permissions.length > 0;
    
    if (panelMode.value === 'edit' && initialData.value) {
        const nameChanged = form.value.name !== initialData.value.name;
        const currentPerms = [...form.value.permissions].sort().join(',');
        const prevPerms = [...(initialData.value.permissions || [])].sort().join(',');
        return nameChanged || currentPerms !== prevPerms;
    }
    
    if (panelMode.value === 'comparison' && initialData.value) {
        return JSON.stringify(form.value.matrixPermissions) !== JSON.stringify(initialData.value.matrixPermissions || {});
    }
    
    return false;
});

// Methods
const fetchRoles = async () => {
    loading.value = true;
    try {
        const response = await api.get('/manage/system/roles?limit=100');
        const rawRoles = getResponseList<Role>(response.data);
        roles.value = Array.isArray(rawRoles) ? rawRoles.filter(Boolean) : [];
        if (panelMode.value === 'comparison') syncMatrixFromRoles();
    } catch (error: unknown) {
        toast.error.load(error as Record<string, unknown>);
    } finally {
        loading.value = false;
    }
};

const fetchPermissions = async () => {
    try {
        const response = await api.get('/manage/system/roles/permissions');
        const rawPerms = response.data || {};
        
        // Sanitize permissions
        const sanitized: Record<string, Permission[]> = {};
        Object.keys(rawPerms).forEach(cat => {
            if (Array.isArray(rawPerms[cat])) {
                sanitized[cat] = rawPerms[cat].filter(Boolean);
            }
        });
        
        permissions.value = sanitized;
        expandedCategories.value = Object.keys(sanitized).slice(0, 2);
    } catch (error: unknown) {
        logger.error('Failed to fetch permissions:', error);
    }
};

const setActiveRole = (role: Role) => {
    router.push({ name: 'roles.edit', params: { id: role.id } });
};

const createNewRole = () => {
    router.push({ name: 'roles.create' });
};

const internalSetActiveRole = (role: Role) => {
    activeRoleId.value = role.id;
    selectedRoleIds.value = []; 
    form.value.name = role.name;
    form.value.permissions = (role.permissions || []).map(p => p.name);
    initialData.value = {
        name: form.value.name,
        permissions: [...form.value.permissions],
        matrixPermissions: {}
    };
    clearErrors();
};

const internalCreateNewRole = () => {
    activeRoleId.value = -1;
    selectedRoleIds.value = [];
    form.value.name = '';
    form.value.permissions = [];
    initialData.value = { 
        name: '', 
        permissions: [],
        matrixPermissions: {}
    };
    clearErrors();
};
 
const duplicateRole = (role: Role) => {
    activeRoleId.value = -1;
    selectedRoleIds.value = [];
    form.value.name = `${role.name} (Copy)`;
    form.value.permissions = (role.permissions || []).map(p => p.name);
    initialData.value = { 
        name: '', // Set to empty to make it dirty immediately
        permissions: [],
        matrixPermissions: {}
    };
    clearErrors();
    router.push({ name: 'roles.create' });
};
 
const isSelected = (id: string) => selectedRoleIds.value.includes(id);

const toggleSelection = (id: string) => {
    const index = selectedRoleIds.value.indexOf(id);
    if (index > -1) {
        selectedRoleIds.value.splice(index, 1);
    } else {
        selectedRoleIds.value.push(id);
        activeRoleId.value = null; // Exit edit mode
    }
    
    // Automatically sync matrix if we are in comparison mode
    if (selectedRoleIds.value.length > 1) {
        syncMatrixFromRoles();
    }
};

const syncMatrixFromRoles = () => {
    const matrix: Record<string, string[]> = {};
    selectedRoleIds.value.forEach(rId => {
        const role = getRole(rId);
        if (role) {
            matrix[rId] = (role.permissions || []).filter(Boolean).map(p => p.name);
        }
    });
    form.value.matrixPermissions = matrix;
    
    // Safety check for initialData
    if (!initialData.value) {
        initialData.value = {
            name: '',
            permissions: [],
            matrixPermissions: {}
        };
    }
    initialData.value.matrixPermissions = JSON.parse(JSON.stringify(matrix));
};

const getRole = (id: string) => roles.value.find(r => r && String(r.id) === String(id));
const roleIdToNum = (id: string) => String(id);

// Permission Handlers
const togglePermission = (name: string) => {
    if (isProtectedRole(activeRole.value?.name || '')) return;
    const index = form.value.permissions.indexOf(name);
    if (index > -1) form.value.permissions.splice(index, 1);
    else form.value.permissions.push(name);
};

const isSelectedPermission = (name: string) => form.value.permissions.includes(name);

const isCategorySelected = (category: string) => {
    const categoryPerms = permissions.value[category] || [];
    if (categoryPerms.length === 0) return false;
    return categoryPerms.every(p => p && form.value.permissions.includes(p.name));
};

const toggleCategory = (category: string) => {
    if (isProtectedRole(activeRole.value?.name || '')) return;
    const categoryPerms = permissions.value[category] || [];
    const categoryNames = categoryPerms.filter(Boolean).map(p => p.name);
    if (isCategorySelected(category)) {
        form.value.permissions = form.value.permissions.filter(p => !categoryNames.includes(p));
    } else {
        const toAdd = categoryNames.filter(name => !form.value.permissions.includes(name));
        form.value.permissions.push(...toAdd);
    }
};

const isRoleHasPermission = (roleId: string, name: string) => (form.value.matrixPermissions[roleId] || []).includes(name);

const togglePermissionForRole = (roleId: string, name: string) => {
    const role = getRole(roleId);
    if (role && isProtectedRole(role.name)) return;
    
    const rolePerms = form.value.matrixPermissions[roleId] || [];
    const index = rolePerms.indexOf(name);
    if (index > -1) rolePerms.splice(index, 1);
    else rolePerms.push(name);
    form.value.matrixPermissions[roleId] = [...rolePerms];
};

const toggleCategoryForRole = (category: string, roleId: string) => {
    const role = getRole(roleId);
    if (role && isProtectedRole(role.name)) return;

    const categoryPerms = permissions.value[category] || [];
    const categoryNames = categoryPerms.filter(Boolean).map(p => p.name);
    const currentRolePerms = form.value.matrixPermissions[roleId] || [];
    const allSelected = categoryNames.every(name => currentRolePerms.includes(name));

    if (allSelected) {
        form.value.matrixPermissions[roleId] = currentRolePerms.filter(p => !categoryNames.includes(p));
    } else {
        const toAdd = categoryNames.filter(name => !currentRolePerms.includes(name));
        form.value.matrixPermissions[roleId] = [...currentRolePerms, ...toAdd];
    }
};

const saveChanges = async () => {
    saving.value = true;
    clearErrors();
    try {
        if (panelMode.value === 'create') {
            if (!validateWithZod({ name: form.value.name, permissions: form.value.permissions })) return;
            await api.post('/manage/system/roles', { name: form.value.name, permissions: form.value.permissions });
            toast.success.create('Role');
            router.push({ name: 'roles' }); // Back to index
        } else if (panelMode.value === 'edit') {
            if (!activeRoleId.value) return;
            if (!validateWithZod({ name: form.value.name, permissions: form.value.permissions })) return;
            await api.put(`/manage/system/roles/${activeRoleId.value}`, { name: form.value.name, permissions: form.value.permissions });
            toast.success.update('Role');
        } else if (panelMode.value === 'comparison') {
            const promises = Object.entries(form.value.matrixPermissions).map(([rId, perms]) => {
                const role = getRole(rId);
                return api.put(`/manage/system/roles/${rId}`, { name: role?.name, permissions: perms });
            });
            await Promise.all(promises);
            toast.success.update('Roles synced successfully');
        }
        await fetchRoles();
    } catch (error: unknown) {
        const err = error as { response?: { status?: number; data?: { errors?: Record<string, string[]> } } };
        if (err.response?.status === 422) setErrors(err.response.data?.errors || {});
        else toast.error.action(error as Record<string, unknown>);
    } finally {
        saving.value = false;
    }
};

const resetForm = () => {
    if (panelMode.value === 'edit' && initialData.value) {
        form.value.name = initialData.value.name;
        form.value.permissions = [...initialData.value.permissions];
    } else if (panelMode.value === 'comparison' && initialData.value?.matrixPermissions) {
        form.value.matrixPermissions = JSON.parse(JSON.stringify(initialData.value.matrixPermissions));
    } else if (panelMode.value === 'create') {
        form.value.name = '';
        form.value.permissions = [];
        form.value.matrixPermissions = {};
    }
    clearErrors();
};

const deleteRole = async (role: Role) => {
    const confirmed = await confirm({ 
        title: t('system.roles.actions.delete'), 
        message: t('system.roles.messages.deleteConfirm', { name: role.name }), 
        variant: 'danger' 
    });
    if (!confirmed) return;
    try {
        await api.delete(`/manage/system/roles/${role.id}`);
        toast.success.delete(t('system.roles.title_singular'));
        if (activeRoleId.value === role.id) router.push({ name: 'roles' });
        selectedRoleIds.value = selectedRoleIds.value.filter(id => id !== role.id);
        fetchRoles();
    } catch (error: unknown) {
        toast.error.delete(error as Record<string, unknown>, 'Role');
    }
};

const expandAll = () => expandedCategories.value = Object.keys(permissions.value);
const collapseAll = () => expandedCategories.value = [];

const formatPermissionName = (name: string, category: string) => {
    const lowerCategory = category.toLowerCase();
    let formatted = name.toLowerCase().replace(lowerCategory, '').trim();
    if (!formatted) return name;
    return formatted.charAt(0).toUpperCase() + formatted.slice(1);
};
 
const cancelAction = () => {
    if (isDirty.value) {
        resetForm();
    } else {
        router.push({ name: 'roles' });
    }
};
 
const handleRouteParams = () => {
    const { name, params } = route;
    
    // Only handle if roles are loaded
    if (roles.value.length === 0 && !loading.value) return;

    if (name === 'roles.create') {
        if (activeRoleId.value !== -1) internalCreateNewRole();
    } else if (name === 'roles.edit' && params.id) {
        const id = String(params.id);
        if (activeRoleId.value !== id) {
            const role = roles.value.find(r => r && r.id === id);
            if (role) {
                internalSetActiveRole(role);
            } else if (roles.value.length > 0) {
                // If role not found and we HAVE roles, go back to index
                router.push({ name: 'roles' });
            }
        }
    } else if (name === 'roles') {
        activeRoleId.value = null;
        if (selectedRoleIds.value.length === 0) {
           // Reset form to clear any stale data
           form.value.name = '';
           form.value.permissions = [];
        }
    }
};

watch(() => roles.value, (newRoles) => {
    if (newRoles.length > 0) handleRouteParams();
}, { immediate: true });

watch(() => route.path, () => {
    handleRouteParams();
});

onMounted(async () => {
    loading.value = true;
    try {
        await Promise.all([
            fetchPermissions(),
            fetchRoles()
        ]);
    } finally {
        loading.value = false;
        handleRouteParams();
    }
});
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 4px; height: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: hsl(var(--border)); border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: hsl(var(--muted-foreground) / 0.3); }

</style>
