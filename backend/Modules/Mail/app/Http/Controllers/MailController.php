<?php

declare(strict_types=1);

namespace Modules\Mail\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Modules\Core\System\Http\Controllers\BaseApiController;
use Modules\Core\System\Models\Setting;
use Modules\Core\System\Models\User;
use Modules\Mail\Exceptions\MailDispatchException;
use Modules\Mail\Http\Controllers\Concerns\InteractsWithUserMailbox;
use Modules\Mail\Models\MailMessage;
use Modules\Mail\Services\MailAttachmentStore;
use Modules\Mail\Services\MailDispatchService;
use Modules\Mail\Support\MailAddressParser;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MailController extends BaseApiController
{
    use InteractsWithUserMailbox;

    public function __construct(
        protected MailDispatchService $mailDispatch,
        protected MailAttachmentStore $attachmentStore,
    ) {}

    /**
     * Get mail messages with filtering & folder statistics
     */
    public function index(Request $request): JsonResponse
    {
        $repo = $this->mailRepo($request);
        if ($repo instanceof JsonResponse) {
            return $repo;
        }

        $accountIdRaw = $request->input('account_id');
        $accountId = is_string($accountIdRaw) && $accountIdRaw !== '' ? $accountIdRaw : null;

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

        $query = $repo->messages($accountId);

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
            $query->whereNotNull('attachments')->whereJsonLength('attachments', '>', 0);
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

        $user = $repo->user();
        $folderCounts = [
            'inbox' => $repo->messages($accountId)->where('folder', 'inbox')->where('is_read', false)->count(),
            'sent' => $repo->messages($accountId)->where('folder', 'sent')->where('is_read', false)->count(),
            'drafts' => $repo->messages($accountId)->where('folder', 'drafts')->count(),
            'trash' => $repo->messages($accountId)->where('folder', 'trash')->count(),
            'spam' => $repo->messages($accountId)->where('folder', 'spam')->where('is_read', false)->count(),
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
            'storage' => $this->calculateStorageStatsForUser($user),
        ], 'Mail messages retrieved successfully');
    }

    /**
     * Get a single message and mark as read
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $repo = $this->mailRepo($request);
        if ($repo instanceof JsonResponse) {
            return $repo;
        }

        $message = $repo->findMessage($id);

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
            'account_id' => 'nullable|string|uuid',
            'attachments' => 'nullable|array|max:10',
            'attachments.*' => 'file|max:10240',
        ]);

        $repo = $this->mailRepo($request);
        if ($repo instanceof JsonResponse) {
            return $repo;
        }

        try {
            $ccList = MailAddressParser::parseList($validated['cc'] ?? null);
            $bccList = MailAddressParser::parseList($validated['bcc'] ?? null);
        } catch (InvalidArgumentException $e) {
            return $this->validationError(['cc' => [$e->getMessage()]]);
        }

        $to = (string) $validated['to'];
        $subject = (string) ($validated['subject'] ?? '(No Subject)');
        $body = (string) ($validated['body'] ?? '');

        $accountId = is_string($validated['account_id'] ?? null) ? $validated['account_id'] : null;
        $account = $repo->resolveAccount($accountId);
        $user = $repo->user();
        $files = $request->file('attachments', []);
        $files = is_array($files) ? $files : [];
        $attachmentMeta = $this->attachmentStore->storeMany($user, $files);

        try {
            $dispatch = $this->mailDispatch->sendOutbound(
                $to,
                $subject,
                $body,
                $ccList,
                $bccList,
                $user,
                $account,
                $attachmentMeta,
            );
        } catch (MailDispatchException $e) {
            return $this->error($e->getMessage(), 502, [], 'MAIL_SEND_FAILED');
        }

        $messageRecord = MailMessage::create([
            'user_id' => $user->id,
            'account_id' => $dispatch['account_id'],
            'folder' => 'sent',
            'sender_name' => $dispatch['from_name'],
            'sender_email' => $dispatch['from_address'],
            'recipients' => [$to],
            'cc' => $ccList,
            'bcc' => $bccList,
            'subject' => $subject,
            'snippet' => $dispatch['snippet'],
            'body' => $body,
            'attachments' => [],
            'is_read' => true,
            'is_starred' => false,
            'labels' => [],
            'sent_at' => now(),
        ]);

        $messageRecord->update([
            'attachments' => $this->attachmentStore->withPublicMeta($attachmentMeta, $messageRecord->id),
        ]);

        return $this->success($messageRecord->fresh(), 'Email sent successfully', 201);
    }

    /**
     * Save email draft
     */
    public function saveDraft(Request $request): JsonResponse
    {
        $repo = $this->mailRepo($request);
        if ($repo instanceof JsonResponse) {
            return $repo;
        }

        $user = $repo->user();

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
                $draft = $repo->findMessage($draftId);
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
            'user_id' => $user->id,
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
        $repo = $this->mailRepo($request);
        if ($repo instanceof JsonResponse) {
            return $repo;
        }

        $user = $repo->user();

        $validated = $request->validate([
            'to' => 'required|email',
            'cc' => 'nullable|string',
            'bcc' => 'nullable|string',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'scheduled_at' => 'required|string',
            'account_id' => 'nullable|string|uuid',
            'attachments' => 'nullable|array|max:10',
            'attachments.*' => 'file|max:10240',
        ]);

        try {
            $ccList = MailAddressParser::parseList($validated['cc'] ?? null);
            $bccList = MailAddressParser::parseList($validated['bcc'] ?? null);
        } catch (InvalidArgumentException $e) {
            return $this->validationError(['cc' => [$e->getMessage()]]);
        }

        $to = is_string($validated['to']) ? trim($validated['to']) : '';
        $subject = is_string($validated['subject']) ? trim($validated['subject']) : '';
        $body = is_string($validated['body']) ? $validated['body'] : '';
        $snippet = Str::limit(strip_tags($body), 100);

        $scheduledAt = Carbon::parse((string) $validated['scheduled_at']);
        if ($scheduledAt->isPast()) {
            return $this->validationError(['scheduled_at' => ['Scheduled time must be in the future.']]);
        }

        $accountId = is_string($validated['account_id'] ?? null) ? $validated['account_id'] : null;
        $account = $repo->resolveAccount($accountId);
        $files = $request->file('attachments', []);
        $files = is_array($files) ? $files : [];
        $attachmentMeta = $this->attachmentStore->storeMany($user, $files);

        $scheduled = MailMessage::create([
            'user_id' => $user->id,
            'message_id' => 'sched_'.Str::uuid()->toString(),
            'account_id' => $account?->id,
            'folder' => 'scheduled',
            'sender_name' => $this->resolveMailFromName(),
            'sender_email' => $this->resolveMailFromAddress(),
            'recipients' => [$to],
            'cc' => $ccList,
            'bcc' => $bccList,
            'subject' => '[Scheduled] '.$subject,
            'snippet' => $snippet,
            'body' => $body,
            'attachments' => [],
            'is_read' => true,
            'is_starred' => false,
            'labels' => ['scheduled'],
            'scheduled_at' => $scheduledAt,
            'sent_at' => null,
        ]);

        $scheduled->update([
            'attachments' => $this->attachmentStore->withPublicMeta($attachmentMeta, $scheduled->id),
        ]);

        return $this->success($scheduled->fresh(), 'Email scheduled successfully', 201);
    }

    /**
     * Download a stored outbound attachment for an owned message.
     */
    public function downloadAttachment(Request $request, string $id, int $index): StreamedResponse|JsonResponse
    {
        $message = $this->ownedMessage($request, $id);
        if ($message instanceof JsonResponse) {
            return $message;
        }

        $attachments = is_array($message->attachments) ? array_values($message->attachments) : [];
        $attachment = $attachments[$index] ?? null;
        if (! is_array($attachment)) {
            return $this->error('Attachment not found', 404);
        }

        $path = is_string($attachment['path'] ?? null) ? $attachment['path'] : '';
        $disk = is_string($attachment['disk'] ?? null) ? $attachment['disk'] : 'local';
        $name = is_string($attachment['name'] ?? null) ? $attachment['name'] : 'attachment.bin';

        if ($path === '' || ! Storage::disk($disk)->exists($path)) {
            return $this->error('Attachment file missing', 404);
        }

        return Storage::disk($disk)->download($path, $name);
    }

    /**
     * Snooze message
     */
    public function snooze(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'snooze_until' => 'required|string',
        ]);

        $message = $this->ownedMessage($request, $id);
        if ($message instanceof JsonResponse) {
            return $message;
        }

        $labels = is_array($message->labels) ? $message->labels : [];
        if (! in_array('snoozed', $labels, true)) {
            $labels[] = 'snoozed';
        }

        $snoozedUntil = Carbon::parse((string) $validated['snooze_until']);

        $message->update([
            'labels' => $labels,
            'snoozed_until' => $snoozedUntil,
        ]);

        return $this->success($message, 'Message snoozed until '.$snoozedUntil->toIso8601String());
    }

    public function move(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'folder' => 'required|string|in:inbox,sent,drafts,trash,spam,archive,scheduled',
        ]);

        $message = $this->ownedMessage($request, $id);
        if ($message instanceof JsonResponse) {
            return $message;
        }

        $message->update(['folder' => $validated['folder']]);

        return $this->success($message, 'Message moved to '.$validated['folder']);
    }

    public function toggleMessageLabel(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'label' => 'required|string|max:50',
        ]);

        $message = $this->ownedMessage($request, $id);
        if ($message instanceof JsonResponse) {
            return $message;
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

    public function toggleStar(Request $request, string $id): JsonResponse
    {
        $message = $this->ownedMessage($request, $id);
        if ($message instanceof JsonResponse) {
            return $message;
        }

        $message->update(['is_starred' => ! $message->is_starred]);

        return $this->success(['is_starred' => $message->is_starred], 'Star status updated');
    }

    public function markRead(Request $request, string $id): JsonResponse
    {
        $message = $this->ownedMessage($request, $id);
        if ($message instanceof JsonResponse) {
            return $message;
        }

        $isRead = $request->boolean('is_read', true);
        $message->update(['is_read' => $isRead]);

        return $this->success(['is_read' => $message->is_read], 'Read status updated');
    }

    public function moveToTrash(Request $request, string $id): JsonResponse
    {
        $message = $this->ownedMessage($request, $id);
        if ($message instanceof JsonResponse) {
            return $message;
        }

        $message->update(['folder' => 'trash']);

        return $this->success(null, 'Message moved to Trash');
    }

    public function restore(Request $request, string $id): JsonResponse
    {
        $message = $this->ownedMessage($request, $id);
        if ($message instanceof JsonResponse) {
            return $message;
        }

        $message->update(['folder' => 'inbox']);

        return $this->success(null, 'Message restored to Inbox');
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $message = $this->ownedMessage($request, $id);
        if ($message instanceof JsonResponse) {
            return $message;
        }

        $message->delete();

        return $this->success(null, 'Message permanently deleted');
    }

    public function emptyTrash(Request $request): JsonResponse
    {
        $repo = $this->mailRepo($request);
        if ($repo instanceof JsonResponse) {
            return $repo;
        }

        $count = $repo->messages()->where('folder', 'trash')->delete();

        return $this->success(['deleted_count' => $count], 'Trash folder emptied successfully');
    }

    public function getLabels(Request $request): JsonResponse
    {
        $repo = $this->mailRepo($request);
        if ($repo instanceof JsonResponse) {
            return $repo;
        }

        $key = $this->userSettingKey($repo->user(), 'mail_client_labels');
        $labelsSetting = Setting::where('key', $key)->first();
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
        $repo = $this->mailRepo($request);
        if ($repo instanceof JsonResponse) {
            return $repo;
        }

        $validated = $request->validate([
            'labels' => 'required|array',
            'labels.*.id' => 'required|string',
            'labels.*.name' => 'required|string|max:50',
            'labels.*.color' => 'required|string|max:50',
        ]);

        $key = $this->userSettingKey($repo->user(), 'mail_client_labels');
        Setting::set($key, $validated['labels'], 'array', 'mail_client');

        return $this->success($validated['labels'], 'Labels saved successfully');
    }

    /**
     * Get list of canned email response templates
     */
    public function getTemplates(Request $request): JsonResponse
    {
        $repo = $this->mailRepo($request);
        if ($repo instanceof JsonResponse) {
            return $repo;
        }

        $key = $this->userSettingKey($repo->user(), 'mail_client_templates');
        $templatesSetting = Setting::where('key', $key)->first();
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
        $repo = $this->mailRepo($request);
        if ($repo instanceof JsonResponse) {
            return $repo;
        }

        $validated = $request->validate([
            'templates' => 'required|array',
            'templates.*.id' => 'required|string',
            'templates.*.title' => 'required|string|max:100',
            'templates.*.snippet' => 'nullable|string|max:200',
            'templates.*.body' => 'required|string',
        ]);

        $key = $this->userSettingKey($repo->user(), 'mail_client_templates');
        Setting::set($key, $validated['templates'], 'array', 'mail_client');

        return $this->success($validated['templates'], 'Templates saved successfully');
    }

    /**
     * Get mail client standard preferences & active global AI status
     */
    public function getSettings(Request $request): JsonResponse
    {
        $repo = $this->mailRepo($request);
        if ($repo instanceof JsonResponse) {
            return $repo;
        }

        $user = $repo->user();
        $globalSettings = Setting::getGroup('mail_client');
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
            'per_page' => $this->userSettingInt($user, 'per_page', $this->settingInt($globalSettings, 'mail_client_per_page', 25)),
            'storage_quota_gb' => $this->settingInt($globalSettings, 'mail_client_storage_quota_gb', 15),
            'trash_retention_days' => $this->settingInt($globalSettings, 'mail_client_trash_retention_days', 30),
            'signature' => $this->userSettingString($user, 'signature', $this->settingString($globalSettings, 'mail_client_signature')),
            'signature_logo' => $this->userSettingString($user, 'signature_logo', $this->settingString($globalSettings, 'mail_client_signature_logo')),
            'signature_company' => $this->userSettingString($user, 'signature_company', $this->settingString($globalSettings, 'mail_client_signature_company')),
            'reply_to' => $this->userSettingString($user, 'reply_to', $this->settingString($globalSettings, 'mail_client_reply_to')),
            'auto_read_delay' => $this->userSettingInt($user, 'auto_read_delay', $this->settingInt($globalSettings, 'mail_client_auto_read_delay')),
            'auto_check_interval' => $this->userSettingInt($user, 'auto_check_interval', $this->settingInt($globalSettings, 'mail_client_auto_check_interval', 5)),
            'sound_notifications' => $this->userSettingBool($user, 'sound_notifications', $this->settingBool($globalSettings, 'mail_client_sound_notifications', true)),
            'block_remote_images' => $this->userSettingBool($user, 'block_remote_images', $this->settingBool($globalSettings, 'mail_client_block_remote_images', true)),
            'vacation_enabled' => $this->userSettingBool($user, 'vacation_enabled', $this->settingBool($globalSettings, 'mail_client_vacation_enabled')),
            'vacation_subject' => $this->userSettingString($user, 'vacation_subject', $this->settingString($globalSettings, 'mail_client_vacation_subject', 'Out of Office Auto-Reply')),
            'vacation_body' => $this->userSettingString($user, 'vacation_body', $this->settingString($globalSettings, 'mail_client_vacation_body', 'Thank you for your message. I am currently out of office.')),
            'ai_enabled' => $this->userSettingBool($user, 'ai_enabled', $this->settingBool($globalSettings, 'mail_client_ai_enabled', true)),
            'ai_provider' => $this->userSettingString($user, 'ai_provider', $this->settingString($globalSettings, 'mail_client_ai_provider', $defaultProvider)),
            'ai_tone' => $this->userSettingString($user, 'ai_tone', $this->settingString($globalSettings, 'mail_client_ai_tone', 'professional')),
            'ai_scope_drafting' => $this->userSettingBool($user, 'ai_scope_drafting', (bool) ($globalSettings['mail_client_ai_scope_drafting'] ?? true)),
            'ai_scope_summarize' => $this->userSettingBool($user, 'ai_scope_summarize', (bool) ($globalSettings['mail_client_ai_scope_summarize'] ?? true)),
            'ai_scope_smart_reply' => $this->userSettingBool($user, 'ai_scope_smart_reply', (bool) ($globalSettings['mail_client_ai_scope_smart_reply'] ?? true)),
            'ai_scope_sentiment' => $this->userSettingBool($user, 'ai_scope_sentiment', (bool) ($globalSettings['mail_client_ai_scope_sentiment'] ?? true)),
            'ai_guardrail_human_review' => $this->userSettingBool($user, 'ai_guardrail_human_review', (bool) ($globalSettings['mail_client_ai_guardrail_human_review'] ?? true)),
            'ai_guardrail_pii_masking' => $this->userSettingBool($user, 'ai_guardrail_pii_masking', (bool) ($globalSettings['mail_client_ai_guardrail_pii_masking'] ?? true)),
            'storage_stats' => $this->calculateStorageStatsForUser($user),
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
        $repo = $this->mailRepo($request);
        if ($repo instanceof JsonResponse) {
            return $repo;
        }

        $user = $repo->user();

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

        $globalKeys = ['storage_quota_gb', 'trash_retention_days'];

        foreach ($validated as $key => $val) {
            $cleanVal = $val === null ? '' : $val;
            $type = is_bool($val) ? 'boolean' : (is_int($val) ? 'integer' : 'string');
            $settingKey = in_array($key, $globalKeys, true)
                ? 'mail_client_'.$key
                : $this->userSettingKey($user, 'mail_client_'.$key);
            Setting::set($settingKey, $cleanVal, $type, 'mail_client');
        }

        return $this->getSettings($request);
    }

    /**
     * Sync mailbox from mail server
     */
    public function sync(Request $request): JsonResponse
    {
        $repo = $this->mailRepo($request);
        if ($repo instanceof JsonResponse) {
            return $repo;
        }

        $user = $repo->user();
        $total = $repo->messages()->count();

        return $this->success([
            'synced_at' => now()->toIso8601String(),
            'total_messages' => $total,
            'storage' => $this->calculateStorageStatsForUser($user),
            'mode' => 'local_index',
        ], 'Mailbox index refreshed (IMAP sync ships in downstream mail product line)');
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

    private function userSettingString(User $user, string $suffix, string $default = ''): string
    {
        $key = $this->userSettingKey($user, 'mail_client_'.$suffix);
        $value = Setting::get($key);

        return is_string($value) && $value !== '' ? $value : $default;
    }

    private function userSettingInt(User $user, string $suffix, int $default = 0): int
    {
        $key = $this->userSettingKey($user, 'mail_client_'.$suffix);
        $value = Setting::get($key);

        return is_numeric($value) ? (int) $value : $default;
    }

    private function userSettingBool(User $user, string $suffix, bool $default = false): bool
    {
        $key = $this->userSettingKey($user, 'mail_client_'.$suffix);
        $value = Setting::get($key);

        if ($value === null) {
            return $default;
        }

        return is_bool($value) ? $value : (bool) $value;
    }
}
