<template>
  <Dialog
    :open="isOpen"
    @update:open="v => { if(!v) $emit('close') }"
  >
    <DialogContent class="!p-0 !gap-0 max-w-2xl h-[600px] max-h-[92vh] flex flex-col overflow-hidden rounded-2xl border border-border/80 bg-card shadow-2xl [&>button[aria-label=Close]]:hidden">
      <!-- Header -->
      <div class="h-12 px-5 bg-muted/40 border-b border-border/40 flex items-center justify-between select-none shrink-0">
        <DialogTitle class="text-sm font-bold text-foreground flex items-center gap-2">
          <Settings class="w-4 h-4 text-primary" />
          <span>{{ $t('system.mail.settings_title') }}</span>
        </DialogTitle>

        <Button
          variant="ghost"
          size="icon"
          class="h-7 w-7 text-muted-foreground hover:text-foreground rounded-lg"
          @click="$emit('close')"
        >
          <X class="w-4 h-4" />
        </Button>
      </div>

      <!-- Navigation Tabs -->
      <div class="flex items-center gap-1 px-5 pt-2.5 border-b border-border/40 shrink-0 bg-background/50 overflow-x-auto custom-scrollbar">
        <button
          v-for="tab in tabs"
          :key="tab.id"
          :class="[
            'px-3 py-2 text-xs font-semibold border-b-2 transition-colors flex items-center gap-1.5 -mb-px shrink-0',
            activeTab === tab.id
              ? 'border-primary text-primary font-bold'
              : 'border-transparent text-muted-foreground hover:text-foreground'
          ]"
          @click="activeTab = tab.id"
        >
          <component :is="tab.icon" class="w-3.5 h-3.5" />
          <span>{{ tab.label }}</span>
        </button>
      </div>

      <!-- Tab Contents (Scrollable) -->
      <div class="flex-1 overflow-y-auto p-5 space-y-4 custom-scrollbar min-h-0">
        <!-- Tab 1: General Preferences & Security -->
        <div v-if="activeTab === 'general'" class="space-y-4">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- Messages per page -->
            <div class="space-y-1.5">
              <label class="text-xs font-bold text-foreground">Messages per Page</label>
              <p class="text-[11px] text-muted-foreground">Default number of emails per page.</p>
              <Select
                :model-value="String(settingsData.per_page)"
                @update:model-value="v => settingsData.per_page = Number(v)"
              >
                <SelectTrigger class="h-8 text-xs">
                  <SelectValue placeholder="Select items" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="10">10 messages per page</SelectItem>
                  <SelectItem value="25">25 messages per page (Default)</SelectItem>
                  <SelectItem value="50">50 messages per page</SelectItem>
                  <SelectItem value="100">100 messages per page</SelectItem>
                </SelectContent>
              </Select>
            </div>

            <!-- Auto-check interval -->
            <div class="space-y-1.5">
              <label class="text-xs font-bold text-foreground">Auto-check Mail Interval</label>
              <p class="text-[11px] text-muted-foreground">Background sync frequency.</p>
              <Select
                :model-value="String(settingsData.auto_check_interval)"
                @update:model-value="v => settingsData.auto_check_interval = Number(v)"
              >
                <SelectTrigger class="h-8 text-xs">
                  <SelectValue placeholder="Select interval" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="1">Every 1 minute (Real-time)</SelectItem>
                  <SelectItem value="5">Every 5 minutes (Recommended)</SelectItem>
                  <SelectItem value="15">Every 15 minutes</SelectItem>
                  <SelectItem value="0">Manual only</SelectItem>
                </SelectContent>
              </Select>
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- Storage Quota Allocation -->
            <div class="space-y-1.5">
              <label class="text-xs font-bold text-foreground">Mail Storage Quota</label>
              <p class="text-[11px] text-muted-foreground">Total mailbox capacity allocated.</p>
              <Select
                :model-value="String(settingsData.storage_quota_gb)"
                @update:model-value="v => settingsData.storage_quota_gb = Number(v)"
              >
                <SelectTrigger class="h-8 text-xs">
                  <SelectValue placeholder="Select quota" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="5">5 GB Storage</SelectItem>
                  <SelectItem value="15">15 GB Storage (Default)</SelectItem>
                  <SelectItem value="30">30 GB Storage</SelectItem>
                  <SelectItem value="50">50 GB Storage</SelectItem>
                  <SelectItem value="100">100 GB Storage</SelectItem>
                </SelectContent>
              </Select>
            </div>

            <!-- Trash Retention -->
            <div class="space-y-1.5">
              <label class="text-xs font-bold text-foreground">Auto-Purge Trash Retention</label>
              <p class="text-[11px] text-muted-foreground">Automatically clean deleted emails.</p>
              <Select
                :model-value="String(settingsData.trash_retention_days)"
                @update:model-value="v => settingsData.trash_retention_days = Number(v)"
              >
                <SelectTrigger class="h-8 text-xs">
                  <SelectValue placeholder="Select retention" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="7">After 7 days</SelectItem>
                  <SelectItem value="14">After 14 days</SelectItem>
                  <SelectItem value="30">After 30 days (Recommended)</SelectItem>
                  <SelectItem value="90">After 90 days</SelectItem>
                  <SelectItem value="0">Never (Manual delete)</SelectItem>
                </SelectContent>
              </Select>
            </div>
          </div>

          <!-- Mark as Read Behavior -->
          <div class="space-y-1.5">
            <label class="text-xs font-bold text-foreground">Mark as Read Behavior</label>
            <p class="text-[11px] text-muted-foreground">When viewing an unread email in message viewer.</p>
            <Select
              :model-value="String(settingsData.auto_read_delay)"
              @update:model-value="v => settingsData.auto_read_delay = Number(v)"
            >
              <SelectTrigger class="h-8 text-xs">
                <SelectValue placeholder="Select behavior" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="0">Immediately upon selection</SelectItem>
                <SelectItem value="3">After 3 seconds delay</SelectItem>
                <SelectItem value="-1">Manual only (do not auto-mark)</SelectItem>
              </SelectContent>
            </Select>
          </div>

          <!-- Security & Privacy: Block Remote Images -->
          <div class="flex items-center justify-between p-3 rounded-xl bg-muted/30 border border-border/40">
            <div>
              <p class="text-xs font-semibold text-foreground flex items-center gap-1.5">
                <ShieldCheck class="w-3.5 h-3.5 text-primary" />
                <span>Privacy Shield: Block Remote Images</span>
              </p>
              <p class="text-[11px] text-muted-foreground">Blocks external tracking pixels and remote image downloads until approved.</p>
            </div>
            <Switch v-model="settingsData.block_remote_images" />
          </div>

          <!-- Sound Notifications -->
          <div class="flex items-center justify-between p-3 rounded-xl bg-muted/30 border border-border/40">
            <div>
              <p class="text-xs font-semibold text-foreground">Sound & Push Notifications</p>
              <p class="text-[11px] text-muted-foreground">Play a subtle chime and show toast alert when new messages arrive.</p>
            </div>
            <Switch v-model="settingsData.sound_notifications" />
          </div>
        </div>

        <!-- Tab 2: Identity & Signature (With Modal Logo Selector) -->
        <div v-if="activeTab === 'signature'" class="space-y-4">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div class="space-y-1.5">
              <label class="text-xs font-bold text-foreground">Company / Organization</label>
              <Input
                v-model="settingsData.signature_company"
                type="text"
                placeholder="e.g. Jejakawan Cloud Technologies"
                class="h-8 text-xs"
              />
            </div>

            <div class="space-y-1.5">
              <label class="text-xs font-bold text-foreground">Reply-To Address</label>
              <Input
                v-model="settingsData.reply_to"
                type="email"
                placeholder="support@company.com"
                class="h-8 text-xs"
              />
            </div>
          </div>

          <!-- Logo Selector (Modal Select & Quick Presets) -->
          <div class="space-y-2 p-3 rounded-xl bg-muted/20 border border-border/60">
            <div class="flex items-center justify-between">
              <label class="text-xs font-bold text-foreground flex items-center gap-1.5">
                <ImageIcon class="w-3.5 h-3.5 text-primary" />
                <span>Signature Brand Logo</span>
              </label>

              <!-- MediaPicker Trigger -->
              <MediaPicker @select="handleMediaSelect">
                <template #trigger="{ open }">
                  <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    class="h-7 text-xs gap-1.5 shadow-xs"
                    @click="open"
                  >
                    <Upload class="w-3 h-3" />
                    <span>Choose from Media Library</span>
                  </Button>
                </template>
              </MediaPicker>
            </div>

            <!-- Logo Quick Presets & Selected State -->
            <div class="flex items-center gap-2 pt-1 flex-wrap">
              <div
                v-if="settingsData.signature_logo"
                class="flex items-center gap-2 p-1.5 pr-3 rounded-lg border border-primary/40 bg-primary/5 text-xs"
              >
                <div class="w-8 h-8 rounded border border-border/60 p-0.5 flex items-center justify-center bg-card shrink-0">
                  <img :src="settingsData.signature_logo" class="max-h-full max-w-full object-contain" alt="Selected Logo">
                </div>
                <span class="text-[11px] font-medium text-foreground truncate max-w-[150px]">Selected Logo</span>
                <button
                  type="button"
                  class="text-muted-foreground hover:text-destructive p-0.5"
                  title="Remove Logo"
                  @click="settingsData.signature_logo = ''"
                >
                  <X class="w-3.5 h-3.5" />
                </button>
              </div>

              <!-- Quick Presets -->
              <div class="flex items-center gap-1.5 text-xs">
                <span class="text-[10px] text-muted-foreground">Presets:</span>
                <button
                  type="button"
                  class="px-2 py-0.5 rounded border border-border/60 bg-muted/40 hover:bg-muted text-[10px] font-semibold transition-colors"
                  @click="settingsData.signature_logo = '/assets/branding/logo.svg'"
                >
                  System Brand
                </button>
                <button
                  type="button"
                  class="px-2 py-0.5 rounded border border-border/60 bg-muted/40 hover:bg-muted text-[10px] font-semibold transition-colors"
                  @click="settingsData.signature_logo = '/favicon.ico'"
                >
                  App Icon
                </button>
              </div>
            </div>
          </div>

          <!-- Signature Text -->
          <div class="space-y-1.5">
            <label class="text-xs font-bold text-foreground">Signature Text</label>
            <Textarea
              v-model="settingsData.signature"
              :rows="3"
              placeholder="Best regards,&#10;Your Name | Lead Engineer&#10;Direct: +62 812-3456-7890"
              class="text-xs rounded-xl resize-none leading-relaxed"
            />
          </div>

          <!-- Live Signature Preview Box -->
          <div class="space-y-1.5 pt-1">
            <label class="text-[11px] font-bold uppercase tracking-wider text-muted-foreground">
              Live Signature Preview
            </label>
            <div class="p-3.5 rounded-xl border border-border/60 bg-muted/20">
              <div class="flex items-center gap-3">
                <div
                  v-if="settingsData.signature_logo"
                  class="w-12 h-12 rounded-xl border border-border/60 p-1 flex items-center justify-center bg-card shrink-0 shadow-xs"
                >
                  <img :src="settingsData.signature_logo" class="max-h-full max-w-full object-contain" alt="Logo">
                </div>
                <div class="text-xs leading-relaxed">
                  <p v-if="settingsData.signature_company" class="font-bold text-foreground">
                    {{ settingsData.signature_company }}
                  </p>
                  <p class="text-muted-foreground whitespace-pre-line text-[11px]">
                    {{ settingsData.signature || 'Your Name | Title\ncontact@example.com' }}
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Tab 3: AI Copilot & Governance Scope (NEW) -->
        <div v-if="activeTab === 'ai'" class="space-y-4">
          <div class="flex items-center justify-between p-3.5 rounded-xl bg-primary/5 border border-primary/20">
            <div>
              <p class="text-xs font-bold text-primary flex items-center gap-1.5">
                <Sparkles class="w-4 h-4 text-amber-500" />
                <span>AI Email Copilot Integration</span>
              </p>
              <p class="text-[11px] text-muted-foreground">Enable LLM-assisted drafting, summarizing, and smart replies in Webmail.</p>
            </div>
            <Switch v-model="settingsData.ai_enabled" />
          </div>

          <!-- Writing Tone Selection -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div class="space-y-1.5">
              <label class="text-xs font-bold text-foreground">Default Writing Tone</label>
              <Select v-model="settingsData.ai_tone">
                <SelectTrigger class="h-8 text-xs">
                  <SelectValue placeholder="Select tone" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="professional">Professional Business</SelectItem>
                  <SelectItem value="friendly">Friendly & Warm</SelectItem>
                  <SelectItem value="concise">Concise & Direct</SelectItem>
                  <SelectItem value="executive">Formal Executive</SelectItem>
                </SelectContent>
              </Select>
            </div>

            <div class="space-y-1.5">
              <label class="text-xs font-bold text-foreground">Preferred AI Engine</label>
              <Select v-model="settingsData.ai_provider">
                <SelectTrigger class="h-8 text-xs">
                  <SelectValue placeholder="Select AI provider" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="system">System Default AI</SelectItem>
                  <SelectItem value="claude">Anthropic Claude</SelectItem>
                  <SelectItem value="gemini">Google Gemini</SelectItem>
                  <SelectItem value="openai">OpenAI GPT</SelectItem>
                  <SelectItem value="grok">xAI Grok</SelectItem>
                  <SelectItem value="deepseek">DeepSeek</SelectItem>
                </SelectContent>
              </Select>
            </div>
          </div>

          <!-- Permitted Contexts (Konteks yang Diizinkan) -->
          <div class="space-y-2 pt-1">
            <h4 class="text-[11px] font-bold uppercase tracking-wider text-muted-foreground">
              Permitted AI Capabilities (Konteks yang Diizinkan)
            </h4>
            <div class="space-y-2 rounded-xl border border-border/40 p-3 bg-muted/20">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-xs font-semibold text-foreground">AI Email Drafting & Polish</p>
                  <p class="text-[10px] text-muted-foreground">Generate drafts and polish business tone in composer.</p>
                </div>
                <Switch v-model="settingsData.ai_scope_drafting" />
              </div>

              <div class="flex items-center justify-between pt-2 border-t border-border/30">
                <div>
                  <p class="text-xs font-semibold text-foreground">Thread Summarization</p>
                  <p class="text-[10px] text-muted-foreground">Summarize long email threads into actionable key points.</p>
                </div>
                <Switch v-model="settingsData.ai_scope_summarize" />
              </div>

              <div class="flex items-center justify-between pt-2 border-t border-border/30">
                <div>
                  <p class="text-xs font-semibold text-foreground">Contextual Smart Replies</p>
                  <p class="text-[10px] text-muted-foreground">Suggest intelligent 1-click quick replies for incoming mail.</p>
                </div>
                <Switch v-model="settingsData.ai_scope_smart_reply" />
              </div>
            </div>
          </div>

          <!-- Safety Boundaries & Guardrails (Batasan & Larangan) -->
          <div class="space-y-2 pt-1">
            <h4 class="text-[11px] font-bold uppercase tracking-wider text-muted-foreground">
              Safety Guardrails & Boundaries (Batasan Keamanan)
            </h4>
            <div class="space-y-2 rounded-xl border border-border/40 p-3 bg-muted/20">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-xs font-semibold text-foreground flex items-center gap-1.5">
                    <ShieldCheck class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" />
                    <span>Human-in-the-Loop Required</span>
                  </p>
                  <p class="text-[10px] text-muted-foreground">AI is strictly prohibited from autonomous dispatch; manual click required.</p>
                </div>
                <Switch v-model="settingsData.ai_guardrail_human_review" />
              </div>

              <div class="flex items-center justify-between pt-2 border-t border-border/30">
                <div>
                  <p class="text-xs font-semibold text-foreground flex items-center gap-1.5">
                    <ShieldCheck class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" />
                    <span>Sensitive Data & PII Sanitization</span>
                  </p>
                  <p class="text-[10px] text-muted-foreground">Automatically redact passwords, tokens, and credit cards before AI prompt.</p>
                </div>
                <Switch v-model="settingsData.ai_guardrail_pii_masking" />
              </div>
            </div>
          </div>
        </div>

        <!-- Tab 4: Out-of-Office / Auto-Reply -->
        <div v-if="activeTab === 'vacation'" class="space-y-4">
          <div class="flex items-center justify-between p-3 rounded-xl bg-muted/30 border border-border/40">
            <div>
              <p class="text-xs font-semibold text-foreground">Enable Vacation Auto-Responder</p>
              <p class="text-[11px] text-muted-foreground">Automatically sends an instant reply message to incoming emails.</p>
            </div>
            <Switch v-model="settingsData.vacation_enabled" />
          </div>

          <div v-if="settingsData.vacation_enabled" class="space-y-3">
            <div class="space-y-1.5">
              <label class="text-xs font-bold text-foreground">Auto-Reply Subject</label>
              <Input
                v-model="settingsData.vacation_subject"
                type="text"
                placeholder="Out of Office Auto-Reply"
                class="h-8 text-xs"
              />
            </div>

            <div class="space-y-1.5">
              <label class="text-xs font-bold text-foreground">Auto-Reply Message</label>
              <Textarea
                v-model="settingsData.vacation_body"
                :rows="4"
                placeholder="Thank you for reaching out. I am currently out of office with limited email access..."
                class="text-xs rounded-xl resize-none leading-relaxed"
              />
            </div>
          </div>
        </div>

        <!-- Tab 5: Server & Transport -->
        <div v-if="activeTab === 'server'" class="space-y-4">
          <div class="p-4 rounded-xl bg-muted/30 border border-border/40 space-y-3">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-2">
                <Server class="w-4 h-4 text-primary" />
                <span class="text-xs font-bold text-foreground">Active Outbound SMTP Server</span>
              </div>
              <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                Connected
              </span>
            </div>
            <p class="text-[11px] text-muted-foreground">
              Outbound mail routing is configured globally in system settings.
            </p>
            <Button
              variant="outline"
              size="sm"
              class="h-7 text-xs gap-1.5"
              @click="goToEmailSettings"
            >
              <ExternalLink class="w-3.5 h-3.5" />
              <span>Open Global Email SMTP Settings</span>
            </Button>
          </div>
        </div>
      </div>

      <!-- Footer -->
      <div class="h-14 px-5 bg-muted/30 border-t border-border/40 flex items-center justify-between shrink-0">
        <span class="text-[11px] text-muted-foreground">Preferences saved to system profile.</span>

        <div class="flex items-center gap-2">
          <Button
            variant="ghost"
            size="sm"
            class="h-8 text-xs"
            @click="$emit('close')"
          >
            Cancel
          </Button>
          <Button
            size="sm"
            class="h-8 gap-1.5 text-xs font-semibold px-4 shadow-xs"
            :disabled="saving"
            @click="saveSettings"
          >
            <Loader2 v-if="saving" class="w-3.5 h-3.5 animate-spin" />
            <Save v-else class="w-3.5 h-3.5" />
            <span>Save Preferences</span>
          </Button>
        </div>
      </div>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useToast } from '@/shared/composables/useToast';
import api from '@/engine/api/client';
import {
  Settings,
  X,
  Sliders,
  PenTool,
  Calendar,
  Server,
  Save,
  Loader2,
  ExternalLink,
  ShieldCheck,
  ImageIcon,
  Sparkles,
  Upload,
} from 'lucide-vue-next';
import {
  Dialog,
  DialogContent,
  DialogTitle,
  Button,
  Input,
  Textarea,
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
  Switch,
} from '@/shared/components/ui';
import MediaPicker from '@/shared/components/ui/MediaPicker.vue';

defineProps<{
    isOpen: boolean;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
}>();

const toast = useToast();
const router = useRouter();
const activeTab = ref<'general' | 'signature' | 'ai' | 'vacation' | 'server'>('general');
const saving = ref(false);

const tabs = [
    { id: 'general' as const, label: 'Preferences', icon: Sliders },
    { id: 'signature' as const, label: 'Signature & Logo', icon: PenTool },
    { id: 'ai' as const, label: 'AI Copilot & Scope', icon: Sparkles },
    { id: 'vacation' as const, label: 'Auto-Reply', icon: Calendar },
    { id: 'server' as const, label: 'Server & Transport', icon: Server },
];

const settingsData = ref({
    per_page: 25,
    storage_quota_gb: 15,
    trash_retention_days: 30,
    auto_check_interval: 5,
    auto_read_delay: 0,
    sound_notifications: true,
    block_remote_images: true,
    signature_company: '',
    signature_logo: '',
    signature: '',
    reply_to: '',
    vacation_enabled: false,
    vacation_subject: 'Out of Office Auto-Reply',
    vacation_body: 'Thank you for your message. I am currently away from my desk.',
    // AI Governance
    ai_enabled: true,
    ai_provider: 'system',
    ai_tone: 'professional',
    ai_scope_drafting: true,
    ai_scope_summarize: true,
    ai_scope_smart_reply: true,
    ai_scope_sentiment: true,
    ai_guardrail_human_review: true,
    ai_guardrail_pii_masking: true,
});

const handleMediaSelect = (media: any) => {
    if (media?.url) {
        settingsData.value.signature_logo = media.url;
    } else if (media?.path) {
        settingsData.value.signature_logo = `/storage/${media.path.replace(/^\//, '')}`;
    }
};

const loadSettings = async () => {
    try {
        const response = await api.get('/manage/mail/settings');
        const data = response.data?.data || response.data;
        if (data) {
            settingsData.value = { ...settingsData.value, ...data };
        }
    } catch {
        // Fallback to local state
    }
};

const saveSettings = async () => {
    saving.value = true;
    try {
        await api.post('/manage/mail/settings', settingsData.value);
        toast.success.action('Mail preferences saved successfully');
        emit('close');
    } catch (error: unknown) {
        toast.error.fromResponse(error);
    } finally {
        saving.value = false;
    }
};

const goToEmailSettings = () => {
    emit('close');
    router.push({ name: 'settings', query: { tab: 'email' } });
};

onMounted(() => {
    loadSettings();
});
</script>
