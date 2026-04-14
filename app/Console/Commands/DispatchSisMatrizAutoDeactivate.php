<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class DispatchSisMatrizAutoDeactivate extends Command
{
    protected $signature = 'automation:sismatriz-auto-deactivate-dispatch {--limit=500} {--dry-run}';

    protected $description = 'Envia para o n8n a lista de usuários SisMatriz elegíveis para inativação automática.';

    public function handle(): int
    {
        $settings = DB::connection('integer')
            ->table('settings')
            ->whereIn('key', [
                'automation.sismatriz_auto_deactivate.enabled',
                'automation.sismatriz_auto_deactivate.max_inactive_days',
            ])
            ->pluck('value', 'key');

        $enabled = (bool) (int) ($settings['automation.sismatriz_auto_deactivate.enabled'] ?? 0);
        if (! $enabled) {
            $this->info('Automação desabilitada (automation.sismatriz_auto_deactivate.enabled = 0).');

            return self::SUCCESS;
        }

        $maxInactiveDays = (int) ($settings['automation.sismatriz_auto_deactivate.max_inactive_days'] ?? 0);
        if ($maxInactiveDays <= 0) {
            $this->error('Configuração inválida: automation.sismatriz_auto_deactivate.max_inactive_days.');

            return self::FAILURE;
        }

        $webhookUrl = (string) env('N8N_SISMATRIZ_AUTO_DEACTIVATE_WEBHOOK_URL', '');
        if ($webhookUrl === '') {
            $this->error('N8N_SISMATRIZ_AUTO_DEACTIVATE_WEBHOOK_URL não configurado no .env.');

            return self::FAILURE;
        }

        $threshold = now()->subDays($maxInactiveDays)->format('Y-m-d H:i:s');
        $limit = (int) $this->option('limit');
        if ($limit <= 0) {
            $limit = 500;
        }

        $lastLoginSub = DB::connection('sismatriz_main')
            ->table('user_access')
            ->selectRaw("user_id, MAX(CONCAT(access_date, ' ', access_time)) as last_login_at")
            ->groupBy('user_id');

        $candidates = DB::connection('sismatriz_main')
            ->table('users')
            ->joinSub($lastLoginSub, 'ua', function ($join) {
                $join->on('ua.user_id', '=', 'users.id');
            })
            ->where('users.status', 0)
            ->where('users.is_pass_change', 1)
            ->whereNotNull('ua.last_login_at')
            ->where('ua.last_login_at', '<=', $threshold)
            ->orderBy('ua.last_login_at', 'asc')
            ->limit($limit)
            ->get([
                'users.id as user_id',
                'users.name',
                'users.email',
                'users.paroquia_id',
                'users.status',
                'users.is_pass_change',
                'ua.last_login_at',
            ])
            ->map(function ($row) {
                $lastLoginAt = (string) $row->last_login_at;
                $inactiveDays = (int) now()->diffInDays(\Carbon\Carbon::parse($lastLoginAt));

                return [
                    'user_id' => (int) $row->user_id,
                    'name' => (string) $row->name,
                    'email' => (string) $row->email,
                    'paroquia_id' => (int) $row->paroquia_id,
                    'status' => (int) $row->status,
                    'is_pass_change' => (int) $row->is_pass_change,
                    'last_login_at' => $lastLoginAt,
                    'inactive_days' => $inactiveDays,
                ];
            })
            ->values()
            ->all();

        $payload = [
            'source' => 'integer',
            'event' => 'sismatriz.users.auto_deactivate.candidates',
            'generated_at' => now()->utc()->toIso8601String(),
            'max_inactive_days' => $maxInactiveDays,
            'candidates' => $candidates,
        ];

        $dryRun = (bool) $this->option('dry-run');
        $this->info('Candidatos: '.count($candidates));
        $this->info('Webhook: '.$webhookUrl);
        $this->info('Threshold: '.$threshold);
        $this->info('Dry-run: '.($dryRun ? 'sim' : 'não'));

        if ($dryRun) {
            return self::SUCCESS;
        }

        $secret = (string) env('N8N_SISMATRIZ_AUTO_DEACTIVATE_WEBHOOK_SECRET', '');
        $body = json_encode($payload, JSON_UNESCAPED_UNICODE);
        if ($body === false) {
            $this->error('Falha ao serializar payload JSON.');

            return self::FAILURE;
        }

        $signature = $secret !== '' ? hash_hmac('sha256', $body, $secret) : null;

        $request = Http::timeout(20)
            ->acceptJson()
            ->withBody($body, 'application/json');

        if ($signature) {
            $request = $request->withHeaders([
                'X-Integer-Signature' => $signature,
            ]);
        }

        $response = $request->post($webhookUrl);

        if (! $response->successful()) {
            $this->error('Falha ao enviar webhook. Status: '.$response->status());
            $this->line((string) $response->body());

            return self::FAILURE;
        }

        $this->info('Webhook enviado com sucesso. Status: '.$response->status());

        return self::SUCCESS;
    }
}
