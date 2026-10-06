<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('marketplace_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->unique()->constrained('empresas');
            $table->string('public_name');
            $table->string('public_location')->nullable();
            $table->boolean('service_provider')->default(false);
            $table->boolean('published')->default(false);
            $table->string('consent_reference')->nullable();
            $table->boolean('history_authorized')->default(false);
            $table->timestamp('history_reviewed_at')->nullable();
            $table->string('catalogue_revision')->nullable();
            $table->timestamp('calculated_at')->nullable();
            $table->timestamps();
        });
        Schema::create('marketplace_specialties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->constrained('marketplace_profiles')->cascadeOnDelete();
            $table->foreignId('pauta_id')->constrained('pauta_aduaneira');
            $table->string('codigo', 50);
            $table->unique(['profile_id', 'pauta_id']);
        });
        Schema::create('marketplace_activity_months', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->constrained('marketplace_profiles')->cascadeOnDelete();
            $table->foreignId('pauta_id')->constrained('pauta_aduaneira');
            $table->string('codigo', 50);
            $table->date('month');
            $table->unsignedInteger('operations');
            $table->date('last_operation');
            $table->unique(['profile_id', 'pauta_id', 'month'], 'mp_activity_identity');
            $table->index(['pauta_id', 'month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketplace_activity_months');
        Schema::dropIfExists('marketplace_specialties');
        Schema::dropIfExists('marketplace_profiles');
    }
};
