<?php

declare(strict_types=1);

namespace Modules\Mail\Services;

use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Modules\Core\System\Models\Setting;
use Modules\Core\System\Models\User;
use Modules\Core\System\Services\SystemMailConfig;
use Modules\Mail\Exceptions\MailDispatchException;
use Modules\Mail\Models\MailAccount;
use Modules\Mail\Models\MailMessage;

class MailDispatchService
{
    public function __construct(
        protected SystemMailConfig $systemMailConfig,
        protected MailAttachmentStore $attachments,
    ) {}

    /**
     * @param  array<string>  $ccList
     * @param  array<string>  $bccList
     * @param  list<array<string, mixed>>  $attachmentMeta
     * @return array{from_name: string, from_address: string, account_id: string|null, snippet: string, attachments: list<array<string, mixed>>}
     */
    public function sendOutbound(
        string $to,
        string $subject,
        string $body,
        array $ccList = [],
        array $bccList = [],
        ?User $user = null,
        ?MailAccount $account = null,
        array $attachmentMeta = [],
    ): array {
        $body = $this->appendSignature($body, $user);
        [$fromAddress, $fromName, $account] = $this->resolveSender($user, $account);
        $replyTo = $this->resolveReplyTo($user);

        try {
            $this->dispatchTransport(
                $to,
                $subject,
                $body,
                $fromAddress,
                $fromName,
                $ccList,
                $bccList,
                $account,
                $replyTo,
                $attachmentMeta,
            );
        } catch (\Throwable $e) {
            Log::error('Outgoing mail dispatch failed', [
                'to' => $to,
                'account_id' => $account?->id,
                'error' => $e->getMessage(),
            ]);

            throw new MailDispatchException(
                'Failed to send email: '.$e->getMessage(),
                (int) $e->getCode(),
                $e,
            );
        }

        return [
            'from_name' => $fromName,
            'from_address' => $fromAddress,
            'account_id' => $account?->id,
            'snippet' => Str::limit(strip_tags($body), 120),
            'attachments' => $attachmentMeta,
        ];
    }

    public function dispatchScheduledMessage(MailMessage $message): void
    {
        $recipients = is_array($message->recipients) ? $message->recipients : [];
        $to = is_string($recipients[0] ?? null) ? $recipients[0] : '';
        if ($to === '') {
            return;
        }

        $cc = is_array($message->cc) ? $message->cc : [];
        $bcc = is_array($message->bcc) ? $message->bcc : [];
        $body = is_string($message->body) ? $message->body : '';
        $subject = preg_replace('/^\[Scheduled\]\s*/i', '', (string) $message->subject) ?: '(No Subject)';
        $attachmentMeta = [];
        $rawAttachments = is_array($message->attachments) ? $message->attachments : [];
        foreach ($rawAttachments as $item) {
            if (is_array($item)) {
                $attachmentMeta[] = $item;
            }
        }

        $account = null;
        if (is_string($message->account_id) && $message->account_id !== '') {
            $account = MailAccount::find($message->account_id);
        }

        $user = null;
        if (is_string($message->user_id) && $message->user_id !== '') {
            $user = User::find($message->user_id);
        }

        $this->sendOutbound($to, $subject, $body, $cc, $bcc, $user, $account, $attachmentMeta);
    }

    /**
     * @param  array<string>  $ccList
     * @param  array<string>  $bccList
     * @param  list<array<string, mixed>>  $attachmentMeta
     */
    private function dispatchTransport(
        string $to,
        string $subject,
        string $body,
        string $fromAddress,
        string $fromName,
        array $ccList,
        array $bccList,
        ?MailAccount $account,
        ?string $replyTo,
        array $attachmentMeta,
    ): void {
        $mailer = $this->resolveMailerName($account);
        $attachSpecs = $this->attachments->absoluteAttachSpecs($attachmentMeta);

        Mail::mailer($mailer)->html($body, function (Message $mail) use ($to, $subject, $fromAddress, $fromName, $ccList, $bccList, $replyTo, $attachSpecs): void {
            $mail->to($to)
                ->subject($subject)
                ->from($fromAddress, $fromName);

            if (is_string($replyTo) && $replyTo !== '') {
                $mail->replyTo($replyTo);
            }

            if ($ccList !== []) {
                $mail->cc($ccList);
            }
            if ($bccList !== []) {
                $mail->bcc($bccList);
            }

            foreach ($attachSpecs as $spec) {
                $mail->attach($spec['path'], [
                    'as' => $spec['name'],
                    'mime' => $spec['mime'],
                ]);
            }
        });
    }

    private function resolveMailerName(?MailAccount $account): string
    {
        if (
            $account instanceof MailAccount
            && $account->account_type === 'custom_personal'
            && is_string($account->smtp_host)
            && $account->smtp_host !== ''
            && is_int($account->smtp_port)
        ) {
            $mailerKey = 'mail_account_'.$account->id;
            Config::set("mail.mailers.{$mailerKey}", [
                'transport' => 'smtp',
                'host' => $account->smtp_host,
                'port' => $account->smtp_port,
                'encryption' => $account->smtp_encryption === 'null' ? null : $account->smtp_encryption,
                'username' => $account->smtp_username,
                'password' => $account->getDecryptedSmtpPassword(),
                'timeout' => null,
            ]);

            return $mailerKey;
        }

        $this->systemMailConfig->apply();

        $defaultMailer = config('mail.default', 'smtp');

        return is_string($defaultMailer) && $defaultMailer !== '' ? $defaultMailer : 'smtp';
    }

    /**
     * @return array{0: string, 1: string, 2: MailAccount|null}
     */
    private function resolveSender(?User $user, ?MailAccount $account): array
    {
        if (! $account instanceof MailAccount && $user instanceof User) {
            $account = MailAccount::query()
                ->where('user_id', $user->id)
                ->where('is_default', true)
                ->first();
        }

        if ($account instanceof MailAccount && $account->account_type === 'custom_personal') {
            return [$account->email, $account->name, $account];
        }

        $applied = $this->systemMailConfig->apply();
        $fromAddress = is_string($applied['from_address'] ?? null) && $applied['from_address'] !== ''
            ? $applied['from_address']
            : $this->resolveMailFromAddress();
        $fromName = is_string($applied['from_name'] ?? null) && $applied['from_name'] !== ''
            ? $applied['from_name']
            : $this->resolveMailFromName();

        return [$fromAddress, $fromName, $account];
    }

    private function resolveReplyTo(?User $user): ?string
    {
        if ($user instanceof User) {
            $perUser = Setting::get('mail_client_reply_to_user_'.$user->id);
            if (is_string($perUser) && filter_var($perUser, FILTER_VALIDATE_EMAIL)) {
                return $perUser;
            }
        }

        $global = Setting::get('mail_client_reply_to');
        if (is_string($global) && filter_var($global, FILTER_VALIDATE_EMAIL)) {
            return $global;
        }

        return null;
    }

    private function appendSignature(string $body, ?User $user = null): string
    {
        $signatureSetting = $this->resolveSettingValue('mail_client_signature', $user);
        $signatureLogo = $this->resolveSettingValue('mail_client_signature_logo', $user);
        $signatureCompany = $this->resolveSettingValue('mail_client_signature_company', $user);

        if ($signatureSetting !== null || $signatureLogo !== null) {
            $sigHtml = '<br/><br/>--<br/><table style="font-family: sans-serif; font-size: 13px; color: #333; margin-top: 16px;"><tr>';
            if ($signatureLogo !== null) {
                $logoUrl = $signatureLogo;
                if (! filter_var($logoUrl, FILTER_VALIDATE_URL) || ! preg_match('#^https?://#i', $logoUrl)) {
                    $logoUrl = '';
                }
                if ($logoUrl !== '') {
                    $sigHtml .= '<td style="padding-right: 12px; vertical-align: middle;"><img src="'.htmlspecialchars($logoUrl).'" style="max-height: 48px; border-radius: 6px;" alt="Logo" /></td>';
                }
            }
            $sigHtml .= '<td style="vertical-align: middle; line-height: 1.4;">';
            if ($signatureCompany !== null) {
                $sigHtml .= '<strong>'.htmlspecialchars($signatureCompany).'</strong><br/>';
            }
            if ($signatureSetting !== null) {
                $sigHtml .= nl2br(htmlspecialchars($signatureSetting));
            }
            $sigHtml .= '</td></tr></table>';
            $body .= $sigHtml;
        }

        return $body;
    }

    private function resolveSettingValue(string $baseKey, ?User $user): ?string
    {
        if ($user instanceof User) {
            $perUser = Setting::get($baseKey.'_user_'.$user->id);
            if (is_string($perUser) && $perUser !== '') {
                return $perUser;
            }
        }

        $global = Setting::get($baseKey);

        return is_string($global) && $global !== '' ? $global : null;
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
}
