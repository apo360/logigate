<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('external_invoice_lines', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('external_invoice_id')->constrained('external_invoices')->cascadeOnDelete();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->unsignedBigInteger('service_id')->nullable();
            $table->unsignedBigInteger('external_artigo_id')->nullable();
            $table->string('type')->default('service');
            $table->text('description');
            $table->decimal('quantity', 18, 4)->default(1);
            $table->decimal('unit_price', 18, 2)->default(0);
            $table->decimal('tax_percentage', 8, 4)->default(0);
            $table->decimal('discount_amount', 18, 2)->default(0);
            $table->decimal('line_net_total', 18, 2)->default(0);
            $table->decimal('line_tax_total', 18, 2)->default(0);
            $table->decimal('line_total', 18, 2)->default(0);
            $table->json('payload')->nullable();
            $table->timestamps();

            $table->index('product_id');
            $table->index('service_id');
            $table->index('external_artigo_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('external_invoice_lines');
    }
};
