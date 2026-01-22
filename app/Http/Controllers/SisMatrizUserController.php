<?php

namespace App\Http\Controllers;

use App\Models\SisMatrizUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Barryvdh\DomPDF\Facade\Pdf;

class SisMatrizUserController extends Controller
{
    public function index(Request $request)
    {
        $query = SisMatrizUser::query();

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filters
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Sorting
        $sortField = $request->get('sort', 'id');
        $sortOrder = $request->get('order', 'desc');
        $allowedSorts = ['id', 'name', 'email', 'role', 'created_at'];

        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortOrder);
        } else {
            $query->orderBy('id', 'desc');
        }

        $users = $query->paginate(10)->withQueryString();
        
        // Get unique roles for filter
        $roles = SisMatrizUser::distinct()->pluck('role')->filter()->values();

        return view('sismatriz.index', compact('users', 'roles'));
    }

    public function create()
    {
        return view('sismatriz.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:sismatriz_ticket.users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|string',
            'sacratech_id' => 'nullable|integer',
        ]);

        SisMatrizUser::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'sacratech_id' => $request->sacratech_id,
        ]);

        return redirect()->route('sismatriz.index')->with('success', 'Usuário criado com sucesso!');
    }

    public function edit($id)
    {
        $user = SisMatrizUser::findOrFail($id);
        return view('sismatriz.edit', compact('user'));
    }

    public function update(Request $request, $id)
    {
        $user = SisMatrizUser::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:sismatriz_ticket.users,email,'.$user->id,
            'role' => 'required|string',
            'sacratech_id' => 'nullable|integer',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'sacratech_id' => $request->sacratech_id,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('sismatriz.index')->with('success', 'Usuário atualizado com sucesso!');
    }

    public function destroy($id)
    {
        $user = SisMatrizUser::findOrFail($id);
        $user->delete();

        return redirect()->route('sismatriz.index')->with('success', 'Usuário excluído com sucesso!');
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
                SisMatrizUser::whereIn('id', $selected)->delete();
                return back()->with('success', count($selected) . ' usuários excluídos com sucesso!');
        }

        return back()->with('error', 'Ação inválida.');
    }

    public function generatePdf(Request $request)
    {
        $query = SisMatrizUser::query();

        if ($request->has('selected') && is_array($request->selected)) {
            $query->whereIn('id', $request->selected);
        } else {
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }
            if ($request->filled('role')) {
                $query->where('role', $request->role);
            }
        }

        $users = $query->latest()->get();
        
        // Default columns if not provided
        $columns = $request->input('columns', ['name', 'email', 'role']);

        $pdf = Pdf::loadView('sismatriz.pdf', compact('users', 'columns'));
        return $pdf->download('Relacao_de_Usuarios_SisMatriz_' . date('Y-m-d_H-i') . '.pdf');
    }
}
