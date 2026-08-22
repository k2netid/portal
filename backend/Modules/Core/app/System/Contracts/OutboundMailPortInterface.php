<?php

declare(strict_types=1);

namespace Modules\Core\System\Contracts;

use Modules\Core\System\Models\User;

/**
 * Cross-module outbound mail port. Bound by JA-Mail when the extension module is loaded.
 *
 * Other modules should type-hint this interface — never import Mail controllers.
 */
interface OutboundMailPortInterface
{
    /**
     * @param  array<int, string>  $cc
     * @param  array<int, string>  $bcc
     * @param  list<array<string, mixed>>  $attachments  Stored attachment meta (paths), not UploadedFile
     * @return array{
     *     status: 'sent'|'queued',
     *     from_name?: string,
     *     from_address?: string,
     *     account_id?: string|null,
     *     snippet?: string,
     *     attachments?: list<array<string, mixed>>
     * }
     */
    public function send(
        string $to,
        string $subject,
        string $htmlBody,
        array $cc = [],
        array $bcc = [],
        ?User $asUser = null,
        ?string $accountId = null,
        array $attachments = [],
        ?bool $queue = null,
    ): array;
}
