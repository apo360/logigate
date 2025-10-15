```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recibo_facturas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('reciboID');
            $table->integer('linha_number')->default(1);
            $table->unsignedBigInteger('documentoID');
            $table->decimal('desconto_documento', 18, 2)->nullable();
            $table->decimal('valor_em_aberto', 18, 2)->nullable();
            $table->decimal('valor_liquidado', 18, 2)->nullable();

            $table->timestamps();

            // FK para recibos
            $table->foreign('reciboID')->references('id')->on('recibos')->onDelete('cascade');

            // FK para invoices (ajustar conforme nome da tabela de faturas no teu sistema)
            $table->foreign('documentoID')->references('id')->on('sales_invoice')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recibo_facturas');
    }
};