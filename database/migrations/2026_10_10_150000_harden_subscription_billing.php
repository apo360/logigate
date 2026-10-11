<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Preserve existing commercial records: refuse ambiguous duplicates.
        if (DB::table('pagamento_onlines')->select('idempotency_key')->whereNotNull('idempotency_key')->groupBy('idempotency_key')->havingRaw('COUNT(*) > 1')->exists()) {
            throw new RuntimeException('Duplicate payment idempotency keys require reconciliation before migration.');
        }
        if (! Schema::hasColumn('subscricoes', 'commercial_snapshot')) {
            Schema::table('subscricoes', function (Blueprint $table) {
                $table->json('commercial_snapshot')->nullable();
                $table->timestamp('renewal_notified_at')->nullable();
            });
        }
        if (! Schema::hasColumn('webhook_events', 'payload')) {
            Schema::table('webhook_events', function (Blueprint $table) {
                $table->timestamp('processed_at')->nullable()->change();
                $table->json('payload')->nullable();
                $table->string('status')->default('processed')->index();
                $table->unsignedInteger('attempts')->default(0);
                $table->text('last_error')->nullable();
                $table->timestamp('received_at')->nullable();
            });
        }
        if (! collect(Schema::getIndexes('pagamento_onlines'))->contains('name', 'billing_idempotency_unique')) {
            Schema::table('pagamento_onlines', fn (Blueprint $table) => $table->unique('idempotency_key', 'billing_idempotency_unique'));
        }
        if (! Schema::hasTable('notifications')) {
            Schema::create('notifications', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('type');
                $table->morphs('notifiable');
                $table->text('data');
                $table->timestamp('read_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::table('pagamento_onlines', fn (Blueprint $table) => $table->dropUnique('billing_idempotency_unique'));
        Schema::table('subscricoes', fn (Blueprint $table) => $table->dropColumn(['commercial_snapshot', 'renewal_notified_at']));
        // Keep processed_at nullable so historical unresolved events stay representable.
        Schema::table('webhook_events', fn (Blueprint $table) => $table->dropColumn(['payload', 'status', 'attempts', 'last_error', 'received_at']));
    }
};
