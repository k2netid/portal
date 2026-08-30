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
                    ->orWhere('name', 'like', '%'.$search.'%');
            });
        }

        $perPageRaw = $request->input('per_page', 15);
        $perPage = is_numeric($perPageRaw) ? min(max((int) $perPageRaw, 1), 50) : 15;

        return $this->paginated($query->paginate($perPage), 'Members retrieved');
    }
}
