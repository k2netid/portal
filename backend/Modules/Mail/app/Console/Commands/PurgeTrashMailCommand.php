<?php

declare(strict_types=1);

namespace Modules\Mail\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Modules\Core\System\Console\Concerns\SkipsWhenProductInactive;
use Modules\Core\System\Models\Setting;
use Modules\Mail\Models\MailMessage;
use Modules\Mail\Services\MailAttachmentStore;

class PurgeTrashMailCommand extends Command
{
    use SkipsWhenProductInactive;

    protected $signature = 'mail:purge-trash
                            {--days= : Override retention days from settings}
                            {--dry-run : Show what would be deleted without deleting}';

    protected $description = 'Permanently delete trashed mail older than the configured retention period';

    public function __construct(
        protected MailAttachmentStore $attachments,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        if ($this->skipUnlessProductActive('mail')) {
            return self::SUCCESS;
        }

        $retentionRaw = $this->option('days') ?? Setting::get('mail_client_trash_retention_days', 30);
        $retentionDays = is_numeric($retentionRaw) ? (int) $retentionRaw : 30;

        if ($retentionDays <= 0) {
            $this->info('Trash retention is set to keep forever (0). No purge performed.');

            return self::SUCCESS;
        }

        $cutoff = now()->subDays($retentionDays);
        $dryRun = (bool) $this->option('dry-run');

        $this->info("Purging trash older than {$retentionDays} days (before {$cutoff->toDateTimeString()})");

        $query = MailMessage::query()
            ->where('folder', 'trash')
            ->where(function ($q) use ($cutoff): void {
                $q->where(function ($inner) use ($cutoff): void {
                    $inner->whereNotNull('trashed_at')
                        ->where('trashed_at', '<', $cutoff);
                })->orWhere(function ($inner) use ($cutoff): void {
                    $inner->whereNull('trashed_at')
                        ->where('updated_at', '<', $cutoff);
                });
            });

        $candidates = $query->get();
        $count = $candidates->count();

        if ($dryRun) {
            $this->warn("DRY RUN — {$count} message(s) would be permanently deleted");

            return self::SUCCESS;
        }

        foreach ($candidates as $message) {
            $raw = is_array($message->attachments) ? $message->attachments : [];
            $this->attachments->deleteStored($raw);
            $message->delete();
        }

        $this->info("Purged {$count} trashed message(s).");

        if ($count > 0) {
            Log::info('Mail trash purge completed', [
                'deleted' => $count,
                'retention_days' => $retentionDays,
                'cutoff' => $cutoff->toIso8601String(),
            ]);
        }

        return self::SUCCESS;
    }
}
