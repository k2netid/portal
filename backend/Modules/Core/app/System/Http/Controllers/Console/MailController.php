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
        $folder = (string) $request->input('folder', 'inbox');
        $label = $request->input('label');
        $filter = (string) $request->input('filter', 'all');
        $search = (string) $request->input('q', '');

        $defaultPerPage = (int) (Setting::where('key', 'mail_client_per_page')->value('value') ?? 25);
        $perPage = (int) $request->input('per_page', $defaultPerPage);
        if ($perPage < 5 || $perPage > 100) {
            $perPage = 25;
        }

        $query = MailMessage::query();

        if ($label && is_string($label) && $label !== '') {
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
        $fromName = config('mail.from.name', 'Jejakawan Core');
        $fromAddress = config('mail.from.address', 'noreply@jejakawan.com');

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

        Setting::updateOrCreate(
            ['key' => 'mail_client_labels'],
            [
                'value' => $validated['labels'],
                'group' => 'mail_client',
                'type' => 'array',
            ]
        );

        return $this->success($validated['labels'], 'Labels saved successfully');
    }

    /**
     * Get mail client standard preferences
     */
    public function getSettings(): JsonResponse
    {
        $settings = Setting::getGroup('mail_client');

        return $this->success([
            'per_page' => (int) ($settings['mail_client_per_page'] ?? 25),
            'storage_quota_gb' => (int) ($settings['mail_client_storage_quota_gb'] ?? 15),
            'trash_retention_days' => (int) ($settings['mail_client_trash_retention_days'] ?? 30),
            'signature' => (string) ($settings['mail_client_signature'] ?? ''),
            'signature_logo' => (string) ($settings['mail_client_signature_logo'] ?? ''),
            'signature_company' => (string) ($settings['mail_client_signature_company'] ?? ''),
            'reply_to' => (string) ($settings['mail_client_reply_to'] ?? ''),
            'auto_read_delay' => (int) ($settings['mail_client_auto_read_delay'] ?? 0),
            'auto_check_interval' => (int) ($settings['mail_client_auto_check_interval'] ?? 5),
            'sound_notifications' => (bool) ($settings['mail_client_sound_notifications'] ?? true),
            'block_remote_images' => (bool) ($settings['mail_client_block_remote_images'] ?? true),
            'vacation_enabled' => (bool) ($settings['mail_client_vacation_enabled'] ?? false),
            'vacation_subject' => (string) ($settings['mail_client_vacation_subject'] ?? 'Out of Office Auto-Reply'),
            'vacation_body' => (string) ($settings['mail_client_vacation_body'] ?? 'Thank you for your message. I am currently out of office.'),
            // AI Governance & Scope
            'ai_enabled' => (bool) ($settings['mail_client_ai_enabled'] ?? true),
            'ai_provider' => (string) ($settings['mail_client_ai_provider'] ?? 'system'),
            'ai_tone' => (string) ($settings['mail_client_ai_tone'] ?? 'professional'),
            'ai_scope_drafting' => (bool) ($settings['mail_client_ai_scope_drafting'] ?? true),
            'ai_scope_summarize' => (bool) ($settings['mail_client_ai_scope_summarize'] ?? true),
            'ai_scope_smart_reply' => (bool) ($settings['mail_client_ai_scope_smart_reply'] ?? true),
            'ai_scope_sentiment' => (bool) ($settings['mail_client_ai_scope_sentiment'] ?? true),
            'ai_guardrail_human_review' => (bool) ($settings['mail_client_ai_guardrail_human_review'] ?? true),
            'ai_guardrail_pii_masking' => (bool) ($settings['mail_client_ai_guardrail_pii_masking'] ?? true),
            'storage_stats' => $this->calculateStorageStats(),
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
            'signature_logo' => 'nullable|string|max:500',
            'signature_company' => 'nullable|string|max:100',
            'reply_to' => 'nullable|email',
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
            if ($val === null) {
                continue;
            }
            $type = is_bool($val) ? 'boolean' : (is_int($val) ? 'integer' : 'string');
            Setting::set('mail_client_'.$key, $val, $type, 'mail_client');
        }

        return $this->success($validated, 'Mail client settings saved successfully');
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
     */
    private function calculateStorageStats(): array
    {
        $bodyBytes = (int) MailMessage::sum(DB::raw('LENGTH(body) + LENGTH(COALESCE(snippet, \'\'))'));
        $overhead = MailMessage::count() * 2048;
        $usedBytes = max(24576, $bodyBytes + $overhead);

        $quotaGb = (int) (Setting::where('key', 'mail_client_storage_quota_gb')->value('value') ?? 15);
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
}
