<template>
  <Dialog
    :open="isOpen"
    @update:open="v => { if(!v) $emit('close') }"
  >
    <DialogContent class="!p-0 !gap-0 max-w-md h-[480px] max-h-[85vh] flex flex-col overflow-hidden rounded-2xl border border-border/80 bg-card shadow-2xl [&>button[aria-label=Close]]:hidden">
      <!-- Header -->
      <div class="h-12 px-4 bg-muted/40 border-b border-border/40 flex items-center justify-between select-none shrink-0">
        <DialogTitle class="text-xs font-bold text-foreground flex items-center gap-2">
          <Tag class="w-3.5 h-3.5 text-primary" />
          <span>{{ $t('system.mail.manage_labels') }}</span>
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

      <!-- Content -->
      <div class="flex-1 overflow-y-auto p-4 space-y-4 custom-scrollbar min-h-0">
        <!-- Create Label Box -->
        <div class="p-3 rounded-xl bg-muted/30 border border-border/40 space-y-2.5">
          <h4 class="text-[11px] font-bold uppercase tracking-wider text-muted-foreground">
            {{ $t('system.mail.add_label') }}
          </h4>
          <div class="flex items-center gap-2">
            <Input
              v-model="newLabelName"
              placeholder="e.g. Invoices, Clients, VIP"
              class="h-8 text-xs flex-1"
              @keydown.enter="createLabel"
            />
            <Button
              size="sm"
              class="h-8 text-xs font-semibold px-3 gap-1 shadow-xs"
              :disabled="!newLabelName.trim() || saving"
              @click="createLabel"
            >
              <Plus class="w-3.5 h-3.5" />
              <span>{{ $t('system.mail.add') }}</span>
            </Button>
          </div>

          <!-- Color Select Palette -->
          <div class="flex items-center gap-1.5 pt-1">
            <button
              v-for="color in colorPalette"
              :key="color.class"
              type="button"
              :class="[
                'w-5 h-5 rounded-full transition-transform flex items-center justify-center',
                color.class,
                selectedColor === color.class ? 'scale-125 ring-2 ring-primary ring-offset-2 ring-offset-background' : 'opacity-70 hover:opacity-100'
              ]"
              :title="color.name"
              @click="selectedColor = color.class"
            >
              <Check v-if="selectedColor === color.class" class="w-3 h-3 text-white" />
            </button>
          </div>
        </div>

        <!-- Existing Labels List -->
        <div class="space-y-1.5">
          <h4 class="text-[11px] font-bold uppercase tracking-wider text-muted-foreground px-1">
            {{ $t('system.mail.existing_labels') }} ({{ labels.length }})
          </h4>

          <div class="divide-y divide-border/30 rounded-xl border border-border/40 overflow-hidden bg-card">
            <div
              v-for="(label, idx) in labels"
              :key="label.id"
              class="flex items-center justify-between p-2.5 hover:bg-muted/30 transition-colors"
            >
              <div class="flex items-center gap-2.5">
                <span :class="['w-3 h-3 rounded-full shrink-0', label.color]" />
                <span class="text-xs font-medium text-foreground">{{ label.name }}</span>
              </div>

              <Button
                variant="ghost"
                size="icon"
                class="h-6 w-6 text-muted-foreground hover:text-destructive rounded"
                :title="$t('system.mail.delete')"
                @click="removeLabel(idx)"
              >
                <Trash2 class="w-3 h-3" />
              </Button>
            </div>
          </div>
        </div>
      </div>

      <!-- Footer -->
      <div class="h-12 px-4 bg-muted/30 border-t border-border/40 flex items-center justify-end gap-2 shrink-0">
        <Button
          size="sm"
          class="h-7 text-xs font-semibold px-4 shadow-xs"
          @click="$emit('close')"
        >
          Done
        </Button>
      </div>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useToast } from '@/shared/composables/useToast';
import api from '@/engine/api/client';
import {
  Tag,
  X,
  Plus,
  Trash2,
  Check,
} from 'lucide-vue-next';
import {
  Dialog,
  DialogContent,
  DialogTitle,
  Button,
  Input,
} from '@/shared/components/ui';
import type { MailLabel } from '@/modules/Core/System/composables/useMailClient';

const props = defineProps<{
    isOpen: boolean;
    labels: MailLabel[];
}>();

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'update:labels', labels: MailLabel[]): void;
}>();

const toast = useToast();
const newLabelName = ref('');
const selectedColor = ref('bg-blue-500');
const saving = ref(false);

const colorPalette = [
    { name: 'Blue', class: 'bg-blue-500' },
    { name: 'Rose', class: 'bg-rose-500' },
    { name: 'Emerald', class: 'bg-emerald-500' },
    { name: 'Amber', class: 'bg-amber-500' },
    { name: 'Purple', class: 'bg-purple-500' },
    { name: 'Cyan', class: 'bg-cyan-500' },
    { name: 'Indigo', class: 'bg-indigo-500' },
    { name: 'Orange', class: 'bg-orange-500' },
    { name: 'Slate', class: 'bg-slate-500' },
];

const createLabel = async () => {
    if (!newLabelName.value.trim()) return;

    const id = newLabelName.value.toLowerCase().replace(/[^a-z0-9]/g, '-');
    const newLabel: MailLabel = {
        id,
        name: newLabelName.value.trim(),
        color: selectedColor.value,
    };

    const updated = [...props.labels, newLabel];
    emit('update:labels', updated);
    newLabelName.value = '';

    try {
        await api.post('/manage/mail/labels', { labels: updated });
        toast.success.action('Label created successfully');
    } catch {
        // Local state updated
    }
};

const removeLabel = async (index: number) => {
    const updated = [...props.labels];
    updated.splice(index, 1);
    emit('update:labels', updated);

    try {
        await api.post('/manage/mail/labels', { labels: updated });
        toast.success.action('Label removed');
    } catch {
        // Local state updated
    }
};
</script>
