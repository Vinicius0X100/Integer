<?php

namespace App\Http\Controllers;

use App\Models\SisMatrizMainUser;
use App\Models\SisMatrizParoquia;
use App\Models\SisMatrizUserAccess;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SisMatrizMainUserController extends Controller
{
    const ROLES = [
        111 => 'Administrador do Sistema',
        1 => 'Administrador Geral',
        2 => 'Gestor de Ministério',
        3 => 'Coordenador - Crisma',
        4 => 'Membro - Vicentinos',
        5 => 'Coordenador - Música Litúrgica',
        6 => 'Coordenador - Acólitos',
        7 => 'Coordenador - 1ª Eucaristia',
        8 => 'Acólito',
        9 => 'Coordenador - PASCOM',
        10 => 'Membro - PASCOM',
        11 => 'Tesoureiro',
        12 => 'Catequista - 1ª Eucaristia',
        13 => 'Catequista - Crisma',
        14 => 'Dizimista',
        15 => 'Gerencia de Estoque/Inventário e Salas e Espaços',
        16 => 'Coordenador - Matrimônio (Visualizar e Imprimir fichas apenas)',
        17 => 'Coordenador - Catequese de Adultos',
        18 => 'Catequista - Catequese de Adultos',
    ];

    public function index(Request $request)
    {
        $lastLoginSubquery = SisMatrizUserAccess::query()
            ->selectRaw("MAX(CONCAT(access_date, ' ', access_time))")
            ->whereColumn('user_id', 'users.id');

        $query = SisMatrizMainUser::query()
            ->with('paroquia')
            ->select('users.*')
            ->selectSub($lastLoginSubquery, 'last_login_at');

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
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
            $query->whereRaw('FIND_IN_SET(?, rule)', [$roleId]);
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
        $threshold = Carbon::now()->subDays(90);

        $users->getCollection()->transform(function ($user) use ($threshold) {
            $lastLoginAt = $user->last_login_at ? Carbon::parse($user->last_login_at) : null;
            $user->formatted_last_login = $lastLoginAt ? $lastLoginAt->format('d/m/Y H:i') : 'Nunca acessou';
            $user->inactive_days = $lastLoginAt ? (int) $lastLoginAt->diffInDays(Carbon::now()) : null;
            $user->inactive_alert = $user->is_pass_change == 1 && $lastLoginAt && $lastLoginAt->lte($threshold);

            return $user;
        });
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
        foreach ($userRoleIds as $rid) {
            if (isset(self::ROLES[$rid])) {
                $userRoleNames[] = self::ROLES[$rid];
            }
        }

        $user->role_names = $userRoleNames;

        $lastLoginAt = SisMatrizUserAccess::query()
            ->where('user_id', $user->id)
            ->orderBy('access_date', 'desc')
            ->orderBy('access_time', 'desc')
            ->first();

        $lastLoginDateTime = null;
        if ($lastLoginAt) {
            $accessDate = $lastLoginAt->access_date instanceof \Carbon\CarbonInterface
                ? $lastLoginAt->access_date->format('Y-m-d')
                : substr((string) $lastLoginAt->access_date, 0, 10);
            $lastLoginDateTime = Carbon::parse(trim($accessDate.' '.(string) $lastLoginAt->access_time));
        }

        $threshold = Carbon::now()->subDays(90);
        $user->formatted_last_login = $lastLoginDateTime ? $lastLoginDateTime->format('d/m/Y H:i') : 'Nunca acessou';
        $user->inactive_days = $lastLoginDateTime ? (int) $lastLoginDateTime->diffInDays(Carbon::now()) : null;
        $user->inactive_alert = $user->is_pass_change == 1 && $lastLoginDateTime && $lastLoginDateTime->lte($threshold);

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

        if (! Hash::check($request->password, $request->user()->senha)) {
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
        if (! $request->user()->isAdmin()) {
            return back()->with('error', 'Apenas administradores podem realizar ações em massa.');
        }

        $request->validate([
            'password' => 'required|string',
        ]);

        if (! Hash::check($request->password, $request->user()->senha)) {
            return back()->with('error', 'Senha de administrador incorreta.');
        }

        switch ($action) {
            case 'delete':
                SisMatrizMainUser::whereIn('id', $selected)->delete();

                return back()->with('success', count($selected).' usuários excluídos com sucesso!');
        }

        return back()->with('error', 'Ação inválida.');
    }

    public function export(Request $request)
    {
        $query = SisMatrizMainUser::query()->with('paroquia');

        // Apply filters
        if ($request->has('selected') && is_array($request->selected)) {
            $query->whereIn('id', $request->selected);
        } else {
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('user', 'like', "%{$search}%");
                });
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('is_pass_change')) {
                $query->where('is_pass_change', $request->is_pass_change);
            }

            if ($request->filled('paroquia_id')) {
                $query->where('paroquia_id', $request->paroquia_id);
            }

            if ($request->filled('role')) {
                $roleId = $request->role;
                $query->whereRaw('FIND_IN_SET(?, rule)', [$roleId]);
            }
        }

        // Check if it's a verification request (AJAX/JSON)
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['count' => $query->count()]);
        }

        $users = $query->latest()->get();

        if ($users->isEmpty()) {
            // Se for CSV ou PDF, redireciona de volta com erro
            return redirect()->back()->with('error', 'Nenhum usuário encontrado para exportação com os filtros selecionados.');
        }

        $rolesMap = self::ROLES;

        // Default columns if not provided
        $columns = $request->input('columns', ['name', 'user', 'email', 'paroquia', 'roles', 'status']);
        $format = $request->input('format', 'pdf');

        if ($format === 'csv') {
            $headers = [
                'Content-type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename=Relacao_de_Usuarios_SisMatriz_Principal_'.date('Y-m-d_H-i').'.csv',
                'Pragma' => 'no-cache',
                'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
                'Expires' => '0',
            ];

            $callback = function () use ($users, $columns, $rolesMap) {
                $file = fopen('php://output', 'w');

                // Add BOM for Excel
                fwrite($file, chr(0xEF).chr(0xBB).chr(0xBF));

                // Headers
                $csvHeaders = [];
                if (in_array('id', $columns)) {
                    $csvHeaders[] = 'ID';
                }
                if (in_array('name', $columns)) {
                    $csvHeaders[] = 'Nome';
                }
                if (in_array('user', $columns)) {
                    $csvHeaders[] = 'Login';
                }
                if (in_array('email', $columns)) {
                    $csvHeaders[] = 'Email';
                }
                if (in_array('paroquia', $columns)) {
                    $csvHeaders[] = 'Paróquia';
                }
                if (in_array('roles', $columns)) {
                    $csvHeaders[] = 'Cargos';
                }
                if (in_array('status', $columns)) {
                    $csvHeaders[] = 'Status';
                }
                if (in_array('is_pass_change', $columns)) {
                    $csvHeaders[] = 'Status da Senha';
                }
                if (in_array('created_at', $columns)) {
                    $csvHeaders[] = 'Data de Cadastro';
                }

                fputcsv($file, $csvHeaders, ';');

                foreach ($users as $user) {
                    $row = [];
                    if (in_array('id', $columns)) {
                        $row[] = $user->id;
                    }
                    if (in_array('name', $columns)) {
                        $row[] = $user->name;
                    }
                    if (in_array('user', $columns)) {
                        $row[] = $user->user;
                    }
                    if (in_array('email', $columns)) {
                        $row[] = $user->email;
                    }
                    if (in_array('paroquia', $columns)) {
                        $row[] = $user->paroquia ? $user->paroquia->name : '-';
                    }

                    if (in_array('roles', $columns)) {
                        $userRoleIds = $user->rule ? explode(',', $user->rule) : [];
                        $userRoleNames = [];
                        foreach ($userRoleIds as $rid) {
                            if (isset($rolesMap[$rid])) {
                                $userRoleNames[] = $rolesMap[$rid];
                            }
                        }
                        $row[] = implode(', ', $userRoleNames);
                    }

                    if (in_array('status', $columns)) {
                        $row[] = $user->status == 0 ? 'Ativo' : 'Inativo';
                    }
                    if (in_array('is_pass_change', $columns)) {
                        $row[] = $user->is_pass_change == 1 ? 'Alterada' : 'Padrão';
                    }
                    if (in_array('created_at', $columns)) {
                        $row[] = $user->created_at ? $user->created_at->format('d/m/Y H:i') : '-';
                    }

                    fputcsv($file, $row, ';');
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        }

        $pdf = Pdf::loadView('sismatriz_main.pdf', compact('users', 'rolesMap', 'columns'));

        return $pdf->download('Relacao_de_Usuarios_SisMatriz_Principal_'.date('Y-m-d_H-i').'.pdf');
    }
}
