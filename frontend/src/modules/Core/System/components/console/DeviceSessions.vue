<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { MonitorSmartphone, Trash2, ShieldAlert } from 'lucide-vue-next';
import { Button, Badge } from '@/shared/components/ui';
import api from '@/engine/api/client';
import { useToast } from '@/shared/composables/useToast';
import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';

dayjs.extend(relativeTime);

const { t } = useI18n();
const toast = useToast();

interface Session {
    id: string;
    name: string;
    abilities: string[];
    last_used_at: string | null;
    created_at: string;
    is_current: boolean;
}

const sessions = ref<Session[]>([]);
const loading = ref(true);

const fetchSessions = async () => {
    loading.value = true;
    try {
        const response = await api.get('/manage/system/profile/sessions');
        const data = response.data;
        sessions.value = Array.isArray(data) ? data : [];
    } catch (e) {
        toast.error.load(e);
    } finally {
        loading.value = false;
    }
};

const revokeSession = async (session: Session) => {
    if (!window.confirm(t('system.profile.sessions.confirmRevoke'))) return;
    try {
        await api.delete(`/manage/system/profile/sessions/${session.id}`);
        toast.success.default(t('system.profile.sessions.revoked'));
        await fetchSessions();
    } catch (e) {
        toast.error.delete(e);
    }
};

onMounted(fetchSessions);
</script>

<template>
    <div class="space-y-6">
        <p class="text-sm text-muted-foreground">{{ t('system.profile.sessions.description') }}</p>

        <div v-if="loading" class="flex justify-center p-8 text-muted-foreground text-sm">
            {{ t('system.profile.sessions.loading') }}
        </div>

        <div v-else-if="sessions.length === 0" class="text-center p-8 text-muted-foreground">
            <ShieldAlert class="mx-auto h-12 w-12 text-muted-foreground/50 mb-4" />
            <p>{{ t('system.profile.sessions.empty') }}</p>
        </div>

        <div v-else class="space-y-4">
            <div
                v-for="session in sessions"
                :key="session.id"
                class="flex items-center justify-between p-4 border rounded-lg bg-card"
            >
                <div class="flex items-start gap-4">
                    <MonitorSmartphone class="w-8 h-8 text-primary mt-1 shrink-0" />
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <h4 class="font-medium text-sm">{{ session.name }}</h4>
                            <Badge
                                v-if="session.is_current"
                                variant="default"
                                class="bg-primary/20 text-primary hover:bg-primary/20 border-none"
                            >
                                {{ t('system.profile.sessions.current') }}
                            </Badge>
                        </div>
                        <p class="text-xs text-muted-foreground mt-1">
                            {{ t('system.profile.sessions.lastActive') }}:
                            {{ session.last_used_at ? dayjs(session.last_used_at).fromNow() : '—' }}
                        </p>
                        <p class="text-xs text-muted-foreground">
                            {{ t('system.profile.sessions.started') }}:
                            {{ dayjs(session.created_at).format('MMM D, YYYY h:mm A') }}
                        </p>
                    </div>
                </div>
                <Button
                    v-if="!session.is_current"
                    variant="destructive"
                    size="sm"
                    :title="t('system.profile.sessions.revoke')"
                    @click="revokeSession(session)"
                >
                    <Trash2 class="w-4 h-4 mr-2" />
                    {{ t('system.profile.sessions.revoke') }}
                </Button>
            </div>
        </div>
    </div>
</template>
