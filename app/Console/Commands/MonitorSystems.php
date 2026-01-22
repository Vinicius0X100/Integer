<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\SystemHealthLog;
use Carbon\Carbon;

class MonitorSystems extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'monitor:systems';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pings system APIs to check health status and logs results.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $systems = [
            [
                'name' => 'SisMatriz',
                'url' => 'https://backend.sismatriz.online/api/health'
            ],
            [
                'name' => 'SisMatriz Ticket',
                'url' => 'https://ticket.sismatriz.online/api/health'
            ],
            [
                'name' => 'Sacratech Cloud',
                'url' => 'https://delivery.sacratech.com/ocs/v2.php/apps/user_status/api/v1/user_status'
            ]
        ];

        $this->info('Starting system monitoring...');

        foreach ($systems as $system) {
            $this->checkSystem($system);
        }

        $this->info('Monitoring completed.');
    }

    private function checkSystem($system)
    {
        $start = microtime(true);
        $status = false;
        $statusCode = null;
        $errorMessage = null;

        try {
            // Timeout set to 10 seconds to prevent hanging
            $response = Http::timeout(10)->get($system['url']);
            
            $end = microtime(true);
            $responseTimeMs = round(($end - $start) * 1000);
            $statusCode = $response->status();

            if ($response->successful()) {
                $status = true;
                $this->info("{$system['name']}: ONLINE ({$responseTimeMs}ms)");
            } else {
                $status = false;
                $errorMessage = "Status Code: {$statusCode}";
                $this->error("{$system['name']}: OFFLINE (Status: {$statusCode})");
            }

        } catch (\Exception $e) {
            $end = microtime(true);
            $responseTimeMs = round(($end - $start) * 1000);
            $status = false;
            $errorMessage = $e->getMessage();
            $this->error("{$system['name']}: ERROR ({$e->getMessage()})");
        }

        SystemHealthLog::create([
            'system_name' => $system['name'],
            'endpoint' => $system['url'],
            'status' => $status,
            'response_time_ms' => $responseTimeMs,
            'status_code' => $statusCode,
            'error_message' => $errorMessage,
        ]);
    }
}
