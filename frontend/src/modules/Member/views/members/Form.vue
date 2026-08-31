<template>
  <div class="space-y-6">
    <PageHeader
      borderless
      :title="pageTitle"
      :subtitle="pageSubtitle"
    >
      <template #actions>
        <router-link :to="{ name: 'members.index' }">
          <Button
            variant="ghost"
            size="sm"
            class="gap-2"
          >
            <ArrowLeft class="w-4 h-4" />
            {{ t('common.actions.back') }}
          </Button>
        </router-link>
      </template>
    </PageHeader>

    <div
      v-if="loading"
      class="flex flex-col items-center justify-center py-12"
    >
      <Loader2 class="h-8 w-8 text-muted-foreground mb-4" />
      <p class="text-muted-foreground">
        {{ t('common.messages.loading.default') }}
      </p>
    </div>

    <form
      v-else
      class="space-y-6"
      @submit.prevent="submit"
    >
      <div class="grid gap-6 xl:grid-cols-12">
        <!-- Left: Identity + Contact -->
        <div class="xl:col-span-7 space-y-6">
          <ConsoleFormCard :title="t('member.portal.profile.sections.identity')">
            <div class="flex flex-col sm:flex-row sm:items-start gap-4">
              <div
                class="flex h-20 w-20 shrink-0 items-center justify-center rounded-full border border-border/60 bg-muted/40 overflow-hidden mx-auto sm:mx-0"
              >
                <img
                  v-if="form.avatar"
                  :src="form.avatar"
                  :alt="form.name || t('member.form.avatar')"
                  class="h-full w-full object-cover"
                >
                <span
                  v-else
                  class="text-xl font-bold text-primary"
                >
                  {{ initials }}
                </span>
              </div>
              <div class="flex-1 min-w-0 space-y-4 w-full">
                <div class="space-y-2">
                  <span class="block text-sm font-medium">{{ t('member.form.avatar') }}</span>
                  <div class="flex flex-wrap items-center gap-2">
                    <MediaPicker
                      :label="t('member.form.selectAvatar')"
                      :constraints="{ allowedExtensions: ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'] }"
                      @selected="onAvatarSelected"
                    >
                      <template #trigger="{ open }">
                        <Button
                          type="button"
                          variant="outline"
                          size="sm"
                          @click="open"
                        >
                          {{ t('member.form.selectAvatar') }}
                        </Button>
                      </template>
                    </MediaPicker>
                    <Button
                      v-if="form.avatar"
                      type="button"
                      variant="outline"
                      size="sm"
                      class="text-destructive border-destructive/40"
                      @click="form.avatar = null"
                    >
                      {{ t('member.form.removeAvatar') }}
                    </Button>
                  </div>
                </div>
                <label class="block space-y-1.5 text-sm">
                  <span class="font-medium">
                    {{ t('member.form.name') }} <span class="text-destructive">*</span>
                  </span>
                  <Input
                    v-model="form.name"
                    type="text"
                    required
                    maxlength="255"
                    :class="{ 'border-destructive focus-visible:ring-destructive': !!errors.name }"
                    :placeholder="t('member.form.placeholders.name')"
                  />
                  <p
                    v-if="errors.name"
                    class="text-sm text-destructive"
                  >
                    {{ errors.name }}
                  </p>
                </label>
              </div>
            </div>
            <label class="block space-y-1.5 text-sm mt-4">
              <span class="font-medium">{{ t('member.portal.profile.bio') }}</span>
              <Textarea
                v-model="form.bio"
                :rows="4"
                maxlength="500"
                :placeholder="t('member.portal.profile.bioHint')"
                class="min-h-[6rem]"
              />
              <span class="text-xs text-muted-foreground">{{ bioLength }}/500</span>
            </label>
          </ConsoleFormCard>

          <ConsoleFormCard :title="t('member.portal.profile.sections.contact')">
            <div class="space-y-4">
              <label class="block space-y-1.5 text-sm">
                <span class="font-medium">
                  {{ t('member.form.email') }} <span class="text-destructive">*</span>
                </span>
                <Input
                  v-model="form.email"
                  type="email"
                  required
                  maxlength="255"
                  :class="{ 'border-destructive focus-visible:ring-destructive': !!errors.email }"
                  :placeholder="t('member.form.placeholders.email')"
                />
                <p
                  v-if="errors.email"
                  class="text-sm text-destructive"
                >
                  {{ errors.email }}
                </p>
              </label>
              <label class="block space-y-1.5 text-sm">
                <span class="font-medium">{{ t('member.form.phone') }}</span>
                <Input
                  v-model="form.phone"
                  type="tel"
                  maxlength="32"
                  :placeholder="t('member.form.placeholders.phone')"
                />
                <p
                  v-if="errors.phone"
                  class="text-sm text-destructive"
                >
                  {{ errors.phone }}
                </p>
              </label>
            </div>
          </ConsoleFormCard>

          <ConsoleFormCard :title="t('member.form.securitySection')">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <label class="block space-y-1.5 text-sm">
                <span class="font-medium">
                  {{ t('member.form.password') }}
                  <span
                    v-if="mode === 'create'"
                    class="text-destructive"
                  >*</span>
                </span>
                <div class="relative">
                  <Input
                    v-model="form.password"
                    :type="showPassword ? 'text' : 'password'"
                    :required="mode === 'create'"
                    minlength="8"
                    autocomplete="new-password"
                    :class="[errors.password ? 'border-destructive focus-visible:ring-destructive' : '', 'pr-10']"
                    :placeholder="mode === 'create' ? t('member.form.placeholders.password') : t('member.form.passwordHint')"
                  />
                  <button
                    type="button"
                    class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground"
                    :aria-label="showPassword ? t('common.actions.hide') : t('common.actions.show')"
                    @click="showPassword = !showPassword"
                  >
                    <Eye
                      v-if="!showPassword"
                      class="h-4 w-4"
                    />
                    <EyeOff
                      v-else
                      class="h-4 w-4"
                    />
                  </button>
                </div>
                <p
                  v-if="mode === 'edit'"
                  class="text-xs text-muted-foreground"
                >
                  {{ t('member.form.passwordHint') }}
                </p>
                <p
                  v-if="errors.password"
                  class="text-sm text-destructive"
                >
                  {{ errors.password }}
                </p>
              </label>
              <label class="block space-y-1.5 text-sm">
                <span class="font-medium">
                  {{ t('member.form.passwordConfirmation') }}
                  <span
                    v-if="mode === 'create'"
                    class="text-destructive"
                  >*</span>
                </span>
                <div class="relative">
                  <Input
                    v-model="form.password_confirmation"
                    :type="showConfirmPassword ? 'text' : 'password'"
                    :required="mode === 'create'"
                    autocomplete="new-password"
                    :class="[errors.password_confirmation ? 'border-destructive focus-visible:ring-destructive' : '', 'pr-10']"
                    :placeholder="t('member.form.placeholders.passwordConfirmation')"
                  />
                  <button
                    type="button"
                    class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground"
                    :aria-label="showConfirmPassword ? t('common.actions.hide') : t('common.actions.show')"
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
                <p
                  v-if="errors.password_confirmation"
                  class="text-sm text-destructive"
                >
                  {{ errors.password_confirmation }}
                </p>
              </label>
            </div>
          </ConsoleFormCard>
        </div>

        <!-- Right: Preferences + Account -->
        <div class="xl:col-span-5 space-y-6">
          <ConsoleFormCard :title="t('member.portal.profile.sections.preferences')">
            <div class="space-y-4">
              <label class="block space-y-1.5 text-sm">
                <span class="font-medium">{{ t('member.portal.profile.locale') }}</span>
                <select
                  v-model="form.locale"
                  class="w-full h-10 rounded-lg border border-border bg-background px-3"
                >
                  <option value="">
                    {{ t('member.portal.profile.localeDefault') }}
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
                <span class="font-medium">{{ t('member.portal.profile.timezone') }}</span>
                <select
                  v-model="form.timezone"
                  class="w-full h-10 rounded-lg border border-border bg-background px-3"
                >
                  <option value="">
                    {{ t('member.portal.profile.timezoneDefault') }}
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

          <ConsoleFormCard :title="t('member.portal.profile.sections.account')">
            <div class="space-y-4">
              <label class="block space-y-1.5 text-sm">
                <span class="font-medium">{{ t('member.table.status') }}</span>
                <select
                  v-model="form.status"
                  class="w-full h-10 rounded-lg border border-border bg-background px-3"
                >
                  <option value="active">
                    {{ t('member.status.active') }}
                  </option>
                  <option value="inactive">
                    {{ t('member.status.inactive') }}
                  </option>
                </select>
              </label>
              <label class="flex items-center gap-2 text-sm font-medium cursor-pointer select-none">
                <Checkbox
                  :checked="form.verify_email"
                  @update:checked="(checked: boolean | 'indeterminate') => form.verify_email = checked === true"
                />
                <span>{{ t('member.form.verifyEmail') }}</span>
              </label>

              <dl
                v-if="mode === 'edit' && accountMeta"
                class="grid gap-4 sm:grid-cols-2 text-sm border-t border-border/50 pt-4"
              >
                <div class="space-y-1">
                  <dt class="text-muted-foreground">
                    {{ t('member.portal.profile.joinedAt') }}
                  </dt>
                  <dd class="font-medium">
                    {{ formatDate(accountMeta.created_at) }}
                  </dd>
                </div>
                <div class="space-y-1">
                  <dt class="text-muted-foreground">
                    {{ t('member.portal.profile.lastLogin') }}
                  </dt>
                  <dd class="font-medium">
                    {{ formatDate(accountMeta.last_login_at) }}
                  </dd>
                </div>
              </dl>
            </div>
          </ConsoleFormCard>
        </div>
      </div>

      <div class="flex flex-wrap items-center justify-between gap-3 border-t border-border/50 pt-5">
        <router-link
          :to="{ name: 'members.index' }"
          class="inline-flex h-9 items-center justify-center rounded-xl border border-border/60 bg-background px-3 text-sm font-medium hover:bg-accent/50"
        >
          {{ t('common.actions.cancel') }}
        </router-link>
        <Button
          type="submit"
          :disabled="pending || !isValid"
          class="rounded-xl px-8"
        >
          <Loader2
            v-if="pending"
            class="w-4 h-4 mr-2"
          />
          {{ pending ? t('member.form.saving') : submitLabel }}
        </Button>
      </div>
    </form>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted, reactive, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRoute, useRouter } from 'vue-router';
import { isAxiosError } from 'axios';
import { ArrowLeft, Eye, EyeOff, Loader2 } from 'lucide-vue-next';
import MemberDirectoryService, { type MemberDirectoryDetail } from '@/modules/Member/services/memberDirectoryService';
import { MEMBER_TIMEZONE_OPTIONS } from '@/modules/Member/types/profile';
import { useLanguage } from '@/shared/composables/useLanguage';
import { PageHeader, ConsoleFormCard } from '@/shared/components/shell';
import {
    Button,
    Checkbox,
    Input,
    Textarea,
} from '@/shared/components/ui';
import MediaPicker from '@/shared/components/ui/MediaPicker.vue';
import { useToast } from '@/shared/composables/useToast';
import { parseSingleResponse } from '@/shared/utils/responseParser';
import type { Media } from '@/shared/types/media';

const props = defineProps<{
    mode: 'create' | 'edit';
}>();

const { t, locale } = useI18n();
const route = useRoute();
const router = useRouter();
const toast = useToast();
const { languages, loadLanguages } = useLanguage();

const timezoneOptions = MEMBER_TIMEZONE_OPTIONS;
const loading = ref(props.mode === 'edit');
const pending = ref(false);
const showPassword = ref(false);
const showConfirmPassword = ref(false);
const errors = reactive<Record<string, string>>({});
const accountMeta = ref<{ created_at?: string | null; last_login_at?: string | null } | null>(null);

const form = reactive({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    phone: '',
    avatar: null as string | null,
    bio: '',
    locale: '',
    timezone: '',
    status: 'active',
    verify_email: false,
});

const pageTitle = computed(() => (
    props.mode === 'create'
        ? t('member.form.createTitle')
        : t('member.form.editTitle')
));

const pageSubtitle = computed(() => (
    props.mode === 'create'
        ? t('member.form.createSubtitle')
        : t('member.form.editSubtitle')
));

const submitLabel = computed(() => (
    props.mode === 'create'
        ? t('common.actions.create')
        : t('common.actions.save')
));

const bioLength = computed(() => form.bio.length);

const initials = computed(() => {
    const raw = form.name.trim() || form.email || 'M';
    const parts = raw.split(/[\s@._-]+/).filter(Boolean);
    if (parts.length >= 2) {
        return `${parts[0]![0] ?? ''}${parts[1]![0] ?? ''}`.toUpperCase();
    }
    return raw.slice(0, 2).toUpperCase();
});

const isValid = computed(() => {
    if (!form.name.trim() || !form.email.trim()) {
        return false;
    }
    if (props.mode === 'create' && !form.password.trim()) {
        return false;
    }
    return true;
});

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

const onAvatarSelected = (media: Media): void => {
    form.avatar = media.url || null;
};

const firstError = (value: unknown): string => {
    if (Array.isArray(value) && typeof value[0] === 'string') {
        return value[0];
    }
    return typeof value === 'string' ? value : '';
};

const clearErrors = (): void => {
    Object.keys(errors).forEach((key) => {
        delete errors[key];
    });
};

const applyServerErrors = (payload: unknown): void => {
    clearErrors();
    if (!payload || typeof payload !== 'object') {
        return;
    }
    for (const [key, value] of Object.entries(payload as Record<string, unknown>)) {
        const message = firstError(value);
        if (message) {
            errors[key] = message;
        }
    }
};

const syncFromMember = (member: MemberDirectoryDetail): void => {
    form.name = member.name ?? '';
    form.email = member.email;
    form.phone = member.phone ?? '';
    form.avatar = member.avatar ?? null;
    form.bio = member.bio ?? '';
    form.locale = member.locale ?? '';
    form.timezone = member.timezone ?? '';
    form.status = member.status ?? 'active';
    form.verify_email = member.email_verified_at !== null;
    form.password = '';
    form.password_confirmation = '';
    accountMeta.value = {
        created_at: member.created_at,
        last_login_at: member.last_login_at,
    };
};

const load = async (): Promise<void> => {
    if (props.mode !== 'edit') {
        return;
    }
    loading.value = true;
    try {
        const response = await MemberDirectoryService.show(String(route.params.id));
        const member = parseSingleResponse<MemberDirectoryDetail>(response);
        if (member) {
            syncFromMember(member);
        }
    } catch (error: unknown) {
        toast.error.fromResponse(error);
        await router.replace({ name: 'members.index' });
    } finally {
        loading.value = false;
    }
};

const submit = async (): Promise<void> => {
    clearErrors();
    if (form.password || form.password_confirmation) {
        if (form.password !== form.password_confirmation) {
            errors.password_confirmation = t('member.form.passwordMismatch');
            return;
        }
    }

    pending.value = true;
    try {
        const payload: Record<string, unknown> = {
            name: form.name.trim(),
            email: form.email.trim(),
            phone: form.phone.trim() || null,
            avatar: form.avatar?.trim() || null,
            bio: form.bio.trim() || null,
            locale: form.locale.trim() || null,
            timezone: form.timezone.trim() || null,
            status: form.status,
            verify_email: form.verify_email,
        };
        if (form.password.trim()) {
            payload.password = form.password;
        }

        if (props.mode === 'create') {
            const response = await MemberDirectoryService.create(payload);
            const member = parseSingleResponse<MemberDirectoryDetail>(response);
            toast.success.create(t('member.title_singular'));
            if (member?.id) {
                await router.push({ name: 'members.show', params: { id: member.id } });
            } else {
                await router.push({ name: 'members.index' });
            }
            return;
        }

        await MemberDirectoryService.update(String(route.params.id), payload);
        toast.success.update(t('member.title_singular'));
        await router.push({ name: 'members.show', params: { id: String(route.params.id) } });
    } catch (error: unknown) {
        if (isAxiosError(error) && error.response?.status === 422) {
            applyServerErrors(error.response.data?.errors);
            return;
        }
        toast.error.fromResponse(error);
    } finally {
        pending.value = false;
    }
};

onMounted(() => {
    void loadLanguages();
    void load();
});
</script>
