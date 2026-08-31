<?php

declare(strict_types=1);

namespace Modules\Layout\SampleData;

final class ThemeSampleDataInstallResult
{
    /**
     * @param  list<string>  $messages
     * @param  list<string>  $warnings
     */
    public function __construct(
        public readonly string $themeSlug,
        public readonly int $menusInstalled = 0,
        public readonly int $pagesInstalled = 0,
        public readonly int $postsInstalled = 0,
        public readonly int $settingsApplied = 0,
        public readonly array $messages = [],
        public readonly array $warnings = [],
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'theme_slug' => $this->themeSlug,
            'menus_installed' => $this->menusInstalled,
            'pages_installed' => $this->pagesInstalled,
            'posts_installed' => $this->postsInstalled,
            'settings_applied' => $this->settingsApplied,
            'messages' => $this->messages,
            'warnings' => $this->warnings,
        ];
    }
}
