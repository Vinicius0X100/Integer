<?php

namespace App\Http\Controllers;

use App\Services\NodalProvisioningService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NodalVerificationController extends Controller
{
    public function __construct(
        protected NodalProvisioningService $nodalService
    ) {}

    /**
     * Lista as verificações pendentes consumindo direto da API do Nodal em tempo real.
     */
    public function index()
    {
        try {
            $response = $this->nodalService->getPendingVerifications();
            $verifications = $response['data'] ?? [];
        } catch (\Exception $e) {
            $verifications = [];
            Log::error('Nodal KYC: Falha ao buscar verificações pendentes.', ['error' => $e->getMessage()]);
            session()->flash('error', 'Não foi possível conectar ao Nodal para buscar os documentos.');
        }

        return view('nodal_verifications.index', compact('verifications'));
    }

    /**
     * Exibe os detalhes de uma verificação direto da API.
     */
    public function show($uuid)
    {
        try {
            $verification = $this->nodalService->getVerificationDetails($uuid);
            return view('nodal_verifications.show', compact('verification'));
        } catch (\Exception $e) {
            Log::error('Nodal KYC: Falha ao buscar detalhes da verificação.', [
                'uuid' => $uuid,
                'error' => $e->getMessage(),
            ]);
            
            return redirect()->route('nodal-verifications.index')->with('error', 'Não foi possível carregar os detalhes do documento no Nodal.');
        }
    }

    /**
     * Aprova a verificação no Nodal.
     */
    public function approve(Request $request, $uuid)
    {
        $request->validate([
            'notes' => 'nullable|string|max:500'
        ]);

        try {
            $this->nodalService->approveVerification($uuid, $request->notes);
            return redirect()->route('nodal-verifications.index')
                ->with('success', "A verificação foi APROVADA com sucesso no Nodal.");
        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao tentar aprovar no Nodal: ' . $e->getMessage());
        }
    }

    /**
     * Rejeita a verificação no Nodal.
     */
    public function reject(Request $request, $uuid)
    {
        $request->validate([
            'reason' => 'required|string|max:1000'
        ], [
            'reason.required' => 'O motivo da rejeição é obrigatório para que a empresa seja notificada.'
        ]);

        try {
            $this->nodalService->rejectVerification($uuid, $request->reason);
            return redirect()->route('nodal-verifications.index')
                ->with('success', "A verificação foi REJEITADA com sucesso no Nodal.");
        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao tentar rejeitar no Nodal: ' . $e->getMessage());
        }
    }
}
