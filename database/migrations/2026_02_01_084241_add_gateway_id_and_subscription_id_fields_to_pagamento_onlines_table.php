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
        Schema::table('pagamento_onlines', function (Blueprint $table) {
            $table->string('gateway_id')->nullable()->after('merchant_transaction_id');
            $table->unsignedBigInteger('subscription_id')->nullable()->after('raw_response');

            $table->foreign('subscription_id')
                ->references('id')
                ->on('subscricoes')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pagamento_onlines', function (Blueprint $table) {
            $table->dropForeign(['subscription_id']);
            $table->dropColumn('gateway_id');
            $table->dropColumn('subscription_id');
        });
    }
};
