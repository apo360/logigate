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
        Schema::create('scheduled_tasks', function (Blueprint $table) {
        $table->id();
        $table->uuid('uuid')->unique();
        $table->string('title');
        $table->text('description')->nullable();
        $table->enum('executor_type', ['user','ai','system'])->default('user');
        $table->enum('type', ['invoice','payment','alert','backup','custom'])->default('custom');
        $table->enum('status', ['pending','running','completed','failed','cancelled'])->default('pending');
        $table->timestampTz('schedule_date');
        $table->timestampTz('next_run_at')->nullable();
        $table->enum('recurrence', ['none','daily','weekly','monthly','yearly'])->default('none');
        $table->text('recurrence_rule')->nullable();
        $table->json('payload')->nullable();
        $table->json('metadata')->nullable();
        $table->unsignedBigInteger('created_by')->nullable();
        $table->boolean('approved')->default(false);
        $table->unsignedBigInteger('approved_by')->nullable();
        $table->timestampTz('approved_at')->nullable();
        $table->timestamps();


        $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
        $table->foreign('approved_by')->references('id')->on('users')->nullOnDelete();


        $table->index(['status','schedule_date'], 'ix_scheduled_tasks_status_schedule');
        $table->index(['next_run_at'], 'ix_scheduled_tasks_next_run_at');
        $table->index(['created_by'], 'ix_scheduled_tasks_created_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scheduled_tasks');
    }
};
