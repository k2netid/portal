<template>
  <div class="p-6 space-y-6">
    <div>
      <h2 class="text-3xl font-bold tracking-tight">
        {{ $t('system.profile.title') }}
      </h2>
      <p class="text-muted-foreground">
        {{ $t('system.profile.subtitle') }}
      </p>
    </div>

    <div class="w-full">
      <Tabs
        v-model="activeTab"
        class="w-full"
      >
        <div class="mb-10 flex items-center justify-between">
          <TabsList class="bg-transparent p-0 h-auto gap-0">
            <TabsTrigger
              value="profile"
              class="relative px-6 py-3 data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none"
            >
              <User class="w-4 h-4 mr-2" />
              {{ $t('system.profile.tabs.profile') }}
            </TabsTrigger>
            <TabsTrigger
              value="two-factor"
              class="relative px-6 py-3 data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none"
            >
              <ShieldCheck class="w-4 h-4 mr-2" />
              {{ $t('system.profile.tabs.two-factor') }}
            </TabsTrigger>
            <TabsTrigger
              value="passkeys"
              class="relative px-6 py-3 data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none"
            >
              <Fingerprint class="w-4 h-4 mr-2" />
              {{ $t('system.profile.tabs.passkeys', 'Passkeys') }}
            </TabsTrigger>
            <TabsTrigger
              value="history"
              class="relative px-6 py-3 data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none"
            >
              <History class="w-4 h-4 mr-2" />
              {{ $t('system.profile.tabs.history') }}
            </TabsTrigger>
            <TabsTrigger
              value="kyc"
              class="relative px-6 py-3 data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none"
            >
              <FileCheck class="w-4 h-4 mr-2" />
              {{ $t('system.profile.tabs.kyc', 'Verification') }}
            </TabsTrigger>
            <TabsTrigger
              value="sessions"
              class="relative px-6 py-3 data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none"
            >
              <MonitorSmartphone class="w-4 h-4 mr-2" />
              {{ $t('system.profile.tabs.sessions', 'Active Sessions') }}
            </TabsTrigger>
          </TabsList>
        </div>

        <TabsContent
          value="profile"
          class="space-y-4"
        >
          <Card>
            <CardHeader>
              <CardTitle>{{ $t('system.profile.tabs.profile') }}</CardTitle>
              <CardDescription>
                {{ $t('system.profile.subtitle') }}
              </CardDescription>
            </CardHeader>
            <CardContent class="space-y-6">
              <!-- Avatar Upload -->
              <div class="flex items-center gap-x-6">
                <div class="relative">
                  <Avatar class="h-24 w-24">
                    <AvatarImage
                      :src="profileForm.avatar || ''"
                      alt="Avatar"
                    />
                    <AvatarFallback class="text-lg">
                      {{ getInitials(profileForm.name) }}
                    </AvatarFallback>
                  </Avatar>
                  <button
                    v-if="profileForm.avatar"
                    type="button"
                    class="absolute -top-1 -right-1 rounded-full bg-destructive p-1 text-destructive-foreground hover:bg-destructive/90 shadow-sm"
                    title="Remove Avatar"
                    @click="profileForm.avatar = null"
                  >
                    <X class="h-3 w-3" />
                  </button>
                </div>
                <div>
                  <MediaPicker
                    :label="$t('system.users.form.selectAvatar')"
                    @selected="(media: { url: string }) => profileForm.avatar = media.url"
                  />
                  <p class="mt-2 text-xs text-muted-foreground">
                    JPG, GIF or PNG. 1MB max.
                  </p>
                </div>
              </div>

              <Separator class="my-4" />

              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2">
                  <Label for="name">{{ $t('system.profile.form.name') }}</Label>
                  <Input
                    id="name"
                    v-model="profileForm.name"
                    type="text"
                  />
                </div>

                <div class="space-y-2">
                  <Label for="username">Username</Label>
                  <div class="relative">
                    <Input
                      id="username"
                      v-model="profileForm.username"
                      type="text"
                      :disabled="!canChangeUsername"
                      @input="onUsernameInput"
                      :class="{'border-destructive': usernameAvailable === false, 'border-green-500': usernameAvailable === true}"
                    />
                    <div class="absolute right-3 top-1/2 -translate-y-1/2 flex items-center">
                      <Loader2 v-if="checkingUsername" class="h-4 w-4 animate-spin text-muted-foreground" />
                      <ShieldCheck v-else-if="usernameAvailable === true" class="h-4 w-4 text-green-500" />
                      <X v-else-if="usernameAvailable === false" class="h-4 w-4 text-destructive" />
                    </div>
                  </div>
                  <p class="text-xs text-muted-foreground">
                    <span v-if="usernameAvailable === false" class="text-destructive font-medium block mb-1">Username is taken!</span>
                    <span v-if="!canChangeUsername" class="text-destructive font-medium block">You have reached the limit of username changes.</span>
                    <span v-else>You can change your username {{ maxUsernameChanges - usernameChangesCount }} more times.</span>
                  </p>
                </div>

                <div class="space-y-2">
                  <Label for="email">{{ $t('system.profile.form.email') }}</Label>
                  <Input
                    id="email"
                    v-model="profileForm.email"
                    type="email"
                  />
                </div>

                <div class="space-y-2">
                  <Label for="phone">{{ $t('system.profile.form.phone') }}</Label>
                  <Input
                    id="phone"
                    v-model="profileForm.phone"
                    type="text"
                  />
                </div>

                <div class="space-y-2">
                  <Label for="location">{{ $t('system.profile.form.location') }}</Label>
                  <Input
                    id="location"
                    v-model="profileForm.location"
                    type="text"
                  />
                </div>
              </div>

              <div class="space-y-2">
                <Label for="bio">{{ $t('system.profile.form.bio') }}</Label>
                <Textarea
                  id="bio"
                  v-model="profileForm.bio"
                  rows="4"
                />
              </div>

              <div class="flex justify-end">
                <Button
                  :disabled="saving || !isProfileDirty"
                  @click="updateProfile"
                >
                  <Loader2
                    v-if="saving"
                    class="mr-2 h-4 w-4"
                  />
                  {{ saving ? $t('system.profile.form.saving') : $t('system.profile.form.save') }}
                </Button>
              </div>
            </CardContent>
          </Card>

          <Card class="mt-6">
            <CardHeader>
              <CardTitle>{{ $t('system.profile.tabs.password') }}</CardTitle>
              <CardDescription>
                {{ $t('system.profile.form.passwordHelp') }}
              </CardDescription>
            </CardHeader>
            <form
              class="grid grid-cols-1 md:grid-cols-2 gap-6 p-6"
              @submit.prevent="updatePassword"
            >
              <div class="space-y-2">
                <Label for="current_password">{{ $t('system.profile.form.currentPassword') }}</Label>
                <div class="relative">
                  <Input 
                    id="current_password" 
                    v-model="passwordForm.current_password" 
                    :type="showCurrentPassword ? 'text' : 'password'" 
                    name="current_password"
                    autocomplete="current-password"
                    class="pr-10"
                  />
                  <button
                    type="button"
                    class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground transition-colors"
                    @click="showCurrentPassword = !showCurrentPassword"
                  >
                    <Eye
                      v-if="!showCurrentPassword"
                      class="h-4 w-4"
                    />
                    <EyeOff
                      v-else
                      class="h-4 w-4"
                    />
                  </button>
                </div>
              </div>
              <div />
              <div class="space-y-2">
                <Label for="new_password">{{ $t('system.profile.form.newPassword') }}</Label>
                <div class="relative">
                  <Input 
                    id="new_password" 
                    v-model="passwordForm.password" 
                    :type="showNewPassword ? 'text' : 'password'" 
                    name="password"
                    autocomplete="new-password"
                    class="pr-10"
                  />
                  <button
                    type="button"
                    class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground transition-colors"
                    @click="showNewPassword = !showNewPassword"
                  >
                    <Eye
                      v-if="!showNewPassword"
                      class="h-4 w-4"
                    />
                    <EyeOff
                      v-else
                      class="h-4 w-4"
                    />
                  </button>
                </div>
              </div>
              <div class="space-y-2">
                <Label for="confirm_password">{{ $t('system.profile.form.confirmPassword') }}</Label>
                <div class="relative">
                  <Input 
                    id="confirm_password" 
                    v-model="passwordForm.password_confirmation" 
                    :type="showConfirmPassword ? 'text' : 'password'" 
                    name="password_confirmation"
                    autocomplete="new-password"
                    class="pr-10"
                  />
                  <button
                    type="button"
                    class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground transition-colors"
                    @click="showConfirmPassword = !showConfirmPassword"
                  >
                    <Eye
                      v-if="!showConfirmPassword"
                      class="h-4 w-4"
                    />
                    <EyeOff
                      v-else
                      class="h-4 w-4"
                    />
                  </button>
                </div>
              </div>
              <div class="flex justify-start">
                <Button
                  type="submit"
                  :disabled="changingPassword || !isPasswordValid"
                >
                  <Loader2
                    v-if="changingPassword"
                    class="mr-2 h-4 w-4"
                  />
                  {{ $t('common.actions.update') }} {{ $t('common.labels.password') }}
                </Button>
              </div>
            </form>
          </Card>
        </TabsContent>

        <TabsContent
          value="two-factor"
          class="space-y-4"
        >
          <Card>
            <CardHeader>
              <CardTitle>{{ $t('system.profile.tabs.twoFactor') }}</CardTitle>
              <CardDescription>
                {{ $t('system.profile.form.twoFactorDescription') }}
              </CardDescription>
            </CardHeader>
            <CardContent class="pb-10">
              <TwoFactorSettings />
            </CardContent>
          </Card>
        </TabsContent>

        <TabsContent
          value="passkeys"
          class="space-y-4"
        >
          <Card>
            <CardHeader>
              <CardTitle>{{ $t('system.profile.tabs.passkeys', 'Passkeys') }}</CardTitle>
              <CardDescription>
                {{ $t('system.profile.form.passkeysDescription', 'Sign in securely without a password.') }}
              </CardDescription>
            </CardHeader>
            <CardContent class="pb-10">
              <PasskeysSettings />
            </CardContent>
          </Card>
        </TabsContent>

        <TabsContent
          value="history"
          class="space-y-4"
        >
          <Card>
            <CardHeader>
              <CardTitle>{{ $t('system.profile.tabs.history') }}</CardTitle>
            </CardHeader>
            <CardContent>
              <LoginHistory />
            </CardContent>
          </Card>
        </TabsContent>

        <TabsContent
          value="kyc"
          class="space-y-4"
        >
          <Card>
            <CardHeader>
              <CardTitle>{{ $t('system.profile.tabs.kyc', 'Verification') }}</CardTitle>
              <CardDescription>
                {{ $t('system.profile.form.kycDescription', 'Complete identity verification to unlock features.') }}
              </CardDescription>
            </CardHeader>
            <CardContent class="pb-10">
              <KycOnboarding />
            </CardContent>
          </Card>
        </TabsContent>

        <TabsContent
          value="sessions"
          class="space-y-4"
        >
          <Card>
            <CardHeader>
              <CardTitle>{{ $t('system.profile.tabs.sessions', 'Active Sessions') }}</CardTitle>
            </CardHeader>
            <CardContent>
              <DeviceSessions />
            </CardContent>
          </Card>
        </TabsContent>
      </Tabs>
    </div>
  </div>
</template>

<script setup lang="ts">
import { logger } from '@/shared/utils/logger';
import { ref, onMounted, computed, defineAsyncComponent, type Ref } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '@/engine/api/client';
import toast from '@/shared/services/toastService';
import { useAuthStore } from '@/modules/Core/System/stores/auth';
import { isAxiosError } from 'axios';

// Standardized Async Components
const LoginHistory = defineAsyncComponent(() => import('@/modules/Core/System/components/console/LoginHistory.vue'));
const DeviceSessions = defineAsyncComponent(() => import('@/modules/Core/System/components/console/DeviceSessions.vue'));
const TwoFactorSettings = defineAsyncComponent(() => import('@/modules/Core/System/components/console/TwoFactorSettings.vue'));
const PasskeysSettings = defineAsyncComponent(() => import('@/modules/Core/System/components/console/PasskeysSettings.vue'));
const KycOnboarding = defineAsyncComponent(() => import('@/modules/Core/System/components/console/KycOnboarding.vue'));
const MediaPicker = defineAsyncComponent(() => import('@/shared/components/ui/MediaPicker.vue'));

// Shadcn Components
// Shadcn Components
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
    Tabs,
    TabsContent,
    TabsList,
    TabsTrigger,
    Input,
    Button,
    Label,
    Textarea,
    Avatar,
    AvatarImage,
    AvatarFallback,
    Separator
} from '@/shared/components/ui';

import {
    User,
    ShieldCheck,
    History,
    Fingerprint,
    FileCheck,
    MonitorSmartphone,
    X,
    Eye,
    EyeOff,
    Loader2,
} from 'lucide-vue-next';


interface ProfileForm {
    name: string;
    username: string;
    email: string;
    phone: string;
    bio: string;
    location: string;
    website: string;
    avatar: string | null;
}

const { t } = useI18n();
const authStore = useAuthStore();

const activeTab = ref('profile');
const saving = ref(false);
const changingPassword = ref(false);
const showCurrentPassword = ref(false);
const showNewPassword = ref(false);
const showConfirmPassword = ref(false);

const usernameChangesCount = ref(0);
const maxUsernameChanges = 3;
const usernameAvailable = ref<boolean | null>(null);
const checkingUsername = ref(false);
let usernameCheckTimeout: ReturnType<typeof setTimeout> | null = null;

const profileForm: Ref<ProfileForm> = ref({
    name: '',
    username: '',
    email: '',
    phone: '',
    bio: '',
    location: '',
    website: '',
    avatar: null,
});

const initialProfileForm = ref<ProfileForm | null>(null);

const passwordForm = ref({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const getInitials = (name: string | null | undefined): string => {
    if (!name) return 'U';
    return name
        .split(' ')
        .map((word) => word[0])
        .join('')
        .toUpperCase()
        .slice(0, 2);
};

const isProfileDirty = computed(() => {
    if (!initialProfileForm.value) return false;
    return JSON.stringify(profileForm.value) !== JSON.stringify(initialProfileForm.value);
});

const canChangeUsername = computed(() => {
    return usernameChangesCount.value < maxUsernameChanges;
});

const checkUsername = async () => {
    if (profileForm.value.username === initialProfileForm.value?.username) {
        usernameAvailable.value = null;
        return;
    }
    
    if (!profileForm.value.username || profileForm.value.username.trim() === '') {
        usernameAvailable.value = null;
        return;
    }

    checkingUsername.value = true;
    try {
        const response = await api.post('/manage/system/profile/username/check', { username: profileForm.value.username });
        usernameAvailable.value = response.data.data.available;
    } catch (error) {
        usernameAvailable.value = null;
    } finally {
        checkingUsername.value = false;
    }
};

const onUsernameInput = () => {
    usernameAvailable.value = null;
    if (usernameCheckTimeout) {
        clearTimeout(usernameCheckTimeout);
    }
    usernameCheckTimeout = setTimeout(() => {
        checkUsername();
    }, 500);
};

const isPasswordValid = computed(() => {
    return passwordForm.value.current_password && 
           passwordForm.value.password && 
           passwordForm.value.password.length >= 8 &&
           passwordForm.value.password === passwordForm.value.password_confirmation;
});

const fetchProfile = async () => {
    try {
        const response = await api.get('/manage/system/profile');
        const data = response.data.data || response.data; // Handle success wrapper if any
        if (data) {
            profileForm.value = {
                name: data.name || '',
                username: data.username || '',
                email: data.email || '',
                phone: data.phone || '',
                bio: data.bio || '',
                location: data.location || '',
                website: data.website || '',
                avatar: data.avatar || null,
            };
            usernameChangesCount.value = data.username_changes_count || 0;
            initialProfileForm.value = JSON.parse(JSON.stringify(profileForm.value));
        }
    } catch (error: unknown) {
        logger.error('Error fetching profile:', error);
        toast.error(t('common.messages.error.default'));
    }
};

const updateProfile = async () => {
    saving.value = true;
    try {
        await api.put('/manage/system/profile', profileForm.value);
        toast.success(t('system.profile.messages.updateSuccess'));
        await authStore.fetchUser();
        await fetchProfile(); // Re-fetch to update initial state
    } catch (error: unknown) {
        let msg = t('system.profile.messages.updateFailed');
        if (isAxiosError(error)) {
            msg = error.response?.data?.message || msg;
        }
        toast.error(msg);
    } finally {
        saving.value = false;
    }
};

const updatePassword = async () => {
    changingPassword.value = true;
    try {
        await api.put('/manage/system/profile/password', passwordForm.value);
        toast.success(t('system.profile.messages.passwordSuccess'));
        passwordForm.value = {
            current_password: '',
            password: '',
            password_confirmation: '',
        };
    } catch (error: unknown) {
        let msg = t('system.profile.messages.passwordFailed');
        if (isAxiosError(error)) {
            msg = error.response?.data?.message || msg;
        }
        toast.error(msg);
    } finally {
        changingPassword.value = false;
    }
};

onMounted(() => {
    fetchProfile();
});
</script>
