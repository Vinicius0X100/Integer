<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    protected $table = 'usuarios';
    protected $primaryKey = 'id';
    
    const CREATED_AT = 'criado_em';
    const UPDATED_AT = 'atualizado_em';
    const DELETED_AT = 'excluido_em';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'email',
        'senha',
        'nome_usuario',
        'nome_exibicao',
        'nome',
        'sobrenome',
        'data_nascimento',
        'papel',
        'status',
        'email_verificado_em',
        'telefone_verificado_em',
        'dois_fatores_ativo',
        'segredo_dois_fatores',
        'ultimo_login_em',
        'ultimo_login_ip',
        'tentativas_falhas',
        'bloqueado_ate',
        'senha_alterada_em',
        'telefone',
        'cep',
        'endereco',
        'numero',
        'bairro',
        'cidade',
        'estado',
        'pais',
        'complemento',
        'avatar_url',
        'fuso_horario',
        'idioma',
        'cota_armazenamento_bytes',
        'uso_armazenamento_bytes',
        'plano_assinatura',
        'assinatura_expira_em',
        'cliente_billing_id',
        'bem_vindo_enviado',
        'metodo_pagamento',
        'criado_por',
        'atualizado_por',
        'nextcloud_user',
        'nextcloud_criado_em',
        'nextcloud_status',
        'senha_temporaria',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'senha',
        'segredo_dois_fatores',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verificado_em' => 'datetime',
            'senha' => 'hashed',
            'data_nascimento' => 'date',
            'ultimo_login_em' => 'datetime',
            'bloqueado_ate' => 'datetime',
            'senha_alterada_em' => 'datetime',
            'assinatura_expira_em' => 'datetime',
            'nextcloud_criado_em' => 'datetime',
        ];
    }

    /**
     * Check if the user is an administrator.
     */
    public function isAdmin(): bool
    {
        return $this->papel === 'admin';
    }

    public function getAuthPassword()
    {
        return $this->senha;
    }
}
