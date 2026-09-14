<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;


class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name'     => 'Administrador',
            'email'    => 'admin@beervana.com',
            'password' => Hash::make('admin123'),
            'rol' => User::ROL_ADMIN,
            // Su rol es inmutable: ni otro administrador puede degradarlo.
            // Garantiza que siempre quede alguien que administre el sistema.
            'super_admin' => true,
        ]);
        // Cajero: entra al panel pero solo ve el mostrador y sus propios cobros
        User::create([
            'name'     => 'Cajero',
            'email'    => 'cajero@beervana.com',
            'password' => Hash::make('cajero123'),
            'rol'      => User::ROL_EMPLEADO,
        ]);

        User::create([
            'name'     => 'usuario',
            'email'    => 'noadmin@beervana.com',
            'password' => Hash::make('noadmin123'),
            'rol' => User::ROL_CLIENTE,
        ]);
        
    }
}
