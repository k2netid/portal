<?php

declare(strict_types=1);

namespace Modules\Member\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\System\Http\Controllers\BaseApiController;
use Modules\Member\Models\Member;
use Modules\Member\Services\MemberAccountService;
use Modules\Member\Support\MemberDirectorySupport;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MemberDirectoryController extends BaseApiController
{
    public function stats(): JsonResponse
    {
        $base = Member::query();

        return $this->success([
            'total' => (clone $base)->count(),
            'verified' => (clone $base)->whereNotNull('email_verified_at')->count(),
            'unverified' => (clone $base)->whereNull('email_verified_at')->count(),
            'active_status' => (clone $base)->where('status', 'active')->count(),
            'inactive_status' => (clone $base)->where('status', 'inactive')->count(),
            'recent' => (clone $base)->where('created_at', '>=', now()->subDays(7))->count(),
            'active' => (clone $base)->whereNotNull('last_login_at')
                ->where('last_login_at', '>=', now()->subDays(30))
                ->count(),
            'trashed' => Member::onlyTrashed()->count(),
        ], 'Member statistics retrieved');
    }

    public function index(Request $request): JsonResponse
    {
        $perPageRaw = $request->input('per_page', 15);
        $perPage = is_numeric($perPageRaw) ? min(max((int) $perPageRaw, 1), 50) : 15;

        $query = MemberDirectorySupport::filteredQuery($request);

        return $this->paginated(
            $query->paginate($perPage)->through(
                static fn (Member $member): array => MemberDirectorySupport::serialize($member),
            ),
            'Members retrieved',
        );
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate(MemberDirectorySupport::adminStoreRules());
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationError($e->errors());
        }

        $member = Member::query()->create([
            'name' => trim((string) $validated['name']),
            'email' => trim((string) $validated['email']),
            'password' => (string) $validated['password'],
            'phone' => isset($validated['phone']) && is_string($validated['phone']) && trim($validated['phone']) !== ''
                ? trim($validated['phone'])
                : null,
            'avatar' => isset($validated['avatar']) && is_string($validated['avatar']) && trim($validated['avatar']) !== ''
                ? trim($validated['avatar'])
                : null,
            'bio' => isset($validated['bio']) && is_string($validated['bio']) && trim($validated['bio']) !== ''
                ? trim($validated['bio'])
                : null,
            'locale' => isset($validated['locale']) && is_string($validated['locale']) && trim($validated['locale']) !== ''
                ? trim($validated['locale'])
                : null,
            'timezone' => isset($validated['timezone']) && is_string($validated['timezone']) && trim($validated['timezone']) !== ''
                ? trim($validated['timezone'])
                : null,
            'status' => (string) ($validated['status'] ?? 'active'),
            'email_verified_at' => ($validated['verify_email'] ?? false) === true ? now() : null,
        ]);

        return $this->success(
            MemberDirectorySupport::serialize($member, detailed: true),
            'Member created',
            201,
        );
    }

    public function show(string $member): JsonResponse
    {
        $record = Member::withTrashed()->findOrFail($member);

        return $this->success(
            MemberDirectorySupport::serialize($record, detailed: true),
            'Member retrieved',
        );
    }

    public function update(Request $request, string $member): JsonResponse
    {
        $record = Member::withTrashed()->findOrFail($member);

        try {
            $validated = $request->validate(MemberDirectorySupport::adminUpdateRules($record));
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationError($e->errors());
        }

        if ($validated === []) {
            return $this->validationError(['name' => ['No valid fields to update.']]);
        }

        $profileKeys = ['name', 'phone', 'avatar', 'bio', 'locale', 'timezone'];
        foreach ($profileKeys as $key) {
            if (! array_key_exists($key, $validated)) {
                continue;
            }
            $value = $validated[$key];
            if ($key === 'name') {
                $record->name = trim((string) $value);
                continue;
            }
            $record->{$key} = ($value === null || (is_string($value) && trim($value) === ''))
                ? null
                : (is_string($value) ? trim($value) : $value);
        }

        if (array_key_exists('email', $validated)) {
            $record->email = trim((string) $validated['email']);
            $record->pending_email = null;
        }

        if (! empty($validated['password'])) {
            $record->password = (string) $validated['password'];
        }

        if (array_key_exists('status', $validated)) {
            $record->status = (string) $validated['status'];
        }

        if (($validated['verify_email'] ?? false) === true && $record->email_verified_at === null) {
            $record->email_verified_at = now();
        }

        $record->save();

        if (($validated['status'] ?? null) === 'inactive' || $record->trashed()) {
            $record->tokens()->delete();
        }

        return $this->success(
            MemberDirectorySupport::serialize($record->fresh() ?? $record, detailed: true),
            'Member updated',
        );
    }

    public function destroy(string $member, MemberAccountService $accountService): JsonResponse
    {
        $record = Member::query()->findOrFail($member);
        $accountService->softDelete($record);

        return $this->success(null, 'Member moved to trash');
    }

    public function restore(string $member): JsonResponse
    {
        $record = Member::onlyTrashed()->findOrFail($member);
        $record->restore();

        return $this->success(
            MemberDirectorySupport::serialize($record->fresh() ?? $record),
            'Member restored',
        );
    }

    public function forceDelete(string $member, MemberAccountService $accountService): JsonResponse
    {
        $record = Member::withTrashed()->findOrFail($member);
        $accountService->forceDelete($record);

        return $this->success(null, 'Member permanently deleted');
    }

    public function bulkAction(Request $request, MemberAccountService $accountService): JsonResponse
    {
        try {
            $validated = $request->validate([
                'ids' => 'required|array|min:1',
                'ids.*' => ['required', 'uuid'],
                'action' => 'required|in:activate,deactivate,verify,delete,restore,force_delete',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationError($e->errors());
        }

        $ids = $validated['ids'];
        $action = (string) $validated['action'];
        $count = 0;

        if ($action === 'activate') {
            $count = Member::withTrashed()->whereIn('id', $ids)->update(['status' => 'active']);
        } elseif ($action === 'deactivate') {
            Member::query()->whereIn('id', $ids)->each(function (Member $member): void {
                $member->tokens()->delete();
            });
            $count = Member::query()->whereIn('id', $ids)->update(['status' => 'inactive']);
        } elseif ($action === 'verify') {
            $count = Member::query()
                ->whereIn('id', $ids)
                ->whereNull('email_verified_at')
                ->update(['email_verified_at' => now()]);
        } elseif ($action === 'delete') {
            Member::query()->whereIn('id', $ids)->get()->each(function (Member $member) use ($accountService): void {
                $accountService->softDelete($member);
            });
            $count = count($ids);
        } elseif ($action === 'restore') {
            $count = Member::onlyTrashed()->whereIn('id', $ids)->restore() ?: 0;
        } elseif ($action === 'force_delete') {
            Member::withTrashed()->whereIn('id', $ids)->get()->each(function (Member $member) use ($accountService): void {
                $accountService->forceDelete($member);
            });
            $count = count($ids);
        }

        return $this->success(['count' => $count], 'Bulk action completed');
    }

    public function export(Request $request): StreamedResponse|JsonResponse
    {
        try {
            $query = MemberDirectorySupport::filteredQuery($request);
            $members = $query->limit(5000)->get();

            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="members-'.now()->format('Y-m-d').'.csv"',
            ];

            $callback = static function () use ($members): void {
                $file = fopen('php://output', 'w');
                if ($file === false) {
                    return;
                }
                fputcsv($file, ['ID', 'Name', 'Email', 'Phone', 'Status', 'Verified', 'Last Login', 'Joined At']);
                foreach ($members as $member) {
                    fputcsv($file, [
                        (string) $member->id,
                        (string) $member->name,
                        (string) $member->email,
                        (string) ($member->phone ?? ''),
                        (string) $member->status,
                        $member->email_verified_at ? 'yes' : 'no',
                        (string) ($member->last_login_at ?? ''),
                        (string) ($member->created_at ?? ''),
                    ]);
                }
                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        } catch (\Throwable $e) {
            return $this->error('Failed to export members', 500);
        }
    }
}
