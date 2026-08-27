<?php

declare(strict_types=1);

namespace Modules\Mail\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Modules\Core\System\Console\Concerns\SkipsWhenProductInactive;
use Modules\Mail\Models\MailMessage;

class ProcessSnoozedMailCommand extends Command
{
    use SkipsWhenProductInactive;
    protected $signature = 'mail:process-snoozed';

    protected $description = 'Wake snoozed messages whose snooze_until time has passed';

    public function handle(): int
    {
        if ($this->skipUnlessProductActive('mail')) {
            return self::SUCCESS;
        }

        $now = now();
        $due = MailMessage::query()
            ->whereNotNull('snoozed_until')
            ->where('snoozed_until', '<=', $now)
            ->get();

        $woken = 0;

        foreach ($due as $message) {
            $labels = is_array($message->labels) ? $message->labels : [];
            $labels = array_values(array_diff($labels, ['snoozed']));

            $message->update([
                'snoozed_until' => null,
                'labels' => $labels,
            ]);
            $woken++;
        }

        $this->info("Woke {$woken} snoozed message(s).");

        if ($woken > 0) {
            Log::info('Mail snooze wake completed', ['woken' => $woken]);
        }

        return self::SUCCESS;
    }
}
