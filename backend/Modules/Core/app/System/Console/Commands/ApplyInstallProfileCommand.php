<?php

declare(strict_types=1);

namespace Modules\Core\System\Console\Commands;

use Illuminate\Console\Command;
use Modules\Core\System\Services\InstallProfileApplicator;

class ApplyInstallProfileCommand extends Command
{
    protected $signature = 'ja:apply-install-profile
        {profile? : core|cms|cms_site (default: config install.profile)}
        {--force : Re-run even when packs already active}';

    protected $description = 'Discover extensions and apply install profile (Core-only vs CMS vs public Site)';

    public function handle(InstallProfileApplicator $applicator): int
    {
        $profileArg = $this->argument('profile');
        $profile = is_string($profileArg) && $profileArg !== '' ? $profileArg : null;

        $this->info('Applying install profile…');
        $result = $applicator->apply($profile);

        $this->line('Profile: '.$result['profile']);
        $this->line('Discovered: '.$result['discovered']);
        $this->line('Activated: '.implode(', ', $result['activated'] ?: ['(none)']));
        if ($result['skipped'] !== []) {
            $this->line('Already active: '.implode(', ', $result['skipped']));
        }
        if ($result['errors'] !== []) {
            foreach ($result['errors'] as $error) {
                $this->error($error);
            }
        }
        $themeActive = $result['themes']['active'] ?? null;
        $this->line('Themes scanned: '.$result['themes']['scanned'].'; active: '.($themeActive ?? '—'));

        $apex = $result['profile'] === InstallProfileApplicator::PROFILE_CMS_SITE
            ? 'apex `/` → public theme (Site on; overrides landing)'
            : 'apex `/` → kernel landing (Site off; console at /auth/console-sign-in)';
        $this->info($apex);

        return $result['errors'] === [] ? self::SUCCESS : self::FAILURE;
    }
}
