<?php

declare(strict_types=1);

namespace Modules\Member\Services;

use Illuminate\Http\Request;
use Modules\Member\Models\Member;
use Modules\Publishing\Contracts\MemberIdentity;
use Modules\Publishing\Contracts\MemberIdentityPort;

class SanctumMemberIdentityPort implements MemberIdentityPort
{
    public function current(?Request $request = null): ?MemberIdentity
    {
        $request ??= request();
        if ($request === null) {
            return null;
        }

        $user = $request->user('member');
        if (! $user instanceof Member || $user->status !== 'active') {
            return null;
        }

        return new MemberIdentity(
            id: (string) $user->id,
            name: (string) $user->name,
            email: (string) $user->email,
        );
    }
}
