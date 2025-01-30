<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::unprepared('
            CREATE TRIGGER before_emolumento_insert_update
            BEFORE INSERT ON emolumento_tarifas
            FOR EACH ROW
            BEGIN
                SET NEW.guia_fiscal = (
                    NEW.direitos + NEW.emolumentos + NEW.porto + NEW.terminal + 
                    NEW.lmc + NEW.navegacao + NEW.inerentes + NEW.frete + 
                    NEW.carga_descarga + NEW.deslocacao + NEW.selos + 
                    NEW.iva_aduaneiro + NEW.iec + NEW.impostoEstatistico + 
                    NEW.juros_mora + NEW.multas + NEW.caucao + NEW.honorario + 
                    NEW.honorario_iva + NEW.orgaos_ofiais
                );
            END;
        ');

        DB::unprepared('
            CREATE TRIGGER before_emolumento_update
            BEFORE UPDATE ON emolumento_tarifas
            FOR EACH ROW
            BEGIN
                SET NEW.guia_fiscal = (
                    COALESCE(NEW.direitos, 0) + COALESCE(NEW.emolumentos, 0) + COALESCE(NEW.porto, 0) + 
                    COALESCE(NEW.terminal, 0) + COALESCE(NEW.lmc, 0) + COALESCE(NEW.navegacao, 0) + 
                    COALESCE(NEW.inerentes, 0) + COALESCE(NEW.frete, 0) + COALESCE(NEW.carga_descarga, 0) + 
                    COALESCE(NEW.deslocacao, 0) + COALESCE(NEW.selos, 0) + COALESCE(NEW.iva_aduaneiro, 0) + 
                    COALESCE(NEW.iec, 0) + COALESCE(NEW.impostoEstatistico, 0) + COALESCE(NEW.juros_mora, 0) + 
                    COALESCE(NEW.multas, 0) + COALESCE(NEW.caucao, 0) + COALESCE(NEW.honorario, 0) + 
                    COALESCE(NEW.honorario_iva, 0) + COALESCE(NEW.orgaos_ofiais, 0)
                );
            END;
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS before_emolumento_insert_update');
        DB::unprepared('DROP TRIGGER IF EXISTS before_emolumento_update');
    }
};
