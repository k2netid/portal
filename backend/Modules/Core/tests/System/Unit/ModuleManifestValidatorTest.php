<?php

declare(strict_types=1);

namespace Modules\Core\System\Tests\Unit;

use Modules\Core\System\Support\ModuleManifestValidator;
use Tests\TestCase;

class ModuleManifestValidatorTest extends TestCase
{
    public function test_mail_manifest_passes_first_party_contract(): void
    {
        $path = base_path('Modules/Mail/manifest.json');
        $this->assertFileExists($path);

        /** @var array<mixed, mixed> $manifest */
        $manifest = json_decode((string) file_get_contents($path), true);
        $this->assertIsArray($manifest);

        $errors = ModuleManifestValidator::validateFirstParty($manifest);
        $this->assertSame([], $errors, implode('; ', $errors));
    }

    public function test_missing_required_fields_fail(): void
    {
        $errors = ModuleManifestValidator::validateFirstParty([
            'name' => 'X',
        ]);

        $this->assertNotEmpty($errors);
        $this->assertFalse(ModuleManifestValidator::isValidFirstParty(['name' => 'X']));
    }

    public function test_non_semver_version_fails(): void
    {
        $errors = ModuleManifestValidator::validateFirstParty([
            'name' => 'Demo',
            'slug' => 'demo',
            'version' => 'V.2.0',
            'type' => 'module',
            'author' => 'Jejakawan',
            'description' => 'x',
            'is_core' => false,
        ]);

        $this->assertNotEmpty($errors);
        $this->assertTrue(
            collect($errors)->contains(fn (string $e): bool => str_contains($e, 'SemVer')),
            implode('; ', $errors)
        );
    }
}
