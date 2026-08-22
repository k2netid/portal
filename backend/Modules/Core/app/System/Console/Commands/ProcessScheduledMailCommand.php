<?php

declare(strict_types=1);

namespace Modules\Core\System\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Modules\Core\System\Models\MailMessage;

class ProcessScheduledMailCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mail:process-scheduled';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process and dispatch scheduled emails that have reached their target dispatch time';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Checking for scheduled emails to dispatch...');

        $now = Carbon::now();

        // Retrieve messages with folder 'scheduled' or having 'scheduled' in labels
        $scheduledMessages = MailMessage::where(function ($query) {
            $query->where('folder', 'scheduled')
                ->orWhereJsonContains('labels', 'scheduled');
        })->get();

        $dispatchedCount = 0;

        foreach ($scheduledMessages as $message) {
            $labels = is_array($message->labels) ? $message->labels : [];
            $labels = array_values(array_diff($labels, ['scheduled']));

            $cleanSubject = preg_replace('/^\[Scheduled\]\s*/i', '', (string) $message->subject);

            $message->update([
                'folder' => 'sent',
                'labels' => $labels,
                'subject' => $cleanSubject,
                'sent_at' => $now,
            ]);

            $dispatchedCount++;
            $this->line("Dispatched email [{$message->id}] to ".implode(', ', $message->recipients ?? []));
        }

        $this->info("Completed scheduled mail processing. Dispatched: {$dispatchedCount} email(s).");

        return self::SUCCESS;
    }
}
