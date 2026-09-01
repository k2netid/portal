<?php

declare(strict_types=1);

namespace Modules\Core\System\Contracts;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Rule;

interface PasswordPolicyPortInterface
{
    /**
     * Laravel validation rule driven by System security settings.
     *
     * @return Rule|ValidationRule|string
     */
    public function rule(): mixed;

    /**
     * Public-safe requirements for forms (min length + flags).
     *
     * @return array{
     *   min_length: int,
     *   require_uppercase: bool,
     *   require_lowercase: bool,
     *   require_number: bool,
     *   require_symbol: bool
     * }
     */
    public function requirements(): array;
}
