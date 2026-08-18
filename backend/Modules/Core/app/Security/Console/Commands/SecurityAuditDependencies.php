<?php

declare(strict_types=1);

namespace Modules\Core\Security\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;
use Modules\Core\Security\Models\DependencyVulnerability;

class SecurityAuditDependencies extends Command
{
    protected $signature = 'security:audit-dependencies';

    protected $description = 'Scan composer and npm dependencies for known vulnerabilities';

    /** @var list<string> */
    private array $npmPackagesFound = [];

    /** @var list<string> */
    private array $composerPackagesFound = [];

    public function handle(): int
    {
        $this->info('Running dependency security audit...');

        $this->info('Checking Composer dependencies...');
        $composerResult = Process::path(base_path())->run('composer audit --format=json --no-interaction 2>/dev/null');

        if ($composerResult->successful() && $composerResult->output() !== '') {
            $this->parseComposerAudit($composerResult->output());
        } else {
            $this->warn('Composer audit skipped or returned no data.');
        }

        $npmPath = $this->resolveNpmProjectPath();
        if ($npmPath !== null) {
            $this->info('Checking NPM dependencies at '.$npmPath.'...');
            $npmResult = Process::path($npmPath)->run('npm audit --json 2>/dev/null');

            if ($npmResult->output() !== '') {
                $this->parseNpmAudit($npmResult->output());
            } else {
                $this->warn('NPM audit returned no data.');
            }
        }

        $this->pruneResolvedVulnerabilities();

        $newCount = DependencyVulnerability::where('status', 'new')->count();

        if ($newCount > 0) {
            $this->warn("Found {$newCount} new vulnerabilities!");
        } else {
            $this->info('No new vulnerabilities found.');
        }

        return Command::SUCCESS;
    }

    protected function resolveNpmProjectPath(): ?string
    {
        foreach ([base_path('../frontend'), base_path('..'), base_path()] as $path) {
            if (is_file($path.'/package.json')) {
                return $path;
            }
        }

        return null;
    }

    protected function parseComposerAudit(string $json): void
    {
        try {
            $data = json_decode($json, true);
            if (! is_array($data)) {
                return;
            }

            $advisories = $data['advisories'] ?? [];
            if (! is_array($advisories)) {
                return;
            }

            foreach ($advisories as $alerts) {
                if (! is_array($alerts)) {
                    continue;
                }
                foreach ($alerts as $advisory) {
                    if (! is_array($advisory)) {
                        continue;
                    }

                    $packageName = isset($advisory['packageName']) && is_string($advisory['packageName']) ? $advisory['packageName'] : 'unknown';
                    $this->composerPackagesFound[] = $packageName;
                    $affectedVersions = isset($advisory['affectedVersions']) && is_string($advisory['affectedVersions']) ? $advisory['affectedVersions'] : 'unknown';
                    $cve = isset($advisory['cve']) && is_string($advisory['cve']) ? $advisory['cve'] : null;
                    $severityRaw = $advisory['severity'] ?? 'medium';
                    $severity = strtolower(is_string($severityRaw) ? $severityRaw : 'medium');
                    $title = isset($advisory['title']) && is_string($advisory['title']) ? $advisory['title'] : '';
                    $fixedIn = null;
                    if (isset($advisory['sources']) && is_array($advisory['sources']) && isset($advisory['sources'][0]) && is_array($advisory['sources'][0])) {
                        $remediated = $advisory['sources'][0]['remediatedVersions'] ?? null;
                        $fixedIn = is_string($remediated) ? $remediated : null;
                    }

                    DependencyVulnerability::updateOrCreate(
                        [
                            'package_name' => $packageName,
                            'version' => $affectedVersions,
                            'cve' => $cve,
                        ],
                        [
                            'severity' => $severity,
                            'fixed_in' => $fixedIn,
                            'source' => 'composer',
                            'description' => $title,
                            'status' => 'new',
                        ]
                    );
                }
            }
        } catch (\Exception $e) {
            $this->error('Failed to parse composer audit: '.$e->getMessage());
        }
    }

    protected function parseNpmAudit(string $json): void
    {
        try {
            $data = json_decode($json, true);
            if (! is_array($data)) {
                return;
            }

            $vulnerabilities = $data['vulnerabilities'] ?? [];
            if (! is_array($vulnerabilities)) {
                return;
            }

            foreach ($vulnerabilities as $name => $vuln) {
                if (! is_array($vuln)) {
                    continue;
                }

                $version = isset($vuln['range']) && is_string($vuln['range']) ? $vuln['range'] : 'unknown';
                $severityRaw = $vuln['severity'] ?? 'medium';
                $severity = strtolower(is_string($severityRaw) ? $severityRaw : 'medium');
                $fixAvailable = (bool) ($vuln['fixAvailable'] ?? false);
                $cve = null;
                $description = '';
                if (isset($vuln['via']) && is_array($vuln['via']) && isset($vuln['via'][0]) && is_array($vuln['via'][0])) {
                    $via = $vuln['via'][0];
                    $cve = isset($via['cve']) && is_string($via['cve']) ? $via['cve'] : null;
                    $description = isset($via['title']) && is_string($via['title']) ? $via['title'] : '';
                }

                $pkgName = is_string($name) ? $name : 'unknown';
                $this->npmPackagesFound[] = $pkgName;

                DependencyVulnerability::updateOrCreate(
                    [
                        'package_name' => $pkgName,
                        'version' => $version,
                        'cve' => $cve,
                    ],
                    [
                        'severity' => $severity,
                        'fixed_in' => $fixAvailable ? 'available' : null,
                        'source' => 'npm',
                        'description' => $description,
                        'status' => 'new',
                    ]
                );
            }
        } catch (\Exception $e) {
            $this->error('Failed to parse npm audit: '.$e->getMessage());
        }
    }

    private function pruneResolvedVulnerabilities(): void
    {
        $composerFound = array_values(array_unique($this->composerPackagesFound));
        $npmFound = array_values(array_unique($this->npmPackagesFound));

        if ($composerFound !== []) {
            DependencyVulnerability::query()
                ->where('source', 'composer')
                ->whereNotIn('package_name', $composerFound)
                ->whereIn('status', ['new', 'acknowledged'])
                ->update(['status' => 'patched']);
        } else {
            DependencyVulnerability::query()
                ->where('source', 'composer')
                ->whereIn('status', ['new', 'acknowledged'])
                ->update(['status' => 'patched']);
        }

        if ($npmFound !== []) {
            DependencyVulnerability::query()
                ->where('source', 'npm')
                ->whereNotIn('package_name', $npmFound)
                ->whereIn('status', ['new', 'acknowledged'])
                ->update(['status' => 'patched']);
        } else {
            DependencyVulnerability::query()
                ->where('source', 'npm')
                ->whereIn('status', ['new', 'acknowledged'])
                ->update(['status' => 'patched']);
        }
    }
}
