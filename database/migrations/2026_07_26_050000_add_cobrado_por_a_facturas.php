<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Quién registró el cobro.
 *
 * La factura ya guardaba al cliente, pero no a quien atendió. Sin eso no hay
 * forma de que un cajero vea sus propios cobros ni de hacer un arqueo de caja
 * por turno.
 *
 * nullOnDelete: si se borra el empleado, la factura no se va con él. El
 * comprobante de una venta tiene que sobrevivir a la baja de un usuario.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('facturas', function (Blueprint $table) {
            $table->foreignId('cobrado_por')->nullable()->after('user_id')
                ->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('facturas', function (Blueprint $table) {
            $table->dropConstrainedForeignId('cobrado_por');
        });
    }
};
