@extends('layouts.pdf')

@section('title', 'Relatório de Clientes - Integer')

@section('content')
    <h2 class="text-center text-uppercase fw-bold mb-4">Relatório de Clientes</h2>

    <table>
        <thead>
            <tr>
                @if(in_array('nome', $columns)) <th style="width: 30%">Cliente</th> @endif
                @if(in_array('tipo', $columns)) <th style="width: 10%">Tipo</th> @endif
                @if(in_array('servico', $columns)) <th style="width: 20%">Serviço</th> @endif
                @if(in_array('financeiro', $columns)) <th style="width: 15%">Valor</th> @endif
                @if(in_array('contato', $columns)) <th style="width: 25%">Contato</th> @endif
            </tr>
        </thead>
        <tbody>
            @foreach($clientes as $cliente)
                <tr>
                    @if(in_array('nome', $columns))
                        <td>
                            <div style="font-weight: bold;">
                                {{ $cliente->tipo === 'PF' ? $cliente->nome : ($cliente->nome ?? $cliente->razao_social) }}
                            </div>
                            <div style="font-size: 8pt; color: #666; margin-top: 2px;">
                                {{ $cliente->tipo === 'PF' ? $cliente->cpf_formatado : $cliente->cnpj_formatado }}
                            </div>
                        </td>
                    @endif
                    @if(in_array('tipo', $columns))
                        <td>{{ $cliente->tipo }}</td>
                    @endif
                    @if(in_array('servico', $columns))
                        <td>{{ $cliente->tipo_servico ?? '-' }}</td>
                    @endif
                    @if(in_array('financeiro', $columns))
                        <td>
                            @if($cliente->modalidade_valor === 'pago')
                                R$ {{ number_format($cliente->valor_servico, 2, ',', '.') }}
                            @else
                                <span class="badge badge-success">Gratuito</span>
                            @endif
                        </td>
                    @endif
                    @if(in_array('contato', $columns))
                        <td>
                            @if($cliente->email) <div>{{ $cliente->email }}</div> @endif
                            @if($cliente->telefone) <div>{{ $cliente->telefone }}</div> @endif
                        </td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="text-right" style="margin-top: 20px;">
        <strong>Total de Registros:</strong> {{ $clientes->count() }}
    </div>
@endsection