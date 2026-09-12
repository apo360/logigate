<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('external_invoices', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('empresa_id')->nullable()->constrained('empresas')->nullOnDelete();
            $table->foreignId('empresa_integracao_id')->nullable()->constrained('empresa_integracoes')->nullOnDelete();
            $table->foreignId('external_customer_mapping_id')->nullable()->constrained('external_customer_mappings')->nullOnDelete();
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('contract_id')->nullable();
            $table->unsignedBigInteger('avenca_id')->nullable();
            $table->unsignedBigInteger('processo_id')->nullable();
            $table->string('provider')->default('hongayetu_facturacao');
            $table->unsignedBigInteger('external_customer_id')->nullable();
            $table->unsignedBigInteger('external_invoice_id')->nullable();
            $table->string('external_invoice_number')->nullable();
            $table->string('local_reference')->nullable();
            $table->string('document_type')->nullable();
            $table->unsignedTinyInteger('api_tipo')->nullable();
            $table->unsignedTinyInteger('api_estado')->nullable();
            $table->string('status')->default('draft');
            $table->dateTime('issue_date')->nullable();
            $table->dateTime('due_date')->nullable();
            $table->string('currency', 3)->default('AOA');
            $table->unsignedTinyInteger('moeda')->default(0);
            $table->decimal('cambio', 18, 6)->nullable();
            $table->decimal('subtotal', 18, 2)->default(0);
            $table->decimal('tax_total', 18, 2)->default(0);
            $table->decimal('discount_total', 18, 2)->default(0);
            $table->decimal('gross_total', 18, 2)->default(0);
            $table->decimal('paid_total', 18, 2)->default(0);
            $table->decimal('balance_due', 18, 2)->default(0);
            $table->json('request_payload')->nullable();
            $table->json('response_payload')->nullable();
            $table->string('pdf_disk')->nullable();
            $table->string('pdf_path')->nullable();
            $table->string('pdf_filename')->nullable();
            $table->string('pdf_mime')->nullable();
            $table->unsignedBigInteger('pdf_size')->nullable();
            $table->timestamp('pdf_stored_at')->nullable();
            $table->text('last_error')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('issued_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('empresa_id');
            $table->index('empresa_integracao_id');
            $table->index('external_customer_mapping_id');
            $table->index('customer_id');
            $table->index('contract_id');
            $table->index('avenca_id');
            $table->index('processo_id');
            $table->index('external_customer_id');
            $table->index('external_invoice_id');
            $table->index('external_invoice_number');
            $table->index('local_reference');
            $table->index(['empresa_id', 'provider', 'status'], 'external_invoices_empresa_provider_status_idx');
            $table->index(['empresa_id', 'external_invoice_id', 'provider'], 'external_invoices_empresa_external_idx');
            $table->index(['customer_id', 'status'], 'external_invoices_customer_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('external_invoices');
    }
};
