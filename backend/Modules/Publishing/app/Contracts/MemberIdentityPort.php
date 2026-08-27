<?php

declare(strict_types=1);

namespace Modules\Publishing\Contracts;

use Illuminate\Http\Request;

interface MemberIdentityPort
{
    public function current(?Request $request = null): ?MemberIdentity;
}
