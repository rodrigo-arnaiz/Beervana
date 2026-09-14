<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Marca al administrador cuyo rol nadie puede tocar.
 *
 * Va como columna y no como una constante con el email en el código: si el día
 * de mañana esa cuenta cambia de dirección, una comparación por string movería
 * la protección de lugar en silencio. Acá la marca viaja con la fila.
 *
 * Además garantiza que siempre quede al menos un admin: como su rol es
 * inmutable, no hay forma de quedarse sin nadie que administre el sistema.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('super_admin')->default(false)->after('rol');
        });

        $marcado = DB::table('users')->where('email', 'admin@beervana.com')->update(['super_admin' => true]);

        // Si esa cuenta no existe (base nueva o con otros datos), se marca al
        // admin más antiguo para no dejar el sistema sin cuenta protegida.
        if ($marcado === 0) {
            $primerAdmin = DB::table('users')->where('rol', 'admin')->orderBy('id')->first();

            if ($primerAdmin) {
                DB::table('users')->where('id', $primerAdmin->id)->update(['super_admin' => true]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('super_admin');
        });
    }
};
