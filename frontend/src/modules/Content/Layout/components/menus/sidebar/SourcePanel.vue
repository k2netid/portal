<template>
  <Card class="border-t-4 border-t-primary/20">
    <CardHeader class="pb-3">
      <div class="flex items-center justify-between">
        <CardTitle class="text-base">
          {{ t('layout.menus.form.addItems') }}
        </CardTitle>
        <Button
          variant="ghost"
          size="icon"
          class="h-10 w-10"
          :aria-label="t('common.actions.collapse')"
          :title="t('common.actions.collapse')"
          @click="$emit('collapse')"
        >
          <PanelLeftClose class="w-4 h-4" />
        </Button>
      </div>
    </CardHeader>
    <CardContent class="p-0">
      <Accordion
        type="multiple"
        class="w-full"
        :default-value="['pages', 'custom']"
      >
        <!-- Pages -->
        <AccordionItem value="pages">
          <AccordionTrigger class="px-4 py-3 hover:no-underline hover:bg-muted/50">
            <div class="flex items-center gap-2 flex-1">
              <FileText class="w-4 h-4 text-blue-500" />
              <span>{{ t('layout.menus.form.types.page') }}</span>
              <Badge
                variant="secondary"
                class="ml-2"
              >
                {{ pages.length }}
              </Badge>
              <Button 
                size="icon" 
                variant="ghost" 
                class="ml-auto h-6 w-6" 
                :aria-label="t('layout.menus.actions.addAll')"
                :title="t('layout.menus.actions.addAll')" 
                @click.stop="addAll('page', pages)"
              >
                <Plus class="w-3 h-3" />
              </Button>
            </div>
          </AccordionTrigger>
          <AccordionContent class="px-4 pb-4">
            <div class="max-h-[250px] overflow-y-auto pr-2 custom-scrollbar">
              <div
                v-if="loadingPages"
                class="flex justify-center py-4"
              >
                <Loader2 class="w-5 h-5 animate-spin text-muted-foreground" />
              </div>
              <draggable
                v-else
                :list="pages"
                :group="{ name: 'menu', pull: 'clone', put: false }"
                :clone="(item: unknown) => createItem('page', item)"
                item-key="id"
                class="space-y-2"
              >
                <template #item="{ element }">
                  <SourceItem 
                    :item="element" 
                    :type="'page'"
                    @add="addItem('page', element)"
                  />
                </template>
              </draggable>
              <div
                v-if="!loadingPages && pages.length === 0"
                class="text-center py-4 text-muted-foreground text-xs"
              >
                {{ t('layout.menus.form.noPages') }}
              </div>
            </div>
          </AccordionContent>
        </AccordionItem>

        <!-- Posts -->
        <AccordionItem value="posts">
          <AccordionTrigger class="px-4 py-3 hover:no-underline hover:bg-muted/50">
            <div class="flex items-center gap-2 flex-1">
              <File class="w-4 h-4 text-orange-500" />
              <span>{{ t('layout.menus.form.types.post') }}</span>
              <Badge
                variant="secondary"
                class="ml-2"
              >
                {{ posts.length }}
              </Badge>
              <Button 
                size="icon" 
                variant="ghost" 
                class="ml-auto h-6 w-6" 
                :aria-label="t('layout.menus.actions.addAll')"
                :title="t('layout.menus.actions.addAll')" 
                @click.stop="addAll('post', posts)"
              >
                <Plus class="w-3 h-3" />
              </Button>
            </div>
          </AccordionTrigger>
          <AccordionContent class="px-4 pb-4">
            <div class="max-h-[250px] overflow-y-auto pr-2 custom-scrollbar">
              <div
                v-if="loadingPosts"
                class="flex justify-center py-4"
              >
                <Loader2 class="w-5 h-5 animate-spin text-muted-foreground" />
              </div>
              <draggable
                v-else
                :list="posts"
                :group="{ name: 'menu', pull: 'clone', put: false }"
                :clone="(item: unknown) => createItem('post', item)"
                item-key="id"
                class="space-y-2"
              >
                <template #item="{ element }">
                  <SourceItem 
                    :item="element" 
                    :type="'post'"
                    @add="addItem('post', element)"
                  />
                </template>
              </draggable>
              <div
                v-if="!loadingPosts && posts.length === 0"
                class="text-center py-4 text-muted-foreground text-xs"
              >
                {{ t('layout.menus.form.noPosts') }}
              </div>
            </div>
          </AccordionContent>
        </AccordionItem>

        <!-- Categories -->
        <AccordionItem value="categories">
          <AccordionTrigger class="px-4 py-3 hover:no-underline hover:bg-muted/50">
            <div class="flex items-center gap-2 flex-1">
              <Tag class="w-4 h-4 text-purple-500" />
              <span>{{ t('layout.menus.form.types.category') }}</span>
              <Badge
                variant="secondary"
                class="ml-2"
              >
                {{ categories.length }}
              </Badge>
              <Button 
                size="icon" 
                variant="ghost" 
                class="ml-auto h-6 w-6" 
                :aria-label="t('layout.menus.actions.addAll')"
                :title="t('layout.menus.actions.addAll')" 
                @click.stop="addAll('category', categories)"
              >
                <Plus class="w-3 h-3" />
              </Button>
            </div>
          </AccordionTrigger>
          <AccordionContent class="px-4 pb-4">
            <div class="max-h-[250px] overflow-y-auto pr-2 custom-scrollbar">
              <div
                v-if="loadingCategories"
                class="flex justify-center py-4"
              >
                <Loader2 class="w-5 h-5 animate-spin text-muted-foreground" />
              </div>
              <draggable
                v-else
                :list="categories"
                :group="{ name: 'menu', pull: 'clone', put: false }"
                :clone="(item: unknown) => createItem('category', item)"
                item-key="id"
                class="space-y-2"
              >
                <template #item="{ element }">
                  <SourceItem 
                    :item="element" 
                    :type="'category'"
                    @add="addItem('category', element)"
                  />
                </template>
              </draggable>
              <div
                v-if="!loadingCategories && categories.length === 0"
                class="text-center py-4 text-muted-foreground text-xs"
              >
                {{ t('layout.menus.form.noCategories') }}
              </div>
            </div>
          </AccordionContent>
        </AccordionItem>

        <!-- Data Models -->
        <AccordionItem value="models">
          <AccordionTrigger class="px-4 py-3 hover:no-underline hover:bg-muted/50">
            <div class="flex items-center gap-2 flex-1">
              <Database class="w-4 h-4 text-cyan-500" />
              <span>Data Models</span>
              <Badge
                variant="secondary"
                class="ml-2"
              >
                {{ models.length }}
              </Badge>
              <Button 
                size="icon" 
                variant="ghost" 
                class="ml-auto h-6 w-6" 
                aria-label="Add all data models"
                title="Add all data models" 
                @click.stop="addAll('model', models)"
              >
                <Plus class="w-3 h-3" />
              </Button>
            </div>
          </AccordionTrigger>
          <AccordionContent class="px-4 pb-4">
            <div class="max-h-[250px] overflow-y-auto pr-2 custom-scrollbar">
              <div
                v-if="loadingModels"
                class="flex justify-center py-4"
              >
                <Loader2 class="w-5 h-5 animate-spin text-muted-foreground" />
              </div>
              <draggable
                v-else
                :list="models"
                :group="{ name: 'menu', pull: 'clone', put: false }"
                :clone="(item: unknown) => createItem('model', item)"
                item-key="id"
                class="space-y-2"
              >
                <template #item="{ element }">
                  <SourceItem 
                    :item="element" 
                    :type="'model'"
                    @add="addItem('model', element)"
                  />
                </template>
              </draggable>
              <div
                v-if="!loadingModels && models.length === 0"
                class="text-center py-4 text-muted-foreground text-xs"
              >
                Tidak ada data model aktif
              </div>
            </div>
          </AccordionContent>
        </AccordionItem>

        <!-- Custom Link -->
        <AccordionItem value="custom">
          <AccordionTrigger class="px-4 py-3 hover:no-underline hover:bg-muted/50">
            <div class="flex items-center gap-2">
              <LinkIcon class="w-4 h-4 text-emerald-500" />
              <span>{{ t('layout.menus.form.customLink') }}</span>
            </div>
          </AccordionTrigger>
          <AccordionContent class="px-4 pb-4 pt-2">
            <div class="space-y-3">
              <div class="space-y-1.5">
                <Label class="text-xs">{{ t('layout.menus.form.linkText') }}</Label>
                <Input
                  v-model="customLink.title"
                  class="h-8"
                  :placeholder="t('layout.menus.form.labelPlaceholder')"
                />
              </div>
              <div class="space-y-1.5">
                <Label class="text-xs">{{ t('layout.menus.form.url') }}</Label>
                <Input
                  v-model="customLink.url"
                  class="h-8"
                  :placeholder="t('common.placeholders.urlShort')"
                />
              </div>
              <Button
                size="sm"
                class="w-full mt-2"
                :disabled="!customLink.title"
                @click="addCustomLink"
              >
                <PlusCircle class="w-3.5 h-3.5 mr-2" />
                <PlusCircle v-if="!customLink.title && 0" /> <!-- Fake usage to suppress icon unused warning if any -->
                {{ t('layout.menus.actions.addToMenu') }}
              </Button>
            </div>
          </AccordionContent>
        </AccordionItem>

        <!-- Column Group -->
        <AccordionItem value="structure">
          <AccordionTrigger class="px-4 py-3 hover:no-underline hover:bg-muted/50">
            <div class="flex items-center gap-2">
              <Columns class="w-4 h-4 text-indigo-500" />
              <span>{{ t('layout.menus.form.structure') }}</span>
            </div>
          </AccordionTrigger>
          <AccordionContent class="px-4 pb-4 pt-2">
            <Button 
              size="sm" 
              variant="outline" 
              class="w-full border-dashed"
              @click.prevent="addColumnGroup"
            >
              <Columns class="w-3.5 h-3.5 mr-2" />
              {{ t('layout.menus.form.addColumnGroup') }}
            </Button>
          </AccordionContent>
        </AccordionItem>
      </Accordion>
    </CardContent>
  </Card>
</template>

<script setup lang="ts">
import { logger } from '@/shared/utils/logger';
import { ref, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import draggable from 'vuedraggable';
import api from '@/engine/api/client';
import { parseResponse, ensureArray } from '@/shared/utils/responseParser';
import { useMenuContext } from '@/modules/Content/Layout/composables/useMenu';
import { menuItemRegistry } from '../registry';

// UI Components
import Card from '@/shared/components/ui/Card.vue';
import CardHeader from '@/shared/components/ui/CardHeader.vue';
import CardTitle from '@/shared/components/ui/CardTitle.vue';
import CardContent from '@/shared/components/ui/CardContent.vue';
import Accordion from '@/shared/components/ui/Accordion.vue';
import AccordionContent from '@/shared/components/ui/AccordionContent.vue';
import AccordionItem from '@/shared/components/ui/AccordionItem.vue';
import AccordionTrigger from '@/shared/components/ui/AccordionTrigger.vue';
import Badge from '@/shared/components/ui/Badge.vue';
import Button from '@/shared/components/ui/Button.vue';
import Input from '@/shared/components/ui/Input.vue';
import Label from '@/shared/components/ui/Label.vue';
import SourceItem from './SourceItem.vue';

import {
  Columns,
  Database,
  File,
  FileText,
  LinkIcon,
  Loader2,
  PanelLeftClose,
  Plus,
  PlusCircle,
  Tag,
} from 'lucide-vue-next';

const { t } = useI18n();
const menuContext = useMenuContext();

defineEmits<{
    (e: 'collapse'): void;
}>();

// Data sources
const pages = ref<unknown[]>([]);
const posts = ref<unknown[]>([]);
const categories = ref<unknown[]>([]);
const models = ref<unknown[]>([]);
const loadingPages = ref(false);
const loadingPosts = ref(false);
const loadingCategories = ref(false);
const loadingModels = ref(false);

// Custom link form
const customLink = ref({ title: '', url: 'https://' });

// Fetch data sources
const fetchPages = async () => {
    loadingPages.value = true;
    try {
        const response = await api.get('/manage/publishing/contents?type=page&status=published');
        const { data } = parseResponse(response);
        pages.value = ensureArray(data);
    } catch (error) {
        logger.error('Failed to fetch pages:', error);
    } finally {
        loadingPages.value = false;
    }
};

const fetchPosts = async () => {
    loadingPosts.value = true;
    try {
        const response = await api.get('/manage/publishing/contents?type=post&status=published');
        const { data } = parseResponse(response);
        posts.value = ensureArray(data);
    } catch (error) {
        logger.error('Failed to fetch posts:', error);
    } finally {
        loadingPosts.value = false;
    }
};

const fetchCategories = async () => {
    loadingCategories.value = true;
    try {
        const response = await api.get('/manage/library/categories');
        const { data } = parseResponse(response);
        categories.value = ensureArray(data);
    } catch (error) {
        logger.error('Failed to fetch categories:', error);
    } finally {
        loadingCategories.value = false;
    }
};

const fetchModels = async () => {
    loadingModels.value = true;
    try {
        const response = await api.get('/manage/infra/models/types');
        const { data } = parseResponse(response);
        const list = ensureArray(data);
        models.value = list.map((m: any) => ({
            id: m.id,
            name: m.name || m.title || m.slug,
            slug: m.slug,
            url: `/dynamic/${m.slug}`
        }));
    } catch (error) {
        logger.error('Failed to fetch data models for menu:', error);
    } finally {
        loadingModels.value = false;
    }
};

// Create menu item from source
const createItem = (type: string, sourceItem: unknown) => {
    const si = sourceItem as Record<string, unknown>;
    const targetType = type === 'model' ? 'custom' : type;
    const item = menuItemRegistry.createInstance(targetType, {
        title: (si.title as string) || (si.name as string),
        target_id: String(si.id),
        url: (si.url as string) || (type === 'model' && si.slug ? `/dynamic/${si.slug}` : undefined)
    });
    return item;
};

// Add single item
const addItem = (type: string, sourceItem: unknown) => {
    const item = createItem(type, sourceItem);
    if (item) {
        menuContext.addItem(item);
    }
};

// Add all items of a type
const addAll = (type: string, items: unknown[]) => {
    items.forEach(sourceItem => {
        addItem(type, sourceItem);
    });
};

// Add custom link
const addCustomLink = () => {
    if (!customLink.value.title) return;
    
    const item = menuItemRegistry.createInstance('custom', {
        title: customLink.value.title,
        url: customLink.value.url || '#'
    });
    
    if (item) {
        menuContext.addItem(item);
        customLink.value = { title: '', url: 'https://' };
    }
};

// Add column group
const addColumnGroup = () => {
    const item = menuItemRegistry.createInstance('column_group', {
        title: t('layout.menus.form.newColumnGroup')
    });
    if (item) {
        menuContext.addItem(item);
    }
};

onMounted(() => {
    fetchPages();
    fetchPosts();
    fetchCategories();
    fetchModels();
});
</script>

<style scoped>
.custom-scrollbar {
    scrollbar-width: thin;
    scrollbar-color: hsl(var(--border)) transparent;
}
.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background-color: hsl(var(--border));
    border-radius: 4px;
}
</style>
