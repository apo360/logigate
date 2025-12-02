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
        Schema::create('task_templates', function (Blueprint $table) {
           $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->enum('type', ['invoice','payment','alert','backup','custom'])->default('custom');
            $table->json('default_payload')->nullable();
            $table->enum('default_recurrence', ['none','daily','weekly','monthly','yearly'])->default('none');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();


            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_templates');
    }
};
