<?php

namespace App\Http\Controllers;

use App\Models\NodalOrganization;
use App\Services\NodalProvisioningService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class NodalController extends Controller
{
    public function __construct(
        protected NodalProvisioningService $nodalService
    ) {}

    /**
     * Lista todas as organizações provisionadas no Nodal.
     */
    public function index(Request $request)
    {
        $query = NodalOrganization::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nome', 'like', "%{$search}%")
                  ->orWhere('owner_email', 'like', "%{$search}%")
                  ->orWhere('owner_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $organizations = $query->latest()->paginate(10)->withQueryString();

        return view('nodal.index', compact('organizations'));
    }

    /**
     * Exibe o formulário de provisioning de nova organização.
     */
    public function create()
    {
        return view('nodal.create');
    }

    /**
     * Processa o provisioning de uma nova organização no Nodal.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome'          => 'required|string|max:255',
            'slug'          => 'nullable|string|max:100|regex:/^[a-z0-9-]+$/',
            'owner_name'    => 'required|string|max:255',
            'owner_email'   => 'required|email|max:255',
            'owner_password' => 'required|string|min:8',
        ], [
            'nome.required'          => 'O nome da organização é obrigatório.',
            'owner_name.required'    => 'O nome do responsável é obrigatório.',
            'owner_email.required'   => 'O e-mail do responsável é obrigatório.',
            'owner_email.email'      => 'O e-mail informado não é válido.',
            'owner_password.required' => 'A senha inicial é obrigatória.',
            'owner_password.min'     => 'A senha deve ter no mínimo 8 caracteres.',
            'slug.regex'             => 'O slug deve conter apenas letras minúsculas, números e hífens.',
        ]);

        try {
            $result = $this->nodalService->provisionCompany(
                [
                    'name' => $validated['nome'],
                    'slug' => $validated['slug'] ?? null,
                ],
                [
                    'name'     => $validated['owner_name'],
                    'email'    => $validated['owner_email'],
                    'password' => $validated['owner_password'],
                ]
            );

            NodalOrganization::create([
                'nome'                 => $validated['nome'],
                'slug'                 => $validated['slug'] ?? null,
                'nodal_organization_id' => $result['organization_id'] ?? null,
                'nodal_user_id'        => $result['user_id'] ?? null,
                'owner_name'           => $validated['owner_name'],
                'owner_email'          => $validated['owner_email'],
                'nodal_login_url'      => $result['login_url'] ?? null,
                'status'               => 'active',
                'provisionado_em'      => now(),
            ]);

            return redirect()->route('nodal.index')
                ->with('success', "Organização \"{$validated['nome']}\" provisionada com sucesso no Nodal! ID: {$result['organization_id']}.");

        } catch (\Illuminate\Http\Client\RequestException $e) {
            $status = $e->response->status();
            $body   = $e->response->json();

            if ($status === 401) {
                return back()
                    ->withInput()
                    ->with('error', 'Erro de autenticação: a NODAL_SYSTEM_API_KEY configurada é inválida. Verifique as Configurações do Nodal.');
            }

            if ($status === 422) {
                $errors = $body['errors'] ?? [];

                // Mapear campos do Nodal para campos locais do formulário
                $mappedErrors = [];
                foreach ($errors as $field => $messages) {
                    $localField = match ($field) {
                        'owner.email'    => 'owner_email',
                        'owner.name'     => 'owner_name',
                        'owner.password' => 'owner_password',
                        'organization.name' => 'nome',
                        'organization.slug' => 'slug',
                        default => $field,
                    };
                    $mappedErrors[$localField] = $messages;
                }

                return back()
                    ->withInput()
                    ->withErrors($mappedErrors)
                    ->with('error', $body['message'] ?? 'Os dados informados são inválidos.');
            }

            // Outros erros HTTP inesperados
            Log::error('Nodal: Erro inesperado ao provisionar organização.', [
                'status' => $status,
                'body'   => $body,
            ]);

            return back()
                ->withInput()
                ->with('error', 'Ocorreu um erro inesperado ao contatar o Nodal. Tente novamente.');

        } catch (\Exception $e) {
            Log::error('Nodal: Exceção ao provisionar organização.', [
                'message' => $e->getMessage(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Não foi possível conectar ao Nodal. Verifique a URL configurada e a conectividade.');
        }
    }

    /**
     * Exibe a tela de configurações do Nodal.
     */
    public function settings()
    {
        $currentKey    = env('NODAL_SYSTEM_API_KEY', '');
        $currentUrl    = env('NODAL_BASE_URL', 'http://nodal.test');
        $keyConfigured = !empty($currentKey);

        return view('nodal.settings', compact('keyConfigured', 'currentUrl', 'currentKey'));
    }

    /**
     * Salva as configurações do Nodal no arquivo .env.
     */
    public function saveSettings(Request $request)
    {
        $request->validate([
            'nodal_api_key'  => 'required|string|min:10',
            'nodal_base_url' => 'required|url',
        ], [
            'nodal_api_key.required'  => 'A API Key é obrigatória.',
            'nodal_api_key.min'       => 'A API Key deve ter no mínimo 10 caracteres.',
            'nodal_base_url.required' => 'A URL base do Nodal é obrigatória.',
            'nodal_base_url.url'      => 'A URL informada não é válida.',
        ]);

        $envPath = base_path('.env');
        $envContent = file_get_contents($envPath);

        // Atualizar ou adicionar NODAL_SYSTEM_API_KEY
        $envContent = $this->updateEnvValue($envContent, 'NODAL_SYSTEM_API_KEY', $request->nodal_api_key);

        // Atualizar ou adicionar NODAL_BASE_URL
        $envContent = $this->updateEnvValue($envContent, 'NODAL_BASE_URL', $request->nodal_base_url);

        file_put_contents($envPath, $envContent);

        return redirect()->route('nodal.settings')
            ->with('success', 'Configurações do Nodal salvas com sucesso!');
    }

    /**
     * Atualiza ou adiciona uma variável no conteúdo do .env.
     */
    private function updateEnvValue(string $envContent, string $key, string $value): string
    {
        // Escapar o valor se necessário (strings com espaços precisam de aspas)
        $escapedValue = Str::contains($value, ' ') ? "\"{$value}\"" : $value;

        if (preg_match("/^{$key}=.*/m", $envContent)) {
            // Chave existe: substituir o valor
            return preg_replace("/^{$key}=.*/m", "{$key}={$escapedValue}", $envContent);
        }

        // Chave não existe: adicionar ao final
        return $envContent . "\n{$key}={$escapedValue}\n";
    }
}
