<template>
  <div class="sarangenge-theme flex-1 flex flex-col py-16">
    <div class="max-w-md mx-auto w-full px-4 text-center space-y-3">
      <h1 class="text-2xl font-extrabold font-heading">
        {{ t('member.oauth.completing', 'Completing sign-in…') }}
      </h1>
      <p
        v-if="error"
        class="text-sm text-destructive"
      >
        {{ error }}
      </p>
      <p
        v-else
        class="text-sm text-muted-foreground"
      >
        {{ t('member.oauth.wait', 'Please wait while we finish connecting your Google account.') }}
      </p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRoute, useRouter } from 'vue-router';
import { useMemberStore } from '@/modules/Member/stores/member';

const { t } = useI18n();
const route = useRoute();
const router = useRouter();
const memberStore = useMemberStore();
const error = ref('');

onMounted(async () => {
    const token = typeof route.query.token === 'string' ? route.query.token : '';
    const redirect = typeof route.query.redirect === 'string' && route.query.redirect.startsWith('/')
        ? route.query.redirect
        : '/member';

    if (!token) {
        error.value = t('member.oauth.missingToken', 'Sign-in token missing. Please try again.');
        await router.replace({ name: 'public-member-login', query: { oauth_error: 'failed' } });
        return;
    }

    try {
        await memberStore.applyOAuthToken(token);
        await router.replace(redirect || '/member');
    } catch {
        error.value = t('member.oauth.failed', 'Could not complete Google sign-in.');
        await router.replace({ name: 'public-member-login', query: { oauth_error: 'failed' } });
    }
});
</script>
