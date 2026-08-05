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
        Schema::table('nodal_organizations', function (Blueprint $table) {
            $table->string('nodal_organization_uuid', 36)->nullable()->after('slug')->comment('UUID retornado pelo Nodal após provisioning');
            $table->string('nodal_user_uuid', 36)->nullable()->after('nodal_organization_uuid')->comment('UUID do usuário owner criado no Nodal');
            
            $table->dropColumn('nodal_organization_id');
            $table->dropColumn('nodal_user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nodal_organizations', function (Blueprint $table) {
            $table->unsignedBigInteger('nodal_organization_id')->nullable()->after('slug')->comment('ID retornado pelo Nodal após provisioning');
            $table->unsignedBigInteger('nodal_user_id')->nullable()->after('nodal_organization_id')->comment('ID do usuário owner criado no Nodal');
            
            $table->dropColumn('nodal_organization_uuid');
            $table->dropColumn('nodal_user_uuid');
        });
    }
};
