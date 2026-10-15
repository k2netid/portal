<template>
  <div class="space-y-8">
    <PageHeader
      :title="t('system.integrations.hub.title')"
      :subtitle="t('system.integrations.hub.subtitle')"
      borderless
    />

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <RouterLink
        v-for="card in hubCards"
        :key="card.title"
        :to="card.to"
        class="group block rounded-xl border border-border bg-card p-5 hover:border-primary/50 hover:shadow-md transition-all"
      >
        <div class="flex items-start justify-between gap-3">
          <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary/10 text-primary">
              <component
                :is="card.icon"
                class="h-5 w-5"
              />
            </div>
            <div>
              <h3 class="font-semibold text-foreground group-hover:text-primary">
                {{ card.title }}
              </h3>
              <p class="text-sm text-muted-foreground mt-1">
                {{ card.description }}
              </p>
            </div>
          </div>
          <span class="text-xs font-medium text-primary shrink-0">{{ t('system.integrations.hub.open') }} →</span>
        </div>
      </RouterLink>
    </div>

    <ConsoleListCard>
      <div class="p-6 space-y-8">
        <section>
          <div class="flex items-center gap-2 mb-2">
            <LogIn class="w-5 h-5 text-primary" />
            <h3 class="text-lg font-medium">
              {{ t('system.integrations.memberGoogle.title') }}
            </h3>
          </div>
          <p class="text-sm text-muted-foreground mb-4">
            {{ t('system.integrations.memberGoogle.description') }}
          </p>

          <div
            v-if="googleLoading"
            class="text-sm text-muted-foreground"
          >
            {{ t('system.integrations.memberGoogle.loading') }}
          </div>

          <form
            v-else
            class="space-y-4 max-w-xl"
            @submit.prevent="saveMemberGoogle"
          >
            <div class="flex items-center justify-between gap-4 rounded-lg border border-border p-3">
              <div>
                <Label for="member-google-enabled">{{ t('system.integrations.memberGoogle.enabled') }}</Label>
                <p class="text-xs text-muted-foreground mt-0.5">
                  {{ t('system.integrations.memberGoogle.enabledHint') }}
                </p>
              </div>
              <Switch
                id="member-google-enabled"
                :checked="googleForm.enabled"
                @update:checked="googleForm.enabled = $event === true"
              />
            </div>

            <div class="space-y-2">
              <Label for="member-google-client-id">{{ t('system.integrations.memberGoogle.clientId') }}</Label>
              <Input
                id="member-google-client-id"
                v-model="googleForm.clientId"
                autocomplete="off"
                spellcheck="false"
                :placeholder="t('system.integrations.memberGoogle.clientIdPlaceholder')"
              />
            </div>

            <div class="space-y-2">
              <Label for="member-google-client-secret">{{ t('system.integrations.memberGoogle.clientSecret') }}</Label>
              <Input
                id="member-google-client-secret"
                v-model="googleForm.clientSecret"
                type="password"
                autocomplete="new-password"
                :placeholder="googleSecretConfigured
                  ? t('system.integrations.memberGoogle.clientSecretKeep')
                  : t('system.integrations.memberGoogle.clientSecretPlaceholder')"
              />
              <p
                v-if="googleSecretConfigured"
                class="text-xs text-muted-foreground"
              >
                {{ t('system.integrations.memberGoogle.clientSecretConfigured') }}
              </p>
            </div>

            <div class="space-y-2">
              <Label>{{ t('system.integrations.memberGoogle.redirectUri') }}</Label>
              <div class="flex gap-2">
                <Input
                  :model-value="googleRedirectUri"
                  readonly
                  class="font-mono text-xs"
                />
                <Button
                  type="button"
                  variant="outline"
                  @click="copyRedirectUri"
                >
                  {{ t('system.integrations.memberGoogle.copy') }}
                </Button>
              </div>
              <p class="text-xs text-muted-foreground">
                {{ t('system.integrations.memberGoogle.redirectHint') }}
              </p>
            </div>

            <div class="flex items-center gap-3 pt-1">
              <Button
                type="submit"
                :disabled="googleSaving"
              >
                {{ googleSaving
                  ? t('system.integrations.memberGoogle.saving')
                  : t('system.integrations.memberGoogle.save') }}
              </Button>
              <p
                v-if="googleStatus"
                class="text-xs"
                :class="googleStatusOk ? 'text-emerald-600' : 'text-destructive'"
              >
                {{ googleStatus }}
              </p>
            </div>
          </form>
        </section>

        <Separator />

        <section>
          <div class="flex items-center gap-2 mb-4">
            <Users class="w-5 h-5 text-primary" />
            <h3 class="text-lg font-medium">
              {{ t('system.integrations.scim.title') }}
            </h3>
          </div>
          <p class="text-sm text-muted-foreground mb-4">
            {{ t('system.integrations.scim.description') }}
          </p>
          <div class="bg-muted p-4 rounded-lg space-y-4 font-mono text-sm">
            <div class="flex justify-between items-center gap-4">
              <span class="text-muted-foreground shrink-0">{{ t('system.integrations.scim.baseUrl') }}:</span>
              <span class="flex-1 select-all break-all">{{ scimBaseUrl }}</span>
            </div>
            <div class="flex justify-between items-center gap-4">
              <span class="text-muted-foreground shrink-0">{{ t('system.integrations.scim.bearer') }}:</span>
              <span class="flex-1 text-yellow-600 dark:text-yellow-400">{{ t('system.integrations.scim.bearerHint') }}</span>
            </div>
          </div>
        </section>

        <Separator />

        <section>
          <div class="flex items-center gap-2 mb-4">
            <Server class="w-5 h-5 text-primary" />
            <h3 class="text-lg font-medium">
              {{ t('system.integrations.config.title') }}
            </h3>
          </div>
          <p class="text-sm text-muted-foreground mb-4">
            {{ t('system.integrations.config.description') }}
          </p>
          <div class="bg-muted p-4 rounded-lg space-y-4 font-mono text-sm">
            <div class="flex justify-between items-center gap-4">
              <span class="text-muted-foreground shrink-0">{{ t('system.integrations.config.resolve') }}:</span>
              <span class="flex-1 select-all break-all">GET {{ configResolveUrl }}</span>
            </div>
            <div class="flex justify-between items-center gap-4">
              <span class="text-muted-foreground shrink-0">{{ t('system.integrations.config.sync') }}:</span>
              <span class="flex-1 select-all break-all">POST {{ configSyncUrl }}</span>
            </div>
          </div>
        </section>
      </div>
    </ConsoleListCard>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted, reactive, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { RouterLink } from 'vue-router';
import { Shield, Webhook, Users, Server, Scale, Radio, LogIn } from 'lucide-vue-next';
import { PageHeader, ConsoleListCard } from '@/shared/components/shell';
import { Separator, Button, Input, Label, Switch } from '@/shared/components/ui';
import api from '@/engine/api/client';
import { consoleNamedRoute } from '@/shared/utils/consoleRoute';
import { useToast } from '@/shared/composables/useToast';

const { t } = useI18n();
const toast = useToast();

interface SettingRow {
    key: string;
    value: unknown;
    type: string;
    group: string;
}

const hubCards = computed(() => [
    {
        to: consoleNamedRoute('oauth-clients'),
        title: t('system.integrations.hub.oauthCard'),
        description: t('system.integrations.hub.oauthDesc'),
        icon: Shield,
    },
    {
        to: consoleNamedRoute('webhooks'),
        title: t('system.integrations.hub.webhooksCard'),
        description: t('system.integrations.hub.webhooksDesc'),
        icon: Webhook,
    },
    {
        to: consoleNamedRoute('settings', {}, { tab: 'abac-policies' }),
        title: t('system.integrations.hub.abacCard'),
        description: t('system.integrations.hub.abacDesc'),
        icon: Scale,
    },
    {
        to: consoleNamedRoute('settings', {}, { tab: 'siem-exports' }),
        title: t('system.integrations.hub.siemCard'),
        description: t('system.integrations.hub.siemDesc'),
        icon: Radio,
    },
]);

const scimBaseUrl = computed(() => `${window.location.origin}/api/scim/v2/`);
const configResolveUrl = computed(() => `${window.location.origin}/api/v1/config/resolve`);
const configSyncUrl = computed(() => `${window.location.origin}/api/v1/config/sync`);
const googleRedirectUri = computed(
    () => `${window.location.origin}/api/v1/public/member/auth/google/callback`,
);

const googleLoading = ref(true);
const googleSaving = ref(false);
const googleSecretConfigured = ref(false);
const googleStatus = ref('');
const googleStatusOk = ref(false);
const googleForm = reactive({
    enabled: true,
    clientId: '',
    clientSecret: '',
});

const settingValue = (rows: SettingRow[], key: string): unknown =>
    rows.find((row) => row.key === key)?.value;

const loadMemberGoogle = async (): Promise<void> => {
    googleLoading.value = true;
    googleStatus.value = '';
    try {
        const response = await api.get('/manage/system/settings');
        const rows = (response.data?.data ?? response.data ?? []) as SettingRow[];
        const list = Array.isArray(rows) ? rows : [];

        const enabled = settingValue(list, 'enable_member_google_login');
        googleForm.enabled = enabled !== false && enabled !== 0 && enabled !== '0';

        const clientId = settingValue(list, 'member_google_client_id');
        googleForm.clientId = typeof clientId === 'string' ? clientId : '';

        const secret = settingValue(list, 'member_google_client_secret');
        googleSecretConfigured.value = typeof secret === 'string' && secret.trim() !== '';
        googleForm.clientSecret = '';
    } catch (error) {
        toast.error.fromResponse(error);
    } finally {
        googleLoading.value = false;
    }
};

const saveMemberGoogle = async (): Promise<void> => {
    googleSaving.value = true;
    googleStatus.value = '';
    try {
        const settings: Array<{ key: string; value: unknown; type: string; group: string }> = [
            {
                key: 'enable_member_google_login',
                value: googleForm.enabled,
                type: 'boolean',
                group: 'integrations',
            },
            {
                key: 'member_google_client_id',
                value: googleForm.clientId.trim(),
                type: 'string',
                group: 'integrations',
            },
        ];

        if (googleForm.clientSecret.trim() !== '') {
            settings.push({
                key: 'member_google_client_secret',
                value: googleForm.clientSecret.trim(),
                type: 'password',
                group: 'integrations',
            });
        } else if (!googleSecretConfigured.value) {
            settings.push({
                key: 'member_google_client_secret',
                value: '',
                type: 'password',
                group: 'integrations',
            });
        }

        await api.post('/manage/system/settings/bulk-update', { settings });
        googleForm.clientSecret = '';
        await loadMemberGoogle();
        googleStatusOk.value = true;
        googleStatus.value = t('system.integrations.memberGoogle.saved');
        toast.success.save();
    } catch (error) {
        googleStatusOk.value = false;
        googleStatus.value = t('system.integrations.memberGoogle.saveFailed');
        toast.error.fromResponse(error);
    } finally {
        googleSaving.value = false;
    }
};

const copyRedirectUri = async (): Promise<void> => {
    try {
        await navigator.clipboard.writeText(googleRedirectUri.value);
        toast.success.action(t('system.integrations.memberGoogle.copied'));
    } catch {
        googleStatusOk.value = false;
        googleStatus.value = t('system.integrations.memberGoogle.copyFailed');
    }
};

onMounted(() => {
    void loadMemberGoogle();
});
</script>
