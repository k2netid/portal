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
use Modules\Mail\Exceptions\MailDispatchException;
use Modules\Mail\Models\MailAccount;
use Modules\Mail\Models\MailMessage;

class MailDispatchService
{
    /**
     * @param  array<string>  $ccList
     * @param  array<string>  $bccList
     * @return array{from_name: string, from_address: string, account_id: string|null, snippet: string}
     */
    public function sendOutbound(
        string $to,
        string $subject,
        string $body,
        array $ccList = [],
        array $bccList = [],
        ?User $user = null,
        ?MailAccount $account = null,
    ): array {
        $body = $this->appendSignature($body);
        [$fromAddress, $fromName, $account] = $this->resolveSender($user, $account);

        try {
            $this->dispatchTransport($to, $subject, $body, $fromAddress, $fromName, $ccList, $bccList, $account);
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

        $account = null;
        if (is_string($message->account_id) && $message->account_id !== '') {
            $account = MailAccount::find($message->account_id);
        }

        $this->sendOutbound($to, $subject, $body, $cc, $bcc, null, $account);
    }

    /**
     * @param  array<string>  $ccList
     * @param  array<string>  $bccList
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
    ): void {
        $mailer = $this->resolveMailerName($account);

        Mail::mailer($mailer)->html($body, function (Message $mail) use ($to, $subject, $fromAddress, $fromName, $ccList, $bccList): void {
            $mail->to($to)
                ->subject($subject)
                ->from($fromAddress, $fromName);

            if ($ccList !== []) {
                $mail->cc($ccList);
            }
            if ($bccList !== []) {
                $mail->bcc($bccList);
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

        return [$this->resolveMailFromAddress(), $this->resolveMailFromName(), $account];
    }

    private function appendSignature(string $body): string
    {
        $signatureSetting = Setting::where('key', 'mail_client_signature')->first();
        $signatureLogo = Setting::where('key', 'mail_client_signature_logo')->first();
        $signatureCompany = Setting::where('key', 'mail_client_signature_company')->first();

        if (
            ($signatureSetting && ! empty($signatureSetting->value))
            || ($signatureLogo && ! empty($signatureLogo->value))
        ) {
            $sigHtml = '<br/><br/>--<br/><table style="font-family: sans-serif; font-size: 13px; color: #333; margin-top: 16px;"><tr>';
            if ($signatureLogo && ! empty($signatureLogo->value)) {
                $logoUrl = (string) $signatureLogo->value;
                if (! filter_var($logoUrl, FILTER_VALIDATE_URL) || ! preg_match('#^https?://#i', $logoUrl)) {
                    $logoUrl = '';
                }
                if ($logoUrl !== '') {
                    $sigHtml .= '<td style="padding-right: 12px; vertical-align: middle;"><img src="'.htmlspecialchars($logoUrl).'" style="max-height: 48px; border-radius: 6px;" alt="Logo" /></td>';
                }
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

        return $body;
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
