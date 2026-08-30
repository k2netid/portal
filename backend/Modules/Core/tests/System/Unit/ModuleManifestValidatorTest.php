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
}
