<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            
            UserRoleSeeder::class,
            // RolesAndPermissionsSeeder::class,

            /*ProvinciasSeeder::class,
            MunicipiosSeeder::class,
            PaisesSeeder::class,
            ModuleSeeder::class,
            MenuSeeder::class,
            PortosSeeder::class,
            ProductTypeSeeder::class,
            ProductExemptionReasonSeeder::class,
            InvoiceTypesSeeder::class,
            RegiaoAduaneirasSeeder::class,
            EstanciasSeeder::class,*/

        ]);
    }
}
