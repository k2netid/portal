<?php

declare(strict_types=1);

namespace Modules\Mail\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Modules\Core\System\Console\Concerns\SkipsWhenProductInactive;
use Modules\Mail\Models\MailMessage;
use Modules\Mail\Services\MailDispatchService;

class ProcessScheduledMailCommand extends Command
{
    use SkipsWhenProductInactive;
    protected $signature = 'mail:process-scheduled';

    protected $description = 'Process and dispatch scheduled emails that have reached their target dispatch time';

    public function __construct(
        protected MailDispatchService $mailDispatch,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        if ($this->skipUnlessProductActive('mail')) {
            return self::SUCCESS;
        }

        $this->info('Checking for scheduled emails to dispatch...');

        $now = Carbon::now();

        $scheduledMessages = MailMessage::query()
            ->where('folder', 'scheduled')
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', $now)
            ->whereNull('sent_at')
            ->orderBy('scheduled_at')
            ->get();

        $dispatchedCount = 0;

        foreach ($scheduledMessages as $message) {
            $lock = Cache::lock('mail:dispatch:'.$message->id, 120);

            if (! $lock->get()) {
                $this->line("Skipping [{$message->id}] — dispatch lock held by another worker.");

                continue;
            }

            try {
                $message->refresh();

                if ($message->sent_at !== null || $message->folder !== 'scheduled') {
                    continue;
                }

                if ($message->dispatch_locked_at !== null && $message->dispatch_locked_at->greaterThan($now->copy()->subMinutes(10))) {
                    continue;
                }

                $message->update(['dispatch_locked_at' => $now]);

                $this->mailDispatch->dispatchScheduledMessage($message);

                $labels = is_array($message->labels) ? $message->labels : [];
                $labels = array_values(array_diff($labels, ['scheduled']));

                $cleanSubject = preg_replace('/^\[Scheduled\]\s*/i', '', (string) $message->subject);

                $message->update([
                    'folder' => 'sent',
                    'labels' => $labels,
                    'subject' => $cleanSubject,
                    'sent_at' => $now,
                    'scheduled_at' => null,
                    'dispatch_locked_at' => null,
                ]);

                $dispatchedCount++;
                $recipients = is_array($message->recipients) ? implode(', ', $message->recipients) : '';
                $this->line("Dispatched email [{$message->id}] to {$recipients}");
            } catch (\Throwable $e) {
                $message->update(['dispatch_locked_at' => null]);
                $this->error("Failed to dispatch [{$message->id}]: {$e->getMessage()}");
            } finally {
                $lock->release();
            }
        }

        $this->info("Completed scheduled mail processing. Dispatched: {$dispatchedCount} email(s).");

        return self::SUCCESS;
    }
}
