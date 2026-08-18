<?php

declare(strict_types=1);

namespace Modules\Core\System\Console\Commands;

use Illuminate\Console\Command;
use Modules\Core\System\Models\ContentType;
use Modules\Core\System\Support\DynamicOpenApiBuilder;

class DynamicOpenApiExport extends Command
{
    protected $signature = 'dynamic:openapi
                            {slug? : CCK slug (omit to export all active types)}
                            {--output= : Directory to write JSON files (default: docs/api in project root)}';

    protected $description = 'Export OpenAPI 3 JSON for dynamic CCK slug API(s)';

    public function handle(DynamicOpenApiBuilder $builder): int
    {
        $slug = $this->argument('slug');
        $outputDir = $this->option('output')
            ?? base_path('../docs/api');

        if (! is_dir($outputDir) && ! mkdir($outputDir, 0755, true) && ! is_dir($outputDir)) {
            $this->error("Cannot create output directory: {$outputDir}");

            return self::FAILURE;
        }

        $types = $slug !== null && $slug !== ''
            ? ContentType::query()->where('slug', $slug)->where('is_active', true)->get()
            : ContentType::query()->where('is_active', true)->orderBy('slug')->get();

        if ($types->isEmpty()) {
            $this->error($slug ? "No active content type for slug: {$slug}" : 'No active content types');

            return self::FAILURE;
        }

        foreach ($types as $type) {
            $document = $builder->buildFor($type);
            $filename = $outputDir.'/dynamic-'.$type->slug.'.openapi.json';
            $json = json_encode($document, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
            file_put_contents($filename, $json."\n");
            $this->info("Wrote {$filename}");
        }

        return self::SUCCESS;
    }
}
