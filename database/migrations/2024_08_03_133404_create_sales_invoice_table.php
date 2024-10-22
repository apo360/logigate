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
        Schema::create('sales_invoice', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_no', 20)->notNull();
            $table->string('hash', 172)->default('0');
            $table->string('hash_control', 1)->default('0');
            $table->integer('period')->nullable(); // Ajuste para permitir valores nulos se necessário
            $table->date('invoice_date')->notNull();
            $table->date('invoice_date_end')->notNull(); // Data de vencimento da factura
            $table->unsignedBigInteger('invoice_type_id');
            $table->integer('self_billing_indicator')->default(0);
            $table->integer('cash_vat_scheme_indicator')->default(0);
            $table->integer('third_parties_billing_indicator')->default(0);
            $table->unsignedBigInteger('source_id');
            $table->datetime('system_entry_date')->nullable();
            $table->integer('transaction_id')->nullable();
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('ship_to_id')->nullable();
            $table->unsignedBigInteger('from_to_id')->nullable();
            $table->datetime('movement_end_time')->nullable();
            $table->datetime('movement_start_time')->nullable();
            $table->char('imposto_retido', 3)->nullable();
            $table->string('motivo_retencao', 60)->nullable();
            $table->decimal('montante_retencao', 10, 2)->default(0.00);
            $table->unsignedBigInteger('empresa_id');
            $table->timestamps();

            $table->foreign('customer_id')->references('id')->on('customers');
            $table->foreign('source_id')->references('id')->on('users');
            $table->foreign('invoice_type_id')->references('id')->on('invoice_types');
            $table->foreign('empresa_id')->references('id')->on('empresas');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_invoice');
    }
};
