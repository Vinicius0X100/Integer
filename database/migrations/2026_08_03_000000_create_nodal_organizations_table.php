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
        Schema::create('nodal_organizations', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('slug')->nullable();
            $table->unsignedBigInteger('nodal_organization_id')->nullable()->comment('ID retornado pelo Nodal após provisioning');
            $table->unsignedBigInteger('nodal_user_id')->nullable()->comment('ID do usuário owner criado no Nodal');
            $table->string('owner_name');
            $table->string('owner_email');
            $table->string('nodal_login_url')->nullable();
            $table->string('status')->default('active')->comment('active, error');
            $table->text('provisioning_error')->nullable()->comment('Mensagem de erro caso o provisioning falhe');
            $table->timestamp('provisionado_em')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nodal_organizations');
    }
};
