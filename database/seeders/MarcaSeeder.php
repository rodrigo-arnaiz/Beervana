<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MarcaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $marcas = [
            'Quilmes',
            'Patagonia',
            'Antares',
            'Andes',
            'Imperial',
            'Brahma',
            'Stella Artois',
            'Corona',
            'Heineken',
            'Schneider',
        ];

        foreach ($marcas as $nombre) {
            DB::table('marcas')->insert([
                'nombre' => $nombre,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
