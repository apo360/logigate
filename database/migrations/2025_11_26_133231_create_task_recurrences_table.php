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
        Schema::create('task_recurrences', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('scheduled_task_id');
            $table->text('rrule');
            $table->string('timezone')->default(config('app.timezone'));
            $table->timestampTz('end_date')->nullable();
            $table->timestamps();

            $table->foreign('scheduled_task_id')->references('id')->on('scheduled_tasks')->cascadeOnDelete();
            $table->index(['scheduled_task_id'], 'ix_task_recurrences_task');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_recurrences');
    }
};
