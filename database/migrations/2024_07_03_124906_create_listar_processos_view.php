<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateListarProcessosView extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("
            CREATE VIEW listar_processos AS
            SELECT 
                `processos`.`id` AS `id`,
                `processos`.`NrProcesso` AS `NrProcesso`,
                `processos`.`ContaDespacho` AS `ContaDespacho`,
                `processos`.`RefCliente` AS `RefCliente`,
                `processos`.`Descricao` AS `Descricao`,
                `processos`.`DataAbertura` AS `DataAbertura`,
                `processos`.`DataFecho` AS `DataFecho`,
                `processos`.`TipoProcesso` AS `TipoProcesso`,
                `processos`.`Situacao` AS `Situacao`,
                `processos`.`customer_id` AS `customer_id`,
                `processos`.`user_id` AS `user_id`,
                `processos`.`empresa_id` AS `empresa_id`,
                `processos`.`created_at` AS `created_at`,
                `processos`.`updated_at` AS `updated_at`,
                `cliente`.`CompanyName` AS `CompanyName`,
                `importacao`.`BLC_Porte` AS `BLC_Porte`,
                `importacao`.`Cambio` AS `Cambio`,
                `importacao`.`MarcaFiscal` AS `MarcaFiscal`,
                `importacao`.`NomeTransporte` AS `NomeTransporte`,
                `importacao`.`PortoOrigem` AS `PortoOrigem`,
                `importacao`.`TipoTransporte` AS `TipoTransporte`,
                `importacao`.`ValorAduaneiro` AS `ValorAduaneiro`,
                `origem`.`pais` AS `origem`,
                `destino`.`pais` AS `destino`,
                `origem`.`codigo` AS `codigo`,
                `empresa`.`id` AS `IdEmpresa`,
                `empresa`.`Empresa` AS `Empresa`
            FROM
                (((((`processos`
                LEFT JOIN `importacao` ON ((`importacao`.`processo_id` = `processos`.`id`)))
                LEFT JOIN `paises` `origem` ON ((`importacao`.`Fk_pais_origem` = `origem`.`id`)))
                LEFT JOIN `paises` `destino` ON ((`importacao`.`Fk_pais_destino` = `destino`.`id`)))
                LEFT JOIN `customers` `cliente` ON ((`processos`.`customer_id` = `cliente`.`id`)))
                LEFT JOIN `empresas` `empresa` ON ((`processos`.`empresa_id` = `empresa`.`id`)))
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('DROP VIEW IF EXISTS listar_processos');
    }
}

