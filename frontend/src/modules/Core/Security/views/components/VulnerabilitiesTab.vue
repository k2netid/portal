<template>
  <Card>
    <CardHeader class="flex flex-row items-center justify-between pb-4">
      <div>
        <CardTitle class="text-lg">
          {{ showAllPackages ? $t('system.security.packages.title') : $t('system.security.vulnerabilities.title') }}
        </CardTitle>
        <CardDescription>{{ showAllPackages ? $t('system.security.packages.description') : $t('system.security.vulnerabilities.description') }}</CardDescription>
      </div>
      <div class="flex items-center gap-3">
        <!-- Toggle All Packages -->
        <div class="flex items-center gap-2">
          <Label class="text-sm text-muted-foreground">{{ $t('system.security.packages.showAll') }}</Label>
          <Switch
            v-model:checked="showAllPackages"
            @update:checked="onToggleMode"
          />
        </div>
        <Button
          variant="default"
          size="sm"
          :disabled="auditRunning"
          @click="$emit('run-audit')"
        >
          <Loader2
            v-if="auditRunning"
            class="w-4 h-4 mr-2"
          />
          <ShieldAlert
            v-else
            class="w-4 h-4 mr-2"
          />
          {{ $t('system.security.vulnerabilities.runAudit') }}
        </Button>
        <Button
          variant="outline"
          size="sm"
          :disabled="loading || packagesLoading"
          @click="handleRefresh"
        >
          <RefreshCw class="w-4 h-4 mr-2" />
          {{ $t('common.actions.refresh') }}
        </Button>
      </div>
    </CardHeader>

    <!-- Statistics -->
    <div class="px-6 pb-4 grid grid-cols-1 md:grid-cols-5 gap-4">
      <template v-if="showAllPackages">
        <div class="bg-muted/30 rounded-lg p-4">
          <p class="text-sm text-muted-foreground">
            {{ $t('system.security.packages.stats.total') }}
          </p>
          <p class="text-2xl font-bold text-foreground">
            {{ packageStats?.total || 0 }}
          </p>
        </div>
        <div class="bg-muted/30 rounded-lg p-4">
          <p class="text-sm text-muted-foreground">
            {{ $t('system.security.packages.stats.composer') }}
          </p>
          <p class="text-2xl font-bold text-purple-600">
            {{ packageStats?.composer || 0 }}
          </p>
        </div>
        <div class="bg-muted/30 rounded-lg p-4">
          <p class="text-sm text-muted-foreground">
            {{ $t('system.security.packages.stats.npm') }}
          </p>
          <p class="text-2xl font-bold text-blue-600">
            {{ packageStats?.npm || 0 }}
          </p>
        </div>
        <div class="bg-muted/30 rounded-lg p-4">
          <p class="text-sm text-muted-foreground">
            {{ $t('system.security.packages.stats.secure') }}
          </p>
          <p class="text-2xl font-bold text-green-600">
            {{ packageStats?.secure || 0 }}
          </p>
        </div>
        <div class="bg-muted/30 rounded-lg p-4">
          <p class="text-sm text-muted-foreground">
            {{ $t('system.security.packages.stats.vulnerable') }}
          </p>
          <p class="text-2xl font-bold text-red-600">
            {{ packageStats?.vulnerable || 0 }}
          </p>
        </div>
      </template>
      <template v-else>
        <div class="bg-muted/30 rounded-lg p-4">
          <p class="text-sm text-muted-foreground">
            {{ $t('system.security.vulnerabilities.stats.total') }}
          </p>
          <p class="text-2xl font-bold text-foreground">
            {{ stats.total || 0 }}
          </p>
        </div>
        <div class="bg-muted/30 rounded-lg p-4">
          <p class="text-sm text-muted-foreground">
            {{ $t('system.security.vulnerabilities.stats.critical') }}
          </p>
          <p class="text-2xl font-bold text-red-600">
            {{ stats.critical || 0 }}
          </p>
        </div>
        <div class="bg-muted/30 rounded-lg p-4">
          <p class="text-sm text-muted-foreground">
            {{ $t('system.security.vulnerabilities.stats.high') }}
          </p>
          <p class="text-2xl font-bold text-orange-600">
            {{ stats.high || 0 }}
          </p>
        </div>
        <div class="bg-muted/30 rounded-lg p-4">
          <p class="text-sm text-muted-foreground">
            {{ $t('system.security.vulnerabilities.stats.medium') }}
          </p>
          <p class="text-2xl font-bold text-yellow-600">
            {{ stats.medium || 0 }}
          </p>
        </div>
        <div class="bg-muted/30 rounded-lg p-4">
          <p class="text-sm text-muted-foreground">
            {{ $t('system.security.vulnerabilities.stats.low') }}
          </p>
          <p class="text-2xl font-bold text-blue-600">
            {{ stats.low || 0 }}
          </p>
        </div>
        <div
          v-if="(stats.patched ?? 0) > 0"
          class="bg-muted/20 rounded-lg p-4 border border-dashed border-border"
        >
          <p class="text-sm text-muted-foreground">
            {{ $t('system.security.vulnerabilities.stats.patched', 'Patched (history)') }}
          </p>
          <p class="text-2xl font-bold text-muted-foreground">
            {{ stats.patched }}
          </p>
        </div>
      </template>
    </div>

    <!-- Filters -->
    <div class="px-6 pb-4">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
          <Label class="mb-2 block">{{ $t('system.security.vulnerabilities.filters.source') }}</Label>
          <Select v-model="localFilters.source">
            <SelectTrigger>
              <SelectValue :placeholder="$t('common.labels.all')" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="all">
                {{ $t('common.labels.all') }}
              </SelectItem>
              <SelectItem value="composer">
                {{ $t('system.security.vulnerabilities.source.composer') }}
              </SelectItem>
              <SelectItem value="npm">
                {{ $t('system.security.vulnerabilities.source.npm') }}
              </SelectItem>
            </SelectContent>
          </Select>
        </div>
        <template v-if="!showAllPackages">
          <div>
            <Label class="mb-2 block">{{ $t('system.security.vulnerabilities.filters.severity') }}</Label>
            <Select v-model="localFilters.severity">
              <SelectTrigger>
                <SelectValue :placeholder="$t('common.labels.all')" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all">
                  {{ $t('common.labels.all') }}
                </SelectItem>
                <SelectItem value="critical">
                  {{ $t('system.security.vulnerabilities.severity.critical') }}
                </SelectItem>
                <SelectItem value="high">
                  {{ $t('system.security.vulnerabilities.severity.high') }}
                </SelectItem>
                <SelectItem value="medium">
                  {{ $t('system.security.vulnerabilities.severity.medium') }}
                </SelectItem>
                <SelectItem value="low">
                  {{ $t('system.security.vulnerabilities.severity.low') }}
                </SelectItem>
              </SelectContent>
            </Select>
          </div>
          <div>
            <Label class="mb-2 block">{{ $t('system.security.vulnerabilities.filters.status') }}</Label>
            <Select v-model="localFilters.status">
              <SelectTrigger>
                <SelectValue :placeholder="$t('common.labels.all')" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all">
                  {{ $t('common.labels.all') }}
                </SelectItem>
                <SelectItem value="new">
                  {{ $t('system.security.vulnerabilities.status.new') }}
                </SelectItem>
                <SelectItem value="acknowledged">
                  {{ $t('system.security.vulnerabilities.status.acknowledged') }}
                </SelectItem>
                <SelectItem value="patched">
                  {{ $t('system.security.vulnerabilities.status.patched') }}
                </SelectItem>
                <SelectItem value="ignored">
                  {{ $t('system.security.vulnerabilities.status.ignored') }}
                </SelectItem>
              </SelectContent>
            </Select>
          </div>
        </template>
        <div v-else>
          <Label class="mb-2 block">{{ $t('system.security.packages.filters.status') }}</Label>
          <Select v-model="packageFilters.status">
            <SelectTrigger>
              <SelectValue :placeholder="$t('common.labels.all')" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="all">
                {{ $t('common.labels.all') }}
              </SelectItem>
              <SelectItem value="secure">
                {{ $t('system.security.packages.status.secure') }}
              </SelectItem>
              <SelectItem value="vulnerable">
                {{ $t('system.security.packages.status.vulnerable') }}
              </SelectItem>
            </SelectContent>
          </Select>
        </div>
        <div>
          <Label class="mb-2 block">{{ $t('system.security.vulnerabilities.filters.package') }}</Label>
          <Input 
            :model-value="showAllPackages ? packageFilters.package : localFilters.package" 
            :placeholder="$t('system.security.vulnerabilities.filters.searchPackage')"
            @update:model-value="handlePackageSearch" 
          />
        </div>
      </div>
      <div class="flex justify-end gap-2 mt-4">
        <Button
          size="sm"
          @click="handleApplyFilters"
        >
          {{ $t('common.actions.apply') }}
        </Button>
        <Button
          variant="outline"
          size="sm"
          @click="handleResetFilters"
        >
          {{ $t('common.actions.reset') }}
        </Button>
      </div>
    </div>

    <CardContent class="p-0">
      <DataTable
        v-if="showAllPackages"
        :table="(packagesTable as unknown as typeof table)"
        :loading="packagesLoading"
        :empty-message="t('system.security.packages.empty')"
      />
      <DataTable
        v-else
        :table="table"
        :loading="loading"
        :empty-message="t('system.security.vulnerabilities.empty')"
      />
    </CardContent>
    <Pagination
      v-if="currentPagination.total > 0"
      :current-page="currentPagination.current_page"
      :total-items="currentPagination.total"
      :per-page="showAllPackages ? packageFilters.per_page : filters.per_page"
      embedded
      @page-change="handlePageChange"
      @per-page-change="handlePerPageChange"
    />
  </Card>
</template>

<script setup lang="ts">
import { ref, computed, h, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { useVueTable, getCoreRowModel, getSortedRowModel, createColumnHelper, type SortingState } from '@tanstack/vue-table';
import api from '@/engine/api/client';
import { logger } from '@/shared/utils/logger';
import {
    Card, CardHeader, CardTitle, CardDescription, CardContent,
    Button, Badge, Input, Label, DataTable, Pagination, Switch,
    Select, SelectTrigger, SelectValue, SelectContent, SelectItem
} from '@/shared/components/ui';

import {
  Loader2,
  RefreshCw,
  ShieldAlert,
} from 'lucide-vue-next';

interface Vulnerability {
    id: string;
    package_name: string;
    version: string;
    severity: string;
    cve: string | null;
    source: string;
    status: string;
}

interface Package {
    name: string;
    version: string;
    source: string;
    status: string;
    vulnerability_id: string | null;
}

interface VulnStats {
    total: number;
    critical: number;
    high: number;
    medium: number;
    low: number;
    patched?: number;
}

interface PackageStats {
    total: number;
    composer: number;
    npm: number;
    secure: number;
    vulnerable: number;
}

interface PaginationInfo {
    total: number;
    current_page: number;
    last_page: number;
}

interface VulnFilters {
    source: string;
    severity: string;
    status: string;
    package: string;
    page: number;
    per_page: number;
}

const props = defineProps<{
    vulnerabilities: Vulnerability[];
    stats: VulnStats;
    loading: boolean;
    auditRunning: boolean;
    pagination: PaginationInfo;
    filters: VulnFilters;
}>();

const emit = defineEmits<{
    'refresh': [];
    'run-audit': [];
    'apply-filters': [];
    'reset-filters': [];
    'update-status': [vuln: Vulnerability, status: string];
    'page-change': [page: number];
    'per-page-change': [perPage: number];
}>();

const { t } = useI18n();

// Local state
const sorting = ref<SortingState>([]);
const localFilters = ref({ ...props.filters });
const showAllPackages = ref(false);

// All Packages state
const packages = ref<Package[]>([]);
const packageStats = ref<PackageStats | null>(null);
const packagesLoading = ref(false);
const packageFilters = ref({ source: 'all', status: 'all', package: '', page: 1, per_page: 50 });
const packagePagination = ref<PaginationInfo>({ total: 0, current_page: 1, last_page: 1 });

const currentPagination = computed(() => showAllPackages.value ? packagePagination.value : props.pagination);

// Sync filters with parent
watch(() => props.filters, (newFilters) => {
    localFilters.value = { ...newFilters };
}, { deep: true });

// Fetch all packages
const fetchPackages = async (): Promise<void> => {
    packagesLoading.value = true;
    try {
        const params: Record<string, string | number> = { ...packageFilters.value };
        if (params.source === 'all') delete params.source;
        if (params.status === 'all') delete params.status;
        
        const response = await api.get('/manage/security/dependency-packages', { params });
        const result = response.data;
        packages.value = (result.data as Package[]) || [];
        packagePagination.value = {
            total: result.total || 0,
            current_page: result.current_page || 1,
            last_page: result.last_page || 1
        };
    } catch (error: unknown) {
        logger.error('Failed to fetch packages:', error);
    } finally {
        packagesLoading.value = false;
    }
};

const fetchPackageStats = async (): Promise<void> => {
    try {
        const response = await api.get('/manage/security/dependency-packages/statistics');
        packageStats.value = (response.data as PackageStats) || null;
    } catch (error: unknown) {
        logger.error('Failed to fetch package stats:', error);
    }
};

const onToggleMode = (checked: boolean) => {
    showAllPackages.value = checked;
    if (checked && packages.value.length === 0) {
        fetchPackages();
        fetchPackageStats();
    }
};

const handleRefresh = () => {
    if (showAllPackages.value) {
        fetchPackages();
        fetchPackageStats();
    } else {
        emit('refresh');
    }
};

const handleApplyFilters = () => {
    if (showAllPackages.value) {
        packageFilters.value.page = 1;
        fetchPackages();
    } else {
        emit('apply-filters');
    }
};

const handleResetFilters = () => {
    if (showAllPackages.value) {
        packageFilters.value = { source: 'all', status: 'all', package: '', page: 1, per_page: 50 };
        fetchPackages();
    } else {
        emit('reset-filters');
    }
};

const handlePackageSearch = (v: string | number) => {
    const value = String(v);
    if (showAllPackages.value) {
        packageFilters.value.package = value;
    } else {
        localFilters.value.package = value;
    }
};

const handlePageChange = (page: number) => {
    if (showAllPackages.value) {
        packageFilters.value.page = page;
        fetchPackages();
    } else {
        emit('page-change', page);
    }
};

const handlePerPageChange = (perPage: number) => {
    if (showAllPackages.value) {
        packageFilters.value.per_page = perPage;
        packageFilters.value.page = 1;
        fetchPackages();
    } else {
        emit('per-page-change', perPage);
    }
};

const getSeverityVariant = (severity: string): "destructive" | "warning" | "secondary" | "outline" => {
    const variants: Record<string, "destructive" | "warning" | "secondary" | "outline"> = {
        critical: 'destructive',
        high: 'warning',
        medium: 'secondary',
        low: 'outline'
    };
    return variants[severity] || 'secondary';
};

const getStatusVariant = (status: string): "destructive" | "warning" | "secondary" | "outline" | "default" => {
    const variants: Record<string, "destructive" | "warning" | "secondary" | "outline" | "default"> = {
        new: 'destructive',
        acknowledged: 'warning',
        patched: 'secondary',
        ignored: 'outline',
        secure: 'default',
        vulnerable: 'destructive'
    };
    return variants[status] || 'secondary';
};

// TanStack Table for Vulnerabilities
const columnHelper = createColumnHelper<Vulnerability>();

const columns = [
    columnHelper.accessor('package_name', {
        header: t('system.security.vulnerabilities.table.package'),
        cell: ({ row }) => h('span', { class: 'font-medium' }, row.original.package_name)
    }),
    columnHelper.accessor('version', {
        header: t('system.security.vulnerabilities.table.version'),
        cell: ({ row }) => h('span', { class: 'font-mono text-sm' }, row.original.version)
    }),
    columnHelper.accessor('severity', {
        header: t('system.security.vulnerabilities.table.severity'),
        cell: ({ row }) => h(Badge, { variant: getSeverityVariant(row.original.severity) }, () => t(`security.vulnerabilities.severity.${row.original.severity}`))
    }),
    columnHelper.accessor('cve', {
        header: t('system.security.vulnerabilities.table.cve'),
        cell: ({ row }) => row.original.cve
            ? h('a', {
                href: `https://nvd.nist.gov/vuln/detail/${row.original.cve}`,
                target: '_blank',
                class: 'text-primary hover:underline text-sm'
            }, row.original.cve)
            : h('span', { class: 'text-muted-foreground' }, '-')
    }),
    columnHelper.accessor('source', {
        header: t('system.security.vulnerabilities.table.source'),
        cell: ({ row }) => h(Badge, { variant: 'outline' }, () => t(`security.vulnerabilities.source.${row.original.source}`))
    }),
    columnHelper.accessor('status', {
        header: t('system.security.vulnerabilities.table.status'),
        cell: ({ row }) => h(Badge, { variant: getStatusVariant(row.original.status) }, () => t(`security.vulnerabilities.status.${row.original.status}`))
    }),
    columnHelper.display({
        id: 'actions',
        header: () => h('div', { class: 'text-right' }, t('system.security.vulnerabilities.table.actions')),
        cell: ({ row }) => h('div', { class: 'flex justify-end' }, [
            h(Select, {
                modelValue: row.original.status,
                'onUpdate:modelValue': (value: string) => emit('update-status', row.original, value)
            }, () => [
                h(SelectTrigger, { class: 'w-32 h-8' }, () => h(SelectValue)),
                h(SelectContent, {}, () => [
                    h(SelectItem, { value: 'new' }, () => t('system.security.vulnerabilities.status.new')),
                    h(SelectItem, { value: 'acknowledged' }, () => t('system.security.vulnerabilities.status.acknowledged')),
                    h(SelectItem, { value: 'patched' }, () => t('system.security.vulnerabilities.status.patched')),
                    h(SelectItem, { value: 'ignored' }, () => t('system.security.vulnerabilities.status.ignored'))
                ])
            ])
        ])
    })
];

const table = useVueTable({
    get data() { return props.vulnerabilities },
    columns,
    state: { get sorting() { return sorting.value } },
    onSortingChange: updaterOrValue => {
        sorting.value = typeof updaterOrValue === 'function' ? updaterOrValue(sorting.value) : updaterOrValue;
    },
    getCoreRowModel: getCoreRowModel(),
    getSortedRowModel: getSortedRowModel(),
    getRowId: row => String(row.id),
});

// TanStack Table for All Packages
const packageColumnHelper = createColumnHelper<Package>();

const packageColumns = [
    packageColumnHelper.accessor('name', {
        header: t('system.security.packages.table.package'),
        cell: ({ row }) => h('span', { class: 'font-medium' }, row.original.name)
    }),
    packageColumnHelper.accessor('version', {
        header: t('system.security.packages.table.version'),
        cell: ({ row }) => h('span', { class: 'font-mono text-sm' }, row.original.version)
    }),
    packageColumnHelper.accessor('source', {
        header: t('system.security.packages.table.source'),
        cell: ({ row }) => h(Badge, { variant: row.original.source === 'composer' ? 'secondary' : 'outline' }, () => row.original.source)
    }),
    packageColumnHelper.accessor('status', {
        header: t('system.security.packages.table.status'),
        cell: ({ row }) => h(Badge, {
            variant: row.original.status === 'secure' ? 'default' : 'destructive',
            class: row.original.status === 'secure' ? 'bg-green-600 hover:bg-green-700' : ''
        }, () => row.original.status === 'secure' ? '✓ Secure' : '⚠ Vulnerable')
    }),
];

const packagesTable = useVueTable({
    get data() { return packages.value },
    columns: packageColumns,
    state: { get sorting() { return sorting.value } },
    onSortingChange: updaterOrValue => {
        sorting.value = typeof updaterOrValue === 'function' ? updaterOrValue(sorting.value) : updaterOrValue;
    },
    getCoreRowModel: getCoreRowModel(),
    getSortedRowModel: getSortedRowModel(),
    getRowId: row => row.name,
});
</script>
