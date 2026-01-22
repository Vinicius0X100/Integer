@extends('layouts.pdf')

@section('title', 'Relatório de Usuários - Sacratech iD')

@section('header')
<div class="header-content">
    <div class="logo-section">
        @if(file_exists(public_path('img/sacratech-id.png')))
            <img src="{{ public_path('img/sacratech-id.png') }}" class="logo" alt="Sacratech iD" style="max-height: 40px;">
        @else
            <h1 class="company-name" style="margin:0;">Sacratech iD</h1>
        @endif
    </div>
    <div class="info-section">
        <div class="company-name">Sacratech iD</div>
        <div class="security-badge">
            <span style="font-family: sans-serif;">🔒</span> Relatório de Usuários &bull; Confidencial
        </div>
        <div style="font-size: 8pt; color: #888; margin-top: 4px;">
            Emitido em: {{ date('d/m/Y H:i') }}
        </div>
    </div>
</div>
@endsection

@section('content')
    <table>
        <thead>
            <tr>
                <th>Nome</th>
                @if(in_array('email', $columns)) <th>Email</th> @endif
                @if(in_array('papel', $columns)) <th>Papel</th> @endif
                @if(in_array('status', $columns)) <th>Status</th> @endif
                @if(in_array('ultimo_login', $columns)) <th>Último Login</th> @endif
            </tr>
        </thead>
        <tbody>
            @foreach($users as $user)
                <tr>
                    <td>{{ $user->nome }} {{ $user->sobrenome }}</td>
                    @if(in_array('email', $columns)) <td>{{ $user->email }}</td> @endif
                    @if(in_array('papel', $columns)) <td>{{ ucfirst($user->papel) }}</td> @endif
                    @if(in_array('status', $columns))
                        <td>
                            @if($user->status)
                                <span class="badge badge-success">Ativo</span>
                            @else
                                <span class="badge badge-danger">Inativo</span>
                            @endif
                        </td>
                    @endif
                    @if(in_array('ultimo_login', $columns))
                        <td>
                            {{ $user->ultimo_login_em ? $user->ultimo_login_em->format('d/m/Y H:i') : '-' }}
                        </td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
