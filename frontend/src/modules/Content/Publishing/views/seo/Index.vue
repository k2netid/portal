<template>
  <div class="space-y-6">
    <PageHeader
borderless
      :title="$t('publishing.seo.title')"
    :subtitle="$t('publishing.seo.subtitle')"
    >
    </PageHeader>

    <!-- Tabs -->
    <ConsoleListCard>
      <div class="w-full px-4 pt-4">
      <Tabs
        v-model="activeTab"
        class="w-full"
      >
        <div class="mb-10 flex items-center justify-between">
          <TabsList class="bg-transparent p-0 h-auto gap-0">
            <TabsTrigger
              value="sitemap"
              class="relative px-6 py-3 data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none transition-colors"
            >
              <Globe class="w-4 h-4 mr-2" />
              {{ $t('publishing.seo.tabs.sitemap') }}
            </TabsTrigger>
            <TabsTrigger
              value="robots"
              class="relative px-6 py-3 data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none transition-colors"
            >
              <FileText class="w-4 h-4 mr-2" />
              {{ $t('publishing.seo.tabs.robots') }}
            </TabsTrigger>
            <TabsTrigger
              value="analysis"
              class="relative px-6 py-3 data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none transition-colors"
            >
              <Search class="w-4 h-4 mr-2" />
              {{ $t('publishing.seo.tabs.analysis') }}
            </TabsTrigger>
            <TabsTrigger
              value="schema"
              class="relative px-6 py-3 data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none transition-colors"
            >
              <FileJson class="w-4 h-4 mr-2" />
              {{ $t('publishing.seo.tabs.schema') }}
            </TabsTrigger>
          </TabsList>
        </div>

        <!-- Sitemap Tab -->
        <TabsContent
          value="sitemap"
          class="space-y-6"
        >
          <Card>
            <CardContent class="p-6 space-y-6">
              <div>
                <h3 class="text-lg font-semibold text-foreground mb-4">
                  {{ $t('publishing.seo.sitemap.title') }}
                </h3>
                <Card class="bg-muted/50 border-none">
                  <CardContent class="p-4">
                    <div class="flex items-center justify-between gap-4">
                      <div class="flex-1 min-w-0">
                        <p class="text-xs font-semibold text-foreground/80 mb-1">
                          {{ $t('publishing.seo.sitemap.urlLabel') }}
                        </p>
                        <code class="text-sm font-mono text-primary truncate block">
                          {{ windowLocationOrigin }}{{ sitemapUrl }}
                        </code>
                      </div>
                      <Button
                        variant="ghost"
                        size="sm"
          class="h-10 inline-flex items-center gap-2"
                        @click="copySitemapUrl"
                      >
                        <Copy class="w-4 h-4 mr-2" />
                        {{ $t('publishing.seo.sitemap.copyUrl') }}
                      </Button>
                    </div>
                  </CardContent>
                </Card>
              </div>
              <Button
                :disabled="generatingSitemap"
                size="lg"
                @click="generateSitemap"
              >
                <Loader2
                  v-if="generatingSitemap"
                  class="w-4 h-4 mr-2 animate-spin"
                />
                <RefreshCw
                  v-else
                  class="w-4 h-4 mr-2"
                />
                {{ generatingSitemap ? $t('publishing.seo.sitemap.generating') : $t('publishing.seo.sitemap.generate') }}
              </Button>
            </CardContent>
          </Card>
        </TabsContent>

        <!-- Robots.txt Tab -->
        <TabsContent
          value="robots"
          class="space-y-6"
        >
          <Card>
            <CardContent class="p-6">
              <div>
                <h3 class="text-lg font-semibold text-foreground mb-4">
                  {{ $t('publishing.seo.robots.title') }}
                </h3>
                <div class="space-y-2 mb-4">
                  <Label class="text-sm font-medium">
                    {{ $t('publishing.seo.robots.contentLabel') }}
                  </Label>
                  <Textarea
                    v-model="robotsContent"
                    :rows="15"
                    class="font-mono text-sm resize-none"
                    :placeholder="$t('publishing.seo.robots.placeholder')"
                  />
                </div>
                <div class="flex gap-3">
                  <Button
                    :disabled="savingRobots || !isDirty"
                    @click="saveRobotsTxt"
                  >
                    <Loader2
                      v-if="savingRobots"
                      class="w-4 h-4 mr-2 animate-spin"
                    />
                    <Save
                      v-else
                      class="mr-2 h-4 w-4"
                    />
                    {{ savingRobots ? $t('publishing.seo.robots.saving') : $t('publishing.seo.robots.save') }}
                  </Button>
                  <Button
                    variant="outline"
                    :disabled="!isDirty && !!robotsContent"
                    @click="fetchRobotsTxt"
                  >
                    <RotateCcw class="w-4 h-4 mr-2" />
                    {{ $t('publishing.seo.robots.reload') }}
                  </Button>
                </div>
              </div>
            </CardContent>
          </Card>
        </TabsContent>

        <!-- SEO Analysis Tab -->
        <TabsContent
          value="analysis"
          class="space-y-6"
        >
          <Card>
            <CardContent class="p-6 space-y-6">
              <div>
                <h3 class="text-lg font-semibold text-foreground mb-4">
                  {{ $t('publishing.seo.analysis.title') }}
                </h3>
                <div class="space-y-2 mb-4">
                  <Label>{{ $t('publishing.seo.analysis.selectContent') }}</Label>
                  <Select
                    :model-value="String(selectedContentId || '')"
                    @update:model-value="(val) => selectedContentId = val"
                  >
                    <SelectTrigger>
                      <SelectValue :placeholder="$t('publishing.seo.analysis.placeholder')" />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem 
                        v-for="content in contents" 
                        :key="content.id" 
                        :value="String(content.id)"
                      >
                        {{ content.title }}
                      </SelectItem>
                    </SelectContent>
                  </Select>
                </div>
                <Button
                  :disabled="analyzing || !selectedContentId"
                  @click="runAnalysis"
                >
                  <Loader2
                    v-if="analyzing"
                    class="w-4 h-4 mr-2 animate-spin"
                  />
                  <Activity
                    v-else
                    class="w-4 h-4 mr-2"
                  />
                  {{ analyzing ? $t('publishing.seo.analysis.analyzing') : $t('publishing.seo.analysis.run') }}
                </Button>
              </div>

              <!-- Analysis Results -->
              <div
                v-if="analysisResults"
                class="space-y-4"
              >
                <h4 class="text-md font-semibold text-foreground">
                  {{ $t('publishing.seo.analysis.results') }}
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <Card
                    v-for="(result, key) in analysisResults"
                    :key="key"
                    class="bg-muted/30"
                  >
                    <CardContent class="p-4">
                      <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-semibold text-foreground capitalize">{{ (key as string).replace(/_/g, ' ') }}</span>
                        <Badge
                          :variant="result.score >= 80 ? 'default' : result.score >= 60 ? 'secondary' : 'destructive'"
                          class="font-bold"
                          :class="[
                            result.score >= 80 ? 'bg-green-500/10 text-green-500 border-green-500/20' :
                            result.score >= 60 ? 'bg-yellow-500/10 text-yellow-600 border-yellow-500/20' :
                            'bg-destructive/10 text-destructive border-destructive/20'
                          ]"
                        >
                          {{ $t('publishing.seo.analysis.score', { score: result.score }) }}
                        </Badge>
                      </div>
                      <p class="text-sm text-muted-foreground mb-3">
                        {{ result.message }}
                      </p>
                      <ul
                        v-if="result.suggestions && result.suggestions.length > 0"
                        class="space-y-1"
                      >
                        <li
                          v-for="(suggestion, idx) in result.suggestions"
                          :key="idx"
                          class="text-xs text-muted-foreground flex items-start"
                        >
                          <span class="mr-2 mt-0.5 text-indigo-500">•</span>
                          {{ suggestion }}
                        </li>
                      </ul>
                    </CardContent>
                  </Card>
                </div>
              </div>
            </CardContent>
          </Card>
        </TabsContent>

        <!-- Schema Generation Tab -->
        <TabsContent
          value="schema"
          class="space-y-6"
        >
          <Card>
            <CardContent class="p-6 space-y-6">
              <div>
                <h3 class="text-lg font-semibold text-foreground mb-4">
                  {{ $t('publishing.seo.schema.title') }}
                </h3>
                <div class="space-y-2 mb-4">
                  <Label>{{ $t('publishing.seo.schema.selectContent') }}</Label>
                  <Select
                    :model-value="String(selectedContentForSchema || '')"
                    @update:model-value="(val) => selectedContentForSchema = val"
                  >
                    <SelectTrigger>
                      <SelectValue :placeholder="$t('publishing.seo.analysis.placeholder')" />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem 
                        v-for="content in contents" 
                        :key="content.id" 
                        :value="String(content.id)"
                      >
                        {{ content.title }}
                      </SelectItem>
                    </SelectContent>
                  </Select>
                </div>
                <Button
                  :disabled="generatingSchema || !selectedContentForSchema"
                  @click="generateSchema"
                >
                  <Loader2
                    v-if="generatingSchema"
                    class="w-4 h-4 mr-2 animate-spin"
                  />
                  <Code2
                    v-else
                    class="w-4 h-4 mr-2"
                  />
                  {{ generatingSchema ? $t('publishing.seo.schema.generating') : $t('publishing.seo.schema.generate') }}
                </Button>
              </div>

              <!-- Schema JSON -->
              <Card
                v-if="schemaJson"
                class="bg-muted/30 overflow-hidden"
              >
                <CardHeader class="flex flex-row items-center justify-between py-3 px-6 bg-muted/50 border-b">
                  <CardTitle class="text-sm font-medium">
                    {{ $t('publishing.seo.schema.jsonTitle') }}
                  </CardTitle>
                  <Button
                    size="sm"
          class="h-10 inline-flex items-center gap-2"
                    variant="secondary"
                    @click="copySchema"
                  >
                    <Copy class="w-3.5 h-3.5 mr-2" />
                    {{ $t('publishing.seo.schema.copy') }}
                  </Button>
                </CardHeader>
                <CardContent class="p-0">
                  <pre class="p-6 overflow-x-auto text-[13px] font-mono leading-relaxed bg-card text-foreground">{{ schemaJson }}</pre>
                </CardContent>
              </Card>
            </CardContent>
          </Card>
        </TabsContent>
      </Tabs>
      </div>
    </ConsoleListCard>
  </div>
</template>

<script setup lang="ts">
import { PageHeader, ConsoleListCard } from '@/shared/components/shell';

import { logger } from '@/shared/utils/logger';
import { ref, onMounted, computed } from 'vue';

import { useI18n } from 'vue-i18n';
import api from '@/engine/api/client';
import { useToast } from '@/shared/composables/useToast';
import { 
    Card, 
    CardHeader, 
    CardTitle, 
    CardContent, 
    Button, 
    Textarea, 
    Label, 
    Badge, 
    Select, 
    SelectTrigger, 
    SelectValue, 
    SelectContent, 
    SelectItem,
    Tabs,
    TabsList,
    TabsTrigger,
    TabsContent
} from '@/shared/components/ui';
import {
  Activity,
  Code2,
  Copy,
  FileJson,
  FileText,
  Globe,
  Loader2,
  RefreshCw,
  RotateCcw,
  Save,
  Search,
} from 'lucide-vue-next';
import { parseResponse, ensureArray, parseSingleResponse } from '@/shared/utils/responseParser';

interface AnalysisResult {
    score: number;
    message: string;
    suggestions: string[];
}

interface SeoContent {
    id: string | string;
    title: string;
}

const { t } = useI18n();
const toast = useToast();
const activeTab = ref('sitemap');

const windowLocationOrigin = ref(window.location.origin);
const sitemapUrl = ref('/sitemap.xml');
const generatingSitemap = ref(false);
const robotsContent = ref('');
const savingRobots = ref(false);
const contents = ref<SeoContent[]>([]);
const selectedContentId = ref<string | null>(null);
const analyzing = ref(false);
const analysisResults = ref<Record<string, AnalysisResult> | null>(null);
const selectedContentForSchema = ref<string | null>(null);
const generatingSchema = ref(false);
const schemaJson = ref<string | null>(null);
const initialRobotsContent = ref('');

const isDirty = computed(() => {
    return robotsContent.value !== initialRobotsContent.value;
});

const fetchRobotsTxt = async () => {
    try {
        const response = await api.get('/manage/publishing/seo/robots-txt');
        const data = parseSingleResponse<{ content: string }>(response) || { content: '' };
        robotsContent.value = data.content || '';
        initialRobotsContent.value = robotsContent.value;
    } catch (error: unknown) {
        logger.error('Failed to fetch robots.txt:', error);
    }
};

const saveRobotsTxt = async () => {
    savingRobots.value = true;
    try {
        await api.put('/manage/publishing/seo/robots-txt', { content: robotsContent.value });
        initialRobotsContent.value = robotsContent.value;
        toast.success.save();
    } catch (error: unknown) {
        logger.error('Failed to save robots.txt:', error);
        toast.error.fromResponse(error);
    } finally {
        savingRobots.value = false;
    }
};

const generateSitemap = async () => {
    generatingSitemap.value = true;
    try {
        await api.get('/manage/publishing/seo/sitemap');
        toast.success.action(t('publishing.seo.sitemap.generated'));
    } catch (error: unknown) {
        logger.error('Failed to generate sitemap:', error);
        toast.error.fromResponse(error);
    } finally {
        generatingSitemap.value = false;
    }
};

const copySitemapUrl = () => {
    navigator.clipboard.writeText(window.location.origin + sitemapUrl.value);
    toast.success.default(t('publishing.seo.sitemap.copySuccess'));
};

const fetchContents = async () => {
    try {
        const response = await api.get('/manage/publishing/contents');
        const { data } = parseResponse(response);
        contents.value = ensureArray(data) as SeoContent[];
    } catch (error: unknown) {
        logger.error('Failed to fetch contents:', error);
        contents.value = [];
    }
};

const runAnalysis = async () => {
    if (!selectedContentId.value) return;
    
    analyzing.value = true;
    try {
        const response = await api.get(`/manage/publishing/contents/${selectedContentId.value}/seo-analysis`);
        analysisResults.value = parseSingleResponse<Record<string, AnalysisResult>>(response) || {};
    } catch (error: unknown) {
        logger.error('Failed to run SEO analysis:', error);
        toast.error.fromResponse(error);
    } finally {
        analyzing.value = false;
    }
};

const generateSchema = async () => {
    if (!selectedContentForSchema.value) return;
    
    generatingSchema.value = true;
    try {
        const response = await api.get(`/manage/publishing/contents/${selectedContentForSchema.value}/schema`);
        const schema = parseSingleResponse<Record<string, unknown>>(response) || {};
        schemaJson.value = JSON.stringify(schema, null, 2);
    } catch (error: unknown) {
        logger.error('Failed to generate schema:', error);
        toast.error.fromResponse(error);
    } finally {
        generatingSchema.value = false;
    }
};

const copySchema = () => {
    if (schemaJson.value) {
        navigator.clipboard.writeText(schemaJson.value);
        toast.success.default(t('publishing.seo.schema.copySuccess'));
    }
};

onMounted(() => {
    fetchRobotsTxt();
    fetchContents();
});
</script>

