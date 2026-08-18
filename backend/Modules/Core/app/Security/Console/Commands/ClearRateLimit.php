<?php

namespace Modules\Core\Security\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Modules\Core\System\Http\Controllers\Console\SystemController;

class ClearRateLimit extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'security:clear-rate-limit {--ip= : Specific IP to clear} {--email= : Specific email to clear}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear rate limit and login attempts';

    /**
     * Execute the console command.
     */
    public function handle(SystemController $systemController): int
    {
        $this->info('Clearing rate limits...');

        $ip = $this->option('ip');
        $email = $this->option('email');

        $request = new Request;

        if ($ip) {
            $request->merge(['ip' => (string) $ip]);
        }

        if ($email) {
            $request->merge(['email' => (string) $email]);
        }

        // Use the existing logic from SystemController
        $response = $systemController->clearRateLimit($request);

        if ($response->getStatusCode() === 200) {
            $data = $response->getData(true);
            $cleared = [];
            if (is_array($data) && isset($data['data']) && is_array($data['data']) && isset($data['data']['cleared'])) {
                $cleared = is_array($data['data']['cleared']) ? $data['data']['cleared'] : [];
            }
            foreach ($cleared as $msg) {
                $this->line('- '.(is_scalar($msg) ? (string) $msg : json_encode($msg)));
            }
            $this->info('Rate limits cleared successfully.');

            return 0;
        } else {
            $this->error('Failed to clear rate limits.');

            return 1;
        }
    }
}
