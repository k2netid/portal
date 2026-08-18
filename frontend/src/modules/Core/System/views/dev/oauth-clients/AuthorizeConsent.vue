<template>
  <div class="min-h-[60vh] flex items-center justify-center p-6">
    <ConsoleFormCard class="w-full max-w-lg p-8 space-y-6">
      <div class="space-y-2 text-center">
        <Shield class="mx-auto h-10 w-10 text-primary" />
        <h1 class="text-xl font-semibold">{{ t('system.oauth.consent.title') }}</h1>
        <p class="text-sm text-muted-foreground">{{ t('system.oauth.consent.description', { client: clientName }) }}</p>
      </div>
      <div v-if="scopeList.length" class="rounded-lg border border-border bg-muted/30 p-4">
        <p class="text-xs font-semibold uppercase tracking-wide text-muted-foreground mb-2">{{ t('system.oauth.consent.scopes') }}</p>
        <ul class="space-y-1 text-sm">
          <li v-for="scope in scopeList" :key="scope" class="font-mono text-foreground/90">{{ scope }}</li>
        </ul>
      </div>
      <p v-if="!authToken" class="text-sm text-destructive text-center">{{ t('system.oauth.consent.missingToken') }}</p>
      <div v-else class="flex flex-col sm:flex-row gap-3 justify-center">
        <Button variant="default" :disabled="submitting" @click="approve">{{ t('system.oauth.consent.allow') }}</Button>
        <Button variant="outline" :disabled="submitting" @click="deny">{{ t('system.oauth.consent.deny') }}</Button>
      </div>
    </ConsoleFormCard>
  </div>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue';
import { useRoute } from 'vue-router';
import { useI18n } from 'vue-i18n';
import { Shield } from 'lucide-vue-next';
import ConsoleFormCard from '@/shared/components/shell/ConsoleFormCard.vue';
import { Button } from '@/shared/components/ui';

const { t } = useI18n();
const route = useRoute();
const submitting = ref(false);
const authToken = computed(() => String(route.query.auth_token ?? ''));
const clientName = computed(() => String(route.query.client ?? t('system.oauth.consent.unknownClient')));
const scopeList = computed(() => {
  const raw = String(route.query.scopes ?? '').trim();
  return raw ? raw.split(/\s+/).filter(Boolean) : [];
});

function getCsrfToken(): string {
  const match = document.cookie.match(/XSRF-TOKEN=([^;]+)/);
  return match?.[1] ? decodeURIComponent(match[1]) : '';
}

function postConsent(approve: boolean) {
  if (!authToken.value) return;
  submitting.value = true;
  const form = document.createElement('form');
  form.method = 'POST';
  form.action = '/oauth/authorize';
  const tokenInput = document.createElement('input');
  tokenInput.type = 'hidden';
  tokenInput.name = '_token';
  tokenInput.value = getCsrfToken();
  form.appendChild(tokenInput);
  const authInput = document.createElement('input');
  authInput.type = 'hidden';
  authInput.name = 'auth_token';
  authInput.value = authToken.value;
  form.appendChild(authInput);
  if (!approve) {
    const methodInput = document.createElement('input');
    methodInput.type = 'hidden';
    methodInput.name = '_method';
    methodInput.value = 'DELETE';
    form.appendChild(methodInput);
  }
  document.body.appendChild(form);
  form.submit();
}

function approve() { postConsent(true); }
function deny() { postConsent(false); }
</script>
