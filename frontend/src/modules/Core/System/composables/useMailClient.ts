import { ref, computed, onMounted } from 'vue';
import { useToast } from '@/shared/composables/useToast';
import api from '@/engine/api/client';

export interface MailAttachment {
    id: string;
    name: string;
    size: string;
    type: string;
    url?: string;
}

export interface MailMessage {
    id: string;
    folder: 'inbox' | 'sent' | 'drafts' | 'trash' | 'spam';
    sender: {
        name: string;
        email: string;
        avatar?: string;
    };
    recipients: string[];
    cc?: string[];
    bcc?: string[];
    subject: string;
    snippet: string;
    body: string;
    date: string;
    isRead: boolean;
    isStarred: boolean;
    labels: string[];
    attachments?: MailAttachment[];
}

export interface MailLabel {
    id: string;
    name: string;
    color: string;
}

export interface MailTemplate {
    id: string;
    title: string;
    snippet: string;
    body: string;
}

export function useMailClient() {
    const toast = useToast();

    // Templates State
    const templates = ref<MailTemplate[]>([
        {
            id: 'tpl_meeting',
            title: 'Meeting Confirmation',
            snippet: 'Hi, confirming our meeting scheduled for...',
            body: 'Hi,\n\nThis is to confirm our meeting scheduled for [Date & Time]. Please let me know if you need to adjust the schedule or add additional attendees.\n\nLooking forward to speaking with you.\n\nBest regards,',
        },
        {
            id: 'tpl_ack',
            title: 'General Acknowledgment',
            snippet: 'Thank you for reaching out. We have received...',
            body: 'Hi,\n\nThank you for reaching out. We have received your message and our team is currently reviewing it. We will get back to you with an update shortly.\n\nBest regards,',
        },
        {
            id: 'tpl_quote',
            title: 'Price Quotation & Proposal',
            snippet: 'Please find attached our formal quotation...',
            body: 'Dear Client,\n\nThank you for your interest in our services. Please find attached our formal quotation and project scope for your review.\n\nFeel free to reach out if you have any questions.\n\nBest regards,',
        },
        {
            id: 'tpl_support',
            title: 'Technical Support Inquiry',
            snippet: 'Could you please provide account details...',
            body: 'Hello,\n\nThank you for contacting technical support. To help us resolve this swiftly, could you please provide your account email and a screenshot/log of the issue?\n\nThank you for your patience.\n\nBest regards,',
        },
        {
            id: 'tpl_followup',
            title: 'Follow-up Check-in',
            snippet: 'Quick follow-up on my previous message...',
            body: 'Hi,\n\nI wanted to quickly follow up on my previous message regarding [Subject]. Please let me know if you need any additional information from our side.\n\nBest regards,',
        },
    ]);

    const fetchTemplates = async () => {
        try {
            const res = await api.get('/manage/mail/templates');
            const data = res.data?.data || res.data;
            if (Array.isArray(data) && data.length > 0) {
                templates.value = data;
            }
        } catch {
            // Keep default templates
        }
    };

    const saveTemplates = async (newTemplates: MailTemplate[]) => {
        try {
            await api.post('/manage/mail/templates', { templates: newTemplates });
            templates.value = newTemplates;
            toast.success.action('Templates saved successfully');
        } catch (error: unknown) {
            toast.error.fromResponse(error);
        }
    };

    // Sidebar state
    const isSidebarMinimized = ref(false);
    const toggleSidebarMinimize = () => {
        isSidebarMinimized.value = !isSidebarMinimized.value;
    };

    // Navigation & Filtering
    const activeFolder = ref<'inbox' | 'sent' | 'drafts' | 'trash' | 'spam'>('inbox');
    const activeLabel = ref<string | null>(null);
    const selectedMessageId = ref<string | null>(null);
    const searchQuery = ref('');
    const filterType = ref<'all' | 'unread' | 'starred' | 'attachments'>('all');
    const isMobileDetailOpen = ref(false);
    const loading = ref(false);
    const syncing = ref(false);

    // Pagination
    const currentPage = ref(1);
    const lastPage = ref(1);
    const totalMessages = ref(0);
    const fromRange = ref(0);
    const toRange = ref(0);
    const perPage = ref(25);

    // Storage Quota Stats
    const storageStats = ref({
        used_bytes: 24576,
        quota_bytes: 16106127360,
        used_formatted: '24 KB',
        quota_formatted: '15 GB',
        percentage: 0.01,
    });

    // Modals
    const isSettingsOpen = ref(false);
    const isLabelsModalOpen = ref(false);
    const isComposerOpen = ref(false);

    // Labels
    const labels = ref<MailLabel[]>([
        { id: 'support', name: 'Support', color: 'bg-blue-500' },
        { id: 'urgent', name: 'Urgent', color: 'bg-rose-500' },
        { id: 'billing', name: 'Billing', color: 'bg-emerald-500' },
        { id: 'system', name: 'System Alerts', color: 'bg-amber-500' },
    ]);

    // Messages list & folder counts
    const messages = ref<MailMessage[]>([]);
    const folderCounts = ref<Record<string, number>>({
        inbox: 0,
        sent: 0,
        drafts: 0,
        trash: 0,
        spam: 0,
    });

    const formatMessageDate = (dateStr: string | null | undefined): string => {
        if (!dateStr) return '';
        const d = new Date(dateStr);
        const now = new Date();
        if (d.toDateString() === now.toDateString()) {
            return d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        }
        return d.toLocaleDateString([], { month: 'short', day: 'numeric' });
    };

    // Transform backend message model to frontend interface
    const transformMessage = (item: any): MailMessage => {
        return {
            id: item.id,
            folder: item.folder || 'inbox',
            sender: {
                name: item.sender_name || 'Unknown',
                email: item.sender_email || '',
                avatar: item.sender_name ? item.sender_name.charAt(0).toUpperCase() : 'M',
            },
            recipients: Array.isArray(item.recipients) ? item.recipients : [item.recipients || ''],
            cc: Array.isArray(item.cc) ? item.cc : [],
            bcc: Array.isArray(item.bcc) ? item.bcc : [],
            subject: item.subject || '(No Subject)',
            snippet: item.snippet || '',
            body: item.body || '',
            date: formatMessageDate(item.sent_at || item.received_at || item.created_at),
            isRead: Boolean(item.is_read),
            isStarred: Boolean(item.is_starred),
            labels: Array.isArray(item.labels) ? item.labels : [],
            attachments: Array.isArray(item.attachments) ? item.attachments : [],
        };
    };

    // Fetch labels from API
    const fetchLabels = async () => {
        try {
            const res = await api.get('/manage/mail/labels');
            const data = res.data?.data || res.data;
            if (Array.isArray(data) && data.length > 0) {
                labels.value = data;
            }
        } catch {
            // Keep default labels
        }
    };

    // Fetch settings preference
    const fetchClientSettings = async () => {
        try {
            const res = await api.get('/manage/mail/settings');
            const data = res.data?.data || res.data;
            if (data?.per_page && typeof data.per_page === 'number') {
                perPage.value = data.per_page;
            }
        } catch {
            // Keep default
        }
    };

    // Fetch messages from backend API
    const fetchMessages = async (page: number = currentPage.value) => {
        loading.value = true;
        try {
            const params: Record<string, string | number> = {
                folder: activeFolder.value,
                filter: filterType.value,
                page,
                per_page: perPage.value,
            };
            if (activeLabel.value) {
                params.label = activeLabel.value;
            }
            if (searchQuery.value.trim() !== '') {
                params.q = searchQuery.value.trim();
            }

            const response = await api.get('/manage/mail/messages', { params });
            const data = response.data?.data || response.data;
            const items = data?.items || [];
            messages.value = items.map(transformMessage);

            currentPage.value = data?.current_page || page;
            lastPage.value = data?.last_page || 1;
            totalMessages.value = data?.total || 0;
            fromRange.value = data?.from || 0;
            toRange.value = data?.to || 0;

            if (data?.folder_counts) {
                folderCounts.value = { ...folderCounts.value, ...data.folder_counts };
            }

            if (data?.storage) {
                storageStats.value = data.storage;
            }

            // Auto select first message if nothing selected
            if (messages.value.length > 0 && !selectedMessageId.value) {
                selectedMessageId.value = messages.value[0]?.id ?? null;
            } else if (messages.value.length === 0) {
                selectedMessageId.value = null;
            }
        } catch (error: unknown) {
            toast.error.fromResponse(error);
        } finally {
            loading.value = false;
        }
    };

    const nextPage = () => {
        if (currentPage.value < lastPage.value) {
            fetchMessages(currentPage.value + 1);
        }
    };

    const prevPage = () => {
        if (currentPage.value > 1) {
            fetchMessages(currentPage.value - 1);
        }
    };

    // Synchronize mailbox
    const syncMailbox = async () => {
        syncing.value = true;
        try {
            await api.post('/manage/mail/sync');
            await fetchMessages(1);
            toast.success.action('Mailbox synchronized');
        } catch (error: unknown) {
            toast.error.fromResponse(error);
        } finally {
            syncing.value = false;
        }
    };

    // Active selected message computed
    const selectedMessage = computed(() => {
        if (!selectedMessageId.value) return null;
        return messages.value.find((m) => m.id === selectedMessageId.value) || null;
    });

    const selectFolder = (folder: 'inbox' | 'sent' | 'drafts' | 'trash' | 'spam') => {
        activeFolder.value = folder;
        activeLabel.value = null;
        selectedMessageId.value = null;
        isMobileDetailOpen.value = false;
        currentPage.value = 1;
        fetchMessages(1);
    };

    const selectLabel = (labelId: string) => {
        activeLabel.value = labelId;
        selectedMessageId.value = null;
        isMobileDetailOpen.value = false;
        currentPage.value = 1;
        fetchMessages(1);
    };

    const selectMessage = async (id: string) => {
        selectedMessageId.value = id;
        isMobileDetailOpen.value = true;
        const msg = messages.value.find((m) => m.id === id);
        if (msg && !msg.isRead) {
            msg.isRead = true;
            const cur = folderCounts.value[activeFolder.value] ?? 0;
            if (cur > 0) {
                folderCounts.value[activeFolder.value] = cur - 1;
            }
            try {
                await api.patch(`/manage/mail/messages/${id}/read`, { is_read: true });
            } catch {
                // Background update failure ignored
            }
        }
    };

    const toggleStar = async (id: string, e?: Event) => {
        if (e) e.stopPropagation();
        const msg = messages.value.find((m) => m.id === id);
        if (msg) {
            msg.isStarred = !msg.isStarred;
            try {
                await api.patch(`/manage/mail/messages/${id}/star`);
            } catch (error: unknown) {
                msg.isStarred = !msg.isStarred;
                toast.error.fromResponse(error);
            }
        }
    };

    const moveMessage = async (id: string, targetFolder: string) => {
        try {
            await api.post(`/manage/mail/messages/${id}/move`, { folder: targetFolder });
            messages.value = messages.value.filter((m) => m.id !== id);
            toast.success.action(`Message moved to ${targetFolder}`);
            selectedMessageId.value = messages.value[0]?.id ?? null;
            isMobileDetailOpen.value = false;
            fetchMessages();
        } catch (error: unknown) {
            toast.error.fromResponse(error);
        }
    };

    const toggleMessageLabel = async (id: string, labelId: string) => {
        try {
            const res = await api.post(`/manage/mail/messages/${id}/label`, { label: labelId });
            const newLabels = res.data?.data?.labels || [];
            const msg = messages.value.find((m) => m.id === id);
            if (msg) {
                msg.labels = newLabels;
            }
            toast.success.action('Labels updated');
        } catch (error: unknown) {
            toast.error.fromResponse(error);
        }
    };

    const moveToTrash = async (id: string) => {
        try {
            await api.delete(`/manage/mail/messages/${id}/trash`);
            messages.value = messages.value.filter((m) => m.id !== id);
            toast.success.action('Message moved to Trash');
            selectedMessageId.value = messages.value[0]?.id ?? null;
            isMobileDetailOpen.value = false;
            fetchMessages();
        } catch (error: unknown) {
            toast.error.fromResponse(error);
        }
    };

    const restoreFromTrash = async (id: string) => {
        try {
            await api.post(`/manage/mail/messages/${id}/restore`);
            messages.value = messages.value.filter((m) => m.id !== id);
            toast.success.action('Message restored to Inbox');
            selectedMessageId.value = messages.value[0]?.id ?? null;
            isMobileDetailOpen.value = false;
            fetchMessages();
        } catch (error: unknown) {
            toast.error.fromResponse(error);
        }
    };

    const deletePermanently = async (id: string) => {
        try {
            await api.delete(`/manage/mail/messages/${id}`);
            messages.value = messages.value.filter((m) => m.id !== id);
            toast.success.action('Message permanently deleted');
            selectedMessageId.value = messages.value[0]?.id ?? null;
            isMobileDetailOpen.value = false;
        } catch (error: unknown) {
            toast.error.fromResponse(error);
        }
    };

    const emptyTrash = async () => {
        try {
            await api.delete('/manage/mail/trash/empty');
            messages.value = [];
            selectedMessageId.value = null;
            toast.success.action('Trash folder emptied');
            fetchMessages(1);
        } catch (error: unknown) {
            toast.error.fromResponse(error);
        }
    };

    // Composer State
    const composerData = ref<{
        to: string;
        cc: string;
        bcc: string;
        subject: string;
        body: string;
        attachments: File[];
    }>({
        to: '',
        cc: '',
        bcc: '',
        subject: '',
        body: '',
        attachments: [],
    });

    const openComposer = (initial?: { to?: string; subject?: string; body?: string }) => {
        composerData.value = {
            to: initial?.to || '',
            cc: '',
            bcc: '',
            subject: initial?.subject || '',
            body: initial?.body || '',
            attachments: [],
        };
        isComposerOpen.value = true;
    };

    const reply = (message: MailMessage) => {
        openComposer({
            to: message.sender.email,
            subject: message.subject.startsWith('Re:') ? message.subject : `Re: ${message.subject}`,
            body: `<br/><br/><blockquote>On ${message.date}, ${message.sender.name} wrote:<br/>${message.snippet}</blockquote>`,
        });
    };

    const forward = (message: MailMessage) => {
        openComposer({
            to: '',
            subject: message.subject.startsWith('Fwd:') ? message.subject : `Fwd: ${message.subject}`,
            body: `<br/><br/>---------- Forwarded message ---------<br/>From: ${message.sender.name} &lt;${message.sender.email}&gt;<br/>Date: ${message.date}<br/>Subject: ${message.subject}<br/><br/>${message.body}`,
        });
    };

    // Undo Send State
    const isUndoToastVisible = ref(false);
    const undoCountdown = ref(5);
    let undoInterval: any = null;
    const pendingEmailData = ref<{
        to: string;
        cc: string;
        bcc: string;
        subject: string;
        body: string;
    } | null>(null);

    const executeActualSend = async (payload: { to: string; cc: string; bcc: string; subject: string; body: string }) => {
        try {
            await api.post('/manage/mail/send', payload);
            toast.success.action('Email delivered successfully');
            fetchMessages(1);
        } catch (error: unknown) {
            toast.error.fromResponse(error);
        } finally {
            pendingEmailData.value = null;
        }
    };

    const sendEmail = () => {
        if (!composerData.value.to.trim()) {
            toast.error.action('Please provide a recipient email address');
            return;
        }

        const payload = {
            to: composerData.value.to,
            cc: composerData.value.cc,
            bcc: composerData.value.bcc,
            subject: composerData.value.subject,
            body: composerData.value.body,
        };

        pendingEmailData.value = payload;
        isComposerOpen.value = false;
        isUndoToastVisible.value = true;
        undoCountdown.value = 5;

        if (undoInterval) clearInterval(undoInterval);

        undoInterval = setInterval(() => {
            undoCountdown.value -= 1;
            if (undoCountdown.value <= 0) {
                clearInterval(undoInterval);
                undoInterval = null;
                isUndoToastVisible.value = false;
                if (pendingEmailData.value) {
                    executeActualSend(pendingEmailData.value);
                }
            }
        }, 1000);
    };

    const undoSend = () => {
        if (undoInterval) {
            clearInterval(undoInterval);
            undoInterval = null;
        }
        isUndoToastVisible.value = false;
        if (pendingEmailData.value) {
            composerData.value = {
                ...pendingEmailData.value,
                attachments: [],
            };
            isComposerOpen.value = true;
            pendingEmailData.value = null;
            toast.info('Sending cancelled', 'Draft restored.');
        }
    };

    const sendNow = () => {
        if (undoInterval) {
            clearInterval(undoInterval);
            undoInterval = null;
        }
        isUndoToastVisible.value = false;
        if (pendingEmailData.value) {
            executeActualSend(pendingEmailData.value);
        }
    };

    onMounted(async () => {
        await fetchClientSettings();
        fetchLabels();
        fetchTemplates();
        fetchMessages(1);
    });

    return {
        isSidebarMinimized,
        toggleSidebarMinimize,
        activeFolder,
        activeLabel,
        selectedMessageId,
        selectedMessage,
        searchQuery,
        filterType,
        isMobileDetailOpen,
        currentPage,
        lastPage,
        totalMessages,
        fromRange,
        toRange,
        perPage,
        nextPage,
        prevPage,
        labels,
        templates,
        messages,
        folderCounts,
        storageStats,
        loading,
        syncing,
        isSettingsOpen,
        isLabelsModalOpen,
        isComposerOpen,
        composerData,
        isUndoToastVisible,
        undoCountdown,
        undoSend,
        sendNow,
        fetchMessages,
        syncMailbox,
        selectFolder,
        selectLabel,
        selectMessage,
        toggleStar,
        moveMessage,
        toggleMessageLabel,
        moveToTrash,
        restoreFromTrash,
        deletePermanently,
        emptyTrash,
        openComposer,
        reply,
        forward,
        sendEmail,
        fetchTemplates,
        saveTemplates,
    };
}
