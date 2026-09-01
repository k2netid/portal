<?php

declare(strict_types=1);

namespace Modules\Core\System\Services;

use Modules\Core\Security\Rules\StrongPassword;
use Modules\Core\System\Contracts\PasswordPolicyPortInterface;
use Modules\Core\System\Models\Setting;

final class PasswordPolicyService implements PasswordPolicyPortInterface
{
    public function rule(): StrongPassword
    {
        return new StrongPassword;
    }

    public function requirements(): array
    {
        $minLenRaw = Setting::get('password_min_length', 8);

        return [
            'min_length' => is_numeric($minLenRaw) ? (int) $minLenRaw : 8,
            'require_uppercase' => filter_var(Setting::get('password_require_uppercase', true), FILTER_VALIDATE_BOOLEAN),
            'require_lowercase' => filter_var(Setting::get('password_require_lowercase', true), FILTER_VALIDATE_BOOLEAN),
            'require_number' => filter_var(Setting::get('password_require_number', true), FILTER_VALIDATE_BOOLEAN),
            'require_symbol' => filter_var(Setting::get('password_require_symbol', false), FILTER_VALIDATE_BOOLEAN),
        ];
    }
}
