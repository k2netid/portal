<template>
  <ConsoleListPage
    :title="contentType?.name ? $t('infra.dynamic.records.title', { name: contentType.name }) : 'Records'"
    :subtitle="contentType?.name ? $t('infra.dynamic.records.subtitle', { name: contentType.name }) : ''"
    :borderless="true"
  >
    <template #actions>
      <div class="flex items-center gap-2">
        <Button
          variant="ghost"
          size="sm"
          class="h-9 gap-1.5 text-xs text-muted-foreground hover:text-foreground"
          @click="router.push({ name: 'model-index' })"
        >
          <ArrowLeft class="h-3.5 w-3.5" />
          {{ $t('infra.models.back') }}
        </Button>
        <Button
          variant="outline"
          size="sm"
          class="h-9 gap-1.5 text-xs"
          :disabled="!contentType"
          @click="router.push({ name: 'model-edit', params: { id: contentType?.id } })"
        >
          <SlidersHorizontal class="h-3.5 w-3.5" />
          {{ $t('infra.models.table.schema') }}
        </Button>
        <Button
          size="sm"
          class="h-9 gap-2 text-xs"
          :disabled="!contentType"
          @click="router.push({ name: 'dynamic-records-create', params: { slug } })"
        >
          <Plus class="h-3.5 w-3.5" />
          {{ $t('infra.dynamic.records.newRecord') }}
        </Button>
      </div>
    </template>

    <!-- Main List Card -->
    <ConsoleListCard>
      <template #toolbar>
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 w-full">
          <!-- Search Box -->
          <div class="relative w-full sm:max-w-xs shrink-0">
            <Search class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
            <Input
              v-model="searchQuery"
              type="text"
              :placeholder="$t('infra.dynamic.records.searchPlaceholder')"
              class="h-9 w-full pl-9 bg-background text-xs"
              @input="onSearchInput"
            />
          </div>

          <!-- API Endpoint Pill -->
          <div class="flex items-center gap-2 text-xs font-mono text-muted-foreground">
            <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded bg-muted/60 text-muted-foreground group">
              <Globe class="h-3.5 w-3.5 text-primary shrink-0" />
              <span class="text-foreground font-medium">/api/v1/dynamic/{{ slug }}</span>
              <button
                type="button"
                class="hover:text-primary transition-colors"
                :title="$t('infra.models.table.copyEndpoint')"
                @click="copyEndpointUrl"
              >
                <Copy class="h-3 w-3" />
              </button>
            </div>
            <Button
              variant="ghost"
              size="sm"
              class="h-8 text-xs text-muted-foreground hover:text-foreground"
              :disabled="loading"
              @click="loadRecords"
            >
              <RefreshCw class="h-3.5 w-3.5" :class="{ 'animate-spin': loading }" />
            </Button>
          </div>
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
        v-else-if="records.length === 0"
        class="p-12 text-center space-y-3"
      >
        <div class="mx-auto w-12 h-12 rounded-full bg-muted/60 flex items-center justify-center text-muted-foreground">
          <Database class="h-6 w-6" />
        </div>
        <div class="space-y-1 max-w-sm mx-auto">
          <h4 class="text-sm font-medium text-foreground">
            {{ $t('infra.dynamic.records.empty') }}
          </h4>
          <p class="text-xs text-muted-foreground">
            {{ searchQuery ? '' : $t('infra.dynamic.records.emptyHint') }}
          </p>
        </div>
        <Button
          v-if="!searchQuery"
          size="sm"
          class="h-8 text-xs gap-1.5 mt-2"
          @click="router.push({ name: 'dynamic-records-create', params: { slug } })"
        >
          <Plus class="h-3.5 w-3.5" />
          {{ $t('infra.dynamic.records.createFirst') }}
        </Button>
      </div>

      <!-- Dynamic Records Table -->
      <div v-else class="overflow-x-auto">
        <Table>
          <TableHeader>
            <TableRow class="bg-muted/40 hover:bg-muted/40">
              <TableHead
                v-for="col in displayColumns"
                :key="col.slug"
                class="text-xs font-semibold"
              >
                {{ col.name }}
              </TableHead>
              <TableHead class="text-xs font-semibold">
                {{ $t('infra.dynamic.records.table.updatedAt') }}
              </TableHead>
              <TableHead class="text-xs font-semibold text-right">
                {{ $t('infra.dynamic.records.table.actions') }}
              </TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow
              v-for="row in records"
              :key="row.id"
              class="hover:bg-muted/30 transition-colors"
            >
              <!-- Dynamic Field Values -->
              <TableCell
                v-for="col in displayColumns"
                :key="col.slug"
                class="py-3 text-xs"
              >
                <!-- Boolean Field -->
                <Badge
                  v-if="col.type === 'boolean'"
                  variant="outline"
                  class="text-[11px] font-normal"
                  :class="row.data?.[col.slug] ? 'bg-emerald-500/10 text-emerald-600 border-emerald-500/20' : 'bg-muted text-muted-foreground'"
                >
                  {{ row.data?.[col.slug] ? $t('infra.dynamic.record.booleanYes') : $t('infra.dynamic.record.booleanNo') }}
                </Badge>

                <!-- Relation Field (Hydrated or ID) -->
                <Badge
                  v-else-if="col.type === 'relation' && row.data?.[col.slug]"
                  variant="outline"
                  class="text-[11px] font-medium gap-1 bg-primary/5 text-primary border-primary/20"
                >
                  <Network class="h-3 w-3" />
                  {{ getRelationLabel(row, col.slug) }}
                </Badge>

                <!-- Color Swatch Field -->
                <div
                  v-else-if="col.type === 'color' && row.data?.[col.slug]"
                  class="flex items-center gap-1.5 font-mono text-[11px]"
                >
                  <div
                    class="w-3.5 h-3.5 rounded border border-border/80 shadow-xs shrink-0"
                    :style="{ backgroundColor: String(row.data[col.slug]) }"
                  />
                  <span>{{ row.data[col.slug] }}</span>
                </div>

                <!-- URL Field -->
                <a
                  v-else-if="col.type === 'url' && row.data?.[col.slug]"
                  :href="String(row.data[col.slug])"
                  target="_blank"
                  rel="noopener noreferrer"
                  class="inline-flex items-center gap-1 text-primary hover:underline text-xs truncate max-w-[180px]"
                >
                  <ExternalLink class="h-3 w-3 shrink-0" />
                  <span class="truncate">{{ row.data[col.slug] }}</span>
                </a>

                <!-- Image / Media Field -->
                <div v-else-if="(col.type === 'image' || col.type === 'media') && row.data?.[col.slug]" class="flex items-center gap-2">
                  <div class="w-8 h-8 rounded border overflow-hidden bg-muted shrink-0">
                    <img :src="String(row.data[col.slug])" alt="media" class="w-full h-full object-cover">
                  </div>
                </div>

                <!-- Rich Text Summary -->
                <span
                  v-else-if="col.type === 'richtext' && row.data?.[col.slug]"
                  class="font-normal text-muted-foreground truncate max-w-xs block"
                >
                  {{ stripHtml(String(row.data[col.slug])) }}
                </span>

                <!-- Select Field -->
                <Badge
                  v-else-if="col.type === 'select' && row.data?.[col.slug]"
                  variant="secondary"
                  class="text-[11px] font-mono font-normal"
                >
                  {{ row.data[col.slug] }}
                </Badge>

                <!-- Default Text / Number / Email / Date -->
                <span v-else class="font-normal text-foreground truncate max-w-xs block">
                  {{ formatCellValue(row.data?.[col.slug]) }}
                </span>
              </TableCell>

              <!-- Updated At -->
              <TableCell class="py-3 text-xs text-muted-foreground whitespace-nowrap">
                {{ row.updated_at ? formatDate(row.updated_at) : '—' }}
              </TableCell>

              <!-- Actions -->
              <TableCell class="py-3 text-right">
                <div class="inline-flex items-center justify-end gap-1.5">
                  <Button
                    size="sm"
                    variant="outline"
                    class="h-8 text-xs gap-1.5"
                    @click="router.push({ name: 'dynamic-records-edit', params: { slug, recordId: row.id } })"
                  >
                    <Pencil class="h-3.5 w-3.5" />
                    {{ $t('infra.dynamic.records.actions.edit') }}
                  </Button>
                  <Button
                    size="sm"
                    variant="ghost"
                    class="h-8 text-xs text-destructive hover:bg-destructive/10"
                    @click="promptDeleteRecord(row)"
                  >
                    <Trash2 class="h-3.5 w-3.5" />
                  </Button>
                </div>
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </div>

      <!-- Pagination Footer -->
      <template v-if="pagination.total > pagination.perPage" #footer>
        <div class="flex items-center justify-between text-xs text-muted-foreground w-full">
          <div>
            Showing {{ paginationFrom }} to {{ paginationTo }} of {{ pagination.total }} records
          </div>
          <div class="flex items-center gap-1.5">
            <Button
              variant="outline"
              size="sm"
              class="h-8 px-2.5 text-xs"
              :disabled="pagination.currentPage <= 1 || loading"
              @click="changePage(pagination.currentPage - 1)"
            >
              Previous
            </Button>
            <span class="px-2 font-medium text-foreground">
              {{ pagination.currentPage }} / {{ pagination.lastPage }}
            </span>
            <Button
              variant="outline"
              size="sm"
              class="h-8 px-2.5 text-xs"
              :disabled="pagination.currentPage >= pagination.lastPage || loading"
              @click="changePage(pagination.currentPage + 1)"
            >
              Next
            </Button>
          </div>
        </div>
      </template>
    </ConsoleListCard>

    <!-- Delete Confirmation Modal -->
    <ConfirmModal
      :open="deleteModalOpen"
      :title="$t('infra.dynamic.records.actions.delete')"
      :message="$t('infra.dynamic.records.confirm.delete')"
      :confirm-label="$t('infra.dynamic.records.actions.delete')"
      :cancel-label="$t('infra.models.cancel')"
      variant="destructive"
      :loading="deleting"
      @confirm="executeDeleteRecord"
      @cancel="deleteModalOpen = false"
    />
  </ConsoleListPage>
</template>

<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';
import {
  Database,
  Plus,
  Search,
  RefreshCw,
  Copy,
  Globe,
  SlidersHorizontal,
  ArrowLeft,
  Pencil,
  Trash2,
  AlertCircle,
  Network,
  ExternalLink,
} from 'lucide-vue-next';
import {
  ConsoleListPage,
  ConsoleListCard,
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
} from '@/shared/components/ui';
import { useToast } from '@/shared/composables/useToast';
import { parseResponse, parseSingleResponse } from '@/shared/utils/responseParser';
import DataModelService, { type DataModelSchema, type DataModelFieldDefinition } from '../../services/dataModelService';
import DynamicRecordService, { type DynamicRecordRow } from '../../services/dynamicRecordService';

const { t } = useI18n();
const route = useRoute();
const router = useRouter();
const toast = useToast();

const slug = ref(String(route.params.slug ?? ''));
const loading = ref(true);
const error = ref('');
const contentType = ref<DataModelSchema | null>(null);
const records = ref<DynamicRecordRow[]>([]);

// Search and Pagination
const searchQuery = ref('');
let searchTimer: ReturnType<typeof setTimeout> | null = null;

const pagination = ref({
    currentPage: 1,
    lastPage: 1,
    perPage: 15,
    total: 0,
});

const paginationFrom = computed(() => {
    if (pagination.value.total === 0) return 0;
    return (pagination.value.currentPage - 1) * pagination.value.perPage + 1;
});

const paginationTo = computed(() => {
    return Math.min(pagination.value.currentPage * pagination.value.perPage, pagination.value.total);
});

// Delete modal state
const deleteModalOpen = ref(false);
const recordToDelete = ref<DynamicRecordRow | null>(null);
const deleting = ref(false);

const displayColumns = computed<DataModelFieldDefinition[]>(() => {
    const fields = contentType.value?.fields ?? [];
    return fields.slice(0, 4);
});

function formatDate(iso: string): string {
    try {
        return new Date(iso).toLocaleString();
    } catch {
        return iso;
    }
}

function formatCellValue(val: unknown): string {
    if (val === null || val === undefined || val === '') {
        return '—';
    }
    return String(val);
}

function getRelationLabel(row: DynamicRecordRow, fieldSlug: string): string {
    const rel = row._relations?.[fieldSlug] as Record<string, unknown> | undefined;
    if (rel && rel.data && typeof rel.data === 'object') {
        const d = rel.data as Record<string, unknown>;
        const titleCandidates = ['title', 'name', 'label', 'heading', 'email', 'slug', 'project_name'];
        for (const key of titleCandidates) {
            if (d[key] && typeof d[key] === 'string') {
                return String(d[key]);
            }
        }
    }
    const rawVal = row.data?.[fieldSlug];
    if (typeof rawVal === 'string') {
        return `#${rawVal.slice(0, 8)}`;
    }
    return String(rawVal || '—');
}

function stripHtml(html: string): string {
    return html.replace(/<[^>]*>?/gm, '').trim();
}

function onSearchInput(): void {
    if (searchTimer) clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
        pagination.value.currentPage = 1;
        loadRecords();
    }, 300);
}

async function loadContentType(): Promise<void> {
    try {
        const res = await DataModelService.getTypeBySlug(slug.value);
        contentType.value = parseSingleResponse<DataModelSchema>(res);
    } catch (e: unknown) {
        error.value = e instanceof Error ? e.message : 'Data model not found';
    }
}

async function loadRecords(): Promise<void> {
    loading.value = true;
    error.value = '';
    try {
        const params: Record<string, string | number> = {
            page: pagination.value.currentPage,
            per_page: pagination.value.perPage,
        };
        if (searchQuery.value.trim()) {
            params.search = searchQuery.value.trim();
        }
        const res = await DynamicRecordService.list(slug.value, params);
        const page = parseResponse<DynamicRecordRow>(res);
        records.value = page.data ?? [];
        pagination.value.total = page.pagination?.total ?? records.value.length;
        pagination.value.lastPage = page.pagination?.last_page ?? 1;
        pagination.value.currentPage = page.pagination?.current_page ?? 1;
    } catch (e: unknown) {
        error.value = e instanceof Error ? e.message : t('infra.dynamic.records.messages.loadFailed');
    } finally {
        loading.value = false;
    }
}

function changePage(page: number): void {
    if (page < 1 || page > pagination.value.lastPage) return;
    pagination.value.currentPage = page;
    loadRecords();
}

async function copyEndpointUrl(): Promise<void> {
    const fullUrl = `${window.location.origin}/api/v1/dynamic/${slug.value}`;
    try {
        await navigator.clipboard.writeText(fullUrl);
        toast.success.default(t('infra.models.messages.endpointCopied'));
    } catch {
        prompt('Endpoint URL:', fullUrl);
    }
}

function promptDeleteRecord(row: DynamicRecordRow): void {
    recordToDelete.value = row;
    deleteModalOpen.value = true;
}

async function executeDeleteRecord(): Promise<void> {
    if (!recordToDelete.value) return;
    deleting.value = true;
    try {
        await DynamicRecordService.remove(slug.value, recordToDelete.value.id);
        toast.success.default(t('infra.dynamic.records.messages.deleted'));
        deleteModalOpen.value = false;
        recordToDelete.value = null;
        await loadRecords();
    } catch (e: unknown) {
        toast.error.default(e instanceof Error ? e.message : t('infra.dynamic.records.messages.deleteFailed'));
    } finally {
        deleting.value = false;
    }
}

watch(
    () => route.params.slug,
    async (newSlug) => {
        if (newSlug) {
            slug.value = String(newSlug);
            await loadContentType();
            await loadRecords();
        }
    },
);

onMounted(async () => {
    if (slug.value) {
        await loadContentType();
        await loadRecords();
    }
});
</script>
