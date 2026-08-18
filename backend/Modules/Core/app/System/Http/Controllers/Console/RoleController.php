<?php

namespace Modules\Core\System\Http\Controllers\Console;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Core\System\Http\Controllers\BaseApiController;
use Modules\Core\System\Models\Permission;
use Modules\Core\System\Models\Role;
use Modules\Core\System\Models\User;
use Modules\Core\System\Support\SqlLikeEscape;

class RoleController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $limitRaw = $request->input('limit', 10);
        $limit = is_numeric($limitRaw) ? (int) $limitRaw : 10;
        $searchRaw = $request->input('search');
        $search = is_string($searchRaw) ? $searchRaw : '';

        $query = Role::with('permissions')->orderBy('name');

        if ($search !== '') {
            $pat = SqlLikeEscape::contains(mb_strtolower($search, 'UTF-8'));
            $esc = SqlLikeEscape::LIKE_ESCAPE_SQL;
            $query->whereRaw('LOWER(name) LIKE ? '.$esc, [$pat]);
        }

        /** @var LengthAwarePaginator<int, Role> $roles */
        $roles = $query->paginate($limit);

        // Optimized: Batch fetch users count in one query to avoid N+1 issues
        $roleIds = $roles->getCollection()->pluck('id')->toArray();

        $tableNameRaw = config('permission.table_names.model_has_roles');
        $tableName = is_string($tableNameRaw) ? $tableNameRaw : 'model_has_roles';

        $columnNameRaw = config('permission.column_names.role_pivot_key');
        $columnName = is_string($columnNameRaw) ? $columnNameRaw : 'role_id';

        $userCounts = \DB::table($tableName)
            ->whereIn($columnName, $roleIds)
            ->select([$columnName, \DB::raw('count(*) as total')])
            ->groupBy($columnName)
            ->pluck('total', $columnName);

        $roles->getCollection()->transform(function (mixed $role) use ($userCounts): Role {
            /** @var Role $role */
            $role->setAttribute('users_count', $userCounts[$role->id] ?? 0);

            return $role;
        });

        return $this->success($roles, 'Roles retrieved successfully');
    }

    public function bulkAction(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'action' => 'required|string|in:delete',
            'ids' => 'required|array',
            'ids.*' => 'exists:roles,id',
        ]);

        $action = $validated['action'];
        $idsRaw = $validated['ids'];
        $ids = is_array($idsRaw) ? $idsRaw : [];
        $count = 0;

        foreach ($ids as $id) {
            /** @var Role|null $role */
            $role = Role::find($id);

            if (! $role) {
                continue;
            }

            // Prevent deleting protected roles
            if ($role->name === 'super') {
                continue;
            }

            // Prevent deleting roles with users
            /** @var BelongsToMany<User, \Spatie\Permission\Models\Role> $usersRelation */
            $usersRelation = $role->users();
            if ($usersRelation->count() > 0) {
                continue;
            }

            if ($action === 'delete') {
                $role->delete();
                $count++;
            }
        }

        $countStr = (string) $count;

        return $this->success(null, ucfirst((string) $action)." completed for {$countStr} roles");
    }

    public function show(Role $role): JsonResponse
    {
        $role->load('permissions');

        $tableNameRaw = config('permission.table_names.model_has_roles');
        $tableName = is_string($tableNameRaw) ? $tableNameRaw : 'model_has_roles';

        $columnNameRaw = config('permission.column_names.role_pivot_key');
        $columnName = is_string($columnNameRaw) ? $columnNameRaw : 'role_id';

        $role->setAttribute('users_count', \DB::table($tableName)
            ->where($columnName, $role->id)
            ->count());

        return $this->success($role, 'Role retrieved successfully');
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        $name = is_string($validated['name']) ? $validated['name'] : '';

        $role = Role::create(['name' => $name, 'guard_name' => 'web']);

        if (! empty($validated['permissions']) && is_array($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        }

        return $this->success($role->load('permissions'), 'Role created successfully', 201);
    }

    public function update(Request $request, Role $role): JsonResponse
    {
        // Prevent editing protected roles
        if ($role->name === 'super') {
            return $this->error('Cannot modify protected role', 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255|unique:roles,name,'.$role->id,
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        if (isset($validated['name'])) {
            $role->update(['name' => $validated['name']]);
        }

        if (isset($validated['permissions']) && is_array($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        }

        return $this->success($role->load('permissions'), 'Role updated successfully');
    }

    public function destroy(Role $role): JsonResponse
    {
        // Prevent deleting protected roles
        if ($role->name === 'super') {
            return $this->error('Cannot delete protected role', 403);
        }

        // Check if role has users
        /** @var BelongsToMany<User, \Spatie\Permission\Models\Role> $usersRelation */
        $usersRelation = $role->users();
        if ($usersRelation->count() > 0) {
            return $this->error('Cannot delete role with assigned users', 400);
        }

        $role->delete();

        return $this->success(null, 'Role deleted successfully');
    }

    public function permissions(): JsonResponse
    {
        $permissions = Permission::orderBy('name')->get()->groupBy(function (Permission $permission): string {
            // Group by category (resource name - everything after the first word)
            $nameStr = $permission->name;
            $parts = explode(' ', $nameStr, 2);

            return isset($parts[1]) ? ucfirst($parts[1]) : 'General';
        });

        return $this->success($permissions, 'Permissions retrieved successfully');
    }

    public function syncPermissions(Request $request, Role $role): JsonResponse
    {
        // Prevent modifying super permissions
        if ($role->name === 'super') {
            return $this->error('Cannot modify super permissions', 403);
        }

        $validated = $request->validate([
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        $permissions = is_array($validated['permissions']) ? $validated['permissions'] : [];

        $role->syncPermissions($permissions);

        return $this->success($role->load('permissions'), 'Permissions synced successfully');
    }

    public function duplicate(Role $role): JsonResponse
    {
        $newRole = Role::create([
            'name' => ($role->name).' (copy)',
            'guard_name' => 'web',
        ]);

        $newRole->syncPermissions($role->permissions);

        return $this->success($newRole->load('permissions'), 'Role duplicated successfully', 201);
    }
}
