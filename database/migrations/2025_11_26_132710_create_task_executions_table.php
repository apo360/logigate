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
        Schema::create('task_executions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('scheduled_task_id');
            $table->integer('attempt')->default(1);
            $table->enum('status', ['running','success','failed','cancelled'])->default('running');
            $table->timestampTz('started_at')->nullable();
            $table->timestampTz('finished_at')->nullable();
            $table->unsignedBigInteger('executed_by')->nullable();
            $table->string('worker_id')->nullable();
            $table->integer('duration_ms')->nullable();
            $table->json('result')->nullable();
            $table->text('logs')->nullable();
            $table->timestamps();

            $table->foreign('scheduled_task_id')->references('id')->on('scheduled_tasks')->cascadeOnDelete();
            $table->foreign('executed_by')->references('id')->on('users')->nullOnDelete();

            $table->index(['scheduled_task_id','attempt'], 'ix_task_exec_task_attempt');
            $table->index(['started_at'], 'ix_task_exec_started_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_executions');
    }
};
