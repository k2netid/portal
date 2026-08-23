<?php

declare(strict_types=1);

namespace Modules\Layout\Console\Commands;

use Illuminate\Console\Command;

class ThemeChecksumCommand extends Command
{
    protected $signature = 'theme:checksum {file : Path to theme.esm.js or other bundle file}';

    protected $description = 'Print SHA-256 checksum for theme.json bundle_checksum field';

    public function handle(): int
    {
        $file = (string) $this->argument('file');
        if (! is_file($file)) {
            $this->error("File not found: {$file}");

            return self::FAILURE;
        }

        $hash = hash_file('sha256', $file);
        $this->info("SHA-256: {$hash}");
        $this->line('Add to theme.json: "bundle_checksum": "'.$hash.'"');

        return self::SUCCESS;
    }
}
