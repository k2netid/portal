<template>
  <div class="space-y-6">
    <PageHeader
borderless
      :title="t('system.languages.title')"
    :subtitle="t('system.languages.description')"
    >
    </PageHeader>

    <!-- UI Languages Info Card -->
    <Card class="mb-6">
      <CardContent class="pt-6">
        <div class="flex items-start justify-between">
          <div>
            <h2 class="text-lg font-semibold text-foreground mb-2">
              {{ $t('system.languages.uiLanguages.title') }}
            </h2>
            <p class="text-sm text-muted-foreground mb-4">
              {{ $t('system.languages.uiLanguages.description') }}
            </p>
            <div class="flex flex-wrap gap-2">
              <Badge 
                v-for="locale in availableUiLocales" 
                :key="locale.code"
                :variant="currentLocale === locale.code ? 'default' : 'secondary'"
                class="px-3 py-1 font-medium"
              >
                <span class="mr-1.5">{{ locale.flag }}</span>
                {{ locale.name }}
                <span
                  v-if="currentLocale === locale.code"
                  class="ml-1.5 text-xs text-primary-foreground/80"
                >({{ $t('system.languages.uiLanguages.active') }})</span>
              </Badge>
            </div>
          </div>
          <div class="text-right">
            <p class="text-xs font-semibold text-foreground/80 mb-1">
              {{ $t('system.languages.uiLanguages.browserDetected') }}
            </p>
            <Badge
              variant="outline"
              class="font-mono"
            >
              {{ browserLocale || t('common.labels.emptyCell') }}
            </Badge>
          </div>
        </div>
      </CardContent>
    </Card>

    <!-- Languages Table -->
    <ConsoleListCard>
      <div
        v-if="loading"
        class="flex flex-col items-center justify-center py-12"
      >
        <Loader2 class="w-8 h-8 text-muted-foreground mb-2" />
        <p class="text-muted-foreground">
          {{ $t('common.messages.loading.default') }}
        </p>
      </div>
      <div
        v-else-if="languages.length === 0"
        class="flex flex-col items-center justify-center py-12"
      >
        <LanguagesIcon class="w-12 h-12 text-muted-foreground/20 mb-4" />
        <p class="text-muted-foreground">
          {{ $t('system.languages.list.empty') }}
        </p>
      </div>
      <Table v-else>
        <TableHeader>
          <TableRow>
            <TableHead>{{ $t('system.languages.list.headers.name') }}</TableHead>
            <TableHead>{{ $t('system.languages.list.headers.code') }}</TableHead>
            <TableHead>{{ $t('system.languages.list.headers.default') }}</TableHead>
            <TableHead>{{ $t('system.languages.uiLanguages.translationKeys') }}</TableHead>
            <TableHead class="text-right">
              {{ $t('system.languages.list.headers.actions') }}
            </TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          <TableRow
            v-for="lang in languages"
            :key="lang.id"
          >
            <TableCell>
              <div class="flex items-center font-medium">
                <span class="text-lg mr-3">{{ getLanguageFlag(lang.code) }}</span>
                <span class="text-sm">{{ lang.name }}</span>
              </div>
            </TableCell>
            <TableCell>
              <code class="text-xs bg-muted px-1.5 py-0.5 rounded border border-border">
                {{ lang.code }}
              </code>
            </TableCell>
            <TableCell>
              <Badge
                v-if="lang.is_default"
                variant="default"
>
                {{ $t('system.languages.list.default') }}
              </Badge>
            </TableCell>
            <TableCell>
              <Badge
                v-if="lang.has_ui_translations"
                variant="outline"
                class="font-normal"
              >
                {{ lang.translation_keys }} {{ $t('system.languages.uiLanguages.keys') }}
              </Badge>
              <span
                v-else
                class="text-muted-foreground text-xs"
              >-</span>
            </TableCell>
            <TableCell class="text-right">
              <div class="flex justify-end gap-2">
                <Button 
                  v-if="lang.has_ui_translations" 
                  variant="outline"
                  size="sm"
          class="h-10 inline-flex items-center gap-2"
                  :disabled="exporting === lang.id" 
                  @click="exportPack(lang)"
                >
                  <Loader2
                    v-if="exporting === lang.id"
                    class="w-4 h-4 mr-2"
                  />
                  <Download
                    v-else
                    class="w-4 h-4 mr-2"
                  />
                  {{ $t('system.languages.actions.export') }}
                </Button>
                <Button 
                  v-if="!lang.is_default"
                  variant="outline"
                  size="sm"
          class="h-10 inline-flex items-center gap-2"
                  @click="setDefault(lang)"
                >
                  <CheckCircle2 class="w-4 h-4 mr-2" />
                  {{ $t('system.languages.actions.setDefault') }}
                </Button>
                <Button 
                  v-if="!lang.is_default"
                  variant="outline"
                  size="sm"
          class="h-10 inline-flex items-center gap-2 text-red-800 border-red-200 hover:bg-red-50"
                  @click="deleteLanguage(lang)"
                >
                  <Trash2 class="w-4 h-4 mr-2" />
                  {{ $t('system.languages.actions.delete') }}
                </Button>
              </div>
            </TableCell>
          </TableRow>
        </TableBody>
      </Table>
    </ConsoleListCard>

    <!-- Import Modal -->
    <Dialog
      :open="showImportModal"
      @update:open="showImportModal = $event"
    >
      <DialogContent class="console-dialog-sm">
        <DialogHeader>
          <DialogTitle>{{ $t('system.languages.import.title') }}</DialogTitle>
          <DialogDescription>{{ $t('system.languages.import.description') }}</DialogDescription>
        </DialogHeader>

        <div class="space-y-4 py-4">
          <div class="space-y-2">
            <Label>{{ $t('system.languages.import.file') }}</Label>
            <Input 
              type="file" 
              accept=".zip"
              class="cursor-pointer"
              @change="handleFileSelect"
            />
          </div>
        </div>

        <DialogFooter>
          <Button
            variant="outline"
            @click="showImportModal = false"
          >
            {{ $t('common.actions.cancel') }}
          </Button>
          <Button 
            :disabled="!selectedFile || importing" 
            @click="importPack"
          >
            <Loader2
              v-if="importing"
              class="w-4 h-4 mr-2"
            />
            {{ importing ? $t('common.messages.loading.default') : $t('system.languages.import.button') }}
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>

    <!-- Create Modal -->
    <Dialog
      :open="showCreateModal"
      @update:open="showCreateModal = $event"
    >
      <DialogContent class="console-dialog-sm">
        <DialogHeader>
          <DialogTitle>{{ $t('system.languages.create.title') }}</DialogTitle>
        </DialogHeader>

        <div class="space-y-4 py-4">
          <div class="space-y-2">
            <Label>{{ $t('system.languages.create.code') }}</Label>
            <Input 
              v-model="form.code"
              type="text" 
              :placeholder="t('system.languages.create.codePlaceholder')"
            />
            <span
              v-if="errors.code"
              class="text-xs text-destructive"
            >{{ errors.code[0] }}</span>
          </div>

          <div class="space-y-2">
            <Label>{{ $t('system.languages.create.name') }}</Label>
            <Input 
              v-model="form.name"
              type="text" 
              :placeholder="t('system.languages.create.namePlaceholder')"
            />
            <span
              v-if="errors.name"
              class="text-xs text-destructive"
            >{{ errors.name[0] }}</span>
          </div>

          <div class="flex items-center space-x-2 pt-2">
            <Checkbox
              id="createFromTemplate"
              v-model:checked="form.create_from_template"
            />
            <Label
              for="createFromTemplate"
              class="text-sm font-normal cursor-pointer"
            >
              {{ $t('system.languages.create.fromTemplate') }}
            </Label>
          </div>
        </div>

        <DialogFooter>
          <Button
            variant="outline"
            @click="showCreateModal = false"
          >
            {{ $t('common.actions.cancel') }}
          </Button>
          <Button 
            :disabled="creating || !isValid" 
            @click="createLanguage"
          >
            <Loader2
              v-if="creating"
              class="w-4 h-4 mr-2"
            />
            {{ creating ? $t('common.messages.loading.default') : $t('common.actions.create') }}
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </div>
</template>

<script setup lang="ts">
import { PageHeader, ConsoleListCard } from '@/shared/components/shell';

import { logger } from '@/shared/utils/logger';
import { ref, onMounted, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '@/engine/api/client';
import { useToast } from '@/shared/composables/useToast';
import { useConfirm } from '@/shared/composables/useConfirm';
import { useFormValidation } from '@/shared/composables/useFormValidation';
import { languageSchema } from '@/shared/schemas/common';
import { Badge, Button, Card, CardContent, Checkbox, Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, Input, Label, Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/shared/components/ui';

import {
  CheckCircle2,
  Download,
  LanguagesIcon,
  Loader2,
  Trash2} from 'lucide-vue-next';
import { parseResponse, ensureArray } from '@/shared/utils/responseParser';
import { getLocale, getAvailableLocales, getBrowserLocale } from '@/engine/i18n';
interface Language {
    id: string;
    code: string;
    name: string;
    is_default: boolean;
    has_ui_translations?: boolean;
    translation_keys?: number;
}

const { t } = useI18n();
const { confirm } = useConfirm();
const toast = useToast();

const languages = ref<Language[]>([]);
const loading = ref(false);
const showCreateModal = ref(false);
const showImportModal = ref(false);
const creating = ref(false);
const importing = ref(false);
const exporting = ref<string | null>(null);
const selectedFile = ref<File | null>(null);

const { errors, validateWithZod, setErrors, clearErrors } = useFormValidation(languageSchema);

const form = ref({
    code: '',
    name: '',
    create_from_template: true});

const isValid = computed(() => {
    return !!form.value.code?.trim() && !!form.value.name?.trim();
});

// UI Locale info
const currentLocale = ref(getLocale());
const availableUiLocales = getAvailableLocales();
const browserLocale = getBrowserLocale();

const fetchLanguages = async () => {
    loading.value = true;
    try {
        const response = await api.get('/manage/system/languages');
        const { data } = parseResponse(response);
        languages.value = ensureArray(data);
    } catch (error: unknown) {
        logger.error('Failed to fetch languages:', error);
        languages.value = [];
    } finally {
        loading.value = false;
    }
};

const setDefault = async (lang: Language) => {
    try {
        await api.post(`/manage/system/languages/${lang.id}/set-default`);
        await fetchLanguages();
        toast.success.action(t('system.languages.messages.set_default_success'));
    } catch (error: unknown) {
        toast.error.fromResponse(error as Record<string, unknown>);
    }
};

const deleteLanguage = async (lang: Language) => {
    const confirmed = await confirm({
        title: t('system.languages.actions.delete'),
        message: t('system.languages.actions.confirmDelete', { name: lang.name }),
        variant: 'danger',
        confirmText: t('common.actions.delete')});

    if (!confirmed) return;

    try {
        await api.delete(`/manage/system/languages/${lang.id}`);
        await fetchLanguages();
        toast.success.delete(t('system.navigation.menu.languages'));
    } catch (error: unknown) {
        toast.error.fromResponse(error as Record<string, unknown>);
    }
};

const createLanguage = async () => {
    if (!validateWithZod(form.value)) return;
    
    creating.value = true;
    clearErrors();
    try {
        await api.post('/manage/system/languages', {
            code: form.value.code,
            name: form.value.name,
            create_from_template: form.value.create_from_template,
            template_locale: 'en'});
        showCreateModal.value = false;
        form.value = { code: '', name: '', create_from_template: true };
        await fetchLanguages();
        toast.success.create(t('system.languages.title'));
    } catch (err: unknown) {
        const error = err as { response?: { status?: number; data?: { errors?: Record<string, string[]> } } };
        if (error.response?.status === 422 && error.response.data?.errors) {
            setErrors(error.response.data.errors);
        } else {
            toast.error.action(error as Record<string, unknown>);
        }
    } finally {
        creating.value = false;
    }
};

const exportPack = async (lang: Language) => {
    exporting.value = lang.id;
    try {
        const response = await api.get(`/manage/system/languages/${lang.id}/export-pack`, {
            responseType: 'blob'});
        
        // Create download link
        const blob = new Blob([response.data], { type: 'application/zip' });
        const url = window.URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', `language-pack-${lang.code}-${new Date().toISOString().slice(0, 10)}.zip`);
        document.body.appendChild(link);
        link.click();
        link.remove();
        window.URL.revokeObjectURL(url);
        toast.success.action(t('system.languages.messages.export_success'));
    } catch (error: unknown) {
        toast.error.fromResponse(error as Record<string, unknown>);
    } finally {
        exporting.value = null;
    }
};

const handleFileSelect = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        selectedFile.value = target.files[0];
    }
};

const importPack = async () => {
    if (!selectedFile.value) return;
    
    importing.value = true;
    try {
        const formData = new FormData();
        formData.append('file', selectedFile.value);

        await api.post('/manage/system/languages/import-pack', formData, {
            headers: { 'Content-Type': 'multipart/form-data' }});

        showImportModal.value = false;
        selectedFile.value = null;
        await fetchLanguages();
        toast.success.action(t('system.languages.messages.importSuccess'));
    } catch (error: unknown) {
        toast.error.fromResponse(error as Record<string, unknown>);
    } finally {
        importing.value = false;
    }
};

const getLanguageFlag = (code: string) => {
    const flagMap: Record<string, string> = {
        'en': '🇺🇸',
        'id': '🇮🇩',
        'ar': '🇸🇦',
        'he': '🇮🇱',
        'fr': '🇫🇷',
        'de': '🇩🇪',
        'es': '🇪🇸',
        'pt': '🇵🇹',
        'zh': '🇨🇳',
        'ja': '🇯🇵',
        'ko': '🇰🇷',
        'ru': '🇷🇺'};
    return flagMap[code.toLowerCase()] || '🌐';
};

onMounted(() => {
    fetchLanguages();
});
</script>
