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
        Schema::create('nodal_verifications', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 36)->unique()->comment('UUID da verificação no Nodal');
            $table->string('nodal_organization_uuid', 36);
            $table->string('organization_name');
            $table->string('document_type');
            $table->string('status')->default('pending')->comment('pending, approved, rejected');
            $table->string('document_url')->nullable()->comment('URL temporária do documento para download/visualização');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nodal_verifications');
    }
};
