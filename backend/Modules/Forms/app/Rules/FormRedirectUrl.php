<?php

declare(strict_types=1);

namespace Modules\Forms\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Allows only safe post-submit redirects: relative paths or http(s) URLs.
 * Blocks javascript:, data:, and protocol-relative tricks.
 */
class FormRedirectUrl implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null || $value === '') {
            return;
        }

        if (! is_string($value)) {
            $fail('The :attribute must be a string.');

            return;
        }

        $trimmed = trim($value);
        if ($trimmed === '') {
            return;
        }

        if (preg_match('#^\s*(javascript|data|vbscript)\s*:#i', $trimmed) === 1) {
            $fail('The :attribute uses a disallowed URL scheme.');

            return;
        }

        if (str_starts_with($trimmed, '/')) {
            if (str_starts_with($trimmed, '//')) {
                $fail('The :attribute may not use a protocol-relative path.');

                return;
            }

            if (str_contains($trimmed, "\0") || str_contains($trimmed, "\r") || str_contains($trimmed, "\n")) {
                $fail('The :attribute contains invalid characters.');

                return;
            }

            return;
        }

        if (filter_var($trimmed, FILTER_VALIDATE_URL) === false) {
            $fail('The :attribute must be a valid URL or a path starting with /.');

            return;
        }

        $scheme = parse_url($trimmed, PHP_URL_SCHEME);
        if (! is_string($scheme) || ! in_array(strtolower($scheme), ['http', 'https'], true)) {
            $fail('The :attribute must use http or https when absolute.');

            return;
        }
    }
}
