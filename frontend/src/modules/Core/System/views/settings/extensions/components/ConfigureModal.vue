<template>
  <Dialog :open="open" @update:open="$emit('update:open', $event)">
    <DialogContent class="console-dialog-md sm:max-w-xl bg-card border border-border/80 rounded-2xl max-h-[90vh] overflow-y-auto">
      <DialogHeader>
        <DialogTitle class="flex items-center gap-2">
          <Settings class="w-5 h-5 text-indigo-500" />
          {{ t('system.appStore.configTitle') }} — {{ activeExtConfig?.name }}
        </DialogTitle>
        <DialogDescription class="sr-only">
          {{ t('system.appStore.configDesc') }}
        </DialogDescription>
      </DialogHeader>

      <!-- DEDICATED VISUAL FORM FOR INSTAGRAM FEED PLUGIN -->
      <div v-if="activeExtConfig?.slug === 'instagram-feed'" class="mt-4 space-y-5">
        <div class="flex items-center justify-between pb-2 border-b border-border">
          <span class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">
            {{ t('plugin.instagram.configFormTitle') }}
          </span>
          <button
            type="button"
            class="text-xs text-indigo-500 hover:text-indigo-600 font-medium"
            @click="showRawJson = !showRawJson"
          >
            {{ showRawJson ? t('plugin.instagram.showVisualForm') : t('plugin.instagram.showRawJson') }}
          </button>
        </div>

        <div v-if="!showRawJson" class="space-y-4">
          <!-- INSTAGRAM USERNAME -->
          <div class="space-y-1.5">
            <label class="text-xs font-semibold text-foreground flex items-center gap-1.5">
              <span>{{ t('plugin.instagram.fieldUsername') }}</span>
              <span class="text-red-500">*</span>
            </label>
            <div class="relative">
              <span class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground text-sm font-semibold">@</span>
              <input
                v-model="igForm.instagram_username"
                type="text"
                class="w-full bg-background border border-border rounded-lg pl-8 pr-3 py-2 text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-indigo-500/50"
                :placeholder="t('plugin.instagram.placeholderUsername')"
                @input="syncToJson"
              />
            </div>
            <p class="text-[11px] text-muted-foreground">
              {{ t('plugin.instagram.helpUsername') }}
            </p>
          </div>

          <!-- ACCESS TOKEN -->
          <div class="space-y-1.5">
            <label class="text-xs font-semibold text-foreground flex items-center gap-1.5">
              <span>{{ t('plugin.instagram.fieldAccessToken') }}</span>
              <span class="text-red-500">*</span>
            </label>
            <div class="relative">
              <input
                v-model="igForm.access_token"
                :type="showToken ? 'text' : 'password'"
                class="w-full bg-background border border-border rounded-lg pl-3 pr-10 py-2 text-sm font-mono text-foreground focus:outline-none focus:ring-2 focus:ring-indigo-500/50"
                :placeholder="t('plugin.instagram.placeholderAccessToken')"
                @input="syncToJson"
              />
              <button
                type="button"
                class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground"
                @click="showToken = !showToken"
              >
                <EyeOff v-if="showToken" class="w-4 h-4" />
                <Eye v-else class="w-4 h-4" />
              </button>
            </div>
            <p class="text-[11px] text-muted-foreground">
              {{ t('plugin.instagram.helpAccessToken') }}
            </p>
          </div>

          <!-- TEST CONNECTION BUTTON & STATUS -->
          <div class="p-3.5 rounded-xl bg-muted/40 border border-border/60 space-y-2.5">
            <div class="flex items-center justify-between">
              <span class="text-xs font-semibold text-foreground flex items-center gap-1.5">
                <CheckCircle2 v-if="testStatus === 'success'" class="w-4 h-4 text-emerald-500" />
                <AlertTriangle v-else-if="testStatus === 'error'" class="w-4 h-4 text-red-500" />
                <Radio v-else class="w-4 h-4 text-indigo-500" />
                <span>{{ t('plugin.instagram.testConnectionTitle') }}</span>
              </span>
              <Button
                type="button"
                variant="outline"
                size="sm"
                class="text-xs h-8"
                :disabled="testingConnection || !igForm.access_token"
                @click="testConnection"
              >
                <Loader2 v-if="testingConnection" class="w-3.5 h-3.5 animate-spin mr-1.5" />
                <span>{{ testingConnection ? t('plugin.instagram.testing') : t('plugin.instagram.testButton') }}</span>
              </Button>
            </div>

            <p v-if="testMessage" class="text-xs leading-relaxed" :class="testStatus === 'success' ? 'text-emerald-600 dark:text-emerald-400 font-medium' : 'text-red-600 dark:text-red-400'">
              {{ testMessage }}
            </p>
          </div>

          <!-- THEME SLOTS -->
          <div class="space-y-2">
            <label class="text-xs font-semibold text-foreground">
              {{ t('plugin.instagram.fieldSlots') }}
            </label>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
              <label class="flex items-center gap-2.5 p-2.5 rounded-lg border border-border bg-background cursor-pointer hover:bg-muted/40 transition-colors">
                <input
                  type="checkbox"
                  :checked="hasSlot('after_hero')"
                  class="rounded border-border text-indigo-600 focus:ring-indigo-500"
                  @change="toggleSlot('after_hero', ($event.target as HTMLInputElement).checked)"
                />
                <span class="text-xs text-foreground font-medium">{{ t('plugin.instagram.slotAfterHero') }}</span>
              </label>

              <label class="flex items-center gap-2.5 p-2.5 rounded-lg border border-border bg-background cursor-pointer hover:bg-muted/40 transition-colors">
                <input
                  type="checkbox"
                  :checked="hasSlot('before_footer')"
                  class="rounded border-border text-indigo-600 focus:ring-indigo-500"
                  @change="toggleSlot('before_footer', ($event.target as HTMLInputElement).checked)"
                />
                <span class="text-xs text-foreground font-medium">{{ t('plugin.instagram.slotBeforeFooter') }}</span>
              </label>
            </div>
            <p class="text-[11px] text-muted-foreground">
              {{ t('plugin.instagram.helpSlots') }}
            </p>
          </div>

          <!-- LAYOUT & LIMIT OPTIONS -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="space-y-1.5">
              <label class="text-xs font-semibold text-foreground">{{ t('plugin.instagram.fieldLayout') }}</label>
              <select
                v-model="igForm.layout_variant"
                class="w-full bg-background border border-border rounded-lg px-3 py-2 text-xs text-foreground focus:outline-none focus:ring-2 focus:ring-indigo-500/50"
                @change="syncToJson"
              >
                <option value="bento">{{ t('plugin.instagram.layoutBento') }}</option>
                <option value="grid">{{ t('plugin.instagram.layoutGrid') }}</option>
                <option value="carousel">{{ t('plugin.instagram.layoutCarousel') }}</option>
              </select>
            </div>

            <div class="space-y-1.5">
              <label class="text-xs font-semibold text-foreground">{{ t('plugin.instagram.fieldLimit') }}</label>
              <select
                v-model.number="igForm.post_limit"
                class="w-full bg-background border border-border rounded-lg px-3 py-2 text-xs text-foreground focus:outline-none focus:ring-2 focus:ring-indigo-500/50"
                @change="syncToJson"
              >
                <option :value="4">4 {{ t('plugin.instagram.posts') }}</option>
                <option :value="6">6 {{ t('plugin.instagram.posts') }}</option>
                <option :value="8">8 {{ t('plugin.instagram.posts') }} ({{ t('plugin.instagram.recommended') }})</option>
                <option :value="12">12 {{ t('plugin.instagram.posts') }}</option>
              </select>
            </div>
          </div>

          <!-- DISPLAY TOGGLES -->
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 pt-1">
            <label class="flex items-center gap-2 text-xs text-foreground cursor-pointer">
              <input
                v-model="igForm.show_likes_count"
                type="checkbox"
                class="rounded border-border text-indigo-600 focus:ring-indigo-500"
                @change="syncToJson"
              />
              <span>{{ t('plugin.instagram.showLikes') }}</span>
            </label>

            <label class="flex items-center gap-2 text-xs text-foreground cursor-pointer">
              <input
                v-model="igForm.show_comments_count"
                type="checkbox"
                class="rounded border-border text-indigo-600 focus:ring-indigo-500"
                @change="syncToJson"
              />
              <span>{{ t('plugin.instagram.showComments') }}</span>
            </label>

            <label class="flex items-center gap-2 text-xs text-foreground cursor-pointer">
              <input
                v-model="igForm.enable_lightbox"
                type="checkbox"
                class="rounded border-border text-indigo-600 focus:ring-indigo-500"
                @change="syncToJson"
              />
              <span>{{ t('plugin.instagram.enableLightbox') }}</span>
            </label>
          </div>

          <!-- COMMENT FILTER KEYWORDS -->
          <div class="space-y-1.5 pt-1">
            <label class="text-xs font-semibold text-foreground">{{ t('plugin.instagram.fieldFilterKeywords') }}</label>
            <input
              v-model="igForm.comment_filter_keywords"
              type="text"
              class="w-full bg-background border border-border rounded-lg px-3 py-2 text-xs text-foreground focus:outline-none focus:ring-2 focus:ring-indigo-500/50"
              :placeholder="t('plugin.instagram.placeholderFilterKeywords')"
              @input="syncToJson"
            />
            <p class="text-[11px] text-muted-foreground">
              {{ t('plugin.instagram.helpFilterKeywords') }}
            </p>
          </div>
        </div>

        <!-- RAW JSON VIEW FOR INSTAGRAM (TOGGLED) -->
        <div v-else class="space-y-1">
          <textarea
            :value="rawSettingsJson"
            rows="8"
            class="w-full bg-background border border-border rounded-lg p-3 font-mono text-xs text-foreground focus:outline-none focus:ring-2 focus:ring-indigo-500/50"
            @input="$emit('update:rawSettingsJson', ($event.target as HTMLTextAreaElement).value); parseFromJson(($event.target as HTMLTextAreaElement).value)"
          />
        </div>
      </div>

      <!-- STANDARD RAW JSON VIEW FOR ALL OTHER EXTENSIONS -->
      <div v-else class="mt-4 space-y-4">
        <div class="space-y-1">
          <label class="text-xs text-muted-foreground uppercase font-mono">{{ t('system.appStore.configureModal.settingsSchema') }}</label>
          <p class="text-xs text-muted-foreground mb-4">{{ t('system.appStore.configDesc') }}</p>
          <textarea
            :value="rawSettingsJson"
            rows="6"
            class="w-full bg-background border border-border rounded-lg p-3 font-mono text-xs text-foreground focus:outline-none focus:ring-2 focus:ring-indigo-500/50"
            :placeholder="t('common.placeholders.jsonObject')"
            @input="$emit('update:rawSettingsJson', ($event.target as HTMLTextAreaElement).value)"
          />
        </div>
      </div>

      <DialogFooter class="mt-6 flex items-center justify-end gap-2">
        <Button
          variant="secondary"
          @click="$emit('update:open', false)"
        >
          {{ t('system.appStore.cancel') }}
        </Button>
        <Button
          class="bg-indigo-600 hover:bg-indigo-700 text-white border-0"
          @click="$emit('save')"
        >
          {{ t('system.appStore.saveBtn') }}
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '@/engine/api/client';
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogDescription,
  DialogFooter,
  Button,
} from '@/shared/components/ui';
import {
  Settings,
  Eye,
  EyeOff,
  Radio,
  CheckCircle2,
  AlertTriangle,
  Loader2,
} from 'lucide-vue-next';

interface FeatureItem {
  id: string;
  extension_slug: string;
  slug: string;
  name: string;
  description?: string;
  category: string;
  is_active: boolean;
}

interface ExtensionItem {
  id: string;
  slug: string;
  type: 'module' | 'plugin';
  name: string;
  version: string;
  status: 'active' | 'inactive';
  is_core: boolean;
  author?: string;
  license?: string;
  settings?: Record<string, unknown>;
  features?: FeatureItem[];
}

const { t } = useI18n();

const props = defineProps<{
  open: boolean;
  activeExtConfig: ExtensionItem | null;
  rawSettingsJson: string;
}>();

const emit = defineEmits<{
  (e: 'update:open', val: boolean): void;
  (e: 'update:rawSettingsJson', val: string): void;
  (e: 'save'): void;
}>();

const showRawJson = ref(false);
const showToken = ref(false);
const testingConnection = ref(false);
const testStatus = ref<'idle' | 'success' | 'error'>('idle');
const testMessage = ref('');

interface InstagramFormData {
  access_token: string;
  instagram_username: string;
  instagram_account_id: string;
  theme_blocks: Array<{ slot: string }>;
  cache_ttl_minutes: number;
  post_limit: number;
  layout_variant: string;
  show_likes_count: boolean;
  show_comments_count: boolean;
  enable_lightbox: boolean;
  comment_filter_keywords: string;
}

const igForm = ref<InstagramFormData>({
  access_token: '',
  instagram_username: '',
  instagram_account_id: '',
  theme_blocks: [{ slot: 'after_hero' }],
  cache_ttl_minutes: 60,
  post_limit: 8,
  layout_variant: 'bento',
  show_likes_count: true,
  show_comments_count: true,
  enable_lightbox: true,
  comment_filter_keywords: '',
});

function parseFromJson(jsonStr: string) {
  try {
    const parsed = JSON.parse(jsonStr || '{}');
    igForm.value = {
      access_token: typeof parsed.access_token === 'string' ? parsed.access_token : '',
      instagram_username: typeof parsed.instagram_username === 'string' ? parsed.instagram_username : '',
      instagram_account_id: typeof parsed.instagram_account_id === 'string' ? parsed.instagram_account_id : '',
      theme_blocks: Array.isArray(parsed.theme_blocks) ? parsed.theme_blocks : [{ slot: 'after_hero' }],
      cache_ttl_minutes: typeof parsed.cache_ttl_minutes === 'number' ? parsed.cache_ttl_minutes : 60,
      post_limit: typeof parsed.post_limit === 'number' ? parsed.post_limit : 8,
      layout_variant: typeof parsed.layout_variant === 'string' ? parsed.layout_variant : 'bento',
      show_likes_count: typeof parsed.show_likes_count === 'boolean' ? parsed.show_likes_count : true,
      show_comments_count: typeof parsed.show_comments_count === 'boolean' ? parsed.show_comments_count : true,
      enable_lightbox: typeof parsed.enable_lightbox === 'boolean' ? parsed.enable_lightbox : true,
      comment_filter_keywords: typeof parsed.comment_filter_keywords === 'string' ? parsed.comment_filter_keywords : '',
    };
  } catch {
    // Ignore invalid JSON on typing
  }
}

function syncToJson() {
  emit('update:rawSettingsJson', JSON.stringify(igForm.value, null, 2));
}

function hasSlot(slotName: string): boolean {
  return igForm.value.theme_blocks.some((b) => b.slot === slotName);
}

function toggleSlot(slotName: string, checked: boolean) {
  if (checked) {
    if (!hasSlot(slotName)) {
      igForm.value.theme_blocks.push({ slot: slotName });
    }
  } else {
    igForm.value.theme_blocks = igForm.value.theme_blocks.filter((b) => b.slot !== slotName);
  }
  syncToJson();
}

async function testConnection() {
  if (!igForm.value.access_token) return;
  testingConnection.value = true;
  testStatus.value = 'idle';
  testMessage.value = '';

  try {
    const response = await api.post('/manage/infra/extensions/instagram/test-connection', {
      access_token: igForm.value.access_token,
      instagram_username: igForm.value.instagram_username,
    });

    const res = response.data;
    if (res?.success) {
      testStatus.value = 'success';
      const acc = res.data;
      testMessage.value = t('plugin.instagram.testSuccess', {
        user: acc.username || igForm.value.instagram_username,
        count: acc.media_count || 0,
        type: acc.account_type || 'BUSINESS',
      });
      if (acc.id) {
        igForm.value.instagram_account_id = acc.id;
        syncToJson();
      }
    } else {
      testStatus.value = 'error';
      testMessage.value = res?.message || t('plugin.instagram.testFailed');
    }
  } catch (err: unknown) {
    testStatus.value = 'error';
    const axiosMsg = (err as { response?: { data?: { message?: string } } })?.response?.data?.message;
    testMessage.value = axiosMsg || t('plugin.instagram.testFailed');
  } finally {
    testingConnection.value = false;
  }
}

watch(
  () => props.open,
  (isOpen) => {
    if (isOpen) {
      testStatus.value = 'idle';
      testMessage.value = '';
      showToken.value = false;
      showRawJson.value = false;
      parseFromJson(props.rawSettingsJson);
    }
  }
);
</script>
