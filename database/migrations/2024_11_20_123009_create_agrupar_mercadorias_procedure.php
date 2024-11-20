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
    public function up()
    {
        DB::unprepared('
            CREATE PROCEDURE AgruparMercadorias(p_licenciamento_id BIGINT unsigned)
            BEGIN
                -- INSERIR NOVOS AGRUPAMENTOS SE NÃO EXISTIREM
                IF NOT EXISTS (
                    SELECT 1
                    FROM mercadoria_agrupadas
                    WHERE licenciamento_id = p_licenciamento_id COLLATE utf8mb4_unicode_ci
                ) THEN
                    INSERT INTO mercadoria_agrupadas (
                        codigo_aduaneiro,
                        licenciamento_id,
                        quantidade_total,
                        peso_total,
                        preco_total,
                        mercadorias_ids,
                        created_at,
                        updated_at
                    )
                    SELECT 
                        codigo_aduaneiro COLLATE utf8mb4_unicode_ci,
                        p_licenciamento_id,
                        SUM(quantidade) AS quantidade_total,
                        SUM(peso) AS peso_total,
                        SUM(preco_total) AS preco_total,
                        JSON_ARRAYAGG(id) AS mercadorias_ids,
                        NOW(),
                        NOW()
                    FROM mercadorias
                    WHERE licenciamento_id = p_licenciamento_id COLLATE utf8mb4_unicode_ci
                    GROUP BY codigo_aduaneiro;
                ELSE
                    -- ATUALIZAR AGRUPAMENTOS EXISTENTES
                    UPDATE mercadoria_agrupadas AS ma
                    JOIN (
                        SELECT 
                            codigo_aduaneiro,
                            SUM(quantidade) AS quantidade_total,
                            SUM(peso) AS peso_total,
                            SUM(preco_total) AS preco_total,
                            JSON_ARRAYAGG(id) AS mercadorias_ids
                        FROM mercadorias
                        WHERE licenciamento_id = p_licenciamento_id COLLATE utf8mb4_unicode_ci
                        GROUP BY codigo_aduaneiro
                    ) AS m
                    ON ma.codigo_aduaneiro = m.codigo_aduaneiro COLLATE utf8mb4_unicode_ci
                    AND ma.licenciamento_id = p_licenciamento_id
                    SET 
                        ma.quantidade_total = m.quantidade_total,
                        ma.peso_total = m.peso_total,
                        ma.preco_total = m.preco_total,
                        ma.mercadorias_ids = m.mercadorias_ids,
                        ma.updated_at = NOW();
                END IF;
                
                -- ATUALIZAR OS CAMPOS DA TABELA licenciamento
                UPDATE licenciamentos
                SET 
                    adicoes = (
                        SELECT COUNT(*) 
                        FROM mercadoria_agrupadas 
                        WHERE licenciamento_id = p_licenciamento_id
                    ),
                    peso_bruto = (
                        SELECT SUM(peso_total) 
                        FROM mercadoria_agrupadas 
                        WHERE licenciamento_id = p_licenciamento_id
                    ),
                    fob_total = (
                        SELECT SUM(preco_total) 
                        FROM mercadoria_agrupadas 
                        WHERE licenciamento_id = p_licenciamento_id
                    ),
                    cif = (
                        SELECT SUM(preco_total) 
                        FROM mercadoria_agrupadas 
                        WHERE licenciamento_id = p_licenciamento_id
                    ) + frete + seguro -- Cálculo do CIF
                WHERE id = p_licenciamento_id;
            END
        ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared('DROP PROCEDURE IF EXISTS AgruparMercadorias');
    }
};
