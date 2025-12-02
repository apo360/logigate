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
        Schema::create('task_audit_items', function (Blueprint $table) {
           $table->bigIncrements('id');
            $table->unsignedBigInteger('scheduled_task_id');
            $table->unsignedBigInteger('execution_id')->nullable();
            $table->string('entity_type')->nullable();
            $table->string('entity_id')->nullable();
            $table->string('action')->nullable();
            $table->json('diff')->nullable();
            $table->timestamps();


            $table->foreign('scheduled_task_id')->references('id')->on('scheduled_tasks')->cascadeOnDelete();
            $table->foreign('execution_id')->references('id')->on('task_executions')->nullOnDelete();
            $table->index(['scheduled_task_id'], 'ix_task_audit_task');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_audit_items');
    }
};
