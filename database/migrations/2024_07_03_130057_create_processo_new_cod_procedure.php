<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateProcessoNewCodProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("
            CREATE PROCEDURE ProcessoNewCod()
            BEGIN
                DECLARE codProcesso VARCHAR(50);
                DECLARE AnoActual INT;

                SET AnoActual = YEAR(CURDATE());

                SET codProcesso = (SELECT CONCAT(LPAD(COUNT(*) + 1, 4, '0'), '/', AnoActual) FROM processos);

                SELECT codProcesso;
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
        DB::statement('DROP PROCEDURE IF EXISTS ProcessoNewCod');
    }
}

