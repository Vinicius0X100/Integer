<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = User::query();

        // Filtro por Busca (Nome, Email)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nome', 'like', "%{$search}%")
                  ->orWhere('sobrenome', 'like', "%{$search}%")
                  ->orWhere('nome_usuario', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filtro por Status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filtro por Papel
        if ($request->filled('role')) {
            $query->where('papel', $request->role);
        }

        // Ordenação
        $sort = $request->get('sort', 'criado_em');
        $order = $request->get('order', 'desc');
        
        // Proteção contra colunas inválidas
        if (!in_array($sort, ['nome', 'email', 'papel', 'status', 'criado_em', 'ultimo_login_em'])) {
            $sort = 'criado_em';
        }
        
        $users = $query->orderBy($sort, $order)->paginate(10)->withQueryString();

        if ($request->ajax()) {
            return view('users.partials.table', compact('users'))->render();
        }
        
        $roles = User::select('papel')->distinct()->pluck('papel')->filter()->values();

        return view('users.index', compact('users', 'roles'));
    }

    public function bulkAction(Request $request)
    {
        if (!$request->user()->isAdmin()) {
            return back()->with('error', 'Apenas administradores podem realizar ações em massa.');
        }

        $request->validate([
            'action' => 'required|in:delete,activate,deactivate',
            'selected' => 'required|array',
            'selected.*' => 'exists:usuarios,id',
            'password' => 'required|string',
        ]);

        if (!Hash::check($request->password, auth()->user()->senha)) {
            throw ValidationException::withMessages([
                'password' => 'Senha de administrador incorreta.',
            ]);
        }

        $action = $request->action;
        $ids = $request->selected;
        $count = count($ids);

        switch ($action) {
            case 'delete':
                // Check if trying to delete self
                if (in_array(auth()->id(), $ids)) {
                     return back()->withErrors(['selected' => 'Você não pode excluir sua própria conta.']);
                }
                User::whereIn('id', $ids)->delete();
                $message = "$count usuários excluídos com sucesso.";
                break;
            case 'activate':
                User::whereIn('id', $ids)->update(['status' => 1]);
                $message = "$count usuários ativados com sucesso.";
                break;
            case 'deactivate':
                if (in_array(auth()->id(), $ids)) {
                     return back()->withErrors(['selected' => 'Você não pode inativar sua própria conta.']);
                }
                User::whereIn('id', $ids)->update(['status' => 0]);
                $message = "$count usuários inativados com sucesso.";
                break;
            default:
                return back()->with('error', 'Ação inválida.');
        }

        return redirect()->route('users.index')->with('status', $message);
    }

    public function generatePdf(Request $request)
    {
        $query = User::query();

        if ($request->has('selected')) {
            $query->whereIn('id', $request->selected);
        } else {
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('nome', 'like', "%{$search}%")
                      ->orWhere('sobrenome', 'like', "%{$search}%")
                      ->orWhere('nome_usuario', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('role')) {
                $query->where('papel', $request->role);
            }
        }

        $users = $query->orderBy('nome')->get();
        $columns = $request->input('columns', []);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('users.pdf', compact('users', 'columns'));
        return $pdf->stream('usuarios.pdf');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('users.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'sobrenome' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:usuarios',
            'senha' => 'required|string|min:8|confirmed',
            'papel' => 'required|string',
            'status' => 'required|boolean',
            'nome_usuario' => 'nullable|string|max:255|unique:usuarios',
            'data_nascimento' => 'nullable|date',
            'telefone' => 'nullable|string|max:20',
            'cep' => 'nullable|string|max:20',
            'endereco' => 'nullable|string|max:255',
            'numero' => 'nullable|string|max:20',
            'bairro' => 'nullable|string|max:255',
            'cidade' => 'nullable|string|max:255',
            'estado' => 'nullable|string|max:255',
            'pais' => 'nullable|string|max:255',
        ]);

        $validated['senha'] = Hash::make($validated['senha']);
        
        // Campos de controle
        $validated['criado_por'] = auth()->id();
        $validated['atualizado_por'] = auth()->id();
        
        // Define nome de exibição padrão se não informado
        if (empty($validated['nome_exibicao'])) {
            $validated['nome_exibicao'] = $validated['nome'] . ' ' . $validated['sobrenome'];
        }

        User::create($validated);

        return redirect()->route('users.index')->with('status', 'Usuário criado com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = User::findOrFail($id);
        
        // Se for uma requisição AJAX, retorna JSON para o modal
        if (request()->wantsJson()) {
            return response()->json($user);
        }

        return view('users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $user = User::findOrFail($id);
        return view('users.edit', compact('user'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'sobrenome' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:usuarios,email,' . $id,
            'senha' => 'nullable|string|min:8|confirmed',
            'papel' => 'required|string',
            'status' => 'required|boolean',
            'nome_usuario' => 'nullable|string|max:255|unique:usuarios,nome_usuario,' . $id,
            'data_nascimento' => 'nullable|date',
            'telefone' => 'nullable|string|max:20',
            'cep' => 'nullable|string|max:20',
            'endereco' => 'nullable|string|max:255',
            'numero' => 'nullable|string|max:20',
            'bairro' => 'nullable|string|max:255',
            'cidade' => 'nullable|string|max:255',
            'estado' => 'nullable|string|max:255',
            'pais' => 'nullable|string|max:255',
        ]);

        if (filled($request->senha)) {
            $validated['senha'] = Hash::make($request->senha);
        } else {
            unset($validated['senha']);
        }

        $validated['atualizado_por'] = auth()->id();
        
        // Atualiza nome de exibição se necessário (opcional, pode manter o antigo ou atualizar)
        // Aqui optamos por não forçar atualização do nome de exibição na edição para preservar customização

        $user->update($validated);

        return redirect()->route('users.index')->with('status', 'Usuário atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        $request->validate([
            'admin_password' => 'required|string',
        ]);

        // Verifica a senha do administrador logado
        if (!Hash::check($request->admin_password, $request->user()->senha)) {
            throw ValidationException::withMessages([
                'admin_password' => 'A senha de administrador está incorreta.',
            ]);
        }

        $user = User::findOrFail($id);
        
        // Impede que o usuário se auto-delete (opcional, mas recomendado)
        if ($user->id === $request->user()->id) {
            return back()->withErrors(['admin_password' => 'Você não pode excluir sua própria conta.']);
        }

        $user->delete();

        return redirect()->route('users.index')->with('status', 'Usuário excluído com sucesso.');
    }
}
