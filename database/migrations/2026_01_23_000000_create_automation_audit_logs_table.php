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
        if (! Schema::hasTable('automation_audit_logs')) {
            Schema::create('automation_audit_logs', function (Blueprint $table) {
                $table->id();
                $table->string('automation_key')->nullable()->index();
                $table->string('automation_name')->nullable();
                $table->string('status')->nullable();
                $table->timestamp('started_at')->nullable();
                $table->timestamp('finished_at')->nullable();
                $table->integer('duration_ms')->nullable();
                $table->json('summary')->nullable();
                $table->json('details')->nullable();
                $table->text('error_message')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('automation_audit_logs');
    }
};
