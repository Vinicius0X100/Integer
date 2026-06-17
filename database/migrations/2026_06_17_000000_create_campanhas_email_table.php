<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'integer';

    public function up(): void
    {
        Schema::connection('integer')->create('campanhas_email', function (Blueprint $table) {
            $table->id();

            $table->string('titulo');
            $table->longText('corpo_html');

            // Produto alvo: all, sacratech_id, sismatriz_ticket, sismatriz_main, airlink
            $table->string('produto')->default('all');

            // todos ou selecionados
            $table->string('destinatarios_tipo')->default('todos');

            // JSON com IDs quando destinatarios_tipo = selecionados
            $table->json('destinatarios_ids')->nullable();

            // Status: rascunho, enviando, enviado, erro
            $table->string('status')->default('rascunho');

            $table->unsignedInteger('total_destinatarios')->default(0);

            // Resposta bruta do webhook n8n
            $table->text('webhook_response')->nullable();

            $table->timestamp('enviado_em')->nullable();

            $table->unsignedBigInteger('criado_por')->nullable();

            $table->timestamp('criado_em')->useCurrent();
            $table->timestamp('atualizado_em')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::connection('integer')->dropIfExists('campanhas_email');
    }
};
