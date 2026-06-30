<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('arquivo_pastas')) {
            return;
        }

        Schema::create('arquivo_pastas', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('empresa_id')->index();
            $table->foreignId('parent_id')->nullable()->index();
            $table->string('name', 160);
            $table->string('slug', 160);
            $table->string('path', 191)->default('');
            $table->string('type', 40)->default('custom')->index();
            $table->boolean('is_system')->default(false)->index();
            $table->boolean('is_locked')->default(false)->index();
            $table->foreignId('created_by')->nullable()->index();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['empresa_id', 'path']);
            $table->index(['empresa_id', 'parent_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('arquivo_pastas');
    }
};
