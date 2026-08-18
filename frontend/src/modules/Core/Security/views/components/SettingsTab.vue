<template>
  <div class="space-y-6">
    <Card>
      <CardHeader>
        <CardTitle class="text-lg">
          {{ $t('system.security.settings.title') }}
        </CardTitle>
        <CardDescription>{{ $t('system.security.settings.description') }}</CardDescription>
      </CardHeader>
      <CardContent class="space-y-6">
        <!-- Security Log Retention -->
        <div class="space-y-2">
          <Label for="security-retention">{{ $t('system.security.settings.retentionDays.label') }}</Label>
          <p class="text-sm text-muted-foreground">
            {{ $t('system.security.settings.retentionDays.description') }}
          </p>
          <div class="flex items-center gap-3">
            <Input
              id="security-retention"
              v-model.number="form.security_log_retention_days"
              type="number"
              :min="7"
              :max="365"
              class="w-32"
            />
            <span class="text-sm text-muted-foreground">{{ $t('system.security.settings.retentionDays.unit') }}</span>
          </div>
        </div>

        <hr class="border-border">

        <!-- Activity Log Retention -->
        <div class="space-y-2">
          <Label for="activity-retention">{{ $t('system.security.settings.activityRetention.label') }}</Label>
          <p class="text-sm text-muted-foreground">
            {{ $t('system.security.settings.activityRetention.description') }}
          </p>
          <div class="flex items-center gap-3">
            <Input
              id="activity-retention"
              v-model.number="form.activity_log_retention_days"
              type="number"
              :min="7"
              :max="365"
              class="w-32"
            />
            <span class="text-sm text-muted-foreground">{{ $t('system.security.settings.retentionDays.unit') }}</span>
          </div>
        </div>

        <hr class="border-border">

        <!-- Login History Retention -->
        <div class="space-y-2">
          <Label for="login-retention">{{ $t('system.security.settings.loginRetention.label') }}</Label>
          <p class="text-sm text-muted-foreground">
            {{ $t('system.security.settings.loginRetention.description') }}
          </p>
          <div class="flex items-center gap-3">
            <Input
              id="login-retention"
              v-model.number="form.login_history_retention_days"
              type="number"
              :min="7"
              :max="365"
              class="w-32"
            />
            <span class="text-sm text-muted-foreground">{{ $t('system.security.settings.retentionDays.unit') }}</span>
          </div>
        </div>

        <hr class="border-border">

        <!-- Auto-Tune Frequency -->
        <div class="space-y-2">
          <Label for="autotune-freq">{{ $t('system.security.settings.autotuneFrequency.label') }}</Label>
          <p class="text-sm text-muted-foreground">
            {{ $t('system.security.settings.autotuneFrequency.description') }}
          </p>
          <select
            id="autotune-freq"
            v-model="form.security_autotune_frequency"
            class="flex h-10 w-48 rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2"
          >
            <option value="weekly">
              {{ $t('system.security.settings.autotuneFrequency.weekly') }}
            </option>
            <option value="daily">
              {{ $t('system.security.settings.autotuneFrequency.daily') }}
            </option>
          </select>
        </div>
      </CardContent>
      <CardFooter>
        <Button
          :disabled="saving"
          @click="$emit('save', { ...form })"
        >
          <Loader2
            v-if="saving"
            class="w-4 h-4 mr-2"
          />
          <Save
            v-else
            class="w-4 h-4 mr-2"
          />
          {{ $t('common.actions.save') }}
        </Button>
      </CardFooter>
    </Card>
  </div>
</template>

<script setup lang="ts">
import { reactive, watch } from 'vue';
import {
    Card, CardHeader, CardTitle, CardDescription, CardContent, CardFooter,
    Button, Input, Label
} from '@/shared/components/ui';
import {
  Loader2,
  Save,
} from 'lucide-vue-next';

interface SecuritySettings {
    security_log_retention_days: number;
    activity_log_retention_days: number;
    login_history_retention_days: number;
    security_autotune_frequency: string;
}

const props = defineProps<{
    settings: SecuritySettings;
    saving: boolean;
}>();

defineEmits<{
    (e: 'save', settings: SecuritySettings): void;
}>();

const form = reactive<SecuritySettings>({
    security_log_retention_days: props.settings.security_log_retention_days,
    activity_log_retention_days: props.settings.activity_log_retention_days,
    login_history_retention_days: props.settings.login_history_retention_days,
    security_autotune_frequency: props.settings.security_autotune_frequency,
});

// Sync form when parent data changes (e.g. after fetch)
watch(() => props.settings, (newSettings) => {
    form.security_log_retention_days = newSettings.security_log_retention_days;
    form.activity_log_retention_days = newSettings.activity_log_retention_days;
    form.login_history_retention_days = newSettings.login_history_retention_days;
    form.security_autotune_frequency = newSettings.security_autotune_frequency;
}, { deep: true });
</script>
