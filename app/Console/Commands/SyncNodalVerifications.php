<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\NodalProvisioningService;
use App\Models\NodalVerification;
use Illuminate\Support\Facades\Log;

class SyncNodalVerifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nodal:sync-verifications';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sincroniza as verificações KYC pendentes do Nodal com o banco de dados local';

    /**
     * Execute the console command.
     */
    public function handle(NodalProvisioningService $nodalService)
    {
        $this->info('Iniciando sincronização de verificações Nodal KYC...');

        try {
            $response = $nodalService->getPendingVerifications();
            $verifications = $response['data'] ?? [];

            $this->info(count($verifications) . ' verificações pendentes encontradas.');

            // Lista de UUIDs pendentes retornados pela API
            $pendingUuids = [];

            foreach ($verifications as $ver) {
                $uuid = $ver['uuid'];
                $pendingUuids[] = $uuid;

                // Cria ou atualiza a verificação local
                NodalVerification::updateOrCreate(
                    ['uuid' => $uuid],
                    [
                        'nodal_organization_uuid' => $ver['organization_uuid'],
                        'organization_name'       => $ver['organization_name'] ?? 'Desconhecida',
                        'document_type'           => $ver['document_type'] ?? 'unknown',
                        'submitted_at'            => $ver['submitted_at'] ?? now(),
                        'status'                  => 'pending',
                    ]
                );
            }

            // Opcional: Se quisermos "limpar" do banco local as pendentes que não vieram mais da API
            // (por exemplo, se foram aprovadas/rejeitadas direto lá no Nodal ou por outra via)
            if (!empty($pendingUuids)) {
                NodalVerification::where('status', 'pending')
                    ->whereNotIn('uuid', $pendingUuids)
                    ->update(['status' => 'unknown']); 
                    // ou deletar, ou checar o status real.
            } else {
                NodalVerification::where('status', 'pending')
                    ->update(['status' => 'unknown']);
            }

            $this->info('Sincronização concluída com sucesso.');
            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Erro ao sincronizar verificações: ' . $e->getMessage());
            Log::error('Nodal: Erro no comando nodal:sync-verifications.', [
                'exception' => $e->getMessage(),
            ]);
            return Command::FAILURE;
        }
    }
}
