<?php

declare(strict_types=1);

namespace Modules\Layout\SampleData;

final class ThemeSampleDataInstallOptions
{
    public function __construct(
        public readonly bool $force = false,
        public readonly bool $menus = true,
        public readonly bool $settings = true,
        public readonly bool $pages = true,
        public readonly bool $forms = true,
    ) {
    }

    /**
     * @param  array<string, mixed>  $input
     */
    public static function fromArray(array $input): self
    {
        return new self(
            force: (bool) ($input['force'] ?? false),
            menus: (bool) ($input['menus'] ?? true),
            settings: (bool) ($input['settings'] ?? true),
            pages: (bool) ($input['pages'] ?? true),
            forms: (bool) ($input['forms'] ?? true),
        );
    }
}
