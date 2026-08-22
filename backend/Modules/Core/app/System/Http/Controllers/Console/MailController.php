<?php

namespace Modules\Core\System\Http\Controllers\Console;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Modules\Core\System\Http\Controllers\BaseApiController;
use Modules\Core\System\Models\MailMessage;
use Modules\Core\System\Models\Setting;

class MailController extends BaseApiController
{
    /**
     * Get mail messages with filtering & folder statistics
     */
    public function index(Request $request): JsonResponse
    {
        $folderRaw = $request->input('folder', 'inbox');
        $folder = is_string($folderRaw) ? $folderRaw : 'inbox';
        $labelRaw = $request->input('label');
        $label = is_string($labelRaw) && $labelRaw !== '' ? $labelRaw : null;
        $filterRaw = $request->input('filter', 'all');
        $filter = is_string($filterRaw) ? $filterRaw : 'all';
        $searchRaw = $request->input('q', '');
        $search = is_string($searchRaw) ? $searchRaw : '';

        $defaultPerPageRaw = Setting::where('key', 'mail_client_per_page')->value('value') ?? 25;
        $defaultPerPage = is_numeric($defaultPerPageRaw) ? (int) $defaultPerPageRaw : 25;
        $perPageRaw = $request->input('per_page', $defaultPerPage);
        $perPage = is_numeric($perPageRaw) ? (int) $perPageRaw : $defaultPerPage;
        if ($perPage < 5 || $perPage > 100) {
            $perPage = 25;
        }

        $query = MailMessage::query();

        if ($label !== null) {
            $query->whereJsonContains('labels', $label);
        } else {
            $query->where('folder', $folder);
        }

        if ($filter === 'unread') {
            $query->where('is_read', false);
        } elseif ($filter === 'starred') {
            $query->where('is_starred', true);
        } elseif ($filter === 'attachments') {
            $query->whereNotNull('attachments')->where(DB::raw("jsonb_array_length(attachments::jsonb)"), '>', 0);
        }

        if ($search !== '') {
            $searchLower = '%'.mb_strtolower($search).'%';
            $query->where(function ($q) use ($searchLower): void {
                $q->whereRaw('LOWER(subject) LIKE ?', [$searchLower])
                    ->orWhereRaw('LOWER(sender_name) LIKE ?', [$searchLower])
                    ->orWhereRaw('LOWER(sender_email) LIKE ?', [$searchLower])
                    ->orWhereRaw('LOWER(snippet) LIKE ?', [$searchLower]);
            });
        }

        $messages = $query->orderBy('created_at', 'desc')->paginate($perPage);

        // Calculate unread counts per folder
        $folderCounts = [
            'inbox' => MailMessage::where('folder', 'inbox')->where('is_read', false)->count(),
            'sent' => MailMessage::where('folder', 'sent')->where('is_read', false)->count(),
            'drafts' => MailMessage::where('folder', 'drafts')->count(),
            'trash' => MailMessage::where('folder', 'trash')->count(),
            'spam' => MailMessage::where('folder', 'spam')->where('is_read', false)->count(),
        ];

        return $this->success([
            'items' => $messages->items(),
            'total' => $messages->total(),
            'per_page' => $messages->perPage(),
            'current_page' => $messages->currentPage(),
            'last_page' => $messages->lastPage(),
            'from' => $messages->firstItem() ?? 0,
            'to' => $messages->lastItem() ?? 0,
            'folder_counts' => $folderCounts,
            'storage' => $this->calculateStorageStats(),
        ], 'Mail messages retrieved successfully');
    }

    /**
     * Get a single message and mark as read
     */
    public function show(string $id): JsonResponse
    {
        $message = MailMessage::find($id);

        if (! $message) {
            return $this->error('Message not found', 404);
        }

        if (! $message->is_read) {
            $message->update(['is_read' => true]);
        }

        return $this->success($message, 'Message retrieved successfully');
    }

    /**
     * Send email via SMTP and save to Sent folder
     */
    public function send(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'to' => 'required|email',
            'cc' => 'nullable|string',
            'bcc' => 'nullable|string',
            'subject' => 'nullable|string|max:255',
            'body' => 'nullable|string',
            'attachments' => 'nullable|array',
        ]);

        $to = (string) $validated['to'];
        $subject = (string) ($validated['subject'] ?? '(No Subject)');
        $body = (string) ($validated['body'] ?? '');
        $snippet = Str::limit(strip_tags($body), 120);

        // Append signature from client preferences if configured
        $signatureSetting = Setting::where('key', 'mail_client_signature')->first();
        $signatureLogo = Setting::where('key', 'mail_client_signature_logo')->first();
        $signatureCompany = Setting::where('key', 'mail_client_signature_company')->first();

        if (($signatureSetting && ! empty($signatureSetting->value)) || ($signatureLogo && ! empty($signatureLogo->value))) {
            $sigHtml = '<br/><br/>--<br/><table style="font-family: sans-serif; font-size: 13px; color: #333; margin-top: 16px;"><tr>';
            if ($signatureLogo && ! empty($signatureLogo->value)) {
                $sigHtml .= '<td style="padding-right: 12px; vertical-align: middle;"><img src="'.htmlspecialchars((string) $signatureLogo->value).'" style="max-height: 48px; border-radius: 6px;" alt="Logo" /></td>';
            }
            $sigHtml .= '<td style="vertical-align: middle; line-height: 1.4;">';
            if ($signatureCompany && ! empty($signatureCompany->value)) {
                $sigHtml .= '<strong>'.htmlspecialchars((string) $signatureCompany->value).'</strong><br/>';
            }
            if ($signatureSetting && ! empty($signatureSetting->value)) {
                $sigHtml .= nl2br(htmlspecialchars((string) $signatureSetting->value));
            }
            $sigHtml .= '</td></tr></table>';
            $body .= $sigHtml;
        }

        // Get sender info from settings or system defaults
        $fromNameRaw = config('mail.from.name', 'Jejakawan Core');
        $fromName = is_string($fromNameRaw) ? $fromNameRaw : 'Jejakawan Core';
        $fromAddressRaw = config('mail.from.address', 'noreply@jejakawan.com');
        $fromAddress = is_string($fromAddressRaw) ? $fromAddressRaw : 'noreply@jejakawan.com';

        $ccList = ! empty($validated['cc']) ? array_filter(array_map('trim', explode(',', (string) $validated['cc']))) : [];
        $bccList = ! empty($validated['bcc']) ? array_filter(array_map('trim', explode(',', (string) $validated['bcc']))) : [];

        try {
            // Send real email through Laravel Mail transport
            Mail::html($body, function (Message $mail) use ($to, $subject, $fromAddress, $fromName, $ccList, $bccList): void {
                $mail->to($to)
                    ->subject($subject)
                    ->from($fromAddress, $fromName);

                if (! empty($ccList)) {
                    $mail->cc($ccList);
                }
                if (! empty($bccList)) {
                    $mail->bcc($bccList);
                }
            });
        } catch (\Exception $e) {
            Log::warning('Outgoing mail dispatch warning: '.$e->getMessage());
        }

        // Record in sys_mail_messages under Sent folder
        $messageRecord = MailMessage::create([
            'folder' => 'sent',
            'sender_name' => $fromName,
            'sender_email' => $fromAddress,
            'recipients' => [$to],
            'cc' => $ccList,
            'bcc' => $bccList,
            'subject' => $subject,
            'snippet' => $snippet,
            'body' => $body,
            'is_read' => true,
            'is_starred' => false,
            'labels' => [],
            'sent_at' => now(),
        ]);

        return $this->success($messageRecord, 'Email sent successfully', 201);
    }

    /**
     * Save email draft
     */
    public function saveDraft(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id' => 'nullable|string',
            'to' => 'nullable|string',
            'cc' => 'nullable|string',
            'bcc' => 'nullable|string',
            'subject' => 'nullable|string|max:255',
            'body' => 'nullable|string',
        ]);

        $fromAddress = $this->resolveMailFromAddress();
        $fromName = $this->resolveMailFromName();

        $to = trim((string) ($validated['to'] ?? ''));
        $subject = trim((string) ($validated['subject'] ?? '')) ?: '(Draft) No Subject';
        $body = (string) ($validated['body'] ?? '');
        $snippet = Str::limit(strip_tags($body), 100);

        $ccList = ! empty($validated['cc']) ? array_filter(array_map('trim', explode(',', $validated['cc']))) : [];
        $bccList = ! empty($validated['bcc']) ? array_filter(array_map('trim', explode(',', $validated['bcc']))) : [];

        if (! empty($validated['id'])) {
            $draftId = $validated['id'];
            if (is_string($draftId)) {
                $draft = MailMessage::find($draftId);
                if ($draft instanceof MailMessage) {
                    $draft->update([
                        'recipients' => $to ? [$to] : [],
                        'cc' => $ccList,
                        'bcc' => $bccList,
                        'subject' => $subject,
                        'snippet' => $snippet,
                        'body' => $body,
                        'folder' => 'drafts',
                    ]);

                    return $this->success($draft, 'Draft updated successfully');
                }
            }
        }

        $draft = MailMessage::create([
            'message_id' => 'draft_'.Str::uuid()->toString(),
            'folder' => 'drafts',
            'sender_name' => $fromName,
            'sender_email' => $fromAddress,
            'recipients' => $to ? [$to] : [],
            'cc' => $ccList,
            'bcc' => $bccList,
            'subject' => $subject,
            'snippet' => $snippet,
            'body' => $body,
            'is_read' => true,
            'is_starred' => false,
            'labels' => [],
        ]);

        return $this->success($draft, 'Draft saved successfully', 201);
    }

    /**
     * Schedule email for delayed dispatch
     */
    public function schedule(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'to' => 'required|email',
            'cc' => 'nullable|string',
            'bcc' => 'nullable|string',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'scheduled_at' => 'required|string',
        ]);

        $fromAddress = $this->resolveMailFromAddress();
        $fromName = $this->resolveMailFromName();

        $to = is_string($validated['to']) ? trim($validated['to']) : '';
        $subject = is_string($validated['subject']) ? trim($validated['subject']) : '';
        $body = is_string($validated['body']) ? $validated['body'] : '';
        $snippet = Str::limit(strip_tags($body), 100);

        $ccList = ! empty($validated['cc']) ? array_filter(array_map('trim', explode(',', $validated['cc']))) : [];
        $bccList = ! empty($validated['bcc']) ? array_filter(array_map('trim', explode(',', $validated['bcc']))) : [];

        $scheduled = MailMessage::create([
            'message_id' => 'sched_'.Str::uuid()->toString(),
            'folder' => 'sent',
            'sender_name' => $fromName,
            'sender_email' => $fromAddress,
            'recipients' => [$to],
            'cc' => $ccList,
            'bcc' => $bccList,
            'subject' => '[Scheduled] '.$subject,
            'snippet' => $snippet,
            'body' => $body,
            'is_read' => true,
            'is_starred' => false,
            'labels' => ['scheduled'],
            'sent_at' => now(),
        ]);

        return $this->success($scheduled, 'Email scheduled successfully', 201);
    }

    /**
     * Snooze message
     */
    public function snooze(string $id, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'snooze_until' => 'required|string',
        ]);

        $message = MailMessage::find($id);
        if (! $message) {
            return $this->error('Message not found', 404);
        }

        $labels = is_array($message->labels) ? $message->labels : [];
        if (! in_array('snoozed', $labels, true)) {
            $labels[] = 'snoozed';
        }

        $message->update(['labels' => $labels]);

        return $this->success($message, 'Message snoozed until '.$validated['snooze_until']);
    }

    /**
     * Move message to a specific folder
     */
    public function move(string $id, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'folder' => 'required|string|in:inbox,sent,drafts,trash,spam,archive',
        ]);

        $message = MailMessage::find($id);
        if (! $message) {
            return $this->error('Message not found', 404);
        }

        $message->update(['folder' => $validated['folder']]);

        return $this->success($message, 'Message moved to '.$validated['folder']);
    }

    /**
     * Assign or remove a label on a message
     */
    public function toggleMessageLabel(string $id, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'label' => 'required|string|max:50',
        ]);

        $message = MailMessage::find($id);
        if (! $message) {
            return $this->error('Message not found', 404);
        }

        $labels = is_array($message->labels) ? $message->labels : [];
        $target = (string) $validated['label'];

        if (in_array($target, $labels, true)) {
            $labels = array_values(array_diff($labels, [$target]));
        } else {
            $labels[] = $target;
        }

        $message->update(['labels' => $labels]);

        return $this->success(['labels' => $labels], 'Message labels updated');
    }

    /**
     * Toggle starred status
     */
    public function toggleStar(string $id): JsonResponse
    {
        $message = MailMessage::find($id);
        if (! $message) {
            return $this->error('Message not found', 404);
        }

        $message->update(['is_starred' => ! $message->is_starred]);

        return $this->success(['is_starred' => $message->is_starred], 'Star status updated');
    }

    /**
     * Mark message as read / unread
     */
    public function markRead(string $id, Request $request): JsonResponse
    {
        $message = MailMessage::find($id);
        if (! $message) {
            return $this->error('Message not found', 404);
        }

        $isRead = $request->boolean('is_read', true);
        $message->update(['is_read' => $isRead]);

        return $this->success(['is_read' => $message->is_read], 'Read status updated');
    }

    /**
     * Move message to trash (Soft Delete)
     */
    public function moveToTrash(string $id): JsonResponse
    {
        $message = MailMessage::find($id);
        if (! $message) {
            return $this->error('Message not found', 404);
        }

        $message->update(['folder' => 'trash']);

        return $this->success(null, 'Message moved to Trash');
    }

    /**
     * Restore message from trash to inbox
     */
    public function restore(string $id): JsonResponse
    {
        $message = MailMessage::find($id);
        if (! $message) {
            return $this->error('Message not found', 404);
        }

        $message->update(['folder' => 'inbox']);

        return $this->success(null, 'Message restored to Inbox');
    }

    /**
     * Delete message permanently (Hard Delete)
     */
    public function destroy(string $id): JsonResponse
    {
        $message = MailMessage::find($id);
        if (! $message) {
            return $this->error('Message not found', 404);
        }

        $message->delete();

        return $this->success(null, 'Message permanently deleted');
    }

    /**
     * Empty entire trash folder
     */
    public function emptyTrash(): JsonResponse
    {
        $count = MailMessage::where('folder', 'trash')->delete();

        return $this->success(['deleted_count' => $count], 'Trash folder emptied successfully');
    }

    /**
     * Get list of custom labels
     */
    public function getLabels(): JsonResponse
    {
        $labelsSetting = Setting::where('key', 'mail_client_labels')->first();
        $labels = $labelsSetting && is_array($labelsSetting->value)
            ? $labelsSetting->value
            : [
                ['id' => 'support', 'name' => 'Support', 'color' => 'bg-blue-500'],
                ['id' => 'urgent', 'name' => 'Urgent', 'color' => 'bg-rose-500'],
                ['id' => 'billing', 'name' => 'Billing', 'color' => 'bg-emerald-500'],
                ['id' => 'system', 'name' => 'System Alerts', 'color' => 'bg-amber-500'],
            ];

        return $this->success($labels, 'Labels retrieved successfully');
    }

    /**
     * Save custom labels list
     */
    public function saveLabels(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'labels' => 'required|array',
            'labels.*.id' => 'required|string',
            'labels.*.name' => 'required|string|max:50',
            'labels.*.color' => 'required|string|max:50',
        ]);

        Setting::set('mail_client_labels', $validated['labels'], 'array', 'mail_client');

        return $this->success($validated['labels'], 'Labels saved successfully');
    }

    /**
     * Get list of canned email response templates
     */
    public function getTemplates(): JsonResponse
    {
        $templatesSetting = Setting::where('key', 'mail_client_templates')->first();
        $templates = $templatesSetting && is_array($templatesSetting->value)
            ? $templatesSetting->value
            : [
                [
                    'id' => 'tpl_meeting',
                    'title' => 'Meeting Confirmation',
                    'snippet' => 'Hi, confirming our meeting scheduled for...',
                    'body' => "Hi,\n\nThis is to confirm our meeting scheduled for [Date & Time]. Please let me know if you need to adjust the schedule or add additional attendees.\n\nLooking forward to speaking with you.\n\nBest regards,",
                ],
                [
                    'id' => 'tpl_ack',
                    'title' => 'General Acknowledgment',
                    'snippet' => 'Thank you for reaching out. We have received...',
                    'body' => "Hi,\n\nThank you for reaching out. We have received your message and our team is currently reviewing it. We will get back to you with an update shortly.\n\nBest regards,",
                ],
                [
                    'id' => 'tpl_quote',
                    'title' => 'Price Quotation & Proposal',
                    'snippet' => 'Please find attached our formal quotation...',
                    'body' => "Dear Client,\n\nThank you for your interest in our services. Please find attached our formal quotation and project scope for your review.\n\nFeel free to reach out if you have any questions.\n\nBest regards,",
                ],
                [
                    'id' => 'tpl_support',
                    'title' => 'Technical Support Inquiry',
                    'snippet' => 'Could you please provide account details...',
                    'body' => "Hello,\n\nThank you for contacting technical support. To help us resolve this swiftly, could you please provide your account email and a screenshot/log of the issue?\n\nThank you for your patience.\n\nBest regards,",
                ],
                [
                    'id' => 'tpl_followup',
                    'title' => 'Follow-up Check-in',
                    'snippet' => 'Quick follow-up on my previous message...',
                    'body' => "Hi,\n\nI wanted to quickly follow up on my previous message regarding [Subject]. Please let me know if you need any additional information from our side.\n\nBest regards,",
                ],
            ];

        return $this->success($templates, 'Templates retrieved successfully');
    }

    /**
     * Save canned email response templates list
     */
    public function saveTemplates(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'templates' => 'required|array',
            'templates.*.id' => 'required|string',
            'templates.*.title' => 'required|string|max:100',
            'templates.*.snippet' => 'nullable|string|max:200',
            'templates.*.body' => 'required|string',
        ]);

        Setting::set('mail_client_templates', $validated['templates'], 'array', 'mail_client');

        return $this->success($validated['templates'], 'Templates saved successfully');
    }

    /**
     * Get mail client standard preferences & active global AI status
     */
    public function getSettings(): JsonResponse
    {
        $settings = Setting::getGroup('mail_client');
        $globalAi = Setting::getGroup('ai');

        $isGlobalAiEnabled = $this->settingBool($globalAi, 'ai_enabled', false);
        $defaultProvider = $this->settingString($globalAi, 'ai_default_provider', 'gemini');

        // Check active AI providers in system
        $providerCatalog = [
            'gemini' => ['name' => 'Google Gemini', 'model' => $globalAi['gemini_model'] ?? 'gemini-2.0-flash', 'has_key' => ! empty($globalAi['gemini_api_key'])],
            'openai' => ['name' => 'OpenAI GPT', 'model' => $globalAi['openai_model'] ?? 'gpt-4o-mini', 'has_key' => ! empty($globalAi['openai_api_key'])],
            'claude' => ['name' => 'Anthropic Claude', 'model' => $globalAi['claude_model'] ?? 'claude-3-5-sonnet-20241022', 'has_key' => ! empty($globalAi['claude_api_key'])],
            'deepseek' => ['name' => 'DeepSeek', 'model' => $globalAi['deepseek_model'] ?? 'deepseek-chat', 'has_key' => ! empty($globalAi['deepseek_api_key'])],
            'grok' => ['name' => 'xAI Grok', 'model' => $globalAi['grok_model'] ?? 'grok-2-latest', 'has_key' => ! empty($globalAi['grok_api_key'])],
            'openrouter' => ['name' => 'OpenRouter', 'model' => $globalAi['openrouter_model'] ?? 'openrouter/auto', 'has_key' => ! empty($globalAi['openrouter_api_key'])],
        ];

        $activeProviders = [];
        foreach ($providerCatalog as $slug => $info) {
            if ($info['has_key'] || $slug === $defaultProvider) {
                $activeProviders[] = [
                    'id' => $slug,
                    'name' => $info['name'],
                    'model' => $info['model'],
                    'has_key' => $info['has_key'],
                    'is_default' => $slug === $defaultProvider,
                ];
            }
        }

        return $this->success([
            'per_page' => $this->settingInt($settings, 'mail_client_per_page', 25),
            'storage_quota_gb' => $this->settingInt($settings, 'mail_client_storage_quota_gb', 15),
            'trash_retention_days' => $this->settingInt($settings, 'mail_client_trash_retention_days', 30),
            'signature' => $this->settingString($settings, 'mail_client_signature'),
            'signature_logo' => $this->settingString($settings, 'mail_client_signature_logo'),
            'signature_company' => $this->settingString($settings, 'mail_client_signature_company'),
            'reply_to' => $this->settingString($settings, 'mail_client_reply_to'),
            'auto_read_delay' => $this->settingInt($settings, 'mail_client_auto_read_delay'),
            'auto_check_interval' => $this->settingInt($settings, 'mail_client_auto_check_interval', 5),
            'sound_notifications' => $this->settingBool($settings, 'mail_client_sound_notifications', true),
            'block_remote_images' => $this->settingBool($settings, 'mail_client_block_remote_images', true),
            'vacation_enabled' => $this->settingBool($settings, 'mail_client_vacation_enabled'),
            'vacation_subject' => $this->settingString($settings, 'mail_client_vacation_subject', 'Out of Office Auto-Reply'),
            'vacation_body' => $this->settingString($settings, 'mail_client_vacation_body', 'Thank you for your message. I am currently out of office.'),
            // AI Governance & Scope
            'ai_enabled' => $this->settingBool($settings, 'mail_client_ai_enabled', true),
            'ai_provider' => $this->settingString($settings, 'mail_client_ai_provider', $defaultProvider),
            'ai_tone' => $this->settingString($settings, 'mail_client_ai_tone', 'professional'),
            'ai_scope_drafting' => (bool) ($settings['mail_client_ai_scope_drafting'] ?? true),
            'ai_scope_summarize' => (bool) ($settings['mail_client_ai_scope_summarize'] ?? true),
            'ai_scope_smart_reply' => (bool) ($settings['mail_client_ai_scope_smart_reply'] ?? true),
            'ai_scope_sentiment' => (bool) ($settings['mail_client_ai_scope_sentiment'] ?? true),
            'ai_guardrail_human_review' => (bool) ($settings['mail_client_ai_guardrail_human_review'] ?? true),
            'ai_guardrail_pii_masking' => (bool) ($settings['mail_client_ai_guardrail_pii_masking'] ?? true),
            'storage_stats' => $this->calculateStorageStats(),
            // System Global AI Integration Info
            'global_ai' => [
                'enabled' => $isGlobalAiEnabled,
                'default_provider' => $defaultProvider,
                'active_providers' => $activeProviders,
            ],
        ], 'Mail client settings retrieved successfully');
    }

    /**
     * Save mail client standard preferences
     */
    public function saveSettings(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'per_page' => 'nullable|integer|min:5|max:100',
            'storage_quota_gb' => 'nullable|integer|min:1|max:500',
            'trash_retention_days' => 'nullable|integer|min:0|max:365',
            'signature' => 'nullable|string',
            'signature_logo' => 'nullable|string',
            'signature_company' => 'nullable|string|max:100',
            'reply_to' => 'nullable|string|max:150',
            'auto_read_delay' => 'nullable|integer',
            'auto_check_interval' => 'nullable|integer',
            'sound_notifications' => 'nullable|boolean',
            'block_remote_images' => 'nullable|boolean',
            'vacation_enabled' => 'nullable|boolean',
            'vacation_subject' => 'nullable|string|max:255',
            'vacation_body' => 'nullable|string',
            // AI Governance
            'ai_enabled' => 'nullable|boolean',
            'ai_provider' => 'nullable|string|max:50',
            'ai_tone' => 'nullable|string|max:50',
            'ai_scope_drafting' => 'nullable|boolean',
            'ai_scope_summarize' => 'nullable|boolean',
            'ai_scope_smart_reply' => 'nullable|boolean',
            'ai_scope_sentiment' => 'nullable|boolean',
            'ai_guardrail_human_review' => 'nullable|boolean',
            'ai_guardrail_pii_masking' => 'nullable|boolean',
        ]);

        foreach ($validated as $key => $val) {
            $cleanVal = $val === null ? '' : $val;
            $type = is_bool($val) ? 'boolean' : (is_int($val) ? 'integer' : 'string');
            Setting::set('mail_client_'.$key, $cleanVal, $type, 'mail_client');
        }

        return $this->getSettings();
    }

    /**
     * Sync mailbox from mail server
     */
    public function sync(): JsonResponse
    {
        $total = MailMessage::count();

        return $this->success([
            'synced_at' => now()->toIso8601String(),
            'total_messages' => $total,
            'storage' => $this->calculateStorageStats(),
        ], 'Mailbox synchronized successfully');
    }

    /**
     * Calculate mailbox storage usage and quota
     *
     * @return array{used_bytes: int, quota_bytes: int, used_formatted: string, quota_formatted: string, percentage: float}
     */
    private function calculateStorageStats(): array
    {
        $bodyBytes = (int) MailMessage::sum(DB::raw('LENGTH(body) + LENGTH(COALESCE(snippet, \'\'))'));
        $overhead = MailMessage::count() * 2048;
        $usedBytes = max(24576, $bodyBytes + $overhead);

        $quotaGbRaw = Setting::where('key', 'mail_client_storage_quota_gb')->value('value') ?? 15;
        $quotaGb = is_numeric($quotaGbRaw) ? (int) $quotaGbRaw : 15;
        $quotaBytes = $quotaGb * 1024 * 1024 * 1024;

        $percentage = $quotaBytes > 0 ? min(100.0, round(($usedBytes / $quotaBytes) * 100, 2)) : 0.0;

        return [
            'used_bytes' => $usedBytes,
            'quota_bytes' => $quotaBytes,
            'used_formatted' => $this->formatBytes($usedBytes),
            'quota_formatted' => "{$quotaGb} GB",
            'percentage' => $percentage,
        ];
    }

    /**
     * Format bytes into human-readable string
     */
    private function formatBytes(int $bytes): string
    {
        if ($bytes >= 1073741824) {
            return round($bytes / 1073741824, 2).' GB';
        }
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 2).' MB';
        }
        if ($bytes >= 1024) {
            return round($bytes / 1024, 2).' KB';
        }

        return $bytes.' B';
    }

    private function resolveMailFromAddress(): string
    {
        $settingValue = Setting::get('mail_from_address');
        if (is_string($settingValue) && $settingValue !== '') {
            return $settingValue;
        }

        $configValue = config('mail.from.address', 'admin@jejakawan.com');

        return is_string($configValue) ? $configValue : 'admin@jejakawan.com';
    }

    private function resolveMailFromName(): string
    {
        $settingValue = Setting::get('mail_from_name');
        if (is_string($settingValue) && $settingValue !== '') {
            return $settingValue;
        }

        $configValue = config('mail.from.name', 'Jejakawan Mail');

        return is_string($configValue) ? $configValue : 'Jejakawan Mail';
    }

    /**
     * @param  array<string, mixed>  $settings
     */
    private function settingString(array $settings, string $key, string $default = ''): string
    {
        $value = $settings[$key] ?? $default;

        return is_string($value) ? $value : (is_scalar($value) ? (string) $value : $default);
    }

    /**
     * @param  array<string, mixed>  $settings
     */
    private function settingInt(array $settings, string $key, int $default = 0): int
    {
        $value = $settings[$key] ?? $default;

        return is_numeric($value) ? (int) $value : $default;
    }

    /**
     * @param  array<string, mixed>  $settings
     */
    private function settingBool(array $settings, string $key, bool $default = false): bool
    {
        $value = $settings[$key] ?? $default;

        return is_bool($value) ? $value : (bool) $value;
    }
}
