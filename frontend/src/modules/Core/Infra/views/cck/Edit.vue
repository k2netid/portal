<template>
  <div class="space-y-6 max-w-5xl">
    <PageHeader
      borderless
      :title="isCreate ? $t('infra.cck.newType') : (form.name || $t('infra.cck.editType'))"
      :subtitle="cckSubtitle"
    >
      <template #actions>
        <div class="flex items-center gap-2">
          <Button
            v-if="!isCreate && form.slug"
            variant="outline"
            size="sm"
            class="h-9 gap-1.5 text-xs"
            @click="router.push({ name: 'dynamic-records-index', params: { slug: form.slug } })"
          >
            <Database class="h-3.5 w-3.5" />
            {{ $t('infra.cck.table.records') }}
          </Button>
          <Button
            variant="ghost"
            size="sm"
            class="h-9 gap-1.5 text-xs text-muted-foreground hover:text-foreground"
            @click="router.push({ name: 'cck-index' })"
          >
            <ArrowLeft class="h-3.5 w-3.5" />
            {{ $t('infra.cck.back') }}
          </Button>
        </div>
      </template>
    </PageHeader>

    <!-- Loading State -->
    <div v-if="loading" class="p-12 text-center text-sm text-muted-foreground flex flex-col items-center justify-center gap-3">
      <Spinner class="h-6 w-6 text-primary" />
      <span>{{ $t('common.messages.loading.default') }}</span>
    </div>

    <!-- Main Editor Form -->
    <form
      v-else
      class="space-y-6"
      @submit.prevent="handleSave"
    >
      <Tabs v-model="activeTab" class="w-full">
        <TabsList class="grid grid-cols-3 w-full max-w-md">
          <TabsTrigger value="schema" class="text-xs gap-1.5">
            <Layers class="h-3.5 w-3.5" />
            {{ $t('infra.cck.form.tabs.schema') }}
          </TabsTrigger>
          <TabsTrigger value="api" :disabled="isCreate" class="text-xs gap-1.5">
            <Globe class="h-3.5 w-3.5" />
            {{ $t('infra.cck.form.tabs.api') }}
          </TabsTrigger>
          <TabsTrigger value="rules" :disabled="isCreate" class="text-xs gap-1.5">
            <CheckSquare class="h-3.5 w-3.5" />
            {{ $t('infra.cck.form.tabs.rules') }}
          </TabsTrigger>
        </TabsList>

        <!-- TAB 1: Schema & Fields -->
        <TabsContent value="schema" class="space-y-6 pt-4">
          <!-- General Schema Information -->
          <ConsoleFormCard
            :title="$t('infra.cck.form.general')"
            :subtitle="$t('infra.cck.form.generalHint')"
          >
            <div class="grid gap-4 sm:grid-cols-2">
              <div>
                <Label class="text-xs mb-1.5 block">
                  {{ $t('infra.cck.form.name') }}
                  <span class="text-destructive">*</span>
                </Label>
                <Input
                  v-model="form.name"
                  required
                  class="h-9 text-xs"
                  :placeholder="$t('infra.cck.form.namePlaceholder')"
                  @blur="maybeSlugFromName"
                />
              </div>

              <div>
                <Label class="text-xs mb-1.5 block">
                  {{ $t('infra.cck.form.slug') }}
                  <span class="text-destructive">*</span>
                </Label>
                <Input
                  v-model="form.slug"
                  required
                  class="h-9 font-mono text-xs"
                  :placeholder="$t('infra.cck.form.slugPlaceholder')"
                  @input="sanitizeSlug"
                />
                <p class="text-[11px] text-muted-foreground mt-1">
                  {{ $t('infra.cck.form.slugHint') }}
                </p>
              </div>

              <div class="sm:col-span-2">
                <Label class="text-xs mb-1.5 block">
                  {{ $t('infra.cck.form.description') }}
                </Label>
                <Textarea
                  v-model="form.description"
                  rows="2"
                  class="text-xs resize-y"
                  :placeholder="$t('infra.cck.form.descriptionPlaceholder')"
                />
              </div>

              <div class="sm:col-span-2 pt-2 border-t border-border/50">
                <label class="flex items-center gap-2.5 text-xs font-medium text-foreground cursor-pointer select-none">
                  <Checkbox
                    :checked="form.is_active"
                    @update:checked="form.is_active = $event === true"
                  />
                  <span>{{ $t('infra.cck.form.active') }}</span>
                </label>
              </div>
            </div>
          </ConsoleFormCard>

          <!-- Field Definitions Section -->
          <ConsoleFormCard
            :title="$t('infra.cck.form.fieldsSection')"
            :subtitle="$t('infra.cck.form.fieldsSectionHint')"
          >
            <CckTypeBuilder v-model="form.fields" />
          </ConsoleFormCard>
        </TabsContent>

        <!-- TAB 2: API & OpenAPI -->
        <TabsContent value="api" class="space-y-6 pt-4">
          <!-- REST API Endpoints Guide -->
          <ConsoleFormCard
            :title="$t('infra.cck.form.apiSection')"
            :subtitle="$t('infra.cck.form.apiSectionHint')"
          >
            <div class="space-y-3">
              <div
                v-for="ep in dynamicEndpoints"
                :key="ep.method + ep.path"
                class="flex flex-col sm:flex-row sm:items-center justify-between p-3 rounded-lg border border-border/60 bg-muted/20 gap-2"
              >
                <div class="flex items-center gap-2 min-w-0">
                  <Badge
                    :variant="ep.method === 'GET' ? 'outline' : ep.method === 'POST' ? 'default' : ep.method === 'PUT' ? 'secondary' : 'destructive'"
                    class="text-[10px] font-mono font-bold uppercase shrink-0"
                  >
                    {{ ep.method }}
                  </Badge>
                  <span class="font-mono text-xs text-foreground truncate">{{ ep.path }}</span>
                </div>
                <div class="flex items-center justify-between sm:justify-end gap-3 shrink-0">
                  <span class="text-xs text-muted-foreground">{{ ep.label }}</span>
                  <Button
                    type="button"
                    variant="ghost"
                    size="icon"
                    class="h-7 w-7 text-muted-foreground hover:text-foreground"
                    :title="$t('infra.cck.table.copyEndpoint')"
                    @click="copyUrl(ep.path)"
                  >
                    <Copy class="h-3.5 w-3.5" />
                  </Button>
                </div>
              </div>
            </div>
          </ConsoleFormCard>

          <!-- OpenAPI Specification -->
          <ConsoleFormCard
            :title="$t('infra.cck.form.openApiTitle')"
            :subtitle="$t('infra.cck.form.openApiHint')"
          >
            <div class="space-y-3">
              <div class="flex items-center justify-between">
                <span class="text-xs font-mono text-muted-foreground">openapi: 3.0.3</span>
                <div class="flex items-center gap-2">
                  <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    class="h-8 text-xs gap-1.5"
                    @click="copyOpenApiSpec"
                  >
                    <Copy class="h-3.5 w-3.5" />
                    {{ $t('infra.cck.form.copySpec') }}
                  </Button>
                  <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    class="h-8 text-xs gap-1.5"
                    @click="downloadOpenApiSpec"
                  >
                    <Download class="h-3.5 w-3.5" />
                    {{ $t('infra.cck.form.downloadSpec') }}
                  </Button>
                </div>
              </div>

              <div class="relative rounded-lg border border-border/80 bg-muted/40 p-4 font-mono text-xs overflow-x-auto max-h-96">
                <pre>{{ openApiPreview || 'Loading specification…' }}</pre>
              </div>
            </div>
          </ConsoleFormCard>
        </TabsContent>

        <!-- TAB 3: Validation Rules -->
        <TabsContent value="rules" class="space-y-6 pt-4">
          <ConsoleFormCard
            :title="$t('infra.cck.form.rulesTitle')"
            :subtitle="$t('infra.cck.form.rulesHint')"
          >
            <div v-if="validationRulesList.length === 0" class="p-6 text-center text-xs text-muted-foreground">
              {{ $t('infra.cck.form.noRules') }}
            </div>
            <div v-else class="overflow-x-auto">
              <Table>
                <TableHeader>
                  <TableRow class="bg-muted/40">
                    <TableHead class="text-xs font-semibold">{{ $t('infra.cck.form.ruleField') }}</TableHead>
                    <TableHead class="text-xs font-semibold">{{ $t('infra.cck.form.ruleDefinition') }}</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  <TableRow
                    v-for="rule in validationRulesList"
                    :key="rule.field"
                    class="hover:bg-muted/30"
                  >
                    <TableCell class="py-2.5 font-mono text-xs font-medium text-foreground">
                      {{ rule.field }}
                    </TableCell>
                    <TableCell class="py-2.5 font-mono text-xs text-muted-foreground">
                      <Badge variant="outline" class="font-mono text-xs bg-background">
                        {{ rule.expression }}
                      </Badge>
                    </TableCell>
                  </TableRow>
                </TableBody>
              </Table>
            </div>
          </ConsoleFormCard>
        </TabsContent>
      </Tabs>

      <!-- Action Footer Toolbar -->
      <div class="flex flex-wrap items-center justify-between gap-3 pt-4 border-t border-border">
        <div class="flex items-center gap-2">
          <Button
            type="submit"
            size="sm"
            class="h-9 gap-2 text-xs"
            :disabled="saving"
          >
            <Spinner v-if="saving" class="h-3.5 w-3.5" />
            <Save v-else class="h-3.5 w-3.5" />
            {{ saving ? $t('infra.cck.saving') : $t('infra.cck.save') }}
          </Button>

          <Button
            type="button"
            variant="outline"
            size="sm"
            class="h-9 text-xs"
            @click="router.push({ name: 'cck-index' })"
          >
            {{ $t('infra.cck.cancel') }}
          </Button>
        </div>

        <Button
          v-if="!isCreate"
          type="button"
          variant="outline"
          size="sm"
          class="h-9 text-xs text-destructive border-destructive/20 hover:bg-destructive/10 gap-1.5"
          :disabled="saving"
          @click="deleteModalOpen = true"
        >
          <Trash2 class="h-3.5 w-3.5" />
          {{ $t('infra.cck.delete') }}
        </Button>
      </div>
    </form>

    <!-- Delete Confirmation Modal -->
    <ConfirmModal
      :open="deleteModalOpen"
      :title="$t('infra.cck.deleteType')"
      :message="$t('infra.cck.deleteTypeConfirm', { name: form.name })"
      :confirm-label="$t('infra.cck.delete')"
      :cancel-label="$t('infra.cck.cancel')"
      variant="destructive"
      :loading="deleting"
      @confirm="executeDelete"
      @cancel="deleteModalOpen = false"
    />
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRoute, useRouter } from 'vue-router';
import {
  Layers,
  Globe,
  CheckSquare,
  ArrowLeft,
  Database,
  Save,
  Trash2,
  Copy,
  Download,
} from 'lucide-vue-next';
import { PageHeader, ConsoleFormCard } from '@/shared/components/shell';
import {
  Button,
  Checkbox,
  Input,
  Label,
  Textarea,
  Badge,
  Spinner,
  Tabs,
  TabsList,
  TabsTrigger,
  TabsContent,
  Table,
  TableHeader,
  TableBody,
  TableRow,
  TableHead,
  TableCell,
  ConfirmModal,
} from '@/shared/components/ui';
import { useToast } from '@/shared/composables/useToast';
import { parseSingleResponse } from '@/shared/utils/responseParser';
import CckTypeBuilder from '../../components/cck/CckTypeBuilder.vue';
import CckService, { type CckContentType, type CckFieldDefinition } from '../../services/cckService';

const { t } = useI18n();
const route = useRoute();
const router = useRouter();
const toast = useToast();

const typeId = computed(() => route.params.id as string | undefined);
const isCreate = computed(() => route.name === 'cck-create');

const activeTab = ref('schema');
const loading = ref(!isCreate.value);
const saving = ref(false);
const deleting = ref(false);
const deleteModalOpen = ref(false);

const openApiPreview = ref('');
const rawOpenApiDoc = ref<Record<string, unknown> | null>(null);
const validationRulesMap = ref<Record<string, string>>({});

const form = ref({
    name: '',
    slug: '',
    description: '',
    is_active: true,
    fields: [] as CckFieldDefinition[],
});

const cckSubtitle = computed(() => {
    const base = t('infra.cck.subtitle');
    const slug = form.value.slug;
    if (!slug) return base;
    return `${base} — ${t('infra.cck.apiHint', { slug })}`;
});

const dynamicEndpoints = computed(() => {
    const slug = form.value.slug || ':slug';
    return [
        { method: 'GET', path: `/api/v1/dynamic/${slug}`, label: t('infra.cck.form.endpoints.list') },
        { method: 'POST', path: `/api/v1/dynamic/${slug}`, label: t('infra.cck.form.endpoints.create') },
        { method: 'GET', path: `/api/v1/dynamic/${slug}/{id}`, label: t('infra.cck.form.endpoints.show') },
        { method: 'PUT', path: `/api/v1/dynamic/${slug}/{id}`, label: t('infra.cck.form.endpoints.update') },
        { method: 'DELETE', path: `/api/v1/dynamic/${slug}/{id}`, label: t('infra.cck.form.endpoints.delete') },
    ];
});

const validationRulesList = computed(() => {
    return Object.entries(validationRulesMap.value).map(([field, expression]) => ({
        field,
        expression,
    }));
});

function slugify(text: string): string {
    return text
        .toLowerCase()
        .trim()
        .replace(/[^a-z0-9]+/g, '_')
        .replace(/^_+|_+$/g, '');
}

function sanitizeSlug(): void {
    form.value.slug = form.value.slug.toLowerCase().replace(/[^a-z0-9_]/g, '');
}

function maybeSlugFromName(): void {
    if (isCreate.value && !form.value.slug) {
        form.value.slug = slugify(form.value.name);
    }
}

async function loadType(): Promise<void> {
    if (!typeId.value) return;
    loading.value = true;
    try {
        const response = await CckService.getType(typeId.value);
        const payload = parseSingleResponse<CckContentType>(response);
        if (!payload) {
            throw new Error('Content type not found');
        }
        form.value = {
            name: payload.name,
            slug: payload.slug,
            description: payload.description ?? '',
            is_active: payload.is_active !== false,
            fields: payload.fields ?? [],
        };
    } catch (e: unknown) {
        toast.error.default(e instanceof Error ? e.message : t('infra.cck.messages.loadFailed'));
    } finally {
        loading.value = false;
    }
}

async function loadValidationRules(): Promise<void> {
    if (!typeId.value) return;
    try {
        const response = await CckService.validationRules(typeId.value);
        const payload = parseSingleResponse<{ validation_rules: Record<string, string> }>(response);
        validationRulesMap.value = payload?.validation_rules ?? {};
    } catch {
        // Quiet fallback
    }
}

async function loadOpenApiSpec(): Promise<void> {
    if (!form.value.slug) return;
    try {
        const response = await CckService.getOpenApiBySlug(form.value.slug);
        const payload = parseSingleResponse<Record<string, unknown>>(response);
        rawOpenApiDoc.value = payload;
        openApiPreview.value = JSON.stringify(payload, null, 2);
    } catch {
        openApiPreview.value = '// OpenAPI specification will be available after saving.';
    }
}

async function copyUrl(path: string): Promise<void> {
    const fullUrl = `${window.location.origin}${path}`;
    try {
        await navigator.clipboard.writeText(fullUrl);
        toast.success.default(t('infra.cck.messages.endpointCopied'));
    } catch {
        prompt('URL:', fullUrl);
    }
}

async function copyOpenApiSpec(): Promise<void> {
    if (!openApiPreview.value) return;
    try {
        await navigator.clipboard.writeText(openApiPreview.value);
        toast.success.default(t('infra.cck.messages.specCopied'));
    } catch {
        prompt('OpenAPI Spec:', openApiPreview.value);
    }
}

function downloadOpenApiSpec(): void {
    if (!rawOpenApiDoc.value && !openApiPreview.value) return;
    const dataStr = 'data:text/json;charset=utf-8,' + encodeURIComponent(openApiPreview.value);
    const dlAnchor = document.createElement('a');
    dlAnchor.setAttribute('href', dataStr);
    dlAnchor.setAttribute('download', `dynamic-${form.value.slug}.openapi.json`);
    dlAnchor.click();
    dlAnchor.remove();
}

async function handleSave(): Promise<void> {
    saving.value = true;
    const payload = {
        name: form.value.name,
        slug: form.value.slug,
        description: form.value.description || null,
        fields: form.value.fields,
        ...(isCreate.value ? {} : { is_active: form.value.is_active }),
    };

    try {
        if (isCreate.value) {
            const response = await CckService.createType(payload);
            const created = parseSingleResponse<CckContentType>(response);
            toast.success.default(t('infra.cck.messages.created'));
            if (created?.id) {
                await router.replace({ name: 'cck-edit', params: { id: created.id } });
            }
        } else if (typeId.value) {
            await CckService.updateType(typeId.value, payload);
            toast.success.default(t('infra.cck.messages.saved'));
            await loadValidationRules();
            await loadOpenApiSpec();
        }
    } catch (e: unknown) {
        toast.error.default(e instanceof Error ? e.message : t('infra.cck.messages.saveFailed'));
    } finally {
        saving.value = false;
    }
}

async function executeDelete(): Promise<void> {
    if (!typeId.value) return;
    deleting.value = true;
    try {
        await CckService.deleteType(typeId.value);
        toast.success.default(t('infra.cck.messages.deleted'));
        deleteModalOpen.value = false;
        await router.push({ name: 'cck-index' });
    } catch (e: unknown) {
        toast.error.default(e instanceof Error ? e.message : t('infra.cck.messages.deleteFailed'));
    } finally {
        deleting.value = false;
    }
}

watch(
    activeTab,
    (tab) => {
        if (tab === 'api' && !openApiPreview.value) {
            loadOpenApiSpec();
        } else if (tab === 'rules' && Object.keys(validationRulesMap.value).length === 0) {
            loadValidationRules();
        }
    },
);

onMounted(async () => {
    if (!isCreate.value) {
        await loadType();
        await loadValidationRules();
        await loadOpenApiSpec();
    }
});
</script>
