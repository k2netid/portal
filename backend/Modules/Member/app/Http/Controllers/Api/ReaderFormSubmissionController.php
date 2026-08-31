<?php

declare(strict_types=1);

namespace Modules\Member\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\System\Http\Controllers\BaseApiController;
use Modules\Forms\Models\FormSubmission;
use Modules\Member\Models\Member;

class ReaderFormSubmissionController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $member = $this->member($request);
        if ($member === null) {
            return $this->error('Unauthenticated', 401);
        }

        $perPageRaw = $request->input('per_page', 15);
        $perPage = is_numeric($perPageRaw) ? min((int) $perPageRaw, 50) : 15;

        $submissions = FormSubmission::query()
            ->with(['form:id,name,slug'])
            ->where('member_id', $member->id)
            ->latest()
            ->paginate($perPage);

        return $this->paginated($submissions, 'Form submissions retrieved successfully');
    }

    private function member(Request $request): ?Member
    {
        $user = $request->user('member');

        return $user instanceof Member ? $user : null;
    }
}
