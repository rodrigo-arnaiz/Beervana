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
            'is_admin' => true,
        ]);
        User::create([
            'name'     => 'usuario',
            'email'    => 'noadmin@beervana.com',
            'password' => Hash::make('noadmin123'),
            'is_admin' => false,
        ]);
        
    }
}
