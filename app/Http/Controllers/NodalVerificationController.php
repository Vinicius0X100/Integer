<?php

namespace App\Http\Controllers;

use App\Models\NodalVerification;
use App\Services\NodalProvisioningService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NodalVerificationController extends Controller
{
    public function __construct(
        protected NodalProvisioningService $nodalService
    ) {}

    /**
     * Lista as verificações pendentes.
     */
    public function index()
    {
        // Lista apenas as pendentes, ou talvez todas com filtro. 
        // Vamos listar todas, ordenadas por mais recentes.
        $verifications = NodalVerification::latest()->paginate(15);

        return view('nodal_verifications.index', compact('verifications'));
    }

    /**
     * Exibe os detalhes de uma verificação e o documento para análise.
     */
    public function show($id)
    {
        $verification = NodalVerification::findOrFail($id);

        if ($verification->status === 'pending' && empty($verification->document_url)) {
            try {
                $details = $this->nodalService->getVerificationDetails($verification->uuid);
                
                if (!empty($details['document_url'])) {
                    $verification->update([
                        'document_url' => $details['document_url'],
                        // Se houver mais detalhes, podemos atualizar aqui.
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Nodal KYC: Falha ao buscar detalhes da verificação.', [
                    'uuid' => $verification->uuid,
                    'error' => $e->getMessage(),
                ]);
                
                return back()->with('error', 'Não foi possível carregar os detalhes do documento no Nodal.');
            }
        }

        return view('nodal_verifications.show', compact('verification'));
    }

    /**
     * Aprova a verificação no Nodal.
     */
    public function approve(Request $request, $id)
    {
        $verification = NodalVerification::findOrFail($id);
        
        $request->validate([
            'notes' => 'nullable|string|max:500'
        ]);

        try {
            $this->nodalService->approveVerification($verification->uuid, $request->notes);

            $verification->update(['status' => 'approved']);

            return redirect()->route('nodal-verifications.index')
                ->with('success', "Verificação da empresa {$verification->organization_name} APROVADA com sucesso no Nodal.");

        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao tentar aprovar no Nodal: ' . $e->getMessage());
        }
    }

    /**
     * Rejeita a verificação no Nodal.
     */
    public function reject(Request $request, $id)
    {
        $verification = NodalVerification::findOrFail($id);
        
        $request->validate([
            'reason' => 'required|string|max:1000'
        ], [
            'reason.required' => 'O motivo da rejeição é obrigatório para que a empresa seja notificada.'
        ]);

        try {
            $this->nodalService->rejectVerification($verification->uuid, $request->reason);

            $verification->update(['status' => 'rejected']);

            return redirect()->route('nodal-verifications.index')
                ->with('success', "Verificação da empresa {$verification->organization_name} REJEITADA com sucesso no Nodal.");

        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao tentar rejeitar no Nodal: ' . $e->getMessage());
        }
    }
}
