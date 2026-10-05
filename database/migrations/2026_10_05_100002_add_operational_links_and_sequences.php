<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        foreach (['NrProcesso', 'ContaDespacho'] as $column) {
            if (DB::table('processos')->select('empresa_id', $column)->whereNotNull($column)->groupBy('empresa_id', $column)->havingRaw('COUNT(*) > 1')->exists()) {
                throw new RuntimeException('Existem duplicados em ' . $column . '. Reveja o diagnóstico antes de aplicar a migration.');
            }
        }
        Schema::create('operational_sequences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas');
            $table->string('kind', 30);
            $table->unsignedSmallInteger('year');
            $table->unsignedBigInteger('value')->default(0);
            $table->unique(['empresa_id', 'kind', 'year']);
        });
        Schema::create('licenciamento_processos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas');
            $table->foreignId('licenciamento_id')->unique()->constrained('licenciamentos');
            $table->foreignId('processo_id')->constrained('processos');
            $table->foreignId('actor_id')->constrained('users');
            $table->timestamps();
        });
        Schema::table('processos', function (Blueprint $table) {
            $table->unique(['empresa_id', 'NrProcesso'], 'processos_empresa_numero_unique');
            $table->unique(['empresa_id', 'ContaDespacho'], 'processos_empresa_conta_unique');
        });
    }
    public function down(): void
    {
        Schema::table('processos', function (Blueprint $table) {
            $table->dropUnique('processos_empresa_numero_unique');
            $table->dropUnique('processos_empresa_conta_unique');
        });
        Schema::dropIfExists('licenciamento_processos');
        Schema::dropIfExists('operational_sequences');
    }
};
