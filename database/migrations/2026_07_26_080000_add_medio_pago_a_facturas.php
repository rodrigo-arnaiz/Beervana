<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Con qué se pagó la factura.
 *
 * Hasta ahora todo cobro era presencial y en efectivo, así que no hacía falta
 * distinguir. Con Mercado Pago hay dos caminos que terminan en la misma factura
 * y sin esta columna el arqueo de caja mezclaría la plata que está en el cajón
 * con la que está en la cuenta de MP.
 *
 * Las facturas viejas quedan como 'efectivo': es lo que efectivamente fueron.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('facturas', function (Blueprint $table) {
            $table->string('medio_pago', 20)->default('efectivo')->after('cobrado_por');
        });

        DB::table('facturas')->update(['medio_pago' => 'efectivo']);
    }

    public function down(): void
    {
        Schema::table('facturas', function (Blueprint $table) {
            $table->dropColumn('medio_pago');
        });
    }
};
