<?php

namespace App\Http\Controllers;

use App\Models\CampanhaEmail;
use App\Models\SisMatrizMainUser;
use App\Models\SisMatrizUser;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CampanhaEmailController extends Controller
{
    // -------------------------------------------------------------------------
    // Listagem
    // -------------------------------------------------------------------------

    public function index()
    {
        $campanhas = CampanhaEmail::orderBy('criado_em', 'desc')->paginate(15);

        return view('campanhas_email.index', compact('campanhas'));
    }

    // -------------------------------------------------------------------------
    // Criação
    // -------------------------------------------------------------------------

    public function create()
    {
        return view('campanhas_email.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'titulo'             => 'required|string|max:255',
            'corpo_html'         => 'required|string',
            'produto'            => 'required|in:all,sacratech_id,sismatriz_ticket,sismatriz_main,airlink',
            'destinatarios_tipo' => 'required|in:todos,selecionados',
            'destinatarios_ids'  => 'nullable|array',
            'destinatarios_ids.*'=> 'nullable|string',
            'acao'               => 'required|in:rascunho,enviar',
        ]);

        // Airlink ainda não tem integração
        if ($validated['produto'] === 'airlink') {
            return back()
                ->withInput()
                ->with('error', 'Airlink Locate ainda não possui integração disponível. Em breve!');
        }

        $campanha = CampanhaEmail::create([
            'titulo'             => $validated['titulo'],
            'corpo_html'         => $validated['corpo_html'],
            'produto'            => $validated['produto'],
            'destinatarios_tipo' => $validated['destinatarios_tipo'],
            'destinatarios_ids'  => $validated['destinatarios_ids'] ?? null,
            'status'             => 'rascunho',
            'total_destinatarios'=> 0,
            'criado_por'         => auth()->id(),
        ]);

        if ($validated['acao'] === 'enviar') {
            return $this->dispararEnvio($campanha);
        }

        return redirect()
            ->route('campanhas_email.show', $campanha)
            ->with('success', 'Campanha salva como rascunho com sucesso!');
    }

    // -------------------------------------------------------------------------
    // Detalhes
    // -------------------------------------------------------------------------

    public function show(CampanhaEmail $campanhasEmail)
    {
        // Busca o criador explicitamente na conexão correta (sacratech_contas)
        // para evitar cross-connection leaking da conexão 'integer'.
        $criador = null;
        if ($campanhasEmail->criado_por) {
            $criador = User::on('mysql')
                ->select('id', 'nome', 'sobrenome', 'email')
                ->find($campanhasEmail->criado_por);
        }

        return view('campanhas_email.show', [
            'campanha' => $campanhasEmail,
            'criador'  => $criador,
        ]);
    }

    // -------------------------------------------------------------------------
    // Exclusão
    // -------------------------------------------------------------------------

    public function destroy(CampanhaEmail $campanhasEmail)
    {
        if ($campanhasEmail->status === 'enviando') {
            return back()->with('error', 'Não é possível excluir uma campanha em andamento.');
        }

        $campanhasEmail->delete();

        return redirect()
            ->route('campanhas_email.index')
            ->with('success', 'Campanha excluída com sucesso.');
    }

    // -------------------------------------------------------------------------
    // Reenvio
    // -------------------------------------------------------------------------

    public function reenviar(CampanhaEmail $campanhasEmail)
    {
        if ($campanhasEmail->status === 'enviando') {
            return back()->with('error', 'Esta campanha já está sendo enviada.');
        }

        if ($campanhasEmail->produto === 'airlink') {
            return back()->with('error', 'Airlink Locate ainda não possui integração disponível. Em breve!');
        }

        return $this->dispararEnvio($campanhasEmail);
    }

    // -------------------------------------------------------------------------
    // AJAX: listar usuários por produto
    // -------------------------------------------------------------------------

    public function getUsuarios(Request $request)
    {
        $produto = $request->get('produto', 'all');
        $destinatarios = $this->resolverDestinatarios($produto, 'todos', null);

        return response()->json($destinatarios);
    }

    // -------------------------------------------------------------------------
    // Lógica interna: disparar webhook ao n8n
    // -------------------------------------------------------------------------

    private function dispararEnvio(CampanhaEmail $campanha)
    {
        $webhookUrl = (string) env('N8N_CAMPANHA_EMAIL_WEBHOOK_URL', '');

        if ($webhookUrl === '') {
            return redirect()
                ->route('campanhas_email.show', $campanha)
                ->with('error', 'Webhook n8n não configurado. Defina N8N_CAMPANHA_EMAIL_WEBHOOK_URL no .env.');
        }

        // Resolver destinatários
        $destinatarios = $this->resolverDestinatarios(
            $campanha->produto,
            $campanha->destinatarios_tipo,
            $campanha->destinatarios_ids
        );

        if (empty($destinatarios)) {
            return redirect()
                ->route('campanhas_email.show', $campanha)
                ->with('error', 'Nenhum destinatário encontrado para os critérios selecionados.');
        }

        // Montar payload
        $payload = [
            'source'          => 'integer',
            'event'           => 'email_marketing.campanha.enviar',
            'campanha_id'     => $campanha->id,
            'titulo'          => $campanha->titulo,
            'corpo_html'      => $campanha->corpo_html,
            'produto'         => $campanha->produto,
            'generated_at'    => now()->utc()->toIso8601String(),
            'destinatarios'   => $destinatarios,
        ];

        // Marcar como "enviando"
        $campanha->update([
            'status'             => 'enviando',
            'total_destinatarios'=> count($destinatarios),
            'enviado_em'         => now(),
            'webhook_response'   => null,
        ]);

        // Enviar webhook
        try {
            $secret  = (string) env('N8N_CAMPANHA_EMAIL_WEBHOOK_SECRET', '');
            $body    = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $signature = $secret !== '' ? hash_hmac('sha256', $body, $secret) : null;

            $httpRequest = Http::timeout(30)
                ->acceptJson()
                ->withBody($body, 'application/json');

            if ($signature) {
                $httpRequest = $httpRequest->withHeaders([
                    'X-Integer-Signature' => $signature,
                ]);
            }

            $response = $httpRequest->post($webhookUrl);

            if ($response->successful()) {
                $campanha->update([
                    'status'           => 'enviado',
                    'webhook_response' => $response->body(),
                ]);

                return redirect()
                    ->route('campanhas_email.show', $campanha)
                    ->with('success', 'Campanha enviada ao n8n com sucesso! ' . count($destinatarios) . ' destinatários.');
            }

            $campanha->update([
                'status'           => 'erro',
                'webhook_response' => 'HTTP ' . $response->status() . ': ' . $response->body(),
            ]);

            return redirect()
                ->route('campanhas_email.show', $campanha)
                ->with('error', 'Falha ao enviar ao n8n. Status HTTP: ' . $response->status());

        } catch (\Throwable $e) {
            Log::error('CampanhaEmail webhook error', [
                'campanha_id' => $campanha->id,
                'error'       => $e->getMessage(),
            ]);

            $campanha->update([
                'status'           => 'erro',
                'webhook_response' => $e->getMessage(),
            ]);

            return redirect()
                ->route('campanhas_email.show', $campanha)
                ->with('error', 'Erro ao conectar ao n8n: ' . $e->getMessage());
        }
    }

    // -------------------------------------------------------------------------
    // Lógica interna: resolver destinatários
    // -------------------------------------------------------------------------

    private function resolverDestinatarios(string $produto, string $tipo, ?array $idsSelecionados): array
    {
        $listas = [];

        if (in_array($produto, ['all', 'sacratech_id'])) {
            // Forçar conexão 'mysql' (sacratech_contas) para evitar connection leaking
            // da conexão 'integer' que pode estar ativa no contexto da request.
            $query = User::on('mysql')
                ->select('id', 'nome', 'sobrenome', 'email')
                ->whereNotNull('email')
                ->where('email', '!=', '');

            if ($tipo === 'selecionados' && $produto === 'sacratech_id' && !empty($idsSelecionados)) {
                $query->whereIn('id', array_map(fn($i) => (int) str_replace('sacratech_id_', '', $i), $idsSelecionados));
            }

            $query->get()->each(function ($u) use (&$listas) {
                $listas[] = [
                    'id'      => $u->id,
                    'produto' => 'sacratech_id',
                    'nome'    => trim(($u->nome ?? '') . ' ' . ($u->sobrenome ?? '')),
                    'email'   => strtolower(trim($u->email)),
                ];
            });
        }

        if (in_array($produto, ['all', 'sismatriz_ticket'])) {
            $query = SisMatrizUser::select('id', 'name', 'email')
                ->whereNotNull('email')
                ->where('email', '!=', '');

            if ($tipo === 'selecionados' && $produto === 'sismatriz_ticket' && !empty($idsSelecionados)) {
                $query->whereIn('id', array_map(fn($i) => ltrim($i, 'sismatriz_ticket_'), $idsSelecionados));
            }

            $query->get()->each(function ($u) use (&$listas) {
                $listas[] = [
                    'id'      => $u->id,
                    'produto' => 'sismatriz_ticket',
                    'nome'    => trim($u->name ?? ''),
                    'email'   => strtolower(trim($u->email)),
                ];
            });
        }

        if (in_array($produto, ['all', 'sismatriz_main'])) {
            $query = SisMatrizMainUser::select('id', 'name', 'email')
                ->whereNotNull('email')
                ->where('email', '!=', '');

            if ($tipo === 'selecionados' && $produto === 'sismatriz_main' && !empty($idsSelecionados)) {
                $query->whereIn('id', array_map(fn($i) => ltrim($i, 'sismatriz_main_'), $idsSelecionados));
            }

            $query->get()->each(function ($u) use (&$listas) {
                $listas[] = [
                    'id'      => $u->id,
                    'produto' => 'sismatriz_main',
                    'nome'    => trim($u->name ?? ''),
                    'email'   => strtolower(trim($u->email)),
                ];
            });
        }

        // Airlink: placeholder, sem dados ainda
        // if (in_array($produto, ['all', 'airlink'])) { ... }

        // Deduplicar por email (mantém primeira ocorrência)
        $seen   = [];
        $result = [];
        foreach ($listas as $item) {
            $email = $item['email'];
            if ($email === '' || isset($seen[$email])) {
                continue;
            }
            $seen[$email] = true;
            $result[]     = $item;
        }

        return $result;
    }
}
