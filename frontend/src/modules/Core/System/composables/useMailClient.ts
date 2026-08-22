import { ref, computed } from 'vue';
import { useToast } from '@/shared/composables/useToast';

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

export function useMailClient() {
    const toast = useToast();

    // Active folder & label navigation
    const activeFolder = ref<'inbox' | 'sent' | 'drafts' | 'trash' | 'spam'>('inbox');
    const activeLabel = ref<string | null>(null);
    const selectedMessageId = ref<string | null>('msg-1');
    const searchQuery = ref('');
    const filterType = ref<'all' | 'unread' | 'starred' | 'attachments'>('all');
    const isMobileDetailOpen = ref(false);

    // Labels
    const labels = ref<MailLabel[]>([
        { id: 'support', name: 'Support', color: 'bg-blue-500' },
        { id: 'urgent', name: 'Urgent', color: 'bg-rose-500' },
        { id: 'billing', name: 'Billing', color: 'bg-emerald-500' },
        { id: 'system', name: 'System Alerts', color: 'bg-amber-500' },
    ]);

    // Initial Mock & Live Messages
    const messages = ref<MailMessage[]>([
        {
            id: 'msg-1',
            folder: 'inbox',
            sender: {
                name: 'Security Operations',
                email: 'security@jejakawan.com',
            },
            recipients: ['admin@jejakawan.com'],
            subject: '🛡️ Automated Threat Intel & IP Blocking Summary',
            snippet: 'Weekly automated security report: 14 malicious botnets blocked, zero zero-day exploits detected on engine node-01.',
            body: `<p>Dear Administrator,</p>
<p>Here is your weekly summary from the <strong>Jejakawan Security Engine</strong>.</p>
<div style="background: rgba(16, 185, 129, 0.08); border-left: 4px solid #10B981; padding: 12px 16px; margin: 16px 0; border-radius: 4px;">
    <strong>System Health: 100% Optimal</strong><br/>
    All nodes passed integrity checks with 0 critical vulnerabilities.
</div>
<ul>
    <li><strong>Blocked Threats:</strong> 14 malicious IP addresses via AbuseIPDB feed.</li>
    <li><strong>WAF Filtered Requests:</strong> 1,280 bad bot requests blocked at edge.</li>
    <li><strong>2FA Enforcement:</strong> 100% compliant for all super-admin roles.</li>
</ul>
<p>You can review detailed intrusion logs directly on your <em>Security Dashboard</em>.</p>
<p>Best regards,<br/><strong>Jejakawan SecOps Team</strong></p>`,
            date: 'Today, 10:45 AM',
            isRead: false,
            isStarred: true,
            labels: ['system', 'urgent'],
            attachments: [
                { id: 'att-1', name: 'secops-audit-report.pdf', size: '2.4 MB', type: 'pdf' },
                { id: 'att-2', name: 'blocked-ip-list.csv', size: '42 KB', type: 'csv' },
            ],
        },
        {
            id: 'msg-2',
            folder: 'inbox',
            sender: {
                name: 'Acme Cloud Platform',
                email: 'billing@acmecloud.io',
            },
            recipients: ['admin@jejakawan.com'],
            subject: 'Invoice #INV-2026-088 for Cloud Infrastructure Services',
            snippet: 'Thank you for your business. Your monthly invoice for August 2026 is now available for download.',
            body: `<p>Hello Team,</p>
<p>Thank you for partnering with <strong>Acme Cloud Platform</strong>. Your invoice for the billing period <strong>August 2026</strong> has been processed successfully.</p>
<table style="width: 100%; border-collapse: collapse; margin: 16px 0;">
    <thead>
        <tr style="border-bottom: 2px solid #E5E7EB; text-align: left;">
            <th style="padding: 8px;">Service</th>
            <th style="padding: 8px;">Usage</th>
            <th style="padding: 8px; text-align: right;">Amount</th>
        </tr>
    </thead>
    <tbody>
        <tr style="border-bottom: 1px solid #E5E7EB;">
            <td style="padding: 8px;">High Performance Compute Node (8 vCPU)</td>
            <td style="padding: 8px;">744 Hours</td>
            <td style="padding: 8px; text-align: right;">$120.00</td>
        </tr>
        <tr style="border-bottom: 1px solid #E5E7EB;">
            <td style="padding: 8px;">NVMe Object Storage (500 GB)</td>
            <td style="padding: 8px;">Standard Tier</td>
            <td style="padding: 8px; text-align: right;">$25.00</td>
        </tr>
        <tr>
            <td colspan="2" style="padding: 8px; font-weight: bold;">Total Amount Paid</td>
            <td style="padding: 8px; font-weight: bold; text-align: right;">$145.00</td>
        </tr>
    </tbody>
</table>
<p>Please find the official PDF receipt attached to this message.</p>`,
            date: 'Yesterday, 3:20 PM',
            isRead: true,
            isStarred: false,
            labels: ['billing'],
            attachments: [
                { id: 'att-3', name: 'Invoice-INV-2026-088.pdf', size: '180 KB', type: 'pdf' },
            ],
        },
        {
            id: 'msg-3',
            folder: 'inbox',
            sender: {
                name: 'DevOps & CI/CD Pipeline',
                email: 'ci-bot@jejakawan.com',
            },
            recipients: ['admin@jejakawan.com'],
            subject: '🚀 Deployment Succeeded: Release v1.0.0-beta.1 to Production',
            snippet: 'Automated deployment workflow passed all 160 unit/feature test suites and finished in 42 seconds.',
            body: `<p>Deployment notification for <strong>ja-core_engine</strong>:</p>
<p>Commit: <code>e883e29</code> (feat: AI integration with 6 providers).<br/>
Environment: <strong>Production (Cluster-A)</strong></p>
<p>Test Suite Results: <strong>160 / 160 passed (972 assertions)</strong><br/>
Frontend Type Check: <strong>Passed (0 errors)</strong></p>
<p>All background workers and dev daemons have reloaded successfully.</p>`,
            date: '21 Aug 2026',
            isRead: true,
            isStarred: true,
            labels: ['system'],
        },
        {
            id: 'msg-4',
            folder: 'inbox',
            sender: {
                name: 'Sarah Jenkins',
                email: 'sarah.j@enterprise-client.com',
            },
            recipients: ['admin@jejakawan.com', 'support@jejakawan.com'],
            subject: 'Inquiry regarding Enterprise Multi-tenant White Labeling',
            snippet: 'Hi, we are evaluating Jejakawan Core Engine for our multinational team and would like to schedule a quick tech demo.',
            body: `<p>Hi Jejakawan Team,</p>
<p>We are currently assessing modern headless CMS & API engine platforms for our enterprise client portal.</p>
<p>We saw that you support <strong>Role-Based Access Control, SCIM, and Multi-model AI Copilot</strong> out of the box. Could we arrange a 30-minute technical demonstration call this Thursday?</p>
<p>Looking forward to hearing from you.</p>
<p>Warm regards,<br/><strong>Sarah Jenkins</strong><br/>Head of Digital Technology</p>`,
            date: '20 Aug 2026',
            isRead: false,
            isStarred: false,
            labels: ['support'],
        },
        {
            id: 'msg-5',
            folder: 'sent',
            sender: {
                name: 'Super Admin',
                email: 'admin@jejakawan.com',
            },
            recipients: ['sarah.j@enterprise-client.com'],
            subject: 'Re: Inquiry regarding Enterprise Multi-tenant White Labeling',
            snippet: 'Hi Sarah, thank you for reaching out! We would be delighted to host a walkthrough session for your team.',
            body: `<p>Hi Sarah,</p>
<p>Thank you for reaching out to us!</p>
<p>Thursday at 2:00 PM UTC works perfectly for our engineering leads. I will send a calendar invite with the meeting link shortly.</p>
<p>Best regards,<br/><strong>Jejakawan Core Team</strong></p>`,
            date: '20 Aug 2026',
            isRead: true,
            isStarred: false,
            labels: ['support'],
        },
        {
            id: 'msg-6',
            folder: 'drafts',
            sender: {
                name: 'Super Admin',
                email: 'admin@jejakawan.com',
            },
            recipients: ['team@jejakawan.com'],
            subject: '[Draft] Quarterly Roadmap: File Manager & Webmail Integration',
            snippet: 'Draft notes on upcoming modules including 3-column Webmail, AI Copilot, and IMAP synchronization.',
            body: `<p>Hi Team,</p>
<p>Here are the draft bullet points for our Q3 milestone roadmap:</p>
<ol>
    <li>File Manager with Recycle Bin and Drag & Drop</li>
    <li>AI Integration (Gemini, OpenAI, Claude, DeepSeek, Grok, OpenRouter)</li>
    <li>Webmail Client with rich text composing and attachment management</li>
</ol>`,
            date: '19 Aug 2026',
            isRead: true,
            isStarred: false,
            labels: [],
        },
        {
            id: 'msg-7',
            folder: 'trash',
            sender: {
                name: 'Newsletter Bot',
                email: 'updates@promotional-weekly.com',
            },
            recipients: ['admin@jejakawan.com'],
            subject: 'Weekly Tech Digest #410',
            snippet: 'Discover the top 10 trends in distributed microservices and reactive state management.',
            body: `<p>Here is your weekly digest of open source software releases.</p>`,
            date: '18 Aug 2026',
            isRead: true,
            isStarred: false,
            labels: [],
        },
    ]);

    // Computed filtered messages
    const filteredMessages = computed(() => {
        return messages.value.filter((msg) => {
            // Folder match
            if (activeLabel.value) {
                if (!msg.labels.includes(activeLabel.value)) return false;
            } else {
                if (msg.folder !== activeFolder.value) return false;
            }

            // Filter Type match
            if (filterType.value === 'unread' && msg.isRead) return false;
            if (filterType.value === 'starred' && !msg.isStarred) return false;
            if (filterType.value === 'attachments' && (!msg.attachments || msg.attachments.length === 0)) return false;

            // Search query match
            if (searchQuery.value.trim() !== '') {
                const q = searchQuery.value.toLowerCase();
                const matchSubject = msg.subject.toLowerCase().includes(q);
                const matchSender = msg.sender.name.toLowerCase().includes(q) || msg.sender.email.toLowerCase().includes(q);
                const matchSnippet = msg.snippet.toLowerCase().includes(q);
                if (!matchSubject && !matchSender && !matchSnippet) return false;
            }

            return true;
        });
    });

    // Unread count per folder
    const folderCounts = computed(() => {
        const counts = {
            inbox: 0,
            sent: 0,
            drafts: 0,
            trash: 0,
            spam: 0,
        };
        messages.value.forEach((m) => {
            if (!m.isRead && m.folder in counts) {
                counts[m.folder]++;
            }
        });
        return counts;
    });

    // Active selected message
    const selectedMessage = computed(() => {
        if (!selectedMessageId.value) return null;
        return messages.value.find((m) => m.id === selectedMessageId.value) || null;
    });

    // Actions
    const selectFolder = (folder: 'inbox' | 'sent' | 'drafts' | 'trash' | 'spam') => {
        activeFolder.value = folder;
        activeLabel.value = null;
        // Auto select first message in folder
        const first = messages.value.find((m) => m.folder === folder);
        selectedMessageId.value = first ? first.id : null;
        isMobileDetailOpen.value = false;
    };

    const selectLabel = (labelId: string) => {
        activeLabel.value = labelId;
        const first = messages.value.find((m) => m.labels.includes(labelId));
        selectedMessageId.value = first ? first.id : null;
        isMobileDetailOpen.value = false;
    };

    const selectMessage = (id: string) => {
        selectedMessageId.value = id;
        isMobileDetailOpen.value = true;
        const msg = messages.value.find((m) => m.id === id);
        if (msg && !msg.isRead) {
            msg.isRead = true;
        }
    };

    const toggleStar = (id: string, e?: Event) => {
        if (e) e.stopPropagation();
        const msg = messages.value.find((m) => m.id === id);
        if (msg) {
            msg.isStarred = !msg.isStarred;
        }
    };

    const markAsRead = (id: string, isRead = true) => {
        const msg = messages.value.find((m) => m.id === id);
        if (msg) {
            msg.isRead = isRead;
        }
    };

    const moveToTrash = (id: string) => {
        const msg = messages.value.find((m) => m.id === id);
        if (msg) {
            msg.folder = 'trash';
            toast.success.action('Message moved to Trash');
            // Select next message
            const remaining = filteredMessages.value.filter((m) => m.id !== id);
            selectedMessageId.value = remaining[0]?.id ?? null;
            isMobileDetailOpen.value = false;
        }
    };

    const restoreFromTrash = (id: string) => {
        const msg = messages.value.find((m) => m.id === id);
        if (msg) {
            msg.folder = 'inbox';
            toast.success.action('Message restored to Inbox');
        }
    };

    const deletePermanently = (id: string) => {
        messages.value = messages.value.filter((m) => m.id !== id);
        toast.success.action('Message permanently deleted');
        if (selectedMessageId.value === id) {
            selectedMessageId.value = filteredMessages.value[0]?.id ?? null;
            isMobileDetailOpen.value = false;
        }
    };

    // Composer State
    const isComposerOpen = ref(false);
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

    const sendEmail = async () => {
        if (!composerData.value.to) {
            toast.error.action('Please provide a recipient email address');
            return;
        }

        // Add message to Sent folder
        const newMsg: MailMessage = {
            id: `msg-${Date.now()}`,
            folder: 'sent',
            sender: {
                name: 'Super Admin',
                email: 'admin@jejakawan.com',
            },
            recipients: [composerData.value.to],
            subject: composerData.value.subject || '(No Subject)',
            snippet: composerData.value.body.replace(/<[^>]*>?/gm, '').slice(0, 100) || '(No content)',
            body: composerData.value.body || '<p>(No content)</p>',
            date: 'Just now',
            isRead: true,
            isStarred: false,
            labels: [],
        };

        messages.value.unshift(newMsg);
        toast.success.action('Email sent successfully!');
        isComposerOpen.value = false;
    };

    return {
        activeFolder,
        activeLabel,
        selectedMessageId,
        selectedMessage,
        searchQuery,
        filterType,
        isMobileDetailOpen,
        labels,
        messages,
        filteredMessages,
        folderCounts,
        selectFolder,
        selectLabel,
        selectMessage,
        toggleStar,
        markAsRead,
        moveToTrash,
        restoreFromTrash,
        deletePermanently,
        isComposerOpen,
        composerData,
        openComposer,
        reply,
        forward,
        sendEmail,
    };
}
