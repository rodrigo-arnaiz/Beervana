<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Un usuario debe tener un solo carrito.
 *
 * CarritoController usa Carrito::firstOrCreate(['user_id' => ...]), que sin
 * restricción de unicidad inserta dos filas si dos requests llegan en paralelo.
 * Pasó de verdad: el CartProvider duplicado del frontend disparaba dos
 * GET /carrito simultáneos y quedaron usuarios con dos carritos. Cuál devuelve
 * first() después es indeterminado, así que "ver" y "vaciar" pueden apuntar a
 * filas distintas.
 *
 * OJO: el up() borra los carritos duplicados que ya existan. Se conserva el que
 * tenga ítems (o el más viejo si ninguno los tiene); el resto se elimina, y
 * carrito_items cae por la foreign key en cascada.
 */
return new class extends Migration
{
    public function up(): void
    {
        $usuariosConDuplicados = DB::table('carritos')
            ->select('user_id')
            ->groupBy('user_id')
            ->havingRaw('count(*) > 1')
            ->pluck('user_id');

        foreach ($usuariosConDuplicados as $userId) {
            $ids = DB::table('carritos')
                ->leftJoin('carrito_items', 'carrito_items.carrito_id', '=', 'carritos.id')
                ->where('carritos.user_id', $userId)
                ->groupBy('carritos.id')
                ->orderByRaw('count(carrito_items.id) desc, carritos.id asc')
                ->pluck('carritos.id');

            // El primero se queda; los demás se van.
            $aBorrar = $ids->slice(1)->values();

            if ($aBorrar->isNotEmpty()) {
                DB::table('carritos')->whereIn('id', $aBorrar)->delete();
            }
        }

        Schema::table('carritos', function (Blueprint $table) {
            $table->unique('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('carritos', function (Blueprint $table) {
            $table->dropUnique(['user_id']);
        });
    }
};
