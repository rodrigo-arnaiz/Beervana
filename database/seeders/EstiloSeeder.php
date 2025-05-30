<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EstiloSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $estilos = [
            [
                'nombre' => 'IPA',
                'descripcion' => 'Cerveza de alta graduación alcohólica, muy lupulada y con un amargor pronunciado.',
                'tipo_fermentacion_id' => 1, // Alta
            ],
            [
                'nombre' => 'Stout',
                'descripcion' => 'Oscura, densa y con sabores a café y chocolate. De fermentación alta.',
                'tipo_fermentacion_id' => 1, // Alta
            ],
            [
                'nombre' => 'Pilsner',
                'descripcion' => 'Estilo claro, ligero y muy refrescante, originario de la República Checa.',
                'tipo_fermentacion_id' => 2, // Baja
            ],
            [
                'nombre' => 'Porter',
                'descripcion' => 'Parecida a la stout, pero más ligera y menos tostada. De fermentación alta.',
                'tipo_fermentacion_id' => 1, // Alta
            ],
            [
                'nombre' => 'Lager',
                'descripcion' => 'Una de las más consumidas del mundo, de sabor suave y fermentación baja.',
                'tipo_fermentacion_id' => 2, // Baja
            ],
            [
                'nombre' => 'Saison',
                'descripcion' => 'Estilo belga especiado y afrutado, tradicionalmente elaborado en granjas.',
                'tipo_fermentacion_id' => 1, // Alta
            ],
            [
                'nombre' => 'Weissbier',
                'descripcion' => 'Cerveza de trigo alemana, turbia, con notas a banana y clavo de olor.',
                'tipo_fermentacion_id' => 1, // Alta
            ],
            [
                'nombre' => 'Bock',
                'descripcion' => 'Lager fuerte y maltosa, con cuerpo y notas tostadas.',
                'tipo_fermentacion_id' => 2, // Baja
            ],
            [
                'nombre' => 'Lambic',
                'descripcion' => 'Fermentada espontáneamente con levaduras salvajes. Tradicional de Bélgica.',
                'tipo_fermentacion_id' => 3, // Espontánea
            ],
            [
                'nombre' => 'Dubbel',
                'descripcion' => 'Estilo trapense oscuro, con notas a frutas secas y caramelo.',
                'tipo_fermentacion_id' => 1, // Alta
            ],
            // Fermentación mixta
            [
                'nombre' => 'American Wild Ale',
                'descripcion' => 'Estilo experimental con levaduras salvajes que generan sabores ácidos y afrutados.',
                'tipo_fermentacion_id' => 4,
            ],
            [
                'nombre' => 'Flanders Red Ale',
                'descripcion' => 'Cerveza belga envejecida en barricas, con notas agrias y sabores complejos.',
                'tipo_fermentacion_id' => 4,
            ],

            // Fermentación secundaria
            [
                'nombre' => 'Barleywine',
                'descripcion' => 'Cerveza fuerte y maltosa, que se beneficia del añejamiento en botella.',
                'tipo_fermentacion_id' => 5,
            ],
            [
                'nombre' => 'Bière de Garde',
                'descripcion' => 'Estilo francés de guarda prolongada, con notas maltosas y cuerpo medio.',
                'tipo_fermentacion_id' => 5,
            ],
        ];

        foreach ($estilos as $estilo) {
            DB::table('estilos')->insert([
                'nombre' => $estilo['nombre'],
                'descripcion' => $estilo['descripcion'],
                'tipo_fermentacion_id' => $estilo['tipo_fermentacion_id'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
