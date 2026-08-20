<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Reemplaza el booleano is_admin por un rol.
 *
 * Con un booleano solo hay dos mundos: cliente o dueño de todo. El cobro en el
 * mostrador lo hace quien atiende, que no tiene por qué poder editar el
 * catálogo ni ver la facturación del negocio. Con un rol eso se separa:
 *
 *   cliente   compra
 *   empleado  cobra en el mostrador
 *   admin     todo lo anterior + el panel de administración
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('rol')->default('cliente')->after('email');
        });

        DB::table('users')->where('is_admin', true)->update(['rol' => 'admin']);
        DB::table('users')->where('is_admin', false)->update(['rol' => 'cliente']);

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_admin');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_admin')->default(false);
        });

        DB::table('users')->where('rol', 'admin')->update(['is_admin' => true]);

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('rol');
        });
    }
};
