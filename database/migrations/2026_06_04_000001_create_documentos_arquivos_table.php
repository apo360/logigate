<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documentos_arquivos', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('empresa_id')->index();
            $table->foreignId('customer_id')->nullable()->index();
            $table->foreignId('processo_id')->nullable()->index();
            $table->foreignId('licenciamento_id')->nullable()->index();
            $table->nullableMorphs('documentable');
            $table->string('contexto', 40)->index();
            $table->string('categoria', 80)->index();
            $table->string('visibilidade', 40)->default('privado')->index();
            $table->string('storage_disk', 40)->default('s3');
            $table->string('bucket')->nullable();
            $table->string('storage_key', 1024)->unique();
            $table->string('nome_original');
            $table->string('mime_type', 150)->nullable();
            $table->string('extension', 20)->nullable();
            $table->unsignedBigInteger('size_bytes')->default(0);
            $table->string('sha256_hash', 64)->nullable()->index();
            $table->foreignId('uploaded_by')->nullable()->index();
            $table->foreignId('deleted_by')->nullable()->index();
            $table->timestamp('retention_until')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documentos_arquivos');
    }
};
