<?php

declare(strict_types=1);

namespace Modules\Member\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\System\Http\Controllers\BaseApiController;
use Modules\Member\Models\Member;
use Modules\Member\Services\MemberPortalService;

class PortalController extends BaseApiController
{
    public function show(Request $request, MemberPortalService $portal): JsonResponse
    {
        $member = $request->user('member');
        if (! $member instanceof Member) {
            return $this->error('Unauthenticated', 401);
        }

        return $this->success($portal->build($member), 'Member portal');
    }
}
