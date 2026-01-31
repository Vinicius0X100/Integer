<?php

namespace App\Http\Controllers;

use App\Models\SisMatrizMainUser;
use App\Models\SisMatrizParoquia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Barryvdh\DomPDF\Facade\Pdf;

class SisMatrizMainUserController extends Controller
{
    const ROLES = [
        111 => 'Administrador do Sistema',
        1   => 'Administrador Geral',
        2   => 'Gestor de Ministério',
        3   => 'Coordenador - Crisma',
        4   => 'Membro - Vicentinos',
        5   => 'Coordenador - Música Litúrgica',
        6   => 'Coordenador - Acólitos',
        7   => 'Coordenador - 1ª Eucaristia',
        8   => 'Acólito',
        9   => 'Coordenador - PASCOM',
        10  => 'Membro - PASCOM',
        11  => 'Tesoureiro',
        12  => 'Catequista - 1ª Eucaristia',
        13  => 'Catequista - Crisma',
        14  => 'Dizimista',
        15  => 'Gerencia de Estoque/Inventário e Salas e Espaços',
        16  => 'Coordenador - Matrimônio (Visualizar e Imprimir fichas apenas)',
        17  => 'Coordenador - Catequese de Adultos',
        18  => 'Catequista - Catequese de Adultos'
    ];

    public function index(Request $request)
    {
        $query = SisMatrizMainUser::query()->with('paroquia');

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('user', 'like', "%{$search}%");
            });
        }

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('paroquia_id')) {
            $query->where('paroquia_id', $request->paroquia_id);
        }

        if ($request->filled('role')) {
            $roleId = $request->role;
            $query->whereRaw("FIND_IN_SET(?, rule)", [$roleId]);
        }

        // Sorting
        $sortField = $request->get('sort', 'id');
        $sortOrder = $request->get('order', 'desc');
        $allowedSorts = ['id', 'name', 'email', 'user', 'created_at', 'status', 'is_pass_change'];

        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortOrder);
        } else {
            $query->orderBy('id', 'desc');
        }

        $users = $query->paginate(10)->withQueryString();
        $rolesMap = self::ROLES;
        $paroquias = SisMatrizParoquia::orderBy('name')->get();

        return view('sismatriz_main.index', compact('users', 'rolesMap', 'paroquias'));
    }

    public function show($id)
    {
        $user = SisMatrizMainUser::with('paroquia')->findOrFail($id);
        
        // Process Roles
        $userRoleIds = $user->rule ? explode(',', $user->rule) : [];
        $userRoleNames = [];
        foreach($userRoleIds as $rid) {
            if(isset(self::ROLES[$rid])) {
                $userRoleNames[] = self::ROLES[$rid];
            }
        }
        
        $user->role_names = $userRoleNames;
        
        // Format Dates
        $user->formatted_created_at = $user->created_at ? $user->created_at->format('d/m/Y H:i') : 'N/A';
        $user->formatted_last_attempt = $user->last_attempt ? $user->last_attempt->format('d/m/Y H:i') : 'Nunca acessou';
        
        // Avatar URL
        $user->avatar_url = $user->avatar && $user->avatar !== 'unknow_user.png' 
            ? "https://central.sismatriz.online/storage/uploads/avatars/{$user->avatar}" 
            : null;

        return response()->json($user);
    }

    public function create()
    {
        $paroquias = SisMatrizParoquia::orderBy('name')->get();
        $roles = self::ROLES;
        return view('sismatriz_main.create', compact('paroquias', 'roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'user' => 'required|string|max:100|unique:sismatriz_main.users',
            'email' => 'required|string|email|max:100',
            'password' => 'required|string|min:6|confirmed',
            'roles' => 'nullable|array',
            'paroquia_id' => 'required|exists:sismatriz_main.paroquias_superadmin,id',
            'status' => 'required|in:0,1',
        ]);

        $rule = $request->roles ? implode(',', $request->roles) : '';

        SisMatrizMainUser::create([
            'name' => $request->name,
            'user' => $request->user,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'rule' => $rule,
            'paroquia_id' => $request->paroquia_id,
            'status' => $request->status,
            'avatar' => 'unknow_user.png',
            'is_pass_change' => 0,
            'login_attempts' => 0,
            'accepted_photo' => 0,
        ]);

        return redirect()->route('sismatriz-main.index')->with('success', 'Usuário criado com sucesso!');
    }

    public function edit($id)
    {
        $user = SisMatrizMainUser::findOrFail($id);
        $paroquias = SisMatrizParoquia::orderBy('name')->get();
        $roles = self::ROLES;
        
        $userRoles = explode(',', $user->rule);

        return view('sismatriz_main.edit', compact('user', 'paroquias', 'roles', 'userRoles'));
    }

    public function update(Request $request, $id)
    {
        $user = SisMatrizMainUser::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:100',
            'user' => 'required|string|max:100|unique:sismatriz_main.users,user,'.$id,
            'email' => 'required|string|email|max:100',
            'roles' => 'nullable|array',
            'paroquia_id' => 'required|exists:sismatriz_main.paroquias_superadmin,id',
            'status' => 'required|in:0,1',
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        $data = [
            'name' => $request->name,
            'user' => $request->user,
            'email' => $request->email,
            'paroquia_id' => $request->paroquia_id,
            'status' => $request->status,
            'rule' => $request->roles ? implode(',', $request->roles) : '',
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('sismatriz-main.index')->with('success', 'Usuário atualizado com sucesso!');
    }

    public function destroy(Request $request, $id)
    {
        // Security Check
        $request->validate([
            'password' => 'required|string',
        ]);

        if (!Hash::check($request->password, $request->user()->senha)) {
            return back()->with('error', 'Senha de administrador incorreta.');
        }

        $user = SisMatrizMainUser::findOrFail($id);
        $user->delete();

        return redirect()->route('sismatriz-main.index')->with('success', 'Usuário excluído com sucesso!');
    }

    public function bulkAction(Request $request)
    {
        $action = $request->input('action');
        $selected = $request->input('selected', []);

        if (empty($selected)) {
            return back()->with('error', 'Nenhum usuário selecionado.');
        }

        // Security Check
        if (!$request->user()->isAdmin()) {
            return back()->with('error', 'Apenas administradores podem realizar ações em massa.');
        }

        $request->validate([
            'password' => 'required|string',
        ]);

        if (!Hash::check($request->password, $request->user()->senha)) {
            return back()->with('error', 'Senha de administrador incorreta.');
        }

        switch ($action) {
            case 'delete':
                SisMatrizMainUser::whereIn('id', $selected)->delete();
                return back()->with('success', count($selected) . ' usuários excluídos com sucesso!');
        }

        return back()->with('error', 'Ação inválida.');
    }

    public function generatePdf(Request $request)
    {
        $query = SisMatrizMainUser::query()->with('paroquia');

        if ($request->has('selected') && is_array($request->selected)) {
            $query->whereIn('id', $request->selected);
        } else {
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('user', 'like', "%{$search}%");
                });
            }
        }

        $users = $query->latest()->get();
        $rolesMap = self::ROLES;
        
        // Default columns if not provided
        $columns = $request->input('columns', ['name', 'user', 'email']);

        $pdf = Pdf::loadView('sismatriz_main.pdf', compact('users', 'rolesMap', 'columns'));
        return $pdf->download('Relacao_de_Usuarios_SisMatriz_Principal_' . date('Y-m-d_H-i') . '.pdf');
    }
}
