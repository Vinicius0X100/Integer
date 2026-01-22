<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    use HasFactory;

    protected $connection = 'integer';

    const CREATED_AT = 'criado_em';
    const UPDATED_AT = 'atualizado_em';

    protected $fillable = [
        'tipo',
        'nome',
        'cpf',
        'rg',
        'razao_social',
        'cnpj',
        'responsavel_legal',
        'representante',
        'tipo_empresa',
        'descricao_servico',
        'tipo_servico',
        'modalidade_valor',
        'tipo_cobranca',
        'parcelado',
        'parcelas',
        'valor_parcela',
        'valor_servico',
        'contrato_ativo',
        'cobranca_automatica',
        'cep',
        'logradouro',
        'numero',
        'complemento',
        'bairro',
        'cidade',
        'uf',
        'email',
        'telefone',
        'recorrencia_inicio_em',
        'recorrencia_fim_em',
        'parcelas_inicio_em',
        'parcelas_status',
    ];

    protected $casts = [
        'parcelado' => 'boolean',
        'contrato_ativo' => 'boolean',
        'cobranca_automatica' => 'boolean',
        'recorrencia_inicio_em' => 'date',
        'recorrencia_fim_em' => 'date',
        'parcelas_inicio_em' => 'date',
        'valor_parcela' => 'decimal:2',
        'valor_servico' => 'decimal:2',
    ];

    public function getCpfFormatadoAttribute()
    {
        if (!$this->cpf) return null;
        $cpf = preg_replace('/\D/', '', $this->cpf);
        if (strlen($cpf) !== 11) return $this->cpf;
        return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $cpf);
    }

    public function getCnpjFormatadoAttribute()
    {
        if (!$this->cnpj) return null;
        $cnpj = preg_replace('/\D/', '', $this->cnpj);
        if (strlen($cnpj) !== 14) return $this->cnpj;
        return preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $cnpj);
    }

    public function servicos()
    {
        return $this->hasMany(Servico::class);
    }
}
