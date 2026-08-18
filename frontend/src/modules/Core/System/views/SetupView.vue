<template>
  <div class="min-h-screen bg-background flex items-center justify-center p-4">
    <Card class="w-full max-w-md border-border/40 shadow-2xl bg-card/50 backdrop-blur-xl overflow-hidden">
      <div class="bg-primary p-6 text-primary-foreground relative overflow-hidden">
        <div class="relative z-10">
          <PageHeader
            borderless
            display-size
            class="mb-0 pb-0 border-0"
            title="Setup System"
            subtitle="Create Super Admin Account"
          />
        </div>
        <div class="absolute -right-10 -top-10 w-40 h-40 bg-white/10 rounded-full blur-3xl pointer-events-none" />
      </div>

      <CardContent class="p-8 space-y-6">
        <div class="space-y-2">
          <p class="text-sm text-muted-foreground">
            Welcome to the post-reset setup wizard. Since the database has been wiped, you need to create a new Super Administrator account to regain access to the system.
          </p>
        </div>

        <form @submit.prevent="handleSetup" class="space-y-4">
          <div class="space-y-2">
            <Label>Full Name</Label>
            <Input v-model="form.name" required placeholder="Administrator" />
          </div>
          <div class="space-y-2">
            <Label>Username</Label>
            <Input v-model="form.username" required placeholder="admin" />
          </div>
          <div class="space-y-2">
            <Label>Email Address</Label>
            <Input v-model="form.email" type="email" required placeholder="admin@example.com" />
          </div>
          <div class="space-y-2">
            <Label>Secure Password</Label>
            <Input v-model="form.password" type="password" required placeholder="Minimum 8 characters" minlength="8" />
          </div>

          <Button type="submit" class="w-full h-11 mt-2" :disabled="loading">
            <Loader2 v-if="loading" class="mr-2 w-4 h-4 animate-spin" />
            Create Account & Login
          </Button>
        </form>
      </CardContent>
    </Card>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { Card, CardContent, Button, Input, Label } from '@/shared/components/ui';
import { PageHeader } from '@/shared/components/shell';
import { Loader2 } from 'lucide-vue-next';
import api, { getCsrfCookie } from '@/engine/api/client';
import { useToast } from '@/shared/composables/useToast';

const toast = useToast();
const route = useRoute();
const router = useRouter();
const loading = ref(false);
const setupToken = ref('');

const form = ref({
  name: '',
  username: '',
  email: '',
  password: '',
});

onMounted(() => {
    const token = route.query.token as string;
    if (!token) {
        toast.error.default('Setup token missing or invalid.');
        router.replace('/404');
        return;
    }
    setupToken.value = token;
});

const handleSetup = async () => {
  if (!setupToken.value) return;
  loading.value = true;
  try {
    const payload = {
        ...form.value,
        setup_token: setupToken.value
    };
    const res = await api.post('/install/setup-admin', payload);
    toast.success.default(res.data.message);
    
    if (res.data.token) {
        localStorage.setItem('auth_token', res.data.token);
    }
    
    setTimeout(async () => {
        // Clear stale session cookies before redirecting to sign-in
        document.cookie = 'XSRF-TOKEN=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
        document.cookie = 'jejakawan-session=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
        
        try {
            await getCsrfCookie();
        } catch (e) {
            // Proceed even if this fails
        }
        
        window.location.href = '/auth/console-sign-in';
    }, 1000);
  } catch (err: any) {
    toast.error.default(err.response?.data?.message || 'Failed to setup admin account.');
  } finally {
    loading.value = false;
  }
};
</script>
