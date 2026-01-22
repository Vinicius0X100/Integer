<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ClienteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Cliente::query();

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nome', 'like', "%{$search}%")
                  ->orWhere('razao_social', 'like', "%{$search}%")
                  ->orWhere('cpf', 'like', "%{$search}%")
                  ->orWhere('cnpj', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filters
        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }
        if ($request->filled('status_financeiro')) {
            if ($request->status_financeiro === 'pago') {
                $query->where('modalidade_valor', 'pago');
            } elseif ($request->status_financeiro === 'gratuito') {
                $query->where('modalidade_valor', 'gratuito');
            }
        }

        // Sorting
        $sortField = $request->get('sort', 'criado_em');
        $sortOrder = $request->get('order', 'desc');
        $allowedSorts = ['nome', 'criado_em', 'tipo', 'valor_servico'];
        
        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortOrder);
        } else {
            $query->latest();
        }

        $clientes = $query->paginate(10)->withQueryString();

        if ($request->wantsJson()) {
            return response()->json($clientes); // For future API use or advanced AJAX
        }

        return view('clientes.index', compact('clientes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('clientes.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validation logic similar to the PHP script provided
        // We will validate basic structure first
        
        $request->validate([
            'nome' => 'required_if:tipo,PF|max:200',
            'razao_social' => 'required_if:tipo,PJ|max:200',
            'email' => 'nullable|email|max:200',
            'tipo' => 'required|in:PF,PJ',
        ]);

        $input = $request->all();

        // Cleaning data logic
        if (isset($input['cnpj'])) {
            $input['cnpj'] = preg_replace('/\D+/', '', $input['cnpj']);
        }
        
        if (isset($input['cpf'])) {
            $input['cpf'] = preg_replace('/\D+/', '', $input['cpf']);
        }

        // Valor Serviço formatting
        if (isset($input['valor_servico']) && $input['modalidade_valor'] === 'pago') {
            $vs = $input['valor_servico'];
            // If it has comma, likely Brazilian format
            if (strpos($vs, ',') !== false) {
                $vs = str_replace('.', '', $vs); // remove thousands separator
                $vs = str_replace(',', '.', $vs); // replace decimal separator
            } else {
                 // assume it might be just numbers, keep as is or clean non-numeric
                 // but if user types 1000.00 it works, if 1000 it works
                 // PHP script logic:
                 // else { $vs_in = preg_replace('/[^0-9.]/', '', $vs_in); }
            }
            $input['valor_servico'] = $vs;
        } else {
             $input['valor_servico'] = null;
        }

        // Parcelas logic
        $parcelado = isset($input['parcelado']) ? (bool)$input['parcelado'] : false;
        $parcelas = $parcelado ? (int)($input['parcelas'] ?? 0) : 0;
        
        $input['parcelado'] = $parcelado;
        $input['parcelas'] = $parcelas;

        // Valor Parcela logic
        $input['valor_parcela'] = null;
        if ($parcelado && $input['modalidade_valor'] === 'pago') {
            if (isset($input['valor_parcela']) && trim($input['valor_parcela']) !== '') {
                $vp = trim($input['valor_parcela']);
                if (strpos($vp, ',') !== false) {
                    $vp = str_replace('.', '', $vp);
                    $vp = str_replace(',', '.', $vp);
                }
                $input['valor_parcela'] = $vp;
            } elseif ($input['valor_servico'] && $parcelas > 0) {
                 $input['valor_parcela'] = number_format(((float)$input['valor_servico']) / $parcelas, 2, '.', '');
            }
        }

        // Checkbox handling for booleans
        $input['contrato_ativo'] = $request->has('contrato_ativo');
        $input['cobranca_automatica'] = $request->has('cobranca_automatica');
        
        Cliente::create($input);

        return redirect()->route('clientes.index')->with('success', 'Cliente cadastrado com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Cliente $cliente)
    {
        // Used for modal details if fetched via AJAX, or we can just pass all data to index
        // For now, let's assume we pass data in index loop, but API endpoint might be useful
        return response()->json($cliente);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Cliente $cliente)
    {
        return view('clientes.edit', compact('cliente'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Cliente $cliente)
    {
        $request->validate([
            'nome' => 'required_if:tipo,PF|max:200',
            'razao_social' => 'required_if:tipo,PJ|max:200',
            'email' => 'nullable|email|max:200',
            'tipo' => 'required|in:PF,PJ',
        ]);

        $input = $request->all();

        // Cleaning data logic
        if (isset($input['cnpj'])) {
            $input['cnpj'] = preg_replace('/\D+/', '', $input['cnpj']);
        }
        
        if (isset($input['cpf'])) {
            $input['cpf'] = preg_replace('/\D+/', '', $input['cpf']);
        }

        // Valor Serviço formatting
        if (isset($input['valor_servico']) && $input['modalidade_valor'] === 'pago') {
            $vs = $input['valor_servico'];
            if (strpos($vs, ',') !== false) {
                $vs = str_replace('.', '', $vs);
                $vs = str_replace(',', '.', $vs);
            }
            $input['valor_servico'] = $vs;
        } else {
             $input['valor_servico'] = null;
        }

        // Parcelas logic
        $parcelado = isset($input['parcelado']) ? (bool)$input['parcelado'] : false;
        $parcelas = $parcelado ? (int)($input['parcelas'] ?? 0) : 0;
        
        $input['parcelado'] = $parcelado;
        $input['parcelas'] = $parcelas;

        // Valor Parcela logic
        $input['valor_parcela'] = null;
        if ($parcelado && $input['modalidade_valor'] === 'pago') {
            if (isset($input['valor_parcela']) && trim($input['valor_parcela']) !== '') {
                $vp = trim($input['valor_parcela']);
                if (strpos($vp, ',') !== false) {
                    $vp = str_replace('.', '', $vp);
                    $vp = str_replace(',', '.', $vp);
                }
                $input['valor_parcela'] = $vp;
            } elseif ($input['valor_servico'] && $parcelas > 0) {
                 $input['valor_parcela'] = number_format(((float)$input['valor_servico']) / $parcelas, 2, '.', '');
            }
        }

        // Checkbox handling for booleans
        $input['contrato_ativo'] = $request->has('contrato_ativo');
        $input['cobranca_automatica'] = $request->has('cobranca_automatica');
        
        $cliente->update($input);

        return redirect()->route('clientes.index')->with('success', 'Cliente atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Cliente $cliente)
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        if (!Hash::check($request->password, $request->user()->senha)) {
            throw ValidationException::withMessages([
                'password' => ['A senha informada está incorreta.'],
            ]);
        }

        $cliente->delete();
        return redirect()->route('clientes.index')->with('success', 'Cliente removido com sucesso!');
    }

    public function bulkAction(Request $request)
    {
        $action = $request->input('action');
        $selected = $request->input('selected', []);

        if (empty($selected)) {
            return back()->with('error', 'Nenhum item selecionado.');
        }

        // Security Check
        if (!$request->user()->isAdmin()) {
            return back()->with('error', 'Apenas administradores podem realizar ações em massa.');
        }

        $request->validate([
            'password' => 'required|string',
        ]);

        if (!Hash::check($request->password, $request->user()->senha)) {
            return back()->with('error', 'Senha incorreta. Ação cancelada.');
        }

        switch ($action) {
            case 'delete':
                Cliente::whereIn('id', $selected)->delete();
                return back()->with('success', count($selected) . ' clientes removidos com sucesso!');
        }

        return back()->with('error', 'Ação inválida.');
    }

    public function generatePdf(Request $request)
    {
        $query = Cliente::query();

        // Check if coming from bulk action (specific IDs)
        if ($request->has('selected') && is_array($request->selected)) {
            $query->whereIn('id', $request->selected);
        } else {
            // Apply normal filters only if not selecting specific items
            // Search
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('nome', 'like', "%{$search}%")
                      ->orWhere('razao_social', 'like', "%{$search}%")
                      ->orWhere('cpf', 'like', "%{$search}%")
                      ->orWhere('cnpj', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }

            // Filters
            if ($request->filled('tipo')) {
                $query->where('tipo', $request->tipo);
            }
            if ($request->filled('status_financeiro')) {
                if ($request->status_financeiro === 'pago') {
                    $query->where('modalidade_valor', 'pago');
                } elseif ($request->status_financeiro === 'gratuito') {
                    $query->where('modalidade_valor', 'gratuito');
                }
            }
        }

        // Sorting
        $sortField = $request->get('sort', 'created_at');
        $sortOrder = $request->get('order', 'desc');
        $allowedSorts = ['nome', 'created_at', 'tipo', 'valor_servico'];
        
        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortOrder);
        } else {
            $query->latest();
        }

        $clientes = $query->get();
        $columns = $request->input('columns', ['nome', 'tipo', 'servico', 'contato']);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('clientes.pdf', compact('clientes', 'columns'));
        return $pdf->download('clientes_sacratech_' . date('Y-m-d_H-i') . '.pdf');
    }
}
