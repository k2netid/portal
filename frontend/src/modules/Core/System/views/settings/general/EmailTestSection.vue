<template>
  <div class="mt-8 pt-8 border-t border-border">
    <Accordion
      type="single"
      collapsible
      class="w-full"
    >
      <AccordionItem
        value="email-test"
        class="border-b-0"
      >
        <AccordionTrigger class="hover:no-underline py-0">
          <h3 class="text-lg font-medium text-foreground">
            {{ $t('system.settings.emailTest.title') }}
          </h3>
        </AccordionTrigger>
        <AccordionContent class="pt-6">
          <!-- Two Column Layout -->
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- LEFT COLUMN: Send Test Email Form -->
            <div class="p-4 bg-muted rounded-lg">
              <h4 class="text-sm font-medium text-foreground mb-3">
                {{ $t('system.settings.emailTest.sendTest') }}
              </h4>
              <div class="space-y-3">
                <div>
                  <label class="block text-xs font-medium text-foreground mb-1">{{ $t('system.settings.emailTest.recipient') }}</label>
                  <Input
                    :model-value="testEmail.to"
                    type="email"
                    :placeholder="$t('system.settings.emailTest.recipientPlaceholder')"
                    @update:model-value="$emit('update:test-email', { ...testEmail, to: String($event) })"
                  />
                </div>
                <div>
                  <label class="block text-xs font-medium text-foreground mb-1">{{ $t('system.settings.emailTest.subject') }}</label>
                  <Input
                    :model-value="testEmail.subject"
                    type="text"
                    :placeholder="$t('system.settings.emailTest.subjectPlaceholder')"
                    @update:model-value="$emit('update:test-email', { ...testEmail, subject: String($event) })"
                  />
                </div>
                <div>
                  <label class="block text-xs font-medium text-foreground mb-1">{{ $t('system.settings.emailTest.message') }}</label>
                  <Textarea
                    :model-value="testEmail.message"
                    :rows="3"
                    :placeholder="$t('system.settings.emailTest.messagePlaceholder')"
                    @update:model-value="$emit('update:test-email', { ...testEmail, message: String($event) })"
                  />
                </div>
                <Button
                  type="button"
                  :disabled="sendingTestEmail || !testEmail.to"
                  class="w-full"
                  @click="$emit('send-test-email')"
                >
                  {{ sendingTestEmail ? $t('system.settings.emailTest.sending') : $t('system.settings.emailTest.sendTest') }}
                </Button>
                <div
                  v-if="testEmailResult"
                  class="text-sm"
                  :class="testEmailResult.success ? 'text-success' : 'text-destructive'"
                >
                  {{ testEmailResult.message }}
                </div>
              </div>
            </div>

            <!-- RIGHT COLUMN: Queue Status & Email Logs -->
            <div class="space-y-6">
              <!-- Queue Status -->
              <div class="p-4 bg-muted rounded-lg">
                <div class="flex items-center justify-between mb-3">
                  <h4 class="text-sm font-medium text-foreground">
                    {{ $t('system.settings.emailTest.queueStatus') }}
                  </h4>
                  <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    :disabled="loadingQueueStatus"
                    @click="$emit('refresh-queue')"
                  >
                    {{ loadingQueueStatus ? $t('system.settings.loading') : $t('system.settings.emailTest.refresh') }}
                  </Button>
                </div>
                <div
                  v-if="queueStatus"
                  class="text-sm text-muted-foreground space-y-1"
                >
                  <div class="flex justify-between">
                    <span>{{ $t('system.settings.emailTest.driver') }}:</span>
                    <span class="font-medium text-foreground">{{ queueStatus.driver }}</span>
                  </div>
                  <div class="flex justify-between">
                    <span>{{ $t('system.settings.emailTest.pending') }}:</span>
                    <span class="font-medium text-foreground">{{ queueStatus.pending_jobs }}</span>
                  </div>
                  <div class="flex justify-between">
                    <span>{{ $t('system.settings.emailTest.failedJobs') }}:</span>
                    <span
                      class="font-medium"
                      :class="Number(queueStatus.failed_jobs) > 0 ? 'text-destructive' : 'text-foreground'"
                    >{{ queueStatus.failed_jobs }}</span>
                  </div>
                </div>
                <div
                  v-else
                  class="text-sm text-muted-foreground"
                >
                  {{ $t('system.settings.loading') }}
                </div>
              </div>

              <!-- Recent Email Logs -->
              <div class="p-4 bg-muted rounded-lg">
                <div class="flex items-center justify-between mb-3">
                  <h4 class="text-sm font-medium text-foreground">
                    {{ $t('system.settings.emailTest.recentLogs') }}
                  </h4>
                  <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    :disabled="loadingLogs"
                    @click="$emit('refresh-logs')"
                  >
                    {{ loadingLogs ? $t('system.settings.loading') : $t('system.settings.emailTest.refresh') }}
                  </Button>
                </div>
                <div
                  v-if="emailLogs && emailLogs.length > 0"
                  class="space-y-2 max-h-48 overflow-y-auto"
                >
                  <div
                    v-for="log in emailLogs"
                    :key="log.sent_at"
                    class="p-2 bg-card rounded border border-border text-xs"
                  >
                    <div class="flex justify-between items-start gap-2">
                      <div class="min-w-0 flex-1">
                        <p class="font-medium text-foreground truncate">
                          {{ log.to }}
                        </p>
                        <p class="text-muted-foreground truncate">
                          {{ log.subject }}
                        </p>
                      </div>
                      <span class="text-muted-foreground text-xs whitespace-nowrap">{{ formatDate(log.sent_at) }}</span>
                    </div>
                  </div>
                </div>
                <div
                  v-else-if="emailLogs && emailLogs.length === 0"
                  class="text-sm text-muted-foreground"
                >
                  {{ $t('system.settings.emailTest.noLogs') }}
                </div>
              </div>
            </div>
          </div>
        </AccordionContent>
      </AccordionItem>
    </Accordion>
  </div>
</template>

<script setup lang="ts">
import {
    Button,
    Input,
    Textarea,
    Accordion,
    AccordionContent,
    AccordionItem,
    AccordionTrigger
} from '@/shared/components/ui';
import type { QueueStatus, EmailLog } from '@/engine/types/settings';

interface TestEmail {
    to: string;
    subject: string;
    message: string;
}

interface TestEmailResult {
    success: boolean;
    message: string;
}


interface Props {
    sendingTestEmail?: boolean;
    testEmailResult?: TestEmailResult | null;
    testEmail?: TestEmail;
    queueStatus?: QueueStatus | null;
    loadingQueueStatus?: boolean;
    emailLogs?: EmailLog[];
    loadingLogs?: boolean;
}

withDefaults(defineProps<Props>(), {
    sendingTestEmail: false,
    testEmailResult: null,
    testEmail: () => ({ to: '', subject: '', message: '' }),
    queueStatus: null,
    loadingQueueStatus: false,
    emailLogs: () => [],
    loadingLogs: false
})

defineEmits<{
    (e: 'send-test-email'): void;
    (e: 'refresh-queue'): void;
    (e: 'refresh-logs'): void;
    (e: 'update:test-email', value: TestEmail): void;
}>()

const formatDate = (dateString: string | null | undefined) => {
    if (!dateString) return ''
    const date = new Date(dateString)
    return date.toLocaleString()
}
</script>
