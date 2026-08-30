<?php

namespace Modules\Newsletter\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Modules\Core\System\Helpers\IpHelper;
use Modules\Core\System\Http\Controllers\BaseApiController;
use Modules\Core\System\Support\SqlLikeEscape;
use Modules\Newsletter\Mail\NewsletterWelcome;
use Modules\Newsletter\Models\NewsletterSubscriber;
use Symfony\Component\HttpFoundation\StreamedResponse;

class NewsletterController extends BaseApiController
{
    public function __construct()
    {
        $this->middleware('auth:sanctum')->except(['subscribe']);
        $this->middleware('permission:manage newsletter')->except(['subscribe']);
    }

    public function subscribe(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:255',
            'name' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors()->toArray());
        }

        try {
            $emailRaw = $request->email;
            $email = is_string($emailRaw) ? $emailRaw : '';
            $nameRaw = $request->name;
            $name = is_string($nameRaw) ? $nameRaw : null;

            // Check if already subscribed
            $existing = NewsletterSubscriber::where('email', $email)->first();

            if ($existing) {
                if ($existing->status === 'subscribed') {
                    return $this->error('Email sudah terdaftar untuk newsletter', 422);
                }

                // Re-subscribe
                $existing->update([
                    'status' => 'subscribed',
                    'subscribed_at' => now(),
                    'unsubscribed_at' => null,
                    'name' => $name ?? $existing->name,
                    'source' => $request->header('referer') ?? 'unknown',
                    'ip_address' => IpHelper::getClientIp($request),
                    'user_agent' => $request->userAgent(),
                ]);

                return $this->success([
                    'subscriber' => $existing,
                ], 'Berhasil berlangganan newsletter!');
            }

            // Create new subscriber
            $subscriber = NewsletterSubscriber::create([
                'email' => $email,
                'name' => $name,
                'status' => 'subscribed',
                'subscribed_at' => now(),
                'source' => $request->header('referer') ?? 'unknown',
                'ip_address' => IpHelper::getClientIp($request),
                'user_agent' => $request->userAgent(),
            ]);

            // Send welcome email
            Mail::to($email)->send(new NewsletterWelcome($subscriber));

            return $this->success([
                'subscriber' => $subscriber,
            ], 'Berhasil berlangganan newsletter!', 201);
        } catch (\Exception $e) {
            Log::error('Newsletter subscription error: '.$e->getMessage());

            return $this->error('Terjadi kesalahan saat mendaftar newsletter', 500);
        }
    }

    public function unsubscribe(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors()->toArray());
        }

        try {
            $emailRaw = $request->email;
            $email = is_string($emailRaw) ? $emailRaw : '';
            $subscriber = NewsletterSubscriber::where('email', $email)->first();

            if (! $subscriber) {
                return $this->error('Email tidak ditemukan', 404);
            }

            if ($subscriber->status === 'unsubscribed') {
                return $this->error('Email sudah berhenti berlangganan', 422);
            }

            $subscriber->update([
                'status' => 'unsubscribed',
                'unsubscribed_at' => now(),
            ]);

            return $this->success(null, 'Berhasil berhenti berlangganan');
        } catch (\Exception $e) {
            Log::error('Newsletter unsubscribe error: '.$e->getMessage());

            return $this->error('Terjadi kesalahan saat berhenti berlangganan', 500);
        }
    }

    /**
     * Admin: Get paginated subscribers
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = NewsletterSubscriber::query();

            if ($request->filled('q')) {
                $searchRaw = $request->q;
                $search = is_string($searchRaw) ? $searchRaw : '';
                SqlLikeEscape::whereContainsAny($query, ['email', 'name'], $search);
            }

            // Filter by status
            if ($request->has('status')) {
                $statusRaw = $request->status;
                $status = is_string($statusRaw) ? $statusRaw : '';
                if (in_array($status, ['subscribed', 'unsubscribed'])) {
                    $query->where('status', $status);
                }
            }

            // Soft deletes filter
            if ($request->has('trashed')) {
                $trashedRaw = $request->trashed;
                $trashed = is_string($trashedRaw) ? $trashedRaw : '';
                if ($trashed === 'only') {
                    $query->onlyTrashed();
                } elseif ($trashed === 'with') {
                    $query->withTrashed();
                }
            }

            $perPageRaw = $request->get('per_page', 10);
            $perPage = is_numeric($perPageRaw) ? (int) $perPageRaw : 10;
            $subscribers = $query->orderBy('created_at', 'desc')->paginate($perPage);

            return $this->success($subscribers);
        } catch (\Exception $e) {
            Log::error('Newsletter admin index error: '.$e->getMessage());

            return $this->error('Failed to fetch subscribers', 500);
        }
    }

    /**
     * Admin: Delete subscriber
     *
     * @param  string|int  $id
     */
    public function destroy($id): JsonResponse
    {
        try {
            /** @var NewsletterSubscriber $subscriber */
            $subscriber = NewsletterSubscriber::findOrFail($id);
            $subscriber->delete();

            return $this->success(null, 'Subscriber deleted successfully');
        } catch (\Exception) {
            return $this->error('Failed to delete subscriber', 500);
        }
    }

    /**
     * @param  string|int  $id
     */
    public function restore($id): JsonResponse
    {
        try {
            /** @var NewsletterSubscriber $subscriber */
            $subscriber = NewsletterSubscriber::withTrashed()->findOrFail($id);
            $subscriber->restore();

            return $this->success(null, 'Subscriber restored successfully');
        } catch (\Exception) {
            return $this->error('Failed to restore subscriber', 500);
        }
    }

    /**
     * @param  string|int  $id
     */
    public function forceDelete($id): JsonResponse
    {
        try {
            /** @var NewsletterSubscriber $subscriber */
            $subscriber = NewsletterSubscriber::withTrashed()->findOrFail($id);
            $subscriber->forceDelete();

            return $this->success(null, 'Subscriber permanently deleted');
        } catch (\Exception) {
            return $this->error('Failed to permanently delete subscriber', 500);
        }
    }

    /**
     * Admin: Export subscribers (returns raw CSV data)
     */
    public function export(Request $request): StreamedResponse|JsonResponse
    {
        try {
            $query = NewsletterSubscriber::query();

            $statusRaw = $request->status;
            $status = is_string($statusRaw) ? $statusRaw : '';
            if ($status && in_array($status, ['subscribed', 'unsubscribed'])) {
                $query->where('status', $status);
            }

            $subscribers = $query->orderBy('created_at', 'desc')->get();

            $headers = [
                'Content-type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename=subscribers.csv',
                'Pragma' => 'no-cache',
                'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
                'Expires' => '0',
            ];

            $callback = function () use ($subscribers): void {
                $file = fopen('php://output', 'w');
                if ($file === false) {
                    return;
                }
                fputcsv($file, ['ID', 'Email', 'Name', 'Status', 'Joined At', 'Source']);

                foreach ($subscribers as $subscriber) {
                    fputcsv($file, [
                        (string) $subscriber->id,
                        (string) $subscriber->email,
                        (string) $subscriber->name,
                        (string) $subscriber->status,
                        (string) $subscriber->created_at,
                        (string) $subscriber->source,
                    ]);
                }
                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        } catch (\Exception $e) {
            Log::error('Newsletter export error: '.$e->getMessage());

            return $this->error('Failed to export subscribers', 500);
        }
    }

    public function bulkAction(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:nwl_subscribers,id',
            'action' => 'required|in:delete,unsubscribe,subscribe,restore,force_delete',
        ]);

        $idsRaw = $request->ids;
        $ids = is_array($idsRaw) ? $idsRaw : [];
        $actionRaw = $request->action;
        $action = is_string($actionRaw) ? $actionRaw : '';

        try {
            if ($action === 'delete') {
                NewsletterSubscriber::whereIn('id', $ids)->delete();

                return $this->success(null, 'Selected subscribers deleted successfully');
            }

            if ($action === 'unsubscribe') {
                NewsletterSubscriber::whereIn('id', $ids)->update(['status' => 'unsubscribed', 'unsubscribed_at' => now()]);

                return $this->success(null, 'Selected subscribers unsubscribed successfully');
            }

            if ($action === 'subscribe') {
                NewsletterSubscriber::whereIn('id', $ids)->update(['status' => 'subscribed', 'subscribed_at' => now(), 'unsubscribed_at' => null]);

                return $this->success(null, 'Selected subscribers subscribed successfully');
            }

            if ($action === 'restore') {
                NewsletterSubscriber::withTrashed()->whereIn('id', $ids)->restore();

                return $this->success(null, 'Selected subscribers restored successfully');
            }

            if ($action === 'force_delete') {
                NewsletterSubscriber::withTrashed()->whereIn('id', $ids)->forceDelete();

                return $this->success(null, 'Selected subscribers permanently deleted');
            }

        } catch (\Exception $e) {
            return $this->error('Bulk action failed: '.$e->getMessage(), 500);
        }

        return $this->error('Invalid action', 422);
    }
}
