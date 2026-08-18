<?php

namespace Modules\Core\System\Http\Controllers\Console;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Modules\Core\System\Http\Controllers\BaseApiController;
use Modules\Core\System\Models\Role;
use Modules\Core\System\Models\User;
use Modules\Core\System\Support\SqlLikeEscape;

/**
 * Manage module-scoped access (RBAC) without granting Core governance.
 *
 * Scope strategy (phase 1):
 * - Publishing roles must be prefixed: `publishing:...`
 */
class ModuleAccessController extends BaseApiController
{
    private const MODULES = ['publishing', 'crm', 'accounting'];

    public function roles(Request $request, string $module): JsonResponse
    {
        if (! in_array($module, self::MODULES, true)) {
            return $this->notFound('Module');
        }

        $roles = $this->queryScopedRoles($module)->with('permissions')->orderBy('name')->get();

        return $this->success($roles, 'Module roles retrieved successfully');
    }

    public function users(Request $request, string $module): JsonResponse
    {
        if (! in_array($module, self::MODULES, true)) {
            return $this->notFound('Module');
        }

        $perPageRaw = $request->input('per_page', 20);
        $perPage = min(max(is_numeric($perPageRaw) ? (int) $perPageRaw : 20, 1), 100);

        $query = User::query()->with('roles')->orderByDesc('id');

        if ($request->filled('search')) {
            $searchRaw = $request->input('search');
            $search = is_string($searchRaw) ? $searchRaw : '';
            $query->where(function ($q) use ($search): void {
                $pat = SqlLikeEscape::contains(mb_strtolower($search, 'UTF-8'));
                $esc = SqlLikeEscape::LIKE_ESCAPE_SQL;
                $q->whereRaw('LOWER(name) LIKE ? '.$esc, [$pat])
                    ->orWhereRaw('LOWER(email) LIKE ? '.$esc, [$pat]);
            });
        }

        $paginator = $query->paginate($perPage);
        $paginator->getCollection()->transform(function (User $user) use ($module): User {
            /** @var Collection<int, Role> $roles */
            $roles = $user->roles;
            $scoped = $roles->filter(fn (Role $r): bool => $this->roleMatchesModule($r, $module))->values();
            $user->setRelation('roles', $scoped);
            $user->setRelation('permissions', $user->getAllPermissions());

            return $user;
        });

        return $this->paginated($paginator, 'Module users retrieved successfully');
    }

    public function updateUserRoles(Request $request, string $module, User $user): JsonResponse
    {
        if (! in_array($module, self::MODULES, true)) {
            return $this->notFound('Module');
        }

        $validated = $request->validate([
            'roles' => 'required|array',
            'roles.*' => 'string',
        ]);

        $requestedNames = array_values(array_filter($validated['roles'], fn ($v): bool => is_string($v) && $v !== ''));

        $allowed = $this->queryScopedRoles($module)->whereIn('name', $requestedNames)->get()->pluck('name')->all();

        // Keep non-module roles intact; replace only module-scoped roles.
        $existing = $user->getRoleNames()->filter(fn ($name): bool => is_string($name))->values()->all();
        $kept = array_values(array_filter($existing, function (string $name) use ($module): bool {
            $role = Role::where('name', $name)->first();
            if (! $role) {
                return false;
            }

            return ! $this->roleMatchesModule($role, $module);
        }));

        /** @var array<int, string> $kept */
        $kept = array_values(array_filter($kept, fn ($v): bool => $v !== ''));
        /** @var array<int, string> $allowed */
        $allowed = array_values(array_filter($allowed, fn ($v): bool => is_string($v) && $v !== ''));

        $user->syncRoles(array_values(array_unique(array_merge($kept, $allowed))));
        $user->load('roles');
        $user->setRelation('permissions', $user->getAllPermissions());

        return $this->success($user, 'Module roles updated successfully');
    }

    /**
     * @return Builder<Role>
     */
    private function queryScopedRoles(string $module): Builder
    {
        return Role::query()->where('name', 'like', 'publishing:%');
    }

    private function roleMatchesModule(Role $role, string $module): bool
    {
        return str_starts_with($role->name, 'publishing:');
    }
}
