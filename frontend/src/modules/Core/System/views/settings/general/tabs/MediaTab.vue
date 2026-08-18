<script setup lang="ts">
import { computed, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import api from '@/engine/api/client'
import axios from 'axios'
import SettingGroup from '@/modules/Core/System/components/settings/SettingGroup.vue'
import SettingField from '@/modules/Core/System/components/settings/SettingField.vue'
import { Button } from '@/shared/components/ui';
import { useConfirm } from '@/shared/composables/useConfirm'
import { systemPaths } from '@/engine/api/paths';
import type { SettingValue } from '@/engine/types/settings'

interface Setting {
    id: string | string;
    key: string;
    value: unknown;
    type: string;
    group: string;
}

interface SettingGroupData {
    id: string;
    title: string;
    description: string;
    icon: unknown;
    color: 'primary' | 'blue' | 'emerald' | 'amber' | 'red' | 'purple' | 'indigo' | 'orange' | 'pink';
    keys: string[];
    settings: Setting[];
    defaultExpanded: boolean;
    isExternal?: boolean;
}

interface TestResult {
    success: boolean;
    message: string;
}

interface MigrationLog {
    type: 'success' | 'error' | 'warning' | 'info';
    message: string;
}

interface Props {
    settings: Setting[];
    formData: Record<string, SettingValue>;
    errors?: Record<string, string[]>;
}

const { t } = useI18n()
const { confirm } = useConfirm()

const props = defineProps<Props>()

const emit = defineEmits<{
    (e: 'update:formData', value: Record<string, SettingValue>): void;
}>()

const updateField = (key: string, value: SettingValue) => {
    emit('update:formData', { ...props.formData, [key]: value })
}

const UploadIcon = {
    template: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" /></svg>`
}

const ImageIcon = {
    template: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" /></svg>`
}

const CloudIcon = {
    template: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0l3 3m-3-3l-3 3M6.75 19.5a4.5 4.5 0 01-1.41-8.775 5.25 5.25 0 0110.233-2.33 3 3 0 013.758 3.848A3.752 3.752 0 0118 19.5H6.75z" /></svg>`
}

const ServerIcon = {
    template: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 14.25h13.5m-13.5 0a3 3 0 01-3-3m3 3a3 3 0 100 6h13.5a3 3 0 100-6m-16.5-3a3 3 0 013-3h13.5a3 3 0 013 3m-19.5 0a4.5 4.5 0 01.9-2.7L5.737 5.1a3.375 3.375 0 012.7-1.35h7.126c1.062 0 2.062.5 2.7 1.35l2.587 3.45a4.5 4.5 0 01.9 2.7m0 0a3 3 0 01-3 3m0 3h.008v.008h-.008v-.008zm0-6h.008v.008h-.008v-.008zm-3 6h.008v.008h-.008v-.008zm0-6h.008v.008h-.008v-.008z" /></svg>`
}

const mediaSettingsGrouped = computed(() => {
    const mediaSettings = props.settings.filter(s => s && s.group === 'media')
    
    // Check drivers
    const driver = props.formData.storage_driver;

    // Base groups
    const groups: SettingGroupData[] = [
        {
            id: 'upload',
            title: t('system.settings.groups.upload.title'),
            description: t('system.settings.groups.upload.description'),
            icon: UploadIcon,
            color: 'blue',
            keys: ['max_upload_size', 'allowed_image_types', 'allowed_file_types', 'storage_driver'],
            settings: [],
            defaultExpanded: true,
        },
    ]

    // S3 Config
    if (driver === 's3') {
        groups.push({
            id: 's3_config',
            title: t('system.settings.groups.s3.title'),
            description: t('system.settings.groups.s3.description'),
            icon: CloudIcon,
            color: 'orange',
            keys: ['aws_access_key_id', 'aws_secret_access_key', 'aws_default_region', 'aws_bucket', 'aws_endpoint'],
            settings: [],
            isExternal: true,
            defaultExpanded: true,
        });
    }

    // Google Drive Config
    if (driver === 'google') {
        groups.push({
            id: 'google_config',
            title: t('system.settings.groups.google.title'),
            description: t('system.settings.groups.google.description'),
            icon: CloudIcon,
            color: 'red',
            keys: ['google_client_id', 'google_client_secret', 'google_refresh_token', 'google_folder_id'],
            settings: [],
            isExternal: true,
            defaultExpanded: true,
        });
    }

    // FTP Config
    if (driver === 'ftp') {
        groups.push({
            id: 'ftp_config',
            title: t('system.settings.groups.ftp.title'),
            description: t('system.settings.groups.ftp.description'),
            icon: ServerIcon,
            color: 'purple',
            keys: ['ftp_host', 'ftp_username', 'ftp_password', 'ftp_root', 'ftp_port', 'ftp_ssl'],
            settings: [],
            isExternal: true,
            defaultExpanded: true,
        });
    }

    // Dropbox Config
    if (driver === 'dropbox') {
        groups.push({
            id: 'dropbox_config',
            title: t('system.settings.groups.dropbox.title'),
            description: t('system.settings.groups.dropbox.description'),
            icon: CloudIcon,
            color: 'indigo',
            keys: ['dropbox_authorization_token'],
            settings: [],
            isExternal: true,
            defaultExpanded: true,
        });
    }

    // Add Image Processing group (last)
    groups.push({
        id: 'image_processing',
        title: t('system.settings.groups.imageProcessing.title'),
        description: t('system.settings.groups.imageProcessing.description'),
        icon: ImageIcon,
        color: 'pink',
        keys: ['thumbnail_width', 'thumbnail_height', 'enable_watermark', 'watermark_text'],
        settings: [],
        defaultExpanded: false,
    })
    
    
    groups.forEach(group => {
        group.settings = mediaSettings.filter(s => group.keys.includes(s.key))
    })
    
    return groups.filter(group => group.settings.length > 0)
})

const testingConnection = ref(false);
const testResult = ref<TestResult | null>(null);

const testConnection = async () => {
    testingConnection.value = true;
    testResult.value = null;
    try {
        // Send current formData to test endpoint
        const response = await api.post(systemPaths.testStorage, {
            driver: props.formData.storage_driver,
            config: props.formData
        });
        testResult.value = { success: true, message: response.data.message };
    } catch (error: unknown) {
        let msg = t('system.settings.groups.test.failed');
        if (axios.isAxiosError(error)) {
            msg = error.response?.data?.message || msg;
        }
        testResult.value = { 
            success: false, 
            message: msg
        };
    } finally {
        testingConnection.value = false;
    }
};

// --- Storage Migration Logic ---
const migrationStatus = ref('idle'); // idle, scanning, migrating, completed, error
const totalFiles = ref(0);
const processedFiles = ref(0);
const migrationLogs = ref<MigrationLog[]>([]);
const stopMigration = ref(false);

const migrationProgress = computed(() => {
    if (totalFiles.value === 0) return 0;
    return Math.round((processedFiles.value / totalFiles.value) * 100);
});

const startMigration = async () => {
    const confirmed = await confirm({
        title: t('system.settings.groups.migration.confirm_title'),
        message: t('system.settings.groups.migration.confirm_message'),
        variant: 'warning',
        confirmText: t('system.settings.groups.migration.start'),
    });

    if (!confirmed) return;

    migrationStatus.value = 'scanning';
    migrationLogs.value = [];
    processedFiles.value = 0;
    totalFiles.value = 0;
    stopMigration.value = false;

    try {
        // 1. Scan files
        const response = await api.get('/manage/storage/migration/files');
        const files = response.data; // Array of paths
        totalFiles.value = files.length;

        if (totalFiles.value === 0) {
            migrationStatus.value = 'completed';
            migrationLogs.value.push({ type: 'info', message: t('system.settings.groups.migration.no_files') });
            return;
        }

        migrationStatus.value = 'migrating';
        
        // 2. Process in batches
        const batchSize = 10;
        for (let i = 0; i < files.length; i += batchSize) {
            if (stopMigration.value) {
                migrationLogs.value.push({ type: 'warning', message: t('system.settings.groups.migration.stopped') });
                break;
            }

            const batch = files.slice(i, i + batchSize);
            try {
                const res = await api.post('/manage/storage/migration/batch', { files: batch });
                const result = res.data;
                
                // Log failures
                Object.entries(result.failed as Record<string, string>).forEach(([file, error]) => {
                     migrationLogs.value.push({ type: 'error', message: `Failed ${file}: ${error}` });
                });
                
                processedFiles.value += batch.length;
            } catch (err: unknown) {
                 let msg = 'Batch failed';
                 if (axios.isAxiosError(err)) {
                     msg = err.message;
                 } else if (err instanceof Error) {
                     msg = err.message;
                 }
                 migrationLogs.value.push({ type: 'error', message: `Batch failed: ${msg}` });
            }
        }
        
        if (!stopMigration.value) {
            migrationStatus.value = 'completed';
            migrationLogs.value.push({ type: 'success', message: t('system.settings.groups.migration.success') });
        }

    } catch (error: unknown) {
        migrationStatus.value = 'error';
        let msg = 'Migration failed';
        if (axios.isAxiosError(error)) {
            msg = error.response?.data?.message || error.message;
        } else if (error instanceof Error) {
            msg = error.message;
        }
        migrationLogs.value.push({ type: 'error', message: msg });
    }
};

const handleStopMigration = () => {
    stopMigration.value = true;
};
</script>

<template>
  <div class="space-y-4">
    <SettingGroup
      v-for="group in mediaSettingsGrouped"
      :key="group.id"
      :title="group.title"
      :description="group.description"
      :icon="(group.icon as any)"
      :color="group.color as any"
      :default-expanded="group.defaultExpanded"
    >
      <SettingField
        v-for="setting in group.settings"
        :key="setting.id"
        :model-value="(formData[setting.key] as any)"
        :field-key="setting.key"
        :label="$t('system.settings.labels.' + setting.key)"
        :description="$t('system.settings.descriptions.' + setting.key)"
        :type="setting.type"
        :enabled-text="$t('system.settings.enabled')"
        :disabled-text="$t('system.settings.disabled')"
        :error="errors?.[setting.key]"
        @update:model-value="(value) => updateField(setting.key, value)"
      />


      <!-- Test Connection Button for External Drivers -->
      <div
        v-if="group.isExternal"
        class="col-span-1 md:col-span-2 mt-4"
      >
        <div class="flex items-center gap-4">
          <Button 
            :disabled="testingConnection" 
            variant="secondary"
            @click="testConnection"
          >
            <span v-if="testingConnection">{{ $t('system.settings.groups.test.testing') }}</span>
            <span v-else>{{ $t('system.settings.groups.test.button') }}</span>
          </Button>
                    
          <div
            v-if="testResult"
            :class="['text-sm', testResult.success ? 'text-success' : 'text-destructive']"
          >
            {{ testResult.message }}
          </div>
        </div>
      </div>
    </SettingGroup>

    <!-- Migration Tool (Show only if driver is NOT local) -->
    <div
      v-if="formData.storage_driver && formData.storage_driver !== 'local'"
      class="p-6 bg-card border border-border rounded-lg"
    >
      <h3 class="text-lg font-medium text-foreground mb-2">
        {{ $t('system.settings.groups.migration.title') }}
      </h3>
      <p class="text-sm text-muted-foreground mb-4">
        {{ $t('system.settings.groups.migration.description', { driver: formData.storage_driver }) }}
      </p>

      <div
        v-if="migrationStatus === 'idle' || migrationStatus === 'completed' || migrationStatus === 'error'"
        class="flex gap-4"
      >
        <Button @click="startMigration">
          {{ $t('system.settings.groups.migration.start') }}
        </Button>
      </div>

      <div
        v-if="migrationStatus === 'scanning' || migrationStatus === 'migrating'"
        class="space-y-4"
      >
        <div class="flex justify-between text-sm text-foreground">
          <span>
            <span v-if="migrationStatus === 'scanning'">{{ $t('system.settings.groups.migration.scanning') }}</span>
            <span v-else>{{ $t('system.settings.groups.migration.migrating', { processed: processedFiles, total: totalFiles }) }}</span>
          </span>
          <span>{{ migrationProgress }}%</span>
        </div>
                
        <!-- Progress Bar -->
        <div class="w-full bg-secondary rounded-full h-2.5 dark:bg-gray-700">
          <div
            class="bg-primary h-2.5 rounded-full transition-colors duration-300"
            :style="{ width: migrationProgress + '%' }"
          />
        </div>

        <Button
          variant="destructive"
          size="sm"
          @click="handleStopMigration"
        >
          {{ $t('system.settings.groups.migration.stop') }}
        </Button>
      </div>

      <!-- Logs -->
      <div
        v-if="migrationLogs.length > 0"
        class="mt-4 p-3 bg-muted rounded-md text-xs max-h-40 overflow-y-auto font-mono"
      >
        <div
          v-for="(log, index) in migrationLogs"
          :key="index"
          :class="{
            'text-success': log.type === 'success',
            'text-destructive': log.type === 'error',
            'text-warning': log.type === 'warning',
            'text-foreground': log.type === 'info'
          }"
        >
          [{{ log.type.toUpperCase() }}] {{ log.message }}
        </div>
      </div>
    </div>
  </div>
</template>
