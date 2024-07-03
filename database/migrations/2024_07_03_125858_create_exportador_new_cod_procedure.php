<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateExportadorNewCodProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("
            CREATE PROCEDURE ExportadorNewCod()
            BEGIN
                DECLARE codigoExportador VARCHAR(50);
                DECLARE AnoActual INT;

                SET AnoActual = YEAR(CURDATE());

                SET codigoExportador = (SELECT CONCAT('exp', LPAD(COUNT(*) + 1, 4, '0'), '/', AnoActual) FROM exportadors);

                SELECT codigoExportador;
            END
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('DROP PROCEDURE IF EXISTS ExportadorNewCod');
    }
}

