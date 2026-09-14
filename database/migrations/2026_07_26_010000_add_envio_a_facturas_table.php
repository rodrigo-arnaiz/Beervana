<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * El carrito mostraba "Envío: $200" y lo sumaba al total, pero la factura solo
 * guardaba la suma de los subtotales: el usuario aceptaba pagar $7.000 y la
 * factura decía $6.800.
 *
 * Ahora el envío se guarda en la factura y forma parte de precio_total. Se
 * registra también el método elegido, porque el retiro en el local no se cobra
 * y hay que poder distinguir una factura sin envío de una con envío gratis.
 *
 * Las facturas viejas quedan como 'envio' con costo 0, que es lo que
 * efectivamente se les cobró.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('facturas', function (Blueprint $table) {
            $table->string('metodo_entrega')->default('envio')->after('precio_total');
            $table->decimal('envio', 10, 2)->default(0)->after('metodo_entrega');
        });
    }

    public function down(): void
    {
        Schema::table('facturas', function (Blueprint $table) {
            $table->dropColumn(['metodo_entrega', 'envio']);
        });
    }
};
