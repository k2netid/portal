<?php

declare(strict_types=1);

namespace Modules\Member\Services;

use Modules\Core\Security\Models\SecurityLog;
use Modules\Core\System\Helpers\IpHelper;
use Modules\Member\Contracts\MemberSecurityAuditPortInterface;
use Modules\Member\Models\Member;

final class MemberSecurityAuditService implements MemberSecurityAuditPortInterface
{
    /**
     * {@inheritdoc}
     */
    public function record(
        string $eventType,
        ?Member $member = null,
        ?string $description = null,
        array $metadata = [],
        ?string $ipAddress = null,
    ): void {
        $meta = array_merge($metadata, [
            'realm' => 'member',
        ]);

        if ($member !== null) {
            $meta['member_id'] = $member->id;
            $meta['member_email'] = $member->email;
        }

        SecurityLog::log(
            $eventType,
            null,
            $ipAddress ?? IpHelper::getClientIp(request()),
            $description,
            $meta,
        );
    }
}
