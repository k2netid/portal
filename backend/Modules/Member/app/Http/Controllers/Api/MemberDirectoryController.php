<?php

declare(strict_types=1);

namespace Modules\Member\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\System\Http\Controllers\BaseApiController;
use Modules\Member\Models\Member;

class MemberDirectoryController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = Member::query()->orderByDesc('created_at');
        $search = trim((string) $request->input('search', ''));
        if ($search !== '') {
            $query->where(function ($inner) use ($search): void {
                $inner->where('email', 'like', '%'.$search.'%')
                    ->orWhere('name', 'like', '%'.$search.'%')
                    ->orWhere('phone', 'like', '%'.$search.'%');
            });
        }

        $perPageRaw = $request->input('per_page', 15);
        $perPage = is_numeric($perPageRaw) ? min(max((int) $perPageRaw, 1), 50) : 15;

        return $this->paginated($query->paginate($perPage), 'Members retrieved');
    }

    public function update(Request $request, string $member): JsonResponse
    {
        try {
            $validated = $request->validate([
                'status' => 'required|in:active,inactive',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationError($e->errors());
        }

        $record = Member::query()->findOrFail($member);
        $record->update(['status' => $validated['status']]);

        return $this->success([
            'id' => (string) $record->id,
            'name' => (string) $record->name,
            'email' => (string) $record->email,
            'phone' => $record->phone,
            'status' => (string) $record->status,
            'email_verified_at' => $record->email_verified_at,
            'last_login_at' => $record->last_login_at,
            'created_at' => $record->created_at,
        ], 'Member updated');
    }
}
