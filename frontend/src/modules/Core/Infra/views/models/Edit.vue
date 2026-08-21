<template>
  <div class="space-y-6 w-full">
    <PageHeader
      borderless
      :title="isCreate ? $t('infra.models.newType') : (form.name || $t('infra.models.editType'))"
      :subtitle="modelsSubtitle"
    >
      <template #actions>
        <div class="flex items-center gap-2">
          <Button
            type="button"
            variant="ghost"
            size="sm"
            class="h-9 gap-1.5 text-xs text-muted-foreground hover:text-foreground"
            @click="router.push({ name: 'model-index' })"
          >
            <ArrowLeft class="h-3.5 w-3.5" />
            {{ $t('infra.models.back') }}
          </Button>
          <Button
            v-if="!isCreate && form.slug"
            type="button"
            variant="outline"
            size="sm"
            class="h-9 gap-1.5 text-xs"
            @click="router.push({ name: 'dynamic-records-index', params: { slug: form.slug } })"
          >
            <Database class="h-3.5 w-3.5" />
            {{ $t('infra.models.table.records') }}
          </Button>
          <Button
            type="button"
            size="sm"
            class="h-9 gap-2 text-xs"
            :disabled="saving"
            @click="handleSave"
          >
            <Spinner v-if="saving" class="h-3.5 w-3.5" />
            <Save v-else class="h-3.5 w-3.5" />
            {{ saving ? $t('infra.models.saving') : $t('infra.models.save') }}
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
        <TabsList class="inline-flex items-center gap-1 p-1 bg-muted/60 rounded-lg border border-border/50 max-w-full overflow-x-auto">
          <TabsTrigger value="schema" class="text-xs gap-1.5 px-4 py-2">
            <Layers class="h-3.5 w-3.5" />
            {{ $t('infra.models.form.tabs.schema') }}
          </TabsTrigger>
          <TabsTrigger value="api" :disabled="isCreate" class="text-xs gap-1.5 px-4 py-2">
            <Globe class="h-3.5 w-3.5" />
            {{ $t('infra.models.form.tabs.api') }}
          </TabsTrigger>
          <TabsTrigger value="rules" :disabled="isCreate" class="text-xs gap-1.5 px-4 py-2">
            <CheckSquare class="h-3.5 w-3.5" />
            {{ $t('infra.models.form.tabs.rules') }}
          </TabsTrigger>
        </TabsList>

        <!-- TAB 1: Schema & Fields -->
        <TabsContent value="schema" class="space-y-6 pt-4">
          <!-- General Schema Information -->
          <ConsoleFormCard
            :title="$t('infra.models.form.general')"
            :subtitle="$t('infra.models.form.generalHint')"
          >
            <div class="grid gap-4 sm:grid-cols-2">
              <div>
                <Label class="text-xs mb-1.5 block">
                  {{ $t('infra.models.form.name') }}
                  <span class="text-destructive">*</span>
                </Label>
                <Input
                  v-model="form.name"
                  required
                  class="h-9 text-xs"
                  :placeholder="$t('infra.models.form.namePlaceholder')"
                  @blur="maybeSlugFromName"
                />
              </div>

              <div>
                <Label class="text-xs mb-1.5 block">
                  {{ $t('infra.models.form.slug') }}
                  <span class="text-destructive">*</span>
                </Label>
                <Input
                  v-model="form.slug"
                  required
                  class="h-9 font-mono text-xs"
                  :placeholder="$t('infra.models.form.slugPlaceholder')"
                  @input="sanitizeSlug"
                />
                <p class="text-[11px] text-muted-foreground mt-1">
                  {{ $t('infra.models.form.slugHint') }}
                </p>
              </div>

              <div class="sm:col-span-2">
                <Label class="text-xs mb-1.5 block">
                  {{ $t('infra.models.form.description') }}
                </Label>
                <Textarea
                  v-model="form.description"
                  rows="2"
                  class="text-xs resize-y"
                  :placeholder="$t('infra.models.form.descriptionPlaceholder')"
                />
              </div>

              <div class="sm:col-span-2 pt-2 border-t border-border/50">
                <label class="flex items-center gap-2.5 text-xs font-medium text-foreground cursor-pointer select-none">
                  <Checkbox
                    :checked="form.is_active"
                    @update:checked="form.is_active = $event === true"
                  />
                  <span>{{ $t('infra.models.form.active') }}</span>
                </label>
              </div>
            </div>
          </ConsoleFormCard>

          <!-- Field Definitions Section -->
          <ConsoleFormCard
            :title="$t('infra.models.form.fieldsSection')"
            :subtitle="$t('infra.models.form.fieldsSectionHint')"
          >
            <DataModelBuilder v-model="form.fields" :current-type-slug="form.slug" />
          </ConsoleFormCard>
        </TabsContent>

        <!-- TAB 2: API & OpenAPI -->
        <TabsContent value="api" class="space-y-6 pt-4">
          <!-- REST API Endpoints Guide -->
          <ConsoleFormCard
            :title="$t('infra.models.form.apiSection')"
            :subtitle="$t('infra.models.form.apiSectionHint')"
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
                    :title="$t('infra.models.table.copyEndpoint')"
                    @click="copyUrl(ep.path)"
                  >
                    <Copy class="h-3.5 w-3.5" />
                  </Button>
                </div>
              </div>
            </div>
          </ConsoleFormCard>

          <!-- Interactive Code Examples Playground -->
          <ConsoleFormCard
            title="Interactive Code Examples"
            subtitle="Copy ready-to-run API request snippets in your favorite language"
          >
            <div class="space-y-4">
              <div class="flex flex-wrap items-center justify-between gap-2 pb-2 border-b border-border/50">
                <!-- Language Selector -->
                <div class="flex items-center gap-1 bg-muted/60 p-1 rounded-lg">
                  <Button
                    type="button"
                    :variant="selectedCodeLang === 'curl' ? 'default' : 'ghost'"
                    size="sm"
                    class="h-7 text-xs px-2.5"
                    @click="selectedCodeLang = 'curl'"
                  >
                    cURL
                  </Button>
                  <Button
                    type="button"
                    :variant="selectedCodeLang === 'js' ? 'default' : 'ghost'"
                    size="sm"
                    class="h-7 text-xs px-2.5"
                    @click="selectedCodeLang = 'js'"
                  >
                    JavaScript
                  </Button>
                  <Button
                    type="button"
                    :variant="selectedCodeLang === 'php' ? 'default' : 'ghost'"
                    size="sm"
                    class="h-7 text-xs px-2.5"
                    @click="selectedCodeLang = 'php'"
                  >
                    PHP
                  </Button>
                  <Button
                    type="button"
                    :variant="selectedCodeLang === 'python' ? 'default' : 'ghost'"
                    size="sm"
                    class="h-7 text-xs px-2.5"
                    @click="selectedCodeLang = 'python'"
                  >
                    Python
                  </Button>
                </div>

                <!-- Operation Selector -->
                <div class="flex items-center gap-1 bg-muted/60 p-1 rounded-lg">
                  <Button
                    type="button"
                    :variant="selectedCodeOp === 'list' ? 'secondary' : 'ghost'"
                    size="sm"
                    class="h-7 text-[11px] px-2"
                    @click="selectedCodeOp = 'list'"
                  >
                    List
                  </Button>
                  <Button
                    type="button"
                    :variant="selectedCodeOp === 'create' ? 'secondary' : 'ghost'"
                    size="sm"
                    class="h-7 text-[11px] px-2"
                    @click="selectedCodeOp = 'create'"
                  >
                    Create
                  </Button>
                  <Button
                    type="button"
                    :variant="selectedCodeOp === 'show' ? 'secondary' : 'ghost'"
                    size="sm"
                    class="h-7 text-[11px] px-2"
                    @click="selectedCodeOp = 'show'"
                  >
                    Get
                  </Button>
                  <Button
                    type="button"
                    :variant="selectedCodeOp === 'update' ? 'secondary' : 'ghost'"
                    size="sm"
                    class="h-7 text-[11px] px-2"
                    @click="selectedCodeOp = 'update'"
                  >
                    Update
                  </Button>
                  <Button
                    type="button"
                    :variant="selectedCodeOp === 'delete' ? 'secondary' : 'ghost'"
                    size="sm"
                    class="h-7 text-[11px] px-2"
                    @click="selectedCodeOp = 'delete'"
                  >
                    Delete
                  </Button>
                </div>
              </div>

              <!-- Code Preview Area -->
              <div class="relative rounded-lg border border-border/80 bg-muted/40 p-4 font-mono text-xs overflow-x-auto">
                <Button
                  type="button"
                  variant="outline"
                  size="sm"
                  class="absolute top-3 right-3 h-7 text-xs gap-1 bg-background/80 backdrop-blur-xs"
                  @click="copyCodeSnippet"
                >
                  <Copy class="h-3 w-3" />
                  <span>Copy</span>
                </Button>
                <pre class="pt-1 text-foreground/90 whitespace-pre-wrap">{{ codeSnippet }}</pre>
              </div>
            </div>
          </ConsoleFormCard>

          <!-- OpenAPI Specification -->
          <ConsoleFormCard
            :title="$t('infra.models.form.openApiTitle')"
            :subtitle="$t('infra.models.form.openApiHint')"
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
                    {{ $t('infra.models.form.copySpec') }}
                  </Button>
                  <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    class="h-8 text-xs gap-1.5"
                    @click="downloadOpenApiSpec"
                  >
                    <Download class="h-3.5 w-3.5" />
                    {{ $t('infra.models.form.downloadSpec') }}
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
            :title="$t('infra.models.form.rulesTitle')"
            :subtitle="$t('infra.models.form.rulesHint')"
          >
            <div v-if="validationRulesList.length === 0" class="p-6 text-center text-xs text-muted-foreground">
              {{ $t('infra.models.form.noRules') }}
            </div>
            <div v-else class="overflow-x-auto">
              <Table>
                <TableHeader>
                  <TableRow class="bg-muted/40">
                    <TableHead class="text-xs font-semibold">{{ $t('infra.models.form.ruleField') }}</TableHead>
                    <TableHead class="text-xs font-semibold">{{ $t('infra.models.form.ruleDefinition') }}</TableHead>
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
      <div class="sticky bottom-0 z-10 bg-background/95 backdrop-blur-sm p-4 -mx-6 -mb-6 sm:-mx-8 sm:-mb-8 border-t border-border mt-8 flex items-center justify-between gap-3 shadow-xs">
        <div>
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
            {{ $t('infra.models.delete') }}
          </Button>
        </div>

        <div class="flex items-center gap-2 ml-auto">
          <Button
            type="button"
            variant="outline"
            size="sm"
            class="h-9 text-xs"
            @click="router.push({ name: 'model-index' })"
          >
            {{ $t('infra.models.cancel') }}
          </Button>

          <Button
            type="submit"
            size="sm"
            class="h-9 gap-2 text-xs"
            :disabled="saving"
          >
            <Spinner v-if="saving" class="h-3.5 w-3.5" />
            <Save v-else class="h-3.5 w-3.5" />
            {{ saving ? $t('infra.models.saving') : $t('infra.models.save') }}
          </Button>
        </div>
      </div>
    </form>

    <!-- Delete Confirmation Modal -->
    <ConfirmModal
      :open="deleteModalOpen"
      :title="$t('infra.models.deleteType')"
      :message="$t('infra.models.deleteTypeConfirm', { name: form.name })"
      :confirm-label="$t('infra.models.delete')"
      :cancel-label="$t('infra.models.cancel')"
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
import DataModelBuilder from '../../components/models/DataModelBuilder.vue';
import DataModelService, { type DataModelSchema, type DataModelFieldDefinition } from '../../services/dataModelService';

const { t } = useI18n();
const route = useRoute();
const router = useRouter();
const toast = useToast();

const typeId = computed(() => route.params.id as string | undefined);
const isCreate = computed(() => route.name === 'model-create');

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
    fields: [] as DataModelFieldDefinition[],
});

const modelsSubtitle = computed(() => {
    const base = t('infra.models.subtitle');
    const slug = form.value.slug;
    if (!slug) return base;
    return `${base} — ${t('infra.models.apiHint', { slug })}`;
});

const dynamicEndpoints = computed(() => {
    const slug = form.value.slug || ':slug';
    return [
        { method: 'GET', path: `/api/v1/dynamic/${slug}`, label: t('infra.models.form.endpoints.list') },
        { method: 'POST', path: `/api/v1/dynamic/${slug}`, label: t('infra.models.form.endpoints.create') },
        { method: 'GET', path: `/api/v1/dynamic/${slug}/{id}`, label: t('infra.models.form.endpoints.show') },
        { method: 'PUT', path: `/api/v1/dynamic/${slug}/{id}`, label: t('infra.models.form.endpoints.update') },
        { method: 'DELETE', path: `/api/v1/dynamic/${slug}/{id}`, label: t('infra.models.form.endpoints.delete') },
    ];
});

const validationRulesList = computed(() => {
    return Object.entries(validationRulesMap.value).map(([field, expression]) => ({
        field,
        expression,
    }));
});

// Interactive Code Examples Logic
const selectedCodeLang = ref<'curl' | 'js' | 'php' | 'python'>('curl');
const selectedCodeOp = ref<'list' | 'create' | 'show' | 'update' | 'delete'>('list');

const codeSnippet = computed(() => {
    const slug = form.value.slug || 'example_model';
    const baseUrl = typeof window !== 'undefined' ? window.location.origin : 'https://api.example.com';
    const samplePayload: Record<string, unknown> = {};
    for (const f of form.value.fields) {
        if (f.type === 'number') samplePayload[f.slug] = 42;
        else if (f.type === 'boolean') samplePayload[f.slug] = true;
        else if (f.type === 'email') samplePayload[f.slug] = 'user@example.com';
        else if (f.type === 'url') samplePayload[f.slug] = 'https://example.com';
        else if (f.type === 'date') samplePayload[f.slug] = '2026-08-20';
        else if (f.type === 'color') samplePayload[f.slug] = '#3b82f6';
        else if (f.type === 'select' && f.options?.length) samplePayload[f.slug] = f.options[0];
        else if (f.type === 'relation' && f.relation_mode === 'multiple') samplePayload[f.slug] = ['rec_uuid_1', 'rec_uuid_2'];
        else if (f.type === 'relation') samplePayload[f.slug] = 'rec_uuid_1';
        else samplePayload[f.slug] = `Sample ${f.name}`;
    }
    const jsonBody = JSON.stringify(samplePayload, null, 2);

    if (selectedCodeLang.value === 'curl') {
        if (selectedCodeOp.value === 'list') {
            return `curl -X GET "${baseUrl}/api/v1/dynamic/${slug}?page=1&per_page=15" \\\n  -H "Accept: application/json" \\\n  -H "Authorization: Bearer YOUR_TOKEN"`;
        }
        if (selectedCodeOp.value === 'create') {
            return `curl -X POST "${baseUrl}/api/v1/dynamic/${slug}" \\\n  -H "Content-Type: application/json" \\\n  -H "Accept: application/json" \\\n  -H "Authorization: Bearer YOUR_TOKEN" \\\n  -d '${JSON.stringify(samplePayload)}'`;
        }
        if (selectedCodeOp.value === 'show') {
            return `curl -X GET "${baseUrl}/api/v1/dynamic/${slug}/RECORD_ID" \\\n  -H "Accept: application/json" \\\n  -H "Authorization: Bearer YOUR_TOKEN"`;
        }
        if (selectedCodeOp.value === 'update') {
            return `curl -X PUT "${baseUrl}/api/v1/dynamic/${slug}/RECORD_ID" \\\n  -H "Content-Type: application/json" \\\n  -H "Accept: application/json" \\\n  -H "Authorization: Bearer YOUR_TOKEN" \\\n  -d '${JSON.stringify(samplePayload)}'`;
        }
        return `curl -X DELETE "${baseUrl}/api/v1/dynamic/${slug}/RECORD_ID" \\\n  -H "Accept: application/json" \\\n  -H "Authorization: Bearer YOUR_TOKEN"`;
    }

    if (selectedCodeLang.value === 'js') {
        if (selectedCodeOp.value === 'list') {
            return `// Fetch dynamic records\nconst response = await fetch('${baseUrl}/api/v1/dynamic/${slug}?page=1&per_page=15', {\n  headers: {\n    'Accept': 'application/json',\n    'Authorization': 'Bearer YOUR_TOKEN'\n  }\n});\nconst result = await response.json();\nconsole.log(result.data);`;
        }
        if (selectedCodeOp.value === 'create') {
            return `// Create a dynamic record\nconst payload = ${jsonBody};\n\nconst response = await fetch('${baseUrl}/api/v1/dynamic/${slug}', {\n  method: 'POST',\n  headers: {\n    'Content-Type': 'application/json',\n    'Accept': 'application/json',\n    'Authorization': 'Bearer YOUR_TOKEN'\n  },\n  body: JSON.stringify(payload)\n});\nconst result = await response.json();\nconsole.log(result.data);`;
        }
        if (selectedCodeOp.value === 'show') {
            return `// Get single dynamic record\nconst recordId = 'RECORD_ID';\nconst response = await fetch(\`${baseUrl}/api/v1/dynamic/${slug}/\${recordId}\`, {\n  headers: {\n    'Accept': 'application/json',\n    'Authorization': 'Bearer YOUR_TOKEN'\n  }\n});\nconst result = await response.json();\nconsole.log(result.data);`;
        }
        if (selectedCodeOp.value === 'update') {
            return `// Update a dynamic record\nconst recordId = 'RECORD_ID';\nconst payload = ${jsonBody};\n\nconst response = await fetch(\`${baseUrl}/api/v1/dynamic/${slug}/\${recordId}\`, {\n  method: 'PUT',\n  headers: {\n    'Content-Type': 'application/json',\n    'Accept': 'application/json',\n    'Authorization': 'Bearer YOUR_TOKEN'\n  },\n  body: JSON.stringify(payload)\n});\nconst result = await response.json();\nconsole.log(result.data);`;
        }
        return `// Delete dynamic record\nconst recordId = 'RECORD_ID';\nconst response = await fetch(\`${baseUrl}/api/v1/dynamic/${slug}/\${recordId}\`, {\n  method: 'DELETE',\n  headers: {\n    'Accept': 'application/json',\n    'Authorization': 'Bearer YOUR_TOKEN'\n  }\n});\nconst result = await response.json();\nconsole.log(result);`;
    }

    if (selectedCodeLang.value === 'php') {
        if (selectedCodeOp.value === 'list') {
            return `<?php\n\n$ch = curl_init('${baseUrl}/api/v1/dynamic/${slug}');\ncurl_setopt_array($ch, [\n    CURLOPT_RETURNTRANSFER => true,\n    CURLOPT_HTTPHEADER => [\n        'Accept: application/json',\n        'Authorization: Bearer YOUR_TOKEN',\n    ],\n]);\n$response = curl_exec($ch);\n$data = json_decode($response, true);\ncurl_close($ch);`;
        }
        if (selectedCodeOp.value === 'create') {
            return `<?php\n\n$payload = ${jsonBody};\n\n$ch = curl_init('${baseUrl}/api/v1/dynamic/${slug}');\ncurl_setopt_array($ch, [\n    CURLOPT_RETURNTRANSFER => true,\n    CURLOPT_POST => true,\n    CURLOPT_POSTFIELDS => json_encode($payload),\n    CURLOPT_HTTPHEADER => [\n        'Content-Type: application/json',\n        'Accept: application/json',\n        'Authorization: Bearer YOUR_TOKEN',\n    ],\n]);\n$response = curl_exec($ch);\n$result = json_decode($response, true);\ncurl_close($ch);`;
        }
        if (selectedCodeOp.value === 'show') {
            return `<?php\n\n$recordId = 'RECORD_ID';\n$ch = curl_init("${baseUrl}/api/v1/dynamic/${slug}/{$recordId}");\ncurl_setopt_array($ch, [\n    CURLOPT_RETURNTRANSFER => true,\n    CURLOPT_HTTPHEADER => [\n        'Accept: application/json',\n        'Authorization: Bearer YOUR_TOKEN',\n    ],\n]);\n$response = curl_exec($ch);\n$result = json_decode($response, true);\ncurl_close($ch);`;
        }
        if (selectedCodeOp.value === 'update') {
            return `<?php\n\n$recordId = 'RECORD_ID';\n$payload = ${jsonBody};\n\n$ch = curl_init("${baseUrl}/api/v1/dynamic/${slug}/{$recordId}");\ncurl_setopt_array($ch, [\n    CURLOPT_RETURNTRANSFER => true,\n    CURLOPT_CUSTOMREQUEST => 'PUT',\n    CURLOPT_POSTFIELDS => json_encode($payload),\n    CURLOPT_HTTPHEADER => [\n        'Content-Type: application/json',\n        'Accept: application/json',\n        'Authorization: Bearer YOUR_TOKEN',\n    ],\n]);\n$response = curl_exec($ch);\n$result = json_decode($response, true);\ncurl_close($ch);`;
        }
        return `<?php\n\n$recordId = 'RECORD_ID';\n$ch = curl_init("${baseUrl}/api/v1/dynamic/${slug}/{$recordId}");\ncurl_setopt_array($ch, [\n    CURLOPT_RETURNTRANSFER => true,\n    CURLOPT_CUSTOMREQUEST => 'DELETE',\n    CURLOPT_HTTPHEADER => [\n        'Accept: application/json',\n        'Authorization: Bearer YOUR_TOKEN',\n    ],\n]);\n$response = curl_exec($ch);\n$result = json_decode($response, true);\ncurl_close($ch);`;
    }

    // Python
    if (selectedCodeOp.value === 'list') {
        return `import requests\n\nheaders = {\n    "Accept": "application/json",\n    "Authorization": "Bearer YOUR_TOKEN"\n}\nres = requests.get("${baseUrl}/api/v1/dynamic/${slug}", headers=headers)\nprint(res.json())`;
    }
    if (selectedCodeOp.value === 'create') {
        return `import requests\n\npayload = ${jsonBody.replace(/true/g, 'True').replace(/false/g, 'False').replace(/null/g, 'None')}\nheaders = {\n    "Content-Type": "application/json",\n    "Accept": "application/json",\n    "Authorization": "Bearer YOUR_TOKEN"\n}\nres = requests.post("${baseUrl}/api/v1/dynamic/${slug}", json=payload, headers=headers)\nprint(res.json())`;
    }
    if (selectedCodeOp.value === 'show') {
        return `import requests\n\nrecord_id = "RECORD_ID"\nheaders = {\n    "Accept": "application/json",\n    "Authorization": "Bearer YOUR_TOKEN"\n}\nres = requests.get(f"${baseUrl}/api/v1/dynamic/${slug}/{record_id}", headers=headers)\nprint(res.json())`;
    }
    if (selectedCodeOp.value === 'update') {
        return `import requests\n\nrecord_id = "RECORD_ID"\npayload = ${jsonBody.replace(/true/g, 'True').replace(/false/g, 'False').replace(/null/g, 'None')}\nheaders = {\n    "Content-Type": "application/json",\n    "Accept": "application/json",\n    "Authorization": "Bearer YOUR_TOKEN"\n}\nres = requests.put(f"${baseUrl}/api/v1/dynamic/${slug}/{record_id}", json=payload, headers=headers)\nprint(res.json())`;
    }
    return `import requests\n\nrecord_id = "RECORD_ID"\nheaders = {\n    "Accept": "application/json",\n    "Authorization": "Bearer YOUR_TOKEN"\n}\nres = requests.delete(f"${baseUrl}/api/v1/dynamic/${slug}/{record_id}", headers=headers)\nprint(res.json())`;
});

async function copyCodeSnippet(): Promise<void> {
    try {
        await navigator.clipboard.writeText(codeSnippet.value);
        toast.success.default(t('infra.models.messages.endpointCopied') || 'Code copied to clipboard');
    } catch {
        prompt('Code Snippet:', codeSnippet.value);
    }
}

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
        const response = await DataModelService.getType(typeId.value);
        const payload = parseSingleResponse<DataModelSchema>(response);
        if (!payload) {
            throw new Error('Data model not found');
        }
        form.value = {
            name: payload.name,
            slug: payload.slug,
            description: payload.description ?? '',
            is_active: payload.is_active !== false,
            fields: payload.fields ?? [],
        };
    } catch (e: unknown) {
        toast.error.default(e instanceof Error ? e.message : t('infra.models.messages.loadFailed'));
    } finally {
        loading.value = false;
    }
}

async function loadValidationRules(): Promise<void> {
    if (!typeId.value) return;
    try {
        const response = await DataModelService.getValidationRules(typeId.value);
        const payload = parseSingleResponse<{ validation_rules: Record<string, string> }>(response);
        validationRulesMap.value = payload?.validation_rules ?? {};
    } catch {
        // Quiet fallback
    }
}

async function loadOpenApiSpec(): Promise<void> {
    if (!form.value.slug) return;
    try {
        const response = await DataModelService.getOpenApiBySlug(form.value.slug);
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
        toast.success.default(t('infra.models.messages.endpointCopied'));
    } catch {
        prompt('URL:', fullUrl);
    }
}

async function copyOpenApiSpec(): Promise<void> {
    if (!openApiPreview.value) return;
    try {
        await navigator.clipboard.writeText(openApiPreview.value);
        toast.success.default(t('infra.models.messages.specCopied'));
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
            const response = await DataModelService.createType(payload);
            const created = parseSingleResponse<DataModelSchema>(response);
            toast.success.default(t('infra.models.messages.created'));
            if (created?.id) {
                await router.replace({ name: 'model-edit', params: { id: created.id } });
            }
        } else if (typeId.value) {
            await DataModelService.updateType(typeId.value, payload);
            toast.success.default(t('infra.models.messages.saved'));
            await loadValidationRules();
            await loadOpenApiSpec();
        }
    } catch (e: unknown) {
        toast.error.default(e instanceof Error ? e.message : t('infra.models.messages.saveFailed'));
    } finally {
        saving.value = false;
    }
}

async function executeDelete(): Promise<void> {
    if (!typeId.value) return;
    deleting.value = true;
    try {
        await DataModelService.deleteType(typeId.value);
        toast.success.default(t('infra.models.messages.deleted'));
        deleteModalOpen.value = false;
        await router.push({ name: 'model-index' });
    } catch (e: unknown) {
        toast.error.default(e instanceof Error ? e.message : t('infra.models.messages.deleteFailed'));
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
