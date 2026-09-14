<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Código corto para identificar el pedido en el mostrador.
 *
 * El id numérico no sirve para eso: es secuencial y adivinable, así que
 * cualquiera podría pedir que le cobren "el pedido 42". El código es aleatorio
 * y lo trae impreso el cliente en su comprobante; el cajero lo busca por ahí y
 * ve exactamente qué pedido está por cobrar y a nombre de quién.
 *
 * Se evitan los caracteres que se confunden al dictarlos o tipearlos
 * (0/O, 1/I/L) porque este código se lee en voz alta.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->string('codigo', 12)->nullable()->unique()->after('id');
        });

        // Los pedidos que ya existen también necesitan uno
        foreach (DB::table('pedidos')->whereNull('codigo')->pluck('id') as $id) {
            DB::table('pedidos')->where('id', $id)->update([
                'codigo' => \App\Models\Pedido::generarCodigo(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->dropUnique(['codigo']);
            $table->dropColumn('codigo');
        });
    }
};
