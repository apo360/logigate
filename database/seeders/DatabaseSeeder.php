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

        DB::statement('TRUNCATE TABLE menus');

        $this->call([
            
            /*UserRoleSeeder::class,
            //RolesAndPermissionsSeeder::class,

            EmpresasSeeder::class,
            ProductGroupsSeeder::class,
            ProvinciasSeeder::class,
            MunicipiosSeeder::class,
            PaisesSeeder::class,
            ModuleSeeder::class,
            MenuSeeder::class, 
            ProductTypeSeeder::class,
            ProductExemptionReasonSeeder::class, 
            ProdutoSeeder::class,
            InvoiceTypesSeeder::class,
            RegiaoAduaneirasSeeder::class,
            EstanciasSeeder::class, 
            PortosSeeder::class, */

        ]);
    }
}
