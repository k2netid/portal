<?php

declare(strict_types=1);

namespace Modules\Member\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Modules\Core\System\Http\Controllers\BaseApiController;
use Modules\Member\Models\Member;
use Modules\Member\Models\MemberBookmark;

class BookmarkController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $member = $this->member($request);
        if ($member === null) {
            return $this->error('Unauthenticated', 401);
        }

        $perPageRaw = $request->input('per_page', 15);
        $perPage = is_numeric($perPageRaw) ? min((int) $perPageRaw, 50) : 15;

        $query = MemberBookmark::query()
            ->with(['content' => function ($q): void {
                $q->select('id', 'title', 'slug', 'excerpt', 'type', 'published_at', 'featured_image');
            }])
            ->where('member_id', $member->id);

        $contentId = $request->input('content_id');
        if (is_string($contentId) && $contentId !== '') {
            $query->where('content_id', $contentId);
        }

        $bookmarks = $query
            ->latest()
            ->paginate($perPage);

        return $this->paginated($bookmarks, 'Bookmarks retrieved successfully');
    }

    public function store(Request $request): JsonResponse
    {
        $member = $this->member($request);
        if ($member === null) {
            return $this->error('Unauthenticated', 401);
        }

        try {
            $validated = $request->validate([
                'content_id' => 'required|uuid|exists:pub_contents,id',
            ]);
        } catch (ValidationException $e) {
            return $this->validationError($e->errors());
        }

        $existing = MemberBookmark::query()
            ->where('member_id', $member->id)
            ->where('content_id', $validated['content_id'])
            ->first();

        if ($existing) {
            return $this->success($existing->load('content'), 'Already bookmarked');
        }

        $bookmark = MemberBookmark::query()->create([
            'member_id' => $member->id,
            'content_id' => $validated['content_id'],
        ]);

        return $this->success($bookmark->load('content'), 'Bookmark added successfully', 201);
    }

    public function destroy(Request $request, MemberBookmark $bookmark): JsonResponse
    {
        $member = $this->member($request);
        if ($member === null) {
            return $this->error('Unauthenticated', 401);
        }

        if ($bookmark->member_id !== $member->id) {
            return $this->forbidden('You can only remove your own bookmarks');
        }

        $bookmark->delete();

        return $this->success(null, 'Bookmark removed successfully');
    }

    private function member(Request $request): ?Member
    {
        $user = $request->user('member');

        return $user instanceof Member ? $user : null;
    }
}
