<?php

declare(strict_types=1);

namespace Modules\Core\Security\Rules;

use Illuminate\Contracts\Validation\Rule;
use Modules\Core\System\Models\Setting;

class StrongPassword implements Rule
{
    protected int $minLength;

    protected bool $requireUppercase;

    protected bool $requireLowercase;

    protected bool $requireNumber;

    protected bool $requireSymbol;

    /** @var array<int, string> */
    protected array $failedChecks = [];

    /**
     * Create a new rule instance.
     * Reads password requirements from settings.
     */
    public function __construct()
    {
        $minLenRaw = Setting::get('password_min_length', 8);
        $this->minLength = is_numeric($minLenRaw) ? (int) $minLenRaw : 8;

        $upperRaw = Setting::get('password_require_uppercase', true);
        $this->requireUppercase = filter_var($upperRaw, FILTER_VALIDATE_BOOLEAN);

        $lowerRaw = Setting::get('password_require_lowercase', true);
        $this->requireLowercase = filter_var($lowerRaw, FILTER_VALIDATE_BOOLEAN);

        $numRaw = Setting::get('password_require_number', true);
        $this->requireNumber = filter_var($numRaw, FILTER_VALIDATE_BOOLEAN);

        $symRaw = Setting::get('password_require_symbol', false);
        $this->requireSymbol = filter_var($symRaw, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $this->failedChecks = [];

        if (! is_string($value)) {
            return false;
        }

        if (strlen($value) < $this->minLength) {
            $this->failedChecks[] = "at least {$this->minLength} characters";
        }

        // Check uppercase requirement
        if ($this->requireUppercase && ! preg_match('/[A-Z]/', $value)) {
            $this->failedChecks[] = 'one uppercase letter';
        }

        // Check lowercase requirement
        if ($this->requireLowercase && ! preg_match('/[a-z]/', $value)) {
            $this->failedChecks[] = 'one lowercase letter';
        }

        // Check number requirement
        if ($this->requireNumber && ! preg_match('/\d/', $value)) {
            $this->failedChecks[] = 'one number';
        }

        // Check symbol requirement
        if ($this->requireSymbol && ! preg_match('/[!@#$%^&*()_+\-=\[\]{};\':\"\\\\|,.<>\/?]/', $value)) {
            $this->failedChecks[] = 'one symbol (!@#$%^&*...)';
        }

        return $this->failedChecks === [];
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        if ($this->failedChecks === []) {
            return 'The :attribute does not meet the password requirements.';
        }

        $requirements = implode(', ', $this->failedChecks);

        return "The :attribute must contain: {$requirements}.";
    }
}
