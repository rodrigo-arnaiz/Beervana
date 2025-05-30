<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TipoFermentacionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('tipo_fermentacions')->insert([
            [
                'nombre' => 'Fermentación alta',
                'levadura' => 'Saccharomyces cerevisiae',
                'temperatura' => '15-22 °C',
                'tiempo' => '3-7 días',
                'descripcion' => 'Las levaduras ascienden a la superficie del fermentador durante la fermentación y producen cervezas con sabores afrutados, complejos y ricos en ésteres.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Fermentación baja',
                'levadura' => 'Saccharomyces pastorianus',
                'temperatura' => '7-13 °C',
                'tiempo' => '7-14 días',
                'descripcion' => 'La levadura trabaja a temperaturas más frías y se deposita en el fondo del fermentador. Se obtienen cervezas limpias, suaves y con menos sabores afrutados.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Fermentación espontánea',
                'levadura' => 'Levaduras salvajes variadas',
                'temperatura' => '10-25 °C',
                'tiempo' => '6-36 meses',
                'descripcion' => 'Fermentación realizada por levaduras y bacterias naturales del ambiente sin adición de cultivos.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Fermentación mixta',
                'levadura' => 'Saccharomyces cerevisiae y bacterias lácticas',
                'temperatura' => '18-22 °C',
                'tiempo' => '1-3 meses',
                'descripcion' => 'Fermentación que combina levaduras y bacterias para producir sabores complejos.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Fermentación secundaria',
                'levadura' => 'Saccharomyces cerevisiae',
                'temperatura' => '12-18 °C',
                'tiempo' => '14-30 días',
                'descripcion' => 'Fermentación adicional en botella o tanque para madurar y carbonatar la cerveza.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
