<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->enum('tipo', ['PF', 'PJ']);
            $table->string('nome', 200)->nullable();
            $table->string('cpf', 20)->nullable();
            $table->string('rg', 20)->nullable();
            $table->string('razao_social', 200)->nullable();
            $table->string('cnpj', 20)->nullable();
            $table->string('responsavel_legal', 200)->nullable();
            $table->string('representante', 200)->nullable();
            $table->string('tipo_empresa', 50)->nullable(); // Enum list is long, using string or we can define enum explicitly if needed.
            $table->text('descricao_servico')->nullable();
            $table->enum('tipo_servico', ['SaaS', 'Sob Demanda', 'Manutenção', 'Outros'])->nullable();
            $table->enum('modalidade_valor', ['gratuito', 'pago'])->default('gratuito');
            $table->enum('tipo_cobranca', ['mensal', 'valor_unico'])->default('mensal');
            $table->boolean('parcelado')->default(0);
            $table->integer('parcelas')->nullable();
            $table->decimal('valor_parcela', 10, 2)->nullable();
            $table->decimal('valor_servico', 10, 2)->nullable();
            $table->boolean('contrato_ativo')->default(0);
            $table->boolean('cobranca_automatica')->default(0);
            $table->string('cep', 12)->nullable();
            $table->string('logradouro', 200)->nullable();
            $table->string('numero', 20)->nullable();
            $table->string('complemento', 200)->nullable();
            $table->string('bairro', 200)->nullable();
            $table->string('cidade', 200)->nullable();
            $table->char('uf', 2)->nullable();
            $table->string('email', 200)->nullable();
            $table->string('telefone', 50)->nullable();
            
            $table->date('recorrencia_inicio_em')->nullable();
            $table->date('recorrencia_fim_em')->nullable();
            $table->date('parcelas_inicio_em')->nullable();
            $table->string('parcelas_status', 20)->nullable();

            $table->timestamps(); // criado_em, atualizado_em
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};
