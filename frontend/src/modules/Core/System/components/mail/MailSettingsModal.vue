<template>
  <Dialog
    :open="isOpen"
    @update:open="v => { if(!v) $emit('close') }"
  >
    <DialogContent class="!p-0 !gap-0 max-w-2xl h-[560px] max-h-[90vh] flex flex-col overflow-hidden rounded-2xl border border-border/80 bg-card shadow-2xl [&>button[aria-label=Close]]:hidden">
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
      <div class="flex items-center gap-2 px-5 pt-3 border-b border-border/40 shrink-0 bg-background/50">
        <button
          v-for="tab in tabs"
          :key="tab.id"
          :class="[
            'px-3 py-2 text-xs font-semibold border-b-2 transition-colors flex items-center gap-2 -mb-px',
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
        <!-- Tab 1: General Preferences -->
        <div v-if="activeTab === 'general'" class="space-y-4">
          <div class="space-y-1.5">
            <label class="text-xs font-bold text-foreground">Auto-check Mail Interval</label>
            <p class="text-[11px] text-muted-foreground">Frequency to automatically synchronize incoming emails in background.</p>
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

          <div class="flex items-center justify-between p-3 rounded-xl bg-muted/30 border border-border/40">
            <div>
              <p class="text-xs font-semibold text-foreground">Sound & Push Notifications</p>
              <p class="text-[11px] text-muted-foreground">Play a subtle chime and show toast alert when new messages arrive.</p>
            </div>
            <Switch v-model="settingsData.sound_notifications" />
          </div>
        </div>

        <!-- Tab 2: Identity & Signature -->
        <div v-if="activeTab === 'signature'" class="space-y-4">
          <div class="space-y-1.5">
            <label class="text-xs font-bold text-foreground">Reply-To Address</label>
            <p class="text-[11px] text-muted-foreground">Optional custom address where recipient replies will be directed.</p>
            <Input
              v-model="settingsData.reply_to"
              type="email"
              placeholder="support@company.com"
              class="h-8 text-xs"
            />
          </div>

          <div class="space-y-1.5">
            <label class="text-xs font-bold text-foreground">Email Signature</label>
            <p class="text-[11px] text-muted-foreground">Appended automatically to the bottom of all composed outgoing emails.</p>
            <Textarea
              v-model="settingsData.signature"
              :rows="4"
              placeholder="Best regards,&#10;Your Name&#10;Company Title | Jejakawan"
              class="text-xs rounded-xl resize-none leading-relaxed"
            />
          </div>
        </div>

        <!-- Tab 3: Out-of-Office / Auto-Reply -->
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

        <!-- Tab 4: Server & SMTP Status -->
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
        <span class="text-[11px] text-muted-foreground">Preferences saved to user profile.</span>

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

defineProps<{
    isOpen: boolean;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
}>();

const toast = useToast();
const router = useRouter();
const activeTab = ref<'general' | 'signature' | 'vacation' | 'server'>('general');
const saving = ref(false);

const tabs = [
    { id: 'general' as const, label: 'Preferences', icon: Sliders },
    { id: 'signature' as const, label: 'Signature', icon: PenTool },
    { id: 'vacation' as const, label: 'Auto-Reply', icon: Calendar },
    { id: 'server' as const, label: 'Server & Transport', icon: Server },
];

const settingsData = ref({
    auto_check_interval: 5,
    auto_read_delay: 0,
    sound_notifications: true,
    reply_to: '',
    signature: '',
    vacation_enabled: false,
    vacation_subject: 'Out of Office Auto-Reply',
    vacation_body: 'Thank you for your message. I am currently away from my desk.',
});

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
