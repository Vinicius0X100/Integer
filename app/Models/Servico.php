<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Servico extends Model
{
    use HasFactory;

    protected $connection = 'integer';
    protected $table = 'servicos';

    protected $fillable = [
        'cliente_id',
        'titulo',
        'tipo_servico',
        'descricao',
        'valor_total',
        'custo_interno',
        'lucro_estimado',
        'parcelado',
        'qtd_parcelas',
        'valor_parcela',
        'recorrente',
        'valor_recorrencia',
        'data_servico',
        'prazo_entrega',
        'status',
        'contrato_path',
    ];

    protected $casts = [
        'data_servico' => 'date',
        'prazo_entrega' => 'date',
        'parcelado' => 'boolean',
        'recorrente' => 'boolean',
        'valor_total' => 'decimal:2',
        'custo_interno' => 'decimal:2',
        'lucro_estimado' => 'decimal:2',
        'valor_parcela' => 'decimal:2',
        'valor_recorrencia' => 'decimal:2',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }
}
