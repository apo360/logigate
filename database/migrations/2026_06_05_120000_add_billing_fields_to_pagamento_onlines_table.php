<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pagamento_onlines', function (Blueprint $table) {
            if (! Schema::hasColumn('pagamento_onlines', 'empresa_id')) {
                $table->unsignedBigInteger('empresa_id')->nullable()->after('id')->index();
            }

            if (! Schema::hasColumn('pagamento_onlines', 'currency')) {
                $table->string('currency', 3)->default('AOA')->after('amount');
            }

            if (! Schema::hasColumn('pagamento_onlines', 'reference_entity')) {
                $table->string('reference_entity')->nullable()->after('status');
            }

            if (! Schema::hasColumn('pagamento_onlines', 'reference_number')) {
                $table->string('reference_number')->nullable()->after('reference_entity');
            }

            if (! Schema::hasColumn('pagamento_onlines', 'reference_due_date')) {
                $table->dateTime('reference_due_date')->nullable()->after('reference_number');
            }

            if (! Schema::hasColumn('pagamento_onlines', 'phone')) {
                $table->string('phone')->nullable()->after('reference_due_date');
            }

            if (! Schema::hasColumn('pagamento_onlines', 'failure_reason')) {
                $table->string('failure_reason')->nullable()->after('phone');
            }

            if (! Schema::hasColumn('pagamento_onlines', 'failed_at')) {
                $table->dateTime('failed_at')->nullable()->after('paid_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pagamento_onlines', function (Blueprint $table) {
            foreach ([
                'empresa_id',
                'currency',
                'reference_entity',
                'reference_number',
                'reference_due_date',
                'phone',
                'failure_reason',
                'failed_at',
            ] as $column) {
                if (Schema::hasColumn('pagamento_onlines', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
