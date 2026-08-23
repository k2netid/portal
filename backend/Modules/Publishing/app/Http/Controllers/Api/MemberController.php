<?php

declare(strict_types=1);

namespace Modules\Publishing\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Modules\Core\System\Http\Controllers\BaseApiController;
use Modules\Core\System\Models\User;
use Modules\Publishing\Models\Bookmark;
use Modules\Publishing\Models\Comment;

class MemberController extends BaseApiController
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    // ─── MY COMMENTS ───────────────────────────────────────────────

    /**
     * List comments made by the authenticated user.
     */
    public function myComments(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $perPageRaw = $request->input('per_page', 15);
        $perPage = is_numeric($perPageRaw) ? min((int) $perPageRaw, 50) : 15;

        $comments = Comment::with(['content:id,title,slug,type'])
            ->where('user_id', $user->id)
            ->latest()
            ->paginate($perPage);

        return $this->paginated($comments, 'My comments retrieved successfully');
    }

    // ─── BOOKMARKS ─────────────────────────────────────────────────

    /**
     * List bookmarked articles for the authenticated user.
     */
    public function myBookmarks(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $perPageRaw = $request->input('per_page', 15);
        $perPage = is_numeric($perPageRaw) ? min((int) $perPageRaw, 50) : 15;

        $bookmarks = Bookmark::with(['content' => function ($q): void {
            $q->select('id', 'title', 'slug', 'excerpt', 'type', 'reading_time', 'published_at', 'featured_image')
                ->with('categories:id,name,slug');
        }])
            ->where('user_id', $user->id)
            ->latest()
            ->paginate($perPage);

        return $this->paginated($bookmarks, 'Bookmarks retrieved successfully');
    }

    /**
     * Add a bookmark.
     */
    public function addBookmark(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        try {
            $validated = $request->validate([
                'content_id' => 'required|uuid|exists:pub_contents,id',
            ]);
        } catch (ValidationException $e) {
            return $this->validationError($e->errors());
        }

        $existing = Bookmark::where('user_id', $user->id)
            ->where('content_id', $validated['content_id'])
            ->first();

        if ($existing) {
            return $this->success($existing->load('content'), 'Already bookmarked');
        }

        $bookmark = Bookmark::create([
            'user_id' => $user->id,
            'content_id' => $validated['content_id'],
        ]);

        return $this->success($bookmark->load('content'), 'Bookmark added successfully', 201);
    }

    /**
     * Remove a bookmark.
     */
    public function removeBookmark(Request $request, Bookmark $bookmark): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if ($bookmark->user_id !== $user->id) {
            return $this->forbidden('You can only remove your own bookmarks');
        }

        $bookmark->delete();

        return $this->success(null, 'Bookmark removed successfully');
    }

    /**
     * Check if a specific content is bookmarked by the authenticated user.
     */
    public function isBookmarked(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        try {
            $validated = $request->validate([
                'content_id' => 'required|uuid',
            ]);
        } catch (ValidationException $e) {
            return $this->validationError($e->errors());
        }

        $exists = Bookmark::where('user_id', $user->id)
            ->where('content_id', $validated['content_id'])
            ->exists();

        return $this->success(['bookmarked' => $exists]);
    }

    // ─── NEWSLETTER PREFERENCES ────────────────────────────────────

    /**
     * @return class-string|null
     */
    private function newsletterSubscriberClass(): ?string
    {
        $class = 'Modules\\Intelligence\\Newsletter\\Models\\NewsletterSubscriber';

        return class_exists($class) ? $class : null;
    }

    /**
     * Get newsletter subscription status for the authenticated user.
     */
    public function newsletterStatus(Request $request): JsonResponse
    {
        $subscriberClass = $this->newsletterSubscriberClass();
        if ($subscriberClass === null) {
            return $this->error('Newsletter module is not installed', 503);
        }

        /** @var User $user */
        $user = $request->user();

        $subscriber = $subscriberClass::where('email', $user->email)
            ->withTrashed()
            ->first();

        if (! $subscriber) {
            return $this->success([
                'subscribed' => false,
                'status' => null,
                'subscribed_at' => null,
            ], 'Not subscribed');
        }

        return $this->success([
            'subscribed' => $subscriber->status === 'subscribed' && $subscriber->deleted_at === null,
            'status' => $subscriber->status,
            'subscribed_at' => $subscriber->subscribed_at,
            'unsubscribed_at' => $subscriber->unsubscribed_at,
        ], 'Newsletter status retrieved');
    }

    /**
     * Update newsletter subscription for the authenticated user.
     */
    public function updateNewsletter(Request $request): JsonResponse
    {
        $subscriberClass = $this->newsletterSubscriberClass();
        if ($subscriberClass === null) {
            return $this->error('Newsletter module is not installed', 503);
        }

        /** @var User $user */
        $user = $request->user();

        try {
            $validated = $request->validate([
                'subscribe' => 'required|boolean',
            ]);
        } catch (ValidationException $e) {
            return $this->validationError($e->errors());
        }

        $wantsSubscribe = (bool) $validated['subscribe'];

        if ($wantsSubscribe) {
            $subscriber = $subscriberClass::withTrashed()
                ->where('email', $user->email)
                ->first();

            if ($subscriber) {
                if ($subscriber->trashed()) {
                    $subscriber->restore();
                }
                $subscriber->update([
                    'status' => 'subscribed',
                    'name' => $user->name,
                    'subscribed_at' => now(),
                    'unsubscribed_at' => null,
                    'source' => 'member_portal',
                ]);
            } else {
                $subscriber = $subscriberClass::create([
                    'email' => $user->email,
                    'name' => $user->name,
                    'status' => 'subscribed',
                    'subscribed_at' => now(),
                    'source' => 'member_portal',
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);
            }

            return $this->success([
                'subscribed' => true,
                'status' => 'subscribed',
                'subscribed_at' => $subscriber->subscribed_at,
            ], 'Successfully subscribed to newsletter');
        }

        $subscriber = $subscriberClass::where('email', $user->email)->first();
        if ($subscriber) {
            $subscriber->update([
                'status' => 'unsubscribed',
                'unsubscribed_at' => now(),
            ]);
        }

        return $this->success([
            'subscribed' => false,
            'status' => 'unsubscribed',
        ], 'Successfully unsubscribed from newsletter');
    }
}
