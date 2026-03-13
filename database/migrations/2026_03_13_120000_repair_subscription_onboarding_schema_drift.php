<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscricoes', function (Blueprint $table) {
            if (! Schema::hasColumn('subscricoes', 'data_inicio')) {
                $table->timestamp('data_inicio')->nullable()->after('data_subscricao');
            }

            if (! Schema::hasColumn('subscricoes', 'activated_at')) {
                $table->timestamp('activated_at')->nullable()->after('data_expiracao');
            }
        });

        Schema::table('pagamento_onlines', function (Blueprint $table) {
            if (! Schema::hasColumn('pagamento_onlines', 'paid_at')) {
                $table->timestamp('paid_at')->nullable()->after('status');
            }
        });

        // Broaden the legacy enum into a string column before normalizing values,
        // so the application can safely persist the canonical lowercase statuses.
        DB::statement("ALTER TABLE subscricoes MODIFY status VARCHAR(20) NOT NULL DEFAULT 'pendente'");

        DB::table('subscricoes')
            ->where('status', 'ATIVA')
            ->update(['status' => 'ativa']);

        DB::table('subscricoes')
            ->where('status', 'EXPIRADA')
            ->update(['status' => 'expirada']);
    }

    public function down(): void
    {
        Schema::table('pagamento_onlines', function (Blueprint $table) {
            if (Schema::hasColumn('pagamento_onlines', 'paid_at')) {
                $table->dropColumn('paid_at');
            }
        });

        Schema::table('subscricoes', function (Blueprint $table) {
            if (Schema::hasColumn('subscricoes', 'activated_at')) {
                $table->dropColumn('activated_at');
            }

            if (Schema::hasColumn('subscricoes', 'data_inicio')) {
                $table->dropColumn('data_inicio');
            }
        });

        DB::table('subscricoes')
            ->where('status', 'ativa')
            ->update(['status' => 'ATIVA']);

        DB::table('subscricoes')
            ->where('status', 'expirada')
            ->update(['status' => 'EXPIRADA']);

        DB::statement("ALTER TABLE subscricoes MODIFY status ENUM('ATIVA', 'EXPIRADA') NOT NULL DEFAULT 'ATIVA'");
    }
};
