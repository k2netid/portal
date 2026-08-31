<template>
  <MemberPage
    :title="t('member.portal.profile.title', 'Profile')"
    :subtitle="t('member.portal.profile.subtitle', 'Your reader identity on this site.')"
  >
    <form
      class="space-y-6"
      @submit.prevent="submit"
    >
      <p
        v-if="error"
        class="text-sm text-destructive rounded-lg border border-destructive/30 bg-destructive/5 px-4 py-3"
      >
        {{ error }}
      </p>
      <p
        v-if="saved"
        class="text-sm text-emerald-700 rounded-lg border border-emerald-500/30 bg-emerald-500/10 px-4 py-3"
      >
        {{ t('member.portal.profile.saved', 'Profile saved.') }}
      </p>

      <div class="grid gap-6 xl:grid-cols-12">
        <div class="xl:col-span-7 space-y-6">
          <ConsoleFormCard :title="t('member.portal.profile.sections.identity', 'Identity')">
            <div class="flex flex-col sm:flex-row sm:items-start gap-4">
              <button
                type="button"
                class="flex h-20 w-20 shrink-0 items-center justify-center rounded-full border border-border/60 bg-muted/40 overflow-hidden mx-auto sm:mx-0 ring-offset-background transition hover:opacity-90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                :aria-label="t('member.form.selectAvatar', 'Select avatar')"
                @click="openAvatarModal"
              >
                <img
                  v-if="form.avatar"
                  :src="form.avatar"
                  :alt="form.name || t('member.portal.profile.avatar', 'Avatar')"
                  class="h-full w-full object-cover"
                >
                <span
                  v-else
                  class="text-xl font-bold text-primary"
                >
                  {{ initials }}
                </span>
              </button>
              <div class="flex-1 min-w-0 space-y-4 w-full">
                <div class="space-y-2">
                  <span class="block text-sm font-medium">{{ t('member.portal.profile.avatar', 'Avatar') }}</span>
                  <div class="flex flex-wrap items-center gap-2">
                    <Button
                      type="button"
                      variant="outline"
                      size="sm"
                      @click="openAvatarModal"
                    >
                      {{ t('member.form.selectAvatar', 'Select avatar') }}
                    </Button>
                    <Button
                      v-if="form.avatar"
                      type="button"
                      variant="outline"
                      size="sm"
                      class="text-destructive border-destructive/40"
                      :disabled="avatarRemoving"
                      @click="removeAvatar"
                    >
                      {{
                        avatarRemoving
                          ? t('member.portal.profile.avatarRemoving', 'Removing…')
                          : t('member.form.removeAvatar', 'Remove avatar')
                      }}
                    </Button>
                  </div>
                </div>
                <label class="block space-y-1.5 text-sm">
                  <span class="font-medium">{{ t('member.portal.profile.name', 'Display name') }}</span>
                  <input
                    v-model="form.name"
                    type="text"
                    required
                    maxlength="255"
                    class="w-full h-10 rounded-lg border border-border bg-background px-3"
                  >
                </label>
              </div>
            </div>
            <label class="block space-y-1.5 text-sm">
              <span class="font-medium">{{ t('member.portal.profile.bio', 'Bio') }}</span>
              <textarea
                v-model="form.bio"
                rows="4"
                maxlength="500"
                :placeholder="t('member.portal.profile.bioHint', 'A short note about you (optional).')"
                class="w-full rounded-lg border border-border bg-background px-3 py-2.5 resize-y min-h-[6rem]"
              />
              <span class="text-xs text-muted-foreground">{{ bioLength }}/500</span>
            </label>
          </ConsoleFormCard>

          <ConsoleFormCard :title="t('member.portal.profile.sections.contact', 'Contact')">
            <div class="space-y-4">
              <label class="block space-y-1.5 text-sm">
                <span class="font-medium">{{ t('member.portal.profile.email', 'Email') }}</span>
                <input
                  :value="memberStore.member?.email || ''"
                  type="email"
                  disabled
                  class="w-full h-10 rounded-lg border border-border bg-muted/40 px-3 text-muted-foreground"
                >
                <span class="text-xs text-muted-foreground">
                  {{ t('member.portal.profile.emailHint', 'Change email from Security settings.') }}
                </span>
              </label>
              <p
                v-if="memberStore.member?.pending_email"
                class="text-sm rounded-lg border border-amber-500/40 bg-amber-500/10 px-3 py-2"
              >
                {{ t('member.portal.security.pendingEmail', 'Pending confirmation:') }}
                <strong>{{ memberStore.member.pending_email }}</strong>
              </p>
              <label class="block space-y-1.5 text-sm">
                <span class="font-medium">{{ t('member.portal.profile.phone', 'Phone') }}</span>
                <input
                  v-model="form.phone"
                  type="tel"
                  maxlength="32"
                  :placeholder="t('member.portal.profile.phoneHint', '+62 …')"
                  class="w-full h-10 rounded-lg border border-border bg-background px-3"
                >
              </label>
            </div>
          </ConsoleFormCard>
        </div>

        <div class="xl:col-span-5 space-y-6">
          <ConsoleFormCard :title="t('member.portal.profile.sections.preferences', 'Preferences')">
            <div class="space-y-4">
              <label class="block space-y-1.5 text-sm">
                <span class="font-medium">{{ t('member.portal.profile.locale', 'Language') }}</span>
                <select
                  v-model="form.locale"
                  class="w-full h-10 rounded-lg border border-border bg-background px-3"
                >
                  <option value="">
                    {{ t('member.portal.profile.localeDefault', 'Use site default') }}
                  </option>
                  <option
                    v-for="lang in languages"
                    :key="lang.code"
                    :value="lang.code"
                  >
                    {{ lang.native_name || lang.name }} ({{ lang.code }})
                  </option>
                </select>
              </label>
              <label class="block space-y-1.5 text-sm">
                <span class="font-medium">{{ t('member.portal.profile.timezone', 'Timezone') }}</span>
                <select
                  v-model="form.timezone"
                  class="w-full h-10 rounded-lg border border-border bg-background px-3"
                >
                  <option value="">
                    {{ t('member.portal.profile.timezoneDefault', 'Use browser default') }}
                  </option>
                  <option
                    v-for="tz in timezoneOptions"
                    :key="tz"
                    :value="tz"
                  >
                    {{ tz }}
                  </option>
                </select>
              </label>
            </div>
          </ConsoleFormCard>

          <ConsoleFormCard :title="t('member.portal.profile.sections.account', 'Account')">
            <dl class="grid gap-4 sm:grid-cols-2 text-sm">
              <div class="space-y-1">
                <dt class="text-muted-foreground">
                  {{ t('member.portal.profile.status', 'Status') }}
                </dt>
                <dd class="font-medium capitalize">
                  {{ memberStore.member?.status || '—' }}
                </dd>
              </div>
              <div class="space-y-1">
                <dt class="text-muted-foreground">
                  {{ t('member.portal.profile.verified', 'Email verified') }}
                </dt>
                <dd class="font-medium">
                  {{
                    memberStore.member?.email_verified === true
                      ? t('member.verified.yes', 'Verified')
                      : t('member.verified.no', 'Unverified')
                  }}
                </dd>
              </div>
              <div class="space-y-1">
                <dt class="text-muted-foreground">
                  {{ t('member.portal.profile.joinedAt', 'Member since') }}
                </dt>
                <dd class="font-medium">
                  {{ formatDate(memberStore.member?.created_at) }}
                </dd>
              </div>
              <div class="space-y-1">
                <dt class="text-muted-foreground">
                  {{ t('member.portal.profile.lastLogin', 'Last sign-in') }}
                </dt>
                <dd class="font-medium">
                  {{ formatDate(memberStore.member?.last_login_at) }}
                </dd>
              </div>
            </dl>
          </ConsoleFormCard>
        </div>
      </div>

      <div class="flex flex-wrap items-center gap-3 border-t border-border/50 pt-5">
        <Button
          type="submit"
          :disabled="pending"
        >
          {{ pending ? t('member.portal.profile.pending', 'Saving…') : t('member.portal.profile.save', 'Save profile') }}
        </Button>
      </div>
    </form>

    <p
      v-if="memberStore.member && memberStore.member.email_verified !== true"
      class="text-sm rounded-lg border border-amber-500/40 bg-amber-500/10 px-4 py-3"
    >
      {{ t('member.account.verifyHint', 'Confirm your email to finish setting up this reader account.') }}
      <button
        type="button"
        class="ml-2 font-semibold text-primary"
        :disabled="resending"
        @click="resend"
      >
        {{ resendLabel }}
      </button>
    </p>

    <Dialog v-model:open="avatarPickerOpen">
      <DialogContent class="sm:max-w-md">
        <DialogHeader>
          <DialogTitle>
            {{ t('member.portal.profile.avatarModalTitle', 'Update avatar') }}
          </DialogTitle>
          <DialogDescription>
            {{ t('member.portal.profile.avatarModalHint', 'Upload a photo or paste an image URL.') }}
          </DialogDescription>
        </DialogHeader>
        <div class="space-y-4 py-1">
          <div class="flex justify-center">
            <div class="flex h-24 w-24 items-center justify-center rounded-full border border-border/60 bg-muted/40 overflow-hidden">
              <img
                v-if="avatarPreviewSrc"
                :src="avatarPreviewSrc"
                alt=""
                class="h-full w-full object-cover"
                @error="avatarPreviewBroken = true"
                @load="avatarPreviewBroken = false"
              >
              <span
                v-else
                class="text-2xl font-bold text-primary"
              >
                {{ initials }}
              </span>
            </div>
          </div>
          <p
            v-if="avatarPreviewBroken && avatarPreviewSrc"
            class="text-xs text-center text-destructive"
          >
            {{ t('member.portal.profile.avatarPreviewFailed', 'Could not load that image.') }}
          </p>
          <p
            v-if="avatarUploadError"
            class="text-xs text-center text-destructive"
          >
            {{ avatarUploadError }}
          </p>

          <div class="space-y-2">
            <input
              ref="avatarFileInput"
              type="file"
              accept="image/jpeg,image/png,image/gif,image/webp"
              class="sr-only"
              @change="onAvatarFileChange"
            >
            <Button
              type="button"
              variant="outline"
              class="w-full"
              :disabled="avatarUploading"
              @click="avatarFileInput?.click()"
            >
              {{
                avatarUploading
                  ? t('member.portal.profile.avatarUploading', 'Uploading…')
                  : t('member.portal.profile.avatarUpload', 'Upload photo')
              }}
            </Button>
            <p class="text-xs text-muted-foreground text-center">
              {{ t('member.portal.profile.avatarUploadHint', 'JPG, PNG, GIF, or WebP · max 2 MB') }}
            </p>
          </div>

          <div class="relative flex items-center gap-3 py-1">
            <div class="h-px flex-1 bg-border/60" />
            <span class="text-xs text-muted-foreground uppercase tracking-wide">
              {{ t('member.portal.profile.avatarOrUrl', 'or URL') }}
            </span>
            <div class="h-px flex-1 bg-border/60" />
          </div>

          <label class="block space-y-1.5 text-sm">
            <span class="font-medium">{{ t('member.portal.profile.avatarUrl', 'Avatar URL') }}</span>
            <input
              v-model="avatarDraft"
              type="url"
              inputmode="url"
              maxlength="512"
              :placeholder="t('member.portal.profile.avatarHint', 'https://…')"
              class="w-full h-10 rounded-lg border border-border bg-background px-3"
              :disabled="avatarUploading"
              @keydown.enter.prevent="applyAvatar"
            >
          </label>
        </div>
        <DialogFooter class="gap-2 sm:gap-0">
          <Button
            type="button"
            variant="outline"
            :disabled="avatarUploading"
            @click="avatarPickerOpen = false"
          >
            {{ t('common.actions.cancel', 'Cancel') }}
          </Button>
          <Button
            type="button"
            :disabled="avatarUploading"
            @click="applyAvatar"
          >
            {{ t('member.portal.profile.applyAvatar', 'Apply') }}
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </MemberPage>
</template>

<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, reactive, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { isAxiosError } from 'axios';
import { Button } from '@/modules/Layout/views/themes/sarangenge/ui';
import MemberPage from '@/modules/Member/components/MemberPage.vue';
import { useLanguage } from '@/shared/composables/useLanguage';
import { useMemberStore } from '@/modules/Member/stores/member';
import type { MemberProfileInput } from '@/modules/Member/types/profile';
import { MEMBER_TIMEZONE_OPTIONS } from '@/modules/Member/types/profile';
import { ConsoleFormCard } from '@/shared/components/shell';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/shared/components/ui';

const { t, locale } = useI18n();
const memberStore = useMemberStore();
const { languages, loadLanguages, setLanguage } = useLanguage();

const timezoneOptions = MEMBER_TIMEZONE_OPTIONS;
const form = reactive<MemberProfileInput>({
    name: '',
    phone: '',
    avatar: '',
    bio: '',
    locale: '',
    timezone: '',
});

const pending = ref(false);
const error = ref('');
const saved = ref(false);
const resending = ref(false);
const resent = ref(false);
const avatarPickerOpen = ref(false);
const avatarDraft = ref('');
const avatarPreviewBroken = ref(false);
const avatarUploading = ref(false);
const avatarRemoving = ref(false);
const avatarUploadError = ref('');
const avatarFileInput = ref<HTMLInputElement | null>(null);
const avatarLocalPreview = ref('');

const avatarPreviewSrc = computed(() => {
    if (avatarLocalPreview.value) {
        return avatarLocalPreview.value;
    }
    return avatarDraft.value.trim();
});

const clearLocalPreview = (): void => {
    if (avatarLocalPreview.value) {
        URL.revokeObjectURL(avatarLocalPreview.value);
        avatarLocalPreview.value = '';
    }
};

const openAvatarModal = (): void => {
    clearLocalPreview();
    avatarDraft.value = form.avatar ?? '';
    avatarPreviewBroken.value = false;
    avatarUploadError.value = '';
    avatarPickerOpen.value = true;
};

const removeAvatar = async (): Promise<void> => {
    if (avatarRemoving.value) {
        return;
    }
    avatarRemoving.value = true;
    error.value = '';
    try {
        await memberStore.updateProfile({
            name: form.name.trim() || memberStore.member?.name || 'Reader',
            phone: form.phone?.trim() || null,
            avatar: null,
            bio: form.bio?.trim() || null,
            locale: form.locale?.trim() || null,
            timezone: form.timezone?.trim() || null,
        });
        form.avatar = '';
        saved.value = true;
    } catch (err: unknown) {
        error.value = isAxiosError(err)
            ? String(err.response?.data?.message || t('member.portal.profile.avatarUploadFailed', 'Could not upload avatar.'))
            : t('member.portal.profile.failed', 'Could not save profile.');
    } finally {
        avatarRemoving.value = false;
    }
};

const onAvatarFileChange = async (event: Event): Promise<void> => {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0];
    input.value = '';
    if (!file) {
        return;
    }

    avatarUploadError.value = '';
    if (!file.type.startsWith('image/')) {
        avatarUploadError.value = t('member.portal.profile.avatarInvalidType', 'Please choose an image file.');
        return;
    }
    if (file.size > 2 * 1024 * 1024) {
        avatarUploadError.value = t('member.portal.profile.avatarTooLarge', 'Image must be 2 MB or smaller.');
        return;
    }

    clearLocalPreview();
    avatarLocalPreview.value = URL.createObjectURL(file);
    avatarDraft.value = '';
    avatarPreviewBroken.value = false;

    avatarUploading.value = true;
    try {
        const member = await memberStore.uploadAvatar(file);
        form.avatar = member.avatar ?? '';
        clearLocalPreview();
        avatarPickerOpen.value = false;
        saved.value = true;
        error.value = '';
    } catch (err: unknown) {
        clearLocalPreview();
        avatarUploadError.value = isAxiosError(err)
            ? String(err.response?.data?.message || t('member.portal.profile.avatarUploadFailed', 'Could not upload avatar.'))
            : t('member.portal.profile.avatarUploadFailed', 'Could not upload avatar.');
    } finally {
        avatarUploading.value = false;
    }
};

const applyAvatar = (): void => {
    if (avatarUploading.value) {
        return;
    }
    form.avatar = avatarDraft.value.trim();
    clearLocalPreview();
    avatarPickerOpen.value = false;
};

watch(avatarPickerOpen, (open) => {
    if (!open) {
        clearLocalPreview();
        avatarUploadError.value = '';
    }
});

onBeforeUnmount(() => {
    clearLocalPreview();
});

const bioLength = computed(() => form.bio?.length ?? 0);

const initials = computed(() => {
    const raw = form.name?.trim() || memberStore.member?.email || '?';
    const parts = raw.split(/[\s@._-]+/).filter(Boolean);
    if (parts.length >= 2) {
        return `${parts[0]![0] ?? ''}${parts[1]![0] ?? ''}`.toUpperCase();
    }
    return raw.slice(0, 2).toUpperCase();
});

const resendLabel = computed(() => (
    resent.value
        ? t('member.account.resent', 'Sent')
        : t('member.account.resend', 'Resend email')
));

function syncFormFromMember(): void {
    const member = memberStore.member;
    if (!member) {
        return;
    }
    form.name = member.name ?? '';
    form.phone = member.phone ?? '';
    form.avatar = member.avatar ?? '';
    form.bio = member.bio ?? '';
    form.locale = member.locale ?? '';
    form.timezone = member.timezone ?? '';
}

watch(() => memberStore.member, syncFormFromMember, { immediate: true, deep: true });

const formatDate = (value?: string | null): string => {
    if (!value) {
        return '—';
    }
    try {
        return new Intl.DateTimeFormat(locale.value || undefined, {
            dateStyle: 'medium',
            timeStyle: 'short',
        }).format(new Date(value));
    } catch {
        return value;
    }
};

const resend = async (): Promise<void> => {
    resending.value = true;
    try {
        await memberStore.resendVerification();
        resent.value = true;
    } finally {
        resending.value = false;
    }
};

const submit = async (): Promise<void> => {
    pending.value = true;
    error.value = '';
    saved.value = false;
    try {
        const payload: MemberProfileInput = {
            name: form.name.trim(),
            phone: form.phone?.trim() || null,
            avatar: form.avatar?.trim() || null,
            bio: form.bio?.trim() || null,
            locale: form.locale?.trim() || null,
            timezone: form.timezone?.trim() || null,
        };
        await memberStore.updateProfile(payload);
        if (payload.locale) {
            await setLanguage(payload.locale);
        }
        saved.value = true;
    } catch (err: unknown) {
        error.value = isAxiosError(err)
            ? String(err.response?.data?.message || t('member.portal.profile.failed', 'Could not save profile.'))
            : t('member.portal.profile.failed', 'Could not save profile.');
    } finally {
        pending.value = false;
    }
};

onMounted(() => {
    void loadLanguages();
});
</script>
