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
        Schema::table('planos', function (Blueprint $table) {
            $table->boolean('is_free')
                ->default(false)
                ->after('status');

            $table->integer('trial_days')
                ->nullable()
                ->after('is_free');

            $table->boolean('is_popular')
                ->default(false)
                ->after('trial_days');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('planos', function (Blueprint $table) {
            $table->dropColumn([
                'is_free',
                'trial_days',
                'is_popular',
            ]);
        });
    }
};
