<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CampanhaEmail extends Model
{
    use HasFactory;

    protected $connection = 'integer';
    protected $table = 'campanhas_email';

    const CREATED_AT = 'criado_em';
    const UPDATED_AT = 'atualizado_em';

    protected $fillable = [
        'titulo',
        'corpo_html',
        'produto',
        'destinatarios_tipo',
        'destinatarios_ids',
        'status',
        'total_destinatarios',
        'webhook_response',
        'enviado_em',
        'criado_por',
    ];

    protected $casts = [
        'destinatarios_ids' => 'array',
        'enviado_em'        => 'datetime',
        'criado_em'         => 'datetime',
        'atualizado_em'     => 'datetime',
    ];

    /**
     * Labels legíveis para os produtos.
     */
    public static function produtoLabel(string $produto): string
    {
        return match ($produto) {
            'all'              => 'Todos os Produtos',
            'sacratech_id'     => 'Sacratech iD',
            'sismatriz_ticket' => 'SisMatriz Ticket',
            'sismatriz_main'   => 'SisMatriz Principal',
            'airlink'          => 'Airlink Locate',
            default            => $produto,
        };
    }

    /**
     * Labels legíveis para os status.
     */
    public static function statusLabel(string $status): string
    {
        return match ($status) {
            'rascunho' => 'Rascunho',
            'enviando' => 'Enviando...',
            'enviado'  => 'Enviado',
            'erro'     => 'Erro',
            default    => $status,
        };
    }

    /**
     * Classe Bootstrap do badge de status.
     */
    public static function statusBadgeClass(string $status): string
    {
        return match ($status) {
            'rascunho' => 'secondary',
            'enviando' => 'warning',
            'enviado'  => 'success',
            'erro'     => 'danger',
            default    => 'secondary',
        };
    }

}

