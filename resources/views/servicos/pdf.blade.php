@extends('layouts.pdf')

@section('title', 'Relatório de Serviços - Integer')

@section('content')
    <h2 class="text-center text-uppercase fw-bold mb-4">Relatório de Serviços</h2>

    <table>
        <thead>
            <tr>
                @if(in_array('cliente', $columns)) <th style="width: 20%">Cliente</th> @endif
                @if(in_array('titulo', $columns)) <th style="width: 35%">Título/Descrição</th> @endif
                @if(in_array('valor', $columns)) <th style="width: 15%">Valor</th> @endif
                @if(in_array('status', $columns)) <th style="width: 15%">Status</th> @endif
                @if(in_array('data', $columns)) <th style="width: 15%">Data</th> @endif
            </tr>
        </thead>
        <tbody>
            @foreach($servicos as $servico)
                <tr>
                    @if(in_array('cliente', $columns))
                        <td>{{ $servico->cliente->nome ?? $servico->cliente->razao_social }}</td>
                    @endif
                    @if(in_array('titulo', $columns))
                        <td>
                            <div style="font-weight: bold;">{{ $servico->titulo }}</div>
                            @if($servico->descricao)
                                <div style="font-size: 8pt; color: #666; margin-top: 2px;">{{ Str::limit($servico->descricao, 60) }}</div>
                            @endif
                        </td>
                    @endif
                    @if(in_array('valor', $columns))
                        <td>R$ {{ number_format($servico->valor_total, 2, ',', '.') }}</td>
                    @endif
                    @if(in_array('status', $columns))
                        <td>
                            @php
                                $badgeClass = match($servico->status) {
                                    'pendente' => 'badge-warning',
                                    'em_andamento' => 'badge-info',
                                    'concluido' => 'badge-success',
                                    'cancelado' => 'badge-danger',
                                    default => 'badge-secondary'
                                };
                            @endphp
                            <span class="badge {{ $badgeClass }}">
                                {{ ucfirst(str_replace('_', ' ', $servico->status)) }}
                            </span>
                        </td>
                    @endif
                    @if(in_array('data', $columns))
                        <td>{{ \Carbon\Carbon::parse($servico->data_servico)->format('d/m/Y') }}</td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>
    
    <div class="text-right" style="margin-top: 20px;">
        <strong>Total de Registros:</strong> {{ $servicos->count() }}
    </div>
@endsection