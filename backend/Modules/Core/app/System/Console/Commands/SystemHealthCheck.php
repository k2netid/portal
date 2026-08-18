<?php

namespace Modules\Core\System\Console\Commands;

use Illuminate\Console\Command;
use Modules\Core\System\Services\SystemService;

class SystemHealthCheck extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'system:health-check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run a system health check';

    /**
     * Execute the console command.
     */
    public function handle(SystemService $systemService): int
    {
        $this->info('Running system health check...');

        $health = $systemService->getSystemHealth();

        $rows = [];
        foreach ($health as $component => $data) {
            $componentStr = (string) $component;
            if (is_array($data) && isset($data['status'])) {
                $statusVal = is_scalar($data['status']) ? (string) $data['status'] : '';
                $status = $statusVal === 'ok' ? '<info>OK</info>' : '<error>'.strtoupper($statusVal).'</error>';
                $message = isset($data['message']) && is_scalar($data['message']) ? (string) $data['message'] : '';
                $rows[] = [ucfirst($componentStr), $status, $message];
            } else {
                $rows[] = [ucfirst($componentStr), '<info>OK</info>', is_scalar($data) ? (string) $data : json_encode($data)];
            }
        }

        $this->table(['Component', 'Status', 'Message'], $rows);

        $this->info('Health check completed.');

        return 0;
    }
}
