<template>
  <div class="space-y-4">
    <SettingGroup
      v-for="group in emailSettingsGrouped"
      :key="group.id"
      :title="group.title"
      :description="group.description"
      :icon="(group.icon as any)"
      :color="group.color"
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

      <!-- SMTP Group Actions -->
      <template
        v-if="group.id === 'smtp'"
        #footer
      >
        <div class="flex flex-col gap-4">
          <div class="flex flex-wrap gap-4">
            <!-- Config Validation -->
            <div class="flex items-center gap-3">
              <Button
                type="button"
                variant="outline"
                size="sm"
                :disabled="validatingConfig"
                @click="$emit('validate-config')"
              >
                {{ validatingConfig ? $t('system.settings.emailTest.validating') : $t('system.settings.emailTest.validate') }}
              </Button>
              <div
                v-if="configValidation"
                class="text-sm"
              >
                <span
                  v-if="configValidation.valid"
                  class="text-success"
                >✓ {{ $t('system.settings.emailTest.valid') }}</span>
                <span
                  v-else
                  class="text-destructive"
                >✗ {{ $t('system.settings.emailTest.invalid') }}</span>
              </div>
            </div>
                        
            <!-- SMTP Connection Test -->
            <div class="flex items-center gap-3">
              <Button
                type="button"
                variant="outline"
                size="sm"
                :disabled="testingConnection"
                @click="$emit('test-connection')"
              >
                {{ testingConnection ? $t('system.settings.emailTest.testing') : $t('system.settings.emailTest.testConnection') }}
              </Button>
              <div
                v-if="connectionResult"
                class="text-sm"
              >
                <span
                  v-if="connectionResult.connected"
                  class="text-success"
                >✓ {{ $t('system.settings.emailTest.connected', { host: connectionResult.host, port: connectionResult.port }) }}</span>
                <span
                  v-else
                  class="text-destructive"
                >✗ {{ $t('system.settings.emailTest.failed') }}</span>
              </div>
            </div>
          </div>

          <!-- Validation Errors/Warnings -->
          <div
            v-if="configValidation && (!configValidation.valid || (configValidation.warnings && configValidation.warnings.length > 0))"
            class="p-4 bg-muted rounded-lg"
          >
            <div
              v-if="configValidation.errors && configValidation.errors.length > 0"
              class="mb-2"
            >
              <p class="text-xs font-medium text-destructive mb-1">
                {{ $t('system.settings.emailTest.errors') }}
              </p>
              <ul class="text-xs text-destructive list-disc list-inside">
                <li
                  v-for="error in configValidation.errors"
                  :key="error"
                >
                  {{ error }}
                </li>
              </ul>
            </div>
            <div v-if="configValidation.warnings && configValidation.warnings.length > 0">
              <p class="text-xs font-medium text-warning mb-1">
                {{ $t('system.settings.emailTest.warnings') }}
              </p>
              <ul class="text-xs text-warning list-disc list-inside">
                <li
                  v-for="warning in configValidation.warnings"
                  :key="warning"
                >
                  {{ warning }}
                </li>
              </ul>
            </div>
          </div>
        </div>
      </template>
    </SettingGroup>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { Mail as MailIcon } from 'lucide-vue-next';
import SettingGroup from '@/modules/Core/System/components/settings/SettingGroup.vue'
import SettingField from '@/modules/Core/System/components/settings/SettingField.vue'
import { Button } from '@/shared/components/ui';

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
}

interface ConfigValidation {
    valid: boolean;
    errors?: string[];
    warnings?: string[];
}

interface ConnectionResult {
    connected: boolean;
    host?: string;
    port?: string;
}

interface Props {
    settings: Setting[];
    formData: Record<string, unknown>;
    validatingConfig?: boolean;
    configValidation?: ConfigValidation | null;
    testingConnection?: boolean;
    connectionResult?: ConnectionResult | null;
    errors?: Record<string, string[]>;
}

const props = defineProps<Props>()

const emit = defineEmits<{
    (e: 'update:formData', value: Record<string, unknown>): void;
    (e: 'validate-config'): void;
    (e: 'test-connection'): void;
}>()

const updateField = (key: string, value: unknown) => {
    emit('update:formData', { ...props.formData, [key]: value })
}

const { t } = useI18n()

const emailSettingsGrouped = computed(() => {
    const emailSettings = props.settings.filter(s => s && s.group === 'email')
    
    const groups: SettingGroupData[] = [
        {
            id: 'smtp',
            title: t('system.settings.groups.smtp.title'),
            description: t('system.settings.groups.smtp.description'),
            icon: MailIcon,
            color: 'indigo',
            keys: [
                'mail_from_address', 
                'mail_from_name',
                'mail_driver', 
                'mail_host', 
                'mail_port', 
                'mail_username', 
                'mail_password', 
                'mail_encryption'
            ],
            settings: [],
        },
    ]
    
    groups.forEach(group => {
        group.settings = emailSettings.filter(s => group.keys.includes(s.key))
        
        // Sort settings based on the order in keys
        group.settings.sort((a, b) => {
            return group.keys.indexOf(a.key) - group.keys.indexOf(b.key)
        })
    })
    
    return groups.filter(group => group.settings.length > 0)
})
</script>
