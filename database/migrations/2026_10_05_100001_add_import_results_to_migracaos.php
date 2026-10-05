<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('migracaos', function (Blueprint $table) {
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->json('result')->nullable();
        });
    }
    public function down(): void
    {
        Schema::table('migracaos', fn (Blueprint $table) => $table->dropColumn(['actor_id', 'result']));
    }
};
