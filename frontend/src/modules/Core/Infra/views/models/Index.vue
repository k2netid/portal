<template>
  <ConsoleListPage
    :title="$t('infra.models.title')"
    :subtitle="$t('infra.models.subtitle')"
    :borderless="true"
  >
    <template #actions>
      <Button
        size="sm"
        class="h-9 gap-2"
        @click="router.push({ name: 'model-create' })"
      >
        <Plus class="h-4 w-4" />
        {{ $t('infra.models.newType') }}
      </Button>
    </template>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <ConsoleStatCard
        :label="$t('infra.models.stats.totalTypes')"
        :value="types.length"
        :icon="Database"
        tone="primary"
      />
      <ConsoleStatCard
        :label="$t('infra.models.stats.activeTypes')"
        :value="activeTypesCount"
        :icon="CheckCircle"
        tone="success"
      />
      <ConsoleStatCard
        :label="$t('infra.models.stats.totalFields')"
        :value="totalFieldsCount"
        :icon="Layers"
        tone="info"
      />
      <ConsoleStatCard
        :label="$t('infra.models.stats.apiBase')"
        value="/api/v1/dynamic/*"
        :icon="Globe"
        tone="muted"
      />
    </div>

    <!-- Main List Card -->
    <ConsoleListCard>
      <template #toolbar>
        <div class="relative w-full sm:max-w-xs shrink-0">
          <Search class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
          <Input
            v-model="search"
            type="text"
            :placeholder="$t('infra.models.searchPlaceholder')"
            class="h-9 w-full pl-9 bg-background text-xs"
          />
        </div>
        <div class="flex items-center gap-2 ml-auto">
          <Button
            variant="ghost"
            size="sm"
            class="h-9 text-xs text-muted-foreground hover:text-foreground gap-1.5"
            :disabled="loading"
            @click="loadTypes"
          >
            <RefreshCw class="h-3.5 w-3.5" :class="{ 'animate-spin': loading }" />
          </Button>
        </div>
      </template>

      <!-- Loading State -->
      <div v-if="loading" class="p-12 text-center text-sm text-muted-foreground flex flex-col items-center justify-center gap-3">
        <Spinner class="h-6 w-6 text-primary" />
        <span>{{ $t('common.messages.loading.default') }}</span>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="p-6">
        <Alert variant="destructive">
          <AlertCircle class="h-4 w-4" />
          <AlertTitle>{{ $t('common.labels.error') }}</AlertTitle>
          <AlertDescription>{{ error }}</AlertDescription>
        </Alert>
      </div>

      <!-- Empty State -->
      <div
        v-else-if="filteredTypes.length === 0"
        class="p-12 text-center space-y-3"
      >
        <div class="mx-auto w-12 h-12 rounded-full bg-muted/60 flex items-center justify-center text-muted-foreground">
          <Database class="h-6 w-6" />
        </div>
        <div class="space-y-1 max-w-sm mx-auto">
          <h4 class="text-sm font-medium text-foreground">
            {{ search ? $t('infra.models.empty') : $t('infra.models.empty') }}
          </h4>
          <p class="text-xs text-muted-foreground">
            {{ search ? '' : $t('infra.models.emptyHint') }}
          </p>
        </div>
        <Button
          v-if="!search"
          size="sm"
          class="h-8 text-xs gap-1.5 mt-2"
          @click="router.push({ name: 'model-create' })"
        >
          <Plus class="h-3.5 w-3.5" />
          {{ $t('infra.models.newType') }}
        </Button>
      </div>

      <!-- Content Types Table -->
      <div v-else class="overflow-x-auto">
        <Table>
          <TableHeader>
            <TableRow class="bg-muted/40 hover:bg-muted/40">
              <TableHead class="text-xs font-semibold">{{ $t('infra.models.table.name') }}</TableHead>
              <TableHead class="text-xs font-semibold">{{ $t('infra.models.table.slug') }}</TableHead>
              <TableHead class="text-xs font-semibold">{{ $t('infra.models.table.fields') }}</TableHead>
              <TableHead class="text-xs font-semibold">{{ $t('infra.models.table.status') }}</TableHead>
              <TableHead class="text-xs font-semibold text-right">{{ $t('infra.models.table.actions') }}</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow
              v-for="type in filteredTypes"
              :key="type.id"
              class="hover:bg-muted/30 transition-colors"
            >
              <!-- Name & Description -->
              <TableCell class="py-3">
                <div class="space-y-0.5">
                  <div class="font-medium text-sm text-foreground flex items-center gap-2">
                    <span>{{ type.name }}</span>
                  </div>
                  <p v-if="type.description" class="text-xs text-muted-foreground line-clamp-1 max-w-md">
                    {{ type.description }}
                  </p>
                </div>
              </TableCell>

              <!-- Slug & API Route -->
              <TableCell class="py-3 font-mono text-xs">
                <div class="inline-flex items-center gap-1.5 px-2 py-1 rounded bg-muted/60 text-muted-foreground group">
                  <span class="text-foreground font-medium">/api/v1/dynamic/{{ type.slug }}</span>
                  <button
                    type="button"
                    class="opacity-0 group-hover:opacity-100 hover:text-primary transition-opacity"
                    :title="$t('infra.models.table.copyEndpoint')"
                    @click="copyEndpointUrl(type.slug)"
                  >
                    <Copy class="h-3 w-3" />
                  </button>
                </div>
              </TableCell>

              <!-- Fields Count -->
              <TableCell class="py-3">
                <Badge variant="outline" class="text-xs font-normal bg-background gap-1">
                  <Layers class="h-3 w-3 text-muted-foreground" />
                  {{ $t('infra.models.table.fieldsCount', { count: type.fields?.length ?? 0 }) }}
                </Badge>
              </TableCell>

              <!-- Status Badge -->
              <TableCell class="py-3">
                <Badge
                  v-if="type.is_active !== false"
                  variant="outline"
                  class="bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20 text-[11px] gap-1 px-2 py-0.5 font-normal"
                >
                  <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 inline-block"></span>
                  {{ $t('infra.models.table.active') }}
                </Badge>
                <Badge
                  v-else
                  variant="outline"
                  class="bg-muted text-muted-foreground border-border text-[11px] gap-1 px-2 py-0.5 font-normal"
                >
                  <span class="w-1.5 h-1.5 rounded-full bg-muted-foreground inline-block"></span>
                  {{ $t('infra.models.table.inactive') }}
                </Badge>
              </TableCell>

              <!-- Row Actions -->
              <TableCell class="py-3 text-right">
                <div class="inline-flex items-center justify-end gap-1.5">
                  <Button
                    size="sm"
                    class="h-8 text-xs gap-1.5"
                    @click="router.push({ name: 'dynamic-records-index', params: { slug: type.slug } })"
                  >
                    <Database class="h-3.5 w-3.5" />
                    {{ $t('infra.models.table.records') }}
                  </Button>
                  <Button
                    size="sm"
                    variant="outline"
                    class="h-8 text-xs gap-1.5"
                    @click="router.push({ name: 'model-edit', params: { id: type.id } })"
                  >
                    <SlidersHorizontal class="h-3.5 w-3.5" />
                    {{ $t('infra.models.table.schema') }}
                  </Button>

                  <DropdownMenu>
                    <DropdownMenuTrigger as-child>
                      <Button variant="ghost" size="icon" class="h-8 w-8 text-muted-foreground hover:text-foreground">
                        <MoreVertical class="h-4 w-4" />
                      </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end" class="w-48">
                      <DropdownMenuItem class="text-xs gap-2 cursor-pointer" @click="copyEndpointUrl(type.slug)">
                        <Copy class="h-3.5 w-3.5" />
                        {{ $t('infra.models.table.copyEndpoint') }}
                      </DropdownMenuItem>
                      <DropdownMenuItem class="text-xs gap-2 cursor-pointer" @click="downloadOpenApiSpec(type)">
                        <Download class="h-3.5 w-3.5" />
                        {{ $t('infra.models.table.downloadOpenApi') }}
                      </DropdownMenuItem>
                      <DropdownMenuSeparator />
                      <DropdownMenuItem
                        class="text-xs gap-2 text-destructive focus:text-destructive cursor-pointer"
                        @click="promptDeleteType(type)"
                      >
                        <Trash2 class="h-3.5 w-3.5" />
                        {{ $t('infra.models.delete') }}
                      </DropdownMenuItem>
                    </DropdownMenuContent>
                  </DropdownMenu>
                </div>
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </div>
    </ConsoleListCard>

    <!-- Delete Confirmation Modal -->
    <ConfirmModal
      :open="deleteModalOpen"
      :title="$t('infra.models.deleteType')"
      :message="$t('infra.models.deleteTypeConfirm', { name: typeToDelete?.name ?? '' })"
      :confirm-label="$t('infra.models.delete')"
      :cancel-label="$t('infra.models.cancel')"
      variant="destructive"
      :loading="deleting"
      @confirm="executeDeleteType"
      @cancel="deleteModalOpen = false"
    />
  </ConsoleListPage>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';
import {
  Database,
  CheckCircle,
  Layers,
  Globe,
  Plus,
  Search,
  RefreshCw,
  Copy,
  Download,
  Trash2,
  SlidersHorizontal,
  MoreVertical,
  AlertCircle,
} from 'lucide-vue-next';
import {
  ConsoleListPage,
  ConsoleListCard,
  ConsoleStatCard,
} from '@/shared/components/shell';
import {
  Button,
  Input,
  Badge,
  Spinner,
  Alert,
  AlertTitle,
  AlertDescription,
  Table,
  TableHeader,
  TableBody,
  TableRow,
  TableHead,
  TableCell,
  ConfirmModal,
  DropdownMenu,
  DropdownMenuTrigger,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuSeparator,
} from '@/shared/components/ui';
import { useToast } from '@/shared/composables/useToast';
import { parseResponse, parseSingleResponse } from '@/shared/utils/responseParser';
import DataModelService, { type DataModelSchema } from '../../services/dataModelService';

const { t } = useI18n();
const router = useRouter();
const toast = useToast();

const loading = ref(true);
const error = ref('');
const search = ref('');
const types = ref<DataModelSchema[]>([]);

// Delete confirmation state
const deleteModalOpen = ref(false);
const typeToDelete = ref<DataModelSchema | null>(null);
const deleting = ref(false);

const activeTypesCount = computed(() => {
    return types.value.filter((type) => type.is_active !== false).length;
});

const totalFieldsCount = computed(() => {
    return types.value.reduce((acc, curr) => acc + (curr.fields?.length ?? 0), 0);
});

const filteredTypes = computed(() => {
    const q = search.value.trim().toLowerCase();
    if (!q) return types.value;
    return types.value.filter((type) => {
        return (
            type.name.toLowerCase().includes(q) ||
            type.slug.toLowerCase().includes(q) ||
            (type.description && type.description.toLowerCase().includes(q))
        );
    });
});

async function loadTypes(): Promise<void> {
    loading.value = true;
    error.value = '';
    try {
        const response = await DataModelService.listTypes();
        const page = parseResponse<DataModelSchema>(response);
        types.value = page.data ?? [];
    } catch (e: unknown) {
        error.value = e instanceof Error ? e.message : t('infra.models.messages.loadFailed');
    } finally {
        loading.value = false;
    }
}

async function copyEndpointUrl(slug: string): Promise<void> {
    const fullUrl = `${window.location.origin}/api/v1/dynamic/${slug}`;
    try {
        await navigator.clipboard.writeText(fullUrl);
        toast.success.default(t('infra.models.messages.endpointCopied'));
    } catch {
        // Fallback
        prompt('Endpoint URL:', fullUrl);
    }
}

async function downloadOpenApiSpec(type: DataModelSchema): Promise<void> {
    try {
        const res = await DataModelService.getOpenApiBySlug(type.slug);
        const payload = parseSingleResponse<Record<string, unknown>>(res);
        const dataStr = 'data:text/json;charset=utf-8,' + encodeURIComponent(JSON.stringify(payload, null, 2));
        const dlAnchor = document.createElement('a');
        dlAnchor.setAttribute('href', dataStr);
        dlAnchor.setAttribute('download', `dynamic-${type.slug}.openapi.json`);
        dlAnchor.click();
        dlAnchor.remove();
    } catch (e: unknown) {
        toast.error.default(e instanceof Error ? e.message : 'Failed to download OpenAPI specification');
    }
}

function promptDeleteType(type: DataModelSchema): void {
    typeToDelete.value = type;
    deleteModalOpen.value = true;
}

async function executeDeleteType(): Promise<void> {
    if (!typeToDelete.value) return;
    deleting.value = true;
    try {
        await DataModelService.deleteType(typeToDelete.value.id);
        toast.success.default(t('infra.models.messages.deleted'));
        deleteModalOpen.value = false;
        typeToDelete.value = null;
        await loadTypes();
    } catch (e: unknown) {
        toast.error.default(e instanceof Error ? e.message : t('infra.models.messages.deleteFailed'));
    } finally {
        deleting.value = false;
    }
}

onMounted(() => {
    loadTypes();
});
</script>
