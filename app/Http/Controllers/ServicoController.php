<?php

namespace App\Http\Controllers;

use App\Models\Servico;
use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ServicoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Servico::with('cliente');

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('titulo', 'like', "%{$search}%")
                  ->orWhere('descricao', 'like', "%{$search}%")
                  ->orWhereHas('cliente', function($subQ) use ($search) {
                      $subQ->where('nome', 'like', "%{$search}%")
                           ->orWhere('razao_social', 'like', "%{$search}%");
                  });
            });
        }

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('tipo_servico')) {
            $query->where('tipo_servico', $request->tipo_servico);
        }
        if ($request->filled('recorrente')) {
            $query->where('recorrente', $request->recorrente == '1');
        }

        // Sorting
        $sortField = $request->get('sort', 'created_at');
        $sortOrder = $request->get('order', 'desc');
        $allowedSorts = ['titulo', 'valor_total', 'data_servico', 'status', 'created_at'];

        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortOrder);
        } else {
            $query->latest();
        }

        $servicos = $query->paginate(10)->withQueryString();
        
        return view('servicos.index', compact('servicos'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $clientes = Cliente::orderBy('nome')->get();
        return view('servicos.create', compact('clientes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'cliente_id' => 'required|exists:integer.clientes,id',
            'titulo' => 'required|string|max:255',
            'valor_total' => 'required|string',
            'data_servico' => 'required|date',
            'status' => 'required|in:pendente,em_andamento,concluido,cancelado',
            'contrato' => 'nullable|file|mimes:pdf,doc,docx,jpg,png|max:2048',
            'tipo_servico' => 'nullable|string',
        ]);

        $data = $request->all();
        
        // Format money
        $data['valor_total'] = str_replace(['.', ','], ['', '.'], $request->valor_total);
        if($request->custo_interno) {
            $data['custo_interno'] = str_replace(['.', ','], ['', '.'], $request->custo_interno);
        }
        if($request->valor_parcela) {
            $data['valor_parcela'] = str_replace(['.', ','], ['', '.'], $request->valor_parcela);
        }
        if($request->valor_recorrencia) {
            $data['valor_recorrencia'] = str_replace(['.', ','], ['', '.'], $request->valor_recorrencia);
        }

        // Calculate profit if cost is provided
        if(isset($data['custo_interno']) && $data['custo_interno'] > 0) {
            $data['lucro_estimado'] = $data['valor_total'] - $data['custo_interno'];
        }

        // Handle boolean
        $data['parcelado'] = $request->has('parcelado');
        $data['recorrente'] = $request->has('recorrente');

        if(!$data['recorrente']) {
            $data['valor_recorrencia'] = null;
        }

        // Handle file upload
        if ($request->hasFile('contrato')) {
            $data['contrato_path'] = $request->file('contrato')->store('contratos', 'public');
        }

        Servico::create($data);

        return redirect()->route('servicos.index')->with('success', 'Serviço cadastrado com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // Implement modal show logic or separate page
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $servico = Servico::findOrFail($id);
        $clientes = Cliente::orderBy('nome')->get();
        return view('servicos.edit', compact('servico', 'clientes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $servico = Servico::findOrFail($id);

        $request->validate([
            'cliente_id' => 'required|exists:integer.clientes,id',
            'titulo' => 'required|string|max:255',
            'valor_total' => 'required|string',
            'data_servico' => 'required|date',
            'status' => 'required|in:pendente,em_andamento,concluido,cancelado',
            'contrato' => 'nullable|file|mimes:pdf,doc,docx,jpg,png|max:2048',
            'tipo_servico' => 'nullable|string',
        ]);

        $data = $request->all();

        // Format money
        $data['valor_total'] = str_replace(['.', ','], ['', '.'], $request->valor_total);
        if($request->custo_interno) {
            $data['custo_interno'] = str_replace(['.', ','], ['', '.'], $request->custo_interno);
        } else {
            $data['custo_interno'] = null;
        }
        
        if($request->valor_parcela) {
            $data['valor_parcela'] = str_replace(['.', ','], ['', '.'], $request->valor_parcela);
        } else {
            $data['valor_parcela'] = null;
        }

        if($request->valor_recorrencia) {
            $data['valor_recorrencia'] = str_replace(['.', ','], ['', '.'], $request->valor_recorrencia);
        } else {
            $data['valor_recorrencia'] = null;
        }

        // Calculate profit
        if(isset($data['custo_interno']) && $data['custo_interno'] > 0) {
            $data['lucro_estimado'] = $data['valor_total'] - $data['custo_interno'];
        } else {
            $data['lucro_estimado'] = null;
        }

        // Handle boolean
        $data['parcelado'] = $request->has('parcelado');
        if(!$data['parcelado']) {
            $data['qtd_parcelas'] = null;
            $data['valor_parcela'] = null;
        }

        $data['recorrente'] = $request->has('recorrente');
        if(!$data['recorrente']) {
            $data['valor_recorrencia'] = null;
        }

        // Handle file upload
        if ($request->hasFile('contrato')) {
            // Delete old file if exists
            if ($servico->contrato_path) {
                Storage::disk('public')->delete($servico->contrato_path);
            }
            $data['contrato_path'] = $request->file('contrato')->store('contratos', 'public');
        }

        $servico->update($data);

        return redirect()->route('servicos.index')->with('success', 'Serviço atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $servico = Servico::findOrFail($id);
        if ($servico->contrato_path) {
            Storage::disk('public')->delete($servico->contrato_path);
        }
        $servico->delete();

        return redirect()->route('servicos.index')->with('success', 'Serviço excluído com sucesso!');
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

        if (!\Illuminate\Support\Facades\Hash::check($request->password, $request->user()->senha)) {
            return back()->with('error', 'Senha incorreta. Ação cancelada.');
        }

        switch ($action) {
            case 'delete':
                $servicos = Servico::whereIn('id', $selected)->get();
                foreach($servicos as $servico) {
                    if ($servico->contrato_path) {
                        Storage::disk('public')->delete($servico->contrato_path);
                    }
                    $servico->delete();
                }
                return back()->with('success', count($selected) . ' serviços excluídos com sucesso!');
            
            case 'concluido':
                Servico::whereIn('id', $selected)->update(['status' => 'concluido']);
                return back()->with('success', count($selected) . ' serviços marcados como concluído!');
        }

        return back()->with('error', 'Ação inválida.');
    }

    public function generatePdf(Request $request)
    {
        $query = Servico::with('cliente');

        // Check if coming from bulk action (specific IDs)
        if ($request->has('selected') && is_array($request->selected)) {
            $query->whereIn('id', $request->selected);
        } else {
            // Apply normal filters only if not selecting specific items
            // Search
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('titulo', 'like', "%{$search}%")
                      ->orWhere('descricao', 'like', "%{$search}%")
                      ->orWhereHas('cliente', function($subQ) use ($search) {
                          $subQ->where('nome', 'like', "%{$search}%")
                               ->orWhere('razao_social', 'like', "%{$search}%");
                      });
                });
            }

            // Filters
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
            if ($request->filled('tipo_servico')) {
                $query->where('tipo_servico', $request->tipo_servico);
            }
            if ($request->filled('recorrente')) {
                $query->where('recorrente', $request->recorrente == '1');
            }
        }

        // Sorting
        $sortField = $request->get('sort', 'created_at');
        $sortOrder = $request->get('order', 'desc');
        $allowedSorts = ['titulo', 'valor_total', 'data_servico', 'status', 'created_at'];

        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortOrder);
        } else {
            $query->latest();
        }

        $servicos = $query->get();
        $columns = $request->input('columns', ['cliente', 'titulo', 'valor', 'status', 'data']);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('servicos.pdf', compact('servicos', 'columns'));
        return $pdf->download('servicos_sacratech_' . date('Y-m-d_H-i') . '.pdf');
    }
}
