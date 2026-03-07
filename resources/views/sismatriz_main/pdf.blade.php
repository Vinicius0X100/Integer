@extends('layouts.pdf')

@section('title', 'Relatório de Usuários - SisMatriz')

@section('header')
<div class="header-content" style="border-bottom: 1px solid #eee; padding-bottom: 10px;">
    <table style="width: 100%; border: none; margin: 0;">
        <tr style="background-color: transparent;">
            <td style="width: 60px; border: none; padding: 0;">
                @if(file_exists(public_path('img/logo-black.png')))
                    <img src="{{ public_path('img/logo-black.png') }}" style="height: 50px; width: 50px; border-radius: 50%; object-fit: cover;">
                @elseif(file_exists(public_path('img/logo.png')))
                    <img src="{{ public_path('img/logo.png') }}" style="height: 50px; width: 50px; border-radius: 50%; object-fit: cover;">
                @endif
            </td>
            <td style="border: none; padding: 0 0 0 15px; vertical-align: middle;">
                <h1 style="margin: 0; font-size: 16pt; color: #333;">Integer</h1>
                <div style="font-size: 10pt; color: #666; margin-top: 2px;">Relatório de Usuários - SisMatriz</div>
            </td>
            <td style="border: none; padding: 0; text-align: right; vertical-align: middle;">
                <div style="font-size: 8pt; color: #888; text-transform: uppercase; letter-spacing: 1px;">Data de Emissão</div>
                <div style="font-size: 10pt; font-weight: bold; color: #333;">{{ date('d/m/Y \à\s H:i') }}</div>
            </td>
        </tr>
    </table>
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
                @if(in_array('is_pass_change', $columns))
                    <th>Status Senha</th>
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
                    @if(in_array('is_pass_change', $columns))
                        <td>{{ $user->is_pass_change == 1 ? 'Alterada' : 'Padrão' }}</td>
                    @endif
                    @if(in_array('created_at', $columns))
                        <td>{{ $user->created_at ? $user->created_at->format('d/m/Y H:i') : '-' }}</td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
