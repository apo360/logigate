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
        Schema::create('task_notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('scheduled_task_id');
            $table->enum('notification_type', ['email','dashboard','whatsapp','sms'])->default('dashboard');
            $table->json('recipient')->nullable();
            $table->timestampTz('sent_at')->nullable();
            $table->enum('status', ['pending','sent','failed'])->default('pending');
            $table->json('payload')->nullable();
            $table->timestamps();


            $table->foreign('scheduled_task_id')->references('id')->on('scheduled_tasks')->cascadeOnDelete();
            $table->index(['scheduled_task_id'], 'ix_task_notifications_task');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_notifications');
    }
};
