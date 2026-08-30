<template>
  <div class="space-y-6">
    <PageHeader
      borderless
      :title="t('publishing.settings.title')"
      :subtitle="t('publishing.settings.subtitle')"
    />

    <div
      v-if="loading"
      class="bg-card border border-border rounded-lg p-12 text-center"
    >
      <p class="text-muted-foreground">
        {{ t('publishing.settings.loading') }}
      </p>
    </div>

    <ConsoleListCard v-else>
      <form
        class="space-y-6 p-6"
        @submit.prevent="save"
      >
        <Tabs
          v-model="activeTab"
          class="w-full"
        >
          <TabsList class="bg-transparent p-0 h-auto gap-0 flex-wrap border-none mb-6">
            <TabsTrigger
              value="seo"
              class="relative px-6 py-3 data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none"
            >
              {{ t('publishing.settings.tabs.seo') }}
            </TabsTrigger>
            <TabsTrigger
              value="comments"
              class="relative px-6 py-3 data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none"
            >
              {{ t('publishing.settings.tabs.discussion') }}
            </TabsTrigger>
          </TabsList>

          <TabsContent value="seo">
            <SeoTab
              v-model:form-data="formData"
              :settings="settings"
              :errors="errors"
            />
          </TabsContent>
          <TabsContent value="comments">
            <DiscussionTab
              v-model:form-data="formData"
              :settings="settings"
              :errors="errors"
            />
          </TabsContent>
        </Tabs>

        <div class="flex justify-end pt-6 border-t">
          <Button
            type="submit"
            :disabled="saving"
          >
            {{ saving ? t('publishing.settings.saving') : t('publishing.settings.save') }}
          </Button>
        </div>
      </form>
    </ConsoleListCard>
  </div>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '@/engine/api/client';
import { publishingPaths } from '@/engine/api/paths';
import { parseResponse, ensureArray } from '@/shared/utils/responseParser';
import { PageHeader, ConsoleListCard } from '@/shared/components/shell';
import { Button, Tabs, TabsList, TabsTrigger, TabsContent } from '@/shared/components/ui';
import { useToast } from '@/shared/composables/useToast';
import SeoTab from '@/modules/Publishing/views/settings/tabs/SeoTab.vue';
import DiscussionTab from '@/modules/Publishing/views/settings/tabs/DiscussionTab.vue';

interface Setting {
    id: string;
    key: string;
    value: unknown;
    type: string;
    group: string;
}

const { t } = useI18n();
const toast = useToast();
const loading = ref(true);
const saving = ref(false);
const activeTab = ref('seo');
const settings = ref<Setting[]>([]);
const formData = ref<Record<string, unknown>>({});
const errors = ref<Record<string, string[]>>({});

const ensureSetting = (key: string, defaultValue: unknown, type: string, group: string): void => {
    if (settings.value.some((row) => row.key === key)) {
        return;
    }
    settings.value.push({
        id: `tmp-${key}`,
        key,
        value: defaultValue,
        type,
        group,
    });
};

const load = async (): Promise<void> => {
    loading.value = true;
    try {
        const response = await api.get(publishingPaths.settings);
        const { data } = parseResponse(response);
        settings.value = ensureArray(data) as Setting[];
        ensureSetting('meta_title', 'Jejakawan', 'string', 'seo');
        ensureSetting('meta_description', 'Modern Platform', 'text', 'seo');
        ensureSetting('meta_keywords', 'jejakawan, enterprise, Jejakawan', 'string', 'seo');
        ensureSetting('google_analytics_id', '', 'string', 'seo');
        ensureSetting('google_search_console', '', 'string', 'seo');
        ensureSetting('enable_sitemap', true, 'boolean', 'seo');
        ensureSetting('enable_robots_txt', true, 'boolean', 'seo');
        ensureSetting('comments.security.enable_reply', true, 'boolean', 'comments');
        ensureSetting('comments.security.allow_guests', false, 'boolean', 'comments');
        ensureSetting('comments.security.moderation_enabled', true, 'boolean', 'comments');
        ensureSetting('comments.security.guest_captcha', true, 'boolean', 'comments');
        ensureSetting('comments.security.max_links', 2, 'integer', 'comments');
        ensureSetting('comments.security.banned_words', '[]', 'string', 'comments');

        const next: Record<string, unknown> = {};
        for (const row of settings.value) {
            next[row.key] = row.value;
        }
        formData.value = next;
    } catch (error: unknown) {
        toast.error.fromResponse(error);
        settings.value = [];
    } finally {
        loading.value = false;
    }
};

const save = async (): Promise<void> => {
    saving.value = true;
    errors.value = {};
    try {
        const payload = settings.value.map((row) => ({
            key: row.key,
            value: formData.value[row.key],
            type: row.type,
            group: row.group,
        }));
        await api.post(publishingPaths.settingsBulkUpdate, { settings: payload });
        toast.success.save();
        await load();
    } catch (error: unknown) {
        toast.error.fromResponse(error);
    } finally {
        saving.value = false;
    }
};

onMounted(() => {
    void load();
});
</script>
