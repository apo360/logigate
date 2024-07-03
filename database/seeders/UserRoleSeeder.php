<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class UserRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Encontre ou crie o papel
        $role = Role::findOrCreate('admin');

        // Encontre o usuário
        $user = User::where('email', 'altina@kiar.ao')->first();

        // Atribua o papel ao usuário
        $user->assignRole($role);
    }
}
