<?php

declare(strict_types=1);

namespace Modules\Core\System\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class SystemAudit extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'system:audit {--generate : Menghasilkan manifes baseline tanda tangan berkas saat ini}';

    /**
     * The console command description.
     */
    protected $description = 'Melakukan audit integritas sistem (file_hash_verification) pada berkas core Tier A';

    /**
     * Target Core Kernel (Tier A) directories to audit.
     *
     * @var array<string>
     */
    protected array $coreDirectories = [
        'Modules/Core/System/app',
        'Modules/Core/System/config',
        'Modules/Core/System/routes',
    ];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $baselinePath = storage_path('app/system_baseline.json');

        if ($this->option('generate')) {
            $this->info('Memulai pembuatan baseline tanda tangan sistem...');
            $filesMap = [];

            foreach ($this->coreDirectories as $dir) {
                $absoluteDir = base_path($dir);
                if (! is_dir($absoluteDir)) {
                    continue;
                }

                $files = File::allFiles($absoluteDir);
                foreach ($files as $file) {
                    $relativePath = str_replace(base_path().DIRECTORY_SEPARATOR, '', $file->getRealPath());
                    $filesMap[$relativePath] = hash_file('sha256', $file->getRealPath());
                }
            }

            File::ensureDirectoryExists(dirname($baselinePath));
            File::put($baselinePath, (string) json_encode($filesMap, JSON_PRETTY_PRINT));
            $this->info("Baseline berhasil dibuat di: {$baselinePath} (".count($filesMap).' berkas dicatat)');

            return 0;
        }

        if (! file_exists($baselinePath)) {
            $this->error('File baseline system_baseline.json tidak ditemukan.');
            $this->warn('Harap buat baseline terlebih dahulu dengan menjalankan: php artisan system:audit --generate');

            return 1;
        }

        $this->info('🏛️  Memulai Audit Integritas Sistem (Core Tier A)...');
        $baseline = json_decode((string) file_get_contents($baselinePath), true);
        if (! is_array($baseline)) {
            $this->error('Format baseline tidak valid.');

            return 1;
        }

        $modified = [];
        $added = [];
        $deleted = [];
        $scannedCount = 0;

        // Trace baseline files (detect deletion & modifications)
        foreach ($baseline as $relativePath => $expectedHash) {
            $absolutePath = base_path((string) $relativePath);

            if (! file_exists($absolutePath)) {
                $deleted[] = $relativePath;

                continue;
            }

            $currentHash = hash_file('sha256', $absolutePath);
            if ($currentHash !== $expectedHash) {
                $modified[] = $relativePath;
            }
            $scannedCount++;
        }

        // Trace current folders to detect unexpected files (added / injected)
        foreach ($this->coreDirectories as $dir) {
            $absoluteDir = base_path($dir);
            if (! is_dir($absoluteDir)) {
                continue;
            }

            $files = File::allFiles($absoluteDir);
            foreach ($files as $file) {
                $relativePath = str_replace(base_path().DIRECTORY_SEPARATOR, '', $file->getRealPath());
                if (! isset($baseline[$relativePath])) {
                    $added[] = $relativePath;
                }
            }
        }

        $this->newLine();
        $this->line("=== HASIL AUDIT INTEGRITAS (Total Scanned: {$scannedCount} files) ===");

        if (empty($modified) && empty($added) && empty($deleted)) {
            $this->info('✓ [INTEGRITY OK] Seluruh berkas core kernel Tier A cocok dengan tanda tangan digital resmi.');

            return 0;
        }

        if (! empty($modified)) {
            $this->error('🚨 [PERINGATAN] Berkas core berikut telah MODIFIKASI tanpa izin (Kritis!):');
            foreach ($modified as $file) {
                $this->line("  - {$file}");
            }
        }

        if (! empty($added)) {
            $this->warn('🚨 [PERINGATAN] Berkas core berikut adalah BERKAS BARU / INJEKSI (Kemungkinan malware!):');
            foreach ($added as $file) {
                $this->line("  - {$file}");
            }
        }

        if (! empty($deleted)) {
            $this->error('🚨 [PERINGATAN] Berkas core berikut telah DIAPUS dari sistem:');
            foreach ($deleted as $file) {
                $this->line("  - {$file}");
            }
        }

        return 1;
    }
}
