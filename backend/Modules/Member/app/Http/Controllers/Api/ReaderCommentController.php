<?php

declare(strict_types=1);

namespace Modules\Member\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\System\Http\Controllers\BaseApiController;
use Modules\Member\Models\Member;
use Modules\Publishing\Models\Comment;

class ReaderCommentController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $member = $this->member($request);
        if ($member === null) {
            return $this->error('Unauthenticated', 401);
        }

        $perPageRaw = $request->input('per_page', 15);
        $perPage = is_numeric($perPageRaw) ? min((int) $perPageRaw, 50) : 15;

        $comments = Comment::query()
            ->with(['content:id,title,slug,type'])
            ->where('member_id', $member->id)
            ->latest()
            ->paginate($perPage);

        return $this->paginated($comments, 'Comments retrieved successfully');
    }

    private function member(Request $request): ?Member
    {
        $user = $request->user('member');

        return $user instanceof Member ? $user : null;
    }
}
