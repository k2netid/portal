<?php

namespace Modules\Content\Layout\Support;

class MenuItemUrlValidator
{
    private const BLOCKED_SCHEMES = [
        'javascript',
        'data',
        'vbscript',
    ];

    public static function isAllowed(?string $url): bool
    {
        if ($url === null || $url === '') {
            return true;
        }

        $trimmed = trim($url);

        if (str_starts_with($trimmed, '//')) {
            return false;
        }

        if (! str_contains($trimmed, ':')) {
            return ! str_starts_with($trimmed, '#');
        }

        $scheme = strtolower((string) parse_url($trimmed, PHP_URL_SCHEME));

        if ($scheme === '') {
            return true;
        }

        return ! in_array($scheme, self::BLOCKED_SCHEMES, true);
    }

    /**
     * @return array<int, string|\Closure(string, mixed, \Closure): void>
     */
    public static function validationRules(): array
    {
        return [
            'nullable',
            'string',
            'max:2048',
            function (string $attribute, mixed $value, \Closure $fail): void {
                if (! is_string($value) || self::isAllowed($value)) {
                    return;
                }

                $fail('The '.$attribute.' uses a disallowed URL scheme.');
            },
        ];
    }
}
