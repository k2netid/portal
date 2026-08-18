<template>
  <div class="space-y-6">
    <div
      v-if="loading"
      class="flex items-center justify-center p-12"
    >
      <Loader2 class="w-8 h-8 text-primary" />
    </div>

    <template v-else-if="integrity">
      <!-- Integrity Overview -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <Card :class="{ 'border-destructive/50 bg-destructive/5': integrity.violations_count > 0 }">
          <CardHeader class="flex flex-row items-center justify-between pb-2">
            <CardTitle class="text-sm font-medium">
              {{ $t('system.security.fileIntegrity.stats.systemStatus') }}
            </CardTitle>
            <ShieldAlert
              v-if="integrity.violations_count > 0"
              class="w-4 h-4 text-destructive"
            />
            <ShieldCheck
              v-else
              class="w-4 h-4 text-primary"
            />
          </CardHeader>
          <CardContent>
            <div
              class="text-2xl font-bold"
              :class="integrity.violations_count > 0 ? 'text-destructive' : 'text-primary'"
            >
              {{ integrity.violations_count > 0 ? $t('system.security.fileIntegrity.stats.tampered') : $t('system.security.fileIntegrity.stats.secure') }}
            </div>
            <p class="text-xs text-muted-foreground mt-1">
              {{ $t('system.security.fileIntegrity.stats.violationsDetected', { count: integrity.violations_count }) }}
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between pb-2">
            <CardTitle class="text-sm font-medium">
              {{ $t('system.security.fileIntegrity.stats.monitoredFiles') }}
            </CardTitle>
            <Files class="w-4 h-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">
              {{ integrity.total_files }}
            </div>
            <p class="text-xs text-muted-foreground mt-1">
              {{ $t('system.security.fileIntegrity.stats.criticalFiles') }}
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between pb-2">
            <CardTitle class="text-sm font-medium">
              {{ $t('system.security.fileIntegrity.stats.lastAudit') }}
            </CardTitle>
            <Clock class="w-4 h-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">
              {{ integrity.last_check ? formatTime(integrity.last_check) : $t('system.security.fileIntegrity.stats.never') }}
            </div>
            <p class="text-xs text-muted-foreground mt-1">
              {{ integrity.last_check ? formatDate(integrity.last_check) : $t('system.security.fileIntegrity.stats.noHistory') }}
            </p>
          </CardContent>
        </Card>
      </div>

      <!-- Action Bar -->
      <div class="flex items-center justify-between">
        <h3 class="text-lg font-semibold">
          {{ $t('system.security.fileIntegrity.violations.title') }}
        </h3>
        <div class="flex items-center gap-2">
          <Button
            variant="outline"
            size="sm"
            :disabled="loading || resyncSubmitting || resyncCooldownSeconds > 0"
            @click="$emit('resync')"
          >
            <RefreshCw class="w-4 h-4 mr-2" />
            {{ resyncCooldownSeconds > 0 ? `${$t('system.security.fileIntegrity.actions.sync')} (${resyncCooldownSeconds}s)` : $t('system.security.fileIntegrity.actions.sync') }}
          </Button>
          <Button
            variant="default"
            size="sm"
            :disabled="loading || resyncSubmitting || resyncCooldownSeconds > 0"
            @click="$emit('run-check')"
          >
            <ScanSearch class="w-4 h-4 mr-2" />
            {{ $t('system.security.fileIntegrity.actions.runAudit') }}
          </Button>
        </div>
      </div>

      <!-- Violations Table -->
      <Card>
        <CardContent class="p-0">
          <div
            v-if="integrity.violations_count === 0"
            class="flex flex-col items-center justify-center py-12 text-muted-foreground"
          >
            <ShieldCheck class="w-16 h-16 mb-4 text-primary opacity-20" />
            <h3 class="text-xl font-medium text-foreground">
              {{ $t('system.security.fileIntegrity.violations.empty') }}
            </h3>
            <p class="max-w-xs text-center mt-2">
              {{ $t('system.security.fileIntegrity.violations.emptyDesc') }}
            </p>
          </div>
          <div
            v-else
            class="max-h-[500px] overflow-y-auto"
          >
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead class="w-[100px]">
                    {{ $t('system.security.fileIntegrity.violations.table.type') }}
                  </TableHead>
                  <TableHead>{{ $t('system.security.fileIntegrity.violations.table.path') }}</TableHead>
                  <TableHead>{{ $t('system.security.fileIntegrity.violations.table.expected') }}</TableHead>
                  <TableHead>{{ $t('system.security.fileIntegrity.violations.table.detected') }}</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <!-- Modified Files -->
                <TableRow
                  v-for="file in integrity.results.modified"
                  :key="file.path"
                  class="bg-destructive/5"
                >
                  <TableCell>
                    <Badge variant="destructive">
                      {{ $t('system.security.fileIntegrity.violations.types.modified') }}
                    </Badge>
                  </TableCell>
                  <TableCell class="font-mono text-xs">
                    /{{ file.path }}
                  </TableCell>
                  <TableCell class="font-mono text-[10px] opacity-50">
                    {{ file.expected.substring(0, 16) }}...
                  </TableCell>
                  <TableCell class="font-mono text-[10px] text-destructive">
                    {{ file.actual.substring(0, 16) }}...
                  </TableCell>
                </TableRow>

                <!-- Missing Files -->
                <TableRow
                  v-for="file in integrity.results.missing"
                  :key="file.path"
                  class="bg-warning/5"
                >
                  <TableCell>
                    <Badge variant="warning">
                      {{ $t('system.security.fileIntegrity.violations.types.missing') }}
                    </Badge>
                  </TableCell>
                  <TableCell class="font-mono text-xs">
                    /{{ file.path }}
                  </TableCell>
                  <TableCell class="font-mono text-[10px] opacity-50">
                    {{ file.expected.substring(0, 16) }}...
                  </TableCell>
                  <TableCell class="text-muted-foreground italic text-xs">
                    {{ $t('system.security.fileIntegrity.violations.table.fileNotFound') }}
                  </TableCell>
                </TableRow>

                <!-- New Unknown Files -->
                <TableRow
                  v-for="file in integrity.results.new_files || []"
                  :key="file.path"
                  class="bg-info/5"
                >
                  <TableCell>
                    <Badge variant="info">
                      {{ $t('system.security.fileIntegrity.violations.types.new') }}
                    </Badge>
                  </TableCell>
                  <TableCell class="font-mono text-xs">
                    /{{ file.path }}
                  </TableCell>
                  <TableCell class="text-muted-foreground italic text-xs">
                    -
                  </TableCell>
                  <TableCell class="font-mono text-[10px]">
                    {{ file.actual.substring(0, 16) }}...
                  </TableCell>
                </TableRow>
              </TableBody>
            </Table>
          </div>
        </CardContent>
      </Card>

      <Alert
        v-if="integrity.violations_count > 0"
        variant="destructive"
        class="mt-6"
      >
        <CircleAlert class="h-4 w-4" />
        <AlertTitle>{{ $t('system.security.fileIntegrity.warning.title') }}</AlertTitle>
        <AlertDescription>
          {{ $t('system.security.fileIntegrity.warning.description') }}
        </AlertDescription>
      </Alert>
    </template>

    <div
      v-else
      class="flex flex-col items-center justify-center p-12 text-muted-foreground"
    >
      <ShieldAlert class="w-12 h-12 mb-4 opacity-20" />
      <p>{{ $t('system.security.fileIntegrity.noData') }}</p>
      <Button
        variant="outline"
        class="mt-4"
        @click="$emit('run-check')"
      >
        {{ $t('system.security.fileIntegrity.actions.runInitial') }}
      </Button>
    </div>
  </div>
</template>

<script setup lang="ts">
import {
  CircleAlert,
  Clock,
  Files,
  Loader2,
  RefreshCw,
  ScanSearch,
  ShieldAlert,
  ShieldCheck,
} from 'lucide-vue-next';
import { 
    Card, CardHeader, CardTitle, CardContent,
    Badge, Button,
    Table, TableHeader, TableRow, TableHead, TableBody, TableCell,
    Alert, AlertTitle, AlertDescription
} from '@/shared/components/ui';

interface IntegrityFile {
    path: string;
    expected: string;
    actual: string;
}

interface FileIntegrityData {
    violations_count: number;
    total_files: number;
    last_check: string | null;
    results: {
        modified: IntegrityFile[];
        missing: IntegrityFile[];
        new_files?: IntegrityFile[];
    };
}

defineProps<{
    integrity: FileIntegrityData | null;
    loading: boolean;
    resyncSubmitting: boolean;
    resyncCooldownSeconds: number;
}>();

defineEmits(['refresh', 'run-check', 'resync']);

const formatTime = (date: string) => {
    return new Date(date).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString();
};
</script>
