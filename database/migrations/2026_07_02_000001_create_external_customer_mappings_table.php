<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('external_customer_mappings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('empresa_id')->nullable()->constrained('empresas')->nullOnDelete();
            $table->foreignId('empresa_integracao_id')->nullable()->constrained('empresa_integracoes')->nullOnDelete();
            $table->unsignedBigInteger('customer_id');
            $table->string('provider')->default('hongayetu_facturacao');
            $table->unsignedBigInteger('external_customer_id');
            $table->string('external_customer_name')->nullable();
            $table->string('external_customer_nif')->nullable();
            $table->json('external_payload')->nullable();
            $table->json('request_payload')->nullable();
            $table->json('response_payload')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamp('last_checked_at')->nullable();
            $table->text('last_error')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('empresa_id');
            $table->index('empresa_integracao_id');
            $table->index('customer_id');
            $table->index('external_customer_id');
            $table->unique(['empresa_id', 'customer_id', 'provider'], 'external_customer_mappings_local_unique');
            $table->index(['empresa_id', 'external_customer_id', 'provider'], 'external_customer_mappings_external_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('external_customer_mappings');
    }
};
