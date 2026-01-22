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
        Schema::connection('integer')->create('servicos', function (Blueprint $table) {
            $table->id();
            $table->integer('cliente_id');
            $table->foreign('cliente_id')->references('id')->on('clientes')->onDelete('cascade');
            $table->string('titulo');
            $table->text('descricao')->nullable();
            
            // Financeiro
            $table->decimal('valor_total', 10, 2);
            $table->decimal('custo_interno', 10, 2)->nullable();
            $table->decimal('lucro_estimado', 10, 2)->nullable();
            
            // Parcelamento
            $table->boolean('parcelado')->default(false);
            $table->integer('qtd_parcelas')->nullable();
            $table->decimal('valor_parcela', 10, 2)->nullable();
            
            // Prazos e Status
            $table->date('data_servico');
            $table->date('prazo_entrega')->nullable();
            $table->enum('status', ['pendente', 'em_andamento', 'concluido', 'cancelado'])->default('pendente');
            
            // Arquivos
            $table->string('contrato_path')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('integer')->dropIfExists('servicos');
    }
};
