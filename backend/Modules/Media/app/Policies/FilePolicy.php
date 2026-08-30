<?php

declare(strict_types=1);

namespace Modules\Media\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\Core\System\Models\User;
use Modules\Media\Models\File;

class FilePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view media') || $user->hasPermissionTo('manage media');
    }

    public function view(User $user, File $file): bool
    {
        return $user->hasPermissionTo('view media') || $user->hasPermissionTo('manage media');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('upload media') || $user->hasPermissionTo('manage media');
    }

    public function update(User $user, File $file): bool
    {
        return $user->hasPermissionTo('edit media') || $user->hasPermissionTo('manage media');
    }

    public function delete(User $user, File $file): bool
    {
        return $user->hasPermissionTo('delete media') || $user->hasPermissionTo('manage media');
    }

    public function restore(User $user, File $file): bool
    {
        return $user->hasPermissionTo('manage media');
    }

    public function forceDelete(User $user, File $file): bool
    {
        return $user->hasPermissionTo('manage media');
    }
}
