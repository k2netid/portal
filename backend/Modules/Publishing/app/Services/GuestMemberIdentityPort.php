<?php

declare(strict_types=1);

namespace Modules\Publishing\Services;

use Illuminate\Http\Request;
use Modules\Publishing\Contracts\MemberIdentity;
use Modules\Publishing\Contracts\MemberIdentityPort;

/**
 * Default: public comments stay guest-or-explicit. Console User is never implied.
 */
class GuestMemberIdentityPort implements MemberIdentityPort
{
    public function current(?Request $request = null): ?MemberIdentity
    {
        return null;
    }
}
