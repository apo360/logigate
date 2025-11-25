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
        Schema::create('product_price_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_price_id');
            $table->decimal('old_price', 15, 4)->nullable();
            $table->decimal('new_price', 15, 4)->nullable();
            $table->decimal('old_tax', 10, 2)->nullable();
            $table->decimal('new_tax', 10, 2)->nullable();
            $table->unsignedBigInteger('changed_by')->nullable();
            $table->string('change_reason')->nullable();
            $table->string('change_origin', 20)->default('Manual');
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('product_price_id')->references('id')->on('product_prices')->onDelete('cascade');
            $table->foreign('changed_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_price_logs');
    }
};
