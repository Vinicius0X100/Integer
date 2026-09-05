<?php

namespace App\Console\Commands;

use App\Services\NodalBillingSyncService;
use Illuminate\Console\Command;

class SyncNodalBillingCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'integer:sync-nodal-billing
                            {--period= : Competência no formato YYYY-MM}
                            {--invoice= : UUID de uma fatura específica}
                            {--full : Executa sincronização completa ignorando o tempo do último sync}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sincroniza as faturas financeiras do Nodal Billing para o Integer.';

    /**
     * Execute the console command.
     */
    public function handle(NodalBillingSyncService $syncService): int
    {
        $this->info('Iniciando sincronização com Nodal Billing...');

        $options = [
            'period'  => $this->option('period'),
            'invoice' => $this->option('invoice'),
            'full'    => (bool) $this->option('full'),
        ];

        try {
            $result = $syncService->sync($options);

            if (($result['status'] ?? '') === 'already_running') {
                $this->warn('Sincronização cancelada: já existe uma sincronização em andamento.');
                return Command::SUCCESS;
            }

            $this->info(sprintf(
                'Sincronização concluída! Processadas: %d | Criadas: %d | Atualizadas: %d',
                $result['synced'] ?? 0,
                $result['created'] ?? 0,
                $result['updated'] ?? 0
            ));

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Erro durante a sincronização: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
