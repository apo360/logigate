<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        // DB::statement('TRUNCATE TABLE menus');
        // DB::statement('TRUNCATE TABLE paises');
        // DB::statement('TRUNCATE TABLE portos');

        $this->call([
            /*TipoTransporteSeeder::class,
            TaxTableSeeder::class,
            ProdutoSeeder::class,
            UserRoleSeeder::class,
            EmpresasSeeder::class,
            ProductGroupsSeeder::class,
            ProvinciasSeeder::class,
            MunicipiosSeeder::class,
            PaisesSeeder::class,
            ModuleSeeder::class,
            MenuSeeder::class, 
            ProductTypeSeeder::class,
            ProductExemptionReasonSeeder::class, 
            InvoiceTypesSeeder::class,
            RegiaoAduaneirasSeeder::class,
            EstanciasSeeder::class, 
            PortosSeeder::class,
            CategoriaAduaneiraSeeder::class,
            SubcategoriaSeeder::class,
            CondicaoPagamentoSeeder::class,
            MercadoriaLocalizacaoSeeder::class,
            PlanoSeeder::class,
            PlanoModuloSeeder::class,*/
            
            //UserRoleSeeder::class,
        ]);

        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }
}
