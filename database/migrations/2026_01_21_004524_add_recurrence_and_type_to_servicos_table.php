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
        Schema::connection('integer')->table('servicos', function (Blueprint $table) {
            $table->string('tipo_servico')->nullable()->after('titulo');
            $table->boolean('recorrente')->default(false)->after('parcelado');
            $table->decimal('valor_recorrencia', 10, 2)->nullable()->after('valor_parcela');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('integer')->table('servicos', function (Blueprint $table) {
            $table->dropColumn(['tipo_servico', 'recorrente', 'valor_recorrencia']);
        });
    }
};
