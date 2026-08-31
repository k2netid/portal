<template>
  <div class="space-y-6 max-w-3xl">
    <PageHeader
      borderless
      :title="pageTitle"
      :subtitle="pageSubtitle"
    >
      <template #actions>
        <Button
          variant="ghost"
          size="sm"
          @click="router.back()"
        >
          {{ t('member.form.back') }}
        </Button>
      </template>
    </PageHeader>

    <form
      class="space-y-6"
      @submit.prevent="submit"
    >
      <ConsoleFormCard :title="t('member.form.identity')">
        <div class="space-y-4">
          <label class="block space-y-1.5 text-sm">
            <span class="font-medium">{{ t('member.form.name') }} <span class="text-destructive">*</span></span>
            <Input
              v-model="form.name"
              required
              maxlength="255"
            />
          </label>
          <label class="block space-y-1.5 text-sm">
            <span class="font-medium">{{ t('member.form.email') }} <span class="text-destructive">*</span></span>
            <Input
              v-model="form.email"
              type="email"
              required
              maxlength="255"
            />
          </label>
          <label class="block space-y-1.5 text-sm">
            <span class="font-medium">
              {{ t('member.form.password') }}
              <span
                v-if="mode === 'create'"
                class="text-destructive"
              >*</span>
            </span>
            <Input
              v-model="form.password"
              type="password"
              :required="mode === 'create'"
              minlength="8"
              autocomplete="new-password"
              :placeholder="mode === 'edit' ? t('member.form.passwordHint') : ''"
            />
          </label>
          <label class="block space-y-1.5 text-sm">
            <span class="font-medium">{{ t('member.form.phone') }}</span>
            <Input
              v-model="form.phone"
              type="tel"
              maxlength="32"
            />
          </label>
          <label
            v-if="mode === 'edit'"
            class="block space-y-1.5 text-sm"
          >
            <span class="font-medium">{{ t('member.portal.profile.bio') }}</span>
            <textarea
              v-model="form.bio"
              rows="3"
              maxlength="500"
              class="w-full rounded-lg border border-border bg-background px-3 py-2.5 resize-y min-h-[5rem]"
            />
          </label>
        </div>
      </ConsoleFormCard>

      <ConsoleFormCard :title="t('member.form.account')">
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
          <label class="flex items-center gap-2 text-sm">
            <Checkbox
              :checked="form.verify_email"
              @update:checked="(val: boolean) => form.verify_email = val"
            />
            <span>{{ t('member.form.verifyEmail') }}</span>
          </label>
        </div>
      </ConsoleFormCard>

      <div class="flex flex-wrap gap-3">
        <Button
          type="submit"
          :disabled="pending"
        >
          {{ pending ? t('member.form.saving') : submitLabel }}
        </Button>
        <Button
          type="button"
          variant="outline"
          @click="router.back()"
        >
          {{ t('common.actions.cancel') }}
        </Button>
      </div>
    </form>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted, reactive, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRoute, useRouter } from 'vue-router';
import MemberDirectoryService, { type MemberDirectoryDetail } from '@/modules/Member/services/memberDirectoryService';
import { PageHeader, ConsoleFormCard } from '@/shared/components/shell';
import { Button, Checkbox, Input } from '@/shared/components/ui';
import { useToast } from '@/shared/composables/useToast';
import { parseSingleResponse } from '@/shared/utils/responseParser';

const props = defineProps<{
    mode: 'create' | 'edit';
}>();

const { t } = useI18n();
const route = useRoute();
const router = useRouter();
const toast = useToast();

const pending = ref(false);
const form = reactive({
    name: '',
    email: '',
    password: '',
    phone: '',
    bio: '',
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
        ? t('member.form.createSubmit')
        : t('member.form.editSubmit')
));

const syncFromMember = (member: MemberDirectoryDetail): void => {
    form.name = member.name ?? '';
    form.email = member.email;
    form.phone = member.phone ?? '';
    form.bio = member.bio ?? '';
    form.status = member.status ?? 'active';
    form.verify_email = member.email_verified_at !== null;
    form.password = '';
};

const load = async (): Promise<void> => {
    if (props.mode !== 'edit') {
        return;
    }
    try {
        const response = await MemberDirectoryService.show(String(route.params.id));
        const member = parseSingleResponse<MemberDirectoryDetail>(response);
        if (member) {
            syncFromMember(member);
        }
    } catch (error: unknown) {
        toast.error.fromResponse(error);
        await router.replace({ name: 'members.index' });
    }
};

const submit = async (): Promise<void> => {
    pending.value = true;
    try {
        const payload: Record<string, unknown> = {
            name: form.name.trim(),
            email: form.email.trim(),
            phone: form.phone.trim() || null,
            status: form.status,
            verify_email: form.verify_email,
        };
        if (props.mode === 'edit') {
            payload.bio = form.bio.trim() || null;
        }
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
        toast.error.fromResponse(error);
    } finally {
        pending.value = false;
    }
};

onMounted(() => {
    void load();
});
</script>
