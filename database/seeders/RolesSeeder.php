<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        // Insertar roles si no existen
        DB::table('roles')->updateOrInsert(['name' => 'admin'], []);
        DB::table('roles')->updateOrInsert(['name' => 'consultor'], []);

        // Crear usuario admin si no existe
        $user = User::firstOrCreate(
            ['email' => 'admin@oast.local'],
            ['name' => 'Admin', 'password' => Hash::make('123456')]
        );

        // Vincular usuario con rol admin
        $adminId = DB::table('roles')->where('name','admin')->value('id');
        DB::table('role_user')->updateOrInsert(
            ['user_id' => $user->id, 'role_id' => $adminId],
            []
        );
    }
}