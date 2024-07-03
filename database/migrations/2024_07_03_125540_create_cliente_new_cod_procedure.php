<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateClienteNewCodProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("
            CREATE PROCEDURE ClienteNewCod()
            BEGIN
                DECLARE codigoCliente VARCHAR(50);
                DECLARE AnoActual INT;

                SET AnoActual = YEAR(CURDATE());

                SET codigoCliente = (SELECT CONCAT('cli', LPAD(COUNT(*) + 1, 4, '0'), '/', AnoActual) FROM customers);

                SELECT codigoCliente;
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
        DB::statement('DROP PROCEDURE IF EXISTS ClienteNewCod');
    }
}

