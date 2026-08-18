<?php

namespace Modules\Core\System\Http\Controllers\Console;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Modules\Core\System\Http\Controllers\BaseApiController;
use Modules\Core\System\Jobs\SendBroadcastNotification;
use Modules\Core\System\Models\Notification;
use Modules\Core\System\Models\User;
use Modules\Core\System\Services\BroadcastNotificationService;

class NotificationController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            /** @var User|null $user */
            if (! $user) {
                return $this->unauthorized('Unauthenticated');
            }

            // Use Notification model directly with user_id filter
            $query = Notification::where('user_id', $user->id);

            if ($request->has('is_read')) {
                $query->where('is_read', $request->boolean('is_read'));
            }

            if ($request->has('type')) {
                $typeRaw = $request->type;
                $type = is_string($typeRaw) ? $typeRaw : '';
                $query->where('type', $type);
            }

            $limitRaw = $request->input('limit', 20);
            $limit = is_numeric($limitRaw) ? (int) $limitRaw : 20;

            // Always use pagination for consistency, but limit results if limit is specified
            if ($limit > 0 && $limit < 100) {
                $notifications = $query->latest()->limit($limit)->get();
                // Return as paginated response for consistency
                $paginator = new LengthAwarePaginator(
                    $notifications,
                    $notifications->count(),
                    $limit,
                    1,
                    ['path' => $request->url(), 'query' => $request->query()]
                );

                return $this->paginated($paginator, 'Notifications retrieved successfully');
            }

            $notifications = $query->latest()->paginate($limit > 0 ? $limit : 20);

            return $this->paginated($notifications, 'Notifications retrieved successfully');
        } catch (\Exception $e) {
            Log::error('Notifications index error: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            // Return empty array instead of error
            return $this->success([], 'Notifications retrieved successfully');
        }
    }

    public function unreadCount(Request $request): JsonResponse
    {
        $user = $request->user();
        /** @var User|null $user */
        if (! $user) {
            return $this->success(['count' => 0], 'Unread count retrieved');
        }

        $count = Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->count();

        return $this->success(['count' => $count], 'Unread count retrieved');
    }

    public function markAsRead(Request $request, string $notification): JsonResponse
    {
        $user = $request->user();
        /** @var User|null $user */
        if (! $user) {
            return $this->unauthorized('Unauthenticated');
        }

        $row = Notification::query()
            ->whereKey($notification)
            ->where('user_id', $user->id)
            ->first();

        if (! $row) {
            return $this->success(null, 'Notification marked as read');
        }

        if (! $row->is_read) {
            $row->markAsRead();
        }

        return $this->success($row->fresh(), 'Notification marked as read');
    }

    public function markAllAsRead(Request $request): JsonResponse
    {
        $user = $request->user();
        /** @var User|null $user */
        if (! $user) {
            return $this->unauthorized('Unauthenticated');
        }

        Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return $this->success(null, 'All notifications marked as read');
    }

    public function indexSystem(Request $request): JsonResponse
    {
        $user = $request->user();
        /** @var User|null $user */
        if (! $user) {
            return $this->unauthorized('Unauthenticated');
        }

        if (! $user->hasRole('super') && ! $user->can('manage system')) {
            return $this->forbidden('Unauthorized');
        }

        $limitRaw = $request->input('limit', 20);
        $limit = is_numeric($limitRaw) ? (int) $limitRaw : 20;

        // Group by title, message, type, and approximate created_at to find unique "broadcasts"
        $notifications = Notification::selectRaw('MIN(CAST(id AS TEXT)) as id, title, message, type, MIN(created_at) as created_at, COUNT(*) as recipient_count')
            ->groupBy('title', 'message', 'type')
            ->orderBy('created_at', 'desc')
            ->paginate($limit);

        return $this->paginated($notifications, 'System notifications retrieved');
    }

    public function revokeSystem(Request $request): JsonResponse
    {
        $user = $request->user();
        /** @var User|null $user */
        if (! $user) {
            return $this->unauthorized('Unauthenticated');
        }

        if (! $user->hasRole('super') && ! $user->can('manage system')) {
            return $this->forbidden('Unauthorized');
        }

        /** @var array{title: string, message: string, created_at: string} $validated */
        $validated = $request->validate([
            'title' => 'required|string',
            'message' => 'required|string',
            'created_at' => 'required|string',
        ]);

        $createdAt = Carbon::parse($validated['created_at']);
        $startOfMinute = (clone $createdAt)->startOfMinute();
        $endOfMinute = (clone $createdAt)->endOfMinute();

        $deleted = app(BroadcastNotificationService::class)->revoke(
            $validated['title'],
            $validated['message'],
            $validated['created_at'],
        );
        $total = $deleted['console'] + $deleted['member'];

        return $this->success($deleted, "Broadcast revoked. {$total} notifications removed.");
    }

    public function bulkRevokeSystem(Request $request): JsonResponse
    {
        $user = $request->user();
        /** @var User|null $user */
        if (! $user) {
            return $this->unauthorized('Unauthenticated');
        }

        if (! $user->hasRole('super') && ! $user->can('manage system')) {
            return $this->forbidden('Unauthorized');
        }

        $request->validate([
            'broadcasts' => 'required|array',
            'broadcasts.*.title' => 'required|string',
            'broadcasts.*.message' => 'required|string',
            'broadcasts.*.created_at' => 'required|string',
        ]);

        $totalDeleted = 0;
        $broadcasts = is_array($request->broadcasts) ? $request->broadcasts : [];

        foreach ($broadcasts as $broadcast) {
            if (! is_array($broadcast)) {
                continue;
            }
            $createdAtRaw = $broadcast['created_at'] ?? '';
            $createdAtStr = is_string($createdAtRaw) ? $createdAtRaw : '';
            $createdAt = Carbon::parse($createdAtStr);
            $startOfMinute = (clone $createdAt)->startOfMinute();
            $endOfMinute = (clone $createdAt)->endOfMinute();

            $titleRaw = $broadcast['title'] ?? '';
            $title = is_string($titleRaw) ? $titleRaw : '';
            $messageRaw = $broadcast['message'] ?? '';
            $message = is_string($messageRaw) ? $messageRaw : '';

            $countRaw = Notification::where('title', $title)
                ->where('message', $message)
                ->whereBetween('created_at', [$startOfMinute, $endOfMinute])
                ->delete();

            $count = is_numeric($countRaw) ? (int) $countRaw : 0;

            $totalDeleted += $count;
        }

        $totalDeletedStr = (string) $totalDeleted;

        return $this->success(['count' => $totalDeleted], "Bulk revocation complete. {$totalDeletedStr} notifications removed.");
    }

    public function broadcast(Request $request): JsonResponse
    {
        $user = $request->user();
        /** @var User|null $user */
        if (! $user) {
            return $this->unauthorized('Unauthenticated');
        }

        if (! $user->hasRole('super') && ! $user->can('manage system')) {
            return $this->forbidden('Unauthorized');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'type' => 'required|in:info,success,warning,error',
            'target_type' => 'required|in:all,role,user',
            'target_id' => 'required_if:target_type,user,role',
            'is_async' => 'nullable|boolean',
        ]);

        $payload = [
            'broadcast_id' => (string) Str::uuid(),
            'type' => $request->type,
            'title' => $request->title,
            'message' => $request->message,
            'target_type' => $request->target_type,
            'target_id' => $request->target_id,
            'action_url' => $request->input('action_url'),
            'sender_id' => $user->id,
        ];

        $isAsync = $request->boolean('is_async', true);
        $mustSync = $request->target_type === 'all' || config('queue.default') === 'sync';

        if ($isAsync && ! $mustSync) {
            SendBroadcastNotification::dispatch($payload);

            return $this->success(null, 'Broadcast notification queued for delivery');
        }

        app(BroadcastNotificationService::class)->deliver($payload);

        return $this->success(null, 'Broadcast notification delivered successfully');
    }

    public function destroy(Request $request, Notification $notification): JsonResponse
    {
        $user = $request->user();
        /** @var User|null $user */
        if (! $user) {
            return $this->unauthorized('Unauthenticated');
        }

        if ($notification->user_id !== $user->id) {
            return $this->forbidden('Unauthorized');
        }

        $notification->delete();

        return $this->success(null, 'Notification deleted successfully');
    }
}
