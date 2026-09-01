<?php

declare(strict_types=1);

namespace Modules\Member\Contracts;

use Modules\Member\Models\Member;

/**
 * Emit reader security events into Core Security Journal (sec_logs).
 * Always stamps metadata.realm = member; never writes member IDs into user_id.
 */
interface MemberSecurityAuditPortInterface
{
    /**
     * @param  array<string, mixed>  $metadata
     */
    public function record(
        string $eventType,
        ?Member $member = null,
        ?string $description = null,
        array $metadata = [],
        ?string $ipAddress = null,
    ): void;
}
