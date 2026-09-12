<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('external_api_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('empresa_id')->nullable()->constrained('empresas')->nullOnDelete();
            $table->foreignId('empresa_integracao_id')->nullable()->constrained('empresa_integracoes')->nullOnDelete();
            $table->foreignId('external_invoice_id')->nullable()->constrained('external_invoices')->nullOnDelete();
            $table->string('provider')->default('hongayetu_facturacao');
            $table->string('endpoint');
            $table->string('method', 10);
            $table->json('request_payload')->nullable();
            $table->json('response_payload')->nullable();
            $table->unsignedSmallInteger('status_code')->nullable();
            $table->boolean('success')->default(false);
            $table->string('error_code')->nullable();
            $table->text('error_message')->nullable();
            $table->unsignedInteger('duration_ms')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();

            $table->index('empresa_id');
            $table->index('empresa_integracao_id');
            $table->index('external_invoice_id');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('external_api_logs');
    }
};
