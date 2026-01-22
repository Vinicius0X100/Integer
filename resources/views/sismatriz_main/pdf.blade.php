@extends('layouts.pdf')

@section('title', 'Relatório de Usuários - SisMatriz')

@section('header')
<div class="header-content">
    <div class="logo-section" style="width: 50%;">
        <img src="{{ public_path('img/sismatriz-logo.png') }}" style="height: 50px; margin-right: 15px; vertical-align: middle;">
        <span style="font-size: 20px; color: #ccc; vertical-align: middle;">|</span>
        <img src="{{ public_path('img/logo-black.png') }}" style="height: 40px; margin-left: 15px; vertical-align: middle;">
    </div>
    <div class="info-section" style="width: 50%;">
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
                @if(in_array('id', $columns))
                    <th>ID</th>
                @endif
                @if(in_array('name', $columns))
                    <th>Nome</th>
                @endif
                @if(in_array('user', $columns))
                    <th>Login</th>
                @endif
                @if(in_array('email', $columns))
                    <th>Email</th>
                @endif
                @if(in_array('paroquia', $columns))
                    <th>Paróquia</th>
                @endif
                @if(in_array('roles', $columns))
                    <th>Cargos</th>
                @endif
                @if(in_array('status', $columns))
                    <th>Status</th>
                @endif
                @if(in_array('created_at', $columns))
                    <th>Data de Cadastro</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach($users as $user)
                <tr>
                    @if(in_array('id', $columns))
                        <td>{{ $user->id }}</td>
                    @endif
                    @if(in_array('name', $columns))
                        <td>{{ $user->name }}</td>
                    @endif
                    @if(in_array('user', $columns))
                        <td>{{ $user->user }}</td>
                    @endif
                    @if(in_array('email', $columns))
                        <td><small>{{ $user->email }}</small></td>
                    @endif
                    @if(in_array('paroquia', $columns))
                        <td>{{ $user->paroquia ? $user->paroquia->name : '-' }}</td>
                    @endif
                    @if(in_array('roles', $columns))
                        <td>
                            @php
                                $userRoleIds = $user->rule ? explode(',', $user->rule) : [];
                                $userRoleNames = [];
                                foreach($userRoleIds as $rid) {
                                    if(isset($rolesMap[$rid])) {
                                        $userRoleNames[] = $rolesMap[$rid];
                                    }
                                }
                            @endphp
                            {{ implode(', ', $userRoleNames) }}
                        </td>
                    @endif
                    @if(in_array('status', $columns))
                        <td>{{ $user->status == 0 ? 'Ativo' : 'Inativo' }}</td>
                    @endif
                    @if(in_array('created_at', $columns))
                        <td>{{ $user->created_at ? $user->created_at->format('d/m/Y H:i') : '-' }}</td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
