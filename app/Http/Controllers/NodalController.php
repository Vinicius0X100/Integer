<?php

namespace App\Http\Controllers;

use App\Models\NodalOrganization;
use App\Services\NodalProvisioningService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
            'nome'           => 'required|string|max:255',
            'slug'           => 'nullable|string|max:100|regex:/^[a-z0-9-]+$/',
            'cnpj'           => 'nullable|string|max:20',
            'address'        => 'nullable|string|max:255',
            'industry'       => 'nullable|string|max:100',
            'owner_name'     => 'required|string|max:255',
            'owner_email'    => 'required|email|max:255',
            'owner_password' => 'required|string|min:8',
        ], [
            'nome.required'           => 'O nome da organização é obrigatório.',
            'owner_name.required'     => 'O nome do responsável é obrigatório.',
            'owner_email.required'    => 'O e-mail do responsável é obrigatório.',
            'owner_email.email'       => 'O e-mail informado não é válido.',
            'owner_password.required' => 'A senha inicial é obrigatória.',
            'owner_password.min'      => 'A senha deve ter no mínimo 8 caracteres.',
            'slug.regex'              => 'O slug deve conter apenas letras minúsculas, números e hífens.',
        ]);

        try {
            $result = $this->nodalService->provisionCompany(
                [
                    'name'     => $validated['nome'],
                    'slug'     => $validated['slug'] ?? null,
                    'cnpj'     => $validated['cnpj'] ?? null,
                    'address'  => $validated['address'] ?? null,
                    'industry' => $validated['industry'] ?? null,
                ],
                [
                    'name'     => $validated['owner_name'],
                    'email'    => $validated['owner_email'],
                    'password' => $validated['owner_password'],
                ]
            );

            NodalOrganization::create([
                'nome'                  => $validated['nome'],
                'slug'                  => $validated['slug'] ?? null,
                'cnpj'                  => $validated['cnpj'] ?? null,
                'address'               => $validated['address'] ?? null,
                'industry'              => $validated['industry'] ?? null,
                'nodal_organization_uuid' => $result['organization_uuid'] ?? null,
                'nodal_user_uuid'         => $result['user_uuid'] ?? null,
                'owner_name'            => $validated['owner_name'],
                'owner_email'           => $validated['owner_email'],
                'nodal_login_url'       => $result['login_url'] ?? null,
                'status'                => 'active',
                'provisionado_em'       => now(),
            ]);

            return redirect()->route('nodal.index')
                ->with('success', "Organização \"{$validated['nome']}\" provisionada com sucesso no Nodal! UUID: {$result['organization_uuid']}.");

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
                        'owner.email'       => 'owner_email',
                        'owner.name'        => 'owner_name',
                        'owner.password'    => 'owner_password',
                        'organization.name' => 'nome',
                        'organization.slug' => 'slug',
                        default             => $field,
                    };
                    $mappedErrors[$localField] = $messages;
                }

                return back()
                    ->withInput()
                    ->withErrors($mappedErrors)
                    ->with('error', $body['message'] ?? 'Os dados informados são inválidos.');
            }

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
     * Exibe o formulário de edição de uma organização provisionada.
     */
    public function edit($id)
    {
        $organization = NodalOrganization::findOrFail($id);
        return view('nodal.edit', compact('organization'));
    }

    /**
     * Atualiza uma organização no Nodal e localmente.
     */
    public function update(Request $request, $id)
    {
        $organization = NodalOrganization::findOrFail($id);

        if (!$organization->nodal_organization_uuid) {
            return back()->with('error', 'Esta organização não possui vínculo (ID) com o Nodal para ser atualizada.');
        }

        $validated = $request->validate([
            'nome'           => 'required|string|max:255',
            'cnpj'           => 'nullable|string|max:20',
            'address'        => 'nullable|string|max:255',
            'industry'       => 'nullable|string|max:100',
            'owner_name'     => 'required|string|max:255',
            'owner_email'    => 'required|email|max:255',
            'owner_password' => 'nullable|string|min:8', // Opcional na edição
        ]);

        try {
            $orgData = [
                'name'     => $validated['nome'],
                'cnpj'     => $validated['cnpj'] ?? null,
                'address'  => $validated['address'] ?? null,
                'industry' => $validated['industry'] ?? null,
            ];

            $ownerData = [
                'name'     => $validated['owner_name'],
                'email'    => $validated['owner_email'],
                'password' => $validated['owner_password'] ?? null,
            ];

            $this->nodalService->updateCompany($organization->nodal_organization_uuid, $orgData, $ownerData);

            $organization->update([
                'nome'        => $validated['nome'],
                'cnpj'        => $validated['cnpj'] ?? null,
                'address'     => $validated['address'] ?? null,
                'industry'    => $validated['industry'] ?? null,
                'owner_name'  => $validated['owner_name'],
                'owner_email' => $validated['owner_email'],
            ]);

            return redirect()->route('nodal.index')
                ->with('success', 'Organização atualizada com sucesso no Nodal!');

        } catch (\Illuminate\Http\Client\RequestException $e) {
            $status = $e->response->status();
            $body   = $e->response->json();

            if ($status === 422) {
                return back()
                    ->withInput()
                    ->with('error', $body['message'] ?? 'Os dados informados são inválidos (Verifique se o e-mail não está em uso por outro usuário no Nodal).');
            }

            return back()
                ->withInput()
                ->with('error', 'Erro da API do Nodal ao atualizar: ' . ($body['message'] ?? 'Erro desconhecido.'));
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Não foi possível conectar ao Nodal para atualizar.');
        }
    }

    /**
     * Exclui uma organização no Nodal e localmente.
     */
    public function destroy($id)
    {
        $organization = NodalOrganization::findOrFail($id);

        try {
            if ($organization->nodal_organization_uuid) {
                $this->nodalService->deleteCompany($organization->nodal_organization_uuid);
            }

            // Exclusão local (Hard Delete)
            $organization->delete();

            return redirect()->route('nodal.index')
                ->with('success', 'Organização excluída permanentemente no Nodal e no sistema local.');

        } catch (\Illuminate\Http\Client\RequestException $e) {
            return back()->with('error', 'Erro da API do Nodal ao excluir. Verifique a conectividade.');
        } catch (\Exception $e) {
            return back()->with('error', 'Não foi possível conectar ao Nodal para excluir.');
        }
    }

    /**
     * Exibe a tela de configurações do Nodal.
     */
    public function settings()
    {
        $currentKey    = config('services.nodal.api_key', '');
        $currentUrl    = config('services.nodal.base_url', 'http://nodal.test');
        $keyConfigured = !empty($currentKey);

        // Diagnóstico de permissão do .env
        $envPath       = base_path('.env');
        $envWritable   = is_writable($envPath);
        $envExists     = file_exists($envPath);
        $phpUser       = function_exists('posix_getpwuid') && function_exists('posix_geteuid')
                         ? (posix_getpwuid(posix_geteuid())['name'] ?? exec('whoami'))
                         : exec('whoami');

        return view('nodal.settings', compact(
            'keyConfigured', 'currentUrl', 'currentKey',
            'envPath', 'envWritable', 'envExists', 'phpUser'
        ));
    }

    /**
     * Salva as configurações do Nodal no arquivo .env.
     */
    public function saveSettings(Request $request)
    {
        $keyAlreadyConfigured = !empty(config('services.nodal.api_key', ''));

        $request->validate([
            // Se já tem chave configurada, pode deixar em branco para manter
            'nodal_api_key'  => $keyAlreadyConfigured ? 'nullable|string|min:10' : 'required|string|min:10',
            'nodal_base_url' => 'required|url',
        ], [
            'nodal_api_key.required'  => 'A API Key é obrigatória.',
            'nodal_api_key.min'       => 'A API Key deve ter no mínimo 10 caracteres.',
            'nodal_base_url.required' => 'A URL base do Nodal é obrigatória.',
            'nodal_base_url.url'      => 'A URL informada não é válida.',
        ]);

        $envPath = base_path('.env');

        // Verificar permissões antes de tentar escrever
        if (!file_exists($envPath) || !is_writable($envPath)) {
            return back()
                ->withInput()
                ->with('error', 'Não foi possível escrever no arquivo .env. Verifique as permissões do arquivo em: ' . $envPath);
        }

        $envContent = file_get_contents($envPath);

        if ($envContent === false) {
            return back()
                ->withInput()
                ->with('error', 'Não foi possível ler o arquivo .env.');
        }

        // Só atualiza a API Key se o usuário preencheu o campo
        if (!empty($request->nodal_api_key)) {
            $envContent = $this->updateEnvValue($envContent, 'NODAL_SYSTEM_API_KEY', $request->nodal_api_key);
        }

        // Sempre atualiza a URL
        $envContent = $this->updateEnvValue($envContent, 'NODAL_BASE_URL', $request->nodal_base_url);

        // Escrever com LOCK_EX para evitar conflitos no Windows
        $written = file_put_contents($envPath, $envContent, LOCK_EX);

        if ($written === false) {
            return back()
                ->withInput()
                ->with('error', 'Falha ao gravar no arquivo .env. O arquivo pode estar bloqueado por outro processo. Tente fechar o VS Code ou editor de texto antes de salvar.');
        }

        // Limpar cache de configuração para o Laravel recarregar imediatamente
        \Artisan::call('config:clear');

        return redirect()->route('nodal.settings')
            ->with('success', 'Configurações do Nodal salvas com sucesso!');
    }

    /**
     * Atualiza ou insere uma variável de ambiente no conteúdo do .env.
     */
    private function updateEnvValue(string $envContent, string $key, string $value): string
    {
        // Valores com espaços precisam de aspas
        $escapedValue = str_contains($value, ' ') ? "\"{$value}\"" : $value;

        if (preg_match("/^{$key}=/m", $envContent)) {
            // Chave existe: substituir a linha inteira
            return preg_replace("/^{$key}=.*/m", "{$key}={$escapedValue}", $envContent);
        }

        // Chave não existe: adicionar ao final
        return rtrim($envContent) . "\n{$key}={$escapedValue}\n";
    }
}
